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

function asset($path) {
    return rtrim(ASSET_URL, '/') . '/' . ltrim($path, '/');
}
