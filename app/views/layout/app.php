<?php
// app/views/layout/app.php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <link rel="icon" href="<?= IMAGE_PATH ?>/Logo.svg">
  <?php foreach ($css as $href): ?>
    <link rel="stylesheet" href="<?= e($href) ?>">
  <?php endforeach; ?>
</head>
<body>
  <?php require __DIR__ . '/partials/header.php'; ?>

  <main>
    <?php
      if ($view && is_file($view)) {
        require $view;
      } else {
        echo '<section class="wall"><div class="card"><h1 class="title">View not found</h1></div></section>';
      }
    ?>
  </main>

  <?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>