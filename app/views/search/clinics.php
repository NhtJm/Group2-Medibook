<?php
// app/views/search/clinics.php

if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

$d = $data ?? [
  'slug'      => '',
  'specialty' => null,   // ['name'=>..., ...] or null
  'offices'   => [],
  'page'      => 1,
  'per'       => 10,
  'total'     => 0,
  'pages'     => 1,
  'q'         => '',
  'loc'       => '',
];

// initials fallback for circle logo
if (!function_exists('initials')) {
  function initials(string $name): string {
    $w = preg_split('/\s+/', trim($name));
    $first = mb_substr($w[0] ?? '', 0, 1);
    $last  = mb_substr($w[count($w)-1] ?? '', 0, 1);
    return mb_strtoupper($first . $last);
  }
}

// pager url (preserve filters)
$base = rtrim(BASE_URL, '/') . '/index.php?page=clinics';
function page_url(string $base, array $params, int $p): string {
  $params['p'] = $p;
  $qs = http_build_query(array_filter($params, fn($v)=> $v !== '' && $v !== null));
  return $base . ($qs ? '&' . $qs : '');
}

// sticky values
$q   = (string)($d['q']   ?? '');
$loc = (string)($d['loc'] ?? '');

// Are we browsing by category (no active search)?
$bySpec = ($d['specialty'] && $q === '' && $loc === '');
?>

<!-- SEARCH PILL (matches your .cl-hero / .search CSS) -->
<!-- SEARCH PILL (same as Home; suggestions enabled) -->
<section class="cl-hero">
  <div class="cl-wrap">
    <form class="search search--has-suggest" method="get" action="<?= BASE_URL ?>index.php" autocomplete="off">
      <input type="hidden" name="page" value="clinics">

      <div class="search__field">
        <label>Doctor, Clinic or Specialty</label>
        <input id="search-q" name="q" type="text"
               placeholder="e.g., Dr. Clark, Cardiology, …"
               value="<?= e($d['q'] ?? '') ?>" autocomplete="off">
      </div>

      <div class="search__field">
        <label>Location</label>
        <input id="search-loc" name="loc" type="text"
               placeholder="Where ?" value="<?= e($d['loc'] ?? '') ?>" autocomplete="off">
      </div>

      <button id="search-btn" class="search__btn" type="submit">Search</button>

      <!-- same suggestion container/IDs as Home -->
      <div id="search-suggest" class="search__suggest hidden" role="listbox" aria-label="Search suggestions"></div>
    </form>
  </div>
</section>
<section class="cl-results">
  <div class="cl-wrap">

    <?php if ((int)$d['total'] === 0): ?>
      <p>
        <?php if ($q !== '' || $loc !== ''): ?>
          No clinics found for your search.
        <?php elseif ($d['specialty']): ?>
          No approved offices found for this specialty.
        <?php else: ?>
          No clinics available yet.
        <?php endif; ?>
      </p>

    <?php else: ?>
      <div class="cl-count">
        <?= number_format((int)$d['total']) ?> result<?= (int)$d['total']===1?'':'s' ?>
        <?php if ($bySpec): ?>
          for <strong><?= e($d['specialty']['name']) ?></strong>
        <?php elseif ($q !== '' || $loc !== ''): ?>
          for <em><?= e(trim($q . ($loc ? ' · '.$loc : ''))) ?></em>
        <?php else: ?>
          (all facilities)
        <?php endif; ?>
      </div>

      <?php foreach ($d['offices'] as $o): ?>
        <?php
          $id    = (int)$o['office_id'];
          $name  = (string)$o['name'];
          $addr  = (string)($o['address'] ?? '');
          $logo  = (string)($o['logo']    ?? '');
          $open  = !empty($o['is_open']);
          $slots = $o['slots'] ?? [];
          $dayLabel = $slots[0]['day_label'] ?? null;

          $slotTags = [];
          foreach ($slots as $s) { $slotTags[] = '<a href="#" class="slot">'. e($s['time_label']) .'</a>'; }
        ?>
        <article class="clinic">
          <div class="clinic__row">
            <div class="clinic__left">
              <?php if ($logo !== ''): ?>
                <div class="clinic__logo"><img src="<?= e($logo) ?>" alt="<?= e($name) ?>"></div>
              <?php else: ?>
                <div class="clinic__logo"><?= e(initials($name)) ?></div>
              <?php endif; ?>

              <div class="clinic__meta">
                <h3 class="clinic__name"><?= e($name) ?></h3>
                <?php if ($addr): ?><p class="clinic__addr"><?= e($addr) ?></p><?php endif; ?>
                <p class="clinic__status <?= $open ? 'is-open' : 'is-closed' ?>">
                  <span class="dot"></span>
                  <?= $open ? 'Open · Slots today' : ('Closed · Next ' . e($dayLabel ?? 'availability')) ?>
                </p>
              </div>
            </div>
            <a class="clinic__cta" href="<?= BASE_URL ?>index.php?page=clinic&id=<?= $id ?>">See</a>
          </div>

          <div class="clinic__slots">
            <?php if ($dayLabel): ?><span class="day"><?= e($dayLabel) ?></span><?php endif; ?>
            <?= implode('', $slotTags) ?>
            <?php if (count($slots) >= 6): ?><a href="#" class="more">+ more</a><?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>

      <?php if ((int)$d['pages'] > 1): ?>
        <?php
          // Persist specialty ONLY when browsing by category (no q/loc).
          $params = [];
          if ($bySpec)           $params['specialty'] = $d['slug'];
          if ($q   !== '')       $params['q']   = $q;
          if ($loc !== '')       $params['loc'] = $loc;
        ?>
        <nav class="pager" aria-label="Clinics pages">
          <a class="pager__item <?= $d['page']<=1 ? 'is-disabled':'' ?>"
             href="<?= $d['page']<=1 ? 'javascript:void(0)' : page_url($base, $params, $d['page']-1) ?>"
             aria-label="Previous page">‹</a>

          <?php
            $win=7;
            $start=max(1,$d['page']-intdiv($win-1,2));
            $end=min((int)$d['pages'],$start+$win-1);
            $start=max(1,$end-$win+1);
            for($i=$start;$i<=$end;$i++):
          ?>
            <a class="pager__item <?= $i===(int)$d['page'] ? 'is-active':'' ?>"
               <?= $i===(int)$d['page'] ? 'aria-current="page"':'' ?>
               href="<?= $i===(int)$d['page'] ? 'javascript:void(0)' : page_url($base, $params, $i) ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>

          <a class="pager__item <?= $d['page']>=$d['pages'] ? 'is-disabled':'' ?>"
             href="<?= $d['page']>=$d['pages'] ? 'javascript:void(0)' : page_url($base, $params, $d['page']+1) ?>"
             aria-label="Next page">›</a>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<!-- Suggest JS (same IDs your search.js expects) -->
<script>const BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>js/search.js"></script>