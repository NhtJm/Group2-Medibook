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


// Ko demo nx
// /* ========= Demo login (optional) ========= */
// if (($_GET['page'] ?? '') === 'demo_login') {
//   $_SESSION['user'] = [
//     'id' => 999, 'name' => 'Demo Patient', 'role' => 'patient', 'email' => 'demo@medibook.local'
//   ];
//   header('Location: ' . BASE_URL . 'index.php?page=dashboard');
//   exit;
// }

/* ========= Routing: xác định $page + allowlist ========= */
$page = $_GET['page'] ?? 'home';
$allowed = [
  'home','login','register','register2','dashboard','appointments','profile',
  'logout','clinics','clinic','doctor','confirm',
  'google_login','google_callback', // Google OAuth
  'google_choose_role','google_begin', 
];
if (!in_array($page, $allowed, true)) $page = 'home';

/* ========= Google OAuth routes ========= */
if ($page === 'google_login') {
  header('Location: ' . BASE_URL . 'index.php?page=google_choose_role');
  exit;
}

if ($page === 'google_begin') {
  // nhận role từ form và lưu vào session rồi mới gọi OAuth
  $role = $_POST['role'] ?? '';
  if (!in_array($role, ['patient','office'], true)) {
    header('Location: ' . BASE_URL . 'index.php?page=google_choose_role&err=role'); exit;
  }
  $_SESSION['oauth_role'] = $role; // lưu role đã chọn cho callback dùng

  require_once __DIR__ . '/../app/function/google_oauth.php';
  // đi tiếp tới Google (hàm cũ của bạn)
  header('Location: ' . google_build_auth_url());
  exit;
}

if ($page === 'google_choose_role') {
  require __DIR__ . '/../app/views/auth/google_choose_role.php';
  exit;
}

if ($page === 'google_callback') {
  require_once __DIR__ . '/../app/function/google_oauth.php';

  $code = $_GET['code'] ?? '';
  if (!$code) {
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
  }

  $token = google_exchange_code_for_token($code);
  if (!$token || empty($token['access_token'])) {
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
  }

  $info = google_fetch_userinfo($token['access_token']); // email, name, sub, ...
  if (!$info) {
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
  }

  if (app_login_or_create_from_google($info)) {
    // Role người dùng đã chọn trước OAuth (patient | office)
    $chosenRole = $_SESSION['oauth_role'] ?? 'patient';

    // Chuẩn hoá session cho current_user()
    if (empty($_SESSION['user'])) {
      $_SESSION['user'] = [
        'id'    => $_SESSION['user_id'] ?? 0,
        'name'  => $_SESSION['username'] ?? ($info['name'] ?? 'User'),
        'role'  => $chosenRole, // dùng role đã chọn
        'email' => $_SESSION['email'] ?? ($info['email'] ?? ''),
      ];
    } else {
      // nếu app_login_or_create_from_google đã set $_SESSION['user'] sẵn, ép role cho khớp luồng
      $_SESSION['user']['role'] = $chosenRole;
    }

    // Dọn session tạm
    unset($_SESSION['oauth_role']);

    // Điều hướng theo role: lần đầu đăng ký nên đưa tới trang setup tương ứng
    if ($chosenRole === 'office') {
      header('Location: ' . BASE_URL . 'index.php?page=clinic_setup');
    } else {
      header('Location: ' . BASE_URL . 'index.php?page=profile_setup');
    }
  } else {
    header('Location: ' . BASE_URL . 'index.php?page=login');
  }
  exit;
}



// /* ========= Login (stub có sẵn) ========= */
// if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
//   $email = trim($_POST['email'] ?? '');
//   $pass  = trim($_POST['password'] ?? '');
//   if ($email !== '' && $pass !== '') {
//     $_SESSION['user'] = [
//       'id' => 1, 'name' => 'Guest Patient', 'role' => 'patient', 'email' => $email
//     ];
//     header('Location: ' . BASE_URL . 'index.php?page=dashboard');
//     exit;
//   }
// }

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
</html>
