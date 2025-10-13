<?php // app/views/auth/register.php ?>
<section class="wall">
  <div class="card">
    <div class="card__logo">
      <img src="<?= IMAGE_PATH ?>/Logo.svg" alt="MediBook" />
    </div>

    <h1 class="title">Join MediBook today!</h1>
    <p class="subtitle">Sign up in seconds and start booking your medical appointments online.</p>

    <div class="segmented" role="tablist" aria-label="Auth tabs">
      <a role="tab" aria-selected="false"
         href="<?= rtrim(BASE_URL,'/') ?>/index.php?page=login"
         class="segmented__item">Log In</a>
      <a role="tab" aria-selected="true"
         href="<?= rtrim(BASE_URL,'/') ?>/index.php?page=register"
         class="segmented__item is-active">Sign Up</a>
    </div>

    <form id="regForm" class="form" method="post" action="#"
          data-next="<?= rtrim(BASE_URL,'/') ?>/index.php?page=register2">
      <label class="form__label">Email address
        <input type="email" name="email" placeholder="Enter your email address" required>
      </label>

      <label class="form__label">Password
        <input type="password" name="password" placeholder="Enter your password" required minlength="6">
      </label>

      <label class="form__label">Confirm Password
        <input type="password" name="password_confirm" placeholder="Enter your password again" required minlength="6">
      </label>

      <div class="form__row" style="justify-content:flex-start; gap:8px;">
        <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#5c6b80;">
          <input type="checkbox" name="is_professional" style="width:16px;height:16px;">
          Are you a professional?
        </label>
      </div>

      <!-- Next (only if valid) -->
      <button id="nextBtn" class="btn btn--primary btn--xl" type="button">Next</button>
    </form>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('regForm');
  const next = document.getElementById('nextBtn');
  if (!form || !next) return;

  const pass = form.elements['password'];
  const confirm = form.elements['password_confirm'];

  function validate() {
    // reset custom message first
    confirm.setCustomValidity('');
    // native required/email/minlength checks
    let ok = form.checkValidity();
    // passwords must match
    if (pass.value && confirm.value && pass.value !== confirm.value) {
      confirm.setCustomValidity('Passwords do not match');
      ok = false;
    }
    return ok;
  }

  next.addEventListener('click', function () {
    if (validate()) {
      window.location.href = form.dataset.next; // go to page 2
    } else {
      form.reportValidity(); // show browser validation UI
    }
  });
});
</script>