<?php
// app/function/google_oauth.php
// Helper thuần OAuth: build URL -> đổi code lấy token -> lấy userinfo
// (Chưa ghi DB, chưa tạo session. Ta sẽ làm ở bước sau.)
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../../config/config.php';

/** Tạo URL để chuyển người dùng sang trang đăng nhập Google */
function google_build_auth_url(): string {
  $params = [
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => GOOGLE_OAUTH_SCOPE,   // 'openid email profile'
    'access_type'   => 'offline',
    'include_granted_scopes' => 'true',
    'prompt'        => 'select_account',     // hoặc 'consent' nếu muốn luôn hiện
  ];
  return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

/** POST x-www-form-urlencoded và trả JSON (mảng) hoặc null nếu lỗi */
function _google_post_form(string $url, array $data): ?array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($data),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT        => 15,
  ]);
  $res = curl_exec($ch);
  if ($res === false) { curl_close($ch); return null; }
  curl_close($ch);
  $json = json_decode($res, true);
  return is_array($json) ? $json : null;
}

/** GET với Bearer token và trả JSON hoặc null */
function _google_get_auth_json(string $url, string $access_token): ?array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $access_token],
    CURLOPT_TIMEOUT        => 15,
  ]);
  $res = curl_exec($ch);
  if ($res === false) { curl_close($ch); return null; }
  curl_close($ch);
  $json = json_decode($res, true);
  return is_array($json) ? $json : null;
}

/** Đổi "code" (từ callback) để lấy access_token/ID token */
function google_exchange_code_for_token(string $code): ?array {
  $post = [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
  ];
  $ch = curl_init('https://oauth2.googleapis.com/token');
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
  ]);
  $resp = curl_exec($ch);
  if ($resp === false) return null;
  $data = json_decode($resp, true);
  return is_array($data) ? $data : null;
}

function google_fetch_userinfo(string $id_token): ?array {
  // id_token là JWT; cách nhanh: gọi endpoint userinfo bằng access_token
  // (nếu bạn đã lấy access_token trong token response)
  // Ở đây demo decode đơn giản bằng split, KHÔNG verify signature (tùy mức an toàn bạn muốn)
  $parts = explode('.', $id_token);
  if (count($parts) !== 3) return null;
  $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
  if (!is_array($payload)) return null;
  // payload thường có: sub, email, name, picture
  return [
    'sub'     => $payload['sub'] ?? null,
    'email'   => $payload['email'] ?? null,
    'name'    => $payload['name'] ?? ($payload['given_name'] ?? ''),
    'picture' => $payload['picture'] ?? null,
  ];
}

/* ===== DB helpers ===== */
function db_find_user_by_oauth(mysqli $conn, string $provider, string $sub): ?array {
  $stmt = $conn->prepare("SELECT * FROM Users WHERE oauth_provider=? AND oauth_sub=? LIMIT 1");
  $stmt->bind_param('ss', $provider, $sub);
  $stmt->execute();
  $res = $stmt->get_result();
  return $res->fetch_assoc() ?: null;
}

function db_find_user_by_email(mysqli $conn, string $email): ?array {
  $stmt = $conn->prepare("SELECT * FROM Users WHERE email=? LIMIT 1");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();
  return $res->fetch_assoc() ?: null;
}

function db_link_oauth_to_user(mysqli $conn, int $user_id, string $provider, string $sub, ?string $picture): bool {
  $stmt = $conn->prepare("UPDATE Users SET oauth_provider=?, oauth_sub=?, picture_url=? WHERE user_id=?");
  $stmt->bind_param('sssi', $provider, $sub, $picture, $user_id);
  return $stmt->execute();
}

function db_create_user_from_google(mysqli $conn, array $g, string $role): ?array {
  // username tạm dựa theo email
  $username = explode('@', $g['email'])[0] . '_' . substr($g['sub'], -6);
  $stmt = $conn->prepare("
    INSERT INTO Users (email, username, password_hash, full_name, role, oauth_provider, oauth_sub, picture_url)
    VALUES (?, ?, NULL, ?, ?, 'google', ?, ?)
  ");
  $stmt->bind_param('ssssss', $g['email'], $username, $g['name'], $role, $g['sub'], $g['picture']);
  if (!$stmt->execute()) return null;

  $user_id = $conn->insert_id;

  // Tạo bản ghi role-specific tối thiểu
  if ($role === 'patient') {
    $conn->query("INSERT INTO Patient (user_id) VALUES ($user_id)");
  } elseif ($role === 'office') {
    $stmt2 = $conn->prepare("INSERT INTO Office (user_id, name, status) VALUES (?, ?, 'pending')");
    $tmpName = $g['name'] . ' Clinic';
    $stmt2->bind_param('is', $user_id, $tmpName);
    $stmt2->execute();
  } elseif ($role === 'webstaff') {
    $conn->query("INSERT INTO Web_staff (user_id, position, status) VALUES ($user_id, 'support', 'offline')");
  }

  // trả về user mới
  $stmt3 = $conn->prepare("SELECT * FROM Users WHERE user_id=?");
  $stmt3->bind_param('i', $user_id);
  $stmt3->execute();
  $res = $stmt3->get_result();
  return $res->fetch_assoc() ?: null;
}

/* ===== Main handler ===== */
function google_handle_callback_login_or_register(): bool {
  if (!isset($_GET['code'])) return false;
  if (session_status() === PHP_SESSION_NONE) session_start();

  $code = $_GET['code'];
  $token = google_exchange_code_for_token($code);
  if (!$token || empty($token['id_token'])) return false;

  $g = google_fetch_userinfo($token['id_token']);
  if (!$g || !$g['sub'] || !$g['email']) return false;

  // DB connection
  $conn = Database::get_instance();

  // 1) đã liên kết bằng sub? => login thẳng
  $u = db_find_user_by_oauth($conn, 'google', $g['sub']);
  if ($u) {
    $_SESSION['user'] = [
      'id' => intval($u['user_id']),
      'name' => $u['full_name'],
      'role' => $u['role'],
      'email' => $u['email'],
      'picture' => $u['picture_url'] ?? null,
    ];
    return true;
  }

  // 2) chưa liên kết; thử tìm theo email
  $byEmail = db_find_user_by_email($conn, $g['email']);
  if ($byEmail) {
    // Flow LOGIN => link tài khoản rồi login; Flow REGISTER cũng có thể link thay vì tạo trùng
    db_link_oauth_to_user($conn, intval($byEmail['user_id']), 'google', $g['sub'], $g['picture']);
    $_SESSION['user'] = [
      'id' => intval($byEmail['user_id']),
      'name' => $byEmail['full_name'],
      'role' => $byEmail['role'], // KHÔNG ghi đè role
      'email' => $byEmail['email'],
      'picture' => $g['picture'] ?? ($byEmail['picture_url'] ?? null),
    ];
    return true;
  }

  // 3) không có trong DB
  $flow = $_SESSION['oauth_flow'] ?? 'login';
  $role = $_SESSION['oauth_role'] ?? null;

  if ($flow === 'register' && $role) {
    // tạo mới theo role đã chọn
    $newU = db_create_user_from_google($conn, $g, $role);
    if (!$newU) return false;
    $_SESSION['user'] = [
      'id' => intval($newU['user_id']),
      'name' => $newU['full_name'],
      'role' => $newU['role'],
      'email' => $newU['email'],
      'picture' => $newU['picture_url'] ?? null,
    ];
    // cleanup
    unset($_SESSION['oauth_flow'], $_SESSION['oauth_role']);
    return true;
  }

  // Flow LOGIN nhưng chưa biết role => yêu cầu chọn role
  // Lưu tạm info để sau khi chọn role còn tạo được
  $_SESSION['pending_google_user'] = $g; // sub/email/name/picture
  return false; // để router chuyển tới google_choose_role
}
