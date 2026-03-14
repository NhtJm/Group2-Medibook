<?php if (!function_exists('e')) {
    function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
$rows         = $data['rows'] ?? [];
$total        = $data['total'] ?? 0;
$page         = $data['page'] ?? 1;
$perPage      = $data['perPage'] ?? 25;
$filters      = $data['filters'] ?? ['office'=>0,'status'=>'','q'=>'','from'=>'','to'=>''];
$offices      = $data['offices'] ?? [];
$officeCounts = $data['officeCounts'] ?? [];
$pages        = max(1, (int)ceil($total / max(1,$perPage)));

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
?>
<section class="admin-shell">
  <?php $active = 'admin_appointments'; require __DIR__.'/partials/sidebar.php'; ?>

  <div class="admin-main">
    <?php
      $title = 'Appointments';
      $searchAction = BASE_URL . 'index.php';
      $searchHidden = ['page' => 'admin_appointments'];
      require __DIR__.'/partials/topbar.php';
    ?>

    <section class="panel">
      <?php if (!empty($_SESSION['flash'])): $f=$_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="flash <?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
      <?php endif; ?>

      <div class="panel__head headbar">
        <form method="get" action="<?= e(BASE_URL.'index.php') ?>" class="filters-bar" id="apptFilters">
          <input type="hidden" name="page" value="admin_appointments">
          <!-- auto-reset to first page on any change -->
          <input type="hidden" name="p" value="<?= (int)$page ?>">

          <!-- Office -->
          <div class="pill">
            <span class="icon">🏥</span>
            <select name="office" class="pill-input">
              <option value="0">(All offices)</option>
              <?php foreach ($offices as $o): ?>
                <option value="<?= (int)$o['office_id'] ?>" <?= (int)$filters['office']===(int)$o['office_id']?'selected':'' ?>>
                  <?= e($o['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Status (segmented) -->
          <div class="seg">
            <?php
              $st = $filters['status'] ?? '';
              $statuses = [''=>'All','booked'=>'Booked','completed'=>'Completed','canceled'=>'Canceled','no-show'=>'No-show'];
              foreach ($statuses as $val=>$label):
                $id = 'st_'.($val===''?'all':$val);
            ?>
              <input type="radio" id="<?= e($id) ?>" name="status" value="<?= e($val) ?>" <?= $st===$val?'checked':'' ?>>
              <label for="<?= e($id) ?>"><?= e($label) ?></label>
            <?php endforeach; ?>
          </div>

          <!-- Date range -->
          <div class="pill pill-range">
            <span class="icon">📅</span>
            <input type="date" name="from" class="pill-input" value="<?= e($filters['from'] ?? '') ?>">
            <span class="sep">–</span>
            <input type="date" name="to"   class="pill-input" value="<?= e($filters['to'] ?? '') ?>">
          </div>

          <!-- Quick ranges (auto-submit) -->
          <div class="chips">
            <button class="chip" type="button" data-range="today">Today</button>
            <button class="chip" type="button" data-range="thisweek">This week</button>
            <button class="chip" type="button" data-range="next7">Next 7 days</button>
          </div>

          <!-- Actions (Apply removed; keep Reset + Export) -->
          <div class="actions">
            <a class="btn btn-ghost" href="<?= e(BASE_URL.'index.php?page=admin_appointments') ?>">Reset</a>
          </div>
        </form>
      </div>

      <?php if (!$rows): ?>
        <div class="empty">No appointments found.</div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th style="width:22%">Office</th>
                <th style="width:20%">Patient</th>
                <th style="width:18%">Doctor</th>
                <th style="width:14%">Start</th>
                <th style="width:14%">End</th>
                <th style="width:12%">Status</th>
                <th style="width:8%">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php $prevOffice = null; foreach ($rows as $r):
                    $office  = $r['office_name'];
                    $patient = $r['patient_name'] ?: ('#'.$r['patient_id']);
                    if ($office !== $prevOffice): ?>
                <tr class="office-row">
                  <td colspan="7">
                    <span class="off-name"><?= e($office) ?></span>
                    <?php if (!empty($officeCounts[$office])): ?>
                      <span class="off-count"><?= (int)$officeCounts[$office] ?> appt</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php $prevOffice = $office; endif; ?>
                <tr class="data">
                  <td class="muted"><?= e($office) ?></td>
                  <td><?= e($patient) ?></td>
                  <td><?= e($r['doctor_name']) ?></td>
                  <td><?= e(substr($r['start_time'],0,16)) ?></td>
                  <td><?= e(substr($r['end_time'],0,16)) ?></td>
                  <td><span class="badge <?= e($r['appt_status']) ?>"><?= e($r['appt_status']) ?></span></td>
                  <td>
                    <form method="post" action="<?= e(BASE_URL.'index.php?page=admin_appointments') ?>"
                          onsubmit="return confirm('Delete appointment #<?= (int)$r['appointment_id'] ?>? This cannot be undone.')"
                          style="display:inline">
                      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                      <input type="hidden" name="act"  value="delete">
                      <input type="hidden" name="appointment_id" value="<?= (int)$r['appointment_id'] ?>">
                      <input type="hidden" name="back" value="<?= e('page=admin_appointments&'.http_build_query($filters+['p'=>$page])) ?>">
                      <button class="icon-btn danger" title="Delete appointment" aria-label="Delete">
                        <span class="icon-trash">🗑</span>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="pager">
          <?php if ($page>1): ?>
            <a class="btn" href="<?= e(BASE_URL.'index.php?page=admin_appointments&'.http_build_query($filters+['p'=>$page-1])) ?>">‹ Prev</a>
          <?php endif; ?>
          <span>Page <?= (int)$page ?> / <?= (int)$pages ?> &nbsp;•&nbsp; <?= (int)$total ?> total</span>
          <?php if ($page<$pages): ?>
            <a class="btn" href="<?= e(BASE_URL.'index.php?page=admin_appointments&'.http_build_query($filters+['p'=>$page+1])) ?>">Next ›</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</section>

<style>
.flash{margin:12px 16px;padding:10px 12px;border-radius:10px;border:1px solid #e6ebf5;background:#fff}
.flash.success{border-color:#c9ead3;background:#eaf6ee;color:#0f5a32}
.flash.error{border-color:#ffd3d1;background:#fff5f5;color:#8a1d1d}

.headbar{padding:12px 16px;border-bottom:1px solid #edf1f7;background:linear-gradient(180deg,#fbfcff 0%,#f6f9ff 100%)}
.filters-bar{display:flex;flex-wrap:wrap;gap:10px;align-items:center}

.pill{display:inline-flex;align-items:center;gap:8px;padding:8px 10px;background:#fff;border:1px solid #dbe2ea;border-radius:999px;box-shadow:0 1px 0 rgba(32,56,103,.03)}
.pill .icon{font-size:1rem;opacity:.9}
.pill .pill-input{border:0;outline:0;background:transparent;padding:0 2px;min-width:160px}
.pill select.pill-input{appearance:none;padding-right:18px}
.pill-range .sep{opacity:.6}

.seg{display:inline-flex;align-items:center;gap:6px;padding:6px;background:#fff;border:1px solid #dbe2ea;border-radius:999px}
.seg input{position:absolute;opacity:0;pointer-events:none}
.seg label{padding:6px 10px;border-radius:999px;cursor:pointer;border:1px solid transparent;color:#274686;user-select:none}
.seg input:checked+label{background:#eaf1ff;border-color:#d6e6ff;color:#143a73;box-shadow:0 1px 0 rgba(32,56,103,.05) inset}

.chips{display:flex;gap:6px;align-items:center}
.chip{padding:6px 10px;border-radius:999px;border:1px solid #dbe2ea;background:#fff;cursor:pointer;font-size:.92rem}
.chip:hover{background:#f8fbff}

.actions{display:flex;gap:8px;align-items:center}
.btn{padding:9px 12px;border:1px solid #dbe2ea;border-radius:10px;background:#fff;cursor:pointer}
.btn-primary{background:#274686;border-color:#274686;color:#fff}
.btn-ghost{background:#fff;color:#1f315c}

.table-wrap{overflow:auto;border-top:1px solid #e6ebf5}
.tbl{width:100%;border-collapse:separate;border-spacing:0}
.tbl thead th{position:sticky;top:0;z-index:1;background:linear-gradient(#f7f9fc,#f4f7fb);text-align:left;font-weight:600;color:#1f315c;padding:10px;border-bottom:1px solid #e6ebf5}
.tbl tbody td{padding:10px;border-bottom:1px solid #f0f3f9;vertical-align:middle}
.tbl tbody tr.data:hover td{background:#fbfdff}
.muted{color:#6b7280}

.office-row td{background:#f5f8ff;color:#1f315c;border-bottom:1px solid #e6ebf5;position:sticky;top:42px;z-index:0;padding:10px 10px}
.off-name{font-weight:700}
.off-count{margin-left:8px;font-size:.86rem;color:#274686;padding:2px 8px;border-radius:999px;background:#eaf1ff;border:1px solid #d6e6ff}

.badge{padding:2px 10px;border-radius:999px;border:1px solid #e6ebf5;background:#fff;font-weight:600;font-size:.92rem}
.badge.booked{background:#eaf6ee;border-color:#c9ead3;color:#0f5a32}
.badge.completed{background:#eef7ff;border-color:#cfe7ff;color:#1a4f7a}
.badge.canceled{background:#fff5f5;border-color:#ffd3d1;color:#8a1d1d}
.badge.no-show{background:#fff1e6;border-color:#ffd9bd;color:#8a4b1d}

.icon-btn{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid #e6ebf5;background:#fff}
.icon-btn:hover{background:#fff5f5;border-color:#ffd3d1}
.icon-btn.danger{color:#a11b1b}
.icon-trash{line-height:1}

.pager{display:flex;gap:8px;align-items:center;padding:12px 16px}

@media (max-width:860px){
  .pill .pill-input{min-width:120px}
  .seg{order:3;width:100%;overflow:auto}
  .chips{order:4}
  .actions{order:5;width:100%;justify-content:flex-start}
}
</style>

<script>
(function(){
  const form  = document.getElementById('apptFilters');
  if (!form) return;

  const inputFrom = form.querySelector('input[name="from"]');
  const inputTo   = form.querySelector('input[name="to"]');
  const pageInput = form.querySelector('input[name="p"]');

  function resetToFirstPage(){ if (pageInput) pageInput.value = '1'; }
  function autoSubmit(){ resetToFirstPage(); form.submit(); }

  // Office change
  form.querySelectorAll('select.pill-input').forEach(el => {
    el.addEventListener('change', autoSubmit);
  });

  // Status change
  form.querySelectorAll('.seg input[type="radio"]').forEach(el => {
    el.addEventListener('change', autoSubmit);
  });

  // Date change
  [inputFrom, inputTo].forEach(el => {
    if (!el) return;
    el.addEventListener('change', autoSubmit);
    el.addEventListener('keydown', e => { if (e.key === 'Enter'){ e.preventDefault(); autoSubmit(); }});
  });

  // Quick ranges
  function fmt(d){ const z=n=>String(n).padStart(2,'0'); return `${d.getFullYear()}-${z(d.getMonth()+1)}-${z(d.getDate())}`; }
  function startOfWeek(d){ const x=new Date(d.getFullYear(),d.getMonth(),d.getDate()); const dow=(x.getDay()+6)%7; x.setDate(x.getDate()-dow); return x; }

  form.querySelectorAll('.chip').forEach(ch => {
    ch.addEventListener('click', () => {
      const kind = ch.getAttribute('data-range');
      const now  = new Date();
      if (kind === 'today'){
        const t = fmt(now); inputFrom.value = t; inputTo.value = t;
      } else if (kind === 'thisweek'){
        const s = startOfWeek(now); const e = new Date(s); e.setDate(s.getDate()+6);
        inputFrom.value = fmt(s); inputTo.value = fmt(e);
      } else if (kind === 'next7'){
        const s = now; const e = new Date(now); e.setDate(now.getDate()+7);
        inputFrom.value = fmt(s); inputTo.value = fmt(e);
      }
      autoSubmit();
    });
  });
})();
</script>