<?php
// app/views/layout/blank.php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="icon" href="<?= IMAGE_PATH ?>/Logo.svg">
  <?php foreach ($css as $href): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
  <?php endforeach; ?>
</head>
<body>
  <main>
    <?php
      if ($view && is_file($view)) {
        include $view;
      } else {
        echo '<section class="wall"><div class="card"><h1 class="title">View not found</h1></div></section>';
      }
    ?>
  </main>
</body>
</html>