<?php // app/views/clinic/setup_clinic.php ?>
<section class="wall">
  <div class="card profile-setup-card">
    <div class="profile-setup__header">
      <div class="profile-setup__header-copy">
        <h1 class="title profile-setup__title">Tell us about your clinic</h1>
        <p class="subtitle profile-setup__subtitle">
          We’ll use this information to help patients find and trust your center.
        </p>
      </div>

      <!-- Logo picker -->
      <div class="profile-setup__avatar">
        <div class="avatar-circle">
          <span>🏥</span>
        </div>

        <div class="avatar-actions">
          <label class="avatar-upload-btn">
            <input
              class="avatar-upload-input"
              type="file"
              name="logo"
              accept="image/*"
            >
            <span>Upload logo</span>
          </label>

          <button class="avatar-remove-btn" type="button">
            Remove
          </button>
        </div>
      </div>
    </div>

    <form
      id="clinicForm"
      class="form"
      method="post"
      enctype="multipart/form-data"
      action="<?= rtrim(BASE_URL, '/') ?>/index.php?page=office_dashboard"
    >
      <!-- Row 1: Clinic Name + Phone -->
      <div class="profile-setup__row">
        <label class="form__label">
          Clinic / Center Name
          <input
            type="text"
            name="clinic_name"
            placeholder="e.g., Sunrise Family Health Center"
            required
          >
        </label>

        <label class="form__label">
          Phone Number
          <input
            type="tel"
            name="clinic_phone"
            placeholder="e.g., (555) 987-6543"
            required
          >
        </label>
      </div>

      <!-- Row 2: Address (full width) -->
      <div class="profile-setup__row profile-setup__row--full">
        <label class="form__label" style="flex:1;">
          Address
          <input
            type="text"
            name="clinic_address"
            placeholder="123 Main St, Suite 200, Springfield"
            required
          >
        </label>
      </div>

      <!-- Row 3: Description (full width textarea) -->
      <div class="profile-setup__row profile-setup__row--full">
        <label class="form__label" style="flex:1;">
          Description
          <textarea
            name="clinic_description"
            placeholder="Briefly describe your services, specialties, and what makes your clinic unique."
            rows="4"
            required
            style="resize: vertical;"
          ></textarea>
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
    const form = document.getElementById('clinicForm');
    if (!form) return;

    // browser validation guard
    form.addEventListener('submit', (e) => {
      if (!form.checkValidity()) {
        e.preventDefault();
        form.reportValidity();
      }
    });

    // optional: preview/remove logo (front-end only)
    const fileInput = form.querySelector('.avatar-upload-input');
    const avatarCircle = document.querySelector('.avatar-circle');
    const removeBtn = document.querySelector('.avatar-remove-btn');

    if (fileInput && avatarCircle && removeBtn) {
      fileInput.addEventListener('change', () => {
        const file = fileInput.files && fileInput.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
          avatarCircle.style.backgroundImage = `url('${e.target.result}')`;
          avatarCircle.style.backgroundSize = 'cover';
          avatarCircle.style.backgroundPosition = 'center';
          avatarCircle.textContent = ''; // remove emoji once we have an image
        };
        reader.readAsDataURL(file);
      });

      removeBtn.addEventListener('click', () => {
        // clear file input
        fileInput.value = '';
        // reset avatar circle
        avatarCircle.style.backgroundImage = 'none';
        avatarCircle.textContent = '🏥';
      });
    }
  });
</script>