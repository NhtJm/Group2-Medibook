<?php
// app/function/send_mail.php
// 1 hàm duy nhất: gửi email (SMTP Gmail) với cấu hình CỐ ĐỊNH

require_once __DIR__ . '/env.php';

require_once __DIR__ . '/../../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../vendor/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

function send_mail(string $to, string $name, string $subject, string $html): array {
  // Lấy cấu hình từ .env
  $host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
  $port = (int)(getenv('MAIL_PORT') ?: 587);
  $user = getenv('MAIL_USERNAME') ?: '';
  $pass = getenv('MAIL_PASSWORD') ?: '';
  $from = getenv('MAIL_FROM') ?: $user;
  $fromName = getenv('MAIL_FROM_NAME') ?: 'MediBook';

  try {
    $mail = new PHPMailer(true);

    // SMTP
    $mail->isSMTP();
    $mail->Host       = $host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $user;
    $mail->Password   = $pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $port;

    // Nội dung
    $mail->setFrom($from, $fromName);
    $mail->addAddress($to, $name);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $html;

    $mail->send();
    return ['ok' => true];
  } catch (\Throwable $e) {
    return ['ok' => false, 'error' => $e->getMessage()];
  }
}
