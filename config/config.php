<?php
// config/config.php

// Nạp .env (dùng loader bạn đã tạo ở app/function/env.php)
require_once __DIR__ . '/../app/function/env.php';
env_load(); // đọc .env vào getenv()/$_ENV

// --- (base URL) ---
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir  = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/\\');

// BASE_URL point to public
define('BASE_URL', 'http://' . $host . $dir . '/public' . '/');

// --- Resource paths ---
define('ASSET_URL',   BASE_URL . 'assets/');   // public/assets/
define('STYLE_PATH',  BASE_URL . 'css/');      // public/assets/css/
define('SCRIPT_PATH', BASE_URL . 'js/');       // public/assets/js/
define('IMAGE_PATH',  BASE_URL . 'images/');   // public/assets/images/

// === Google OAuth: đọc từ .env ===
define('GOOGLE_CLIENT_ID',     getenv('GOOGLE_CLIENT_ID')     ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
// Redirect URL phải public được truy cập (đang dùng front controller)
define('GOOGLE_REDIRECT_URI',  rtrim(BASE_URL, '/') . '/index.php?page=google_callback');
// scope yêu cầu email + profile
define('GOOGLE_OAUTH_SCOPE',   'openid email profile');

// (tuỳ chọn) Nếu muốn dùng ở nơi khác như hằng số:
if (!defined('MEDIBOOK_DEV_API_KEY')) {
  define('MEDIBOOK_DEV_API_KEY', getenv('MEDIBOOK_DEV_API_KEY') ?: 'dev-key-123');
}

function asset($path) {
  return rtrim(ASSET_URL, '/') . '/' . ltrim($path, '/');
}
