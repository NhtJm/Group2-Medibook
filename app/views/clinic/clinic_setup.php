<?php 
// app/views/clinic/clinic_setup.php 
$office = $data['office'] ?? null;
$errors = $data['errors'] ?? [];
?>
<section class="wall">
  <div class="card profile-setup-card">
    <div class="profile-setup__header">
      <div class="profile-setup__header-copy">
        <h1 class="title profile-setup__title">
          <?= $office ? 'Update your clinic' : 'Tell us about your clinic' ?>
        </h1>
        <p class="subtitle profile-setup__subtitle">
          We'll use this information to help patients find and trust your center.
        </p>
      </div>

      <!-- Logo picker -->
      <div class="profile-setup__avatar">
        <?php 
        $hasValidLogo = $office && $office['logo'] && str_starts_with($office['logo'], 'uploads/');
        ?>
        <div class="avatar-circle" id="avatarCircle" <?php if ($hasValidLogo): ?>style="background-image: url('<?= BASE_URL ?><?= e($office['logo']) ?>'); background-size: cover; background-position: center;"<?php endif; ?>>
          <span id="avatarEmoji" <?php if ($hasValidLogo): ?>style="display: none;"<?php endif; ?>>🏥</span>
        </div>

        <div class="avatar-actions">
          <label class="avatar-upload-btn">
            <input
              class="avatar-upload-input"
              type="file"
              name="logo"
              accept="image/*"
              form="clinicForm"
            >
            <span>Upload logo</span>
          </label>

          <button class="avatar-remove-btn" type="button">
            Remove
          </button>
        </div>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="form-errors" style="background: #fee; border: 1px solid #c00; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
      <ul style="margin: 0; padding-left: 1.2rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <form
      id="clinicForm"
      class="form"
      method="post"
      enctype="multipart/form-data"
      action="<?= rtrim(BASE_URL, '/') ?>/index.php?page=clinic_setup"
    >
      <!-- Row 1: Clinic Name + Phone -->
      <div class="profile-setup__row">
        <label class="form__label">
          Clinic / Center Name
          <input
            type="text"
            name="clinic_name"
            placeholder="e.g., Sunrise Family Health Center"
            value="<?= e($office['name'] ?? '') ?>"
            required
          >
        </label>

        <label class="form__label">
          Phone Number
          <input
            type="tel"
            name="clinic_phone"
            placeholder="e.g., (555) 987-6543"
            value="<?= e($office['phone'] ?? '') ?>"
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
            value="<?= e($office['address'] ?? '') ?>"
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
          ><?= e($office['description'] ?? '') ?></textarea>
        </label>
      </div>

      <!-- Action buttons -->
      <div class="profile-setup__actions">
        <button class="btn btn--primary btn--xl" type="submit">
          <?= $office ? 'Update Clinic' : 'Save & Continue' ?>
        </button>
        <?php if ($office): ?>
        <a href="<?= BASE_URL ?>index.php?page=office_dashboard" class="btn btn--secondary btn--xl">
          Back to Dashboard
        </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('clinicForm');
    if (!form) return;

    form.addEventListener('submit', (e) => {
      if (!form.checkValidity()) {
        e.preventDefault();
        form.reportValidity();
      }
    });

    const fileInput = form.querySelector('.avatar-upload-input');
    const avatarCircle = document.getElementById('avatarCircle');
    const avatarEmoji = document.getElementById('avatarEmoji');
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
          if (avatarEmoji) avatarEmoji.style.display = 'none';
        };
        reader.readAsDataURL(file);
      });

      removeBtn.addEventListener('click', () => {
        fileInput.value = '';
        avatarCircle.style.backgroundImage = 'none';
        if (avatarEmoji) {
          avatarEmoji.style.display = '';
        } else {
          avatarCircle.innerHTML = '<span id="avatarEmoji">🏥</span>';
        }
      });
    }
  });
</script>