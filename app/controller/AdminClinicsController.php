<?php
// app/controller/AdminClinicsController.php
require_once __DIR__ . '/../db.php';

class AdminClinicsController
{

    public static function index(): array
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        // filters
        $status = strtolower(trim((string) ($_GET['status'] ?? 'all')));
        $allowedStatus = ['approved', 'pending', 'deactivated'];
        if (!in_array($status, $allowedStatus, true))
            $status = 'all';

        $q = trim((string) ($_GET['q'] ?? ''));

        // where
        $where = ' WHERE 1 ';
        $args = [];
        $types = '';

        if ($q !== '') {
            $where .= " AND (o.name LIKE ? OR o.address LIKE ? OR o.slug LIKE ?)";
            $like = "%{$q}%";
            $args[] = $like;
            $args[] = $like;
            $args[] = $like;
            $types .= 'sss';
        }
        if ($status !== 'all') {
            $where .= " AND o.status = ?";
            $args[] = $status;
            $types .= 's';
        }

        // fetch ALL rows (no pagination)
        $sql = "SELECT o.office_id, o.name, o.address, o.slug, o.rating, o.logo, o.status,
                       p.phone
                FROM Office o
                LEFT JOIN (
                   SELECT office_id, MIN(phone) AS phone FROM Office_phone GROUP BY office_id
                ) p ON p.office_id = o.office_id
                {$where}
                ORDER BY o.name ASC";

        $st = $conn->prepare($sql);
        if ($types !== '') {
            $bind = [&$types];
            foreach ($args as $i => $v) {
                $bind[] = &$args[$i];
            }
            call_user_func_array([$st, 'bind_param'], $bind);
        }
        $st->execute();
        $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st->close();

        return [
            'clinics' => $rows,
            'search' => $q,
            'status' => $status,
        ];
    }


    public static function api(): void
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');

        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        // ---------- POST: update status ----------
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Optional: role guard (let it pass if helpers are absent)
            if (function_exists('current_user')) {
                $u = current_user();
                if (!$u || (($u['role'] ?? '') !== 'admin')) {
                    http_response_code(403);
                    echo json_encode(['ok' => false, 'error' => 'forbidden']);
                    exit;
                }
            }

            $body = json_decode(file_get_contents('php://input'), true) ?: [];
            $csrf = (string) ($body['csrf'] ?? '');
            if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'bad_csrf']);
                exit;
            }

            $office_id = (int) ($body['office_id'] ?? 0);
            $status = strtolower(trim((string) ($body['status'] ?? '')));

            $allowed = ['approved', 'pending', 'deactivated'];
            if ($office_id <= 0 || !in_array($status, $allowed, true)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'invalid_input']);
                exit;
            }

            $st = $conn->prepare("UPDATE Office SET status=? WHERE office_id=?");
            if (!$st) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'prepare_failed']);
                exit;
            }
            $st->bind_param('si', $status, $office_id);
            $ok = $st->execute();
            $aff = $st->affected_rows;
            $st->close();

            if (!$ok) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'db_update_failed']);
                exit;
            }
            // If office_id not found, still return 200 with ok=false (or use 404 if you prefer)
            if ($aff === 0) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'not_found', 'office_id' => $office_id]);
                exit;
            }

            echo json_encode(['ok' => true, 'office_id' => $office_id, 'status' => $status]);
            exit;
        }

        // ---------- GET: lazy list with filters ----------
        $q = trim((string) ($_GET['q'] ?? ''));
        $off = max(0, (int) ($_GET['off'] ?? 0));
        $limit = min(50, max(1, (int) ($_GET['limit'] ?? 12)));
        $status = strtolower(trim((string) ($_GET['status'] ?? 'all')));
        $allowed = ['approved', 'pending', 'deactivated'];
        if (!in_array($status, $allowed, true))
            $status = 'all';

        $where = ' WHERE 1 ';
        $args = [];
        $types = '';

        if ($q !== '') {
            $where .= " AND (o.name LIKE ? OR o.address LIKE ? OR o.slug LIKE ?)";
            $like = "%{$q}%";
            $args[] = $like;
            $args[] = $like;
            $args[] = $like;
            $types .= 'sss';
        }
        if ($status !== 'all') {
            $where .= " AND o.status = ?";
            $args[] = $status;
            $types .= 's';
        }

        // total
        $sqlCount = "SELECT COUNT(DISTINCT o.office_id) AS total FROM Office o {$where}";
        $st = $conn->prepare($sqlCount);
        if ($types !== '') {
            $bind = [&$types];
            foreach ($args as $i => $v) {
                $bind[] = &$args[$i];
            }
            call_user_func_array([$st, 'bind_param'], $bind);
        }
        $st->execute();
        $total = (int) ($st->get_result()->fetch_assoc()['total'] ?? 0);
        $st->close();

        // page data
        $sql = "SELECT o.office_id, o.name, o.address, o.slug, o.rating, o.logo, o.status,
                       p.phone
                FROM Office o
                LEFT JOIN (
                    SELECT office_id, MIN(phone) AS phone FROM Office_phone GROUP BY office_id
                ) p ON p.office_id = o.office_id
                {$where}
                ORDER BY o.name ASC
                LIMIT ? OFFSET ?";

        $st = $conn->prepare($sql);
        if ($types !== '') {
            $types2 = $types . 'ii';
            $bind = [&$types2];
            foreach ($args as $i => $v) {
                $bind[] = &$args[$i];
            }
            $bind[] = &$limit;
            $bind[] = &$off;
            call_user_func_array([$st, 'bind_param'], $bind);
        } else {
            $st->bind_param('ii', $limit, $off);
        }
        $st->execute();
        $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st->close();

        $next = $off + count($rows);
        echo json_encode([
            'items' => $rows,
            'next_offset' => ($next < $total) ? $next : null,
            'total' => $total,
            'count' => count($rows),
        ]);
        exit;
    }
}