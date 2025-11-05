<?php
// app/function/google_oauth.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config/config.php';

function google_build_auth_url(): string {
  $params = [
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => GOOGLE_OAUTH_SCOPE,
    'access_type'   => 'offline',
    'include_granted_scopes' => 'true',
    'prompt'        => 'select_account', // hoặc 'consent'
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
  // $g ví dụ: ['sub'=>'...', 'email'=>'..','email_verified'=>true,'name'=>..., 'given_name'=>..., 'picture'=>...]
  if (empty($g['email'])) return false;

  $conn = Database::get_instance();

  // 1) Tìm theo email
  $email = $g['email'];
  $stmt = $conn->prepare("SELECT user_id, username FROM Users WHERE email = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $stmt->close();

  if ($row) {
    // Đăng nhập
    $_SESSION['user_id']  = (int)$row['user_id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['email']    = $email;
    return true;
  }

  // 2) Chưa có → tạo user mới
  $username = explode('@', $email)[0];
  // đảm bảo unique username: nếu trùng thì thêm số ngẫu nhiên
  $try = 0;
  $base = $username;
  while (true) {
    $check = $conn->prepare("SELECT 1 FROM Users WHERE username = ?");
    $check->bind_param('s', $username);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) { $check->close(); break; }
    $check->close();
    $try++;
    $username = $base . $try;
    if ($try > 50) break;
  }

  $name  = $g['name'] ?? $username;
  $hash  = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

  $ins = $conn->prepare("INSERT INTO Users (username, email, password_hash, name) VALUES (?, ?, ?, ?)");
  $ins->bind_param('ssss', $username, $email, $hash, $name);
  if (!$ins->execute()) {
    $ins->close();
    return false;
  }
  $newId = $ins->insert_id;
  $ins->close();

  // Đăng nhập
  $_SESSION['user_id']  = (int)$newId;
  $_SESSION['username'] = $username;
  $_SESSION['email']    = $email;
  return true;
}
