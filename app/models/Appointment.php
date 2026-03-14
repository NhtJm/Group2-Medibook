<?php
// app/models/Appointment.php

class Appointment
{
    /**
     * Find appointment by ID for a specific patient
     */
    public static function findByIdForPatient(int $appointmentId, int $patientId): ?array
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT a.appointment_id, a.slot_id, a.status, s.doctor_id FROM Appointment a JOIN Appointment_slot s ON s.slot_id = a.slot_id WHERE a.appointment_id = ? AND a.patient_id = ?");
        $stmt->bind_param('ii', $appointmentId, $patientId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    /**
     * Find appointment by ID for a specific office (clinic)
     * Verifies the appointment belongs to a doctor of this office
     */
    public static function findByIdForOffice(int $appointmentId, int $officeId): ?array
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("
            SELECT a.appointment_id, a.slot_id, a.status, s.doctor_id 
            FROM Appointment a 
            JOIN Appointment_slot s ON s.slot_id = a.slot_id 
            JOIN Doctor d ON d.doctor_id = s.doctor_id
            WHERE a.appointment_id = ? AND d.office_id = ?
        ");
        $stmt->bind_param('ii', $appointmentId, $officeId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    /**
     * Create a new appointment
     */
    public static function create(int $patientId, int $slotId): int
    {
        $conn = self::getConnection();
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO Appointment (patient_id, slot_id, status, booked_at) VALUES (?, ?, 'booked', NOW())");
            $stmt->bind_param('ii', $patientId, $slotId);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $appointmentId = (int) $conn->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("UPDATE Appointment_slot SET status = 'unavailable' WHERE slot_id = ? AND status = 'available'");
            $stmt->bind_param('i', $slotId);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) throw new Exception('Slot taken');
            $stmt->close();

            $conn->commit();
            return $appointmentId;
        } catch (Exception $e) {
            $conn->rollback();
            return 0;
        }
    }

    /**
     * Cancel an appointment
     */
    public static function cancel(int $appointmentId): bool
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT slot_id FROM Appointment WHERE appointment_id = ?");
        $stmt->bind_param('i', $appointmentId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) return false;

        $conn->begin_transaction();
        try {
            $conn->query("UPDATE Appointment SET status = 'canceled' WHERE appointment_id = $appointmentId");
            $conn->query("UPDATE Appointment_slot SET status = 'available' WHERE slot_id = {$row['slot_id']}");
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    /**
     * Reschedule an appointment
     */
    public static function reschedule(int $appointmentId, int $oldSlotId, int $newSlotId, int $doctorId): bool
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT 1 FROM Appointment_slot WHERE slot_id = ? AND doctor_id = ? AND status = 'available' AND start_time > NOW()");
        $stmt->bind_param('ii', $newSlotId, $doctorId);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) { $stmt->close(); return false; }
        $stmt->close();

        $conn->begin_transaction();
        try {
            $conn->query("UPDATE Appointment_slot SET status = 'available' WHERE slot_id = $oldSlotId");
            $conn->query("UPDATE Appointment_slot SET status = 'unavailable' WHERE slot_id = $newSlotId");
            $conn->query("UPDATE Appointment SET slot_id = $newSlotId, status = 'booked' WHERE appointment_id = $appointmentId");
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    private static function getConnection(): mysqli
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) $conn = Database::get_instance();
        return $conn;
    }
}
