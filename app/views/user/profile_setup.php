<?php // app/views/user/profile_setup.php ?>
<section class="wall">
  <div class="card profile-setup-card">
    <div class="profile-setup__header">
      <div class="profile-setup__header-copy">
        <h1 class="title profile-setup__title">Tell us about you</h1>
        <p class="subtitle profile-setup__subtitle">
          Just a few details so doctors know who they’re caring for.
        </p>
      </div>

      <!-- Avatar picker -->
      <div class="profile-setup__avatar">
        <div class="avatar-circle">
          <span>👤</span>
        </div>

        <div class="avatar-actions">
          <label class="avatar-upload-btn">
            <input
              class="avatar-upload-input"
              type="file"
              name="avatar"
              accept="image/*"
            >
            <span>Upload photo</span>
          </label>

          <button class="avatar-remove-btn" type="button">
            Remove
          </button>
        </div>
      </div>
    </div>

    <form
      id="profileForm"
      class="form"
      method="post"
      action="<?= rtrim(BASE_URL, '/') ?>/index.php?page=dashboard"
    >
      <!-- Row 1: Full Name + DOB -->
      <div class="profile-setup__row">
        <label class="form__label">
          Full Name
          <input
            type="text"
            name="full_name"
            placeholder="e.g., Jane Doe"
            required
          >
        </label>

        <label class="form__label">
          Date of Birth
          <input
            type="date"
            name="dob"
            max="<?= date('Y-m-d'); ?>"
            required
          >
        </label>
      </div>

      <!-- Row 2: Phone + Job Title -->
      <div class="profile-setup__row">
        <label class="form__label">
          Phone Number
          <input
            type="tel"
            name="phone"
            placeholder="e.g., (555) 123-4567"
          >
        </label>

        <label class="form__label">
          Job Title
          <input
            type="text"
            name="job_title"
            placeholder="e.g., Software Engineer"
            required
          >
        </label>
      </div>

      <!-- Row 3: Gender + Nationality -->
      <div class="profile-setup__row">
        <label class="form__label">
          Gender
          <select name="gender" required>
            <option value="" disabled selected>Select...</option>
            <option value="female">Female</option>
            <option value="male">Male</option>
          </select>
        </label>

        <label class="form__label">
          Nationality
          <select name="country" required>
            <option value="" disabled selected>Select nationality</option>
            <option value="US">United States</option>
            <option value="CA">Canada</option>
            <option value="GB">United Kingdom</option>
            <option value="AU">Australia</option>
            <option value="VN">Vietnam</option>
            <option value="SG">Singapore</option>
            <option value="OTHER">Other</option>
          </select>
        </label>
      </div>

      <!-- Action buttons -->
      <div class="profile-setup__actions">
        <button class="btn btn--primary btn--xl" type="submit">
          Save &amp; Continue
        </button>
      </div>
    </form>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('profileForm');
    if (!form) return;

    // Submit guard to trigger browser validation UI
    form.addEventListener('submit', (e) => {
      if (!form.checkValidity()) {
        e.preventDefault();
        form.reportValidity();
      }
    });
  });
</script>