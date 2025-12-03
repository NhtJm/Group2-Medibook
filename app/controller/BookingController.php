<?php
// app/controller/BookingController.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../models/Appointment.php';
require_once __DIR__ . '/../models/AppointmentSlot.php';

class BookingController
{
    /**
     * Confirm a booking for the logged-in user
     */
    public static function confirm(): array
    {
        // Must be logged in
        require_auth_or_redirect();

        $slotId = (int) ($_POST['slot'] ?? 0);
        
        if ($slotId <= 0) {
            return ['error' => 'Missing time slot.'];
        }

        // Get current user
        $user = current_user();
        $userId = (int) ($user['user_id'] ?? 0);
        
        if ($userId <= 0) {
            header('Location: ' . BASE_URL . 'index.php?page=login');
            exit;
        }

        // Get or create patient profile via Model
        $patientId = Patient::getOrCreate($userId);
        
        if ($patientId <= 0) {
            return ['error' => 'Could not create patient profile.'];
        }

        // Validate slot is available via Model
        $slot = AppointmentSlot::findAvailable($slotId);
        
        if (!$slot) {
            return ['error' => 'This slot is no longer available.'];
        }

        // Create the appointment via Model
        $appointmentId = Appointment::create($patientId, $slotId);
        
        if ($appointmentId <= 0) {
            return ['error' => 'Could not book this slot. Please pick another one.'];
        }

        // Success - redirect to appointments page
        header('Location: ' . BASE_URL . 'index.php?page=appointments&ok=1');
        exit;
    }
}
