<?php
// app/function/google_oauth.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../../config/config.php';

function google_build_auth_url(): string {
  $params = [
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => GOOGLE_OAUTH_SCOPE,
    'access_type'   => 'offline',
    'include_granted_scopes' => 'true',
    'prompt'        => 'select_account',
  ];
  return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

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
  ]);
  $res = curl_exec($ch);
  if ($res === false) return null;
  $data = json_decode($res, true);
  return is_array($data) ? $data : null;
}

function google_fetch_userinfo(string $accessToken): ?array {
  $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
  curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
    CURLOPT_RETURNTRANSFER => true,
  ]);
  $res = curl_exec($ch);
  if ($res === false) return null;
  $data = json_decode($res, true);
  return is_array($data) ? $data : null;
}

function app_login_or_create_from_google(array $g): bool {
  if (empty($g['email'])) return false;

  $conn = Database::get_instance();

  // Tìm user theo email
  $email = trim($g['email']);
  $q = $conn->prepare("SELECT user_id, username, email, password_hash, full_name, role FROM Users WHERE email=? LIMIT 1");
  $q->bind_param('s', $email);
  $q->execute();
  $u = $q->get_result()->fetch_assoc();
  $q->close();

  // Nếu đã có user: set session + profile_id theo role
  if ($u) {
    $profile_id = _app_fetch_profile_id($conn, (int)$u['user_id'], (string)$u['role']);

    $_SESSION['user'] = [
      'id'         => (int)$u['user_id'],
      'username'   => $u['username'],
      'email'      => $u['email'],
      'name'       => $u['full_name'],
      'role'       => $u['role'],
      'profile_id' => $profile_id,
    ];
    return true;
  }

  // Chưa có user → tạo mới (role mặc định: patient)
  $google_sub = trim($g['sub'] ?? '');
  $username = preg_replace('/[^a-z0-9_]/i', '', strtok($email, '@'));
  if ($username === '') $username = 'google_' . substr($google_sub, 0, 12);

  // Đảm bảo unique username
  $base = $username; $try = 0;
  $chk = $conn->prepare("SELECT 1 FROM Users WHERE username=? LIMIT 1");
  while (true) {
    $chk->bind_param('s', $username);
    $chk->execute(); $chk->store_result();
    if ($chk->num_rows === 0) break;
    $chk->free_result();
    $try++;
    $username = $base . $try;
    if ($try > 100) { $chk->close(); return false; }
  }
  $chk->close();

  $full_name = trim($g['name'] ?? '');
  if ($full_name === '') $full_name = $username;

  $rand = bin2hex(random_bytes(16));
  $hash = password_hash($rand, PASSWORD_DEFAULT);
  $role = 'patient';

  $conn->begin_transaction();
  try {
    // Tạo Users (đúng cột: full_name, role)
    $ins = $conn->prepare("INSERT INTO Users (email, username, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
    $ins->bind_param('sssss', $email, $username, $hash, $full_name, $role);
    if (!$ins->execute()) throw new Exception($ins->error);
    $user_id = (int)$conn->insert_id;
    $ins->close();

    // Tạo hồ sơ Patient mặc định
    $p = $conn->prepare("INSERT INTO Patient (user_id, status) VALUES (?, 'active')");
    $p->bind_param('i', $user_id);
    if (!$p->execute()) throw new Exception($p->error);
    $patient_id = (int)$conn->insert_id; // optional
    $p->close();

    $conn->commit();

    $_SESSION['user'] = [
      'id'         => $user_id,
      'username'   => $username,
      'email'      => $email,
      'name'       => $full_name,
      'role'       => $role,
      'profile_id' => $patient_id ?: null,
    ];
    return true;

  } catch (Throwable $e) {
    $conn->rollback();
    return false;
  }
}

/* Helper: lấy profile_id theo role hiện tại của user */
function _app_fetch_profile_id(mysqli $conn, int $user_id, string $role): ?int {
  if ($role === 'patient') {
    $s = $conn->prepare("SELECT patient_id AS id FROM Patient WHERE user_id=? LIMIT 1");
  } elseif ($role === 'webstaff') {
    $s = $conn->prepare("SELECT staff_id AS id FROM Web_staff WHERE user_id=? LIMIT 1");
  } elseif ($role === 'office') {
    $s = $conn->prepare("SELECT office_id AS id FROM Office WHERE user_id=? LIMIT 1");
  } elseif ($role === 'admin') {
    $s = $conn->prepare("SELECT admin_id AS id FROM Admin WHERE user_id=? LIMIT 1");
  } else {
    return null;
  }
  $s->bind_param('i', $user_id);
  $s->execute();
  $r = $s->get_result()->fetch_assoc();
  $s->close();
  return $r['id'] ?? null;
}
