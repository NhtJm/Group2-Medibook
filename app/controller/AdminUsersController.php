<?php
// app/controller/AdminUsersController.php
require_once __DIR__ . '/../db.php';

class AdminUsersController {
  public static function index(): array {
    if (session_status() === PHP_SESSION_NONE) session_start();

    global $conn;
    if (!isset($conn) || !($conn instanceof mysqli)) {
      $conn = Database::get_instance();
    }

    $q    = trim((string)($_GET['q'] ?? ''));
    $role = trim((string)($_GET['role'] ?? 'all'));
    $rolesAllowed = ['admin','webstaff','office','patient'];
    if (!in_array($role, $rolesAllowed, true)) $role = 'all';

    $where = ' WHERE 1 ';
    $types = '';
    $args  = [];

    if ($q !== '') {
      $where .= ' AND (u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)';
      $like = "%{$q}%";
      $args[] = $like; $args[] = $like; $args[] = $like;
      $types .= 'sss';
    }
    if ($role !== 'all') {
      $where .= ' AND u.role = ?';
      $args[] = $role;
      $types .= 's';
    }

    $sql = "SELECT u.user_id, u.email, u.username, u.full_name, u.role,
                   u.oauth_provider, u.oauth_sub, u.picture_url, u.created_at,
                   (u.password_hash IS NOT NULL AND u.password_hash <> '') AS has_password
            FROM Users u
            {$where}
            ORDER BY u.created_at DESC";

    $st = $conn->prepare($sql);
    if ($types !== '') {
      $bind = [&$types];
      foreach ($args as $i => $v) { $bind[] = &$args[$i]; }
      call_user_func_array([$st, 'bind_param'], $bind);
    }
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();

    return [
      'users' => $rows,
      'search' => $q,
      'role' => $role,
    ];
  }
}