<?php // app/views/auth/register2.php ?>
<section class="wall">
    <div class="card">
        <div class="card__logo">
            <img src="<?= IMAGE_PATH ?>/Logo.svg" alt="MediBook" />
        </div>

        <h1 class="title">Join MediBook today!</h1>
        <p class="subtitle">Sign up in seconds and start booking your medical appointments online.</p>

        <form class="form" method="post" action="#">
            <label class="form__label">First Name
                <input type="text" name="first_name" placeholder="Enter your first name" required>
            </label>

            <label class="form__label">Last Name
                <input type="text" name="last_name" placeholder="Enter your last name" required>
            </label>

            <label class="form__label">Date of Birth
                <input type="text" name="dob" placeholder="Enter your date of birth" required>
            </label>

            <div class="form__row" style="justify-content:flex-start; gap:18px; align-items:center;">
                <span class="form__label" style="margin:0;">Sex</span>
                <label style="display:flex; align-items:center; gap:8px; font-size:14px; color:#5c6b80;">
                    <input type="radio" name="sex" value="male"> Male
                </label>
                <label style="display:flex; align-items:center; gap:8px; font-size:14px; color:#5c6b80;">
                    <input type="radio" name="sex" value="female"> Female
                </label>
            </div>

            <!-- replace the existing single Sign up button with this row -->
            <div class="form__row" style="justify-content:space-between; gap:12px; margin-top:6px;">
                <a class="btn btn--ghost" href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=register">← Back</a>
                <button class="btn btn--primary btn--xl" type="submit">Sign up</button>
            </div>
        </form>
    </div>
</section>