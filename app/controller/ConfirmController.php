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
                return ['clinic'=>null,'doctor'=>null,'when'=>null,'slot_id'=>null];
            }
        }

        $slotId   = (int)($_GET['slot']   ?? 0);
        $docId    = (int)($_GET['doc']    ?? 0);
        $officeId = (int)($_GET['clinic'] ?? 0);
        $dateStr  = trim((string)($_GET['date'] ?? ''));
        $timeStr  = trim((string)($_GET['time'] ?? ''));

        $clinic = null;
        $doctor = null;
        $when   = null;

        // Helper to compute initials
        $mkInitials = static function(string $name): string {
            $parts = preg_split('/\s+/u', trim($name)) ?: [];
            $ini = '';
            foreach ($parts as $p) { $ini .= mb_substr($p, 0, 1); }
            $ini = mb_strtoupper(preg_replace('/[^A-ZÀ-Ỵ]/iu','', $ini));
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
                $officeId = (int)$row['office_id'];
                $clinic   = Office::findOne($conn, $officeId) ?: ['office_id'=>$officeId];

                $doctor = [
                    'doctor_id' => (int)$row['doctor_id'],
                    'name'      => (string)$row['doc_name'],
                    'degree'    => (string)($row['degree'] ?? ''),
                    'specialty' => (string)($row['specialty'] ?? ''),
                    'initials'  => $mkInitials((string)$row['doc_name']),
                ];

                $dtStart = new DateTimeImmutable($row['start_time']);
                $dtEnd   = new DateTimeImmutable($row['end_time']);
                $when = [
                    'date_iso'   => $dtStart->format('Y-m-d'),
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
                $officeId = (int)($row['office_id'] ?: $officeId);
                $clinic   = Office::findOne($conn, $officeId) ?: ['office_id'=>$officeId];

                $doctor = [
                    'doctor_id' => (int)$row['doctor_id'],
                    'name'      => (string)$row['doc_name'],
                    'degree'    => (string)($row['degree'] ?? ''),
                    'specialty' => (string)($row['specialty'] ?? ''),
                    'initials'  => $mkInitials((string)$row['doc_name']),
                ];

                // Build a display-only "when" from query params
                if ($dateStr !== '') {
                    $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateStr) ?: new DateTimeImmutable($dateStr);
                    $when = [
                        'date_iso'   => $date->format('Y-m-d'),
                        'date_label' => $date->format('l, d M Y'),
                        'time_label' => $timeStr, // may be empty
                    ];
                }
            }
        }

        return [
            'clinic'  => $clinic,
            'doctor'  => $doctor,
            'when'    => $when,
            'slot_id' => $slotId ?: null,
        ];
    }
}