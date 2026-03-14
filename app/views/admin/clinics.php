<?php if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}
$search = $data['search'] ?? '';
$clinics = $data['clinics'] ?? [];
$statusCurrent = $data['status'] ?? 'all';

if (session_status() === PHP_SESSION_NONE)
  session_start();
if (empty($_SESSION['csrf']))
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
?>
<section class="admin-shell">
  <?php $active = 'admin_clinics';
  require __DIR__ . '/partials/sidebar.php'; ?>

  <div class="admin-main admin-clinics">
    <?php
    $title = 'Clinics';
    $searchAction = BASE_URL . 'index.php';
    $searchHidden = ['page' => 'admin_clinics'];
    require __DIR__ . '/partials/topbar.php';
    ?>

    <section class="panel">
      <div class="panel__head" style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
        <div>All Clinics</div>
        <div style="display:flex;align-items:center;gap:12px;">
          <div class="ac-count"><span id="ac-count"><?= count($clinics) ?></span> shown</div>

          <!-- Status filter -->
          <label for="statusFilter" class="visually-hidden">Status</label>
          <select id="statusFilter" class="ac-select">
            <option value="all" <?= $statusCurrent === 'all' ? 'selected' : '' ?>>All</option>
            <option value="approved" <?= $statusCurrent === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="pending" <?= $statusCurrent === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="deactivated" <?= $statusCurrent === 'deactivated' ? 'selected' : '' ?>>Deactivated</option>
          </select>
        </div>
      </div>

      <div class="ac-section">
        <div id="ac-list">
          <?php
          $buildLogoUrl = function (string $raw) {
            $raw = trim($raw);
            if ($raw === '')
              return '';
            if (preg_match('~^(https?://|data:image/)~i', $raw))
              return $raw;
            if ($raw[0] === '/')
              return rtrim(BASE_URL, '/') . $raw;
            return rtrim(BASE_URL, '/') . '/public/uploads/clinics/' . $raw;
          };

          foreach ($clinics as $c):
            $name = (string) ($c['name'] ?? '');
            $href = BASE_URL . 'index.php?page=clinic&clinic=' . (int) $c['office_id'];
            $logoUrl = !empty($c['logo']) ? $buildLogoUrl((string) $c['logo'])
              : (!empty($c['image']) ? $buildLogoUrl((string) $c['image']) : '');
            $status = strtolower((string) ($c['status'] ?? 'pending'));
            ?>
            <?php
            $publicHref = BASE_URL . 'index.php?page=clinic&clinic=' . (int) $c['office_id'];
            // change the page name below if your edit route differs
            $editHref = BASE_URL . 'index.php?page=clinic_edit&clinic=' . (int)$c['office_id'];
            ?>



            <div class="ac-item" data-id="<?= (int) $c['office_id'] ?>" data-edit="<?= e($editHref) ?>" tabindex="0"
              role="link" aria-label="Edit clinic <?= e($name) ?>">
              <a class="ac-left" href="<?= e($href) ?>" aria-label="Open clinic">
                <?php if ($logoUrl): ?>
                  <div class="ac-avatar" style="background:#fff;overflow:hidden;">
                    <img class="ac-logo" src="<?= e($logoUrl) ?>" alt="<?= e($name) ?> logo" loading="lazy" decoding="async"
                      style="width:100%;height:100%;object-fit:cover;"
                      onerror="this.parentElement.textContent='<?= e(mb_substr($name, 0, 2)) ?>'; this.remove();">
                  </div>
                <?php else: ?>
                  <div class="ac-avatar"><?= e(mb_substr($name, 0, 2)) ?></div>
                <?php endif; ?>
              </a>

              <div class="ac-body">
                <div class="ac-title">
                  <a href="<?= e($href) ?>" class="ac-title-link" style="text-decoration:none;color:inherit;">
                    <?= e($name) ?>
                  </a>
                </div>
                <div class="ac-meta"><?= e($c['address'] ?? '') ?></div>
                <div class="ac-tags">
                  <?php if (!empty($c['phone'])): ?>
                    <span class="ac-tag"><?= e($c['phone']) ?></span>
                  <?php endif; ?>
                  <?php if (isset($c['rating'])): ?>
                    <span class="ac-tag rate">★ <?= e(number_format((float) $c['rating'], 1)) ?></span>
                  <?php endif; ?>
                  <?php if (!empty($c['slug'])): ?>
                    <span class="ac-tag">slug: <?= e($c['slug']) ?></span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="ac-right">
                <button type="button"
                  class="status-chip js-status <?= e($status === 'approved' ? 'is-approved' : ($status === 'deactivated' ? 'is-deactivated' : 'is-pending')) ?>"
                  data-id="<?= (int) $c['office_id'] ?>" data-status="<?= e($status) ?>">
                  <?= e(ucfirst($status)) ?> ▾
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>


      </div>
    </section>
  </div>
</section>

<style>
  .visually-hidden {
    position: absolute !important;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
    white-space: nowrap;
    border: 0;
  }

  .ac-select {
    border: 1px solid #d5dce7;
    border-radius: 8px;
    background: #fff;
    padding: 6px 10px;
    font-size: .9rem;
    color: #1a1f2e;
  }
</style>

<script>
  (function () {
    'use strict';

    const BASE_URL = '<?= e(BASE_URL) ?>';
    const API_URL  = BASE_URL + 'index.php?page=admin_clinics_api';
    const CSRF     = '<?= e($csrf) ?>';

    // current state coming from PHP (unused here, kept for reference)
    let q = '<?= e($search) ?>';
    let s = '<?= e($statusCurrent) ?>';

    // DOM
    const $statusSel = document.getElementById('statusFilter');
    const $form  = document.querySelector('.admin-topbar .ac-search');
    const $input = $form ? $form.querySelector('input[name="q"]') : null;

    // helpers
    const cap = (v) => v ? v[0].toUpperCase() + v.slice(1) : '';
    const statusClass = (v) => (v === 'approved' ? 'is-approved' : (v === 'deactivated' ? 'is-deactivated' : 'is-pending'));

    // Navigate with current search + status to SSR page
    function reloadWithFilters() {
      const usp = new URLSearchParams();
      usp.set('page', 'admin_clinics');

      const qVal = $input ? ($input.value || '').trim() : '';
      const sVal = $statusSel ? $statusSel.value : 'all';

      if (qVal) usp.set('q', qVal);
      if (sVal && sVal !== 'all') usp.set('status', sVal);

      // NEW: remember to refocus search after navigation
      try { sessionStorage.setItem('refocusSearch', '1'); } catch {}

      window.location.href = BASE_URL + 'index.php?' + usp.toString();
    }

    // Status filter → reload
    if ($statusSel) {
      $statusSel.addEventListener('change', reloadWithFilters);
    }

    // Submit on Enter (no auto-search while typing)
    if ($form && $input) {
      $form.addEventListener('submit', (e) => { e.preventDefault(); reloadWithFilters(); });
      $form.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); reloadWithFilters(); }
      });
    }

    // ===== Status dropdown + confirm (unchanged) =====
    let openMenu = null;

    function closeStatusMenu() { if (openMenu) { openMenu.remove(); openMenu = null; } }
    function buildStatusRight(id, status) {
      const wrap = document.createElement('div');
      wrap.className = 'ac-right';
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'status-chip js-status ' + statusClass(status || 'pending');
      btn.dataset.id = String(id || '');
      btn.dataset.status = String(status || 'pending');
      btn.textContent = cap(status || 'pending') + ' ▾';
      wrap.appendChild(btn);
      return wrap;
    }
    function openStatusMenu(anchorBtn) {
      closeStatusMenu();
      const id = anchorBtn.dataset.id;

      const menu = document.createElement('div');
      menu.className = 'status-menu';
      menu.innerHTML = ['approved', 'pending', 'deactivated']
        .map(st => `<button type="button" class="status-opt" data-id="${id}" data-status="${st}">${cap(st)}</button>`)
        .join('');

      menu.addEventListener('click', async (e) => {
        const opt = e.target.closest('.status-opt');
        if (!opt) return;
        const officeId = opt.dataset.id;
        const newStatus = opt.dataset.status;
        closeStatusMenu();
        showConfirmModal(newStatus, async () => {
          const saved = await postStatus(officeId, newStatus);
          const row = document.querySelector(`.ac-item[data-id="${officeId}"]`);
          const right = row ? row.querySelector('.ac-right') : null;
          if (right) { right.innerHTML = ''; right.appendChild(buildStatusRight(officeId, saved)); }
        });
      });

      document.body.appendChild(menu);
      const r = anchorBtn.getBoundingClientRect();
      menu.style.position = 'fixed';
      menu.style.top  = (r.bottom + 6) + 'px';
      menu.style.left = Math.min(r.left, window.innerWidth - 180) + 'px';
      openMenu = menu;
    }
    function showConfirmModal(newStatus, onYes) {
      document.querySelectorAll('.status-modal, .status-modal-backdrop').forEach(n => n.remove());
      const backdrop = document.createElement('div'); backdrop.className = 'status-modal-backdrop';
      const modal = document.createElement('div'); modal.className = 'status-modal';
      modal.innerHTML = `
        <div class="sc-title">Confirm change</div>
        <div class="sc-text">Change status to <b>${cap(newStatus)}</b>?</div>
        <div class="sc-actions">
          <button type="button" class="btn btn-ghost js-no">No</button>
          <button type="button" class="btn btn-primary js-yes">Yes</button>
        </div>`;
      const cleanup = () => { backdrop.remove(); modal.remove(); window.removeEventListener('keydown', onEsc); };
      const onEsc = (e) => { if (e.key === 'Escape') cleanup(); };
      modal.querySelector('.js-no').onclick = cleanup;
      modal.querySelector('.js-yes').onclick = async () => { try { await onYes(); } finally { cleanup(); } };
      backdrop.onclick = cleanup;
      window.addEventListener('keydown', onEsc);
      document.body.appendChild(backdrop);
      document.body.appendChild(modal);
    }
    async function postStatus(id, status) {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ action: 'update_status', office_id: Number(id), status, csrf: CSRF })
      });
      const j = await res.json().catch(() => null);
      if (!res.ok || !j || j.ok !== true) throw new Error((j && j.error) || 'Update failed');
      return j.status || status;
    }
    document.addEventListener('click', (e) => {
      const chip = e.target.closest('.js-status');
      if (chip) { e.preventDefault(); openStatusMenu(chip); return; }
      if (openMenu && !openMenu.contains(e.target)) closeStatusMenu();
    });

    // NEW: put the cursor back in the search box after a search
    window.addEventListener('DOMContentLoaded', () => {
      try {
        if (sessionStorage.getItem('refocusSearch') === '1') {
          sessionStorage.removeItem('refocusSearch');
          const input = document.querySelector('.admin-topbar .ac-search input[name="q"]');
          if (input) {
            input.focus();
            const len = input.value.length;
            if (input.setSelectionRange) input.setSelectionRange(len, len);
          }
        }
      } catch {}
    });
  })();
</script>
<script>
(function () {
  const $list = document.getElementById('ac-list');
  if (!$list) return;

  // Click anywhere on the card (except excluded areas) => go to edit page
  $list.addEventListener('click', (e) => {
    const item = e.target.closest('.ac-item');
    if (!item) return;

    // Let these native actions proceed:
    if (e.target.closest('.ac-left, .js-status, .status-menu, .status-modal, a')) return;

    const href = item.dataset.edit;
    if (href) window.location.href = href;
  });

  // Keyboard support (Enter focuses the card -> edit)
  $list.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;
    const item = e.target.closest('.ac-item');
    if (!item) return;
    const href = item.dataset.edit;
    if (href) window.location.href = href;
  });
})();
</script>