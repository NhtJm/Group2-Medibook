<?php
// app/models/AppointmentSlot.php

class AppointmentSlot
{
    /**
     * Find an available slot by ID
     */
    public static function findAvailable(int $slotId): ?array
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT slot_id, doctor_id, start_time FROM Appointment_slot WHERE slot_id = ? AND status = 'available' AND start_time > NOW() LIMIT 1");
        $stmt->bind_param('i', $slotId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    /**
     * Get available slots for a doctor
     */
    public static function getAvailableForDoctor(int $doctorId, int $limit = 20): array
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT slot_id, start_time FROM Appointment_slot WHERE doctor_id = ? AND status = 'available' AND start_time > NOW() ORDER BY start_time ASC LIMIT ?");
        $stmt->bind_param('ii', $doctorId, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $slots = [];
        foreach ($rows as $row) {
            $dt = new DateTimeImmutable($row['start_time']);
            $slots[] = ['slot_id' => (int)$row['slot_id'], 'date_label' => $dt->format('D, M j'), 'time_label' => $dt->format('g:i A')];
        }
        return $slots;
    }

    /**
     * Count available slots per day for a doctor in a month
     */
    public static function getCountsByMonth(int $doctorId, string $yearMonth): array
    {
        $conn = self::getConnection();
        $first = new DateTimeImmutable($yearMonth . '-01');
        $next = $first->modify('first day of next month');

        $stmt = $conn->prepare("SELECT DATE(start_time) AS d, COUNT(*) AS c FROM Appointment_slot WHERE doctor_id = ? AND status = 'available' AND start_time >= ? AND start_time < ? AND (DATE(start_time) > CURDATE() OR DATE_ADD(start_time, INTERVAL 30 MINUTE) > NOW()) GROUP BY DATE(start_time)");
        $from = $first->format('Y-m-d');
        $to = $next->format('Y-m-d');
        $stmt->bind_param('iss', $doctorId, $from, $to);
        $stmt->execute();
        $result = $stmt->get_result();

        $counts = [];
        while ($row = $result->fetch_assoc()) $counts[$row['d']] = (int)$row['c'];
        $stmt->close();
        return $counts;
    }

    /**
     * Get available slots for a doctor on a date
     */
    public static function getForDoctorOnDate(int $doctorId, string $date): array
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT slot_id, start_time FROM Appointment_slot WHERE doctor_id = ? AND status = 'available' AND DATE(start_time) = ? AND DATE_ADD(start_time, INTERVAL 30 MINUTE) > NOW() ORDER BY start_time ASC");
        $stmt->bind_param('is', $doctorId, $date);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $slots = [];
        foreach ($rows as $row) $slots[] = ['slot_id' => (int)$row['slot_id'], 'start_time' => $row['start_time']];
        return $slots;
    }

    /**
     * Get month metadata for calendar
     */
    public static function getMonthMeta(string $yearMonth): array
    {
        $first = new DateTimeImmutable(preg_match('/^\d{4}-\d{2}$/', $yearMonth) ? "$yearMonth-01" : date('Y-m-01'));
        return [
            'ym' => $first->format('Y-m'),
            'name' => $first->format('F Y'),
            'firstDow' => (int)$first->format('w'),
            'daysInMonth' => (int)$first->format('t'),
            'prev' => $first->modify('first day of previous month')->format('Y-m'),
            'next' => $first->modify('first day of next month')->format('Y-m'),
        ];
    }

    private static function getConnection(): mysqli
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) $conn = Database::get_instance();
        return $conn;
    }
}
