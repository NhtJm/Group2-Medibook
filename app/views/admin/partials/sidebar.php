<?php
if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}

$admin = current_user() ?? [];
$fullFromParts = trim(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? ''));
$adminName = ($admin['full_name'] ?? null)
  ?? ($fullFromParts !== '' ? $fullFromParts : null)
  ?? ($admin['name'] ?? null)
  ?? ($admin['display_name'] ?? null)
  ?? ($admin['username'] ?? null)
  ?? ($admin['email'] ?? 'Administrator');

$active = $active ?? ''; // set by the view using this partial
?>
<aside class="admin-sidebar">
  <div class="admin-sidebar__brand">
    <div class="brand-icon"><img src="<?= IMAGE_PATH ?>/Logo.svg" alt="MediBook"></div>
    <div class="brand-copy">
      <div class="brand-name">MediBook Admin</div>
      <div class="brand-user"><?= e($adminName) ?></div>
    </div>
  </div>

  <nav class="admin-nav">
    <div class="admin-nav__section">
      <div class="admin-nav__label">MAIN</div>

      <a class="admin-nav__item <?= $active === 'admin_dashboard' ? 'is-active' : '' ?>"
        href="<?= e(BASE_URL . 'index.php?page=admin_dashboard') ?>">
        <span class="admin-nav__icon">📊</span>
        <span class="admin-nav__text">Dashboard</span>
        <span class="admin-nav__badge">v2</span>
      </a>
      <a class="admin-nav__item <?= $active === 'admin_clinics' ? 'is-active' : '' ?>"
        href="<?= e(BASE_URL . 'index.php?page=admin_clinics') ?>">
        <span class="admin-nav__icon">🏥</span>
        <span class="admin-nav__text">Clinics</span>
      </a>
      <a class="admin-nav__item <?= $active === 'admin_specialties' ? 'is-active' : '' ?>"
        href="<?= e(BASE_URL . 'index.php?page=admin_specialties') ?>">
        <span class="admin-nav__icon">👨‍⚕️</span>
        <span class="admin-nav__text">Medical Specialties</span>
      </a>

      <!-- app/views/admin/partials/sidebar.php -->
      <a class="admin-nav__item <?= $active === 'admin_users' ? 'is-active' : '' ?>"
        href="<?= e(BASE_URL . 'index.php?page=admin_users') ?>">
        <span class="admin-nav__icon">🧑</span>
        <span class="admin-nav__text">Users</span>
      </a>

      <a class="admin-nav__item <?= ($active === 'admin_doctors' ? 'is-active' : '') ?>"
        href="<?= e(BASE_URL) ?>index.php?page=admin_doctors">
        <span class="admin-nav__icon">👨‍⚕️</span>
        <span class="admin-nav__text">Doctors</span>
      </a>

      <a class="admin-nav__item <?= ($active ?? '') === 'admin_appointments' ? 'is-active' : '' ?>"
        href="<?= e(BASE_URL . 'index.php?page=admin_appointments') ?>">
        <span class="admin-nav__icon">💳</span>
        <span class="admin-nav__text">Appointments</span>
      </a>

    </div>

    <div class="admin-nav__section">
      <div class="admin-nav__label">REPORTS</div>
      <a class="admin-nav__item" href="#">
        <span class="admin-nav__icon">📈</span>
        <span class="admin-nav__text">Complains</span>
      </a>
    </div>

    <div class="admin-nav__section">
      <div class="admin-nav__label">ACCOUNT</div>
      <a class="admin-nav__item" href="<?= e(BASE_URL . 'index.php?page=dashboard') ?>">
        <span class="admin-nav__icon">👤</span>
        <span class="admin-nav__text">Homepage</span>
      </a>

      <a class="admin-nav__item" href="#" onclick="document.getElementById('adminLogoutForm').submit();return false;">
        <span class="admin-nav__icon">🚪</span>
        <span class="admin-nav__text">Sign Out</span>
      </a>
      <form id="adminLogoutForm" action="<?= e(BASE_URL . 'index.php?page=logout') ?>" method="post"
        style="display:none">
      </form>
    </div>
  </nav>
</aside>