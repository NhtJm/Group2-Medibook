<?php
// app/views/layout/partials/footer.php
?>
<footer class="site-footer">
    <div class="footer__inner">
        <div class="footer__brand">
            <a class="brand" href="<?= BASE_URL ?>index.php?page=home">
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
                    <li><a href="<?= BASE_URL ?>index.php?page=doctor">Search Doctors</a></li>
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