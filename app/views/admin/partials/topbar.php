<?php if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
$title = $title ?? '';
$search = $search ?? '';
$searchAction = $searchAction ?? null;      // e.g. BASE_URL.'index.php'
$searchHidden = $searchHidden ?? [];        // e.g. ['page'=>'admin_clinics']
?>
<header class="admin-topbar">
    <div class="admin-topbar__left">
        <span class="admin-topbar__page"><?= e($title) ?></span>
    </div>

    <div class="admin-topbar__center">
        <form class="ac-search <?= !empty($fixedSearch) ? 'is-fixed' : '' ?>" method="get"
            action="<?= e($searchAction ?? (BASE_URL . 'index.php')) ?>">
            <?php if (!empty($searchHidden))
                foreach ($searchHidden as $k => $v): ?>
                    <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                <?php endforeach; ?>
            <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="Search…">
            <button type="submit" title="Search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18">
                    <circle cx="11" cy="11" r="7" />
                    <path d="M21 21l-4.3-4.3" />
                </svg>
            </button>
        </form>
    </div>

    <div class="admin-topbar__right">
        <button class="topbar-icon-btn" title="Notifications">🔔</button>
    </div>
</header>