<?php
// app/views/search/clinic.php
if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}

$clinic = $data['clinic'] ?? null;
$doctors = $data['doctors'] ?? [];
?>

<?php if (!$clinic): ?>
  <section class="wall">
    <div class="card">
      <h1 class="title">Clinic not found</h1>
    </div>
  </section>
<?php else: ?>

  <?php
  // ---- Rating (0–5) -> stars width %
  $rating = isset($clinic['rating']) ? (float) $clinic['rating'] : 0.0;
  $rating = max(0.0, min(5.0, $rating));
  $ratingPct = $rating > 0 ? ($rating / 5) * 100 : 0;

  // ---- Map (supports iframe/html, direct URL, or plain text)
  function gmap_view_url(string $u): string
  {
    $p = parse_url($u);
    if (!$p || empty($p['host']))
      return $u;
    $path = $p['path'] ?? '';
    $path = preg_replace('#/embed/?#', '/', $path);      // strip /embed
    $query = '';
    if (!empty($p['query'])) {
      parse_str($p['query'], $q);
      unset($q['output']);                               // drop output=embed
      $query = http_build_query($q);
    }
    $scheme = $p['scheme'] ?? 'https';
    $host = $p['host'];
    $frag = isset($p['fragment']) ? '#' . $p['fragment'] : '';
    return $scheme . '://' . $host . $path . ($query ? '?' . $query : '') . $frag;
  }

  $mapField = trim((string) ($clinic['googleMap'] ?? ''));
  $fallbackQ = trim((string) ($clinic['address'] ?? ($clinic['name'] ?? 'Hospital')));
  $mapEmbedUrl = null;
  $mapViewUrl = null;

  if ($mapField !== '') {
    if (stripos($mapField, '<iframe') !== false) {
      if (preg_match('/src=["\']([^"\']+)["\']/', $mapField, $m)) {
        $src = $m[1];
        $mapEmbedUrl = $src;
        $mapViewUrl = gmap_view_url($src);
      }
    } elseif (preg_match('#^https?://#i', $mapField)) {
      $u = $mapField;
      // embed version
      $embed = $u;
      if (strpos($embed, '/embed') === false && strpos($embed, 'output=embed') === false) {
        if (stripos($embed, 'google.com/maps') !== false) {
          $embed .= (parse_url($embed, PHP_URL_QUERY) ? '&' : '?') . 'output=embed';
        }
      }
      $mapEmbedUrl = $embed;
      // view (non-embed) version
      $mapViewUrl = gmap_view_url($u);
    } else {
      // plain text address/coords
      $mapEmbedUrl = 'https://www.google.com/maps?q=' . urlencode($mapField) . '&output=embed';
      $mapViewUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapField);
    }
  }

  if (!$mapEmbedUrl) {
    $mapEmbedUrl = 'https://www.google.com/maps?q=' . urlencode($fallbackQ) . '&output=embed';
  }
  if (!$mapViewUrl) {
    $mapViewUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($fallbackQ);
  }

  // Optional: a Directions link
  $mapDirectionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($fallbackQ);
  ?>

  <!-- BLUE HERO -->
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

  <!-- WHITE SECTION -->
  <section class="clv-white">
    <div class="clv-wrap">
      <?php
      // You can replace service data later from DB if needed
      $services = $data['services'] ?? [
        ['icon' => 'svc-appointment.svg', 'title' => 'Khám dịch vụ', 'sub' => $clinic['address'] ?? ''],
        ['icon' => 'svc-doctor.svg', 'title' => 'Khám theo bác sĩ', 'sub' => $clinic['address'] ?? ''],
        ['icon' => 'svc-afterhours.svg', 'title' => 'Khám ngoài giờ', 'sub' => $clinic['address'] ?? ''],
        ['icon' => 'svc-vip.svg', 'title' => 'Phòng khám VIP - Doanh nhân', 'sub' => $clinic['address'] ?? ''],
        ['icon' => 'svc-regular.svg', 'title' => 'Khám thường', 'sub' => $clinic['address'] ?? ''],
      ];
      ?>

      <div class="clv-main">
        <!-- LEFT -->
        <div class="clv-main__left">
          <!-- MÔ TẢ (from Office.description; falls back to legacy fields) -->
          <section class="card clv-about">
            <h2 class="h-title">Mô tả</h2>
            <?php
            $desc = trim((string) ($clinic['description'] ?? ''));
            if ($desc === '' && !empty($clinic['about_html']))
              $desc = (string) $clinic['about_html']; // legacy
            if ($desc === '' && !empty($clinic['about']))
              $desc = (string) $clinic['about'];      // legacy
          
            if ($desc !== '') {
              if (stripos($desc, '<') !== false && stripos($desc, '>') !== false) {
                echo $desc; // trusted HTML you stored
              } else {
                echo '<p>' . nl2br(e($desc)) . '</p>';
              }
            } else {
              echo '<p>Thông tin đang được cập nhật.</p>';
            }
            ?>
          </section>

          <!-- CÁC DỊCH VỤ -->
          <section class="card clv-services">
            <h2 class="h-title">Các dịch vụ</h2>
            <div class="svc-grid">
              <?php foreach ($services as $s): ?>
                <div class="svc-card">
                  <div class="svc-icon"><img src="<?= BASE_URL ?>assets/<?= e($s['icon']) ?>" alt=""></div>
                  <div class="svc-meta">
                    <div class="svc-title"><?= e($s['title']) ?></div>
                    <?php if (!empty($s['sub'])): ?>
                      <div class="svc-sub"><?= e($s['sub']) ?></div><?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </section>

          <!-- TABS (demo content) -->
          <section class="card clv-tabs">
            <nav class="tabs-nav" role="tablist" aria-label="Clinic sections">
              <button class="tab is-active" data-tab="about" role="tab" aria-selected="true">Giới thiệu</button>
              <button class="tab" data-tab="pricing" role="tab" aria-selected="false">Bảng giá khám sức khỏe</button>
              <button class="tab" data-tab="guide" role="tab" aria-selected="false">Hướng dẫn đi khám</button>
              <button class="tab" data-tab="faq" role="tab" aria-selected="false">Câu hỏi thường gặp</button>
            </nav>
            <div class="tabpanels">
              <div class="tabpanel is-active" id="tab-about" role="tabpanel">
                <p>Đây là phần giới thiệu tổng quan về <strong><?= e($clinic['name']) ?></strong>.</p>
              </div>
              <div class="tabpanel" id="tab-pricing" role="tabpanel" hidden>
                <ul class="bullets">
                  <li>Khám tổng quát: …</li>
                  <li>Xét nghiệm cơ bản: …</li>
                  <li>Gói tầm soát: …</li>
                </ul>
              </div>
              <div class="tabpanel" id="tab-guide" role="tabpanel" hidden>
                <ol class="steps">
                  <li>Chuẩn bị giấy tờ.</li>
                  <li>Lấy số thứ tự.</li>
                  <li>Vào phòng khám.</li>
                </ol>
              </div>
              <div class="tabpanel" id="tab-faq" role="tabpanel" hidden>
                <p><strong>Q:</strong> Giờ làm việc? <br><strong>A:</strong> Xem trạng thái ở đầu trang hoặc gọi điện.</p>
              </div>
            </div>
          </section>

          <!-- DOCTORS -->
          <?php if ($doctors): ?>
            <div class="clv-doctors card">
              <?php foreach ($doctors as $d): ?>
                <div class="doc-row">
                  <div class="doc-left">
                    <div class="avatar"><?= e($d['init']) ?></div>
                    <div class="doc-meta">
                      <h3><?= e($d['name']) ?></h3><small><?= e($d['spec']) ?></small>
                    </div>
                  </div>
                  <div class="doc-mid">
                    <div class="kv">
                      <svg viewBox="0 0 24 24" fill="none" stroke="#1f3b72" stroke-width="2" class="ico-18"
                        aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="3" />
                        <path d="M16 2v4M8 2v4M3 10h18" />
                      </svg>
                      <span>Next availability : <strong><?= e($d['next'] ?? '—') ?></strong></span>
                    </div>
                  </div>
                  <a class="btn-make"
                    href="<?= BASE_URL ?>index.php?page=doctor&clinic=<?= (int) $clinic['office_id'] ?>&doc=<?= (int) $d['id'] ?>">Make
                    Appointment</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- RIGHT: MAP -->
        <aside class="clv-main__right">
          <div class="card map-card">
            <div class="map-head">
              <strong>Bản đồ</strong>
              <?php if (!empty($clinic['phone'])): ?>
                <a class="map-call"
                  href="tel:<?= e(preg_replace('/\s+/', '', $clinic['phone'])) ?>"><?= e($clinic['phone']) ?></a>
              <?php endif; ?>
            </div>
            <div class="map-embed">
              <iframe loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="<?= e($mapEmbedUrl) ?>"></iframe>
            </div>
            <div class="map-actions">
              <!-- Optional directions link -->
              <a class="map-dir" target="_blank" rel="noopener" href="<?= e($mapDirectionsUrl) ?>">
                Get Directions
              </a>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </section>
<?php endif; ?>