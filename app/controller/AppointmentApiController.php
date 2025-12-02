<?php
// app/controller/AppointmentApiController.php
require_once __DIR__ . '/../db.php';

class AppointmentApiController
{
    public static function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                throw new Exception('POST only');
            }

            $user = current_user();
            if (!$user) throw new Exception('Not authenticated');

            $conn = Database::get_instance();
            if (!$conn) throw new Exception('Database error');

            // Récupérer patient_id
            $uid = (int)($user['user_id'] ?? $user['id'] ?? 0);
            $patientId = self::getPatientId($conn, $uid);
            if (!$patientId) throw new Exception('No patient profile');

            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $action = $input['action'] ?? '';
            $appointmentId = (int)($input['appointment_id'] ?? 0);

            if ($appointmentId <= 0) throw new Exception('Invalid appointment ID');

            // Vérifier que le rdv appartient au patient
            $appt = self::getAppointment($conn, $appointmentId, $patientId);
            if (!$appt) throw new Exception('Appointment not found');

            switch ($action) {
                case 'cancel':
                    self::cancel($conn, $appointmentId, $appt['slot_id']);
                    break;
                case 'get_slots':
                    self::getSlots($conn, (int)$appt['doctor_id']);
                    break;
                case 'reschedule':
                    self::reschedule($conn, $appointmentId, $appt['slot_id'], $appt['doctor_id'], (int)($input['new_slot_id'] ?? 0));
                    break;
                default:
                    throw new Exception('Invalid action');
            }
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    private static function getPatientId(mysqli $conn, int $uid): int
    {
        $st = $conn->prepare("SELECT patient_id FROM Patient WHERE user_id = ? LIMIT 1");
        $st->bind_param('i', $uid);
        $st->execute();
        $result = $st->get_result()->fetch_assoc();
        $st->close();
        return (int)($result['patient_id'] ?? 0);
    }

    private static function getAppointment(mysqli $conn, int $appointmentId, int $patientId): ?array
    {
        $st = $conn->prepare("
            SELECT a.appointment_id, a.slot_id, a.status, s.doctor_id 
            FROM Appointment a 
            JOIN Appointment_slot s ON s.slot_id = a.slot_id 
            WHERE a.appointment_id = ? AND a.patient_id = ?
        ");
        $st->bind_param('ii', $appointmentId, $patientId);
        $st->execute();
        $result = $st->get_result()->fetch_assoc();
        $st->close();
        return $result ?: null;
    }

    private static function cancel(mysqli $conn, int $appointmentId, int $slotId): void
    {
        $conn->begin_transaction();
        try {
            $conn->query("UPDATE Appointment SET status = 'canceled' WHERE appointment_id = $appointmentId");
            $conn->query("UPDATE Appointment_slot SET status = 'available' WHERE slot_id = $slotId");
            $conn->commit();
            echo json_encode(['ok' => true, 'msg' => 'Appointment cancelled']);
        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception('Failed to cancel');
        }
    }

    private static function getSlots(mysqli $conn, int $doctorId): void
    {
        $st = $conn->prepare("
            SELECT slot_id, start_time FROM Appointment_slot 
            WHERE doctor_id = ? AND status = 'available' AND start_time > NOW()
            ORDER BY start_time LIMIT 20
        ");
        $st->bind_param('i', $doctorId);
        $st->execute();
        $result = $st->get_result();
        
        $slots = [];
        while ($row = $result->fetch_assoc()) {
            $dt = new DateTimeImmutable($row['start_time']);
            $slots[] = [
                'slot_id' => (int)$row['slot_id'],
                'date_label' => $dt->format('D, M j'),
                'time_label' => $dt->format('g:i A'),
            ];
        }
        $st->close();
        echo json_encode(['ok' => true, 'slots' => $slots]);
    }

    private static function reschedule(mysqli $conn, int $appointmentId, int $oldSlotId, int $doctorId, int $newSlotId): void
    {
        if ($newSlotId <= 0) throw new Exception('Invalid slot ID');

        // Vérifier disponibilité
        $st = $conn->prepare("SELECT 1 FROM Appointment_slot WHERE slot_id = ? AND doctor_id = ? AND status = 'available' AND start_time > NOW()");
        $st->bind_param('ii', $newSlotId, $doctorId);
        $st->execute();
        if (!$st->get_result()->fetch_assoc()) {
            $st->close();
            throw new Exception('Slot not available');
        }
        $st->close();

        $conn->begin_transaction();
        try {
            $conn->query("UPDATE Appointment_slot SET status = 'available' WHERE slot_id = $oldSlotId");
            $conn->query("UPDATE Appointment_slot SET status = 'unavailable' WHERE slot_id = $newSlotId");
            $conn->query("UPDATE Appointment SET slot_id = $newSlotId, status = 'booked' WHERE appointment_id = $appointmentId");
            $conn->commit();
            echo json_encode(['ok' => true, 'msg' => 'Appointment rescheduled']);
        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception('Failed to reschedule');
        }
    }
}
