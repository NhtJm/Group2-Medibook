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
            <?php if (current_user()): ?>
                <a class="btn" href="<?= BASE_URL ?>index.php?page=clinics">Search</a>
                <a class="btn" href="<?= BASE_URL ?>index.php?page=appointments">My Appointments</a>

                <div class="dropdown">
                    <button id="accountBtn" class="dropbtn">
                        <?= e(current_user()['name'] ?? 'Account') ?>
                        <span class="chev"></span>
                    </button>
                    <div id="accountMenu" class="dropdown-content">
                        <?php if (current_user() && (current_user()['role'] ?? null) === 'admin'): ?>
                            <a href="<?= BASE_URL ?>index.php?page=admin_dashboard">Admin</a>
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
        const btn = e.target.closest('#accountBtn');
        const menu = document.getElementById('accountMenu');
        const box = e.target.closest('.dropdown');
        if (btn) { menu.classList.toggle('show'); return; }
        if (!box && menu) menu.classList.remove('show');
    });
</script>