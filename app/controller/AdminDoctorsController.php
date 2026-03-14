<?php
// app/controller/AdminDoctorsController.php
require_once __DIR__ . '/../db.php';

class AdminDoctorsController
{
    public static function index(): array
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli))
            $conn = Database::get_instance();

        $q = trim((string) ($_GET['q'] ?? ''));
        $status = (string) ($_GET['status'] ?? 'all');

        $where = [];
        $types = '';
        $params = [];

        // Search by clinic fields OR any doctor in that clinic
        if ($q !== '') {
            $like = '%' . $q . '%';
            $where[] =
                '(o.name LIKE ? OR o.slug LIKE ? OR o.address LIKE ? OR EXISTS (
                    SELECT 1
                    FROM Doctor d2
                    WHERE d2.office_id = o.office_id
                      AND d2.doctor_name LIKE ?
                 ))';
            $types .= 'ssss';
            array_push($params, $like, $like, $like, $like);
        }

        // Status filter
        if (in_array($status, ['approved', 'pending', 'deactivated'], true)) {
            $where[] = 'o.status = ?';
            $types .= 's';
            $params[] = $status;
        }

        // Keep counting via scalar subquery so the WHERE doesn’t affect counts
        $sql = "
            SELECT
                o.office_id,
                o.name,
                o.slug,
                o.address,
                o.logo,
                o.status,
                (SELECT COUNT(*) FROM Doctor d WHERE d.office_id = o.office_id) AS doctor_count
            FROM Office o
            " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . "
            ORDER BY o.name ASC
        ";

        $st = $conn->prepare($sql);
        if ($params)
            $st->bind_param($types, ...$params);
        $st->execute();
        $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st->close();

        return [
            'q' => $q,
            'status' => $status,
            'offices' => $rows,
        ];
    }
}