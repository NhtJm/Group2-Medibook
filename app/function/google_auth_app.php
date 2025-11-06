<?php
// app/function/google_auth_app.php
// Nhận $info từ Google (sub, email, name, picture)
// -> Đăng nhập theo chế độ cấu hình
//   - 'upsert' : tạo mới nếu chưa có (mặc định trước đây)
//   - 'link'   : KHÔNG tạo mới; chỉ gắn Google vào tài khoản có cùng email rồi đăng nhập
//   - 'strict' : KHÔNG tạo mới, KHÔNG gắn email; chỉ đăng nhập nếu đã từng liên kết Google
define('GOOGLE_LOGIN_MODE', 'link'); // 'upsert' | 'link' | 'strict'

require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/** Lấy mysqli connection từ Singleton Database hoặc $conn global */
function _ga_get_conn() {
  if (!isset($GLOBALS['conn']) || !$GLOBALS['conn']) {
    if (class_exists('Database')) return Database::get_instance();
    // Nếu dự án dùng $conn global:
    global $conn;
    return $conn ?? null;
  }
  return $GLOBALS['conn'];
}

/** Đăng nhập nội bộ (set session) */
function _ga_login_session(array $row, ?string $fallbackName = null, ?string $fallbackEmail = null): void {
  $_SESSION['user'] = [
    'id'    => (int)$row['user_id'],
    'email' => $row['email'] ?? $fallbackEmail,
    'name'  => ($row['full_name'] ?? null) ?: $fallbackName ?: ($row['email'] ?? 'User'),
    'role'  => $row['role'] ?? 'patient',
  ];
}

/** Hàm chính: đăng nhập/tạo tài khoản tùy chế độ */
function app_login_or_create_from_google(array $info): bool {
  $conn = _ga_get_conn();
  if (!$conn) return false;

  // Fields từ Google
  $sub     = $info['sub']     ?? null;  // Google subject (unique)
  $email   = $info['email']   ?? null;
  $name    = $info['name']    ?? ($info['given_name'] ?? 'Google User');
  $picture = $info['picture'] ?? null;
  if (!$sub || !$email) return false;

  // Vai trò mặc định nếu phải tạo mới (chỉ dùng cho chế độ 'upsert')
  $chosenRole = $_SESSION['oauth_role'] ?? 'patient';
  $role = in_array($chosenRole, ['patient','office'], true) ? $chosenRole : 'patient';

  // 1) Đã có mapping Google?
  $stmt = $conn->prepare("SELECT user_id, email, full_name, role FROM Users
                          WHERE oauth_provider='google' AND oauth_sub=? LIMIT 1");
  if (!$stmt) return false;
  $stmt->bind_param('s', $sub);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) {
    // Cập nhật ảnh nếu có
    if ($picture) {
      if ($upd = $conn->prepare("UPDATE Users SET picture_url=? WHERE user_id=?")) {
        $upd->bind_param('si', $picture, $row['user_id']);
        $upd->execute();
      }
    }
    _ga_login_session($row, $name, $email);
    return true;
  }

  // 2) Chế độ 'strict' -> chỉ cho user đã liên kết trước đó
  if (GOOGLE_LOGIN_MODE === 'strict') {
    return false;
  }

  // 3) Chế độ 'link' -> gắn Google vào tài khoản có sẵn theo email (nếu có)
  if (GOOGLE_LOGIN_MODE === 'link') {
    if ($s2 = $conn->prepare("SELECT user_id, email, full_name, role FROM Users WHERE email=? LIMIT 1")) {
      $s2->bind_param('s', $email);
      $s2->execute();
      $u = $s2->get_result()->fetch_assoc();
      if ($u) {
        if ($upd = $conn->prepare("UPDATE Users SET oauth_provider='google', oauth_sub=?, picture_url=? WHERE user_id=?")) {
          $upd->bind_param('ssi', $sub, $picture, $u['user_id']);
          $upd->execute();
        }
        _ga_login_session($u, $name, $email);
        return true;
      }
    }
    // Không có tài khoản email này -> từ chối (không tự tạo mới)
    return false;
  }

  // 4) Chế độ 'upsert' -> tạo mới tài khoản
  $username = explode('@', $email)[0];
  // Tránh trùng username
  if ($chk = $conn->prepare("SELECT COUNT(*) c FROM Users WHERE username=?")) {
    $chk->bind_param('s', $username);
    $chk->execute();
    $c = $chk->get_result()->fetch_assoc()['c'] ?? 0;
    if ($c > 0) $username .= rand(1000, 9999);
  }

  // password_hash NULL vì SSO
  $null = null;
  $ins = $conn->prepare("INSERT INTO Users
    (email, username, password_hash, full_name, role, oauth_provider, oauth_sub, picture_url)
    VALUES (?,?,?,?,?,'google',?,?)");
  if (!$ins) return false;
  $ins->bind_param('sssssss', $email, $username, $null, $name, $role, $sub, $picture);
  if (!$ins->execute()) return false;
  $newId = $ins->insert_id;

  // Tùy schema: tạo hồ sơ role (không bắt buộc, có thể lược bỏ)
  @ $conn->query("INSERT INTO Patient (user_id, status, payment) VALUES ($newId,'active','insurance')");
  if ($role === 'office') {
    if ($s3 = $conn->prepare("INSERT INTO Office (user_id, name, status) VALUES (?, ?, 'pending')")) {
      $s3->bind_param('is', $newId, $name);
      $s3->execute();
    }
  }

  _ga_login_session([
    'user_id'   => $newId,
    'email'     => $email,
    'full_name' => $name,
    'role'      => $role,
  ], $name, $email);

  return true;
}
