<?php
if (session_status() === PHP_SESSION_NONE) session_start();

class OfficeDoctorSlotsApiController
{
    public static function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $u = $_SESSION['user'] ?? null;
        if (!$u) { echo json_encode(['ok'=>false,'err'=>'auth']); return; }

        $role     = $u['role'] ?? '';
        $officeId = (int)($u['profile_id'] ?? 0);

        $raw = file_get_contents('php://input');
        $in  = json_decode($raw ?: '[]', true) ?: [];

        if (!isset($_SESSION['csrf']) || ($in['csrf'] ?? '') !== $_SESSION['csrf']) {
            echo json_encode(['ok'=>false,'err'=>'csrf']); return;
        }

        $action   = $in['action'] ?? '';
        $doctorId = (int)($in['doctor_id'] ?? $_GET['doctor_id'] ?? 0);
        if ($doctorId <= 0) { echo json_encode(['ok'=>false,'err'=>'doctor']); return; }

        require_once __DIR__ . '/../db.php';
        global $conn;
        if (!($conn instanceof mysqli) && class_exists('Database')) $conn = Database::get_instance();

        // Ownership: admin can manage all; office only its own doctors
        if ($role === 'office') {
            $chk = $conn->prepare("SELECT 1 FROM Doctor WHERE doctor_id=? AND office_id=?");
            $chk->bind_param('ii', $doctorId, $officeId);
            $chk->execute();
            $ok = (bool)$chk->get_result()->fetch_row();
            $chk->close();
            if (!$ok) { echo json_encode(['ok'=>false,'err'=>'forbidden']); return; }
        } elseif ($role !== 'admin') {
            echo json_encode(['ok'=>false,'err'=>'forbidden']); return;
        }

        // helpers
        $exists = function(int $doc, string $start) use ($conn): ?int {
            $st=$conn->prepare("SELECT slot_id FROM Appointment_slot WHERE doctor_id=? AND start_time=? LIMIT 1");
            $st->bind_param('is',$doc,$start); $st->execute();
            $id=null; $st->bind_result($id); $st->fetch(); $st->close();
            return $id;
        };

        try {
            switch ($action) {
                case 'list': {
                    $start = $in['start'] ?? ''; // YYYY-MM-DD
                    $end   = $in['end']   ?? '';
                    if (!$start || !$end) { echo json_encode(['ok'=>false,'err'=>'range']); return; }
                    $st = $conn->prepare(
                        "SELECT slot_id, doctor_id, start_time, end_time, status
                           FROM Appointment_slot
                          WHERE doctor_id=? AND DATE(start_time)>=? AND DATE(start_time)<? 
                          ORDER BY start_time"
                    );
                    $st->bind_param('iss', $doctorId, $start, $end);
                    $st->execute();
                    $slots = $st->get_result()->fetch_all(MYSQLI_ASSOC);
                    $st->close();
                    echo json_encode(['ok'=>true,'slots'=>$slots]); return;
                }

                case 'upsert': {
                    $start = $in['start'] ?? '';
                    $end   = $in['end']   ?? '';
                    $status= ($in['status'] ?? 'available') === 'unavailable' ? 'unavailable' : 'available';

                    if (!$start || !$end) { echo json_encode(['ok'=>false,'err'=>'params']); return; }

                    if ($id = $exists($doctorId, $start)) {
                        $st=$conn->prepare("UPDATE Appointment_slot SET end_time=?, status=? WHERE slot_id=? AND doctor_id=?");
                        $st->bind_param('ssii',$end,$status,$id,$doctorId);
                        $st->execute(); $st->close();
                        echo json_encode(['ok'=>true,'slot_id'=>$id,'mode'=>'update']); return;
                    } else {
                        $st=$conn->prepare("INSERT INTO Appointment_slot (doctor_id,start_time,end_time,status) VALUES (?,?,?,?)");
                        $st->bind_param('isss',$doctorId,$start,$end,$status);
                        $st->execute();
                        $id = $st->insert_id; $st->close();
                        echo json_encode(['ok'=>true,'slot_id'=>$id,'mode'=>'insert']); return;
                    }
                }

                case 'toggle': {
                    $slotId = (int)($in['slot_id'] ?? 0);
                    $to     = ($in['status'] ?? '') === 'unavailable' ? 'unavailable' : 'available';
                    if ($slotId<=0) { echo json_encode(['ok'=>false,'err'=>'slot']); return; }
                    $st=$conn->prepare("UPDATE Appointment_slot SET status=? WHERE slot_id=? AND doctor_id=?");
                    $st->bind_param('sii',$to,$slotId,$doctorId);
                    $st->execute(); $aff=$st->affected_rows; $st->close();
                    echo json_encode(['ok'=>$aff>0]); return;
                }

                case 'delete': {
                    $slotId = (int)($in['slot_id'] ?? 0);
                    if ($slotId<=0) { echo json_encode(['ok'=>false,'err'=>'slot']); return; }
                    $st=$conn->prepare("DELETE FROM Appointment_slot WHERE slot_id=? AND doctor_id=?");
                    $st->bind_param('ii',$slotId,$doctorId);
                    $st->execute(); $aff=$st->affected_rows; $st->close();
                    echo json_encode(['ok'=>$aff>0]); return;
                }

                case 'bulk_generate': {
                    $weekStart = $in['weekStart'] ?? '';  // YYYY-MM-DD (Mon)
                    $hFrom     = max(0, min(23, (int)($in['hoursFrom'] ?? 9)));
                    $hTo       = max($hFrom+1, min(24, (int)($in['hoursTo'] ?? 17)));
                    $days      = array_values(array_filter(array_map('intval', $in['days'] ?? []), fn($d)=>$d>=1 && $d<=7));

                    if (!$weekStart || !$days) { echo json_encode(['ok'=>false,'err'=>'params']); return; }

                    $ins = $conn->prepare("INSERT INTO Appointment_slot (doctor_id,start_time,end_time,status) VALUES (?,?,?,?)");
                    $upd = $conn->prepare("UPDATE Appointment_slot SET end_time=?, status=? WHERE slot_id=? AND doctor_id=?");

                    $created = 0; $updated = 0;
                    foreach ($days as $dow) {
                        $d = new DateTime($weekStart); $d->modify('+' . ($dow-1) . ' day');
                        for ($h=$hFrom; $h < $hTo; $h++) {
                            foreach ([0,30] as $m) {
                                $start = $d->format('Y-m-d') . sprintf(' %02d:%02d:00', $h, $m);
                                $end   = $d->format('Y-m-d') . sprintf(' %02d:%02d:00', $h, $m+30 >= 60 ? 0 : $m+30);
                                if ($m===30 && $h+1 <= 23 && $m+30>=60) {
                                    $end = $d->format('Y-m-d') . sprintf(' %02d:%02d:00', $h+1, 0);
                                }
                                if ($id = $exists($doctorId, $start)) {
                                    $upd->bind_param('ssii',$end,$status='available',$id,$doctorId);
                                    $upd->execute(); $updated++;
                                } else {
                                    $ins->bind_param('isss',$doctorId,$start,$end,$status='available');
                                    $ins->execute(); $created++;
                                }
                            }
                        }
                    }
                    $ins->close(); $upd->close();
                    echo json_encode(['ok'=>true,'created'=>$created,'updated'=>$updated]); return;
                }

                default: echo json_encode(['ok'=>false,'err'=>'action']); return;
            }
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false,'err'=>'exception','msg'=>$e->getMessage()]);
        }
    }
}