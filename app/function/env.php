<?php
// app/function/env.php
// Loader .env đơn giản, không cần Composer
function env_load(string $path = null): void {
  $root = $path ?: dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
  if (!is_file($root)) return;

  $lines = file($root, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;

    $pos = strpos($line, '=');
    if ($pos === false) continue;

    $key = trim(substr($line, 0, $pos));
    $val = trim(substr($line, $pos + 1));

    // Bỏ dấu nháy nếu có
    if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
        (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
      $val = substr($val, 1, -1);
    }

    // Set vào môi trường
    putenv("$key=$val");
    $_ENV[$key] = $val;
    $_SERVER[$key] = $val;
  }
}
