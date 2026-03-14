<?php
// app/controller/AdminSpecialtiesController.php
require_once __DIR__ . '/../db.php';

class AdminSpecialtiesController
{


    public static function index(): array
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance(); // mysqli
        }

        $q = trim((string) ($_GET['q'] ?? ''));
        $featured = (function ($v) {
            return in_array($v, ['all', 'yes', 'no'], true) ? $v : 'all'; })
        ((string) ($_GET['featured'] ?? 'all'));

        // --- WHERE + args ---
        $where = ' WHERE 1 ';
        $args = [];
        $types = '';

        if ($q !== '') {
            $where .= ' AND (name LIKE ? OR slug LIKE ? OR blurb LIKE ?)';
            $like = "%{$q}%";
            $args[] = $like;
            $args[] = $like;
            $args[] = $like;
            $types .= 'sss';
        }
        if ($featured === 'yes') {
            $where .= ' AND is_featured = 1';
        } elseif ($featured === 'no') {
            $where .= ' AND is_featured = 0';
        }

        // --- total count (for the "X shown") ---
        $sqlCount = "SELECT COUNT(*) AS total FROM Medical_specialty {$where}";
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

        // --- data (NO LIMIT) ---
        $sql = "SELECT specialty_id, slug, name, blurb, image_url, is_featured, sort_order
            FROM Medical_specialty
            {$where}
            ORDER BY sort_order ASC, name ASC";

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

        // pager kept for compatibility, but it’s always a single page now
        return [
            'specialties' => $rows,
            'search' => $q,
            'featured' => $featured,
            'pager' => ['page' => 1, 'pages' => 1, 'total' => $total],
        ];
    }

    public static function api(): void
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');

        // guards
        if (function_exists('current_user')) {
            $u = current_user();
            if (!$u || (($u['role'] ?? '') !== 'admin')) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'error' => 'forbidden']);
                return;
            }
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli))
            $conn = Database::get_instance();

        if ($method === 'POST') {
            $body = json_decode(file_get_contents('php://input'), true) ?: [];
            $csrf = (string) ($body['csrf'] ?? '');
            if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'bad_csrf']);
                return;
            }

            $action = (string) ($body['action'] ?? '');
            if ($action === 'toggle_featured') {
                $id = (int) ($body['id'] ?? 0);
                $val = (int) !!($body['featured'] ?? 0);
                $st = $conn->prepare("UPDATE Medical_specialty SET is_featured=? WHERE specialty_id=?");
                $st->bind_param('ii', $val, $id);
                $ok = $st->execute();
                $st->close();
                echo json_encode(['ok' => $ok, 'id' => $id, 'is_featured' => $val]);
                return;
            }

            if ($action === 'delete') {
                $id = (int) ($body['id'] ?? 0);
                $st = $conn->prepare("DELETE FROM Medical_specialty WHERE specialty_id=?");
                $st->bind_param('i', $id);
                $ok = $st->execute();
                $st->close();
                echo json_encode(['ok' => $ok, 'id' => $id]);
                return;
            }

            if ($action === 'move') {
                $id = (int) ($body['id'] ?? 0);
                $dir = (string) ($body['dir'] ?? 'up'); // up|down

                // fetch current
                $st = $conn->prepare("SELECT sort_order FROM Medical_specialty WHERE specialty_id=?");
                $st->bind_param('i', $id);
                $st->execute();
                $cur = $st->get_result()->fetch_assoc();
                $st->close();
                if (!$cur) {
                    echo json_encode(['ok' => false, 'error' => 'not_found']);
                    return;
                }
                $so = (int) $cur['sort_order'];

                if ($dir === 'up') {
                    $sql = "SELECT specialty_id, sort_order FROM Medical_specialty
                            WHERE sort_order < ? ORDER BY sort_order DESC LIMIT 1";
                } else {
                    $sql = "SELECT specialty_id, sort_order FROM Medical_specialty
                            WHERE sort_order > ? ORDER BY sort_order ASC LIMIT 1";
                }
                $st = $conn->prepare($sql);
                $st->bind_param('i', $so);
                $st->execute();
                $neighbor = $st->get_result()->fetch_assoc();
                $st->close();

                if (!$neighbor) {
                    echo json_encode(['ok' => true, 'no_change' => true]);
                    return;
                }

                // swap
                $nid = (int) $neighbor['specialty_id'];
                $nso = (int) $neighbor['sort_order'];
                $conn->begin_transaction();
                $st = $conn->prepare("UPDATE Medical_specialty SET sort_order=? WHERE specialty_id=?");
                $st->bind_param('ii', $nso, $id);
                $ok1 = $st->execute();
                $st->close();
                $st = $conn->prepare("UPDATE Medical_specialty SET sort_order=? WHERE specialty_id=?");
                $st->bind_param('ii', $so, $nid);
                $ok2 = $st->execute();
                $st->close();
                ($ok1 && $ok2) ? $conn->commit() : $conn->rollback();

                echo json_encode(['ok' => ($ok1 && $ok2)]);
                return;
            }

            echo json_encode(['ok' => false, 'error' => 'unknown_action']);
            return;
        }

        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    }
}