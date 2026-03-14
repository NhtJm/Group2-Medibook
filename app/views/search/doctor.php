<?php
if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}
function vi_date_label(?string $ymd): string
{
  if (!$ymd)
    return '';
  $dt = DateTime::createFromFormat('Y-m-d', $ymd);
  if (!$dt)
    return $ymd;
  $w = (int) $dt->format('w');            // 0=CN, 1=Mon ... 6=Sat
  $weekday = $w === 0 ? 'CN' : 'Th ' . ($w + 1);
  return $weekday . ', ' . $dt->format('d/m/Y');
}
$clinic = $data['clinic'] ?? null;
$doctor = $data['doctor'] ?? null;
$month = $data['month'] ?? null;
$counts = $data['counts'] ?? [];
$selected = $data['selected'] ?? null;
$slots = $data['slots'] ?? [];

$rating = 0.0;
$ratingPct = 0.0;
if ($clinic && isset($clinic['rating'])) {
  $rating = (float) $clinic['rating'];
  $rating = max(0.0, min(5.0, $rating));
  $ratingPct = $rating * 20; // 5 stars -> 100%
}
?>
<?php if (!$doctor): ?>
  <section class="wall">
    <div class="card">
      <h1 class="title">Doctor not found</h1>
    </div>
  </section>
<?php else: ?>
  <section class="clv-hero">
    <div class="clv-wrap">
      <div class="clv-card">
        <!-- Logo -->
        <div class="clv-card__logo" aria-hidden="true">
          <?php if (!empty($clinic['logo'])): ?>
            <img src="<?= e($clinic['logo']) ?>" alt="<?= e($clinic['name']) ?>">
          <?php else: ?>
            <span class="badge"><?= e($clinic['code'] ?? $clinic['badge'] ?? 'CL') ?></span>
          <?php endif; ?>
        </div>

        <!-- Name + address + status + rating -->
        <div class="clv-card__meta">
          <h1 class="clv-card__name">
            <?= e($clinic['name']) ?>
            <?php if ($rating > 0): ?>
              <span class="rating" aria-label="Rating <?= number_format($rating, 1) ?>/5">
                <span class="rstars">
                  <span class="rstars-bg">★★★★★</span>
                  <span class="rstars-fg" style="width:<?= $ratingPct ?>%">★★★★★</span>
                </span>
                <span class="rating-num"><?= number_format($rating, 1) ?></span>
              </span>
            <?php endif; ?>
          </h1>

          <?php if (!empty($clinic['address'])): ?>
            <p class="clv-card__addr"><?= e($clinic['address']) ?></p>
          <?php endif; ?>

          <?php
          $open = !empty($clinic['is_open']);
          $hours = $clinic['hours_label'] ?? null;
          ?>
          <p class="clv-card__status <?= $open ? 'is-open' : 'is-closed' ?>">
            <span class="dot"></span><?= $open ? 'Open' : 'Closed' ?><?= $hours ? ' · ' . e($hours) : '' ?>
          </p>
        </div>

        <!-- Call button (shows number) -->
        <div class="clv-card__cta">
          <?php if (!empty($clinic['phone'])):
            $raw = trim((string) $clinic['phone']);
            $tel = preg_replace('/[^\d+]/', '', $raw); // tel: safe
            $disp = $raw;
            ?>
            <a class="btn-call" href="tel:<?= e($tel) ?>" aria-label="Call <?= e($disp) ?>">
              <span class="btn-call__label">Call</span>
              <svg class="ico ico-right" viewBox="0 0 24 24" aria-hidden="true">
                <path
                  d="M22 16.92v2a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.92 4.18 2 2 0 0 1 4.92 2h2a2 2 0 0 1 2 1.72c.12.9.32 1.78.59 2.63a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.45-1.45a2 2 0 0 1 2.11-.45c.85.27 1.73.47 2.63.59A2 2 0 0 1 22 16.92z" />
              </svg>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- WHITE SECTION (like doctors.php) -->
  <section class="clv-white">
    <div class="dv-wrap dv-grid">
      <!-- LEFT: clinic info (same component as doctors.php) -->
      <aside class="card dv-clinic">
        <div class="dv-clinic-head"><strong>Info</strong></div>
        <div class="dv-clinic-name">
          <span><?= e($clinic['name']) ?></span>
          <span class="dv-verified" title="Đã xác minh">✓</span>
        </div>
        <?php if (!empty($clinic['address'])): ?>
          <p class="dv-clinic-addr"><?= e($clinic['address']) ?></p>
        <?php endif; ?>
        <?php if (!empty($clinic['phone'])): ?>
          <p class="dv-clinic-addr">
            <a href="tel:<?= e(preg_replace('/\s+/', '', $clinic['phone'])) ?>">
              <?= e($clinic['phone']) ?>
            </a>
          </p>
        <?php endif; ?>
        <?php if (!empty($doctor)): ?>
          <hr class="dv-divider">

          <p class="dv-aside-kv">
            <strong>Chuyên khoa:</strong>
            <span class="muted"><?= e($doctor['specialty'] ?? '—') ?></span>
          </p>

          <p class="dv-aside-kv">
            <strong>Bác sĩ:</strong>
            <span class="muted">
              <?= e($doctor['name'] ?? '') ?>
              <?= !empty($doctor['degree']) ? ' - ' . e($doctor['degree']) : '' ?>
            </span>
          </p>
        <?php endif; ?>
        <p class="dv-clinic-addr" style="margin-top:8px">
          <a href="<?= BASE_URL ?>index.php?page=clinic&id=<?= (int) $clinic['office_id'] ?>">Quay lại cơ sở</a>
        </p>
      </aside>

      <!-- RIGHT: calendar + slots inside the same dv-panel shell -->
      <section class="card dv-panel">
        <h2 class="dv-title" style="margin-bottom:12px">Chọn lịch khám</h2>

        <div id="dv-root" data-clinic="<?= (int) ($clinic['office_id'] ?? 0) ?>"
          data-doc="<?= (int) ($doctor['doctor_id'] ?? ($doctor['id'] ?? 0)) ?>" data-ym="<?= e($month['ym']) ?>"
          data-today="<?= e((new DateTimeImmutable('today'))->format('Y-m-d')) ?>"
          data-selected="<?= e($selected ?? '') ?>">


          <!-- Calendar card -->
          <section class="dv-schedule" style="margin-bottom:14px">
            <div class="calbar">
              <a id="btnPrev" class="nav" aria-label="Prev month" data-go="<?= e($month['prev']) ?>"
                href="<?= BASE_URL ?>index.php?page=doctor&clinic=<?= (int) ($clinic['office_id'] ?? 0) ?>&doc=<?= (int) ($doctor['doctor_id'] ?? ($doctor['id'] ?? 0)) ?>&ym=<?= e($month['prev']) ?>">‹</a>
              <div id="calTitle" class="title"><?= e(strtoupper($month['name'])) ?></div>
              <a id="btnNext" class="nav" aria-label="Next month" data-go="<?= e($month['next']) ?>"
                href="<?= BASE_URL ?>index.php?page=doctor&clinic=<?= (int) ($clinic['office_id'] ?? 0) ?>&doc=<?= (int) ($doctor['doctor_id'] ?? ($doctor['id'] ?? 0)) ?>&ym=<?= e($month['next']) ?>">›</a>
            </div>

            <div class="cal">
              <div class="cal-head">
                <?php foreach (['CN', 'Hai', 'Ba', 'Tư', 'Năm', 'Sáu', 'Bảy'] as $n): ?>
                  <div><?= e($n) ?></div>
                <?php endforeach; ?>
              </div>

              <div id="calBody" class="cal-body">
                <?php
                $todayYmd = (new DateTimeImmutable('today'))->format('Y-m-d');
                $firstDow = (int) $month['firstDow'];
                $daysIn = (int) $month['daysInMonth'];
                $ym = $month['ym'];
                for ($i = 0; $i < $firstDow; $i++)
                  echo '<div class="cell empty"></div>';
                for ($d = 1; $d <= $daysIn; $d++) {
                  $date = sprintf('%s-%02d', $ym, $d);
                  $cnt = $counts[$date] ?? 0;

                  $isPast = ($date < $todayYmd);
                  $isToday = ($date === $todayYmd);

                  $cls = 'cell'
                    . ($cnt > 0 ? ' has' : '')
                    . ($selected === $date ? ' sel' : '')
                    . ($isPast ? ' past' : '')
                    . ($isToday ? ' today' : '');

                  $inner = '<div class="dnum">' . $d . '</div>'
                    . ($cnt > 0 ? '<div class="dsub">' . $cnt . ' slot' . ($cnt > 1 ? 's' : '') . '</div>' : '');

                  if ($isPast) {
                    echo '<div class="' . $cls . '" data-date="' . e($date) . '">' . $inner . '</div>';
                  } else {
                    $href = BASE_URL . 'index.php?page=doctor&clinic=' . (int) ($clinic['office_id'] ?? 0)
                      . '&doc=' . (int) ($doctor['doctor_id'] ?? ($doctor['id'] ?? 0))
                      . '&ym=' . e($ym) . '&date=' . $date;
                    echo '<a class="' . $cls . '" data-date="' . e($date) . '" href="' . e($href) . '">' . $inner . '</a>';
                  }
                }
                ?>
              </div>
            </div>
          </section>

          <!-- Slots list -->
          <section class="slots-card">
            <h3 id="slotTitle" class="h-title" style="margin-bottom:10px">
              <?= $selected ? ('Khung giờ · ' . e(vi_date_label($selected))) : 'Chọn một ngày để xem khung giờ' ?>
            </h3>
            <div id="slotGrid">
              <?php if ($selected && $slots): ?>
                <div class="slot-grid">
                  <?php foreach ($slots as $s):
                    $t = (new DateTime($s['start_time']))->format('H:i'); ?>
                    <a class="slot" href="<?= BASE_URL ?>index.php?page=confirm&slot=<?= (int) $s['slot_id'] ?>">
                      <?= e($t) ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php elseif ($selected): ?>
                <p>Không còn khung giờ trống trong ngày này.</p>
              <?php else: ?>
                <p>Hãy chọn một ngày trong lịch để tiếp tục.</p>
              <?php endif; ?>
            </div>
          </section>

        </div><!-- /#dv-root -->
      </section>
    </div>
  </section>
<?php endif; ?>
<script>
  (function () {
    const root = document.getElementById('dv-root');
    if (!root) return;
    const clinic = root.dataset.clinic;
    const doc = root.dataset.doc;

    async function fetchData(params) {
      const url = new URL('<?= BASE_URL ?>index.php', location.origin);
      url.searchParams.set('page', 'doctor');
      url.searchParams.set('ajax', '1');
      url.searchParams.set('clinic', clinic);
      url.searchParams.set('doc', doc);
      url.searchParams.set('ym', params.ym || root.dataset.ym);
      if (params.date) url.searchParams.set('date', params.date);

      // prevent browser caching of the JSON
      return (await fetch(url, {
        headers: { 'X-Requested-With': 'fetch' },
        cache: 'no-store'
      })).json();
    }

    function buildCells(month, counts, selected) {
      const first = +month.firstDow, days = +month.daysInMonth, ym = month.ym;

      // prefer server "today" to avoid timezone drift
      const srvToday = document.getElementById('dv-root')?.dataset.today;
      let todayStr;
      if (srvToday) {
        todayStr = srvToday;
      } else {
        const t = new Date();
        todayStr = `${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2, '0')}-${String(t.getDate()).padStart(2, '0')}`;
      }

      let html = '';
      for (let i = 0; i < first; i++) html += '<div class="cell empty"></div>';

      for (let d = 1; d <= days; d++) {
        const date = ym + '-' + String(d).padStart(2, '0');
        const cnt = counts[date] || 0;

        const isPast = date < todayStr;
        const isToday = date === todayStr;

        let cls = 'cell';
        if (cnt > 0) cls += ' has';
        if (selected === date) cls += ' sel';
        if (isPast) cls += ' past';
        if (isToday) cls += ' today';

        const inner = `
      <div class="dnum">${d}</div>
      ${cnt > 0 ? `<div class="dsub">${cnt} slot${cnt > 1 ? 's' : ''}</div>` : ''}
    `;

        // Past days are plain blocks (unclickable)
        if (isPast) {
          html += `<div class="${cls}" data-date="${date}">${inner}</div>`;
        } else {
          html += `<a class="${cls}" data-date="${date}" href="#">${inner}</a>`;
        }
      }
      return html;
    }

    function labelDate(ymd) {
      const [y, m, d] = (ymd || '').split('-').map(Number);
      if (!y || !m || !d) return ymd;
      const dt = new Date(y, m - 1, d);
      return dt.toLocaleDateString('vi-VN', {
        weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric'
      });
    }
    const initSel = document.getElementById('dv-root')?.dataset.selected;
    if (initSel) {
      const titleEl = document.getElementById('slotTitle');
      if (titleEl) titleEl.textContent = `Khung giờ · ${labelDate(initSel)}`;
    }
    function render(res) {
      // month bar (existing)
      document.getElementById('calTitle').textContent = res.month.name.toUpperCase();
      root.dataset.ym = res.month.ym;
      document.getElementById('btnPrev').dataset.go = res.month.prev;
      document.getElementById('btnNext').dataset.go = res.month.next;

      // calendar (existing)
      document.getElementById('calBody').innerHTML =
        buildCells(res.month, res.counts || {}, res.selected || null);

      // >>> NEW: update the heading <<<
      const titleEl = document.getElementById('slotTitle');
      if (titleEl) {
        titleEl.textContent = res.selected
          ? `Khung giờ · ${labelDate(res.selected)}`
          : 'Chọn một ngày để xem khung giờ';
      }

      // slots (existing)
      const sg = document.getElementById('slotGrid');
      if (res.selected && res.slots && res.slots.length) {
        sg.innerHTML =
          '<div class="slot-grid">' +
          res.slots.map(s => {
            const t = new Date((s.start_time || s.startTime).replace(' ', 'T'))
              .toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const sid = s.slot_id ?? s.id ?? s.slotId;           // <— robust
            return `<a class="slot"
                   href="<?= BASE_URL ?>index.php?page=confirm&slot=${s.slot_id}">${t}</a>`;
          }).join('') +
          '</div>';
      } else if (res.selected) {
        sg.innerHTML = '<p>Không còn khung giờ trống trong ngày này.</p>';
      } else {
        sg.innerHTML = '<p>Hãy chọn một ngày trong lịch để tiếp tục.</p>';
      }

      // reflect state in URL (existing)
      const p = new URLSearchParams(location.search);
      p.set('page', 'doctor'); p.set('clinic', root.dataset.clinic); p.set('doc', root.dataset.doc); p.set('ym', res.month.ym);
      if (res.selected) p.set('date', res.selected); else p.delete('date');
      history.replaceState(null, '', `?${p.toString()}`);
    }

    // day click
    document.getElementById('calBody').addEventListener('click', async (e) => {
      const a = e.target.closest('a.cell'); if (!a) return;
      e.preventDefault();
      const res = await fetchData({ date: a.dataset.date });
      render(res);
    });

    // month nav
    document.getElementById('btnPrev').addEventListener('click', async (e) => {
      e.preventDefault();
      const go = e.currentTarget.dataset.go;
      const res = await fetchData({ ym: go });
      render(res);
    });
    document.getElementById('btnNext').addEventListener('click', async (e) => {
      e.preventDefault();
      const go = e.currentTarget.dataset.go;
      const res = await fetchData({ ym: go });
      render(res);
    });
  })();
</script>