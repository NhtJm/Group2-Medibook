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
         href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=login"
         class="segmented__item">Log In</a>

      <a role="tab" aria-selected="true"
         href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=register"
         class="segmented__item is-active">Sign Up</a>
    </div>

    <form id="regForm" class="form" method="post" action="#">
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
          Signing up for your healthcare facility?
        </label>
      </div>

      <!-- Submit -->
      <div class="form__row" style="justify-content:space-between; gap:12px; margin-top:6px;">
        <button class="btn btn--primary btn--xl" type="submit">Sign up</button>
      </div>
    </form>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('regForm');
  if (!form) return;

  const emailEl     = form.elements['email'];
  const passEl      = form.elements['password'];
  const confirmEl   = form.elements['password_confirm'];
  const facilityEl  = form.elements['is_professional'];

  // where we send them next
  const PATIENT_NEXT  = "<?= rtrim(BASE_URL, '/') ?>/index.php?page=profile_setup";
  const CLINIC_NEXT   = "<?= rtrim(BASE_URL, '/') ?>/index.php?page=clinic_setup";

  function validatePasswordsMatch() {
    confirmEl.setCustomValidity('');
    if (passEl.value && confirmEl.value && passEl.value !== confirmEl.value) {
        confirmEl.setCustomValidity('Passwords do not match');
    }
  }

  // keep validation message live
  passEl.addEventListener('input', validatePasswordsMatch);
  confirmEl.addEventListener('input', validatePasswordsMatch);

  form.addEventListener('submit', (e) => {
    e.preventDefault(); // we control navigation manually

    // run browser validations
    validatePasswordsMatch();
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    // you can AJAX-save the account here later (email/pass),
    // for now we just branch the flow:
    const isFacility = facilityEl.checked;

    if (isFacility) {
      // healthcare facility → go to clinic/facility onboarding (register2)
      window.location.href = CLINIC_NEXT;
    } else {
      // regular patient → go fill personal details (profile_setup)
      window.location.href = PATIENT_NEXT;
    }
  });
});
</script>