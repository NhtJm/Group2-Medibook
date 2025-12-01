<?php if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}
$admin = current_user() ?? [];
$logoutUrl = BASE_URL . 'index.php?page=logout';
$fullFromParts = trim(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? ''));
$adminName =
  ($admin['full_name'] ?? null)
  ?? ($fullFromParts !== '' ? $fullFromParts : null)
  ?? ($admin['name'] ?? null)
  ?? ($admin['display_name'] ?? null)
  ?? ($admin['username'] ?? null)
  ?? ($admin['email'] ?? 'Administrator');
?>

<section class="admin-shell">
  <?php
  $active = 'admin_dashboard';
  require __DIR__ . '/partials/sidebar.php';
  ?>

  <div class="admin-main admin-dashboard">
    <?php
    $title = 'Dashboard v2';
    $search = $_GET['q'] ?? '';
    $searchAction = BASE_URL . 'index.php';
    $searchHidden = ['page' => 'admin_clinics']; // or whatever your router expects
    require __DIR__ . '/partials/topbar.php';
    ?>

    <?php
    $k = $data['kpis'] ?? [];
    if (!function_exists('e')) {
      function e($s)
      {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
      }
    }
    $nf = fn($n) => number_format((int) ($n ?? 0));
    ?>
    <section class="admin-kpis">
      <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--blue">👨‍⚕️</div>
        <div class="kpi-card__content">
          <div class="kpi-card__value"><?= e($nf($k['doctors_active'])) ?></div>
          <div class="kpi-card__label">Active Doctors</div>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--red">❤️</div>
        <div class="kpi-card__content">
          <div class="kpi-card__value"><?= e($nf($k['patients_total'])) ?></div>
          <div class="kpi-card__label">Patients</div>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--green">📅</div>
        <div class="kpi-card__content">
          <div class="kpi-card__value"><?= e($nf($k['appointments_week'])) ?></div>
          <div class="kpi-card__label">Appointments This Week</div>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--yellow">🏥</div>
        <div class="kpi-card__content">
          <div class="kpi-card__value"><?= e($nf($k['offices_total'])) ?></div>
          <div class="kpi-card__label">Offices</div>
        </div>
      </div>
      <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--indigo">👥</div>
        <div class="kpi-card__content">
          <div class="kpi-card__value"><?= e($nf($k['users_total'])) ?></div>
          <div class="kpi-card__label">Users</div>
        </div>
      </div>

      <!-- Medical Specialties -->
      <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--teal">🩺</div>
        <div class="kpi-card__content">
          <div class="kpi-card__value"><?= e($nf($k['specialties_total'])) ?></div>
          <div class="kpi-card__label">Medical Specialties</div>
        </div>
      </div>
    </section>

    <!-- First big row: Recap + Stats (unchanged) -->

    <!-- Second row (unchanged) -->
    <section class="admin-row">
      <div class="panel">
        <div class="panel__head">
          <div class="panel__title">Pending Clinics</div>
          <div class="panel__meta">
            <?= isset($data['pending_offices']) ? count($data['pending_offices']) : 0 ?> awaiting review
          </div>
        </div>

        <?php
        $pend = $data['pending_offices'] ?? [];
        if (session_status() === PHP_SESSION_NONE)
          session_start();
        if (empty($_SESSION['csrf']))
          $_SESSION['csrf'] = bin2hex(random_bytes(16));
        $csrf = $_SESSION['csrf'];
        ?>

        <?php if (!$pend): ?>
          <div style="padding:16px; color:#6b7380;">No pending clinics 🎉</div>
        <?php else: ?>
          <ul class="latest-list" id="pendingClinics">
            <?php foreach ($pend as $o):
              $letter = mb_strtoupper(mb_substr($o['name'], 0, 1));
              $meta = trim(($o['city'] ? $o['city'] : '') . ($o['country'] ? ' • ' . $o['country'] : ''));
              ?>
              <li class="latest-item" data-id="<?= (int) $o['office_id'] ?>">
                <div class="latest-avatar"><span><?= e($letter) ?></span></div>
                <div class="latest-info">
                  <div class="latest-name"><?= e($o['name']) ?></div>
                  <div class="latest-meta"><?= e($meta !== '' ? $meta : 'Pending review') ?></div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <div class="panel">
        <div class="panel__head">
          <div class="panel__title">Traffic Split</div>
          <div class="panel__meta">Last 7 days</div>
        </div>

        <div class="traffic">
          <div class="traffic__donut">
            <div class="traffic__donut-inner">
              <div class="traffic__donut-hole">62%</div>
            </div>
          </div>
          <ul class="traffic__legend">
            <li><span class="legend-dot legend-dot--blue"></span> Chrome</li>
            <li><span class="legend-dot legend-dot--green"></span> iOS App</li>
            <li><span class="legend-dot legend-dot--yellow"></span> Safari</li>
            <li><span class="legend-dot legend-dot--red"></span> Android App</li>
            <li><span class="legend-dot legend-dot--gray"></span> Other</li>
          </ul>
        </div>
      </div>
    </section>

    <footer class="admin-footer">
      <div>Copyright © <?= date('Y') ?> MediBook. All rights reserved.</div>
      <div class="admin-footer__ver">Version 1.0.0</div>
    </footer>

  </div><!-- /admin-main -->
</section>
<script>
(function(){
  const list = document.getElementById('pendingClinics');
  if (!list) return;

  const API  = "<?= e(BASE_URL.'index.php?page=admin_clinics_api') ?>";
  const CSRF = "<?= e($csrf) ?>";

  async function moderate(li, to){
    const id = li.getAttribute('data-id');
    const btns = li.querySelectorAll('.mod-btn');
    btns.forEach(b => b.disabled = true);

    try{
      const resp = await fetch(API, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
          action: 'moderate',
          office_id: id,
          to: to,
          csrf: CSRF
        })
      });
      const json = await resp.json();
      if (json && json.ok){
        li.remove(); // gone from the list
      } else {
        alert('Action failed' + (json && json.error ? ': ' + json.error : ''));
        btns.forEach(b => b.disabled = false);
      }
    } catch(e){
      alert('Network error');
      btns.forEach(b => b.disabled = false);
    }
  }

  list.addEventListener('click', (ev) => {
    const btn = ev.target.closest('.mod-btn');
    if (!btn) return;
    const li = ev.target.closest('li.latest-item');
    const to = btn.getAttribute('data-to');
    if (!li || !to) return;

    const name = li.querySelector('.latest-name')?.textContent?.trim() || 'this clinic';
    const ok = confirm(`Are you sure to set "${name}" to ${to}?`);
    if (!ok) return;

    moderate(li, to);
  });
})();
</script>