<?php
// app/views/search/clinics.php

if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

// Expect $data from controller (router will pass it in)
$data     = $data ?? [];
$clinics  = $data['clinics'] ?? [];
$total    = (int)($data['total'] ?? 0);
$page     = (int)($data['page'] ?? 1);
$pages    = (int)($data['pages'] ?? 1);
$q        = (string)($data['q']   ?? '');
$loc      = (string)($data['loc'] ?? '');

$base = rtrim(BASE_URL, '/').'/index.php?page=clinics';
function qurl($base, $page, $q, $loc){
  $qs = http_build_query(array_filter([
    'p'   => $page,
    'q'   => $q,
    'loc' => $loc,
  ], fn($v) => $v !== null && $v !== ''));
  return $base . ($qs ? '&' . $qs : '');
}
?>

<section class="cl-hero">
  <div class="cl-wrap">
    <form class="search" method="get" action="<?= BASE_URL ?>index.php">
      <input type="hidden" name="page" value="clinics">
      <div class="search__field">
        <label>Doctor, Clinic or Specialty</label>
        <input type="text" name="q" placeholder="e.g., Dr. Clark, Cardiology, …" value="<?= e($q) ?>">
      </div>
      <div class="search__field">
        <label>Location</label>
        <input type="text" name="loc" placeholder="Where ?" value="<?= e($loc) ?>">
      </div>
      <button class="search__btn" type="submit">Search</button>
    </form>
  </div>
</section>

<section class="cl-results">
  <div class="cl-wrap">
    <div class="cl-count"><?= number_format($total) ?> result<?= $total===1?'':'s' ?></div>

    <?php if (empty($clinics)): ?>
      <p>No clinics found. Try different keywords.</p>
    <?php else: ?>
      <?php foreach ($clinics as $c): ?>
        <?php
          $id     = (int)$c['office_id'];
          $name   = (string)$c['name'];
          $addr   = (string)($c['address'] ?? '');
          $logo   = (string)($c['logo'] ?? '');
          $badge  = (string)$c['badge'];
          $slots  = $c['slots'] ?? [];
          $isOpen = !empty($c['is_open']);

          // Prepare day label for the first row of slots
          $dayLabel = $slots[0]['day_label'] ?? null;
          // Build up to 6 slot tags (time only)
          $slotTags = [];
          foreach ($slots as $s) {
            $slotTags[] = '<a href="#" class="slot">'. e($s['time_label']) .'</a>';
          }
        ?>
        <article class="clinic">
          <div class="clinic__row">
            <div class="clinic__left">
              <?php if ($logo): ?>
                <div class="clinic__logo">
                  <img src="<?= e($logo) ?>" alt="<?= e($name) ?>">
                </div>
              <?php else: ?>
                <div class="clinic__logo"><?= e($badge) ?></div>
              <?php endif; ?>
              <div class="clinic__meta">
                <h3 class="clinic__name"><?= e($name) ?></h3>
                <?php if ($addr): ?><p class="clinic__addr"><?= e($addr) ?></p><?php endif; ?>
                <p class="clinic__status <?= $isOpen ? 'is-open' : 'is-closed' ?>">
                  <span class="dot"></span>
                  <?= $isOpen ? 'Open · Slots today' : ('Closed · Next ' . e($dayLabel ?? 'availability')) ?>
                </p>
              </div>
            </div>
            <a class="clinic__cta" href="<?= BASE_URL ?>index.php?page=clinic&id=<?= $id ?>">See</a>
          </div>

          <div class="clinic__slots">
            <?php if ($dayLabel): ?><span class="day"><?= e($dayLabel) ?></span><?php endif; ?>
            <?= implode("", $slotTags) ?>
            <?php if (count($slots) >= 6): ?><a href="#" class="more">+ more</a><?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>

      <?php if ($pages > 1): ?>
        <nav class="pager" aria-label="Clinics pages">
          <a class="pager__item <?= $page<=1 ? 'is-disabled':'' ?>"
             href="<?= $page<=1 ? 'javascript:void(0)' : e(qurl($base, $page-1, $q, $loc)) ?>">‹</a>

          <?php
            // Windowed pager (7)
            $win=7;
            $start = max(1, $page - intdiv($win-1,2));
            $end   = min($pages, $start + $win - 1);
            $start = max(1, $end - $win + 1);
            for ($i=$start; $i<=$end; $i++):
          ?>
            <a class="pager__item <?= $i===$page?'is-active':'' ?>"
               <?= $i===$page ? 'aria-current="page"':'' ?>
               href="<?= $i===$page ? 'javascript:void(0)' : e(qurl($base, $i, $q, $loc)) ?>">
               <?= $i ?>
            </a>
          <?php endfor; ?>

          <a class="pager__item <?= $page>=$pages ? 'is-disabled':'' ?>"
             href="<?= $page>=$pages ? 'javascript:void(0)' : e(qurl($base, $page+1, $q, $loc)) ?>">›</a>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>