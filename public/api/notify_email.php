<?php
// public/api/notify_email.php
declare(strict_types=1);

/**
 * Endpoint: gửi email thông báo cho user.
 * - Input: JSON POST { to, name?, template?, data?, subject?, raw_html?, dry_run? }
 * - Header: X-Api-Key: dev-key-123 (dev)  // có thể đổi qua MEDIBOOK_DEV_API_KEY trong .env
 * - Hành vi:
 *   + dry_run=true  -> chỉ render preview, KHÔNG gửi.
 *   + mặc định      -> GỬI THẬT bằng SMTP (PHPMailer) với cấu hình lấy từ .env
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');           // Dev-only CORS. Khóa khi lên prod
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204); exit;
}

// ====== Project includes ======
require_once __DIR__ . '/../../config/config.php';            // BASE_URL, ...
$maybeDb = __DIR__ . '/../../app/db.php';
if (file_exists($maybeDb)) require_once $maybeDb;

// .env loader
require_once __DIR__ . '/../../app/function/env.php';
env_load(); // nạp .env vào getenv()

// Helper để build subject/html từ template hoặc raw_html
require_once __DIR__ . '/../../app/function/notify_mailer.php';

// ====== Nạp PHPMailer (không dùng Composer) ======
require_once __DIR__ . '/../../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../vendor/phpmailer/src/Exception.php';

function json_error(int $code, string $message, array $extra = []) {
  http_response_code($code);
  echo json_encode(['ok' => false, 'error' => $message] + $extra, JSON_UNESCAPED_UNICODE);
  exit;
}

// ====== API key check (đọc từ .env nếu có) ======
$DEV_API_KEY = getenv('MEDIBOOK_DEV_API_KEY') ?: 'dev-key-123';
$hdrKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($DEV_API_KEY && $hdrKey !== $DEV_API_KEY) {
  json_error(401, 'Unauthorized (missing/invalid X-Api-Key)');
}

// ====== Parse JSON body ======
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
  json_error(400, 'Invalid JSON body');
}

// ====== Validate input ======
$to = trim($payload['to'] ?? '');
if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
  json_error(422, 'Field "to" must be a valid email');
}
$name     = trim((string)($payload['name'] ?? ''));
$template = trim((string)($payload['template'] ?? 'generic'));
$dryRun   = (bool)($payload['dry_run'] ?? false);
$data     = is_array($payload['data'] ?? null) ? $payload['data'] : [];
$subject  = $payload['subject'] ?? null;
$rawHtml  = $payload['raw_html'] ?? null;

// ====== Rate limit thô sơ theo email (1 req/10s/email) ======
$rlDir = sys_get_temp_dir() . '/medibook_rl';
@mkdir($rlDir, 0777, true);
$rlKey = $rlDir . '/email_' . md5(strtolower($to));
$now   = time();
$last  = is_file($rlKey) ? (int)@file_get_contents($rlKey) : 0;
if ($now - $last < 10) {
  json_error(429, 'Too Many Requests: please wait a few seconds');
}
@file_put_contents($rlKey, (string)$now);

// ====== Build subject + html từ template/raw ======
if ($rawHtml) { $payload['raw_html'] = $rawHtml; }
if ($subject) { $payload['subject'] = $subject; }
$payload['template'] = $template;
$payload['data']     = $data;

[$finalSubject, $finalHtml] = notify_build_message($payload);

// ====== DRY RUN: chỉ preview, KHÔNG gửi ======
if ($dryRun === true) {
  echo json_encode([
    'ok'      => true,
    'dry_run' => true,
    'preview' => [
      'to'       => $to,
      'name'     => $name ?: null,
      'subject'  => $finalSubject,
      'template' => $template,
      'data'     => $data,
      'html'     => $finalHtml
    ]
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// ====== SMTP config từ .env ======
$host     = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
$port     = (int)(getenv('MAIL_PORT') ?: 587);
$user     = getenv('MAIL_USERNAME') ?: '';
$pass     = getenv('MAIL_PASSWORD') ?: '';
$from     = getenv('MAIL_FROM') ?: $user;
$fromName = getenv('MAIL_FROM_NAME') ?: 'MediBook';

// ====== GỬI EMAIL THẬT BẰNG SMTP (PHPMailer) ======
try {
  $mail = new PHPMailer\PHPMailer\PHPMailer(true);

  // === Cấu hình SMTP theo .env ===
  $mail->isSMTP();
  $mail->Host       = $host;
  $mail->SMTPAuth   = true;
  $mail->Username   = $user;
  $mail->Password   = $pass;
  $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = $port;

  // === Nội dung thư ===
  $mail->setFrom($from, $fromName);
  $mail->addAddress($to, $name ?: '');
  $mail->isHTML(true);
  $mail->Subject = $finalSubject;
  $mail->Body    = $finalHtml;

  $mail->send();

  echo json_encode([
    'ok' => true,
    'where' => 'smtp',
    'preview' => [
      'to' => $to,
      'name' => $name ?: null,
      'subject' => $finalSubject,
      'template' => $template,
      'data' => $data,
      'html' => $finalHtml
    ]
  ], JSON_UNESCAPED_UNICODE);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'SMTP failed', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
  exit;
}
