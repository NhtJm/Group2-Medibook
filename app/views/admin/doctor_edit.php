<?php if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
$doc = $data['doctor'] ?? null;
$docName = $doc['doctor_name'] ?? $doc['name'] ?? ('#' . ($doc['doctor_id'] ?? ''));
$specs = $data['specialties'] ?? [];
$offs = $data['offices'] ?? [];
$flash = $data['flash'] ?? null;

if (session_status() === PHP_SESSION_NONE)
    session_start();
if (empty($_SESSION['csrf']))
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
?>
<section class="admin-shell">
    <?php $active = 'admin_doctors';
    require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-main admin-doctor-edit">
        <?php
        $title = $doc ? ('Edit Doctor · ' . $docName) : 'Edit Doctor';
        $searchAction = BASE_URL . 'index.php';
        $searchHidden = ['page' => 'admin_doctors'];
        require __DIR__ . '/partials/topbar.php';
        ?>

        <section class="panel">
            <?php if ($flash): ?>
                <div class="flash <?= $flash['type'] === 'success' ? 'ok' : 'err' ?>"><?= e($flash['msg']) ?></div>
            <?php endif; ?>

            <?php if (!$doc): ?>
                <div class="panel__head">Doctor not found</div>
            <?php else: ?>
                <!-- Header -->
                <div class="de-head">
                    <div class="de-avatar">
                        <?php if (!empty($doc['photo'])): ?>
                            <img id="de-photo" src="<?= e($doc['photo']) ?>" alt="" onerror="this.remove()">
                        <?php else: ?>
                            <div id="de-fallback"><?= e(mb_substr($docName, 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="de-title"><?= e($docName) ?></div>
                        <div class="de-sub">Office: <?= e($doc['office_name']) ?></div>
                    </div>
                </div>

                <!-- Edit form -->
                <form class="de-form" method="post"
                    action="<?= e(BASE_URL . 'index.php?page=admin_doctor_edit&doctor=' . (int) $doc['doctor_id']) ?>">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

                    <div class="grid">
                        <label><span>Full name *</span>
                            <input class="input" type="text" name="name" required value="<?= e($docName) ?>"></label>

                        <label><span>Email *</span>
                            <input class="input" type="email" name="email" required value="<?= e($doc['email']) ?>"></label>

                        <label><span>Office *</span>
                            <select class="select" name="office_id" required>
                                <?php foreach ($offs as $o): ?>
                                    <option value="<?= (int) $o['office_id'] ?>"
                                        <?= (int) $o['office_id'] === (int) $doc['office_id'] ? 'selected' : '' ?>>
                                        <?= e($o['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label><span>Specialty</span>
                            <select class="select" name="specialty_id">
                                <option value="0">(none)</option>
                                <?php foreach ($specs as $s): ?>
                                    <option value="<?= (int) $s['specialty_id'] ?>"
                                        <?= (int) $s['specialty_id'] === (int) $doc['specialty_id'] ? 'selected' : '' ?>>
                                        <?= e($s['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="span-2">
                            <span>Photo</span>
                            <input class="input" type="text" name="photo" id="photoInput"
                                value="<?= e($doc['photo'] ?? '') ?>"
                                placeholder="doctor_427_1.jpg or https://example.com/pic.jpg">
                            <div class="help">Leave blank to keep current photo. Enter a filename or a full URL to change
                                it.</div>
                            <label style="display:inline-flex;gap:6px;align-items:center;margin-top:6px;">
                                <input type="checkbox" name="clear_photo" value="1"> Remove photo
                            </label>
                        </label>

                        <label><span>Degree</span><input class="input" name="degree"
                                value="<?= e($doc['degree'] ?? '') ?>"></label>
                        <label><span>Graduate</span><input class="input" name="graduate"
                                value="<?= e($doc['graduate'] ?? '') ?>"></label>
                    </div>

                    <div class="form-actions">
                        <a class="btn btn-ghost"
                            href="<?= e(BASE_URL . 'index.php?page=admin_doctors_office&office=' . (int) $doc['office_id']) ?>">←
                            Back</a>
                        <button class="btn btn-primary" type="submit">Save changes</button>
                    </div>
                </form>

                <!-- === SCHEDULE (Google Calendar–style) === -->
                <section class="panel de-schedule">
                    <div class="de-sched-head">
                        <div class="h-left">
                            <strong>Schedule</strong> <span class="sub">(30-minute blocks)</span>
                            <span id="wk-label" class="wk-label"></span>
                        </div>

                        <div class="h-right">
                            <button class="btn" id="wk-prev">‹</button>
                            <button class="btn" id="wk-today">Today</button>
                            <button class="btn" id="wk-next">›</button>

                            <div class="bulk">
                              <label>Hours
                                <input type="number" id="h-from" value="0" min="0" max="23">–
                                <input type="number" id="h-to"   value="24" min="1" max="24">
                              </label>
                              <label><input type="checkbox" class="dchk" value="1"> Mon</label>
                              <label><input type="checkbox" class="dchk" value="2"> Tue</label>
                              <label><input type="checkbox" class="dchk" value="3"> Wed</label>
                              <label><input type="checkbox" class="dchk" value="4"> Thu</label>
                              <label><input type="checkbox" class="dchk" value="5"> Fri</label>
                              <label><input type="checkbox" class="dchk" value="6"> Sat</label>
                              <label><input type="checkbox" class="dchk" value="7"> Sun</label>

                              <button class="btn btn-primary" id="bulk-gen">Generate week</button>
                              <button class="btn btn-danger" id="wk-clear">Clear week</button>
                            </div>
                        </div>
                    </div>

                    <div class="cal">
                        <div class="cal-header">
                            <div class="cal-gutter"></div>
                            <div class="cal-days" id="cal-days"></div>
                        </div>

                        <div class="cal-wrap" id="cal-wrap">
                            <div class="cal-times" id="cal-times"></div>
                            <div class="cal-grid" id="cal-grid">
                                <div class="cal-cols" id="cal-cols"></div>
                                <div class="cal-slots" id="cal-slots"></div>
                                <div class="cal-drag" id="cal-drag" hidden></div>
                            </div>
                        </div>
                    </div>

                    <!-- Context menu -->
                    <div id="slot-menu" class="slot-menu" hidden>
                        <button class="mitem" data-act="toggle">Toggle available/unavailable</button>
                        <button class="mitem" data-act="delete">Delete</button>
                    </div>

                    <!-- Slot popover (left-click) -->
                    <div id="slot-pop" class="slot-pop" hidden>
                      <div class="sp-head">Edit slot</div>

                      <div class="sp-row">
                        <label>Day
                          <select id="sp-day">
                            <option value="0">Mon</option>
                            <option value="1">Tue</option>
                            <option value="2">Wed</option>
                            <option value="3">Thu</option>
                            <option value="4">Fri</option>
                            <option value="5">Sat</option>
                            <option value="6">Sun</option>
                          </select>
                        </label>
                      </div>

                      <div class="sp-row">
                        <label>Start
                          <input type="time" id="sp-time" step="300">
                        </label>
                        <span class="sp-note">Auto end after 30 minutes</span>
                      </div>

                      <div class="sp-actions">
                        <button class="btn" id="sp-cancel">Cancel</button>
                        <button class="btn btn-primary" id="sp-save">Save</button>
                      </div>

                      <hr class="sp-hr">
                      <div class="sp-actions">
                        <button class="btn" id="sp-toggle">Mark unavailable</button>
                        <button class="btn" id="sp-delete" style="color:#a11b1b;border-color:#ffd3d1;background:#fff5f5;">Delete</button>
                      </div>
                    </div>
                </section>
            <?php endif; ?>
        </section>
    </div>
</section>

<style>
    /* Panel + form */
    .admin-doctor-edit .panel { border:1px solid #e6ebf5; border-radius:14px; background:#fff }
    .flash { margin:12px; border-radius:10px; padding:10px }
    .flash.ok { background:#eaf6ee; border:1px solid #c9ead3; color:#1c6a36 }
    .flash.err { background:#fff5f5; border:1px solid #ffd3d1; color:#a11b1b }

    .de-head { display:flex; align-items:center; gap:14px; padding:14px 16px; border-bottom:1px solid #edf1f7 }
    .de-avatar { width:52px; height:52px; border-radius:14px; border:1px solid #e6ebf5; overflow:hidden; background:#fff; display:flex; align-items:center; justify-content:center }
    .de-avatar img { width:100%; height:100%; object-fit:cover }
    .de-title { font-weight:700; color:#1f315c }
    .de-sub { color:#6b7280 }

    .de-form { padding:16px }
    .grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px }
    .grid .span-2 { grid-column: span 2 }
    .input, .select { width:100%; padding:10px; border:1px solid #dbe2ea; border-radius:10px }
    .help { font-size:.85rem; color:#6b7280; margin-top:4px }
    .form-actions { display:flex; justify-content:space-between; align-items:center; margin-top:12px }
    .btn { padding:10px 12px; border-radius:10px; border:1px solid #dbe2ea; background:#fff; cursor:pointer }
    .btn-primary { background:#274686; color:#fff; border-color:#274686 }
    .btn-primary:hover { filter:brightness(1.05) }

    /* Schedule card */
    .de-schedule { margin-top:16px; border:1px solid #e6ebf5; border-radius:14px; background:#fff; box-shadow:0 6px 20px rgba(32,56,103,.06) }
    .de-sched-head { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:12px 14px; border-bottom:1px solid #edf1f7 }
    .de-sched-head .sub { color:#6b7280; font-weight:500; margin-left:6px }
    .wk-label { color:#4b587c; font-weight:600 }
    .de-sched-head .btn { height:34px; padding:6px 10px; border-radius:10px; border:1px solid #dbe2ea; background:#fff }
    .de-sched-head .btn:hover { box-shadow:0 2px 10px rgba(32,56,103,.08) }
    .de-sched-head .btn.btn-primary { background:#274686; border-color:#274686; color:#fff }

    .bulk { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-left:8px }
    .bulk label { color:#4b587c; font-size:.92rem; display:flex; align-items:center; gap:6px }
    .bulk input[type=number] { width:58px; padding:6px 8px; border:1px solid #dbe2ea; border-radius:8px }

    /* Calendar layout */
    .cal { --rowH:24px; --cols:7; --gutterW:70px; --colGap:1px; --lineColor:#eef2f7; }
    .cal-header { display:grid; grid-template-columns:var(--gutterW) 1fr; border-bottom:1px solid #e9edf5 }
    .cal-gutter { background:#fff }
    .cal-days { display:grid; grid-template-columns:repeat(var(--cols), 1fr); gap:var(--colGap); background:#fff }
    .cal-days .day { padding:8px 6px; font-weight:600; color:#1f315c; text-align:center }
    .cal-wrap { display:grid; grid-template-columns:var(--gutterW) 1fr; height:640px; overflow:auto; border-top:1px solid #e9edf5; border-bottom-left-radius:14px; border-bottom-right-radius:14px }
    .cal-times { background:#fff; border-right:1px solid #e9edf5; position:relative }
    .cal-grid { position:relative; background:#fff }

    .cal-times .tick { height:var(--rowH); border-bottom:1px solid var(--lineColor); font-size:.86rem; color:#6b7280; display:flex; align-items:flex-start; justify-content:flex-end; padding:2px 8px 0 0 }
    .cal-times .tick.quarter { opacity:.55 }

    .cal-cols { position:absolute; inset:0; display:grid; grid-template-columns:repeat(var(--cols), 1fr); gap:var(--colGap) }
    .cal-cols .col { position:relative; border-right:1px solid #e9edf5 }

    .cal-grid::before {
        content:""; position:absolute; left:0; right:0; top:0; bottom:0;
        background: repeating-linear-gradient(to bottom, var(--lineColor), var(--lineColor) 1px, transparent 1px, transparent calc(var(--rowH)));
        pointer-events:none;
    }
    .btn-danger { color:#a11b1b; border-color:#ffd3d1; background:#fff5f5 }
.btn[disabled] { opacity:.6; cursor:not-allowed }
    .cal-slots { position:relative; inset:0 }

    .slot {
      position:absolute; left:0; right:0; margin-left:4px; margin-right:4px;
      border-radius:8px; border:1px solid; padding:6px 8px;
      font-size:.86rem; line-height:1.2; cursor:pointer;
      box-shadow:0 2px 10px rgba(32,56,103,.08); user-select:none;
      display:flex; align-items:center; justify-content:center; font-weight:600;
    }
    .slot.available { background:#eaf6ee; border-color:#c9ead3; color:#0f5a32 }
    .slot.unavailable { background:#fff5f5; border-color:#ffd3d1; color:#8a1d1d }

    .cal-drag { position:absolute; background:rgba(39,70,134,.12); border:1px solid rgba(39,70,134,.35); border-radius:8px; pointer-events:none }

    .slot-menu {
        position: fixed; z-index: 3000; min-width: 180px; padding: 6px;
        border:1px solid #e6ebf5; border-radius:10px; background:#fff; box-shadow:0 12px 30px rgba(32,56,103,.18)
    }
    .slot-menu .mitem { width:100%; text-align:left; border:0; background:transparent; cursor:pointer; border-radius:8px; padding:8px 10px; color:#1f315c }
    .slot-menu .mitem:hover { background:#f5f7fb }

    /* Popover */
    .slot-pop{
      position: fixed; z-index: 3001; min-width: 240px;
      padding: 10px; border:1px solid #e6ebf5; border-radius:10px;
      background:#fff; box-shadow:0 12px 30px rgba(32,56,103,.18)
    }
    .slot-pop .sp-head{ font-weight:700; color:#1f315c; margin-bottom:6px }
    .slot-pop .sp-row{ display:flex; align-items:center; gap:10px; margin:8px 0 }
    .slot-pop label{ color:#4b587c; font-size:.92rem; display:flex; flex-direction:column; gap:6px }
    .lot-pop input[type=time], .slot-pop select{ padding:6px 8px; border:1px solid #dbe2ea; border-radius:8px }
    .slot-pop .sp-note{ color:#6b7280; font-size:.85rem }
    .slot-pop .sp-actions{ display:flex; gap:8px; justify-content:flex-end; margin-top:6px }
    .slot-pop .sp-hr{ border:none; border-top:1px solid #edf1f7; margin:10px 0 }
</style>

<script>
    /* Live photo preview */
    (() => {
        const input = document.querySelector('input[name="photo"]');
        const wrap = document.querySelector('.de-avatar');
        if (!input || !wrap) return;
        input.addEventListener('input', () => {
            const url = input.value.trim();
            wrap.innerHTML = '';
            if (!url) { const f = document.createElement('div'); f.id = 'de-fallback'; f.textContent = '?'; wrap.appendChild(f); return; }
            const img = document.createElement('img'); img.id = 'de-photo'; img.alt = '';
            img.onerror = () => img.remove(); img.src = url; wrap.appendChild(img);
        });
    })();
</script>

<script>
    (() => {
        const BASE = '<?= e(BASE_URL) ?>';
        const API = BASE + 'index.php?page=admin_doctor_slots_api';
        const CSRF = '<?= e($csrf) ?>';
        const DID = <?= (int) $doc['doctor_id'] ?>;

        const pop = document.getElementById('slot-pop');
        const spDay   = document.getElementById('sp-day');
        const spTime  = document.getElementById('sp-time');
        const spSave  = document.getElementById('sp-save');
        const spCancel= document.getElementById('sp-cancel');
        const spDelete= document.getElementById('sp-delete');
        const spToggle= document.getElementById('sp-toggle');

        let popTarget = null; // the <div.slot> being edited

        const daysH = document.getElementById('cal-days');
        const times = document.getElementById('cal-times');
        const cols = document.getElementById('cal-cols');
        const grid = document.getElementById('cal-grid');
        const slotsLayer = document.getElementById('cal-slots');
        const wrap = document.getElementById('cal-wrap');
        const dragBox = document.getElementById('cal-drag');
        const wkLbl = document.getElementById('wk-label');
        const menu = document.getElementById('slot-menu');
        const hFrom = document.getElementById('h-from');
        const hTo = document.getElementById('h-to');

        const STEP_MIN = 30, TICK_MIN = 15, ROW_H = 24;
        const FREE_STEP = 5; // minutes to snap start time
        spTime.step = String(FREE_STEP * 60); // seconds for <input type=time>

        // VIEW is fixed to full day
        const VIEW_FROM = 0;
        const VIEW_TO   = 24;

        // Keep HFROM/HTO as view boundaries for all math (do not change at runtime)
        const HFROM = VIEW_FROM;
        const HTO   = VIEW_TO;

        function minutesFromY(y){
          // Convert y-pixels to minutes using visual scale (ROW_H = 15 min)
          const mins = (y / ROW_H) * TICK_MIN; // TICK_MIN = 15
          return Math.round(mins / FREE_STEP) * FREE_STEP;   // snap to FREE_STEP
        }
        const startOfWeek = d => { const x = new Date(d.getFullYear(), d.getMonth(), d.getDate()); const day = (x.getDay() + 6) % 7; x.setDate(x.getDate() - day); x.setHours(0, 0, 0, 0); return x; };
        const addDays = (d, n) => { const x = new Date(d); x.setDate(x.getDate() + n); return x; };
        const ymd = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        const hm = (h, m) => `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
        const mysqlDT = d => `${ymd(d)} ${hm(d.getHours(), d.getMinutes())}:00`;
        const weekHuman = a => { const b = addDays(a, 6); const F = { month: 'short', day: '2-digit' }; return `${a.toLocaleDateString(undefined, F)} → ${b.toLocaleDateString(undefined, F)}  (${ymd(a)} → ${ymd(b)})`; };

        async function call(action, payload = {}) {
            const res = await fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.assign({ action, csrf: CSRF, doctor_id: DID }, payload))
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            let json;
            try { json = await res.json(); }
            catch { throw new Error('Invalid JSON from API'); }
            return json;
        }

        let wk0 = startOfWeek(new Date());
        let data = new Map();

        function renderFrame() {
            // days header
            daysH.innerHTML = '';
            for (let d = 0; d < 7; d++) {
                const dt = addDays(wk0, d);
                const el = document.createElement('div');
                el.className = 'day';
                el.textContent = dt.toLocaleDateString(undefined, { weekday: 'long' }) + ' ' + ymd(dt);
                daysH.appendChild(el);
            }
            // time gutter (00:00 → 24:00)
            times.innerHTML = '';
            const ticks = ((HTO - HFROM) * 60) / TICK_MIN; // 24h
            for (let t = 0; t < ticks; t++) {
                const m = t * TICK_MIN, hh = HFROM + Math.floor(m / 60), mi = m % 60;
                const tick = document.createElement('div');
                tick.className = 'tick' + (mi ? ' quarter' : '');
                tick.style.height = ROW_H + 'px';
                tick.textContent = hm(hh % 24, mi);
                times.appendChild(tick);
            }
            // background day columns
            cols.innerHTML = '';
            for (let d = 0; d < 7; d++) { const c = document.createElement('div'); c.className = 'col'; cols.appendChild(c); }

            wkLbl.textContent = weekHuman(wk0);

            // set grid height (full day) & clear slots
            const totalPx = ((HTO - HFROM) * 60 / TICK_MIN) * ROW_H; // 96 * 24px = 2304px
            grid.style.height = totalPx + 'px';
            slotsLayer.innerHTML = '';
        }

        function placeSlot(s){
          const start = new Date(s.start_time.replace(' ', 'T'));
          const end   = new Date(s.end_time.replace(' ', 'T'));
          const dayIx = ((start.getDay()+6)%7);
          const minFromH = (start.getHours()*60 + start.getMinutes()) - HFROM*60;
          if (minFromH < 0) return;

          const top = (minFromH / TICK_MIN) * ROW_H;
          const durMin = (end - start) / 60000;
          const height = (durMin / TICK_MIN) * ROW_H;

          const colW = grid.clientWidth / 7;
          const left = dayIx * colW;

          const el = document.createElement('div');
          el.className = 'slot ' + (s.status === 'unavailable' ? 'unavailable' : 'available');
          el.style.top    = `${top}px`;
          el.style.left   = `${left + 2}px`;
          el.style.width  = `${colW - 4}px`;
          el.style.height = `${Math.max(height - 2, ROW_H)}px`;

          el.dataset.slotId  = s.slot_id;
          el.dataset.status  = s.status;
          el.dataset.start   = s.start_time;
          el.dataset.end     = s.end_time;

          el.title = `${s.status} • ${s.start_time.slice(11,16)}–${s.end_time.slice(11,16)}`;
          el.textContent = `${s.start_time.slice(11,16)}–${s.end_time.slice(11,16)}`;

          // prevent grid drag start when interacting with a slot
          el.addEventListener('mousedown', (e)=> e.stopPropagation());

          // LEFT CLICK -> popover
          el.addEventListener('click', (e)=>{
            e.stopPropagation();
            showSlotPop(el);
          });

          // RIGHT CLICK -> context menu
          el.addEventListener('contextmenu', (e)=>{
            e.preventDefault();
            showMenu(e.pageX, e.pageY, el);
          });

          slotsLayer.appendChild(el);
        }

        async function loadWeek() {
            renderFrame();
            let j = { ok: false, slots: [] };
            try {
                j = await call('list', { start: ymd(wk0), end: ymd(addDays(wk0, 7)) });
            } catch (err) {
                console.error('Slots API', err);
                showWarn('Can’t load slots (check API route/JSON).');
            }

            data.clear();
            if (j.ok && Array.isArray(j.slots)) {
                for (const s of j.slots) { data.set(s.start_time, s); placeSlot(s); }
            }

            // scroll to current time
            const now = new Date();
            if (now >= wk0 && now < addDays(wk0, 7)) {
                const minNow = now.getHours() * 60 + now.getMinutes();
                const y = Math.max(0, ((minNow - HFROM * 60) / TICK_MIN) * ROW_H - 150);
                wrap.scrollTop = y;
            }
        }

        // drag create
        let dragging = null;
        grid.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return;
            if (e.target.closest('.slot')) return;
            if (e.target.closest('#slot-menu')) return;

            const rect = grid.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const colW = grid.clientWidth / 7;
            const dayIx = Math.min(6, Math.max(0, Math.floor(x / colW)));

            dragging = { dayIx, y0: y, y1: y, started: false };
            updateDrag();

            function onMove(ev) {
                const dy = Math.abs(ev.clientY - e.clientY);
                if (!dragging.started && dy < 6) return;
                dragging.started = true;
                dragging.y1 = Math.max(0, Math.min(grid.clientHeight, ev.clientY - rect.top));
                updateDrag();
            }
            function onUp() {
              document.removeEventListener('mousemove', onMove);
              document.removeEventListener('mouseup', onUp);
              finishDrag();
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });

        function updateDrag() {
            if (!dragging) { dragBox.hidden = true; return; }
            const colW = grid.clientWidth / 7;
            const L = dragging.dayIx * colW + 2;
            const T = Math.min(dragging.y0, dragging.y1);
            const H = Math.max(2, Math.abs(dragging.y1 - dragging.y0));
            dragBox.style.left = `${L}px`; dragBox.style.top = `${T}px`;
            dragBox.style.width = `${colW - 4}px`; dragBox.style.height = `${H}px`;
            dragBox.hidden = false;
        }

        async function finishDrag(){
          if (!dragging){ dragBox.hidden = true; return; }

          const topY = Math.min(dragging.y0, dragging.y1);
          const botY = Math.max(dragging.y0, dragging.y1);
          const day  = addDays(wk0, dragging.dayIx);

          // click-size -> single 30-min slot
          const selMin = (botY - topY) / ROW_H * TICK_MIN;
          if (selMin < STEP_MIN * 0.5){
            let mFromTop = minutesFromY(topY) + HFROM*60;
            const dayStart = HFROM*60, dayEnd = HTO*60 - STEP_MIN;
            mFromTop = Math.max(dayStart, Math.min(dayEnd, mFromTop));
            const start = new Date(day.getFullYear(), day.getMonth(), day.getDate(),
                                   Math.floor(mFromTop/60), mFromTop%60, 0, 0);
            const end   = new Date(start.getTime() + STEP_MIN*60000);
            try { await call('upsert', { start: mysqlDT(start), end: mysqlDT(end), status:'available' }); }
            catch (err) { console.error(err); }
            dragging = null; dragBox.hidden = true; loadWeek(); return;
          }

          // drag-range -> multiple blocks
          let mStartFromTop = minutesFromY(topY) + HFROM*60;
          let mEndFromTop   = minutesFromY(botY) + HFROM*60;

          mStartFromTop = Math.max(HFROM*60, Math.min(HTO*60 - STEP_MIN, mStartFromTop));
          mEndFromTop   = Math.max(mStartFromTop + STEP_MIN, Math.min(HTO*60, mEndFromTop));

          const total  = Math.max(STEP_MIN, mEndFromTop - mStartFromTop);
          const blocks = Math.ceil(total / STEP_MIN);

          for (let i = 0; i < blocks; i++){
            const mStart = mStartFromTop + i*STEP_MIN;
            const start  = new Date(day.getFullYear(), day.getMonth(), day.getDate(),
                                    Math.floor(mStart/60), mStart%60, 0, 0);
            const end    = new Date(start.getTime() + STEP_MIN*60000);
            if (start.getHours() < HFROM || start.getHours() >= HTO) continue;
            try { await call('upsert', { start: mysqlDT(start), end: mysqlDT(end), status:'available' }); }
            catch (err) { console.error(err); }
          }

          dragging = null; dragBox.hidden = true;
          loadWeek();
        }

        // context menu
        let menuTarget = null;
        function showMenu(x, y, target) { menuTarget = target; menu.style.left = x + 'px'; menu.style.top = y + 'px'; menu.hidden = false; }
        function hideMenu() { menu.hidden = true; menuTarget = null; }
        document.addEventListener('click', (e) => { if (!menu.hidden && !menu.contains(e.target)) hideMenu(); });
        window.addEventListener('scroll', hideMenu, true);
        window.addEventListener('resize', hideMenu);
        menu.addEventListener('click', async (e) => {
            const btn = e.target.closest('.mitem'); if (!btn || !menuTarget) return;
            const act = btn.dataset.act;
            const id = parseInt(menuTarget.dataset.slotId || '0', 10);
            try {
                if (act === 'toggle') {
                    const to = (menuTarget.dataset.status === 'available') ? 'unavailable' : 'available';
                    const j = await call('toggle', { slot_id: id, status: to });
                    if (j.ok) { menuTarget.dataset.status = to; menuTarget.classList.toggle('available'); menuTarget.classList.toggle('unavailable'); }
                }
                if (act === 'delete') {
                    const j = await call('delete', { slot_id: id });
                    if (j.ok) menuTarget.remove();
                }
            } catch (err) { console.error(err); }
            hideMenu();
        });

        // nav
        document.getElementById('wk-prev').onclick = () => { wk0 = addDays(wk0, -7); loadWeek(); };
        document.getElementById('wk-next').onclick = () => { wk0 = addDays(wk0, 7); loadWeek(); };
        document.getElementById('wk-today').onclick = () => { wk0 = startOfWeek(new Date()); loadWeek(); };
        // Clear all slots in the currently visible week
const clearBtn = document.getElementById('wk-clear');

clearBtn.addEventListener('click', async () => {
  // Collect slot IDs from the in-memory data map (populated by loadWeek)
  const ids = Array.from(data.values())
    .map(s => parseInt(s.slot_id, 10))
    .filter(Boolean);

  if (!ids.length) { showWarn('No slots to clear in this week.'); return; }

  const label = wkLbl.textContent || 'this week';
  if (!confirm(`Delete ${ids.length} slot(s) in ${label}?`)) return;

  clearBtn.disabled = true;
  try {
    // Delete sequentially to be gentle with the API
    for (const id of ids) {
      try { await call('delete', { slot_id: id }); }
      catch (err) { console.error('Delete failed for', id, err); }
    }
    showWarn('All slots in this week have been cleared.');
    await loadWeek();
  } catch (err) {
    console.error(err);
    showWarn('Clear week failed. Please try again.');
  } finally {
    clearBtn.disabled = false;
  }
});
        // bulk generate uses inputs as a range but DOES NOT change the full-day view
        document.getElementById('bulk-gen').onclick = async () => {
            const bf = Math.max(0, Math.min(23, parseInt(hFrom.value || '0', 10)));
            const bt = Math.max(bf + 1, Math.min(24, parseInt(hTo.value || '24', 10)));
            const days = Array.from(document.querySelectorAll('.dchk:checked')).map(x => parseInt(x.value, 10));
            try {
                const j = await call('bulk_generate', { weekStart: ymd(wk0), hoursFrom: bf, hoursTo: bt, days });
                if (j.ok) loadWeek();
            } catch (err) { console.error(err); showWarn('Bulk generate failed.'); }
        };

        // sanitize inputs but don't affect the view
        hFrom.addEventListener('change', () => {
          hFrom.value = Math.max(0, Math.min(23, parseInt(hFrom.value || '0', 10)));
        });
        hTo.addEventListener('change', () => {
          const val = Math.max(1, Math.min(24, parseInt(hTo.value || '24', 10)));
          hTo.value = val;
        });

        function showWarn(msg) {
            const el = document.createElement('div');
            el.style.cssText = 'margin:8px 14px;color:#a11b1b;background:#fff5f5;border:1px solid #ffd3d1;border-radius:8px;padding:6px 10px;';
            el.textContent = msg;
            document.querySelector('.de-schedule').insertBefore(el, document.querySelector('.cal'));
            setTimeout(() => el.remove(), 4000);
        }

        function hhmm(d){ return hm(d.getHours(), d.getMinutes()); }
        function slotDayIxFromStart(startStr){
          const d = new Date(startStr.replace(' ','T'));
          return (d.getDay()+6)%7; // Mon=0..Sun=6
        }

        // Popover anchored beside slot with flip/clamp
        function showSlotPop(slotEl){
          if (typeof hideMenu === 'function') hideMenu();
          popTarget = slotEl;

          const dIx = slotDayIxFromStart(slotEl.dataset.start);
          spDay.value = String(dIx);

          const d = new Date(slotEl.dataset.start.replace(' ','T'));
          spTime.value = hhmm(d);

          spToggle.textContent = (slotEl.dataset.status === 'available') ? 'Mark unavailable' : 'Mark available';

          pop.style.visibility = 'hidden';
          pop.hidden = false;

          const rect = slotEl.getBoundingClientRect();
          const PAD = 8;

          let left = rect.right + PAD;
          let top  = rect.top + (rect.height - pop.offsetHeight) / 2;

          const maxTop = window.innerHeight - pop.offsetHeight - PAD;
          top = Math.max(PAD, Math.min(top, maxTop));

          const maxLeft = window.innerWidth - pop.offsetWidth - PAD;
          if (left > maxLeft) {
            left = rect.left - pop.offsetWidth - PAD;
            if (left < PAD) {
              left = Math.max(PAD, Math.min(rect.left, maxLeft));
              let below = rect.bottom + PAD;
              if (below + pop.offsetHeight > window.innerHeight - PAD) {
                below = rect.top - pop.offsetHeight - PAD;
              }
              top = Math.max(PAD, Math.min(below, maxTop));
            }
          }

          pop.style.left = `${left}px`;
          pop.style.top  = `${top}px`;
          pop.style.visibility = '';
        }

        function hideSlotPop(){ pop.hidden = true; popTarget = null; }
        spCancel.addEventListener('click', hideSlotPop);

        spSave.addEventListener('click', async () => {
          if (!popTarget) return;
          const id = parseInt(popTarget.dataset.slotId, 10);
          const toStatus = popTarget.dataset.status;

          const dayIx = parseInt(spDay.value, 10);
          const [hh, mm] = spTime.value.split(':').map(n=>parseInt(n,10));
          const day = addDays(wk0, dayIx);
          const start = new Date(day.getFullYear(), day.getMonth(), day.getDate(), hh, mm, 0, 0);
          const end   = new Date(start.getTime() + STEP_MIN*60000);

          const mins = hh*60+mm;
          if (mins < HFROM*60 || mins+STEP_MIN > HTO*60){
            showWarn(`Time must be within ${hm(HFROM,0)}–${hm(HTO,0)}.`);
            return;
          }

          try{
            await call('delete', { slot_id: id });
            await call('upsert', { start: mysqlDT(start), end: mysqlDT(end), status: toStatus });
          }catch(err){ console.error(err); }
          hideSlotPop(); loadWeek();
        });

        spDelete.addEventListener('click', async ()=>{
          if (!popTarget) return;
          const id = parseInt(popTarget.dataset.slotId, 10);
          try{
            const j = await call('delete', { slot_id: id });
            if (j.ok) popTarget.remove();
          }catch(err){ console.error(err); }
          hideSlotPop();
        });

        spToggle.addEventListener('click', async ()=>{
          if (!popTarget) return;
          const id = parseInt(popTarget.dataset.slotId, 10);
          const to = (popTarget.dataset.status === 'available') ? 'unavailable' : 'available';
          try{
            const j = await call('toggle', { slot_id: id, status: to });
            if (j.ok){
              popTarget.dataset.status = to;
              popTarget.classList.toggle('available');
              popTarget.classList.toggle('unavailable');
            }
          }catch(err){ console.error(err); }
          hideSlotPop();
        });

        // close popover on outside click/scroll/resize
        document.addEventListener('click', (e)=>{
          if (!pop.hidden && !pop.contains(e.target) && !e.target.closest('.slot')) hideSlotPop();
        });
        window.addEventListener('scroll', hideSlotPop, true);
        window.addEventListener('resize', hideSlotPop);

        loadWeek();
    })();
</script>