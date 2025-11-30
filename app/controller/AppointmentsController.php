<?php
// app/controller/AppointmentsController.php
require_once __DIR__ . '/../db.php';

class AppointmentsController
{
    public static function index(): array
    {
        // must be logged in (router already guards this)
        $u = current_user();
        if (!$u) {
            header('Location: ' . BASE_URL . 'index.php?page=login&next=' . rawurlencode(BASE_URL . 'index.php?page=appointments'));
            exit;
        }

        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        // find patient_id for this user
        $uid = (int)($u['user_id'] ?? $u['id'] ?? 0);
        $patientId = 0;
        if ($st = $conn->prepare("SELECT patient_id FROM Patient WHERE user_id=? LIMIT 1")) {
            $st->bind_param('i', $uid);
            $st->execute();
            $st->bind_result($pid);
            if ($st->fetch()) $patientId = (int)$pid;
            $st->close();
        }
        if ($patientId <= 0) {
            // No patient profile yet → empty state
            return [
                'stats' => ['upcoming' => 0, 'completed' => 0],
                'upcoming' => [],
                'past' => [],
            ];
        }

        // helper to map a row for the view
        $map = function(array $r): array {
            // initials
            $ini = '';
            foreach (preg_split('/\s+/u', trim((string)$r['doctor_name'])) ?: [] as $p) {
                $ini .= mb_substr($p, 0, 1);
            }
            $ini = mb_strtoupper(preg_replace('/[^A-ZÀ-Ỵ]/iu', '', $ini)) ?: 'DR';

            $start = new DateTimeImmutable($r['start_time']);
            $end   = new DateTimeImmutable($r['end_time']);

            return [
                'appointment_id' => (int)$r['appointment_id'],
                'slot_id'        => (int)$r['slot_id'],
                'status'         => (string)$r['status'],
                'doctor_id'      => (int)$r['doctor_id'],
                'doctor_name'    => (string)$r['doctor_name'],
                'specialty'      => (string)($r['specialty'] ?? ''),
                'initials'       => $ini,
                'date_label'     => $start->format('d-m-Y'),
                'time_label'     => $start->format('g:i a'),
                'office_name'    => (string)($r['office_name'] ?? ''),
                'start_time'     => $r['start_time'],
                'end_time'       => $r['end_time'],
            ];
        };

        // UPCOMING (start_time >= now, status booked-ish)
        $upcoming = [];
        $sql = "
          SELECT a.appointment_id, a.status, a.slot_id,
                 s.start_time, s.end_time,
                 d.doctor_id, d.doctor_name, d.specialty_id,
                 ms.name AS specialty,
                 o.name  AS office_name
            FROM Appointment a
            JOIN Appointment_slot s ON s.slot_id = a.slot_id
            JOIN Doctor d           ON d.doctor_id = s.doctor_id
            LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id
            LEFT JOIN Office o             ON o.office_id = d.office_id
           WHERE a.patient_id = ?
             AND s.start_time >= NOW()
             AND a.status IN ('booked','rescheduled','confirmed')
           ORDER BY s.start_time ASC";
        if ($st = $conn->prepare($sql)) {
            $st->bind_param('i', $patientId);
            $st->execute();
            $rs = $st->get_result();
            while ($row = $rs->fetch_assoc()) $upcoming[] = $map($row);
            $st->close();
        }

        // PAST (start_time < now)
        $past = [];
        $sql = "
          SELECT a.appointment_id, a.status, a.slot_id,
                 s.start_time, s.end_time,
                 d.doctor_id, d.doctor_name, d.specialty_id,
                 ms.name AS specialty,
                 o.name  AS office_name
            FROM Appointment a
            JOIN Appointment_slot s ON s.slot_id = a.slot_id
            JOIN Doctor d           ON d.doctor_id = s.doctor_id
            LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id
            LEFT JOIN Office o             ON o.office_id = d.office_id
           WHERE a.patient_id = ?
             AND s.start_time < NOW()
           ORDER BY s.start_time DESC";
        if ($st = $conn->prepare($sql)) {
            $st->bind_param('i', $patientId);
            $st->execute();
            $rs = $st->get_result();
            while ($row = $rs->fetch_assoc()) $past[] = $map($row);
            $st->close();
        }

        // stats
        $upcomingCount = count($upcoming);

        $completedCount = 0;
        if ($st = $conn->prepare("
            SELECT COUNT(*) FROM Appointment a
            JOIN Appointment_slot s ON s.slot_id = a.slot_id
            WHERE a.patient_id=? AND a.status='completed'")) {
            $st->bind_param('i', $patientId);
            $st->execute();
            $st->bind_result($c);
            if ($st->fetch()) $completedCount = (int)$c;
            $st->close();
        }

        return [
            'stats'    => ['upcoming' => $upcomingCount, 'completed' => $completedCount],
            'upcoming' => $upcoming,
            'past'     => $past,
        ];
    }
}