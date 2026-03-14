<?php
// app/controller/OfficeDashboardController.php
if (session_status() === PHP_SESSION_NONE)
    session_start();

class OfficeDashboardController
{
    public static function index(): array
    {
        $u = $_SESSION['user'] ?? null;
        $role = $u['role'] ?? null;
        $officeId = (int) ($u['profile_id'] ?? 0);

        if (!$u || $role !== 'office' || $officeId <= 0) {
            return ['redirect' => 'dashboard'];
        }

        require_once __DIR__ . '/../db.php';
        global $conn;
        if (!($conn instanceof mysqli) && class_exists('Database')) {
            $conn = Database::get_instance();
        }
        if (!($conn instanceof mysqli)) {
            return self::emptyPayload($officeId, 'Database connection not available');
        }

        // Column helpers (so we tolerate minor naming differences)
        $docNameCol = self::pickColumn($conn, 'Doctor', ['full_name', 'name', 'doctor_name']) ?: 'full_name';
        $docStateCol = self::pickColumn($conn, 'Doctor', ['status', 'state', 'is_online']); // optional
        $docRateCol = self::pickColumn($conn, 'Doctor', ['rating', 'avg_rating']);         // optional
        $offRateCol = self::pickColumn($conn, 'Office', ['rating', 'avg_rating']);         // optional
        $specNameCol = self::pickColumn($conn, 'Medical_specialty', ['name', 'title']) ?: 'name';

        /* -------------------- Doctors in this office -------------------- */
        $doctors = [];
        $sql = "SELECT d.doctor_id, d.$docNameCol AS name, d.specialty_id"
            . ($docStateCol ? ", d.$docStateCol AS status" : "")
            . ($docRateCol ? ", d.$docRateCol  AS rating" : "")
            . " FROM Doctor d WHERE d.office_id=? ORDER BY name";
        if ($st = $conn->prepare($sql)) {
            $st->bind_param('i', $officeId);
            $st->execute();
            $doctors = $st->get_result()->fetch_all(MYSQLI_ASSOC);
            $st->close();
        }

        /* -------------------- Specialty map ----------------------------- */
        $specMap = [];
        if ($res = $conn->query("SELECT specialty_id, $specNameCol AS name FROM Medical_specialty")) {
            while ($row = $res->fetch_assoc()) {
                $specMap[(int) $row['specialty_id']] = $row['name'];
            }
        }

        /* -------------------- Stats ------------------------------------ */
        $stats = [
            'doctors' => count($doctors),
            'today' => 0,
            'avg_rating' => null,
            'avg_wait' => null, // not present in your schema; keep null
        ];
        // default
        $stats['avail_today'] = 0;

        // count available slots today for this office
        $sqlAvail = "
  SELECT COUNT(*) 
  FROM Appointment_slot s
  JOIN Doctor d ON d.doctor_id = s.doctor_id
  WHERE d.office_id = ?
    AND s.status = 'available'
    AND DATE(s.start_time) = CURDATE()
";
        if ($st = $conn->prepare($sqlAvail)) {
            $st->bind_param('i', $officeId);
            $st->execute();
            $st->bind_result($cnt);
            $st->fetch();
            $st->close();
            $stats['avail_today'] = (int) $cnt;
        }
        // Appointments today (by office via Doctor.office_id, time via Appointment_slot.start_time)
        $sql = "SELECT COUNT(*) 
                FROM Appointment a
                JOIN Appointment_slot s ON s.slot_id = a.slot_id
                JOIN Doctor d          ON d.doctor_id = s.doctor_id
                WHERE d.office_id = ? AND DATE(s.start_time) = CURDATE()";
        if ($st = $conn->prepare($sql)) {
            $st->bind_param('i', $officeId);
            $st->execute();
            $st->bind_result($cnt);
            $st->fetch();
            $st->close();
            $stats['today'] = (int) $cnt;
        }

        // Average rating: prefer Office.rating; else average across doctors if present
        if ($offRateCol) {
            if ($st = $conn->prepare("SELECT $offRateCol FROM Office WHERE office_id=? LIMIT 1")) {
                $st->bind_param('i', $officeId);
                $st->execute();
                $st->bind_result($r);
                if ($st->fetch() && $r !== null)
                    $stats['avg_rating'] = round((float) $r, 1);
                $st->close();
            }
        }
        if ($stats['avg_rating'] === null && $docRateCol) {
            if ($st = $conn->prepare("SELECT AVG($docRateCol) FROM Doctor WHERE office_id=?")) {
                $st->bind_param('i', $officeId);
                $st->execute();
                $st->bind_result($r);
                if ($st->fetch() && $r !== null)
                    $stats['avg_rating'] = round((float) $r, 1);
                $st->close();
            }
        }

        /* -------------------- Upcoming appointments (next 8) ------------ */
        $appointments = [];
        $sql = "SELECT 
            a.appointment_id,
            s.start_time, s.end_time,
            d.doctor_id,
            d.$docNameCol AS doctor_name,
            d.specialty_id,
            p.patient_id,
            COALESCE(u.full_name, u.username, u.email, CONCAT('Patient #', p.patient_id)) AS patient_name
        FROM Appointment a
        JOIN Appointment_slot s ON s.slot_id = a.slot_id
        JOIN Doctor d          ON d.doctor_id = s.doctor_id
        JOIN Patient p         ON p.patient_id = a.patient_id
        LEFT JOIN Users u      ON u.user_id = p.user_id
        WHERE d.office_id = ? AND s.start_time >= NOW()
        ORDER BY s.start_time ASC
        LIMIT 8";
        if ($st = $conn->prepare($sql)) {
            $st->bind_param('i', $officeId);
            $st->execute();
            $appointments = $st->get_result()->fetch_all(MYSQLI_ASSOC);
            $st->close();
        }

        return [
            'office_id' => $officeId,
            'clinic_url' => self::clinicUrl($officeId),
            'stats' => $stats,
            'doctors' => $doctors,
            'appointments' => $appointments,
            'specMap' => $specMap,
        ];
    }

    /* ---------------------------- helpers ------------------------------ */
    private static function pickColumn(mysqli $conn, string $table, array $cands): ?string
    {
        if (!$res = $conn->query("SHOW COLUMNS FROM `$table`"))
            return null;
        $have = [];
        while ($r = $res->fetch_assoc())
            $have[strtolower($r['Field'])] = true;
        foreach ($cands as $c)
            if (isset($have[strtolower($c)]))
                return $c;
        return null;
    }

    private static function clinicUrl(int $officeId): string
    {
        $base = rtrim(BASE_URL ?? '', '/');
        return $officeId > 0 ? "$base/index.php?page=clinic&id=$officeId" : "$base/index.php?page=clinics";
    }

    private static function emptyPayload(int $officeId, string $err): array
    {
        return [
            'office_id' => $officeId,
            'clinic_url' => self::clinicUrl($officeId),
            'stats' => ['doctors' => 0, 'today' => 0, 'avg_rating' => null, 'avg_wait' => null],
            'doctors' => [],
            'appointments' => [],
            'specMap' => [],
            'error' => $err,
        ];
    }
}