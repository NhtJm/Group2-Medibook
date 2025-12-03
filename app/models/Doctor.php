<?php
// app/models/Doctor.php

class Doctor
{
    /**
     * Find a doctor by ID with specialty info
     */
    public static function findById(int $doctorId): ?array
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT d.doctor_id, d.office_id, d.doctor_name AS name, d.degree, d.graduate, d.specialty_id, ms.name AS specialty FROM Doctor d LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id WHERE d.doctor_id = ? LIMIT 1");
        $stmt->bind_param('i', $doctorId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    /**
     * Find doctors by office
     */
    public static function findByOffice(int $officeId): array
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT d.doctor_id, d.doctor_name AS name, d.degree, d.photo, ms.name AS specialty FROM Doctor d LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id WHERE d.office_id = ? ORDER BY d.doctor_name ASC");
        $stmt->bind_param('i', $officeId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$doc) {
            $doc['id'] = (int) $doc['doctor_id'];
            $doc['spec'] = $doc['specialty'] ?? '';
            $parts = preg_split('/\s+/', trim($doc['name']));
            $doc['init'] = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr(end($parts) ?: '', 0, 1));
            $doc['next'] = self::getNextSlotLabel((int)$doc['doctor_id']);
        }
        return $rows;
    }

    /**
     * Search doctors by name
     */
    public static function search(string $query, string $location = '', int $limit = 10): array
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT d.doctor_id, d.doctor_name, d.specialty_id, ms.name AS specialty_name, o.office_id, o.name AS office_name, o.address FROM Doctor d JOIN Office o ON o.office_id = d.office_id AND o.status = 'approved' LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id WHERE d.doctor_name LIKE CONCAT('%', ?, '%') AND (? = '' OR o.address LIKE CONCAT('%', ?, '%')) ORDER BY d.doctor_name LIMIT ?");
        $stmt->bind_param('sssi', $query, $location, $location, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $results = [];
        foreach ($rows as $r) {
            $results[] = ['type' => 'doctor', 'id' => (int)$r['doctor_id'], 'name' => $r['doctor_name'], 'office_id' => (int)$r['office_id'], 'office_name' => $r['office_name'], 'address' => $r['address'] ?? '', 'specialty_id' => $r['specialty_id'] ? (int)$r['specialty_id'] : null, 'specialty_name' => $r['specialty_name']];
        }
        return $results;
    }

    private static function getNextSlotLabel(int $doctorId): string
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT start_time FROM Appointment_slot WHERE doctor_id = ? AND status = 'available' AND start_time >= NOW() ORDER BY start_time ASC LIMIT 1");
        $stmt->bind_param('i', $doctorId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? (new DateTimeImmutable($row['start_time']))->format('D, g:ia') : '—';
    }

    private static function getConnection(): mysqli
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) $conn = Database::get_instance();
        return $conn;
    }
}
