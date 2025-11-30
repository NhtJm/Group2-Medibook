<?php if (!function_exists('e')) { function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); } }
$rows   = $data['offices'] ?? [];
$q      = $data['q'] ?? '';
$status = $data['status'] ?? 'all';

if (session_status() === PHP_SESSION_NONE) session_start();
?>
<link rel="stylesheet" href="<?= e(BASE_URL) ?>public/css/admin_doctors.css?v=3">

<section class="admin-shell">
  <?php $active='admin_doctors'; require __DIR__.'/partials/sidebar.php'; ?>

  <div class="admin-main admin-doctors">
    <?php
      $title = 'Doctors';
      $searchAction = BASE_URL.'index.php';
      $searchHidden = ['page'=>'admin_doctors'];
      require __DIR__.'/partials/topbar.php';
    ?>

    <section class="panel">
      <div class="panel__head doc-head">
        <div class="doc-count"><?= count($rows) ?> offices</div>
        <div class="doc-tools">
          <label for="statusFilter" class="visually-hidden">Status</label>
          <select id="statusFilter" class="ac-select">
            <option value="all"         <?= $status==='all'?'selected':'' ?>>All</option>
            <option value="approved"    <?= $status==='approved'?'selected':'' ?>>Approved</option>
            <option value="pending"     <?= $status==='pending'?'selected':'' ?>>Pending</option>
            <option value="deactivated" <?= $status==='deactivated'?'selected':'' ?>>Deactivated</option>
          </select>
        </div>
      </div>

      <?php
      $buildLogo = function($raw){
        $raw = trim((string)$raw);
        if ($raw==='') return '';
        if (preg_match('~^(https?://|/|data:image/)~i',$raw)) return $raw;
        return rtrim(BASE_URL,'/').'/public/uploads/clinics/'.$raw;
      };
      ?>

      <div id="doc-grid">
        <?php foreach ($rows as $o): ?>
          <?php
            $href   = BASE_URL.'index.php?page=admin_doctors_office&office='.(int)$o['office_id'];
            $logo   = $buildLogo($o['logo'] ?? '');
            $name   = (string)($o['name'] ?? '');
            $addr   = (string)($o['address'] ?? '');
            $slug   = (string)($o['slug'] ?? '');
            $count  = (int)($o['doctor_count'] ?? 0);
            $st     = strtolower((string)($o['status'] ?? 'pending'));
            $stCls  = $st==='approved' ? 'is-approved' : ($st==='deactivated' ? 'is-deactivated' : 'is-pending');
          ?>
          <div class="ac-card" data-href="<?= e($href) ?>" tabindex="0" role="link" aria-label="Open doctors at <?= e($name) ?>">
            <div class="ac-left">
              <?php if ($logo): ?>
                <div class="ac-avatar">
                  <img class="ac-logo" src="<?= e($logo) ?>" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                </div>
              <?php else: ?>
                <div class="ac-avatar"><?= e(mb_substr($name,0,2)) ?></div>
              <?php endif; ?>
            </div>

            <div class="ac-body">
              <div class="ac-title"><?= e($name) ?></div>
              <div class="ac-meta" title="<?= e($addr) ?>"><?= e($addr) ?></div>
              <div class="ac-tags">
                <span class="ac-tag"><?= $count ?> <?= $count===1?'doctor':'doctors' ?></span>
              </div>
            </div>

            <div class="ac-right">
              <span class="status-chip <?= $stCls ?>"><?= e(ucfirst($st)) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</section>

<script>
(() => {
  const BASE = '<?= e(BASE_URL) ?>';
  const statusSel = document.getElementById('statusFilter');
  const form  = document.querySelector('.admin-topbar .ac-search');
  const input = form ? form.querySelector('input[name="q"]') : null;

  function reloadWithFilters(){
    const usp = new URLSearchParams();
    usp.set('page','admin_doctors');
    if (input && input.value.trim()) usp.set('q', input.value.trim());
    if (statusSel && statusSel.value !== 'all') usp.set('status', statusSel.value);

    // keep UX nice: put caret back in search after reload
    try { sessionStorage.setItem('refocusSearch','1'); } catch {}
    window.location.href = BASE + 'index.php?' + usp.toString();
  }
  if (statusSel) statusSel.addEventListener('change', reloadWithFilters);
  if (form && input) {
    form.addEventListener('submit', e => { e.preventDefault(); reloadWithFilters(); });
    form.addEventListener('keydown', e => { if (e.key==='Enter'){ e.preventDefault(); reloadWithFilters(); } });
  }

  // Grid card → open office doctors
  const grid = document.getElementById('doc-grid');
  if (grid) {
    grid.addEventListener('click', e => {
      const card = e.target.closest('.ac-card'); if (!card) return;
      const href = card.dataset.href; if (href) window.location.href = href;
    });
    grid.addEventListener('keydown', e => {
      if (e.key !== 'Enter') return;
      const card = e.target.closest('.ac-card'); if (!card) return;
      const href = card.dataset.href; if (href) window.location.href = href;
    });
  }

  // return focus to search after navigation
  (function(){
    try {
      if (sessionStorage.getItem('refocusSearch') === '1') {
        sessionStorage.removeItem('refocusSearch');
        const inp = document.querySelector('.admin-topbar .ac-search input[name="q"]');
        if (inp) { inp.focus(); const L = inp.value.length; inp.setSelectionRange(L,L); }
      }
    } catch {}
  })();
})();
</script>