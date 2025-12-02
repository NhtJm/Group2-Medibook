<?php
// app/router.php

require_once __DIR__ . '/db.php';

/* ------------------------------ DB bootstrap ------------------------------ */
global $conn;
if (!isset($conn) || !($conn instanceof mysqli)) {
  if (class_exists('Database') && method_exists('Database', 'get_instance')) {
    $conn = Database::get_instance(); // must return mysqli
  }
}

/* ------------------------------- Page & ACL ------------------------------- */
$page = $_GET['page'] ?? 'home';

/** Allow-list of pages users can navigate to (non-API). */
$allowedPages = [
  // public
  'home',
  'login',
  'register',
  'clinics',
  'clinic',
  'doctor',
  'doctors',
  'confirm',
  // oauth
  'google_login',
  'google_callback',
  'google_choose_role',
  'google_begin',
  // user area
  'dashboard',
  'appointments',
  'profile',
  'logout',
  'book',
  // admin
  'admin_dashboard',
  'admin_clinics',
  'admin_clinics_api',
  'clinic_setup',
  'office_dashboard',
  'doctor_schedule',
  'help',
  'contact',
  'privacy',
  'terms',
  'clinic_edit',
  'admin_users',
  // json search endpoint
  'api.search',
  // in $allowed
  'admin_specialties',
  'admin_specialties_api',
  'admin_specialty_edit',
  'admin_user',
  'admin_doctors',
  'admin_doctors_office',
  'admin_doctor_edit',
  'admin_doctor_slots_api',
  'admin_appointments',
  'appointment_api',
];

if (!in_array($page, $allowedPages, true)) {
  $page = 'home';
}

/* Route groups for guards */
// in $adminOnly
$adminOnly = [
  'admin_dashboard',
  'admin_clinics',
  'admin_clinics_api',
  'clinic_edit',
  'admin_specialties',
  'admin_specialties_api',
  'admin_specialty_edit',
  'admin_users',
  'admin_user',
  'admin_doctors',
  'admin_doctors_office',
  'admin_doctor_edit',
  'admin_doctor_slots_api',
  'admin_appointments',
];
$protected = ['dashboard', 'appointments', 'profile', 'office_dashboard', 'clinic_setup', 'doctor_schedule',];

/* ------------------------------ Auth guards ------------------------------ */
if (in_array($page, $adminOnly, true)) {
  require_auth_or_redirect();
  require_role(['admin']); // only admins
} elseif (in_array($page, $protected, true)) {
  require_auth_or_redirect();
}
if ($page === 'confirm' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_auth_or_redirect();
}

/* --------------------------- Special routes (early) ----------------------- */
/* JSON search (no layout) */
if ($page === 'api.search') {
  require_once __DIR__ . '/controller/SearchController.php';
  SearchController::suggest();
  exit;
}

/* Admin clinics API (no layout) */
if (($_GET['page'] ?? '') === 'admin_clinics_api') {
  require_once __DIR__ . '/controller/AdminClinicsController.php';
  AdminClinicsController::api();
  exit;
}

/* Admin specialties API (no layout) */
if (($_GET['page'] ?? '') === 'admin_specialties_api') {
  require_once __DIR__ . '/controller/AdminSpecialtiesController.php';
  AdminSpecialtiesController::api();
  exit;
}

/* Appointment API (cancel, reschedule) */
if (($_GET['page'] ?? '') === 'appointment_api') {
  require_once __DIR__ . '/controller/AppointmentApiController.php';
  AppointmentApiController::index();
  exit;
}

/* OAuth begin */
if ($page === 'google_begin') {
  $role = $_POST['role'] ?? '';
  if ($role === 'webstaff') {
    header('Location: ' . BASE_URL . 'index.php?page=google_choose_role&err=staff_forbidden');
    exit;
  }
  if (!in_array($role, ['patient', 'office'], true)) {
    header('Location: ' . BASE_URL . 'index.php?page=google_choose_role&err=role');
    exit;
  }
  $_SESSION['oauth_flow'] = 'register';
  $_SESSION['oauth_role'] = $role;
  require_once __DIR__ . '/function/google_oauth.php';
  header('Location: ' . google_build_auth_url());
  exit;
}

/* OAuth login */
if ($page === 'google_login') {
  $_SESSION['oauth_flow'] = 'login';
  unset($_SESSION['oauth_role']);
  require_once __DIR__ . '/function/google_oauth.php';
  header('Location: ' . google_build_auth_url());
  exit;
}

/* OAuth callback */
if ($page === 'google_callback') {
  require_once __DIR__ . '/function/google_oauth.php';
  $ok = google_handle_callback_login_or_register();
  header('Location: ' . BASE_URL . 'index.php?page=' . ($ok ? 'dashboard' : 'google_choose_role'));
  exit;
}

/* OAuth role choose guard */
if ($page === 'google_choose_role') {
  $flow = $_SESSION['oauth_flow'] ?? null;
  $hasPending = isset($_SESSION['pending_google_user']);
  if ($flow !== 'login' && !$hasPending) {
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
  }
}

/* Logout */
if ($page === 'logout') {
  session_destroy();
  header('Location: ' . BASE_URL . 'index.php?page=home');
  exit;
}

/* ------------------------------- Labels & CSS ----------------------------- */
$labels = [
  'home' => 'Home',
  'login' => 'Login',
  'register' => 'Sign Up',
  'dashboard' => 'Dashboard',
  'appointments' => 'Appointments',
  'profile' => 'My Profile',
  'clinics' => 'Clinics',
  'clinic' => 'Clinic',
  'doctor' => 'Doctor Visit',
  'doctors' => 'Doctors',
  'confirm' => 'Confirm',
  'book' => 'Choose Date',
  'admin_dashboard' => 'Admin',
  'admin_clinics' => 'Clinics',
  'clinic_setup' => 'Clinic Setup',
  'office_dashboard' => 'Office',
  'doctor_schedule' => 'Schedule',
  'help' => 'Help',
  'contact' => 'Contact',
  'privacy' => 'Privacy',
  'terms' => 'Terms'
];

$title = 'MEDIBOOK — ' . ($labels[$page] ?? 'MediBook');

/* Pages with admin chrome hidden (we use a blank layout) */
$noChromePages = [
  'admin_dashboard',
  'admin_clinics',
  'clinic_edit',
  'admin_specialties',
  'admin_specialty_edit',
  'admin_users',
  'admin_user',
  'admin_doctors',
  'admin_doctors_office',
  'admin_doctor_edit',
  'admin_appointments',
  'login',
  'register',
];
$noChrome = in_array($page, $noChromePages, true);

/* CSS pipeline */
$css = [STYLE_PATH . '/base.css'];
if (!$noChrome) {
  $css[] = STYLE_PATH . '/header.css';
}
if (
  in_array($page, [
    'admin_dashboard',
    'admin_clinics',
    'clinic_edit',
    'admin_specialties',
    'admin_specialty_edit',
    'admin_users',
    'admin_user',
    'admin_doctors',
    'admin_doctors_office',
    'admin_doctor_edit',
    'admin_appointments',
  ], true)
) {
  $css[] = STYLE_PATH . '/admin_sidebar.css';
  $css[] = STYLE_PATH . '/admin_topbar.css';
} elseif (in_array($page, ['help', 'contact', 'privacy', 'terms'], true)) {
  $css[] = STYLE_PATH . '/static.css';
}

/* Page-specific CSS */
switch ($page) {
  case 'clinics':
    $css[] = STYLE_PATH . '/clinics.css';
    break;
  case 'clinic':
    $css[] = STYLE_PATH . '/clinic.css';
    break;
  case 'admin_clinics':
    $css[] = STYLE_PATH . '/admin_clinics.css';
    break;
  case 'clinic_edit':
    $css[] = STYLE_PATH . '/clinic_edit.css';
    break;
  case 'doctor':
    $css[] = STYLE_PATH . '/clinic.css';
    $css[] = STYLE_PATH . '/doctors.css';
    $css[] = STYLE_PATH . '/doctor.css';
    break;
  case 'doctors':
    $css[] = STYLE_PATH . '/clinic.css';
    $css[] = STYLE_PATH . '/doctors.css';
    break;
  case 'appointments':
    $css[] = STYLE_PATH . '/appointments.css';
    break;
  case 'confirm':
    $css[] = STYLE_PATH . '/confirm.css';
    break;
  case 'admin_doctors':
    $css[] = STYLE_PATH . '/admin_doctors.css';
    break;
  case 'admin_doctors_office':
    $css[] = STYLE_PATH . '/admin_doctors.css';
    break;
  case 'admin_dashboard':
    $css[] = STYLE_PATH . '/admin_dashboard.css';
    break;
  case 'admin_specialty_edit':
    $css[] = STYLE_PATH . '/admin_specialty_edit.css';
    break;
  case 'admin_specialties':
    $css[] = STYLE_PATH . '/admin_specialties.css';
    break;
  case 'office_dashboard':
    $css[] = STYLE_PATH . '/office_dashboard.css';
    break;
  case 'doctor_schedule':
    $css[] = STYLE_PATH . '/doctor_schedule.css';
    break;
  case 'admin_users':
    $css[] = STYLE_PATH . '/admin_users.css';
    break;
  case 'clinic_setup':
    $css[] = STYLE_PATH . '/clinic_setup.css';
    break;
  case 'admin_user':
    $css[] = STYLE_PATH . '/admin_user.css';
    break;
  case 'home':
  case 'dashboard':
    $css[] = STYLE_PATH . '/home.css';
    break;
  default:
    $css[] = STYLE_PATH . '/login.css';
}
if (!$noChrome) {
  $css[] = STYLE_PATH . '/footer.css';
}

/* ------------------------------- View map -------------------------------- */
$views = [
  'home' => __DIR__ . '/views/home.php',
  'login' => __DIR__ . '/views/auth/login.php',
  'register' => __DIR__ . '/views/auth/register.php',
  'dashboard' => __DIR__ . '/views/user/dashboard.php',
  'appointments' => __DIR__ . '/views/user/appointments.php',
  'profile' => __DIR__ . '/views/user/profile.php',
  'clinics' => __DIR__ . '/views/search/clinics.php',
  'clinic' => __DIR__ . '/views/search/clinic.php',
  'doctor' => __DIR__ . '/views/search/doctor.php',
  'doctors' => __DIR__ . '/views/search/doctors.php',
  'confirm' => __DIR__ . '/views/booking/confirm.php',
  'google_choose_role' => __DIR__ . '/views/auth/google_choose_role.php',
  'book' => __DIR__ . '/views/appointment/calendar.php',
  'admin_dashboard' => __DIR__ . '/views/admin/admin_dashboard.php',
  'clinic_setup' => __DIR__ . '/views/clinic/clinic_setup.php',
  'office_dashboard' => __DIR__ . '/views/clinic/office_dashboard.php',
  'doctor_schedule' => __DIR__ . '/views/clinic/doctor_schedule.php',
  'admin_clinics' => __DIR__ . '/views/admin/clinics.php',
  'clinic_edit' => __DIR__ . '/views/admin/clinic_edit.php',
  'admin_specialties' => __DIR__ . '/views/admin/admin_specialties.php',
  'admin_specialty_edit' => __DIR__ . '/views/admin/specialty_edit.php',
  'admin_users' => __DIR__ . '/views/admin/users.php',
  'admin_user' => __DIR__ . '/views/admin/admin_user.php',
  'admin_doctors' => __DIR__ . '/views/admin/doctors.php',
  'admin_doctors_office' => __DIR__ . '/views/admin/doctors_office.php',
  'admin_doctor_edit' => __DIR__ . '/views/admin/doctor_edit.php',
  'admin_appointments' => __DIR__ . '/views/admin/admin_appointments.php',
  'help' => __DIR__ . '/views/static/help.php',
  'contact' => __DIR__ . '/views/static/contact.php',
  'privacy' => __DIR__ . '/views/static/privacy.php',
  'terms' => __DIR__ . '/views/static/terms.php'
];

/* ----------------------------- Controller flow ---------------------------- */
$brandTarget = current_user() ? 'dashboard' : 'home';
$data = null;

switch ($page) {
  case 'clinics':
    require_once __DIR__ . '/controller/ClinicsController.php';
    $data = ClinicsController::index();
    break;

  case 'clinic':
    require_once __DIR__ . '/controller/ClinicController.php';
    $data = ClinicController::show();
    break;

  case 'doctors':
    require_once __DIR__ . '/controller/DoctorController.php';
    $data = DoctorController::listByClinic();
    break;

  case 'doctor':
    require_once __DIR__ . '/controller/DoctorController.php';
    if (isset($_GET['ajax'])) {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(DoctorController::calendarData(), JSON_UNESCAPED_UNICODE);
      exit;
    }
    $data = DoctorController::visit();
    break;

  case 'confirm':
    require_once __DIR__ . '/controller/ConfirmController.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || (($_GET['do'] ?? '') === 'confirm')) {
      $data = ConfirmController::confirm(); // usually redirects
    } else {
      $data = ConfirmController::show();
    }
    break;

  case 'appointments':
    require_once __DIR__ . '/controller/AppointmentsController.php';
    $data = AppointmentsController::index();
    break;

  case 'admin_clinics':
    require_once __DIR__ . '/controller/AdminClinicsController.php';
    $data = AdminClinicsController::index();
    break;

  case 'clinic_edit':
    require_once __DIR__ . '/controller/AdminClinicEditController.php';
    $data = AdminClinicEditController::index(); // GET shows form, POST saves then redirects
    break;

  case 'admin_specialties':
    require_once __DIR__ . '/controller/AdminSpecialtiesController.php';
    $data = AdminSpecialtiesController::index();
    break;

  case 'admin_specialty_edit':
    require_once __DIR__ . '/controller/AdminSpecialtyEditController.php';
    $data = AdminSpecialtyEditController::index();
    break;
  case 'admin_users':
    require_once __DIR__ . '/controller/AdminUsersController.php';
    $data = AdminUsersController::index();
    break;
  // index.php or your front controller
  case 'admin_user':
    require_once __DIR__ . '/controller/AdminUserController.php';
    $data = AdminUserController::index();
    break;
  // Admin: Doctors
  case 'admin_doctors':
    require_once __DIR__ . '/controller/AdminDoctorsController.php';
    $data = AdminDoctorsController::index();
    break;

  case 'admin_doctors_office':
    require_once __DIR__ . '/controller/AdminDoctorsOfficeController.php';
    $data = AdminDoctorsOfficeController::index();
    break;
  case 'admin_doctor_edit':
    require_once __DIR__ . '/controller/AdminDoctorEditController.php';
    $data = AdminDoctorEditController::index();
    break;
  case 'admin_doctor_slots_api':
    require_once __DIR__ . '/../app/controller/AdminDoctorSlotsApiController.php';
    AdminDoctorSlotsApiController::index();
    exit;
  case 'admin_appointments':
    require_once __DIR__ . '/controller/AdminAppointmentsController.php'; // fixed path
    $data = AdminAppointmentsController::index(); // returns array
    break;
  case 'admin_dashboard':
    require_once __DIR__ . '/controller/AdminDashboardController.php';
    $data = AdminDashboardController::index();
    break;
  default:
    // pages without controllers simply fall through with $data === null
    break;
}

/* ------------------------------ Render layout ---------------------------- */
$view = $views[$page] ?? null;
$layout = __DIR__ . '/views/layout/' . ($noChrome ? 'blank.php' : 'app.php');
require $layout;