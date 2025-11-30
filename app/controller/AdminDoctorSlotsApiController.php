<?php
// app/controller/AdminDoctorSlotsApiController.php
require_once __DIR__ . '/../db.php';

class AdminDoctorSlotsApiController
{
    private static function purgeFutureSlots(mysqli $conn, int $doctor_id, string $cutoffHiEx): void
    {
        // Delete anything at/after the exclusive upper bound
        $st = $conn->prepare(
            "DELETE FROM Appointment_slot WHERE doctor_id=? AND start_time >= ?"
        );
        $st->bind_param('is', $doctor_id, $cutoffHiEx);
        $st->execute();
        $st->close();
    }
    public static function index()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (function_exists('ini_set')) {
            @ini_set('display_errors', '0');
        }
        header('Content-Type: application/json; charset=utf-8');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            echo json_encode(['ok' => false, 'msg' => 'POST only']);
            return;
        }

        $in = json_decode(file_get_contents('php://input'), true) ?: [];
        $action = $in['action'] ?? '';
        $doctor_id = (int) ($in['doctor_id'] ?? 0);
        if ($doctor_id <= 0) {
            echo json_encode(['ok' => false, 'msg' => 'Invalid doctor']);
            return;
        }

        $conn = Database::get_instance();

        // === Retention window ===
        // Keep from Monday of last week 00:00 (inclusive)
        // …to Monday of (current week + 3) 00:00 (exclusive).
        $cutoffLoDT = self::weekCutoffLo();
        $cutoffHiDT = self::weekCutoffHiExclusive();
        $cutoffLo = $cutoffLoDT->format('Y-m-d H:i:s');
        $cutoffHiEx = $cutoffHiDT->format('Y-m-d H:i:s');

        // Auto-purge OLD rows below lower cutoff
        self::purgeOldSlots($conn, $doctor_id, $cutoffLo);

        // ALSO purge any future slots at/after the upper cutoff
        self::purgeFutureSlots($conn, $doctor_id, $cutoffHiEx);

        // helpers
        $jok = function ($arr = []) {
            echo json_encode(['ok' => true] + $arr);
        };
        $jerr = function ($m) {
            echo json_encode(['ok' => false, 'msg' => $m]);
        };

        if ($action === 'list') {
            // Inputs come as Y-m-d; clamp into window [cutoffLo, cutoffHiEx)
            $startIn = $in['start'] ?? date('Y-m-d');
            $endIn = $in['end'] ?? date('Y-m-d', strtotime('+7 days'));

            $startDT = new DateTime($startIn . ' 00:00:00');
            $endDT = new DateTime($endIn . ' 00:00:00');

            if ($startDT < $cutoffLoDT)
                $startDT = clone $cutoffLoDT;
            if ($endDT > $cutoffHiDT)
                $endDT = clone $cutoffHiDT;

            // If range collapses (e.g., user asked far future), return empty list
            if ($startDT >= $endDT) {
                return $jok(['slots' => []]);
            }

            $start = $startDT->format('Y-m-d H:i:s');
            $end = $endDT->format('Y-m-d H:i:s');

            $sql = "SELECT slot_id, doctor_id, start_time, end_time, status
                    FROM Appointment_slot
                    WHERE doctor_id=? AND start_time>=? AND start_time<? 
                    ORDER BY start_time";
            $st = $conn->prepare($sql);
            $st->bind_param('iss', $doctor_id, $start, $end);
            $st->execute();
            $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
            $st->close();
            return $jok(['slots' => $rows]);
        }

        if ($action === 'upsert') {
            $start = $in['start'] ?? '';
            $end = $in['end'] ?? '';
            $status = ($in['status'] ?? 'available') === 'unavailable' ? 'unavailable' : 'available';
            if (!$start || !$end)
                return $jerr('Missing times');

            // Enforce window
            if (strtotime($start) < strtotime($cutoffLo)) {
                return $jerr('Slot start is older than allowed retention window');
            }
            if (strtotime($start) >= strtotime($cutoffHiEx)) {
                return $jerr('Slot start exceeds the 2-week future window');
            }

            // unique index recommended: UNIQUE (doctor_id, start_time)
            $sql = "INSERT INTO Appointment_slot(doctor_id,start_time,end_time,status)
                    VALUES(?,?,?,?)
                    ON DUPLICATE KEY UPDATE end_time=VALUES(end_time), status=VALUES(status)";
            $st = $conn->prepare($sql);
            $st->bind_param('isss', $doctor_id, $start, $end, $status);
            $st->execute();
            $id = $conn->insert_id ?: self::getSlotId($conn, $doctor_id, $start);
            $st->close();
            return $jok(['slot_id' => $id]);
        }

        if ($action === 'toggle') {
            $slot_id = (int) ($in['slot_id'] ?? 0);
            $status = ($in['status'] ?? 'available') === 'unavailable' ? 'unavailable' : 'available';
            if ($slot_id <= 0)
                return $jerr('Missing slot_id');
            $st = $conn->prepare("UPDATE Appointment_slot SET status=? WHERE slot_id=? AND doctor_id=?");
            $st->bind_param('sii', $status, $slot_id, $doctor_id);
            $st->execute();
            $st->close();
            return $jok();
        }

        if ($action === 'delete') {
            $slot_id = (int) ($in['slot_id'] ?? 0);
            if ($slot_id <= 0)
                return $jerr('Missing slot_id');
            $st = $conn->prepare("DELETE FROM Appointment_slot WHERE slot_id=? AND doctor_id=?");
            $st->bind_param('ii', $slot_id, $doctor_id);
            $st->execute();
            $st->close();
            return $jok();
        }

        if ($action === 'bulk_generate') {
            $weekStart = $in['weekStart'] ?? date('Y-m-d');
            $from = (int) ($in['hoursFrom'] ?? 8);
            $to = (int) ($in['hoursTo'] ?? 18);
            $days = array_map('intval', $in['days'] ?? [1, 2, 3, 4, 5]); // 1=Mon..7=Sun
            $startW = new DateTime($weekStart);

            $ins = $conn->prepare(
                "INSERT IGNORE INTO Appointment_slot(doctor_id,start_time,end_time,status)
                 VALUES(?,?,?,'available')"
            );

            foreach ($days as $dow) {
                $d = clone $startW;
                $d->modify("+" . ($dow - 1) . " days");
                for ($h = $from; $h < $to; $h++) {
                    for ($m = 0; $m < 60; $m += 30) {
                        $s = (clone $d)->setTime($h, $m, 0);
                        // Enforce window: skip outside [cutoffLoDT, cutoffHiDT)
                        if ($s < $cutoffLoDT)
                            continue;
                        if ($s >= $cutoffHiDT)
                            continue;

                        $e = (clone $s)->modify('+30 minutes');
                        $ss = $s->format('Y-m-d H:i:s');
                        $ee = $e->format('Y-m-d H:i:s');
                        $ins->bind_param('iss', $doctor_id, $ss, $ee);
                        $ins->execute();
                    }
                }
            }
            $ins->close();
            return $jok();
        }

        return $jerr('Unknown action');
    }

    /**
     * Lower bound: Monday of LAST week at 00:00:00 (inclusive).
     * Example: if today is Wed 2025-11-26, returns Mon 2025-11-17 00:00:00.
     */
    private static function weekCutoffLo(): DateTime
    {
        $now = new DateTime('now');
        $mondayThisWeek = (clone $now)->modify('Monday this week')->setTime(0, 0, 0);
        return (clone $mondayThisWeek)->modify('-7 days');
    }

    /**
     * Upper bound (exclusive): Monday of (CURRENT week + 3) at 00:00:00.
     * This makes “current week + 2 full weeks” allowed for saving.
     */
    private static function weekCutoffHiExclusive(): DateTime
    {
        $now = new DateTime('now');
        $mondayThisWeek = (clone $now)->modify('Monday this week')->setTime(0, 0, 0);
        // +3 weeks Monday 00:00 is the exclusive ceiling
        return (clone $mondayThisWeek)->modify('+3 weeks');
    }

    private static function purgeOldSlots(mysqli $conn, int $doctor_id, string $cutoffLo): void
    {
        // Delete anything older than the lower cutoff for this doctor
        $st = $conn->prepare("DELETE FROM Appointment_slot WHERE doctor_id=? AND start_time < ?");
        $st->bind_param('is', $doctor_id, $cutoffLo);
        $st->execute();
        $st->close();
    }

    private static function getSlotId(mysqli $conn, int $doctor_id, string $start)
    {
        $st = $conn->prepare("SELECT slot_id FROM Appointment_slot WHERE doctor_id=? AND start_time=?");
        $st->bind_param('is', $doctor_id, $start);
        $st->execute();
        $id = ($st->get_result()->fetch_assoc()['slot_id'] ?? 0);
        $st->close();
        return (int) $id;
    }
}