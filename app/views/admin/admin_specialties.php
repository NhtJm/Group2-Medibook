<?php if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}
$rows = $data['specialties'] ?? [];
$search = $data['search'] ?? '';
$featured = $data['featured'] ?? 'all';

if (session_status() === PHP_SESSION_NONE)
  session_start();
if (empty($_SESSION['csrf']))
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
?>
<section class="admin-shell">
  <?php $active = 'admin_specialties';
  require __DIR__ . '/partials/sidebar.php'; ?>

  <div class="admin-main admin-specialties">
    <?php
    $title = 'Medical Specialties';
    $searchAction = BASE_URL . 'index.php';
    $searchHidden = ['page' => 'admin_specialties', 'featured' => $featured]; // keep current featured filter
    require __DIR__ . '/partials/topbar.php';
    ?>

    <section class="panel">
      <div class="panel__head" style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
        <a class="btn btn-primary" href="<?= e(BASE_URL . 'index.php?page=admin_specialty_edit') ?>">+ Add new</a>
        <?php $pg = $data['pager'] ?? ['pp' => 20, 'page' => 1, 'pages' => 1, 'total' => count($rows)]; ?>
        <div style="display:flex;gap:8px;align-items:center;">
          <div class="ac-count">
            <?= (int) ($data['pager']['total'] ?? count($rows)) ?> shown
          </div>
          <label class="visually-hidden" for="featFilter"></label>
          <select id="featFilter" class="ac-select">
            <option value="all" <?= $featured === 'all' ? 'selected' : '' ?>>All</option>
            <option value="yes" <?= $featured === 'yes' ? 'selected' : '' ?>>Featured only</option>
            <option value="no" <?= $featured === 'no' ? 'selected' : '' ?>>Not featured</option>
          </select>

        </div>
      </div>

      <div class="table-scroller">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width:56px;">Order</th>
              <th style="width:56px;">Icon</th>
              <th>Name</th>
              <th>Slug</th>
              <th>Blurb</th>
              <th style="width:120px;">Featured</th>
              <th style="width:160px;">Actions</th>
            </tr>
          </thead>
          <tbody id="spc-tbody">
            <?php foreach ($rows as $r): ?>
              <tr data-id="<?= (int) $r['specialty_id'] ?>">
                <td>
                  <button class="btn btn-ghost js-move" data-dir="up">▲</button>
                  <button class="btn btn-ghost js-move" data-dir="down">▼</button>
                </td>
                <td>
                  <?php if (!empty($r['image_url'])): ?>
                    <img src="<?= e($r['image_url']) ?>" alt=""
                      style="width:36px;height:36px;object-fit:cover;border-radius:6px" onerror="this.remove()">
                  <?php endif; ?>
                </td>
                <td><?= e($r['name']) ?></td>
                <td><code><?= e($r['slug']) ?></code></td>
                <td><?= e($r['blurb'] ?? '') ?></td>
                <td>
                  <label class="switch">
                    <input type="checkbox" class="js-featured" <?= ((int) $r['is_featured'] === 1 ? 'checked' : '') ?>>
                    <span class="slider"></span>
                  </label>
                </td>
                <td>
                  <a class="btn"
                    href="<?= e(BASE_URL . 'index.php?page=admin_specialty_edit&sid=' . (int) $r['specialty_id']) ?>">Edit</a>
                  <button class="btn btn-danger js-del">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</section>

<style>
  .table-scroller {
    overflow: auto
  }

  .admin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0
  }

  .admin-table th,
  .admin-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
    vertical-align: middle
  }

  .switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px
  }

  .switch input {
    opacity: 0;
    width: 0;
    height: 0
  }

  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #d1d5db;
    border-radius: 999px;
    transition: .2s
  }

  .slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    top: 3px;
    background: white;
    border-radius: 50%;
    transition: .2s
  }

  .switch input:checked+.slider {
    background: #10b981
  }

  .switch input:checked+.slider:before {
    transform: translateX(20px)
  }

  .btn {
    border: 1px solid #d5dce7;
    border-radius: 8px;
    padding: 6px 10px;
    background: #fff
  }

  .btn-primary {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb
  }

  .btn-ghost {
    background: transparent
  }

  .btn-danger {
    background: #ef4444;
    color: #fff;
    border-color: #ef4444
  }

  .ac-select {
    border: 1px solid #d5dce7;
    border-radius: 8px;
    background: #fff;
    padding: 6px 10px
  }
</style>

<script>
  (() => {
    const BASE_URL = '<?= e(BASE_URL) ?>';
    const API_URL = BASE_URL + 'index.php?page=admin_specialties_api';
    const CSRF = '<?= e($csrf) ?>';

    // search + featured filter (reload SSR)
    const $form = document.querySelector('.admin-topbar .ac-search');
    const $input = $form ? $form.querySelector('input[name="q"]') : null;
    const $feat = document.getElementById('featFilter');

    function reloadWithFilters() {
      const usp = new URLSearchParams();
      usp.set('page', 'admin_specialties');
      if ($input && $input.value.trim()) usp.set('q', $input.value.trim());
      if ($feat && $feat.value !== 'all') usp.set('featured', $feat.value);

      try { sessionStorage.setItem('refocusSearch', '1'); } catch { }

      window.location.href = BASE_URL + 'index.php?' + usp.toString();
    }
    if ($feat) $feat.addEventListener('change', reloadWithFilters);
    if ($form && $input) {
      $form.addEventListener('submit', e => { e.preventDefault(); reloadWithFilters(); });
      $form.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); reloadWithFilters(); } });
    }

    // actions
    const $tbody = document.getElementById('spc-tbody');
    if (!$tbody) return;

    $tbody.addEventListener('change', async (e) => {
      const cb = e.target.closest('.js-featured');
      if (!cb) return;
      const tr = cb.closest('tr');
      const id = Number(tr.dataset.id);
      const featured = cb.checked ? 1 : 0;
      try {
        const res = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ action: 'toggle_featured', id, featured, csrf: CSRF })
        });
        const j = await res.json();
        if (!j.ok) throw new Error(j.error || 'toggle failed');
      } catch (err) {
        console.error(err); cb.checked = !cb.checked; alert('Update failed');
      }
    });

    $tbody.addEventListener('click', async (e) => {
      const del = e.target.closest('.js-del');
      if (del) {
        const tr = del.closest('tr');
        const id = Number(tr.dataset.id);
        if (!confirm('Delete this specialty?')) return;
        try {
          const res = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ action: 'delete', id, csrf: CSRF })
          });
          const j = await res.json();
          if (!j.ok) throw new Error(j.error || 'delete failed');
          tr.remove();
        } catch (err) { console.error(err); alert('Delete failed'); }
        return;
      }

      const mv = e.target.closest('.js-move');
      if (mv) {
        const tr = mv.closest('tr');
        const id = Number(tr.dataset.id);
        const dir = mv.dataset.dir || 'up';
        try {
          const res = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ action: 'move', id, dir, csrf: CSRF })
          });
          const j = await res.json();
          if (!j.ok) throw new Error(j.error || 'move failed');
          // Optimistic UI: swap rows visually
          if (dir === 'up' && tr.previousElementSibling) tr.parentNode.insertBefore(tr, tr.previousElementSibling);
          if (dir === 'down' && tr.nextElementSibling) tr.parentNode.insertBefore(tr.nextElementSibling, tr);
        } catch (err) { console.error(err); alert('Reorder failed'); }
      }
    });
  })();
</script>
<script>
(function () {
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
})();
</script>