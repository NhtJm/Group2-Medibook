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

$active = $active ?? '';                // provided by caller
$role = $admin['role'] ?? '';
$isAdmin = ($role === 'admin');
$isStaff = in_array($role, ['admin', 'webstaff'], true);   // moderators + admins

$homeTarget = $isStaff ? 'dashboard' : 'dashboard';
?>
<?php
$role = $admin['role'] ?? '';
$panelTitle = ($role === 'admin') ? 'MediBook Admin' : (($role === 'webstaff') ? 'MediBook Webstaff' : 'MediBook');
?>
<aside class="admin-sidebar">
  <div class="admin-sidebar__brand">
    <div class="brand-icon"><img src="<?= IMAGE_PATH ?>/Logo.png" alt="MediBook"></div>
    <div class="brand-copy">
      <div class="brand-name"><?= e($panelTitle) ?></div>
      <div class="brand-user"><?= e($adminName) ?></div>
    </div>
  </div>

  <nav class="admin-nav">
    <div class="admin-nav__section">
      <div class="admin-nav__label">MAIN</div>

      <?php if ($isStaff): ?>
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

        <a class="admin-nav__item <?= $active === 'admin_appointments' ? 'is-active' : '' ?>"
          href="<?= e(BASE_URL . 'index.php?page=admin_appointments') ?>">
          <span class="admin-nav__icon">💳</span>
          <span class="admin-nav__text">Appointments</span>
        </a>
      <?php endif; ?>

      <?php if ($isAdmin): ?>
        <a class="admin-nav__item <?= $active === 'admin_specialties' ? 'is-active' : '' ?>"
          href="<?= e(BASE_URL . 'index.php?page=admin_specialties') ?>">
          <span class="admin-nav__icon">🩺</span>
          <span class="admin-nav__text">Medical Specialties</span>
        </a>

        <a class="admin-nav__item <?= $active === 'admin_users' ? 'is-active' : '' ?>"
          href="<?= e(BASE_URL . 'index.php?page=admin_users') ?>">
          <span class="admin-nav__icon">🧑</span>
          <span class="admin-nav__text">Users</span>
        </a>

        <a class="admin-nav__item <?= $active === 'admin_doctors' ? 'is-active' : '' ?>"
          href="<?= e(BASE_URL . 'index.php?page=admin_doctors') ?>">
          <span class="admin-nav__icon">👨‍⚕️</span>
          <span class="admin-nav__text">Doctors</span>
        </a>
      <?php endif; ?>
    </div>

    <div class="admin-nav__section">
      <div class="admin-nav__label">REPORTS</div>
      <!-- visible to staff + admin; wire later to admin_complaints -->
      <a class="admin-nav__item" href="#">
        <span class="admin-nav__icon">📈</span>
        <span class="admin-nav__text">Complaints</span>
      </a>
    </div>

    <div class="admin-nav__section">
      <div class="admin-nav__label">ACCOUNT</div>

      <a class="admin-nav__item" href="<?= e(BASE_URL . 'index.php?page=' . $homeTarget) ?>">
        <span class="admin-nav__icon">👤</span>
        <span class="admin-nav__text">Homepage</span>
      </a>

      <a class="admin-nav__item" href="<?= e(BASE_URL . 'index.php?page=logout') ?>">
        <span class="admin-nav__icon">🚪</span>
        <span class="admin-nav__text">Sign Out</span>
      </a>
    </div>
  </nav>
</aside>