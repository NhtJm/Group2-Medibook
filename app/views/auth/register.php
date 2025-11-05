<?php 
require_once __DIR__ . '/../../db.php';  // db.php nằm trong app/
$conn = Database::get_instance();

if (!$conn) { die('DB not connected'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $pass     = $_POST['password'] ?? '';
  $confirm  = $_POST['password_confirm'] ?? '';

  $errors = [];

  if ($username === '') $errors[] = 'Username is required';
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
  if ($pass === '' || strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters';
  if ($pass !== $confirm) $errors[] = 'Passwords do not match';

  if (!$errors) {
    // kiểm tra trùng username/email
    $stmt = $conn->prepare("SELECT 1 FROM Users WHERE username = ? OR email = ?");
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $errors[] = 'Username or email already exists';
    }
    $stmt->close();
  }

  if (!$errors) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO Users (username, email, password_hash, name) VALUES (?, ?, ?, ?)");
    /* tạm dùng username làm name; sau này bạn thêm field "Full name" thì bind tên thật */
    $name = $username;
    $stmt->bind_param('ssss', $username, $email, $hash, $name);
    if ($stmt->execute()) {
      header('Location: ' . rtrim(BASE_URL, '/') . '/index.php?page=login');
      exit;
    } else {
      $errors[] = 'Register failed: ' . $stmt->error;
    }
    $stmt->close();
  }
}

?>
<section class="wall">
  <div class="card">
    <div class="card__logo">
      <img src="<?= IMAGE_PATH ?>/Logo.svg" alt="MediBook" />
    </div>

    <h1 class="title">Join MediBook today!</h1>
    <p class="subtitle">Sign up in seconds and start booking your medical appointments online.</p>

    <style>
      .notice{display:flex; gap:12px; align-items:flex-start; padding:12px 14px; 
        border-radius:12px; border:1px solid; background:#fff; margin:16px 0;}
      .notice--error{background:#fff6f6; border-color:#f3c2c2;}
      .notice__icon{width:20px; height:20px; flex:0 0 auto; margin-top:2px;}
      .notice__title{font-weight:700; color:#7a0f0f; margin:0 0 2px;}
      .notice__list{margin:4px 0 0 18px; padding:0; color:#7a0f0f;}
    </style>

    <div class="segmented" role="tablist" aria-label="Auth tabs">
        <a role="tab" aria-selected="false"
          href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=login"
          class="segmented__item">Log In</a>

        <a role="tab" aria-selected="true"
          href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=register"
          class="segmented__item is-active">Sign Up</a>
    </div>

      <?php if (!empty($errors)): ?>
        <div class="notice notice--error" role="alert" aria-live="polite" style="margin-top:14px;">
          <svg class="notice__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke="#c0392b" stroke-width="2"/>
            <path d="M12 7v7" stroke="#c0392b" stroke-width="2" stroke-linecap="round"/>
            <circle cx="12" cy="17" r="1.5" fill="#c0392b"/>
          </svg>
          <div>
            <div class="notice__title">Registration failed</div>
            <ul class="notice__list">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>

    <form id="regForm" class="form" method="post" action="">
      <label class="form__label">Username
        <input type="text" name="username" placeholder="Choose a username" required>
      </label>

      <label class="form__label">Email address
        <input type="email" name="email" placeholder="Enter your email address" required>
      </label>

      <label class="form__label">Password
        <input type="password" name="password" placeholder="Enter your password" required minlength="8">
      </label>

      <label class="form__label">Confirm Password
        <input type="password" name="password_confirm" placeholder="Enter your password again" required minlength="8">
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

  const passEl    = form.elements['password'];
  const confirmEl = form.elements['password_confirm'];

  function validatePasswordsMatch() {
    confirmEl.setCustomValidity('');
    if (passEl.value && confirmEl.value && passEl.value !== confirmEl.value) {
      confirmEl.setCustomValidity('Passwords do not match');
    }
  }

  passEl.addEventListener('input', validatePasswordsMatch);
  confirmEl.addEventListener('input', validatePasswordsMatch);
});
</script>
