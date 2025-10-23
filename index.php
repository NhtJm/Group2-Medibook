<?php
require_once __DIR__ . '/config.php';
session_start();

/* ---- Helpers ---- */
function current_user()
{
  return $_SESSION['user'] ?? null;
}
function require_auth_or_redirect()
{
  if (!current_user()) {
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
  }
}

/* ---- Demo login ---- */
if (($_GET['page'] ?? '') === 'demo_login') {
  $_SESSION['user'] = ['id' => 999, 'name' => 'Demo Patient', 'role' => 'patient', 'email' => 'demo@medibook.local'];
  header('Location: ' . BASE_URL . 'index.php?page=dashboard');
  exit;
}

/* ---- Routing ---- */
$page = $_GET['page'] ?? 'home';
$allowed = [
  'home',
  'login',
  'register',
  'register2',
  'dashboard',
  'appointments',
  'profile',
  'logout',
  'clinics',
  'clinic',
  'doctor',
  'confirm',
];
if (!in_array($page, $allowed, true))
  $page = 'home';

/* Login (stub) */
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass = trim($_POST['password'] ?? '');
  if ($email !== '' && $pass !== '') {
    $_SESSION['user'] = ['id' => 1, 'name' => 'Guest Patient', 'role' => 'patient', 'email' => $email];
    header('Location: ' . BASE_URL . 'index.php?page=dashboard');
    exit;
  }
}

/* Logout */
if ($page === 'logout') {
  session_destroy();
  header('Location: ' . BASE_URL . 'index.php?page=home');
  exit;
}

/* Protect logged-in pages */
$protected = ['dashboard', 'appointments', 'profile'];
if (in_array($page, $protected, true))
  require_auth_or_redirect();

/* Title + CSS */
$labels = [
  'home' => 'Home',
  'login' => 'Login',
  'register' => 'Sign Up',
  'register2' => 'Sign Up',
  'dashboard' => 'Dashboard',
  'appointments' => 'Appointments',
  'profile' => 'My Profile',
  'clinics' => 'Clinics'  // <-- add
];
$title = 'MEDIBOOK — ' . ($labels[$page] ?? 'MediBook');
$css = [STYLE_PATH . '/base.css', STYLE_PATH . '/header.css'];
if ($page === 'clinics')
  $css[] = STYLE_PATH . '/clinics.css';
elseif ($page === 'clinic')
  $css[] = STYLE_PATH . '/clinic.css';
elseif ($page === 'doctor')   
  $css[] = STYLE_PATH . '/doctor.css';
elseif ($page === 'appointments')
  $css[] = STYLE_PATH . '/appointments.css';
elseif ($page === 'confirm')  
  $css[] = STYLE_PATH . '/confirm.css'; 
elseif ($page === 'home' || $page === 'dashboard')
  $css[] = STYLE_PATH . '/home.css';
else
  $css[] = STYLE_PATH . '/login.css';

/* Views map */
$views = [
  'home' => __DIR__ . '/app/views/home.php',
  'login' => __DIR__ . '/app/views/auth/login.php',
  'register' => __DIR__ . '/app/views/auth/register.php',
  'register2' => __DIR__ . '/app/views/auth/register2.php',
  'dashboard' => __DIR__ . '/app/views/user/dashboard.php',
  'appointments' => __DIR__ . '/app/views/user/appointments.php',
  'profile' => __DIR__ . '/app/views/user/profile.php',
  'clinics' => __DIR__ . '/app/views/search/clinics.php',
  'clinic' => __DIR__ . '/app/views/search/clinic.php',
  'doctor' => __DIR__ . '/app/views/search/doctor.php',
  'confirm' => __DIR__ . '/app/views/booking/confirm.php',
];

$brandTarget = current_user() ? 'dashboard' : 'home';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="icon" href="<?= IMAGE_PATH ?>/Logo.svg">
  <?php foreach ($css as $href): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($href) ?>">
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
              <?= htmlspecialchars(current_user()['name'] ?? 'Account') ?>
              <span class="chev"></span>
            </button>
            <div id="accountMenu" class="dropdown-content">
              <a href="<?= BASE_URL ?>index.php?page=profile">Profile</a>
              <a href="<?= BASE_URL ?>index.php?page=logout">Sign out</a>
            </div>
          </div>
        <?php else: ?>
          <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?page=login">Login</a>
          <a class="btn btn--dark" href="<?= BASE_URL ?>index.php?page=register">Sign Up</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main>
    <?php
    $view = $views[$page] ?? null;
    if ($view && is_file($view))
      include $view;
    else
      echo '<section class="wall"><div class="card"><h1 class="title">View not found</h1></div></section>';
    ?>
  </main>

  <!-- dropdown navigation -->
  <script>
    // toggle
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