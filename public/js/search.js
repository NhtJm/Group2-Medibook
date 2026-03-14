// public/js/search.js

/* ========== Utils ========== */
function debounce(fn, delay = 250) {
  let t;
  return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), delay); };
}
const esc = s => (s || '').replace(/[&<>"']/g, m => ({
  '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'
}[m]));

/* ========== Elements ========== */
const $q   = document.querySelector('#search-q');
const $loc = document.querySelector('#search-loc');
const $box = document.querySelector('#search-suggest');
const $btn = document.querySelector('#search-btn');

if (!$q || !$loc || !$box) {
  // Không tìm thấy phần tử cần thiết -> thoát sớm để tránh lỗi JS
  console.warn('[search.js] Missing search elements in DOM.');
}

/* ========== Show/Hide dropdown ========== */
function closeSuggest() {
  if ($box) {
    $box.classList.add('hidden');
    $box.innerHTML = '';
  }
}
function openSuggest() {
  if ($box) $box.classList.remove('hidden');
}

/* ========== Fetch & Render ========== */
async function fetchSuggest() {
  if (!$q || !$loc || !$box) return;

  const q   = ($q.value || '').trim();
  const loc = ($loc.value || '').trim();

  if (!q && !loc) { 
    closeSuggest();
    return; 
  }

  const url = new URL(`${BASE_URL}index.php`, window.location.origin);
  url.searchParams.set('page', 'api.search');
  if (q)   url.searchParams.set('q', q);
  if (loc) url.searchParams.set('loc', loc);

  try {
    const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await r.json();
    render(data.results || {});
  } catch (e) {
    console.error('[search.js] fetchSuggest error:', e);
  }
}
const fetchSuggestDebounced = debounce(fetchSuggest, 300);

function render(r) {
  if (!$box) return;

  const parts = [];

  // Doctors
  if ((r.doctors || []).length) {
    parts.push(`<div class="s-head">Doctors</div>`);
    (r.doctors || []).forEach(d => {
      parts.push(`
        <div class="s-item" data-type="doctor" data-id="${d.id}">
          <span class="s-pill">Doctor</span>
          <span>${esc(d.name)}</span>
          <span class="s-meta">
            ${esc(d.specialty_name || '')}${d.specialty_name && (d.office_name || d.address) ? ' · ' : ''}
            ${esc(d.office_name || '')}${d.address && d.office_name ? ' · ' : ''}${esc(d.address || '')}
          </span>
        </div>
      `);
    });
  }

  // Specialties
  if ((r.specialties || []).length) {
    parts.push(`<div class="s-head">Specialties</div>`);
    (r.specialties || []).forEach(s => {
      parts.push(`
        <div class="s-item" data-type="specialty" data-slug="${s.slug}">
          <span class="s-pill">Specialty</span>
          <span>${esc(s.name)}</span>
        </div>
      `);
    });
  }

  // Offices
  if ((r.offices || []).length) {
    parts.push(`<div class="s-head">Clinics</div>`);
    (r.offices || []).forEach(o => {
      parts.push(`
        <div class="s-item" data-type="office" data-id="${o.id}">
          <span class="s-pill">Office</span>
          <span>${esc(o.name)}</span>
          <span class="s-meta">${esc(o.address || '')}</span>
        </div>
      `);
    });
  }

  $box.innerHTML = parts.join('') || `<div class="s-empty">No results</div>`;
  openSuggest();
}

/* ========== Events ========== */
// gõ vào 2 ô input -> gọi suggest
$q?.addEventListener('input', fetchSuggestDebounced);
$loc?.addEventListener('input', fetchSuggestDebounced);

// click vào item -> điều hướng
document.addEventListener('click', (e) => {
  const it = e.target.closest('.s-item');
  if (it) {
    const type = it.dataset.type;

    if (type === 'doctor') {
      const id = it.dataset.id;
      window.location.href = `${BASE_URL}index.php?page=doctor&id=${id}`;
      return;
    }
    if (type === 'specialty') {
      const slug = it.dataset.slug;
      window.location.href = `${BASE_URL}index.php?page=clinics&specialty=${encodeURIComponent(slug)}`;
      return;
    }
    if (type === 'office') {
      const id = it.dataset.id;
      window.location.href = `${BASE_URL}index.php?page=clinic&id=${id}`;
      return;
    }
  } else {
    // click ra ngoài dropdown -> đóng
    if ($box && !e.target.closest('#search-suggest') && !e.target.closest('.search--has-suggest')) {
      closeSuggest();
    }
  }
});

// nút Search → đi tới trang listing tổng hợp
$btn?.addEventListener('click', (e) => {
  e.preventDefault();
  if (!$q || !$loc) return;

  const q   = ($q.value || '').trim();
  const loc = ($loc.value || '').trim();

  const url = new URL(`${BASE_URL}index.php`, window.location.origin);
  url.searchParams.set('page', 'clinics');
  if (q)   url.searchParams.set('q', q);
  if (loc) url.searchParams.set('loc', loc);

  window.location.href = url.toString();
});

// phím Esc -> đóng dropdown
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeSuggest();
});
