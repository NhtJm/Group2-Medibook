<?php
// --- (base URL) ---
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir  = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

// BASE_URL point to public
define('BASE_URL', 'http://' . $host . $dir . '/public' . '/');

// --- Resource paths ---
define('ASSET_URL', BASE_URL . 'assets/');     // public/assets/
define('STYLE_PATH',  BASE_URL . 'css/');     // public/assets/css/
define('SCRIPT_PATH', BASE_URL . 'js/');      // public/assets/js/
define('IMAGE_PATH',  BASE_URL . 'images/');  // public/assets/images/

// Google OAuth
define('GOOGLE_CLIENT_ID',     '823718931471-5pkqnju02peal0pg41pbtk3j7pq3b77u.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');

// Redirect URL phải public được truy cập (đang dùng front controller)
define('GOOGLE_REDIRECT_URI',  rtrim(BASE_URL, '/') . '/index.php?page=google_callback');

// scope yêu cầu email + profile
define('GOOGLE_OAUTH_SCOPE',   'openid email profile');

function asset($path) {
    return rtrim(ASSET_URL, '/') . '/' . ltrim($path, '/');
}
