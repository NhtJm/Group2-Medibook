<?php // app/views/auth/login.php ?>
<section class="wall">
  <div class="card">
    <div class="card__logo">
      <img src="<?= IMAGE_PATH ?>/Logo.svg" alt="Medibook" />
    </div>

    <h1 class="title">Welcome Back!</h1>
    <p class="subtitle">Sign in to book and track your appointments</p>

    <!-- Use router param instead of linking to /app/views files -->
    <div class="segmented" role="tablist" aria-label="Auth tabs">
      <a role="tab" aria-selected="true" href="<?= BASE_URL ?>/index.php?page=login"
        class="segmented__item is-active">Log In</a>
      <a role="tab" aria-selected="false" href="<?= BASE_URL ?>/index.php?page=register" class="segmented__item">Sign
        Up</a>
    </div>

    <form class="form" method="post" action="<?= BASE_URL ?>/index.php?page=login">
      <label class="form__label">Email address
        <input type="email" name="email" placeholder="Enter your email address" required>
      </label>

      <label class="form__label">Password
        <input type="password" name="password" placeholder="Enter your password" required>
      </label>

      <div class="form__row">
        <a class="link" href="#">Forgot Password?</a>
      </div>

      <button class="btn btn--primary btn--xl" type="submit">Log In</button>
    </form>
    <p class="subtitle" style="text-align:center;margin-top:8px;">
      <a class="link" href="<?= BASE_URL ?>/index.php?page=demo_login">Use demo account</a>
    </p>
  </div>
</section>