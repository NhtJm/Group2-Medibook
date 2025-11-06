<?php
require_once __DIR__ . '/../config/config.php';
session_start();

/* ========= Helpers ========= */
function current_user() {
  return $_SESSION['user'] ?? null;
}
function require_auth_or_redirect() {
  if (!current_user()) {
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
  }
}

/* ========= Routing: xác định $page + allowlist ========= */
$page = $_GET['page'] ?? 'home';
$allowed = [
  'home','login','register','register2','dashboard','appointments','profile',
  'logout','clinics','clinic','doctor','confirm',
  'google_login','google_callback', // Google OAuth
  'google_choose_role','google_begin', 
  'api.search', // JSON endpoint cho AJAX
];
if (!in_array($page, $allowed, true)) $page = 'home';

if ($page === 'google_begin') {
  $role = $_POST['role'] ?? '';

  // Web Staff không được tự đăng ký
  if ($role === 'webstaff') {
    header('Location: ' . BASE_URL . 'index.php?page=google_choose_role&err=staff_forbidden');
    exit;
  }

  // Chỉ chấp nhận 2 role được phép tự tạo
  if (!in_array($role, ['patient', 'office'], true)) {
    header('Location: ' . BASE_URL . 'index.php?page=google_choose_role&err=role');
    exit;
  }

  $_SESSION['oauth_flow'] = 'register';
  $_SESSION['oauth_role'] = $role;

  require_once __DIR__ . '/../app/function/google_oauth.php';
  header('Location: ' . google_build_auth_url());
  exit;
}

// LOGIN -> đi Google (không hỏi role)
if ($page === 'google_login') {
  $_SESSION['oauth_flow'] = 'login';
  unset($_SESSION['oauth_role']);
  require_once __DIR__ . '/../app/function/google_oauth.php';
  header('Location: ' . google_build_auth_url());
  exit;
}

// CALLBACK: login/link/tạo mới nếu cần
if ($page === 'google_callback') {
  require_once __DIR__ . '/../app/function/google_oauth.php';
  $ok = google_handle_callback_login_or_register();
  if ($ok) {
    header('Location: ' . BASE_URL . 'index.php?page=dashboard');
    exit;
  }
  // flow=login, chưa có user -> yêu cầu chọn role
  header('Location: ' . BASE_URL . 'index.php?page=google_choose_role');
  exit;
}

// Trang chọn role (chỉ cho phép khi flow=login hoặc có pending_google_user)
if ($page === 'google_choose_role') {
  $flow = $_SESSION['oauth_flow'] ?? null;
  $hasPending = isset($_SESSION['pending_google_user']);
  if ($flow !== 'login' && !$hasPending) {
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
  }
  require __DIR__ . '/../app/views/auth/google_choose_role.php';
  exit;
}



if ($page === 'api.search') {
  require_once __DIR__ . '/../app/controller/SearchController.php';
  SearchController::suggest();
  exit;
}

/* ========= Logout ========= */
if ($page === 'logout') {
  session_destroy();
  header('Location: ' . BASE_URL . 'index.php?page=home');
  exit;
}

/* ========= Bảo vệ các trang cần đăng nhập ========= */
$protected = ['dashboard','appointments','profile'];
if (in_array($page, $protected, true)) require_auth_or_redirect();

/* ========= Title + CSS ========= */
$labels = [
  'home' => 'Home',
  'login' => 'Login',
  'register' => 'Sign Up',
  'register2' => 'Sign Up',
  'dashboard' => 'Dashboard',
  'appointments' => 'Appointments',
  'profile' => 'My Profile',
  'clinics' => 'Clinics',
];
$title = 'MEDIBOOK — ' . ($labels[$page] ?? 'MediBook');

$css = [STYLE_PATH . '/base.css', STYLE_PATH . '/header.css'];
if     ($page === 'clinics')      $css[] = STYLE_PATH . '/clinics.css';
elseif ($page === 'clinic')       $css[] = STYLE_PATH . '/clinic.css';
elseif ($page === 'doctor')       $css[] = STYLE_PATH . '/doctor.css';
elseif ($page === 'appointments') $css[] = STYLE_PATH . '/appointments.css';
elseif ($page === 'confirm')      $css[] = STYLE_PATH . '/confirm.css';
elseif ($page === 'home' || $page === 'dashboard')
                                $css[] = STYLE_PATH . '/home.css';
else                            $css[] = STYLE_PATH . '/login.css';

/* ========= Views map ========= */
$views = [
  'home'         => __DIR__ . '/../app/views/home.php',
  'login'        => __DIR__ . '/../app/views/auth/login.php',
  'register'     => __DIR__ . '/../app/views/auth/register.php',
  'register2'    => __DIR__ . '/../app/views/auth/register2.php',
  'dashboard'    => __DIR__ . '/../app/views/user/dashboard.php',
  'appointments' => __DIR__ . '/../app/views/user/appointments.php',
  'profile'      => __DIR__ . '/../app/views/user/profile.php',
  'clinics'      => __DIR__ . '/../app/views/search/clinics.php',
  'clinic'       => __DIR__ . '/../app/views/search/clinic.php',
  'doctor'       => __DIR__ . '/../app/views/search/doctor.php',
  'confirm'      => __DIR__ . '/../app/views/booking/confirm.php',
];

$brandTarget = current_user() ? 'dashboard' : 'home';
?>
<!doctype html>
<html lang="en">
<head>
  <link rel="stylesheet" href="<?= BASE_URL ?>css/footer.css?v=1">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="icon" href="<?= IMAGE_PATH ?>/Logo.svg">
  <?php foreach ($css as $href): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
  <?php endforeach; ?>
</head>
<body>
  <header class="topbar">
    <div class="topbar__inner">
      <a class="brand" href="<?= BASE_URL ?>index.php?page=<?= $brandTarget ?>">
        <img src="<?= IMAGE_PATH ?>/Logo.svg" alt="" class="brand__logo" />
        <span class="brand__name">MEDIBOOK</span>
      </a>
      <nav class="topbar__nav">
        <?php if (current_user()): ?>
          <a class="btn" href="<?= BASE_URL ?>index.php?page=clinics">Search</a>
          <a class="btn" href="<?= BASE_URL ?>index.php?page=appointments">My Appointments</a>

          <div class="dropdown">
            <button id="accountBtn" class="dropbtn">
              <?= htmlspecialchars(current_user()['name'] ?? 'Account', ENT_QUOTES, 'UTF-8') ?>
              <span class="chev"></span>
            </button>
            <div id="accountMenu" class="dropdown-content">
              <a href="<?= BASE_URL ?>index.php?page=profile">Profile</a>
              <a href="<?= BASE_URL ?>index.php?page=logout">Sign out</a>
            </div>
          </div>
        <?php else: ?>
          <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?page=login">Login</a>
          <a class="btn btn--dark"  href="<?= BASE_URL ?>index.php?page=register">Sign Up</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main>
    <?php
      $view = $views[$page] ?? null;
      if ($view && is_file($view)) {
        include $view;
      } else {
        echo '<section class="wall"><div class="card"><h1 class="title">View not found</h1></div></section>';
      }
    ?>
  </main>

  <script>
    // dropdown toggle
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('#accountBtn');
      const menu = document.getElementById('accountMenu');
      const box = e.target.closest('.dropdown');
      if (btn) { menu.classList.toggle('show'); return; }
      if (!box && menu) menu.classList.remove('show');
    });
  </script>
</body>

<footer class="site-footer">
  <div class="footer__inner">
    <div class="footer__brand">
      <a class="brand" href="<?= BASE_URL ?>index.php?page=home">
        <img class="brand__logo" src="<?= IMAGE_PATH ?>logo.svg" alt="MediBook">
        <span class="brand__name">MediBook</span>
      </a>
      <p class="footer__blurb">
        Find the right doctor, book appointments online, and manage your care—all in one place.
      </p>
    </div>

    <nav class="footer__cols">
      <div class="footer__col">
        <h4>Explore</h4>
        <ul>
          <li><a href="<?= BASE_URL ?>index.php?page=clinics">Find Clinics</a></li>
          <li><a href="<?= BASE_URL ?>index.php?page=search">Search Doctors</a></li>
          <li><a href="<?= BASE_URL ?>index.php?page=home#why">Why MediBook</a></li>
        </ul>
      </div>

      <div class="footer__col">
        <h4>Account</h4>
        <ul>
          <?php if (!empty($_SESSION['user'])): ?>
            <li><a href="<?= BASE_URL ?>index.php?page=dashboard">Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>index.php?page=appointments">My Appointments</a></li>
            <li><a href="<?= BASE_URL ?>index.php?page=profile">Profile</a></li>
            <li><a href="<?= BASE_URL ?>index.php?page=logout">Sign out</a></li>
          <?php else: ?>
            <li><a href="<?= BASE_URL ?>index.php?page=login">Login</a></li>
            <li><a href="<?= BASE_URL ?>index.php?page=register">Sign Up</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="footer__col">
        <h4>Support</h4>
        <ul>
          <li><a href="<?= BASE_URL ?>index.php?page=help">Help Center</a></li>
          <li><a href="<?= BASE_URL ?>index.php?page=contact">Contact</a></li>
          <li><a href="<?= BASE_URL ?>index.php?page=privacy">Privacy</a></li>
          <li><a href="<?= BASE_URL ?>index.php?page=terms">Terms</a></li>
        </ul>
      </div>
    </nav>
  </div>

  <div class="footer__bar">
    <div class="footer__bar-inner">
      <span>© <?= date('Y') ?> MediBook. All rights reserved.</span>
      <div class="footer__social">
        <a aria-label="Facebook" href="#" rel="noopener">Fb</a>
        <a aria-label="X" href="#" rel="noopener">X</a>
        <a aria-label="LinkedIn" href="#" rel="noopener">In</a>
      </div>
    </div>
  </div>
</footer>
</html>
