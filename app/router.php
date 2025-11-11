<?php
// app/router.php

/* ========= Routing: which page ========= */
$page = $_GET['page'] ?? 'home';

/* ---- Allow-list ---- */
$allowed = [
  'home','login','register',
  'dashboard','appointments','profile',
  'logout',
  'clinics','clinic','doctor','confirm',
  // Google OAuth flow
  'google_login','google_callback','google_choose_role','google_begin',
  // JSON endpoint
  'api.search',
  // admin/office
  'admin_dashboard','clinic_setup','office_dashboard','doctor_schedule',
];
if (!in_array($page, $allowed, true)) $page = 'home';

/* ---- Route groups ---- */
$adminOnly  = ['admin_dashboard'];
$protected  = ['dashboard','appointments','profile','office_dashboard','clinic_setup','doctor_schedule'];

/* ---- Guards ---- */
if (in_array($page, $adminOnly, true)) {
  require_auth_or_redirect();
  require_role(['admin']); // or ['admin','webstaff']
} elseif (in_array($page, $protected, true)) {
  require_auth_or_redirect();
}

/* ========= Special routes (no layout) ========= */
if ($page === 'api.search') {
  require_once __DIR__ . '/controller/SearchController.php';
  SearchController::suggest();
  exit;
}

/* ---- OAuth begin ---- */
if ($page === 'google_begin') {
  $role = $_POST['role'] ?? '';
  if ($role === 'webstaff') {
    header('Location: ' . BASE_URL . 'index.php?page=google_choose_role&err=staff_forbidden');
    exit;
  }
  if (!in_array($role, ['patient','office'], true)) {
    header('Location: ' . BASE_URL . 'index.php?page=google_choose_role&err=role');
    exit;
  }
  $_SESSION['oauth_flow'] = 'register';
  $_SESSION['oauth_role'] = $role;
  require_once __DIR__ . '/function/google_oauth.php';
  header('Location: ' . google_build_auth_url());
  exit;
}

/* ---- OAuth login ---- */
if ($page === 'google_login') {
  $_SESSION['oauth_flow'] = 'login';
  unset($_SESSION['oauth_role']);
  require_once __DIR__ . '/function/google_oauth.php';
  header('Location: ' . google_build_auth_url());
  exit;
}

/* ---- OAuth callback ---- */
if ($page === 'google_callback') {
  require_once __DIR__ . '/function/google_oauth.php';
  $ok = google_handle_callback_login_or_register();
  header('Location: ' . BASE_URL . 'index.php?page=' . ($ok ? 'dashboard' : 'google_choose_role'));
  exit;
}

/* ---- Role selection guard ---- */
if ($page === 'google_choose_role') {
  $flow = $_SESSION['oauth_flow'] ?? null;
  $hasPending = isset($_SESSION['pending_google_user']);
  if ($flow !== 'login' && !$hasPending) {
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
  }
}

/* ---- Logout ---- */
if ($page === 'logout') {
  session_destroy();
  header('Location: ' . BASE_URL . 'index.php?page=home');
  exit;
}

/* ========= Labels & CSS ========= */
$labels = [
  'home'             => 'Home',
  'login'            => 'Login',
  'register'         => 'Sign Up',
  'dashboard'        => 'Dashboard',
  'appointments'     => 'Appointments',
  'profile'          => 'My Profile',
  'clinics'          => 'Clinics',
  'admin_dashboard'  => 'Admin',
  'clinic_setup'     => 'Clinic Setup',
  'office_dashboard' => 'Office',
  'doctor_schedule'  => 'Schedule',
];
$title = 'MEDIBOOK — ' . ($labels[$page] ?? 'MediBook');

/* ---- No-chrome pages (no header/footer) ---- */
$noChromePages = ['admin_dashboard'];
$noChrome = in_array($page, $noChromePages, true);

/* ---- CSS list ---- */
$css = [STYLE_PATH . '/base.css'];
if (!$noChrome) {
  $css[] = STYLE_PATH . '/header.css';
}
if     ($page === 'clinics')         $css[] = STYLE_PATH . '/clinics.css';
elseif ($page === 'clinic')          $css[] = STYLE_PATH . '/clinic.css';
elseif ($page === 'doctor')          $css[] = STYLE_PATH . '/doctor.css';
elseif ($page === 'appointments')    $css[] = STYLE_PATH . '/appointments.css';
elseif ($page === 'confirm')         $css[] = STYLE_PATH . '/confirm.css';
elseif ($page === 'admin_dashboard') $css[] = STYLE_PATH . '/admin_dashboard.css';
elseif ($page === 'office_dashboard')$css[] = STYLE_PATH . '/office_dashboard.css';
elseif ($page === 'doctor_schedule') $css[] = STYLE_PATH . '/doctor_schedule.css';
elseif ($page === 'clinic_setup')    $css[] = STYLE_PATH . '/clinic_setup.css';
elseif ($page === 'home' || $page === 'dashboard')
                                     $css[] = STYLE_PATH . '/home.css';
else                                 $css[] = STYLE_PATH . '/login.css';
if (!$noChrome) {
  $css[] = STYLE_PATH . '/footer.css';
}

/* ========= Views map ========= */
$views = [
  'home'               => __DIR__ . '/views/home.php',
  'login'              => __DIR__ . '/views/auth/login.php',
  'register'           => __DIR__ . '/views/auth/register.php',
  'dashboard'          => __DIR__ . '/views/user/dashboard.php',
  'appointments'       => __DIR__ . '/views/user/appointments.php',
  'profile'            => __DIR__ . '/views/user/profile.php',
  'clinics'            => __DIR__ . '/views/search/clinics.php',
  'clinic'             => __DIR__ . '/views/search/clinic.php',
  'doctor'             => __DIR__ . '/views/search/doctor.php',
  'confirm'            => __DIR__ . '/views/booking/confirm.php',
  'google_choose_role' => __DIR__ . '/views/auth/google_choose_role.php',
  'admin_dashboard'    => __DIR__ . '/views/admin/admin_dashboard.php',
  'clinic_setup'       => __DIR__ . '/views/clinic/clinic_setup.php',
  'office_dashboard'   => __DIR__ . '/views/clinic/office_dashboard.php',
  'doctor_schedule'    => __DIR__ . '/views/clinic/doctor_schedule.php',
];

$brandTarget = current_user() ? 'dashboard' : 'home';
$view        = $views[$page] ?? null;
$data = null;
if ($page === 'clinics') {
  require_once __DIR__ . '/controller/ClinicsController.php';
  $data = ClinicsController::index(); // passes $data to the included view
}
/* ========= Choose layout ========= */
$layout = __DIR__ . '/views/layout/' . ($noChrome ? 'blank.php' : 'app.php');
require $layout;