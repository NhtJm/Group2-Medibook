<?php if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
$sp = $data['specialty'] ?? [];
$err = $data['errors'] ?? [];
$flash = $data['flash'] ?? null;

if (session_status() === PHP_SESSION_NONE)
    session_start();
if (empty($_SESSION['csrf']))
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
?>
<section class="admin-shell">
    <?php $active = 'admin_specialties';
    require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-main admin-specialty-edit">
        <?php
        $title = ($sp['specialty_id'] ?? 0) ? 'Edit Specialty' : 'Add Specialty';
        $searchAction = BASE_URL . 'index.php';
        $searchHidden = ['page' => 'admin_specialties'];
        require __DIR__ . '/partials/topbar.php';
        ?>

        <?php if ($flash): ?>
            <div class="alert <?= $flash['type'] === 'success' ? 'is-success' : 'is-error' ?>"><?= e($flash['msg']) ?></div>
        <?php endif; ?>

        <form class="panel form" method="post"
            action="<?= e(BASE_URL . 'index.php?page=admin_specialty_edit&sid=' . ((int) ($sp['specialty_id'] ?? 0))) ?>">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <div class="panel__head">Specialty info</div>
            <div class="form-grid">
                <label><span>Name *</span>
                    <input type="text" name="name" required value="<?= e($sp['name'] ?? '') ?>">
                    <?php if (!empty($err['name'])): ?><small class="err"><?= e($err['name']) ?></small><?php endif; ?>
                </label>

                <label><span>Slug *</span>
                    <input type="text" name="slug" required value="<?= e($sp['slug'] ?? '') ?>">
                    <?php if (!empty($err['slug'])): ?><small class="err"><?= e($err['slug']) ?></small><?php endif; ?>
                </label>

                <label class="span-2"><span>Blurb</span>
                    <input type="text" name="blurb" value="<?= e($sp['blurb'] ?? '') ?>">
                </label>

                <label class="span-2"><span>Description</span>
                    <textarea name="description" rows="6"><?= e($sp['description'] ?? '') ?></textarea>
                </label>

                <label class="span-2"><span>Image URL</span>
                    <input type="text" name="image_url" value="<?= e($sp['image_url'] ?? '') ?>">
                </label>

                <label><span>Sort order</span>
                    <input type="number" name="sort_order" value="<?= e((string) ($sp['sort_order'] ?? 0)) ?>">
                </label>

                <label><span>Featured</span>
                    <input type="checkbox" name="is_featured" <?= !empty($sp['is_featured']) ? 'checked' : '' ?>>
                </label>
            </div>
            <div class="form-actions form-actions--split">
                <a class="btn btn-ghost" href="<?= e(BASE_URL . 'index.php?page=admin_specialties') ?>">← Back</a>
                <button class="btn btn-primary" type="submit">Save changes</button>
            </div>
        </form>
    </div>
</section>

<style>
    .alert {
        margin: 12px 0;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: .9rem
    }

    .alert.is-success {
        background: #ecfdf5;
        color: #047857
    }

    .alert.is-error {
        background: #fef2f2;
        color: #b91c1c
    }

    .form {
        padding: 0
    }

    .form .panel__head {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        padding: 20px
    }

    .form-grid label {
        display: flex;
        flex-direction: column;
        gap: 8px
    }

    .form-grid .span-2 {
        grid-column: span 2
    }

    .form-grid input[type="text"],
    .form-grid input[type="number"],
    .form-grid textarea {
        border: 1px solid #d5dce7;
        border-radius: 8px;
        padding: 10px;
        font: inherit
    }

    .form-grid .err {
        color: #b91c1c
    }

    .btn {
        border: 1px solid #d5dce7;
        border-radius: 8px;
        padding: 6px 10px;
        background: #fff
    }

    .btn-primary {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb
    }

    .form-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        background: #fff;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }

    .form-actions--split {
        justify-content: space-between;
    }

    /* optional: keep buttons a good touch target */
    .form-actions .btn {
        min-width: 120px;
    }

    /* (Optional) make the action bar stick to the bottom of the viewport while scrolling */
    @supports (position: sticky) {
        .form-actions {
            position: sticky;
            bottom: 0;
            z-index: 5;
        }
    }
</style>