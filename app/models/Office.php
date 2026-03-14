<?php
// app/models/Office.php

class Office
{
    /**
     * Search approved offices by name/specialty + location (paginated)
     */
    public static function searchPaginated(mysqli $conn, ?string $q, ?string $loc, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ["o.status = 'approved'"];
        $params = [];
        $types = "";

        if ($q !== null && $q !== '') {
            $where[] = "(o.name LIKE ? OR EXISTS (
                SELECT 1 FROM Office_has_specialty ohs
                JOIN Medical_specialty m ON m.specialty_id = ohs.specialty_id
                WHERE ohs.office_id = o.office_id AND (m.name LIKE ? OR m.slug LIKE ?)
            ))";
            $like = "%$q%";
            $params = array_merge($params, [$like, $like, $like]);
            $types .= "sss";
        }

        if ($loc !== null && $loc !== '') {
            $where[] = "o.address LIKE ?";
            $params[] = "%$loc%";
            $types .= "s";
        }

        $whereSql = "WHERE " . implode(" AND ", $where);

        // Count
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT o.office_id) AS c FROM Office o $whereSql");
        if ($types !== "") $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $total = (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();

        // Fetch page
        $stmt = $conn->prepare("SELECT o.office_id, o.name, o.address, o.logo FROM Office o $whereSql ORDER BY o.name ASC LIMIT ? OFFSET ?");
        $params2 = array_merge($params, [$perPage, $offset]);
        $stmt->bind_param($types . "ii", ...$params2);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$r) {
            $r['slots'] = self::nextSlots($conn, (int) $r['office_id'], 6);
            $r['is_open'] = self::hasSlotToday($r['slots']);
            $r['badge'] = self::initials($r['name'] ?? '');
        }

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pages' => max(1, (int) ceil($total / $perPage))];
    }

    /**
     * List all approved offices (paginated)
     */
    public static function listPaginatedAll(mysqli $conn, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        $total = (int) ($conn->query("SELECT COUNT(*) AS c FROM Office WHERE status = 'approved'")->fetch_assoc()['c'] ?? 0);

        $stmt = $conn->prepare("SELECT office_id, name, address, logo FROM Office WHERE status = 'approved' ORDER BY name ASC LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $perPage, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$r) {
            $r['slots'] = self::nextSlots($conn, (int) $r['office_id'], 6);
            $r['is_open'] = self::hasSlotToday($r['slots']);
            $r['badge'] = self::initials($r['name'] ?? '');
            $r['status'] = 'approved';
        }

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pages' => max(1, (int) ceil($total / $perPage))];
    }

    /**
     * List approved offices by specialty ID (paginated)
     */
    public static function bySpecialtyPaginated(mysqli $conn, int $specialtyId, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $conn->prepare("SELECT COUNT(DISTINCT o.office_id) AS c FROM Office o JOIN Office_has_specialty ohs ON ohs.office_id = o.office_id WHERE o.status = 'approved' AND ohs.specialty_id = ?");
        $stmt->bind_param("i", $specialtyId);
        $stmt->execute();
        $total = (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();

        $stmt = $conn->prepare("SELECT DISTINCT o.office_id, o.name, o.address, o.logo FROM Office o JOIN Office_has_specialty ohs ON ohs.office_id = o.office_id WHERE o.status = 'approved' AND ohs.specialty_id = ? ORDER BY o.name ASC LIMIT ? OFFSET ?");
        $stmt->bind_param("iii", $specialtyId, $perPage, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$r) {
            $r['slots'] = self::nextSlots($conn, (int) $r['office_id'], 6);
            $r['is_open'] = self::hasSlotToday($r['slots']);
            $r['badge'] = self::initials($r['name'] ?? '');
            $r['status'] = 'approved';
        }

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pages' => max(1, (int) ceil($total / $perPage))];
    }

    /**
     * Find one office by ID
     */
    public static function findOne(mysqli $conn, int $officeId): ?array
    {
        $stmt = $conn->prepare("SELECT o.office_id, o.name, o.address, o.logo, o.status, o.website, o.description, o.googleMap, o.rating, MIN(op.phone) AS phone FROM Office o LEFT JOIN Office_phone op ON op.office_id = o.office_id WHERE o.office_id = ? AND o.status = 'approved' GROUP BY o.office_id LIMIT 1");
        $stmt->bind_param("i", $officeId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) return null;

        $row['code'] = self::initials($row['name']);
        $row['is_open'] = self::hasSlotToday(self::nextSlots($conn, $officeId, 1));
        $row['hours_label'] = $row['is_open'] ? 'Slots today' : null;

        return $row;
    }

    /**
     * Get next available slots for an office
     */
    public static function nextSlots(mysqli $conn, int $officeId, int $limit = 6): array
    {
        $stmt = $conn->prepare("SELECT s.start_time FROM Appointment_slot s JOIN Doctor d ON d.doctor_id = s.doctor_id WHERE d.office_id = ? AND s.status = 'available' AND s.start_time >= NOW() ORDER BY s.start_time ASC LIMIT ?");
        $stmt->bind_param("ii", $officeId, $limit);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        $now = new DateTimeImmutable('now');
        $todayYmd = $now->format('Y-m-d');
        $tomYmd = $now->modify('+1 day')->format('Y-m-d');

        while ($row = $res->fetch_assoc()) {
            $dt = new DateTimeImmutable($row['start_time']);
            $ymd = $dt->format('Y-m-d');
            $out[] = [
                'dt' => $dt->format('c'),
                'day_key' => $ymd,
                'day_label' => $ymd === $todayYmd ? "Today" : ($ymd === $tomYmd ? "Tomorrow" : $dt->format('D')),
                'time_label' => strtolower($dt->format('g:ia')),
            ];
        }
        $stmt->close();
        return $out;
    }

    /**
     * Check if slots contain a slot for today
     */
    public static function hasSlotToday(array $slots): bool
    {
        $todayYmd = (new DateTimeImmutable('now'))->format('Y-m-d');
        foreach ($slots as $s) {
            if (($s['day_key'] ?? '') === $todayYmd) return true;
        }
        return false;
    }

    /**
     * Search offices for autocomplete
     */
    public static function search(string $query, string $location = '', int $limit = 10): array
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT office_id, name, address FROM Office WHERE status = 'approved' AND (? = '' OR name LIKE CONCAT('%', ?, '%')) AND (? = '' OR address LIKE CONCAT('%', ?, '%')) ORDER BY name LIMIT ?");
        $stmt->bind_param('ssssi', $query, $query, $location, $location, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $results = [];
        foreach ($rows as $r) {
            $results[] = ['type' => 'office', 'id' => (int) $r['office_id'], 'name' => $r['name'], 'address' => $r['address'] ?? ''];
        }
        return $results;
    }

    /**
     * Get initials from name
     */
    private static function initials(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($name === '') return 'CL';
        $parts = explode(' ', $name);
        return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
    }

    /**
     * Get DB connection
     */
    private static function getConnection(): mysqli
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }
        return $conn;
    }
}
