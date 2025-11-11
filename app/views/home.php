<?php
require_once __DIR__ . '/../db.php';

/* get mysqli $conn (works with your db.php pattern) */
if (!isset($conn) || !$conn) {
  if (class_exists('Database'))
    $conn = Database::get_instance();
}

if (!function_exists('h')) {
  function h($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}

/* ---- Pagination vars ---- */
$spPage = max(1, (int) ($_GET['sp'] ?? 1));  // current page from ?sp=
$perPage = 6;                                // 3x3 grid per page
$offset = ($spPage - 1) * $perPage;

/* ---- Total count (featured specialties) ----
   NOTE: your snippet queried `Medical_specialty1`. If your real table
   is `Medical_specialty`, change BOTH queries to that. */
$total = 0;
if ($res = $conn->query("SELECT COUNT(*) AS c FROM Medical_specialty WHERE is_featured = 1")) {
  $row = $res->fetch_assoc();
  $total = (int) $row['c'];
  $res->free();
}
$totalPages = max(1, (int) ceil($total / $perPage));

/* ---- Page data ---- */
$specialties = [];
$sql = "
  SELECT specialty_id, slug, name, blurb, image_url
  FROM Medical_specialty
  WHERE is_featured = 1
  ORDER BY sort_order ASC, name ASC
  LIMIT {$perPage} OFFSET {$offset}";
if ($res = $conn->query($sql)) {
  $specialties = $res->fetch_all(MYSQLI_ASSOC);
  $res->free();
}
?>
<section class="hero">
  <div class="hero__inner">
    <!-- LEFT: text -->
    <div class="hero__copy">
      <h1 class="hero__title">
        Connecting <span>patients</span> with care.
      </h1>
      <p class="hero__lead">
        MediBook makes it easy to find the right doctor, schedule appointments online,
        and manage your healthcare—all in one place
      </p>
    </div>
    <div class="hero__art">
      <img src="images/hero-doctor.png" alt="Doctor illustration">
    </div>

    <form class="search search--has-suggest">
      <div class="search__field">
        <label>Doctor, Clinic or Specialty</label>
        <input id="search-q" type="text" placeholder="e.g., Dr. Clark, Cardiology, …" autocomplete="off">
      </div>

      <div class="search__field">
        <label>Location</label>
        <input id="search-loc" type="text" placeholder="Where ?" autocomplete="off">
      </div>

      <button id="search-btn" class="search__btn">Search</button>

      <!-- dropdown gợi ý -->
      <div id="search-suggest" class="search__suggest hidden" role="listbox" aria-label="Search suggestions"></div>
    </form>
  </div>
</section>

<section class="home-specialties container" id="specialties">
  <h2 class="block-title">Popular Specialties</h2>

  <?php if (empty($specialties)): ?>
    <p>No specialties found.</p>
  <?php else: ?>
    <div class="specialty-grid">
      <?php foreach ($specialties as $sp): ?>
        <article class="specialty-card">
          <a class="specialty-media"
            href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=clinics&specialty=<?= h($sp['slug']) ?>">
            <img src="<?= h($sp['image_url']) ?>" alt="<?= h($sp['name']) ?>">
          </a>
          <div class="specialty-body">
            <h3 class="specialty-title"><?= h($sp['name']) ?></h3>
            <?php if (!empty($sp['blurb'])): ?>
              <p class="specialty-blurb"><?= h($sp['blurb']) ?></p>
            <?php endif; ?>
            <a class="specialty-cta"
              href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=clinics&specialty=<?= h($sp['slug']) ?>">
              Find Specialists
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <?php
    // windowed page numbers (max 7 dots wide)
    $win = 7;
    $start = max(1, $spPage - (int) floor(($win - 1) / 2));
    $end = min($totalPages, $start + $win - 1);
    $start = max(1, $end - $win + 1);
    $base = rtrim(BASE_URL, '/') . '/index.php?page=home&sp=';
    $anchor = '#specialties';
    ?>
    <nav class="pager" role="navigation" aria-label="Specialties pages">
      <a class="pager__item <?= $spPage <= 1 ? 'is-disabled' : '' ?>"
        href="<?= $spPage <= 1 ? 'javascript:void(0)' : $base . ($spPage - 1) . $anchor ?>"
        aria-label="Previous page">‹</a>

      <?php for ($i = $start; $i <= $end; $i++): ?>
        <a class="pager__item <?= $i === $spPage ? 'is-active' : '' ?>" <?= $i === $spPage ? 'aria-current="page"' : '' ?>
          href="<?= $i === $spPage ? 'javascript:void(0)' : $base . $i . $anchor ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <a class="pager__item <?= $spPage >= $totalPages ? 'is-disabled' : '' ?>"
        href="<?= $spPage >= $totalPages ? 'javascript:void(0)' : $base . ($spPage + 1) . $anchor ?>"
        aria-label="Next page">›</a>
    </nav>
  <?php endif; ?>
</section>

<style>
  /* ==== Popular Specialties (Home) ==== */
  /* ==== Pager (circles) ==== */
  .pager {
    margin-top: 18px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    user-select: none;
  }

  .pager__item {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #e5e9f2;
    color: #374151;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
    transition: background .15s ease, color .15s ease, border-color .15s ease, transform .05s ease;
  }

  .pager__item:hover {
    background: #f7f9ff;
  }

  .pager__item.is-active,
  .pager__item[aria-current="page"] {
    background: #5b93f7;
    /* active blue like screenshot */
    border-color: #5b93f7;
    color: #fff;
  }

  .pager__item.is-disabled {
    opacity: .45;
    pointer-events: none;
  }

  @media (max-width: 480px) {
    .pager {
      gap: 8px;
    }

    .pager__item {
      width: 38px;
      height: 38px;
    }
  }

  .home-specialties {
    margin-top: 24px;
  }

  .home-specialties .specialty-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    /* 3 cột như Figma */
    gap: 24px;
    align-items: stretch;
  }

  .home-specialties .specialty-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.06);
    display: flex;
    flex-direction: column;
    transition: transform .15s ease, box-shadow .15s ease;
  }

  .home-specialties .specialty-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.1);
  }

  .home-specialties .specialty-media {
    display: block;
    line-height: 0;
    /* bỏ khoảng trắng dưới ảnh */
  }

  .home-specialties .specialty-media img {
    width: 100%;
    height: 160px;
    /* chiều cao phần ảnh giống card Figma */
    object-fit: cover;
    /* cắt ảnh cho đầy khung */
    display: block;
  }

  .home-specialties .specialty-body {
    padding: 16px 18px 18px;
  }

  .home-specialties .specialty-title {
    margin: 0 0 6px;
    font-size: 20px;
    font-weight: 700;
    color: #1c2430;
  }

  .home-specialties .specialty-blurb {
    margin: 0 0 12px;
    color: #5b6675;
    line-height: 1.5;
  }

  .home-specialties .specialty-cta {
    display: inline-block;
    color: #224f9e;
    /* xanh link nhẹ */
    text-decoration: underline;
    font-weight: 500;
  }

  /* Responsive: 2 cột tablet, 1 cột mobile */
  @media (max-width: 1024px) {
    .home-specialties .specialty-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 640px) {
    .home-specialties .specialty-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<style>
  .why {
    padding: 64px 0 72px;
  }

  .why__inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 16px;
    text-align: center;
  }

  .why .section__title {
    /* giữ màu mặc định */
    font-size: 36px;
    line-height: 1.2;
    font-weight: 700;
    margin: 0 0 10px;
  }

  .why .section__bar {
    /* GIỮ màu #2563eb như trước */
    width: 80px;
    height: 8px;
    background: #2563eb;
    border-radius: 999px;
    margin: 0 auto 36px;
  }

  .why-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 24px;
    align-items: stretch;
  }

  @media (max-width: 960px) {
    .why-grid {
      grid-template-columns: 1fr;
      gap: 16px;
    }
  }

  .why-card {
    background: #fff;
    border: 1px solid #eef2f7;
    border-radius: 26px;
    padding: 28px 22px 26px;
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: transform .2s ease, box-shadow .2s ease;
  }

  .why-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.10);
  }

  .why-card__icon {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    background: #eef6ff;
    /* GIỮ màu icon circle cũ */
    position: relative;
    margin: 4px auto 16px;
    flex: none;
  }

  .why-card__icon::before {
    content: "";
    position: absolute;
    inset: 0;
    margin: auto;
    width: 36px;
    height: 36px;
    /* icon to hơn */
    background-image: var(--icon-url);
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
  }

  .why-card h3 {
    margin: 0 0 8px;
    font-size: 20px;
    font-weight: 700;
  }

  /* giữ màu chữ mặc định */
  .why-card p {
    margin: 0;
    color: #4b5563;
    line-height: 1.55;
    font-size: 15px;
  }

  /* GIỮ màu #4b5563 */

  /* Form bọc thanh search */
  .search--has-suggest {
    position: relative;
  }

  /* DROPDOWN */
  .search__suggest {
    position: absolute;
    /* bám theo padding của khung trắng: nếu khung có padding-x = 24px thì đặt 24 */
    left: 24px;
    right: 24px;
    top: calc(100% + 12px);
    /* ngay dưới form */
    background: #fff;
    border: 1px solid #e7eaf3;
    border-radius: 16px;
    box-shadow: 0 18px 40px rgba(16, 24, 40, .12);
    max-height: 420px;
    overflow: auto;
    z-index: 150;
    /* cao hơn header/ảnh */
    padding: 6px;
    color: #1c2430;
  }

  .search__suggest.hidden {
    display: none;
  }

  /* Nhóm tiêu đề */
  .search__suggest .s-head {
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: 10px 12px 4px;
  }

  /* Item */
  .search__suggest .s-item {
    display: flex;
    align-items: baseline;
    gap: 8px;
    padding: 12px 14px;
    border-radius: 12px;
    cursor: pointer;
    transition: background .15s ease;
  }

  .search__suggest .s-item:hover {
    background: #f5f7ff;
  }

  /* Nhãn nhỏ */
  .search__suggest .s-pill {
    font-size: 12px;
    line-height: 20px;
    padding: 0 8px;
    border-radius: 999px;
    background: #eef2ff;
    color: #3c50e0;
    flex: 0 0 auto;
    background: #eef2ff;
  }

  .search__suggest .s-item>span:nth-of-type(2) {
    color: #1c2430;
    font-weight: 700;
    font-size: 18px;
  }

  /* Meta */
  .search__suggest .s-meta {
    opacity: .7;
    font-size: 12px;
    margin-left: 6px;
    color: #667085;
  }

  /* Empty */
  .search__suggest .s-empty {
    padding: 14px;
    color: #667085;
    text-align: center;
  }

  /* Scrollbar */
  .search__suggest::-webkit-scrollbar {
    width: 8px;
  }

  .search__suggest::-webkit-scrollbar-thumb {
    background: #e1e6f9;
    border-radius: 999px;
  }

  /* Mobile: khít mép, không tràn */
  @media (max-width: 768px) {
    .search__suggest {
      left: 12px;
      right: 12px;
      top: calc(100% + 8px);
    }
  }

  /* form chứa dropdown */
  .search--has-suggest {
    position: relative;
    z-index: 1000;
    /* nổi hơn các section phía dưới */
  }

  /* chính dropdown */
  .search__suggest {
    z-index: 10000;
    /* cao hẳn để không bị che */
  }

  /* hero/khung chứa form – tránh cắt dropdown */
  .hero,
  .hero__inner {
    overflow: visible !important;
    /* nếu trước đó bị hidden */
    position: relative;
    z-index: 5;
    /* cao hơn các section tiếp theo */
  }

  /* các section phía dưới không cần cao z-index */
  .home-specialties,
  .why,
  .companion {
    position: relative;
    z-index: 1;
    /* đảm bảo thấp hơn hero */
  }
</style>

<section class="why">
  <div class="why__inner">
    <h2 class="section__title">Why choose MediBook</h2>
    <div class="section__bar"></div>

    <div class="why-grid">
      <div class="why-card">
        <!-- FIX path: thêm '/' sau BASE_URL -->
        <div class="why-card__icon" style="--icon-url: url('<?= BASE_URL ?>assets/timer.png');"></div>
        <h3>Simple and fast booking</h3>
        <p>Find the right doctor and schedule your appointment in just a few clicks.</p>
      </div>

      <div class="why-card">
        <div class="why-card__icon" style="--icon-url: url('<?= BASE_URL ?>assets/shield-check.png');"></div>
        <h3>Verified doctors and clinics</h3>
        <p>All medical offices are reviewed and approved to ensure trusted, quality care.</p>
      </div>

      <div class="why-card">
        <div class="why-card__icon" style="--icon-url: url('<?= BASE_URL ?>assets/user-heart.png');"></div>
        <h3>Personalized experience</h3>
        <p>Easily manage your appointments and health information in one secure place.</p>
      </div>
    </div>
  </div>
</section>

<section class="companion">
  <div class="section__inner">
    <h2 class="section__title">Your everyday health companion</h2>

    <div class="companion-grid">
      <div class="companion-item">
        <img src="<?= BASE_URL ?>assets/comp-1.png" alt="" class="companion-item__img">
        <h4>Access care with ease</h4>
        <p>Find doctors by specialty and location, and book your appointments in just a few clicks.</p>
      </div>
      <div class="companion-item">
        <img src="<?= BASE_URL ?>assets/comp-2.png" alt="" class="companion-item__img">
        <h4>Receive personalized care</h4>
        <p>Choose the right doctor for your needs and keep track of your upcoming visits.</p>
      </div>
      <div class="companion-item">
        <img src="<?= BASE_URL ?>assets/comp-3.png" alt="" class="companion-item__img">
        <h4>Manage your health</h4>
        <p>Store and organize your health information, along with the details of your loved ones.</p>
      </div>
    </div>
  </div>
</section>
<!-- This is a single-line comment in HTML -->

<script>const BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>js/search.js"></script>