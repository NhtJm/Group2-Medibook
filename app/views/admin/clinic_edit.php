<?php if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
$office = $data['office'] ?? [];
$errors = $data['errors'] ?? [];
$flash = $data['flash'] ?? null;

if (session_status() === PHP_SESSION_NONE)
    session_start();
if (empty($_SESSION['csrf']))
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
?>
<section class="admin-shell">
    <?php $active = 'clinics';
    require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-main admin-clinic-edit">
        <?php
        $title = 'Edit Clinic';
        $searchAction = BASE_URL . 'index.php';
        $searchHidden = ['page' => 'admin_clinics']; // keeps topbar layout consistent
        require __DIR__ . '/partials/topbar.php';
        ?>

        <?php if ($flash): ?>
            <div class="alert <?= $flash['type'] === 'success' ? 'is-success' : 'is-error' ?>" role="status">
                <?= e($flash['msg']) ?>
            </div>
        <?php endif; ?>

        <form class="panel form" method="post"
            action="<?= e(BASE_URL . 'index.php?page=clinic_edit&clinic=' . (int) $office['office_id']) ?>"
            enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

            <div class="panel__head">Clinic info</div>

            <div class="form-grid">
                <label>
                    <span>Name *</span>
                    <input type="text" name="name" required value="<?= e($office['name'] ?? '') ?>">
                    <?php if (!empty($errors['name'])): ?><small
                            class="err"><?= e($errors['name']) ?></small><?php endif; ?>
                </label>

                <label>
                    <span>Slug *</span>
                    <input type="text" name="slug" required value="<?= e($office['slug'] ?? '') ?>" id="slug">
                </label>

                <label class="span-2">
                    <span>Address</span>
                    <textarea name="address" rows="2"><?= e($office['address'] ?? '') ?></textarea>
                </label>

                <label class="span-2">
                    <span>Google Map (embed or link)</span>
                    <textarea name="googleMap" rows="2"><?= e($office['googleMap'] ?? '') ?></textarea>
                </label>

                <label>
                    <span>Website</span>
                    <input type="text" name="website" value="<?= e($office['website'] ?? '') ?>">
                </label>

                <label>
                    <span>Rating (0–5)</span>
                    <input type="number" step="0.1" min="0" max="5" name="rating"
                        value="<?= e((string) ($office['rating'] ?? '0.0')) ?>">
                    <?php if (!empty($errors['rating'])): ?><small
                            class="err"><?= e($errors['rating']) ?></small><?php endif; ?>
                </label>

                <div class="span-2 logo-field">
                    <label>
                        <span>Logo (URL)</span>
                        <input type="text" name="logo" value="<?= e($office['logo'] ?? '') ?>"
                            placeholder="/public/uploads/clinics/xxx.png or https://...">
                    </label>

                    <?php if (!empty($office['logo'])): ?>
                        <div class="logo-preview">
                            <img src="<?= e($office['logo']) ?>" alt="Current logo" onerror="this.remove()">
                        </div>
                    <?php endif; ?>
                </div>
                <?php
                $ALL = $data['all_specialties'] ?? [];
                $SEL = $data['selected_specialty_ids'] ?? [];
                ?>
                <label class="span-2">
                    <span>Specialties</span>
                    <div class="sp-picker">
                        <div class="sp-toolbar">
                            <input type="text" class="sp-search" id="spSearch" placeholder="Search specialties…">
                            <div class="sp-count"><span id="spCount"><?= count($SEL) ?></span> selected</div>
                        </div>

                        <div class="sp-grid" id="spGrid">
                            <?php foreach ($ALL as $s):
                                $sid = (int) $s['specialty_id'];
                                $checked = in_array($sid, $SEL, true); ?>
                                <label class="sp-chip" data-name="<?= e(mb_strtolower($s['name'])) ?>"
                                    data-slug="<?= e(mb_strtolower($s['slug'])) ?>">
                                    <input type="checkbox" name="specialties[]" value="<?= $sid ?>" <?= $checked ? 'checked' : '' ?>>
                                    <span class="sp-chip__pill">
                                        <span class="sp-chip__name"><?= e($s['name']) ?></span>
                                        <small class="sp-chip__slug"><?= e($s['slug']) ?></small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </label>
                <label class="span-2">
                    <span>Description</span>
                    <textarea name="description" rows="6"><?= e($office['description'] ?? '') ?></textarea>
                </label>

            </div>

            <div class="form-actions">
                <div class="fa-left">
                    <a class="btn btn-ghost" href="<?= e(BASE_URL . 'index.php?page=admin_clinics') ?>">← Back</a>
                </div>

                <div class="fa-right">
                    <a class="btn" href="<?= e(BASE_URL . 'index.php?page=clinic&clinic=' . $office['office_id']) ?>"
                        target="_blank" rel="noopener">
                        View public page
                    </a>
                    <button class="btn btn-primary" type="submit">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
    /* ---- Specialties picker (scoped to clinic edit) ---- */
    .admin-clinic-edit .sp-picker {
        border: 1px solid #e6ebf5;
        background: #fff;
        border-radius: 12px;
        padding: 10px 10px 12px;
    }

    .admin-clinic-edit .sp-toolbar {
        display: flex;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .admin-clinic-edit .sp-search {
        flex: 1;
        border: 1px solid #d5dce7;
        border-radius: 8px;
        padding: 8px 10px;
        font: inherit;
    }

    .admin-clinic-edit .sp-count {
        font-size: .9rem;
        color: #55627a
    }

    .admin-clinic-edit .sp-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    @media (max-width:900px) {
        .admin-clinic-edit .sp-grid {
            grid-template-columns: 1fr
        }
    }

    .admin-clinic-edit .sp-chip {
        display: block
    }

    .admin-clinic-edit .sp-chip input {
        position: absolute;
        opacity: 0;
        pointer-events: none
    }

    .admin-clinic-edit .sp-chip__pill {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border: 1px solid #dbe2ea;
        border-radius: 999px;
        background: #f8fafc;
        color: #1f315c;
        user-select: none;
        cursor: pointer;
    }

    .admin-clinic-edit .sp-chip input:checked+.sp-chip__pill {
        background: #eaf6ee;
        border-color: #c9ead3;
        color: #1c6a36;
    }

    .admin-clinic-edit .sp-chip__name {
        font-weight: 600
    }

    .admin-clinic-edit .sp-chip__slug {
        opacity: .7;
        background: #fff;
        border: 1px solid #e6ebf5;
        border-radius: 6px;
        padding: 2px 6px
    }

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

    .logo-field {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        align-items: start
    }

    .logo-preview {
        grid-column: span 2
    }

    .logo-preview img {
        max-height: 64px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 6px;
        background: #fff
    }

    .form-actions {
        display: flex;
        gap: 10px;
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb
    }
</style>

<script>
    // Small helper: auto-slugify when user changes name if slug is empty initially
    (function () {
        const nameInput = document.querySelector('input[name="name"]');
        const slugInput = document.querySelector('input[name="slug"]');
        if (!nameInput || !slugInput) return;

        const initialSlug = slugInput.value.trim();
        let userTouchedSlug = initialSlug.length > 0;

        slugInput.addEventListener('input', () => { userTouchedSlug = true; });

        function slugify(s) {
            return s.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').replace(/-+/g, '-') || 'clinic';
        }

        nameInput.addEventListener('input', () => {
            if (userTouchedSlug) return;
            slugInput.value = slugify(nameInput.value || '');
        });
    })();
</script>
<script>
    (function () {
        const grid = document.getElementById('spGrid');
        const q = document.getElementById('spSearch');
        const cnt = document.getElementById('spCount');
        if (!grid) return;

        function updateCount() {
            cnt.textContent = grid.querySelectorAll('input[type="checkbox"]:checked').length;
        }
        grid.addEventListener('change', updateCount);
        updateCount();

        if (q) {
            q.addEventListener('input', () => {
                const term = q.value.trim().toLowerCase();
                grid.querySelectorAll('.sp-chip').forEach(el => {
                    const n = el.dataset.name || '';
                    const s = el.dataset.slug || '';
                    el.style.display = (!term || n.includes(term) || s.includes(term)) ? '' : 'none';
                });
            });
        }
    })();
</script>