<?php
// app/controller/ConfirmController.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Office.php';

class ConfirmController
{
    public static function show(): array
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            if (class_exists('Database') && method_exists('Database', 'get_instance')) {
                $conn = Database::get_instance();
            } else {
                // View will render a simple fallback
                return ['clinic' => null, 'doctor' => null, 'when' => null, 'slot_id' => null];
            }
        }

        $slotId = (int) ($_GET['slot'] ?? 0);
        $docId = (int) ($_GET['doc'] ?? 0);
        $officeId = (int) ($_GET['clinic'] ?? 0);
        $dateStr = trim((string) ($_GET['date'] ?? ''));
        $timeStr = trim((string) ($_GET['time'] ?? ''));

        $clinic = null;
        $doctor = null;
        $when = null;

        // Helper to compute initials
        $mkInitials = static function (string $name): string {
            $parts = preg_split('/\s+/u', trim($name)) ?: [];
            $ini = '';
            foreach ($parts as $p) {
                $ini .= mb_substr($p, 0, 1);
            }
            $ini = mb_strtoupper(preg_replace('/[^A-ZÀ-Ỵ]/iu', '', $ini));
            return $ini !== '' ? $ini : 'DR';
        };

        // ── Path A: called from doctor.php with a slot id (preferred)
        if ($slotId > 0) {
            $sql = "SELECT
                        s.slot_id, s.start_time, s.end_time, s.status,
                        d.doctor_id, d.office_id, d.doctor_name AS doc_name, d.degree,
                        d.specialty_id, ms.name AS specialty
                    FROM Appointment_slot s
                    JOIN Doctor d                  ON d.doctor_id = s.doctor_id
                    LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id
                    WHERE s.slot_id = ?
                    LIMIT 1";
            $st = $conn->prepare($sql);
            $st->bind_param('i', $slotId);
            $st->execute();
            $row = $st->get_result()->fetch_assoc();
            $st->close();

            if ($row) {
                $officeId = (int) $row['office_id'];
                $clinic = Office::findOne($conn, $officeId) ?: ['office_id' => $officeId];

                $doctor = [
                    'doctor_id' => (int) $row['doctor_id'],
                    'name' => (string) $row['doc_name'],
                    'degree' => (string) ($row['degree'] ?? ''),
                    'specialty' => (string) ($row['specialty'] ?? ''),
                    'initials' => $mkInitials((string) $row['doc_name']),
                ];

                $dtStart = new DateTimeImmutable($row['start_time']);
                $dtEnd = new DateTimeImmutable($row['end_time']);
                $when = [
                    'date_iso' => $dtStart->format('Y-m-d'),
                    'date_label' => $dtStart->format('l, d M Y'),
                    'time_label' => $dtStart->format('H:i') . ' – ' . $dtEnd->format('H:i'),
                ];
            }
        }

        // ── Path B: direct link with doc/clinic/date/time (no slot id)
        if (!$doctor && $docId > 0) {
            $sql = "SELECT d.doctor_id, d.office_id, d.doctor_name AS doc_name, d.degree,
                           d.specialty_id, ms.name AS specialty
                    FROM Doctor d
                    LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id
                    WHERE d.doctor_id = ?
                    LIMIT 1";
            $st = $conn->prepare($sql);
            $st->bind_param('i', $docId);
            $st->execute();
            $row = $st->get_result()->fetch_assoc();
            $st->close();

            if ($row) {
                // trust DB office_id when present
                $officeId = (int) ($row['office_id'] ?: $officeId);
                $clinic = Office::findOne($conn, $officeId) ?: ['office_id' => $officeId];

                $doctor = [
                    'doctor_id' => (int) $row['doctor_id'],
                    'name' => (string) $row['doc_name'],
                    'degree' => (string) ($row['degree'] ?? ''),
                    'specialty' => (string) ($row['specialty'] ?? ''),
                    'initials' => $mkInitials((string) $row['doc_name']),
                ];

                // Build a display-only "when" from query params
                if ($dateStr !== '') {
                    $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateStr) ?: new DateTimeImmutable($dateStr);
                    $when = [
                        'date_iso' => $date->format('Y-m-d'),
                        'date_label' => $date->format('l, d M Y'),
                        'time_label' => $timeStr, // may be empty
                    ];
                }
            }
        }

        return [
            'clinic' => $clinic,
            'doctor' => $doctor,
            'when' => $when,
            'slot_id' => $slotId ?: null,
        ];
    }
    public static function confirm(): array
    {
        // ---- DB bootstrap
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        // Accept slot from POST or GET (login bounce returns via GET)
        $slotId = (int) ($_POST['slot'] ?? $_GET['slot'] ?? 0);

        // Where to land after successful booking
        $defaultNext = BASE_URL . 'index.php?page=appointments';
        $next = trim((string) ($_GET['next'] ?? ''));
        if ($next === '' || self::isExternal($next)) {
            $next = $defaultNext; // safety: avoid open redirects
        }

        if ($slotId <= 0) {
            return ['error' => 'Missing time slot.'];
        }

        $u = current_user();
        $uid = (int) ($u['user_id'] ?? $u['id'] ?? ($_SESSION['user_id'] ?? 0));  // ← unified

        if ($uid <= 0) {
            // Build a return URL back to confirm (preserve slot & final next)
            $returnTo = BASE_URL . 'index.php?page=confirm'
                . '&slot=' . $slotId
                . '&do=confirm'                        // 👈 add this flag
                . '&next=' . rawurlencode($next);

            $loginUrl = BASE_URL . 'index.php?page=login'
                . '&next=' . rawurlencode($returnTo);
            header('Location: ' . $loginUrl);
            exit;
        }

        // ---------- ensure patient profile ----------
        $pid = null;
        if ($st = $conn->prepare("SELECT patient_id FROM Patient WHERE user_id=? LIMIT 1")) {
            $st->bind_param('i', $uid);
            $st->execute();
            $st->bind_result($pidTmp);
            if ($st->fetch())
                $pid = (int) $pidTmp;
            $st->close();
        }
        if (!$pid) {
            $st = $conn->prepare("INSERT INTO Patient (user_id, status) VALUES (?, 'active')");
            $st->bind_param('i', $uid);
            if (!$st->execute()) {
                return ['error' => 'Could not create patient profile.'];
            }
            $pid = (int) $conn->insert_id;
            $st->close();
        }

        // ---------- transactional booking ----------
        $conn->begin_transaction();
        try {
            // lock the slot row
            $st = $conn->prepare("
                SELECT slot_id, status, start_time
                  FROM Appointment_slot
                 WHERE slot_id = ?
                 FOR UPDATE
            ");
            $st->bind_param('i', $slotId);
            $st->execute();
            $slot = $st->get_result()->fetch_assoc();
            $st->close();

            if (!$slot) {
                throw new Exception('Slot not found.');
            }
            $status = strtolower((string) $slot['status']);
            if (!in_array($status, ['available', 'open', 'free'], true)) {
                throw new RuntimeException('E_ALREADY_BOOKED');
            }
            if (strtotime($slot['start_time']) <= time()) {
                throw new Exception('Slot is in the past.');
            }

            // insert appointment
            $st = $conn->prepare("
                INSERT INTO Appointment (patient_id, slot_id, status, booked_at)
                VALUES (?, ?, 'booked', NOW())
            ");
            $st->bind_param('ii', $pid, $slotId);
            if (!$st->execute()) {
                throw new Exception('Insert failed: ' . $conn->error);
            }
            $st->close();

            // mark slot booked
            $bookedStatus = 'unavailable';
            $st = $conn->prepare("UPDATE Appointment_slot SET status=? WHERE slot_id=?");
            $st->bind_param('si', $bookedStatus, $slotId);
            if (!$st->execute()) {
                throw new RuntimeException('E_UPDATE_FAIL: ' . $conn->error);
            }
            $st->close();



            $conn->commit();

            // important: 303 after write (prevents form resubmission)
            header('Location: ' . $next, true, 303);
            exit;

        } catch (Throwable $e) {
            $conn->rollback();
            $present = self::presentForSlot($conn, $slotId);

            // already taken -> friendly flash + auto-redirect
            if ($e instanceof RuntimeException && $e->getMessage() === 'E_ALREADY_BOOKED') {
                return $present + [
                    'flash' => 'This slot has been booked.',
                    'flash_type' => 'warn',
                    'redirect_to' => $next,
                    'redirect_after' => 500, // ms
                ];
            }

            // generic fallback
            return $present + ['error' => 'Could not book this slot. Please pick another one.'];
        }
    }

    /** Disallow external redirects */
    private static function isExternal(string $url): bool
    {
        $p = @parse_url($url);
        // external if scheme or host present; allow only same-origin relative
        return isset($p['scheme']) || isset($p['host']);
    }
    private static function presentForSlot(mysqli $conn, int $slotId): array {
    $clinic = $doctor = $when = null;

    $sql = "SELECT
                s.slot_id, s.start_time, s.end_time, s.status,
                d.doctor_id, d.office_id, d.doctor_name AS doc_name, d.degree,
                d.specialty_id, ms.name AS specialty
            FROM Appointment_slot s
            JOIN Doctor d                  ON d.doctor_id = s.doctor_id
            LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id
            WHERE s.slot_id = ?
            LIMIT 1";
    $st = $conn->prepare($sql);
    $st->bind_param('i', $slotId);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();

    if ($row) {
        // Office
        $officeId = (int)$row['office_id'];
        $clinic = Office::findOne($conn, $officeId) ?: ['office_id' => $officeId];

        // Doctor
        $name = (string)$row['doc_name'];
        $ini  = '';
        foreach (preg_split('/\s+/u', trim($name)) ?: [] as $p) { $ini .= mb_substr($p, 0, 1); }
        $ini = mb_strtoupper(preg_replace('/[^A-ZÀ-Ỵ]/iu', '', $ini)) ?: 'DR';

        $doctor = [
            'doctor_id' => (int)$row['doctor_id'],
            'name'      => $name,
            'degree'    => (string)($row['degree'] ?? ''),
            'specialty' => (string)($row['specialty'] ?? ''),
            'initials'  => $ini,
        ];

        // When
        $dtStart = new DateTimeImmutable($row['start_time']);
        $dtEnd   = new DateTimeImmutable($row['end_time']);
        $when = [
            'date_iso'   => $dtStart->format('Y-m-d'),
            'date_label' => $dtStart->format('l, d M Y'),
            'time_label' => $dtStart->format('H:i') . ' – ' . $dtEnd->format('H:i'),
        ];
    }

    return [
        'clinic'  => $clinic,
        'doctor'  => $doctor,
        'when'    => $when,
        'slot_id' => $slotId ?: null,
    ];
}
}