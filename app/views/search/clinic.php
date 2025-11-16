<?php
// app/views/search/clinic.php
if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}
if (session_status() !== PHP_SESSION_ACTIVE)
  session_start();

/** App-specific: treat any of these as a logged-in marker */
$isLoggedIn = !empty($_SESSION['user_id'] ?? $_SESSION['auth']['id'] ?? $_SESSION['user'] ?? null);

/** If not logged in, send to login with ?next=<target> */
function gate_url(string $target): string
{
  global $isLoggedIn;
  return $isLoggedIn ? $target : (BASE_URL . 'index.php?page=login&next=' . urlencode($target));
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
      <?php $firstDocId = $doctors[0]['id'] ?? null; ?>
      <?php
      // You can replace service data later from DB if needed
      $firstDocId = $doctors[0]['id'] ?? null;
      $services = [
        ['icon' => 'stethoscope.png', 'title' => 'Standard Visit', 'sub' => $clinic['address'] ?? ''],
        [
          'icon' => 'doctor.png',
          'title' => 'Doctor-specific Visit',
          'sub' => $clinic['address'] ?? '',
          'href' => BASE_URL . 'index.php?page=doctors&clinic=' . (int) $clinic['office_id']  // ⬅️ here
        ],
        ['icon' => 'clock.png', 'title' => 'Paid Service Visit', 'sub' => $clinic['address'] ?? ''],
        ['icon' => 'calendar.png', 'title' => 'After-hours Visit', 'sub' => $clinic['address'] ?? ''],
        ['icon' => 'vip.png', 'title' => 'VIP - Executive', 'sub' => $clinic['address'] ?? ''],
      ];
      ?>

      <div class="clv-main">
        <!-- LEFT -->
        <div class="clv-main__left">
          <!-- MÔ TẢ (from Office.description; falls back to legacy fields) -->
          <section class="card clv-about">
            <h2 class="h-title">Overview</h2>
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
            <h2 class="h-title">Our services</h2>
            <div class="svc-grid">
              <?php foreach ($services as $s): ?>
                <?php if (!empty($s['href'])):
                  $target = gate_url($s['href']); ?>
                  <a class="svc-card" href="<?= e($target) ?>">
                    <div class="svc-icon"><img src="<?= BASE_URL ?>images/<?= e($s['icon']) ?>" alt=""></div>
                    <div class="svc-meta">
                      <div class="svc-title"><?= e($s['title']) ?></div>
                      <?php if (!empty($s['sub'])): ?>
                        <div class="svc-sub"></div><?php endif; ?>
                    </div>
                  </a>
                <?php else: ?>
                  <div class="svc-card">
                    <div class="svc-icon"><img src="<?= BASE_URL ?>images/<?= e($s['icon']) ?>" alt=""></div>
                    <div class="svc-meta">
                      <div class="svc-title"><?= e($s['title']) ?></div>
                      <?php if (!empty($s['sub'])): ?>
                        <div class="svc-sub"></div><?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </section>

          <!-- TABS (demo content) -->

          <section class="card clv-tabs" id="clinic-tabs">
            <nav class="tabs-nav" role="tablist" aria-label="Clinic sections">
              <button class="tab is-active" role="tab" aria-selected="true" data-target="#tab-overview">Overview</button>
              <button class="tab" role="tab" aria-selected="false" data-target="#tab-pricing">Pricing</button>
              <button class="tab" role="tab" aria-selected="false" data-target="#tab-guide">Visit Guide</button>
              <button class="tab" role="tab" aria-selected="false" data-target="#tab-faq">FAQ</button>
            </nav>

            <div class="tabpanels">
              <!-- Overview -->
              <div class="tabpanel is-active" id="tab-overview" role="tabpanel">
                <p><strong><?= e($clinic['name']) ?></strong> is a multi-disciplinary medical facility providing
                  outpatient and inpatient care, diagnostics, and preventive services in
                  <?= e($clinic['address'] ?? 'the city') ?>.
                  Our team combines evidence-based practice with patient-centered communication to deliver safe and
                  efficient care.
                </p>

                <p>We focus on short waiting times, transparent pricing, and clear follow-up plans. The clinic supports
                  Vietnamese and English (with interpreter assistance on request). For urgent needs, please call
                  <?php if (!empty($clinic['phone'])): ?>
                    <a href="tel:<?= e(preg_replace('/\s+/', '', $clinic['phone'])) ?>"><?= e($clinic['phone']) ?></a>
                  <?php else: ?>
                    your local emergency number
                  <?php endif; ?>.
                </p>

                <h3 class="h-title">Key services & strengths</h3>
                <ul class="bullets">
                  <li>Primary care & internal medicine (chronic disease management, annual check-ups).</li>
                  <li>Specialist clinics (cardiology, gastroenterology, orthopedics, ENT, OB-GYN, pediatrics, and more).
                  </li>
                  <li>Diagnostics (laboratory tests, ultrasound, X-ray; fast results with doctor review).</li>
                  <li>Preventive health packages and employer medicals.</li>
                  <li>Medication counseling and post-visit care instructions.</li>
                </ul>

                <h3 class="h-title">Care philosophy</h3>
                <p>We believe in clear explanations, shared decision-making, and minimal unnecessary procedures.
                  Every visit ends with a written plan that lists your diagnosis, medications, warning signs, and
                  next steps.</p>
              </div>

              <!-- Pricing -->
              <div class="tabpanel" id="tab-pricing" role="tabpanel" hidden>
                <p>Prices vary with complexity and whether extra tests are required. Below are typical ranges to help you
                  plan.
                  We will always confirm estimated fees before proceeding.</p>

                <table class="price-table">
                  <thead>
                    <tr>
                      <th style="width:40%">Service</th>
                      <th style="width:25%">Typical fee</th>
                      <th style="width:35%">Notes</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>General doctor consultation</td>
                      <td>200,000 – 400,000₫</td>
                      <td>Includes history & exam; meds/tests billed separately.</td>
                    </tr>
                    <tr>
                      <td>Specialist consultation</td>
                      <td>300,000 – 600,000₫</td>
                      <td>Cardiology, Orthopedics, ENT, OB-GYN, etc.</td>
                    </tr>
                    <tr>
                      <td>Basic lab panel</td>
                      <td>300,000 – 700,000₫</td>
                      <td>Fasting required for some tests (see Visit Guide).</td>
                    </tr>
                    <tr>
                      <td>Ultrasound (abdominal/OB)</td>
                      <td>350,000 – 900,000₫</td>
                      <td>Depends on modality and area scanned.</td>
                    </tr>
                    <tr>
                      <td>X-ray (single view)</td>
                      <td>200,000 – 400,000₫</td>
                      <td>Protective shielding provided; report included.</td>
                    </tr>
                    <tr>
                      <td>Comprehensive check-up package</td>
                      <td>1.5 – 4.5m₫</td>
                      <td>Customizable by age, risk, and employer policy.</td>
                    </tr>
                  </tbody>
                </table>

                <p class="muted" style="margin-top:10px">*The figures above are informative ranges. Final charges depend
                  on the
                  doctor’s orders and your insurance coverage, if applicable.</p>
              </div>

              <!-- Visit Guide -->
              <div class="tabpanel" id="tab-guide" role="tabpanel" hidden>
                <h3 class="h-title">Before your visit</h3>
                <ul class="bullets">
                  <li><strong>Documents:</strong> ID/passport, insurance card, prior records or imaging, current
                    medication list.</li>
                  <li><strong>Fasting:</strong> For cholesterol/glucose and some imaging, avoid food for 8–10 hours (water
                    is fine).</li>
                  <li><strong>Medications:</strong> Continue routine meds unless your doctor advised otherwise.</li>
                  <li><strong>Booking:</strong> To reduce wait time, make an appointment online or by phone.</li>
                </ul>

                <h3 class="h-title">When you arrive</h3>
                <ol class="steps">
                  <li>Check in at reception and verify your details.</li>
                  <li>Nurse triage: vitals and brief screening.</li>
                  <li>Doctor consultation and, if needed, labs/imaging.</li>
                  <li>Results discussion, treatment plan, and prescriptions.</li>
                  <li>Payment & scheduling any follow-up.</li>
                </ol>

                <h3 class="h-title">After your visit</h3>
                <ul class="bullets">
                  <li>Follow the written instructions; contact us if symptoms worsen.</li>
                  <li>You can request English summaries for work/insurance.</li>
                  <li>Keep your receipts for reimbursement if required.</li>
                </ul>

                <p style="margin-top:10px">
                  <?php if (!empty($mapDirectionsUrl)): ?>
                    <a class="map-dir" target="_blank" rel="noopener" href="<?= e($mapDirectionsUrl) ?>">Open Directions in
                      Google Maps</a>
                  <?php endif; ?>
                  <?php if (!empty($clinic['phone'])): ?>
                    · Need help? Call <a
                      href="tel:<?= e(preg_replace('/\s+/', '', $clinic['phone'])) ?>"><?= e($clinic['phone']) ?></a>.
                  <?php endif; ?>
                </p>
              </div>

              <!-- FAQ -->
              <div class="tabpanel" id="tab-faq" role="tabpanel" hidden>
                <div class="faq">
                  <details>
                    <summary>How do I book, reschedule, or cancel?</summary>
                    <p>Book online, by phone, or at reception. To reschedule or cancel, please contact us at least 24
                      hours in advance to free up the slot for other patients.</p>
                  </details>
                  <details>
                    <summary>Do you accept insurance?</summary>
                    <p>Most domestic policies are accepted. Please bring your insurance card and a valid ID. Coverage
                      depends on your plan and medical necessity rules.</p>
                  </details>
                  <details>
                    <summary>When should I go to Emergency instead?</summary>
                    <p>Chest pain, severe shortness of breath, sudden weakness on one side, uncontrollable bleeding, or
                      major trauma — go directly to Emergency or call your local emergency number.</p>
                  </details>
                  <details>
                    <summary>How do I get test results?</summary>
                    <p>Routine labs are usually ready the same day. Your doctor will explain the results and upload them
                      to your file; printed copies are available on request.</p>
                  </details>
                  <details>
                    <summary>Payments & methods</summary>
                    <p>We accept cash, major cards, and bank transfer. Invoices are issued in English/Vietnamese upon
                      request.</p>
                  </details>
                  <details>
                    <summary>Preparation for blood tests & imaging</summary>
                    <p>Fasting 8–10 hours for lipid/glucose profiles; drink water. For abdominal ultrasound, avoid heavy
                      meals; for pregnancy ultrasound, follow the sonographer’s guidance.</p>
                  </details>
                </div>
              </div>
            </div>
          </section>

          <!-- DOCTORS
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
          <?php endif; ?> -->
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
<script>
  (function () {
    const box = document.getElementById('clinic-tabs');
    if (!box) return;

    const tabs = Array.from(box.querySelectorAll('.tab'));
    const panels = Array.from(box.querySelectorAll('.tabpanel'));

    function activate(targetId) {
      tabs.forEach(btn => {
        const on = btn.dataset.target.replace(/^#/, '') === targetId;
        btn.classList.toggle('is-active', on);
        btn.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      panels.forEach(p => {
        const on = p.id === targetId;
        p.hidden = !on;
        p.classList.toggle('is-active', on);
      });
    }

    tabs.forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.target.replace(/^#/, '');
        activate(id);
      });
      // keyboard support
      btn.addEventListener('keydown', (e) => {
        if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
        e.preventDefault();
        const i = tabs.indexOf(btn);
        const j = (e.key === 'ArrowRight') ? (i + 1) % tabs.length : (i - 1 + tabs.length) % tabs.length;
        tabs[j].focus();
      });
    });
  })();
</script>
<script>
  (function () {
    const box = document.getElementById('clinic-tabs');
    if (!box) return;

    const tabs = Array.from(box.querySelectorAll('.tab'));
    const panels = Array.from(box.querySelectorAll('.tabpanel'));

    function activate(targetId) {
      tabs.forEach(btn => {
        const on = btn.dataset.target.replace(/^#/, '') === targetId;
        btn.classList.toggle('is-active', on);
        btn.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      panels.forEach(p => {
        const on = p.id === targetId;
        p.hidden = !on;
        p.classList.toggle('is-active', on);
      });
    }

    function idFromHash(hash) {
      if (!hash) return 'tab-overview';
      const clean = hash.replace(/^#/, '');
      return clean.startsWith('tab-') ? clean : ('tab-' + clean);
    }

    // init from current hash
    activate(idFromHash(location.hash));

    // react to back/forward or manual hash changes
    window.addEventListener('hashchange', () => activate(idFromHash(location.hash)));

    // clicking tabs updates the hash (nice URLs)
    tabs.forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.target.replace(/^#/, '');
        history.replaceState(null, '', '#' + id.replace(/^tab-/, ''));
        activate(id);
      });
    });
  })();
</script>