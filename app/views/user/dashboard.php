<?php
// app/views/user/dashboard.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../db.php';

// Lấy mysqli connection từ db.php (hỗ trợ Singleton hoặc $conn global)
if (!isset($conn) || !$conn) {
  if (class_exists('Database')) {
    $conn = Database::get_instance();
  } else {
    // global $conn;
  }
}

if (!function_exists('h')) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

$active     = 'dashboard';            // nếu header/menu có highlight tab
$page_title = 'Dashboard - MediBook'; // nếu home.php có dùng $page_title
$is_dashboard = true;                 // flag để home.php (nếu có kiểm tra) biết đây là dashboard

include __DIR__ . '/../home.php';
