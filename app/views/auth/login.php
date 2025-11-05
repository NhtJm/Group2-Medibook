<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Gom lỗi để hiển thị trong view
$errors = [];

// BASE_URL, ... nếu bạn cần hằng số khác
require_once __DIR__ . '/../../../config/config.php';

// Kéo file db.php (Singleton)
$dbPath = __DIR__ . '/../../db.php'; // app/views/auth/../../ == app/
if (file_exists($dbPath)) {
  require_once $dbPath;
} else {
  $errors[] = 'db.php not found at ' . $dbPath;
}

// Lấy kết nối $conn từ Singleton (db.php của bạn)
if (class_exists('Database')) {
  $conn = Database::get_instance(); // <-- LẤY MYSQLI CONNECTION Ở ĐÂY
} else {
  $errors[] = 'Database class not found. Check app/db.php';
}

// Đảm bảo $conn là mysqli hợp lệ
if (!isset($conn) || !($conn instanceof mysqli)) {
  $errors[] = 'Database connection object is invalid.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  if ($email === '' || $pass === '') {
    $errors[] = 'Email and password are required';
  }

  if (!$errors) {
    $stmt = $conn->prepare("
      SELECT user_id, email, username, full_name, role, password_hash
      FROM Users
      WHERE email = ?
      LIMIT 1
    ");
    if ($stmt) {
      $stmt->bind_param('s', $email);
      $stmt->execute();
      $res  = $stmt->get_result();
      $user = $res ? $res->fetch_assoc() : null;
      $stmt->close();
    } else {
      $user = null;
      $errors[] = 'Failed to prepare login query.';
    }

    if (!$user || !password_verify($pass, $user['password_hash'])) {
      $errors[] = 'Invalid credentials';
    } else {
      // Lấy profile_id theo role (nếu có)
      $profileId = null;
      $roleMap = [
        'patient'  => "SELECT patient_id AS pid FROM Patient WHERE user_id=? LIMIT 1",
        'webstaff' => "SELECT staff_id  AS pid FROM Web_staff WHERE user_id=? LIMIT 1",
        'office'   => "SELECT office_id  AS pid FROM Office     WHERE user_id=? LIMIT 1",
        'admin'    => "SELECT admin_id   AS pid FROM Admin      WHERE user_id=? LIMIT 1",
      ];
      if (isset($roleMap[$user['role']])) {
        if ($q = $conn->prepare($roleMap[$user['role']])) {
          $uid = (int)$user['user_id'];
          $q->bind_param('i', $uid);
          $q->execute();
          if ($r = $q->get_result()) {
            $row = $r->fetch_assoc();
            if ($row && isset($row['pid'])) $profileId = $row['pid'];
          }
          $q->close();
        }
      }

      // Đăng nhập REAL user
      session_regenerate_id(true);
      $_SESSION['user'] = [
        'id'         => (int)$user['user_id'],
        'email'      => $user['email'],
        'username'   => $user['username'] ?? null,
        'name'       => $user['full_name'] ?? ($user['username'] ?? $user['email']),
        'role'       => $user['role'],
        'profile_id' => $profileId,
      ];

      header('Location: ' . BASE_URL . 'index.php?page=dashboard');
      exit;
    }
  }
}
?>



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

    <?php if (!empty($errors)): ?>
      <div class="alert alert--error">
        <?php foreach ($errors as $e): ?>
          <p><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

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
  </div>
</section>