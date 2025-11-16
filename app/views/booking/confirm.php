<?php
// app/views/booking/confirm.php
if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}

$clinic = $data['clinic'] ?? null;
$doctor = $data['doctor'] ?? null;
$when = $data['when'] ?? null;
$slotId = $data['slot_id'] ?? null;

$back = BASE_URL . 'index.php?page=doctor'
  . '&clinic=' . (int) ($clinic['office_id'] ?? $_GET['clinic'] ?? 0)
  . '&doc=' . (int) ($doctor['doctor_id'] ?? $_GET['doc'] ?? 0);

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
            <div class="tile-title"><?= e($doctor['name'] ?? 'Doctor') ?>
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

      <!-- When -->
      <!-- When -->
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

    <form class="conf-actions" method="post" action="#">
      <?php if ($slotId): ?><input type="hidden" name="slot" value="<?= (int) $slotId ?>"><?php endif; ?>
      <button class="btn-confirm" type="submit">Confirm</button>
    </form>
  </div>
</section>