<?php
// app/models/Patient.php

class Patient
{
    /**
     * Get patient ID by user ID
     */
    public static function getPatientId(int $userId): int
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT patient_id FROM Patient WHERE user_id = ? LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) ($result['patient_id'] ?? 0);
    }

    /**
     * Get or create patient ID for a user
     */
    public static function getOrCreate(int $userId): int
    {
        $patientId = self::getPatientId($userId);
        if ($patientId > 0) return $patientId;

        $conn = self::getConnection();
        $stmt = $conn->prepare("INSERT INTO Patient (user_id, status) VALUES (?, 'active')");
        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) { $stmt->close(); return 0; }
        $patientId = (int) $conn->insert_id;
        $stmt->close();
        return $patientId;
    }

    private static function getConnection(): mysqli
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) $conn = Database::get_instance();
        return $conn;
    }
}
