<?php
// app/bootstrap.php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

/* -------- Helpers (no HTML here) -------- */
function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function require_auth_or_redirect(): void {
  if (!current_user()) {
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
  }
}

/** Escape HTML */
function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function require_role(array $roles): void {
  $u = $_SESSION['user'] ?? null;
  if (!$u || !in_array($u['role'] ?? null, $roles, true)) {
    header('Location: ' . BASE_URL . 'index.php?page=dashboard');
    exit;
  }
}