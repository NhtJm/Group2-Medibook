<?php if (!function_exists('e')) { function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); } }
$msg = $data['message'] ?? 'You do not have permission to perform this action.';
$active = 'admin_clinics';
?>
<section class="admin-shell">
  <?php require __DIR__ . '/../admin/partials/sidebar.php'; ?>

  <div class="admin-main">
    <?php
      $title = 'Access denied';
      $searchAction = BASE_URL . 'index.php';
      $searchHidden = ['page' => 'admin_clinics'];
      require __DIR__ . '/../admin/partials/topbar.php';
    ?>

    <section class="panel" style="margin:16px;">
      <div class="flash error" style="margin:12px 16px;"><?= e($msg) ?></div>
      <div style="padding:16px;">
        <a class="btn" href="<?= e(BASE_URL . 'index.php?page=admin_clinics') ?>">← Back to Clinics</a>
      </div>
    </section>
  </div>
</section>