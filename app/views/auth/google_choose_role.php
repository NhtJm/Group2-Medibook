<?php
// /app/views/auth/google_choose_role.php
if (session_status() === PHP_SESSION_NONE) session_start();
$err   = $_GET['err'] ?? '';
$role0 = $_SESSION['oauth_role'] ?? '';
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<section class="wall">
  <div class="card">
    <div class="card__logo">
      <img src="<?= IMAGE_PATH ?>Logo.svg" alt="MEDIBOOK">
    </div>

    <h1 class="title">Sign in with Google</h1>
    <p class="subtitle">Choose your role to continue</p>

    <?php if ($err === 'role'): ?>
      <div style="background:#fff3f3;color:#b00020;border:1px solid #ffd7d7;padding:10px 12px;border-radius:8px;margin-bottom:12px;">
        Please select a role to continue.
      </div>
    <?php elseif ($err === 'staff_forbidden'): ?>
      <div style="background:#fff8e6;color:#7a4b00;border:1px solid #ffe2a8;padding:10px 12px;border-radius:8px;margin-bottom:12px;">
        Web Staff cannot self-register. Please contact an administrator.
      </div>
    <?php endif; ?>

    <form method="post" action="<?= rtrim(BASE_URL, '/') ?>/index.php?page=google_begin" class="form">
      <div class="rolebox">
        <label class="roleitem">
          <input type="radio" name="role" value="patient" <?= $role0==='patient'?'checked':'' ?> required>
          <div class="roleitem__body">
            <div class="roleitem__title">Patient</div>
            <div class="roleitem__desc">Book appointments and manage your personal profile.</div>
          </div>
        </label>

        <label class="roleitem">
          <input type="radio" name="role" value="office" <?= $role0==='office'?'checked':'' ?> required>
          <div class="roleitem__body">
            <div class="roleitem__title">Doctor’s Office</div>
            <div class="roleitem__desc">Manage clinic info, doctors, and schedules.</div>
          </div>
        </label>

        <!-- Web Staff (mặc định disabled để đúng spec: không tự đăng ký) -->
        <label class="roleitem is-disabled" title="Web Staff accounts are issued by Admin">
          <input type="radio" name="role" value="webstaff" disabled>
          <div class="roleitem__body">
            <div class="roleitem__title">Web Staff</div>
            <div class="roleitem__desc">Internal tools for managing platform operations.</div>
          </div>
        </label>
      </div>

      <button type="submit" class="btn btn--dark btn--xl google-btn">
        <img src="<?= IMAGE_PATH ?>google.svg" alt="" class="google-icon">
        <span>Continue with Google</span>
      </button>

      <div style="text-align:center;margin-top:10px;">
        <a href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=login" class="link">Back to Login</a>
      </div>
    </form>
  </div>

  <style>
    /* ===== Inline styles to match current auth look ===== */
    :root{
      --ink:#0d1726; --muted:#6b7a90; --border:#e6e8eb; --brand:#2563eb;
      --bg:#fff; --radius:18px; --shadow:0 10px 26px rgba(13,23,38,.10);
      --card-width: 520px; --card-height:auto;
    }

    .rolebox{ display:grid; gap:10px; margin:4px 0 8px; }
    .roleitem{
      display:flex; gap:12px; align-items:flex-start; padding:12px 12px;
      border:1px solid var(--border); border-radius:16px; background:#fff;
      cursor:pointer; transition:box-shadow .18s ease, border-color .18s ease;
    }
    .roleitem input{ margin-top:3px; transform:scale(1.1); }
    .roleitem__body{ display:grid; gap:4px; }
    .roleitem__title{ font-weight:700; color:var(--ink); }
    .roleitem__desc{ color:var(--muted); font-size:14px; }
    .roleitem:hover{ border-color:#bcd0ff; box-shadow:0 6px 18px rgba(37,99,235,.08); }
    .roleitem.is-disabled{ opacity:.55; cursor:not-allowed; filter:grayscale(15%); }

    .google-btn{
      display:flex; align-items:center; justify-content:center; gap:10px;
      width:100%; height:46px; border-radius:999px; margin-top:6px;
    }
    .google-icon{ width:18px; height:18px; display:block; }

    /* Make the card breathe a bit more on tall screens */
    @media (min-height:820px){ .card{ padding:32px; } }
  </style>
</section>
