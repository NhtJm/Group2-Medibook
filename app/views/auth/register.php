<?php 
require_once __DIR__ . '/../../db.php';  // db.php nằm trong app/
require_once __DIR__ . '/../../../config/config.php';

$conn = Database::get_instance();

if (!$conn) { die('DB not connected'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $pass     = $_POST['password'] ?? '';
  $confirm  = $_POST['password_confirm'] ?? '';
  $full_name = trim($_POST['full_name'] ?? $username);

  if ($username === '') $errors[] = 'Username is required';
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
  if ($pass === '' || strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters';
  if ($pass !== $confirm) $errors[] = 'Passwords do not match';

  if (!$errors) {
    $stmt = $conn->prepare("SELECT 1 FROM Users WHERE username = ? OR email = ?");
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors[] = 'Username or email already exists';
    $stmt->close();
  }

  $account_type = $_POST['account_type'] ?? 'patient';
  $allowed_types = ['patient','webstaff','office','admin'];
  if (!in_array($account_type, $allowed_types, true)) $account_type = 'patient';

  if (!$errors) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $conn->begin_transaction();
    try {
      // 1) Tạo Users (đúng cột: full_name, role)
      $stmt = $conn->prepare("
        INSERT INTO Users (email, username, password_hash, full_name, role)
        VALUES (?, ?, ?, ?, ?)
      ");
      $stmt->bind_param('sssss', $email, $username, $hash, $full_name, $account_type);
      if (!$stmt->execute()) throw new Exception($stmt->error);
      $user_id = $conn->insert_id;
      $stmt->close();

      // 2) Hồ sơ theo role
      if ($account_type === 'patient') {
        $stmt = $conn->prepare("INSERT INTO Patient (user_id, status) VALUES (?, 'active')");
        $stmt->bind_param('i', $user_id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

      } elseif ($account_type === 'webstaff') {
        $stmt = $conn->prepare("INSERT INTO Web_staff (user_id, position, status) VALUES (?, 'support', 'offline')");
        $stmt->bind_param('i', $user_id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

      } elseif ($account_type === 'office') {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $full_name), '-'));
        $slug = $slug ?: 'office';
        $slug = $slug . '-' . $user_id;
        
        $stmt = $conn->prepare("INSERT INTO Office (user_id, name, slug, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param('iss', $user_id, $full_name, $slug);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

      } elseif ($account_type === 'admin') {
        $stmt = $conn->prepare("INSERT INTO Admin (user_id) VALUES (?)");
        $stmt->bind_param('i', $user_id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
      }

      $conn->commit();
      
      if (session_status() === PHP_SESSION_NONE) session_start();
      session_regenerate_id(true);
      $_SESSION['user'] = [
        'id' => $user_id,
        'email' => $email,
        'username' => $username,
        'name' => $full_name,
        'role' => $account_type,
      ];
      
      $redirectPage = 'dashboard';
      if ($account_type === 'admin') {
        $redirectPage = 'admin_dashboard';
      } elseif ($account_type === 'office') {
        $redirectPage = 'office_dashboard';
      }
      
      header('Location: ' . rtrim(BASE_URL, '/') . '/index.php?page=' . $redirectPage);
      exit;

    } catch (Throwable $e) {
      $conn->rollback();
      $errors[] = 'Register failed. Please try again.';
      error_log('Registration error: ' . $e->getMessage());
    }
  }
}

?>
<section class="wall">
  <div class="card">
    <?php
    $u = current_user();
    $targetPage = ($u && (($u['role'] ?? '') === 'admin')) ? 'admin_dashboard' : ($u ? 'dashboard' : 'home');
    ?>
    <div class="card__logo">
      <a class="auth-logo" href="<?= e(BASE_URL . 'index.php?page=' . $targetPage) ?>" aria-label="Back to Dashboard">
        <img src="<?= IMAGE_PATH ?>/Logo.png" alt="MediBook" width="56" height="56" decoding="async" loading="eager">
      </a>
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
      .radio-group { display:flex; gap:16px; align-items:center; }
      .radio-group label { display:inline-flex; gap:6px; align-items:center; font-weight:400; }
    </style>

    <div class="segmented" role="tablist" aria-label="Auth tabs">
        <a role="tab" aria-selected="false"
          href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=login"
          class="segmented__item">Log In</a>

        <a role="tab" aria-selected="true"
          href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=register"
          class="segmented__item is-active">Sign Up</a>
    </div>

    <div class="oauth-or" style="text-align:center;margin:16px 0 8px;color:#6b7a90;">or</div>
      <a class="btn btn--ghost btn--xl"
        style="width:100%;display:flex;align-items:center;justify-content:center;gap:10px"
        href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=google_choose_role">
        <img src="<?= IMAGE_PATH ?>google.svg" alt="" style="width:18px;height:18px">
        <span>Sign in with Google</span>
      </a>

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

      <div class="field">
        <label>Account type</label>
        <div class="radio-group">
          <label><input type="radio" name="account_type" value="patient" checked> Patient</label>
          <label><input type="radio" name="account_type" value="webstaff"> Web staff</label>
          <label><input type="radio" name="account_type" value="office"> Office</label>
        </div>
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
