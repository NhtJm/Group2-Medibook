<?php
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$scheme = $https ? 'https://' : 'http://';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir    = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');  // "" or "/subdir"

define('BASE_URL',  rtrim($scheme.$host.$dir, '/') . '/');       // always ends with '/'
define('ASSET_URL', BASE_URL . 'public/assets/');                // ends with '/'

function asset($path){ return ASSET_URL . ltrim($path, '/'); }   // safe join
define('STYLE_PATH', ASSET_URL . '/css');    // .../public/assets/css
define('SCRIPT_PATH', ASSET_URL . '/js');    // .../public/assets/js
define('IMAGE_PATH', ASSET_URL . '/images'); // .../public/assets/images