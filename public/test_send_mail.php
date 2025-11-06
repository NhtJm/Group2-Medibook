<?php
require_once __DIR__ . '/../app/function/send_mail.php';

$res = send_mail(
  'hungyle123@gmail.com',         // email người nhận
  'Test User',                    // tên người nhận
  'Appointment Confirmed',        // tiêu đề
  '<p>Hello from MediBook.</p>'   // nội dung HTML
);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($res, JSON_UNESCAPED_UNICODE);
