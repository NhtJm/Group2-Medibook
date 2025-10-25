<?php
require_once __DIR__ . '/../db.php';

class AppointmentModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::get_instance();
    }

    /* ===================== APPOINTMENT MODEL ===================== */

    public function addAppointment($patient_id, $slot_id, $status = 'booked') {
        // Check for patient existence
        $checkPatient = $this->conn->prepare("SELECT patient_id FROM Patient WHERE patient_id = ?");
        $checkPatient->bind_param("i", $patient_id);
        $checkPatient->execute();
        if ($checkPatient->get_result()->num_rows === 0) {
            return ['success' => false, 'message' => 'Patient not found'];
        }

        // Check for slot existence
        $checkSlot = $this->conn->prepare("SELECT slot_id, status FROM Appointment_slot WHERE slot_id = ?"); // CHANGE HERE
        $checkSlot->bind_param("i", $slot_id);
        $checkSlot->execute();
        $slot = $checkSlot->get_result()->fetch_assoc();
        if (!$slot) {
            return ['status' => 'fail', 'message' => 'Slot not found'];
        }

        // Check for existing booking
        $checkBooked = $this->conn->prepare("SELECT appointment_id FROM Appointment WHERE slot_id = ?");
        $checkBooked->bind_param("i", $slot_id);
        $checkBooked->execute();
        if ($checkBooked->get_result()->num_rows > 0) {
            return ['status' => 'fail', 'message' => 'Slot already booked'];
        }

        $status_array = ['booked','canceled','completed','no-show'];
        if (!in_array($status, $status_array)) {
            return ['status' => 'fail', 'message' => 'Invalid status value'];
        }

        // Check for overlapping appointments
        $checkOverlap = $this->conn->prepare("
            SELECT a.appointment_id
            FROM Appointment a
            JOIN Appointment_slot s ON a.slot_id = s.slot_id  -- CHANGE HERE
            WHERE a.patient_id = ?
              AND s.start_time = (SELECT start_time FROM Appointment_slot WHERE slot_id = ?)  -- CHANGE HERE
        ");
        $checkOverlap->bind_param("ii", $patient_id, $slot_id);
        $checkOverlap->execute();
        if ($checkOverlap->get_result()->num_rows > 0) {
            return ['status' => 'fail', 'message' => 'Patient already has an overlapping appointment'];
        }

        // Add new appointment
        $stmt = $this->conn->prepare("
            INSERT INTO Appointment (patient_id, slot_id, status)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $patient_id, $slot_id, $status);
        $success = $stmt->execute();

        return $stmt->affected_rows > 0
            ? ['status' => 'success', 'appointment_id' => $stmt->insert_id]
            : ['status' => 'fail', 'message' => 'Error booking appointment'];
    }

    public function getAllAppointments() {
        $query = "
            SELECT 
                a.appointment_id,
                a.status,
                p.patient_id,
                u.full_name AS patient_name,  -- CHANGE HERE
                s.slot_id, s.start_time, s.end_time,  -- CHANGE HERE
                d.doctor_id,
                o.office_id, o.name AS office_name
            FROM Appointment a
            JOIN Patient p ON a.patient_id = p.patient_id
            JOIN Users u ON p.user_id = u.user_id
            JOIN Appointment_slot s ON a.slot_id = s.slot_id  -- CHANGE HERE
            JOIN Doctor d ON s.doctor_id = d.doctor_id
            JOIN Office o ON s.office_id = o.office_id
            WHERE a.status != 'canceled'  -- CHANGE HERE
            ORDER BY s.start_time DESC  -- CHANGE HERE
        ";
        return $this->conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public function getAppointmentById($appointment_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                a.appointment_id, a.status,
                s.start_time, s.end_time,  -- CHANGE HERE
                u.full_name AS patient_name,  -- CHANGE HERE
                o.name AS office_name
            FROM Appointment a
            JOIN Patient p ON a.patient_id = p.patient_id
            JOIN Users u ON p.user_id = u.user_id
            JOIN Appointment_slot s ON a.slot_id = s.slot_id  -- CHANGE HERE
            JOIN Doctor d ON s.doctor_id = d.doctor_id
            JOIN Office o ON s.office_id = o.office_id
            WHERE a.appointment_id = ?
        ");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAppointmentsByPatient($patient_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                a.appointment_id, a.status,
                s.start_time, s.end_time,  -- CHANGE HERE
                o.name AS office_name
            FROM Appointment a
            JOIN Appointment_slot s ON a.slot_id = s.slot_id  -- CHANGE HERE
            JOIN Doctor d ON s.doctor_id = d.doctor_id
            JOIN Office o ON s.office_id = o.office_id
            WHERE a.patient_id = ? AND a.status != 'canceled'  -- CHANGE HERE
            ORDER BY s.start_time  -- CHANGE HERE
        ");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAppointmentsByDoctor($doctor_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                a.appointment_id, a.status,
                s.start_time, s.end_time,  -- CHANGE HERE
                u.full_name AS patient_name,  -- CHANGE HERE
                o.name AS office_name
            FROM Appointment a
            JOIN Patient p ON a.patient_id = p.patient_id
            JOIN Users u ON p.user_id = u.user_id
            JOIN Appointment_slot s ON a.slot_id = s.slot_id  -- CHANGE HERE
            JOIN Office o ON s.office_id = o.office_id
            WHERE s.doctor_id = ? AND a.status != 'canceled'  -- CHANGE HERE
            ORDER BY s.start_time  -- CHANGE HERE
        ");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updateAppointmentStatus($appointment_id, $status) {
        // Check valid status
        $allowed = ['booked', 'completed', 'canceled', 'no-show']; // CHANGE HERE
        if (!in_array($status, $allowed)) {
            return ['status' => 'fail', 'message' => 'Status is not valid'];
        }

        // Check appointment existence
        $current = $this->getAppointmentById($appointment_id);
        if (!$current) {
            return ['status' => 'fail', 'message' => 'Appointment not found'];
        }

        // Check if already completed
        if ($current['status'] === 'completed') {
            return ['status' => 'fail', 'message' => 'Cannot change completed appointment'];
        }

        // Update status
        $stmt = $this->conn->prepare("
            UPDATE Appointment
            SET status = ?
            WHERE appointment_id = ?
        ");
        $stmt->bind_param("si", $status, $appointment_id);
        $success = $stmt->execute();

        // If canceled, free up the slot
        if ($success && $status === 'canceled') { // CHANGE HERE
            $this->conn->query("
                UPDATE Appointment_slot s  -- CHANGE HERE
                JOIN Appointment a ON s.slot_id = a.slot_id
                SET s.status = 'available'
                WHERE a.appointment_id = $appointment_id
            ");
        }

        return $stmt->affected_rows > 0
            ? ['status' => 'success', 'appointment_id' => $appointment_id]
            : ['status' => 'fail', 'message' => 'No changes made'];
    }

    public function deleteAppointment($appointment_id) {
        // Check appointment existence
        $appointment = $this->getAppointmentById($appointment_id);
        if (!$appointment) {
            return ['status' => 'fail', 'message' => 'Appointment not found'];
        }

        // Do not delete if completed
        if ($appointment['status'] === 'completed') {
            return ['status' => 'fail', 'message' => 'Cannot delete completed appointment'];
        }

        // Perform delete
        $stmt = $this->conn->prepare("DELETE FROM Appointment WHERE appointment_id = ?");
        $stmt->bind_param("i", $appointment_id);
        $success = $stmt->execute();

        // If deleted, free up the slot
        if ($success) {
            $this->conn->query("
                UPDATE Appointment_slot s  -- CHANGE HERE
                JOIN Appointment a ON s.slot_id = a.slot_id
                SET s.status = 'available'
                WHERE a.appointment_id = $appointment_id
            ");
        }
        return $stmt->affected_rows > 0
            ? ['status' => 'success', 'message' => 'Appointment deleted']
            : ['status' => 'fail', 'message' => 'Error deleting appointment'];
    }
}
?>
