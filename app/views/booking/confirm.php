<?php
// app/views/booking/confirm.php
if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}
$flash = $data['flash'] ?? null;
$flashType = $data['flash_type'] ?? 'info';
$redirectTo = $data['redirect_to'] ?? null;
$redirectAfter = (int) ($data['redirect_after'] ?? 0);
$user = current_user();
$clinic = $data['clinic'] ?? null;
$doctor = $data['doctor'] ?? null;
$when = $data['when'] ?? null;
$slotId = $data['slot_id'] ?? null;

// ✅ use the same login state as the rest of the app
$me = function_exists('current_user') ? current_user() : ($_SESSION['user'] ?? null);
$isLoggedIn = !empty($me);

// back link
$back = BASE_URL . 'index.php?page=doctor'
  . '&clinic=' . (int) ($clinic['office_id'] ?? $_GET['clinic'] ?? 0)
  . '&doc=' . (int) ($doctor['doctor_id'] ?? $_GET['doc'] ?? 0);

// where to land after successful insert
$appointmentsUrl = BASE_URL . 'index.php?page=appointments';

// confirm endpoint (+slot)
$confirmUrl = BASE_URL . 'index.php?page=confirm' . ($slotId ? '&slot=' . (int) $slotId : '');
$confirmAction = $confirmUrl . '&next=' . rawurlencode($appointmentsUrl);

// 🔁 not-logged-in bounce: login -> return to confirm with do=confirm -> auto insert -> appointments
$loginNext = $confirmUrl . '&do=confirm&next=' . rawurlencode($appointmentsUrl);
$loginUrl = BASE_URL . 'index.php?page=login&next=' . rawurlencode($loginNext);

// avatar initials
$ini = $doctor ? ($doctor['initials'] ?? 'DR') : 'DR';
?>
<section class="conf-wrap">
  <a class="conf-back" href="<?= e($back) ?>">Back to time selection</a>

  <div class="conf-card">
    <div class="conf-logo"><img src="<?= IMAGE_PATH ?>/logo.png" alt="MediBook"></div>
    <h1 class="conf-title">Appointment Summary</h1>

    <div class="conf-grid">
      <!-- Doctor -->
      <div class="conf-tile">
        <div class="tile-left">
          <div class="avatar-lg"><?= e($ini) ?></div>
          <div>
            <div class="tile-title">
              <?= e($doctor['name'] ?? 'Doctor') ?>
              <?php if (!empty($doctor['degree'])): ?> - <?= e($doctor['degree']) ?><?php endif; ?>
            </div>
            <div class="tile-sub"><?= e($doctor['specialty'] ?? 'Medical Doctor') ?></div>
          </div>
        </div>
      </div>

      <!-- Clinic -->
      <div class="conf-tile">
        <div class="tile-left">
          <?php if (!empty($clinic['logo'])): ?>
            <img class="avatar-img" src="<?= e($clinic['logo']) ?>" alt="<?= e($clinic['name'] ?? 'Clinic') ?>">
          <?php else: ?>
            <div class="avatar-img"></div>
          <?php endif; ?>
          <div>
            <div class="tile-title"><?= e($clinic['name'] ?? 'Clinic') ?></div>
            <div class="tile-sub"><?= e($clinic['address'] ?? '') ?></div>
            <?php if (!empty($clinic['phone'])): ?>
              <div class="tile-links">
                <a href="tel:<?= e(preg_replace('/[^\d+]/', '', $clinic['phone'])) ?>"><?= e($clinic['phone']) ?></a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="conf-tile conf-tile--wide">
        <div class="tile-title">Your time</div>
        <div class="chips">
          <?php if (!empty($when['date_label'])): ?>
            <span class="chip chip--date"><?= e($when['date_label']) ?></span>
          <?php endif; ?>
          <?php if (!empty($when['time_label'])): ?>
            <span class="chip chip--time"><?= e($when['time_label']) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (current_user()): ?>
      <?php
      $confirmUrl = BASE_URL . 'index.php?page=confirm'
        . ($slotId ? '&slot=' . (int) $slotId : '');
      $go = $confirmUrl . '&do=confirm&next='
        . rawurlencode(BASE_URL . 'index.php?page=appointments');
      ?>
      <div class="conf-actions">
        <a class="btn-confirm" href="<?= e($go) ?>">Confirm</a>
      </div>
    <?php else: ?>
      <?php
      $confirmUrl = BASE_URL . 'index.php?page=confirm'
        . ($slotId ? '&slot=' . (int) $slotId : '');
      $loginNext = $confirmUrl . '&do=confirm&next='
        . rawurlencode(BASE_URL . 'index.php?page=appointments');
      $loginUrl = BASE_URL . 'index.php?page=login&next=' . rawurlencode($loginNext);
      ?>
      <div class="conf-actions">
        <a class="btn-confirm" href="<?= e($loginUrl) ?>">Confirm</a>
      </div>
    <?php endif; ?>


    <?php if ($flash): ?>
      <p class="notice notice--<?= e($flashType) ?>" role="status"><?= e($flash) ?></p>
      <?php if ($redirectTo && $redirectAfter > 0): ?>
        <script>
          setTimeout(function () { window.location.href = "<?= e($redirectTo) ?>"; }, <?= (int) $redirectAfter ?>);
        </script>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($data['error'])): ?>
      <p class="notice notice--error" role="alert"><?= e($data['error']) ?></p>
    <?php endif; ?>
  </div>
</section>