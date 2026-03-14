<?php
// app/function/notify_mailer.php
// Mục tiêu: dựng HTML/subject từ template + ghi outbox (DB nếu có, nếu không thì log file)

if (!function_exists('h')) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

/** Tập template mẫu */
function notify_templates(): array {
  return [
    'generic' => [
      'subject' => fn($d) => $d['subject'] ?? 'Notification',
      'html' => function($d){
        $title = $d['title'] ?? 'Notification';
        $body  = $d['message'] ?? 'Hello from MediBook.';
        return "<h2 style='margin:0 0 12px'>{$title}</h2><p>{$body}</p>";
      }
    ],
    'appointment' => [
      'subject' => fn($d) => 'Your appointment is confirmed',
      'html' => function($d){
        $name = h($d['name'] ?? 'Patient');
        $doctor = h($d['doctor'] ?? 'Doctor');
        $office = h($d['office'] ?? 'Clinic');
        $time = h($d['time'] ?? '—');
        $code = h($d['code'] ?? '');
        return "
          <h2 style='margin:0 0 12px'>Appointment Confirmed</h2>
          <p>Hi <strong>{$name}</strong>, your appointment details:</p>
          <ul>
            <li>Doctor: <strong>{$doctor}</strong></li>
            <li>Office: <strong>{$office}</strong></li>
            <li>Time: <strong>{$time}</strong></li>
            ".($code ? "<li>Confirmation Code: <strong>{$code}</strong></li>" : "")."
          </ul>
          <p>See you soon!</p>
        ";
      }
    ],
    'password_reset' => [
      'subject' => fn($d) => 'Password reset code',
      'html' => function($d){
        $otp = h($d['otp'] ?? '------');
        return "<p>Your password reset code is: <strong style='font-size:20px'>{$otp}</strong></p>";
      }
    ],
  ];
}

/** Kết xuất subject + html từ template hoặc từ chuỗi truyền vào */
function notify_build_message(array $payload): array {
  $tpls = notify_templates();
  $template = $payload['template'] ?? 'generic';

  // Cho phép FE truyền thẳng subject/html để preview nhanh
  if (!empty($payload['raw_html'])) {
    $subject = $payload['subject'] ?? 'Notification';
    $html    = $payload['raw_html'];
    return [$subject, $html];
  }

  $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
  if (isset($tpls[$template])) {
    $subject = ($tpls[$template]['subject'])($data);
    $html    = ($tpls[$template]['html'])($data);
  } else {
    // fallback generic
    $subject = $payload['subject'] ?? 'Notification';
    $title   = $payload['title']   ?? 'Notification';
    $message = $payload['message'] ?? 'Hello from MediBook.';
    $html = "<h2 style='margin:0 0 12px'>".h($title)."</h2><p>".h($message)."</p>";
  }
  return [$subject, $html];
}

/**
 * Ghi outbox: nếu có Database::get_instance() thì insert vào DB;
 * nếu không => ghi file storage/email_outbox.log
 * Trả về mảng [ok(bool), id(string), where('db'|'file'), preview(array)]
 */
function notify_store_outbox(array $meta, string $subject, string $html): array {
  $id = 'outbox_'.date('Ymd_His').'_'.substr(md5(json_encode($meta).mt_rand()),0,8);
  $preview = [
    'to'      => $meta['to'] ?? null,
    'name'    => $meta['name'] ?? null,
    'subject' => $subject,
    'template'=> $meta['template'] ?? null,
    'data'    => $meta['data'] ?? null,
    'html'    => $html
  ];

  // DB path
  try {
    if (class_exists('Database')) {
      $conn = Database::get_instance();
      $sql = "INSERT INTO email_outbox (to_email, to_name, subject, template, payload_json, html_body, status, meta)
              VALUES (?,?,?,?,JSON_OBJECT(),?, 'queued', JSON_OBJECT())";
      $stmt = $conn->prepare($sql);
      if (!$stmt) throw new Exception($conn->error);

      $to_email = $meta['to'] ?? '';
      $to_name  = $meta['name'] ?? null;
      $template = $meta['template'] ?? null;
      $payload_json = json_encode($meta, JSON_UNESCAPED_UNICODE);
      $html_body = $html;

      // Vì câu SQL đã cố định JSON_OBJECT() cho payload/meta, ta đổi sang bind payload/meta trực tiếp:
      $sql = "INSERT INTO email_outbox (to_email,to_name,subject,template,payload_json,html_body,status,meta)
              VALUES (?,?,?,?,?,?, 'queued', ?)";
      $stmt = $conn->prepare($sql);
      if (!$stmt) throw new Exception($conn->error);
      $meta_json = json_encode(['via'=>'mock_api','req_id'=>$id], JSON_UNESCAPED_UNICODE);
      $stmt->bind_param('sssssss', $to_email, $to_name, $subject, $template, $payload_json, $html_body, $meta_json);
      if (!$stmt->execute()) throw new Exception($stmt->error);

      return ['ok'=>true, 'id'=>$id, 'where'=>'db', 'preview'=>$preview];
    }
  } catch (Throwable $e) {
    // fallthrough to file log
  }

  // File log path
  $dir = __DIR__ . '/../../storage';
  if (!is_dir($dir)) @mkdir($dir, 0777, true);
  $file = $dir . '/email_outbox.log';
  $line = json_encode(['id'=>$id,'at'=>date('c'),'payload'=>$preview], JSON_UNESCAPED_UNICODE);
  @file_put_contents($file, $line.PHP_EOL, FILE_APPEND);

  return ['ok'=>true, 'id'=>$id, 'where'=>'file', 'preview'=>$preview];
}
