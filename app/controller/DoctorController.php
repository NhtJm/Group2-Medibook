<?php
// app/controller/DoctorController.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Office.php';

class DoctorController
{
    /** List all doctors in a clinic */
    public static function listByClinic(): array
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            if (class_exists('Database') && method_exists('Database', 'get_instance')) {
                $conn = Database::get_instance();
            } else {
                throw new RuntimeException('DB connection not available');
            }
        }

        $officeId = max(0, (int) ($_GET['clinic'] ?? 0));
        if ($officeId <= 0)
            return ['clinic' => null, 'doctors' => []];

        $clinic = Office::findOne($conn, $officeId);
        if ($clinic) {
            $slots = Office::nextSlots($conn, $officeId, 1);
            $clinic['is_open'] = Office::hasSlotToday($slots);
            $clinic['hours_label'] = $clinic['is_open'] ? 'Slots today' : null;
        }
        $doctors = $clinic ? Office::doctorsWithNext($conn, $officeId) : [];
        return ['clinic' => $clinic, 'doctors' => $doctors];
    }

    /** Calendar + slots (user-facing doctor visit page) */
    public static function visit(): array
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            if (class_exists('Database') && method_exists('Database', 'get_instance')) {
                $conn = Database::get_instance();
            } else {
                return ['clinic' => null, 'doctor' => null, 'month' => null, 'counts' => [], 'selected' => null, 'slots' => []];
            }
        }

        $docId = (int) ($_GET['doc'] ?? 0);
        $officeId = (int) ($_GET['clinic'] ?? 0);
        if ($docId <= 0) {
            return ['clinic' => null, 'doctor' => null, 'month' => null, 'counts' => [], 'selected' => null, 'slots' => []];
        }

        // Doctor record (trust DB for office_id)
        $sql = "SELECT d.doctor_id, d.office_id, d.doctor_name AS name,
                   d.degree, d.graduate, d.specialty_id, ms.name AS specialty
            FROM Doctor d
            LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id
            WHERE d.doctor_id = ?
            LIMIT 1";
        $st = $conn->prepare($sql);
        $st->bind_param('i', $docId);
        $st->execute();
        $doctor = $st->get_result()->fetch_assoc();
        $st->close();
        if (!$doctor) {
            return ['clinic' => null, 'doctor' => null, 'month' => null, 'counts' => [], 'selected' => null, 'slots' => []];
        }

        // Normalize clinic to the doctor’s real office
        $officeId = (int) $doctor['office_id'];
        $clinic = Office::findOne($conn, $officeId);
        $slots = Office::nextSlots($conn, $officeId, 1);
        $clinic['is_open'] = Office::hasSlotToday($slots);
        $clinic['hours_label'] = $clinic['is_open'] ? 'Slots today' : null;
        // Month meta
        $ymParam = $_GET['ym'] ?? '';
        $first = new DateTimeImmutable(
            preg_match('/^\d{4}-\d{2}$/', $ymParam) ? ($ymParam . '-01') : date('Y-m-01')
        );
        $next = $first->modify('first day of next month');
        $prev = $first->modify('first day of previous month');

        $month = [
            'ym' => $first->format('Y-m'),
            'name' => $first->format('F Y'),
            'firstDow' => (int) $first->format('w'),       // 0..6 (Sun..Sat)
            'daysInMonth' => (int) $first->format('t'),
            'prev' => $prev->format('Y-m'),
            'next' => $next->format('Y-m'),
        ];

        // Count available slots per day in month
        $counts = [];
        // If you have duration_min column:
        $sqlC = "SELECT DATE(start_time) AS d, COUNT(*) AS c
         FROM Appointment_slot
         WHERE doctor_id = ?
           AND status = 'available'
           AND start_time >= ?
           AND start_time <  ?
           AND (
                DATE(start_time) > CURDATE()
             OR DATE_ADD(start_time, INTERVAL 30 MINUTE) > NOW()
           )
         GROUP BY DATE(start_time)";
        $st = $conn->prepare($sqlC);
        $from = $first->format('Y-m-d 00:00:00');
        $to = $next->format('Y-m-d 00:00:00');
        $st->bind_param('iss', $docId, $from, $to);
        $st->execute();
        $rs = $st->get_result();
        while ($r = $rs->fetch_assoc())
            $counts[$r['d']] = (int) $r['c'];
        $st->close();

        // Selected date & slots (optional)
        $selected = null;
        $slots = [];
        $dateSel = $_GET['date'] ?? null;
        if ($dateSel && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateSel)) {
            if ($dateSel >= $first->format('Y-m-01') && $dateSel < $next->format('Y-m-01')) {
                $selected = $dateSel;
                // Only future (not-ended) slots for that date:
                $sqlS = "SELECT slot_id, start_time
         FROM Appointment_slot
         WHERE doctor_id = ?
           AND status = 'available'
           AND DATE(start_time) = ?
           AND DATE_ADD(start_time, INTERVAL 30 MINUTE) > NOW()
         ORDER BY start_time ASC";
                $st = $conn->prepare($sqlS);
                $st->bind_param('is', $docId, $selected);
                $st->execute();
                $rs = $st->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $slots[] = [
                        'slot_id' => (int) $row['slot_id'],
                        'start_time' => (string) $row['start_time'],
                    ];
                }
                $st->close();
            }
        }

        return [
            'clinic' => $clinic,
            'doctor' => $doctor,
            'month' => $month,
            'counts' => $counts,
            'selected' => $selected,
            'slots' => $slots,
            // server "today" so the view/JS can reliably grey past days
            'today' => (new DateTimeImmutable('today'))->format('Y-m-d'),
        ];
    }
    public static function calendarData(): array
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            if (class_exists('Database') && method_exists('Database', 'get_instance')) {
                $conn = Database::get_instance();
            } else {
                return ['ok' => false, 'error' => 'db'];
            }
        }

        $docId = max(0, (int) ($_GET['doc'] ?? 0));
        if ($docId <= 0)
            return ['ok' => false, 'error' => 'bad doc'];

        // YM meta
        $ym = preg_match('/^\d{4}-\d{2}$/', $_GET['ym'] ?? '') ? $_GET['ym'] : date('Y-m');
        $first = new DateTimeImmutable($ym . '-01 00:00:00');
        $next = $first->modify('first day of next month');
        $prev = $first->modify('first day of previous month');

        // Counts per day in [first, next)
        $counts = [];
        // If you have duration_min column:
        $sqlC = "SELECT DATE(start_time) AS d, COUNT(*) AS c
         FROM Appointment_slot
         WHERE doctor_id = ?
           AND status = 'available'
           AND start_time >= ?
           AND start_time <  ?
           AND (
                DATE(start_time) > CURDATE()
             OR DATE_ADD(start_time, INTERVAL 30 MINUTE) > NOW()
           )
         GROUP BY DATE(start_time)";
        $st = $conn->prepare($sqlC);
        $from = $first->format('Y-m-d H:i:s');
        $to = $next->format('Y-m-d H:i:s');
        $st->bind_param('iss', $docId, $from, $to);
        $st->execute();
        $rs = $st->get_result();
        while ($r = $rs->fetch_assoc())
            $counts[$r['d']] = (int) $r['c'];
        $st->close();

        // Slots for selected day (optional)
        $sel = null;
        $slots = [];
        $dateSel = $_GET['date'] ?? null;
        if (
            $dateSel && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateSel)
            && $dateSel >= $first->format('Y-m-01') && $dateSel < $next->format('Y-m-01')
        ) {
            $sel = $dateSel;
            // Only future (not-ended) slots for that date:
            $sqlS = "SELECT slot_id, start_time
         FROM Appointment_slot
         WHERE doctor_id = ?
           AND status = 'available'
           AND DATE(start_time) = ?
           AND DATE_ADD(start_time, INTERVAL 30 MINUTE) > NOW()
         ORDER BY start_time ASC";
            $st = $conn->prepare($sqlS);
            $st->bind_param('is', $docId, $sel);
            $st->execute();
            $rs = $st->get_result();
            while ($row = $rs->fetch_assoc()) {
                $slots[] = [
                    'slot_id' => (int) $row['slot_id'],     // <— exact key
                    'start_time' => (string) $row['start_time']
                ];
            }
            $st->close();
        }

        $out = [
            'ok' => true,
            'month' => [
                'ym' => $first->format('Y-m'),
                'name' => $first->format('F Y'),
                'firstDow' => (int) $first->format('w'),
                'daysInMonth' => (int) $first->format('t'),
                'prev' => $prev->format('Y-m'),
                'next' => $next->format('Y-m'),
            ],
            'counts' => $counts,
            'selected' => $sel,
            'slots' => $slots,
            'today' => (new DateTimeImmutable('today'))->format('Y-m-d'),
        ];

        // If this is actually being called via ?ajax=1, emit JSON here with no-cache headers
        if (!empty($_GET['ajax'])) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo json_encode($out);
            exit;
        }

        return $out;
    }
}