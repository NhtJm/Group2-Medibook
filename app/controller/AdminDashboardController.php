<?php
// app/controller/AdminDashboardController.php
require_once __DIR__ . '/../db.php';

class AdminDashboardController
{
    public static function index(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $conn = Database::get_instance();

        // --- helpers ---
        $scalar = function (mysqli $conn, string $sql, array $bind = null, string $types = ''): int {
            if ($bind) {
                $st = $conn->prepare($sql);
                $st->bind_param($types, ...$bind);
                $st->execute();
                $n = (int) ($st->get_result()->fetch_assoc()['n'] ?? 0);
                $st->close();
                return $n;
            }
            $rs = $conn->query($sql);
            $n = (int) ($rs->fetch_assoc()['n'] ?? 0);
            $rs->close();
            return $n;
        };
        $hasCol = function (mysqli $conn, string $table, string $col): bool {
            // Use INFORMATION_SCHEMA; SHOW ... LIKE ? cannot be prepared
            $st = $conn->prepare("
                SELECT COUNT(*) AS n
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = ?
            ");
            $st->bind_param('ss', $table, $col);
            $st->execute();
            $n = (int) ($st->get_result()->fetch_assoc()['n'] ?? 0);
            $st->close();
            return $n > 0;
        };

        // ---------- KPIs ----------
        $hasStatus = $hasCol($conn, 'Doctor', 'status');
        $doctorsSql = $hasStatus
            ? "SELECT COUNT(*) AS n FROM Doctor WHERE status IN ('active','approved')"
            : "SELECT COUNT(*) AS n FROM Doctor";
        $doctorsActive    = $scalar($conn, $doctorsSql);
        $patientsTotal    = $scalar($conn, "SELECT COUNT(*) AS n FROM Patient");
        $officesTotal     = $scalar($conn, "SELECT COUNT(*) AS n FROM Office");
        $usersTotal       = $scalar($conn, "SELECT COUNT(*) AS n FROM Users");
        $specialtiesTotal = $scalar($conn, "SELECT COUNT(*) AS n FROM Medical_specialty");

        // Appointments this week (Mon..Sun)
        $tz = new DateTimeZone(date_default_timezone_get());
        $startW = new DateTimeImmutable('monday this week 00:00:00', $tz);
        $endW   = $startW->modify('+6 days 23:59:59');
        $from = $startW->format('Y-m-d H:i:s');
        $to   = $endW->format('Y-m-d H:i:s');
        $apptsWeek = $scalar(
            $conn,
            "SELECT COUNT(*) AS n
             FROM Appointment a
             JOIN Appointment_slot s ON a.slot_id = s.slot_id
             WHERE s.start_time BETWEEN ? AND ?",
            [$from, $to],
            'ss'
        );

        // ---------- Pending clinics (resilient to missing columns) ----------
        $hasCity      = $hasCol($conn, 'Office', 'city');
        $hasCountry   = $hasCol($conn, 'Office', 'country');
        $hasCreatedAt = $hasCol($conn, 'Office', 'created_at');

        $selCity    = $hasCity    ? "o.city AS city"       : "'' AS city";
        $selCountry = $hasCountry ? "o.country AS country" : "'' AS country";
        $orderExpr  = $hasCreatedAt ? "o.created_at" : "o.office_id";

        $sqlPend = "
            SELECT o.office_id, o.name, $selCity, $selCountry, o.status
            FROM Office o
            WHERE o.status = 'pending'
            ORDER BY $orderExpr DESC
            LIMIT ?
        ";
        $limit = 6;
        $st = $conn->prepare($sqlPend);
        $st->bind_param('i', $limit);
        $st->execute();
        $pending = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st->close();

        return [
            'kpis' => [
                'doctors_active'    => $doctorsActive,
                'patients_total'    => $patientsTotal,
                'appointments_week' => $apptsWeek,
                'offices_total'     => $officesTotal,
                'users_total'       => $usersTotal,
                'specialties_total' => $specialtiesTotal,
            ],
            'pending_offices' => $pending, // used by your "Pending Clinics" panel
        ];
    }
}