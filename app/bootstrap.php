<?php
// app/bootstrap.php
declare(strict_types=1);

if (defined('APP_BOOTSTRAPPED')) return;
define('APP_BOOTSTRAPPED', true);

// Detect whether a session is already active (e.g., session.auto_start=1 or other code)
$sessionActive = (session_status() === PHP_SESSION_ACTIVE);

// Only set INI/cookie params BEFORE session_start()
if (!$sessionActive) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',      // make cookie valid for ALL routes
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
} else {
    // Session already active: DO NOT call ini_set()/session_set_cookie_params()
    // If you want to enforce cookie attributes anyway, re-issue the cookie (no warnings)
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    if (!headers_sent()) {
        setcookie(session_name(), session_id(), [
            'expires'  => 0,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

// Load config AFTER session is ready (no output!)
require_once __DIR__ . '/../config/config.php';

/* ---------- Helpers ---------- */
function current_user(): ?array {
    // Normalize: accept either ['user'] or ['user_id']
    if (isset($_SESSION['user']) && is_array($_SESSION['user'])) return $_SESSION['user'];
    if (isset($_SESSION['user_id'])) return ['user_id' => (int)$_SESSION['user_id']];
    return null;
}
function is_logged_in(): bool { return current_user() !== null; }

function require_auth_or_redirect(): void {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . 'index.php?page=login');
        exit;
    }
}

if (!function_exists('e')) {
  function e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

function require_role(array $roles): void {
  $u = current_user();
  $role = $u['role'] ?? null;
  if (!$role || !in_array($role, $roles, true)) {
    header('Location: ' . BASE_URL . 'index.php?page=dashboard');
    exit;
  }
}