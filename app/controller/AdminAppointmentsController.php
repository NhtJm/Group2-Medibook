<?php
// app/controller/AdminAppointmentsController.php
require_once __DIR__ . '/../db.php';

class AdminAppointmentsController
{
    public static function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $conn = Database::get_instance();

        // --- inline DELETE handler (POST) ---
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['act'] ?? '') === 'delete') {
            self::handleDelete($conn);
            return null; // redirect happens inside
        }

        // --------- LIST VIEW ----------
        $office_id = isset($_GET['office']) ? (int)$_GET['office'] : 0;
        $status    = isset($_GET['status']) ? trim($_GET['status']) : '';
        $q         = isset($_GET['q']) ? trim($_GET['q']) : '';
        $from      = isset($_GET['from']) ? trim($_GET['from']) : '';
        $to        = isset($_GET['to'])   ? trim($_GET['to'])   : '';
        $page      = max(1, (int)($_GET['p'] ?? 1));
        $perPage   = 25;
        $offset    = ($page - 1) * $perPage;

        // Offices for filter
        $offices = [];
        if ($rs = $conn->query("SELECT office_id, name FROM Office ORDER BY name")) {
            while ($row = $rs->fetch_assoc()) $offices[] = $row;
        }

        // WHERE builder
        $where  = [];
        $types  = '';
        $params = [];

        if ($office_id > 0) { $where[] = 'o.office_id=?'; $types.='i'; $params[]=$office_id; }

        if ($status !== '') {
            $ok = ['booked','canceled','completed','no-show'];
            if (in_array($status, $ok, true)) { $where[]='a.status=?'; $types.='s'; $params[]=$status; }
        }

        if ($q !== '') {
            $where[] = "("
                . "u.full_name  LIKE CONCAT('%',?,'%') OR "
                . "u.username   LIKE CONCAT('%',?,'%') OR "
                . "u.email      LIKE CONCAT('%',?,'%') OR "
                . "d.doctor_name LIKE CONCAT('%',?,'%')"
                . ")";
            $types .= 'ssss';
            array_push($params, $q, $q, $q, $q);
        }

        if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $where[]='DATE(s.start_time)>=?'; $types.='s'; $params[]=$from; }
        if ($to   !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   { $where[]='DATE(s.start_time)<=?'; $types.='s'; $params[]=$to; }

        $W = $where ? 'WHERE '.implode(' AND ', $where) : '';

        // Count
        $sqlCount = "SELECT COUNT(*) AS n
                     FROM Appointment a
                     JOIN Appointment_slot s ON a.slot_id = s.slot_id
                     JOIN Doctor d          ON s.doctor_id = d.doctor_id
                     JOIN Office o          ON d.office_id = o.office_id
                     JOIN Patient p         ON a.patient_id = p.patient_id
                     LEFT JOIN Users u      ON p.user_id = u.user_id
                     $W";
        $stc = $conn->prepare($sqlCount);
        if ($types) $stc->bind_param($types, ...$params);
        $stc->execute();
        $total = (int)($stc->get_result()->fetch_assoc()['n'] ?? 0);
        $stc->close();

        // Rows
        $sql = "SELECT
                    a.appointment_id,
                    a.status AS appt_status,
                    a.booked_at,
                    s.start_time, s.end_time,
                    d.doctor_id, d.doctor_name,
                    o.office_id, o.name AS office_name,
                    p.patient_id,
                    u.user_id AS patient_user_id,
                    COALESCE(u.full_name, u.username, u.email) AS patient_name
                FROM Appointment a
                JOIN Appointment_slot s ON a.slot_id = s.slot_id
                JOIN Doctor d          ON s.doctor_id = d.doctor_id
                JOIN Office o          ON d.office_id = o.office_id
                JOIN Patient p         ON a.patient_id = p.patient_id
                LEFT JOIN Users u      ON p.user_id = u.user_id
                $W
                ORDER BY o.name, s.start_time
                LIMIT ? OFFSET ?";
        $st = $conn->prepare($sql);
        $typesRows  = $types . 'ii';
        $paramsRows = $params; $paramsRows[] = $perPage; $paramsRows[] = $offset;
        $st->bind_param($typesRows, ...$paramsRows);
        $st->execute();
        $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st->close();

        // Count rows per office (current page)
        $officeCounts = [];
        foreach ($rows as $r) {
            $officeCounts[$r['office_name']] = ($officeCounts[$r['office_name']] ?? 0) + 1;
        }

        return [
            'rows'         => $rows,
            'total'        => $total,
            'page'         => $page,
            'perPage'      => $perPage,
            'offices'      => $offices,
            'officeCounts' => $officeCounts,
            'filters'      => ['office'=>$office_id,'status'=>$status,'q'=>$q,'from'=>$from,'to'=>$to]
        ];
    }

    private static function handleDelete(mysqli $conn): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // CSRF
        $csrf = $_POST['csrf'] ?? '';
        if (!isset($_SESSION['csrf']) || !$csrf || !hash_equals($_SESSION['csrf'], $csrf)) {
            $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid CSRF token.'];
            self::redirectBack(); return;
        }

        $appointment_id = (int)($_POST['appointment_id'] ?? 0);
        if ($appointment_id <= 0) {
            $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid appointment id.'];
            self::redirectBack(); return;
        }

        $conn->begin_transaction();
        try {
            // Lock & fetch slot id
            $st = $conn->prepare("SELECT slot_id FROM Appointment WHERE appointment_id=? FOR UPDATE");
            $st->bind_param('i', $appointment_id);
            $st->execute();
            $row = $st->get_result()->fetch_assoc();
            $st->close();
            if (!$row) throw new Exception('Appointment not found');
            $slot_id = (int)$row['slot_id'];

            // Delete appointment
            $st = $conn->prepare("DELETE FROM Appointment WHERE appointment_id=?");
            $st->bind_param('i', $appointment_id);
            $st->execute();
            $deleted = $st->affected_rows > 0;
            $st->close();

            // Free slot
            if ($deleted && $slot_id > 0) {
                $st = $conn->prepare("UPDATE Appointment_slot SET status='available' WHERE slot_id=?");
                $st->bind_param('i', $slot_id);
                $st->execute();
                $st->close();
            }

            $conn->commit();
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Appointment deleted. Slot is now available.'];
        } catch (Throwable $e) {
            $conn->rollback();
            $_SESSION['flash'] = ['type'=>'error','msg'=>'Delete failed.'];
        }

        self::redirectBack();
    }

    private static function redirectBack(): void
    {
        $back   = $_POST['back'] ?? '';
        $target = BASE_URL.'index.php?page=admin_appointments';
        if ($back && strpos($back, 'page=admin_appointments') === 0) {
            $target = BASE_URL.'index.php?'.$back;
        }
        header('Location: '.$target);
        exit;
    }
}