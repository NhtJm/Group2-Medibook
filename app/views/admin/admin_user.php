<?php if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
$user = $data['user'] ?? null;
$all = $data['all'] ?? [];
$auth = $data['auth_label'] ?? '—';
$flash = $data['flash'] ?? null;

if (session_status() === PHP_SESSION_NONE)
    session_start();
if (empty($_SESSION['csrf']))
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
?>
<link rel="stylesheet" href="<?= e(BASE_URL) ?>public/assets/css/admin_user.css">

<section class="admin-shell">
    <?php $active = 'admin_users';
    require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-main admin-user-view">
        <?php
        $title = $user ? ('User #' . $user['user_id']) : 'User';
        $searchAction = BASE_URL . 'index.php';
        $searchHidden = ['page' => 'admin_users']; // keep topbar consistent
        require __DIR__ . '/partials/topbar.php';
        ?>

        <section class="panel">
            <?php if ($flash): ?>
                <div class="flash <?= $flash['type'] === 'success' ? 'ok' : 'err' ?>"><?= e($flash['msg']) ?></div>
            <?php endif; ?>

            <?php if (!$user): ?>
                <div class="panel__head">User not found</div>
            <?php else: ?>

                <!-- Header -->
                <div class="u-header">
                    <div>
                        <?php if (!empty($user['picture_url'])): ?>
                            <img class="u-avatar" src="<?= e($user['picture_url']) ?>" alt="" onerror="this.remove()">
                        <?php else: ?>
                            <?php $initials = mb_substr(($user['full_name'] ?? ($user['username'] ?? '?')), 0, 2); ?>
                            <div class="u-avatar-fallback"><?= e($initials) ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="u-title"><?= e($user['full_name'] ?? $user['username'] ?? ('#' . $user['user_id'])) ?>
                        </div>
                        <div class="chips">
                            <?php if (!empty($user['email'])): ?><span class="chip"><a
                                        href="mailto:<?= e($user['email']) ?>"><?= e($user['email']) ?></a></span><?php endif; ?>
                            <?php if (!empty($user['username'])): ?><span class="chip">user:
                                    <?= e($user['username']) ?></span><?php endif; ?>
                            <?php if (!empty($user['role'])): ?><span
                                    class="chip role-<?= e($user['role']) ?>"><?= e(ucfirst($user['role'])) ?></span><?php endif; ?>
                            <span class="chip"><?= e($auth) ?></span>
                        </div>
                    </div>
                </div>

                <!-- EDIT FORM -->
                <form class="u-form" method="post"
                    action="<?= e(BASE_URL . 'index.php?page=admin_user&uid=' . (int) $user['user_id']) ?>">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

                    <!-- Account -->
                    <div class="u-section">
                        <div class="u-section__head">Account</div>
                        <div class="form-grid">
                            <label>
                                <span>Email *</span>
                                <input class="input" type="email" name="email" required
                                    value="<?= e($user['email'] ?? '') ?>">
                            </label>
                            <label>
                                <span>Username *</span>
                                <input class="input" type="text" name="username" required
                                    value="<?= e($user['username'] ?? '') ?>">
                            </label>
                            <label>
                                <span>Full name</span>
                                <input class="input" type="text" name="full_name"
                                    value="<?= e($user['full_name'] ?? '') ?>">
                            </label>
                            <?php
                            $role = $user['role'] ?? 'patient';
                            $canChangeRole = in_array($role, ['admin', 'webstaff'], true);
                            ?>
                            <label>
                                <span>Role *</span>

                                <?php if ($canChangeRole): ?>
                                    <select class="select" name="role" required>
                                        <?php foreach (['admin', 'webstaff'] as $r): ?>
                                            <option value="<?= e($r) ?>" <?= $role === $r ? 'selected' : '' ?>><?= e(ucfirst($r)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="help">Only Admin/Webstaff can be reassigned here.</div>
                                <?php else: ?>
                                    <input class="input" type="text" value="<?= e(ucfirst($role)) ?>" disabled>
                                    <input type="hidden" name="role" value="<?= e($role) ?>">
                                    <div class="help">Role is fixed for Patient/Office.</div>
                                <?php endif; ?>
                            </label>
                        </div>
                    </div>

                    <!-- Authentication -->
                    <!-- Authentication -->
                    <div class="u-section">
                        <div class="u-section__head">Authentication</div>
                        <div class="form-grid">
                            <label class="span-2">
                                <span>Set new password</span>
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <input class="input" type="password" id="newPwd" name="new_password"
                                        autocomplete="new-password" placeholder="Leave blank to keep current">
                                    <button type="button" class="btn" id="toggleNewPwd" title="Show/Hide">👁</button>
                                    <button type="button" class="btn" id="genPwd"
                                        title="Generate strong password">Generate</button>
                                </div>
                                <?php if (!empty($user['password_hash'])): ?>
                                    <div class="help">
                                        Current password: <span id="pwd-mask">••••••••</span>
                                        <button type="button" class="btn btn-ghost" id="reveal-hash">Reveal hash</button>
                                        <code id="pwd-hash" style="display:none"><?= e($user['password_hash']) ?></code>
                                    </div>
                                <?php else: ?>
                                    <div class="help">This user has no password hash stored (OAuth-only or not set).</div>
                                <?php endif; ?>
                            </label>

                            <label>
                                <span>OAuth provider</span>
                                <input class="input" type="text" name="oauth_provider"
                                    value="<?= e($user['oauth_provider'] ?? '') ?>" placeholder="google, facebook, ...">
                            </label>
                            <label>
                                <span>OAuth sub</span>
                                <input class="input" type="text" name="oauth_sub"
                                    value="<?= e($user['oauth_sub'] ?? '') ?>">
                            </label>
                        </div>
                    </div>

                    <!-- Profile -->
                    <div class="u-section">
                        <div class="u-section__head">Profile</div>
                        <div class="form-grid">
                            <label class="span-2">
                                <span>Avatar URL (picture_url)</span>
                                <input class="input" type="url" name="picture_url"
                                    value="<?= e($user['picture_url'] ?? '') ?>" placeholder="https://...">
                                <div class="help">Paste a URL and save to update the avatar above.</div>
                            </label>
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="u-section">
                        <div class="u-section__head">Metadata</div>
                        <div class="form-grid">
                            <label>
                                <span>User ID</span>
                                <input class="input" type="text" value="<?= e($user['user_id']) ?>" disabled>
                            </label>
                            <label>
                                <span>Created at</span>
                                <?php
                                $created = $user['created_at'] ?? '';
                                $created_val = $created ? date('Y-m-d\TH:i', strtotime($created)) : '';
                                ?>
                                <input class="input" type="datetime-local" name="created_at" value="<?= e($created_val) ?>">
                                <div class="help">Optional: adjust if needed.</div>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="fa-left">
                            <a class="btn btn-ghost" href="<?= e(BASE_URL . 'index.php?page=admin_users') ?>">← Back</a>
                        </div>
                        <div class="fa-right">
                            <button class="btn btn-primary" type="submit">Save changes</button>
                        </div>
                    </div>
                </form>



            <?php endif; ?>
        </section>
    </div>
</section>
<script>
    (() => {
        const newPwd = document.getElementById('newPwd');
        const eye = document.getElementById('toggleNewPwd');
        const gen = document.getElementById('genPwd');
        const reveal = document.getElementById('reveal-hash');
        const mask = document.getElementById('pwd-mask');
        const hashEl = document.getElementById('pwd-hash');

        if (eye && newPwd) {
            eye.addEventListener('click', () => {
                newPwd.type = (newPwd.type === 'password') ? 'text' : 'password';
                newPwd.focus();
                newPwd.setSelectionRange(newPwd.value.length, newPwd.value.length);
            });
        }

        if (gen && newPwd) {
            gen.addEventListener('click', () => {
                const s = randomStrong(12);
                newPwd.type = 'text';
                newPwd.value = s;
                newPwd.focus();
                newPwd.setSelectionRange(0, s.length);
            });
        }

        if (reveal && mask && hashEl) {
            reveal.addEventListener('click', () => {
                const open = (hashEl.style.display === 'inline');
                hashEl.style.display = open ? 'none' : 'inline';
                mask.style.display = open ? 'inline' : 'none';
                reveal.textContent = open ? 'Reveal hash' : 'Hide hash';
            });
        }

        function randomStrong(len) {
            const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
            const out = [];
            const arr = new Uint32Array(len);
            if (window.crypto?.getRandomValues) crypto.getRandomValues(arr);
            for (let i = 0; i < len; i++) out.push(alphabet[arr[i] % alphabet.length]);
            return out.join('');
        }
    })();
</script>