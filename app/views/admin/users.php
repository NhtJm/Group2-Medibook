<?php if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
$rows = $data['users'] ?? [];
$search = $data['search'] ?? '';
$role = $data['role'] ?? 'all';

if (session_status() === PHP_SESSION_NONE)
    session_start();
?>
<section class="admin-shell">
    <?php $active = 'admin_users';
    require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-main admin-users">
        <?php
        $title = 'Users';
        $searchAction = BASE_URL . 'index.php';
        $searchHidden = ['page' => 'admin_users', 'role' => $role];
        require __DIR__ . '/partials/topbar.php';
        ?>

        <section class="panel">
            <div class="panel__head users-head">
                <div class="u-count"><?= count($rows) ?> shown</div>
                <div class="u-filters">
                    <label for="roleFilter" class="visually-hidden">Role</label>
                    <select id="roleFilter" class="ac-select">
                        <option value="all" <?= $role === 'all' ? 'selected' : '' ?>>All roles</option>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="webstaff" <?= $role === 'webstaff' ? 'selected' : '' ?>>Webstaff</option>
                        <option value="office" <?= $role === 'office' ? 'selected' : '' ?>>Office</option>
                        <option value="patient" <?= $role === 'patient' ? 'selected' : '' ?>>Patient</option>
                    </select>
                </div>
            </div>

            <div class="table-scroller">
                <table class="admin-table users-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">ID</th>
                            <th style="width:56px;">Avatar</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th style="width:120px;">Role</th>
                            <th style="width:140px;">Auth</th>
                            <th style="width:170px;">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $u): ?>
                            <tr>
                                <td>#<?= (int) $u['user_id'] ?></td>
                                <td>
                                    <?php $viewHref = BASE_URL . 'index.php?page=admin_user&uid=' . (int) $u['user_id']; ?>
                                    <?php if (!empty($u['picture_url'])): ?>
                                        <a href="<?= e($viewHref) ?>" title="View user">
                                            <img class="u-avatar" src="<?= e($u['picture_url']) ?>" alt=""
                                                onerror="this.remove()">
                                        </a>
                                    <?php else: ?>
                                        <?php $initials = mb_substr(($u['full_name'] ?? ($u['username'] ?? '?')), 0, 2); ?>
                                        <a href="<?= e($viewHref) ?>" title="View user" class="u-avatar u-avatar-fallback"
                                            style="display:inline-grid;place-items:center;width:36px;height:36px;border-radius:50%;background:#f3f4f6;font-weight:600;color:#111;text-decoration:none;">
                                            <?= e($initials) ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($u['full_name'] ?? '') ?></td>
                                <td><a href="mailto:<?= e($u['email']) ?>"><?= e($u['email']) ?></a></td>
                                <td><?= e($u['username'] ?? '') ?></td>
                                <td><span class="chip role-<?= e($u['role']) ?>"><?= e(ucfirst($u['role'])) ?></span></td>
                                <td>
                                    <?php
                                    $auth = $u['oauth_provider'] ? ('OAuth: ' . $u['oauth_provider']) : ($u['has_password'] ? 'Password' : '—');
                                    ?>
                                    <span class="chip"><?= e($auth) ?></span>
                                </td>
                                <td><?= e($u['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</section>

<script>
    (() => {
        const BASE = '<?= e(BASE_URL) ?>';
        const roleSel = document.getElementById('roleFilter');
        const form = document.querySelector('.admin-topbar .ac-search');
        const input = form ? form.querySelector('input[name="q"]') : null;

        function reloadWithFilters() {
            const usp = new URLSearchParams();
            usp.set('page', 'admin_users');
            if (input && input.value.trim()) usp.set('q', input.value.trim());
            if (roleSel && roleSel.value !== 'all') usp.set('role', roleSel.value);

            try { sessionStorage.setItem('refocusSearch', '1'); } catch { }
            window.location.href = BASE + 'index.php?' + usp.toString();
        }
        if (roleSel) roleSel.addEventListener('change', reloadWithFilters);
        if (form && input) {
            form.addEventListener('submit', e => { e.preventDefault(); reloadWithFilters(); });
            form.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); reloadWithFilters(); } });
        }
    })();
</script>
<script>
    // NEW: put the caret back into the search input after a search
    (function () {
        try {
            if (sessionStorage.getItem('refocusSearch') === '1') {
                sessionStorage.removeItem('refocusSearch');
                const input = document.querySelector('.admin-topbar .ac-search input[name="q"]');
                if (input) {
                    input.focus();
                    const len = input.value.length;
                    if (input.setSelectionRange) input.setSelectionRange(len, len);
                }
            }
        } catch { }
    })();
</script>