<?php
// app/views/layout/partials/header.php
?>
<header class="topbar">
  <div class="topbar__inner">
    <a class="brand" href="<?= BASE_URL ?>index.php?page=<?= e($brandTarget) ?>">
      <img src="<?= IMAGE_PATH ?>/Logo.png" alt="" class="brand__logo" />
      <span class="brand__name">MEDIBOOK</span>
    </a>

    <nav class="topbar__nav">
      <?php if ($u = current_user()): ?>
        <?php
          $role     = $u['role'] ?? null;
          $isOffice = ($role === 'office');
          // from your login flow, profile_id is office_id for office role
          $officeId = (int)($u['profile_id'] ?? 0);
          $clinicHref = $isOffice && $officeId
            ? BASE_URL . 'index.php?page=clinic&id=' . $officeId     // e.g. ...&id=427
            : BASE_URL . 'index.php?page=clinics';
        ?>

        <!-- Left buttons adapt for office role -->
        <a class="btn" href="<?= e($clinicHref) ?>">
          <?= $isOffice ? 'My Clinic' : 'All Clinics' ?>
        </a>

        <a class="btn" href="<?= e($isOffice
              ? BASE_URL . 'index.php?page=office_dashboard'
              : BASE_URL . 'index.php?page=appointments') ?>">
          <?= $isOffice ? 'Office Dashboard' : 'My Appointments' ?>
        </a>

        <div class="dropdown">
          <button id="accountBtn" class="dropbtn">
            <?= e($u['name'] ?? 'Account') ?>
            <span class="chev"></span>
          </button>
          <div id="accountMenu" class="dropdown-content">
            <?php if (in_array($role, ['admin','webstaff'], true)): ?>
              <a href="<?= BASE_URL ?>index.php?page=admin_dashboard">
                <?= $role === 'admin' ? 'Admin' : 'Webstaff' ?>
              </a>
            <?php endif; ?>

            <?php if ($isOffice): ?>
              <a href="<?= BASE_URL ?>index.php?page=office_dashboard">Office Dashboard</a>
            <?php endif; ?>

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

<script>
  // dropdown toggle
  document.addEventListener('click', function (e) {
    const btn  = e.target.closest('#accountBtn');
    const menu = document.getElementById('accountMenu');
    const box  = e.target.closest('.dropdown');
    if (btn) { menu.classList.toggle('show'); return; }
    if (!box && menu) menu.classList.remove('show');
  });
</script>