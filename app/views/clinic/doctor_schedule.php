<?php
if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}
$doc = $data['doctor'] ?? null;
$csrf = $data['csrf'] ?? ($_SESSION['csrf'] ?? '');
if (!$doc) {
  echo '<div style="padding:24px">Doctor not found.</div>';
  return;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manage Schedule — <?= e($doc['doctor_name'] ?? ('Doctor #' . $doc['doctor_id'])) ?></title>
  <link rel="stylesheet" href="<?= STYLE_PATH ?>/doctor_schedule.css">
</head>

<body style="background:linear-gradient(100deg,#0b1e3b,#2f6fb0 70%,#68afd8);min-height:100vh;margin:0;">
  <main style="max-width:1100px;margin:24px auto;padding:0 12px">
    <header style="display:flex;align-items:center;justify-content:space-between;color:#fff;margin-bottom:12px">
      <div>
        <h1 style="margin:0 0 6px;font-size:22px;font-weight:700">Manage Schedule</h1>
        <div style="opacity:.9">
          <?= e($doc['doctor_name'] ?? ('Doctor #' . $doc['doctor_id'])) ?> · <?= e($doc['office_name'] ?? '') ?>
        </div>
      </div>
      <a href="<?= BASE_URL ?>index.php?page=office_dashboard"
        style="color:#fff;border:1px solid rgba(255,255,255,.5);padding:8px 12px;border-radius:10px;text-decoration:none">←
        Back</a>
    </header>

    <!-- the schedule card (identical structure/classes to admin) -->
    <section class="panel de-schedule"
      style="background:#fff;border-radius:14px;border:1px solid #e6ebf5;box-shadow:0 6px 20px rgba(32,56,103,.06)">
      <div class="de-sched-head"
        style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:12px 14px;border-bottom:1px solid #edf1f7">
        <div class="h-left">
          <strong>Schedule</strong> <span class="sub" style="color:#6b7280;font-weight:500;margin-left:6px">(30-minute
            blocks)</span>
          <span id="wk-label" class="wk-label" style="color:#4b587c;font-weight:600"></span>
        </div>

        <div class="h-right" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
          <button class="btn" id="wk-prev">‹</button>
          <button class="btn" id="wk-today">Today</button>
          <button class="btn" id="wk-next">›</button>

          <div class="bulk" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-left:8px">
            <label>Hours
              <input type="number" id="h-from" value="9" min="0" max="23"
                style="width:58px;padding:6px 8px;border:1px solid #dbe2ea;border-radius:8px">–
              <input type="number" id="h-to" value="17" min="1" max="24"
                style="width:58px;padding:6px 8px;border:1px solid #dbe2ea;border-radius:8px">
            </label>
            <label><input type="checkbox" class="dchk" value="1"> Mon</label>
            <label><input type="checkbox" class="dchk" value="2"> Tue</label>
            <label><input type="checkbox" class="dchk" value="3"> Wed</label>
            <label><input type="checkbox" class="dchk" value="4"> Thu</label>
            <label><input type="checkbox" class="dchk" value="5"> Fri</label>
            <label><input type="checkbox" class="dchk" value="6"> Sat</label>
            <label><input type="checkbox" class="dchk" value="7"> Sun</label>

            <button class="btn btn-primary" id="bulk-gen"
              style="background:#274686;border-color:#274686;color:#fff">Generate week</button>
            <button class="btn btn-danger" id="wk-clear"
              style="color:#a11b1b;border-color:#ffd3d1;background:#fff5f5">Clear week</button>
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

      <div id="slot-menu" class="slot-menu" hidden>
        <button class="mitem" data-act="toggle">Toggle available/unavailable</button>
        <button class="mitem" data-act="delete">Delete</button>
      </div>

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
          <button class="btn btn-primary" id="sp-save"
            style="background:#274686;border-color:#274686;color:#fff">Save</button>
        </div>
        <hr class="sp-hr">
        <div class="sp-actions">
          <button class="btn" id="sp-toggle">Mark unavailable</button>
          <button class="btn" id="sp-delete"
            style="color:#a11b1b;border-color:#ffd3d1;background:#fff5f5;">Delete</button>
        </div>
      </div>
    </section>
  </main>

  <script>
    // ---- wiring ----
    const BASE = '<?= htmlspecialchars(BASE_URL, ENT_QUOTES) ?>';
    const API = BASE + 'index.php?page=office_doctor_slots_api';
    const CSRF = '<?= htmlspecialchars($csrf ?? "", ENT_QUOTES) ?>';
    const DID = <?= (int) ($doc['doctor_id'] ?? 0) ?>;

    // ---- DOM refs ----
    const daysH = document.getElementById('cal-days');
    const times = document.getElementById('cal-times');
    const cols = document.getElementById('cal-cols');
    const grid = document.getElementById('cal-grid');
    const slotsLayer = document.getElementById('cal-slots');
    const wrap = document.getElementById('cal-wrap');
    const wkLbl = document.getElementById('wk-label');
    const dragBox = document.getElementById('cal-drag');

    const menu = document.getElementById('slot-menu');
    const pop = document.getElementById('slot-pop');
    const spDay = document.getElementById('sp-day');
    const spTime = document.getElementById('sp-time');
    const spSave = document.getElementById('sp-save');
    const spCancel = document.getElementById('sp-cancel');
    const spDelete = document.getElementById('sp-delete');
    const spToggle = document.getElementById('sp-toggle');

    // ---- constants ----
    const STEP_MIN = 30, TICK_MIN = 15, ROW_H = 24;
    const VIEW_FROM = 0, VIEW_TO = 24;
    const FREE_STEP = 5;
    spTime && (spTime.step = String(FREE_STEP * 60));

    // ---- helpers ----
    const startOfWeek = d => { const x = new Date(d.getFullYear(), d.getMonth(), d.getDate()); const day = (x.getDay() + 6) % 7; x.setDate(x.getDate() - day); x.setHours(0, 0, 0, 0); return x; };
    const addDays = (d, n) => { const x = new Date(d); x.setDate(x.getDate() + n); return x; };
    const ymd = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    const hm = (h, m) => `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
    const mysqlDT = d => `${ymd(d)} ${hm(d.getHours(), d.getMinutes())}:00`;
    const weekHuman = a => { const b = addDays(a, 6); const F = { month: 'short', day: '2-digit' }; return `${a.toLocaleDateString(undefined, F)} → ${b.toLocaleDateString(undefined, F)}  (${ymd(a)} → ${ymd(b)})`; };

    function minutesFromY(yPx) { const mins = (yPx / ROW_H) * TICK_MIN; return Math.round(mins / FREE_STEP) * FREE_STEP; }

    async function call(action, payload = {}) {
      const body = Object.assign({ action, csrf: CSRF, doctor_id: DID }, payload);
      const res = await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const json = await res.json();
      return json;
    }

    // ---- render frame ----
    function renderFrame(week0) {
      daysH.innerHTML = '';
      for (let d = 0; d < 7; d++) {
        const dt = addDays(week0, d);
        const el = document.createElement('div');
        el.className = 'day';
        el.textContent = dt.toLocaleDateString(undefined, { weekday: 'long' }) + ' ' + ymd(dt);
        daysH.appendChild(el);
      }

      times.innerHTML = '';
      const ticks = ((VIEW_TO - VIEW_FROM) * 60) / TICK_MIN;
      for (let t = 0; t < ticks; t++) {
        const m = t * TICK_MIN, hh = VIEW_FROM + Math.floor(m / 60), mi = m % 60;
        const tick = document.createElement('div');
        tick.className = 'tick' + (mi ? ' quarter' : '');
        tick.style.height = ROW_H + 'px';
        tick.textContent = hm(hh % 24, mi);
        times.appendChild(tick);
      }

      cols.innerHTML = '';
      for (let d = 0; d < 7; d++) cols.appendChild(Object.assign(document.createElement('div'), { className: 'col' }));

      const totalPx = ((VIEW_TO - VIEW_FROM) * 60 / TICK_MIN) * ROW_H;
      grid.style.height = totalPx + 'px';
      slotsLayer.innerHTML = '';
    }

    // ---- slots ----
    function placeSlot(s) {
      const start = new Date(s.start_time.replace(' ', 'T'));
      const end = new Date(s.end_time.replace(' ', 'T'));
      const dayIx = (start.getDay() + 6) % 7;
      const minFromH = (start.getHours() * 60 + start.getMinutes()) - VIEW_FROM * 60;
      if (minFromH < 0) return;

      const top = (minFromH / TICK_MIN) * ROW_H;
      const durMin = (end - start) / 60000;
      const height = (durMin / TICK_MIN) * ROW_H;
      const colW = grid.clientWidth / 7;
      const left = dayIx * colW;

      const el = document.createElement('div');
      el.className = 'slot ' + (s.status === 'unavailable' ? 'unavailable' : 'available');
      el.style.top = `${top}px`;
      el.style.left = `${left + 2}px`;
      el.style.width = `${colW - 4}px`;
      el.style.height = `${Math.max(height - 2, ROW_H)}px`;
      el.textContent = `${s.start_time.slice(11, 16)}–${s.end_time.slice(11, 16)}`;
      el.title = `${s.status} • ${el.textContent}`;
      el.dataset.slotId = s.slot_id;
      el.dataset.status = s.status;
      el.dataset.start = s.start_time;
      el.dataset.end = s.end_time;

      el.addEventListener('mousedown', (e) => e.stopPropagation());
      el.addEventListener('click', (e) => { e.stopPropagation(); showSlotPop(el); });
      el.addEventListener('contextmenu', (e) => { e.preventDefault(); showMenu(e.pageX, e.pageY, el); });

      slotsLayer.appendChild(el);
    }

    // ---- load week with scroll control ----
    let wk0 = startOfWeek(new Date());
    let firstLoad = true;

    async function loadWeek(preserveScroll = false) {
      const prev = preserveScroll ? wrap.scrollTop : 0;
      renderFrame(wk0);
      wkLbl.textContent = weekHuman(wk0);

      try {
        const j = await call('list', { start: ymd(wk0), end: ymd(addDays(wk0, 7)) });
        if (j.ok && Array.isArray(j.slots)) j.slots.forEach(placeSlot);
        else console.warn('list() not ok:', j);
      } catch (e) { console.error(e); }

      if (preserveScroll) {
        wrap.scrollTop = prev;
      } else if (firstLoad) {
        // autoscroll only once on the very first render
        const now = new Date();
        if (now >= wk0 && now < addDays(wk0, 7)) {
          const minNow = now.getHours() * 60 + now.getMinutes();
          const y = Math.max(0, ((minNow - VIEW_FROM * 60) / TICK_MIN) * ROW_H - 150);
          wrap.scrollTop = y;
        }
      }
      firstLoad = false;
    }

    // ---- drag/create on empty grid ----
    let dragging = null;
    grid.addEventListener('mousedown', (e) => {
      if (e.button !== 0) return;
      if (e.target.closest('.slot') || e.target.closest('#slot-menu')) return;

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
      dragBox.style.left = `${L}px`;
      dragBox.style.top = `${T}px`;
      dragBox.style.width = `${colW - 4}px`;
      dragBox.style.height = `${H}px`;
      dragBox.hidden = false;
    }

    async function finishDrag() {
      if (!dragging) { dragBox.hidden = true; return; }

      const topY = Math.min(dragging.y0, dragging.y1);
      const botY = Math.max(dragging.y0, dragging.y1);
      const day = addDays(wk0, dragging.dayIx);

      // click-size -> single 30-min slot
      const selMin = (botY - topY) / ROW_H * TICK_MIN;
      if (selMin < STEP_MIN * 0.5) {
        let mFromTop = minutesFromY(topY) + VIEW_FROM * 60;
        const dayStart = VIEW_FROM * 60, dayEnd = VIEW_TO * 60 - STEP_MIN;
        mFromTop = Math.max(dayStart, Math.min(dayEnd, mFromTop));
        const start = new Date(day.getFullYear(), day.getMonth(), day.getDate(),
          Math.floor(mFromTop / 60), mFromTop % 60, 0, 0);
        const end = new Date(start.getTime() + STEP_MIN * 60000);
        try { await call('upsert', { start: mysqlDT(start), end: mysqlDT(end), status: 'available' }); }
        catch (err) { console.error(err); }
        dragging = null; dragBox.hidden = true; loadWeek(true); return;
      }

      // drag-range -> multiple blocks
      let mStartFromTop = minutesFromY(topY) + VIEW_FROM * 60;
      let mEndFromTop = minutesFromY(botY) + VIEW_FROM * 60;

      mStartFromTop = Math.max(VIEW_FROM * 60, Math.min(VIEW_TO * 60 - STEP_MIN, mStartFromTop));
      mEndFromTop = Math.max(mStartFromTop + STEP_MIN, Math.min(VIEW_TO * 60, mEndFromTop));

      const total = Math.max(STEP_MIN, mEndFromTop - mStartFromTop);
      const blocks = Math.ceil(total / STEP_MIN);

      for (let i = 0; i < blocks; i++) {
        const mStart = mStartFromTop + i * STEP_MIN;
        const start = new Date(day.getFullYear(), day.getMonth(), day.getDate(),
          Math.floor(mStart / 60), mStart % 60, 0, 0);
        const end = new Date(start.getTime() + STEP_MIN * 60000);
        try { await call('upsert', { start: mysqlDT(start), end: mysqlDT(end), status: 'available' }); }
        catch (err) { console.error(err); }
      }

      dragging = null; dragBox.hidden = true;
      loadWeek(true);
    }

    // ---- context menu ----
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
          else console.warn('toggle not ok:', j);
        }
        if (act === 'delete') {
          const j = await call('delete', { slot_id: id });
          if (j.ok) menuTarget.remove(); else console.warn('delete not ok:', j);
        }
      } catch (err) { console.error(err); }
      hideMenu();
    });

    // ---- popover (left-click) ----
    const slotDayIxFromStart = (s) => (new Date(s.replace(' ', 'T')).getDay() + 6) % 7;
    const hhmm = (d) => hm(d.getHours(), d.getMinutes());

    function showSlotPop(slotEl) {
      hideMenu();
      spDay.value = String(slotDayIxFromStart(slotEl.dataset.start));
      spTime.value = hhmm(new Date(slotEl.dataset.start.replace(' ', 'T')));
      spToggle.textContent = (slotEl.dataset.status === 'available') ? 'Mark unavailable' : 'Mark available';

      pop.style.visibility = 'hidden';
      pop.hidden = false;
      const r = slotEl.getBoundingClientRect(), PAD = 8;
      let left = r.right + PAD, top = r.top + (r.height - pop.offsetHeight) / 2;
      const maxTop = window.innerHeight - pop.offsetHeight - PAD; top = Math.max(PAD, Math.min(top, maxTop));
      const maxLeft = window.innerWidth - pop.offsetWidth - PAD;
      if (left > maxLeft) {
        left = r.left - pop.offsetWidth - PAD;
        if (left < PAD) {
          left = Math.max(PAD, Math.min(r.left, maxLeft));
          let below = r.bottom + PAD;
          if (below + pop.offsetHeight > window.innerHeight - PAD) below = r.top - pop.offsetHeight - PAD;
          top = Math.max(PAD, Math.min(below, maxTop));
        }
      }
      pop.style.left = `${left}px`; pop.style.top = `${top}px`; pop.style.visibility = '';
      pop.dataset.targetId = slotEl.dataset.slotId; pop.dataset.targetStatus = slotEl.dataset.status;
    }
    function hideSlotPop() { pop.hidden = true; pop.dataset.targetId = ''; }
    spCancel && spCancel.addEventListener('click', hideSlotPop);

    spSave && spSave.addEventListener('click', async () => {
      const id = parseInt(pop.dataset.targetId || '0', 10);
      if (!id) return;
      const toStatus = pop.dataset.targetStatus || 'available';
      const dayIx = parseInt(spDay.value, 10);
      const [hh, mm] = spTime.value.split(':').map(n => parseInt(n, 10));
      const day = addDays(wk0, dayIx);
      const start = new Date(day.getFullYear(), day.getMonth(), day.getDate(), hh, mm, 0, 0);
      const end = new Date(start.getTime() + STEP_MIN * 60000);
      const mins = hh * 60 + mm;
      if (mins < VIEW_FROM * 60 || mins + STEP_MIN > VIEW_TO * 60) { alert(`Time must be within ${hm(VIEW_FROM, 0)}–${hm(VIEW_TO, 0)}.`); return; }

      try {
        await call('delete', { slot_id: id });
        await call('upsert', { start: mysqlDT(start), end: mysqlDT(end), status: toStatus });
      } catch (err) { console.error(err); }
      hideSlotPop();
      loadWeek(true);
    });

    spDelete && spDelete.addEventListener('click', async () => {
      const id = parseInt(pop.dataset.targetId || '0', 10);
      if (!id) return;
      try { const j = await call('delete', { slot_id: id }); if (!j.ok) console.warn('delete not ok:', j); } catch (err) { console.error(err); }
      hideSlotPop(); loadWeek(true);
    });

    spToggle && spToggle.addEventListener('click', async () => {
      const id = parseInt(pop.dataset.targetId || '0', 10);
      if (!id) return;
      const to = (pop.dataset.targetStatus === 'available') ? 'unavailable' : 'available';
      try { const j = await call('toggle', { slot_id: id, status: to }); if (!j.ok) console.warn('toggle not ok:', j); } catch (err) { console.error(err); }
      hideSlotPop(); loadWeek(true);
    });

    document.addEventListener('click', (e) => { if (!pop.hidden && !pop.contains(e.target) && !e.target.closest('.slot')) hideSlotPop(); });
    window.addEventListener('scroll', hideSlotPop, true);
    window.addEventListener('resize', hideSlotPop);

    // ---- nav + bulk ----
    document.getElementById('wk-prev').onclick = () => { wk0 = addDays(wk0, -7); loadWeek(false); };
    document.getElementById('wk-next').onclick = () => { wk0 = addDays(wk0, 7); loadWeek(false); };
    document.getElementById('wk-today').onclick = () => { wk0 = startOfWeek(new Date()); loadWeek(false); };

    const hFrom = document.getElementById('h-from');
    const hTo = document.getElementById('h-to');

    async function generateClientSide(bf, bt, days) {
      // fallback: produce 30-min blocks via upsert
      for (const di of days) {
        const day = addDays(wk0, di - 1); // Mon=1..Sun=7
        for (let m = bf * 60; m < bt * 60; m += STEP_MIN) {
          const start = new Date(day.getFullYear(), day.getMonth(), day.getDate(), Math.floor(m / 60), m % 60, 0, 0);
          const end = new Date(start.getTime() + STEP_MIN * 60000);
          try { await call('upsert', { start: mysqlDT(start), end: mysqlDT(end), status: 'available' }); }
          catch (e) { console.error('upsert fail', e); }
        }
      }
    }

    document.getElementById('bulk-gen').onclick = async () => {
      const bf = Math.max(0, Math.min(23, parseInt(hFrom.value || '9', 10)));
      const bt = Math.max(bf + 1, Math.min(24, parseInt(hTo.value || '17', 10)));
      const days = Array.from(document.querySelectorAll('.dchk:checked')).map(x => parseInt(x.value, 10));
      if (!days.length) { alert('Pick at least one day.'); return; }

      const btn = document.getElementById('bulk-gen');
      btn.disabled = true;
      try {
        // try server bulk first
        const j = await call('bulk_generate', { weekStart: ymd(wk0), hoursFrom: bf, hoursTo: bt, days });
        console.log('bulk_generate response:', j);
        if (!j || j.ok !== true) {
          // fallback if server didn’t handle it
          console.warn('bulk_generate not available; falling back to client-side generation.');
          await generateClientSide(bf, bt, days);
        }
        await loadWeek(true);
      } catch (e) {
        console.error('bulk_generate error:', e);
        // hard fallback
        await generateClientSide(bf, bt, days);
        await loadWeek(true);
      } finally {
        btn.disabled = false;
      }
    };

    document.getElementById('wk-clear').onclick = async () => {
      if (!confirm('Delete all slots visible in this week?')) return;
      try {
        const j = await call('list', { start: ymd(wk0), end: ymd(addDays(wk0, 7)) });
        if (j.ok) {
          for (const s of j.slots) { try { await call('delete', { slot_id: s.slot_id }); } catch { } }
          loadWeek(true);
        } else {
          console.warn('list for clear not ok:', j);
        }
      } catch (e) { console.error(e); alert('Clear failed.'); }
    };

    // ---- init ----
    loadWeek(false);
  </script>
</body>

</html>