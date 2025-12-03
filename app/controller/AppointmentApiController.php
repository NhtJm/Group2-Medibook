<?php
// app/controller/AppointmentApiController.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../models/Appointment.php';
require_once __DIR__ . '/../models/AppointmentSlot.php';

class AppointmentApiController
{
    /**
     * Handle API requests for appointments
     */
    public static function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Validate request method
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                throw new Exception('POST only');
            }

            // Validate authentication
            $user = current_user();
            if (!$user) {
                throw new Exception('Not authenticated');
            }

            // Get patient ID via Model
            $userId = (int) ($user['user_id'] ?? $user['id'] ?? 0);
            $patientId = Patient::getPatientId($userId);
            
            if (!$patientId) {
                throw new Exception('No patient profile');
            }

            // Parse input
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $action = $input['action'] ?? '';
            $appointmentId = (int) ($input['appointment_id'] ?? 0);

            if ($appointmentId <= 0) {
                throw new Exception('Invalid appointment ID');
            }

            // Verify appointment belongs to patient via Model
            $appointment = Appointment::findByIdForPatient($appointmentId, $patientId);
            
            if (!$appointment) {
                throw new Exception('Appointment not found');
            }

            // Handle action
            switch ($action) {
                case 'cancel':
                    self::handleCancel($appointmentId);
                    break;
                    
                case 'get_slots':
                    self::handleGetSlots((int) $appointment['doctor_id']);
                    break;
                    
                case 'reschedule':
                    self::handleReschedule(
                        $appointmentId,
                        (int) $appointment['slot_id'],
                        (int) $appointment['doctor_id'],
                        (int) ($input['new_slot_id'] ?? 0)
                    );
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Handle appointment cancellation
     */
    private static function handleCancel(int $appointmentId): void
    {
        $success = Appointment::cancel($appointmentId);
        
        if ($success) {
            echo json_encode(['ok' => true, 'msg' => 'Appointment cancelled']);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Failed to cancel']);
        }
    }

    /**
     * Handle get available slots for reschedule
     */
    private static function handleGetSlots(int $doctorId): void
    {
        $slots = AppointmentSlot::getAvailableForDoctor($doctorId, 20);
        echo json_encode(['ok' => true, 'slots' => $slots]);
    }

    /**
     * Handle appointment reschedule
     */
    private static function handleReschedule(int $appointmentId, int $oldSlotId, int $doctorId, int $newSlotId): void
    {
        if ($newSlotId <= 0) {
            echo json_encode(['ok' => false, 'msg' => 'Invalid slot ID']);
            return;
        }

        $success = Appointment::reschedule($appointmentId, $oldSlotId, $newSlotId, $doctorId);
        
        if ($success) {
            echo json_encode(['ok' => true, 'msg' => 'Appointment rescheduled']);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Failed to reschedule. Slot may no longer be available.']);
        }
    }
}
