<?php
// app/views/layout/partials/footer.php

if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

$user      = $_SESSION['user'] ?? null;
$role      = $user['role'] ?? null;
$isOffice  = ($role === 'office');
$isAdmin   = ($role === 'admin');
$isStaff   = ($role === 'webstaff');
$officeId  = $isOffice ? (int)($user['profile_id'] ?? 0) : 0;

// Brand target by role
if ($isAdmin || $isStaff) {
  $brandHref = BASE_URL . 'index.php?page=admin_dashboard';
} elseif ($isOffice) {
  $brandHref = BASE_URL . 'index.php?page=office_dashboard';
} elseif ($user) {
  $brandHref = BASE_URL . 'index.php?page=dashboard';
} else {
  $brandHref = BASE_URL . 'index.php?page=home';
}

// Office clinic URL (falls back to Clinics if missing)
$officeClinicUrl = $officeId
  ? BASE_URL . 'index.php?page=clinic&id=' . $officeId
  : BASE_URL . 'index.php?page=clinics';
?>
<footer class="site-footer">
  <div class="footer__inner">
    <div class="footer__brand">
      <a class="brand" href="<?= e($brandHref) ?>">
        <span class="brand__name">MediBook</span>
      </a>
      <p class="footer__blurb">
        <?= $isOffice
            ? 'Manage your clinic profile, doctors, and schedules—right from MediBook.'
            : 'Find the right doctor, book appointments online, and manage your care—all in one place.' ?>
      </p>
    </div>

    <nav class="footer__cols">
      <div class="footer__col">
        <h4><?= $isOffice ? 'Clinic' : 'Explore' ?></h4>
        <ul>
          <?php if ($isOffice): ?>
            <li><a href="<?= e($officeClinicUrl) ?>">Your Clinic Page</a></li>
            <li><a href="<?= BASE_URL ?>index.php?page=office_dashboard">Office Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>index.php?page=clinic_setup">Clinic Setup</a></li>
          <?php else: ?>
            <li><a href="<?= BASE_URL ?>index.php?page=clinics">Find Clinics</a></li>
            <li><a href="<?= BASE_URL ?>index.php?page=doctor">Search Doctors</a></li>
            <li><a href="<?= BASE_URL ?>index.php?page=home#why">Why MediBook</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="footer__col">
        <h4>Account</h4>
        <ul>
          <?php if ($user): ?>
            <?php if ($isOffice): ?>
              <li><a href="<?= BASE_URL ?>index.php?page=office_dashboard">Office Dashboard</a></li>
            <?php elseif ($isAdmin || $isStaff): ?>
              <li><a href="<?= BASE_URL ?>index.php?page=admin_dashboard"><?= $isAdmin ? 'Admin' : 'Webstaff' ?> Panel</a></li>
              <li><a href="<?= BASE_URL ?>index.php?page=admin_clinics">Clinics</a></li>
            <?php else: ?>
              <li><a href="<?= BASE_URL ?>index.php?page=dashboard">Dashboard</a></li>
              <li><a href="<?= BASE_URL ?>index.php?page=appointments">My Appointments</a></li>
            <?php endif; ?>
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
      <?php if ($isOffice && $officeId): ?>
        <span style="opacity:.7"> • Office ID: <?= (int)$officeId ?></span>
      <?php endif; ?>
    </div>
  </div>
</footer>