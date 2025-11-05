<?php
require_once __DIR__ . '/../db.php';

/* THÊM DÒNG NÀY để lấy mysqli connection từ db.php */
if (!isset($conn) || !$conn) {
  if (class_exists('Database')) {
    $conn = Database::get_instance();
  } else {
    // Nếu db.php của bạn dùng biến global $conn thì mở khóa dòng dưới:
    // global $conn;
  }
}

if (!function_exists('h')) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

$specialties = [];
$sql = "SELECT * FROM Medical_specialty ORDER BY name";  // không giả định cột mới
if ($res = $conn->query($sql)) {
  $rows = $res->fetch_all(MYSQLI_ASSOC);
  $res->free();

  // Chuẩn hoá dữ liệu + fallback cho UI card
  $specialties = array_map(function($sp){
    $id    = (int)($sp['specialty_id'] ?? 0);
    $name  = (string)($sp['name'] ?? '');
    $desc  = trim((string)($sp['description'] ?? ''));

    // Fallback nếu DB CHƯA có các cột mới
    $slug  = isset($sp['slug']) ? (string)$sp['slug'] : (string)$id;
    $blurb = isset($sp['blurb']) && $sp['blurb'] !== '' ? (string)$sp['blurb'] : (
      mb_strlen($desc) > 140 ? (mb_substr($desc, 0, 140) . '…') : $desc
    );
    $image = isset($sp['image_url']) && $sp['image_url'] !== '' ? (string)$sp['image_url'] : '/images/specialties/placeholder.jpg';

    return [
      'id'    => $id,
      'slug'  => $slug,
      'name'  => $name,
      'blurb' => $blurb,
      'image' => $image,
    ];
  }, $rows);

  // Lấy 6 cái “Popular” đầu tiên (đúng layout Figma)
  $specialties = array_slice($specialties, 0, 6);
} else {
  echo "<!-- specialties query failed: " . h($conn->error) . " -->";
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

    <form class="search">
      <div class="search__field">
        <label>Doctor, Clinic or Specialty</label>
        <input type="text" placeholder="e.g., Dr. Clark, Cardiology, …">
      </div>
      <div class="search__field">
        <label>Location</label>
        <input type="text" placeholder="Where ?">
      </div>
      <button class="search__btn" type="button">Search</button>
    </form>
  </div>
</section>

<section class="home-specialties container">
  <h2 class="block-title">Popular Specialties</h2>

  <?php if (empty($specialties)): ?>
    <p>No specialties found.</p>
  <?php else: ?>
    <div class="specialty-grid">
      <?php foreach ($specialties as $sp): ?>
        <article class="specialty-card">
          <a class="specialty-media" href="/public/index.php?page=clinics&specialty=<?= h($sp['slug']) ?>">
            <img src="<?= h($sp['image']) ?>" alt="<?= h($sp['name']) ?>">
          </a>

          <div class="specialty-body">
            <h3 class="specialty-title"><?= h($sp['name']) ?></h3>
            <?php if ($sp['blurb'] !== ''): ?>
              <p class="specialty-blurb"><?= h($sp['blurb']) ?></p>
            <?php endif; ?>

            <a class="specialty-cta" href="/public/index.php?page=clinics&specialty=<?= h($sp['slug']) ?>">
              Find Specialists
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<style>
/* ==== Popular Specialties (Home) ==== */
.home-specialties { margin-top: 24px; }

.home-specialties .specialty-grid{
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr)); /* 3 cột như Figma */
  gap: 24px;
  align-items: stretch;
}

.home-specialties .specialty-card{
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  border: 1px solid rgba(0,0,0,0.06);
  display: flex;
  flex-direction: column;
  transition: transform .15s ease, box-shadow .15s ease;
}

.home-specialties .specialty-card:hover{
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(0,0,0,0.1);
}

.home-specialties .specialty-media{
  display:block;
  line-height:0; /* bỏ khoảng trắng dưới ảnh */
}

.home-specialties .specialty-media img{
  width: 100%;
  height: 160px;               /* chiều cao phần ảnh giống card Figma */
  object-fit: cover;           /* cắt ảnh cho đầy khung */
  display:block;
}

.home-specialties .specialty-body{
  padding: 16px 18px 18px;
}

.home-specialties .specialty-title{
  margin: 0 0 6px;
  font-size: 20px;
  font-weight: 700;
  color: #1c2430;
}

.home-specialties .specialty-blurb{
  margin: 0 0 12px;
  color: #5b6675;
  line-height: 1.5;
}

.home-specialties .specialty-cta{
  display: inline-block;
  color: #224f9e;              /* xanh link nhẹ */
  text-decoration: underline;
  font-weight: 500;
}

/* Responsive: 2 cột tablet, 1 cột mobile */
@media (max-width: 1024px){
  .home-specialties .specialty-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px){
  .home-specialties .specialty-grid{ grid-template-columns: 1fr; }
}
</style>

<style>
  .why { padding: 64px 0 72px; }                
  .why__inner { max-width: 1100px; margin: 0 auto; padding: 0 16px; text-align: center; }

  .why .section__title {                          /* giữ màu mặc định */
    font-size: 36px; line-height: 1.2; font-weight: 700;
    margin: 0 0 10px;
  }
  .why .section__bar {                            /* GIỮ màu #2563eb như trước */
    width: 80px; height: 8px; background: #2563eb;
    border-radius: 999px; margin: 0 auto 36px;
  }

  .why-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 24px; align-items: stretch;
  }
  @media (max-width: 960px){ .why-grid { grid-template-columns: 1fr; gap: 16px; } }

  .why-card {
    background: #fff;                             
    border: 1px solid #eef2f7;      
    border-radius: 26px;                      
    padding: 28px 22px 26px;
    box-shadow: 0 8px 22px rgba(0,0,0,0.08);  
    display: flex; flex-direction: column; align-items: center; text-align: center;
    transition: transform .2s ease, box-shadow .2s ease;
  }
  .why-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(0,0,0,0.10); }

  .why-card__icon {
    width: 76px; height: 76px; border-radius: 50%;
    background: #eef6ff;                          /* GIỮ màu icon circle cũ */
    position: relative; margin: 4px auto 16px; flex: none;
  }
  .why-card__icon::before {
    content: ""; position: absolute; inset: 0; margin: auto;
    width: 36px; height: 36px;                    /* icon to hơn */
    background-image: var(--icon-url);
    background-repeat: no-repeat; background-position: center; background-size: contain;
  }

  .why-card h3 { margin: 0 0 8px; font-size: 20px; font-weight: 700; }   /* giữ màu chữ mặc định */
  .why-card p  { margin: 0; color: #4b5563; line-height: 1.55; font-size: 15px; }  /* GIỮ màu #4b5563 */
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
