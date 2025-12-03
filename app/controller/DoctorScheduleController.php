<?php
if (session_status() === PHP_SESSION_NONE) session_start();

class DoctorScheduleController
{
    public static function index(): array
    {
        $u = $_SESSION['user'] ?? null;
        if (!$u) { header('Location:' . BASE_URL . 'index.php?page=login'); exit; }

        $role     = $u['role'] ?? '';
        $officeId = (int)($u['profile_id'] ?? 0);
        $doctorId = (int)($_GET['doctor_id'] ?? 0);

        require_once __DIR__ . '/../db.php';
        global $conn;
        if (!($conn instanceof mysqli) && class_exists('Database')) $conn = Database::get_instance();

        // if no doctor passed, pick first doctor of this office (office role only)
        if ($doctorId <= 0 && $role === 'office' && $officeId > 0) {
            $st = $conn->prepare("SELECT doctor_id FROM Doctor WHERE office_id=? ORDER BY doctor_name LIMIT 1");
            $st->bind_param('i', $officeId);
            $st->execute();
            $st->bind_result($doctorId); $st->fetch();
            $st->close();
        }

        // fetch doctor row
        $st = $conn->prepare("SELECT d.*, o.name AS office_name
                              FROM Doctor d
                              JOIN Office o USING(office_id)
                              WHERE d.doctor_id=? LIMIT 1");
        $st->bind_param('i', $doctorId);
        $st->execute();
        $doc = $st->get_result()->fetch_assoc();
        $st->close();

        if (!$doc) {
            http_response_code(404);
            return ['doctor' => null, 'error' => 'Doctor not found'];
        }

        // office users may only access their own doctor
        if ($role === 'office' && (int)$doc['office_id'] !== $officeId) {
            http_response_code(403);
            return ['doctor' => null, 'error' => 'Forbidden'];
        }

        if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

        return [
            'doctor' => $doc,
            'csrf'   => $_SESSION['csrf'],
        ];
    }
}