<?php if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
$office = $data['office'] ?? null;
$rows = $data['doctors'] ?? [];
$q = $data['q'] ?? '';
?>
<section class="admin-shell">
    <?php $active = 'admin_doctors';
    require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-main admin-doctors">
        <?php
        $title = $office ? ('Doctors · ' . $office['name']) : 'Doctors';
        $searchAction = BASE_URL . 'index.php';
        $searchHidden = ['page' => 'admin_doctors_office', 'office' => $office['office_id'] ?? 0];
        require __DIR__ . '/partials/topbar.php';
        ?>

        <?php if (!$office): ?>
            <section class="panel">
                <div class="panel__head">Office not found</div>
            </section>
        <?php else: ?>
            <section class="panel">
                <div class="panel__head" style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <div>
                        <strong><?= e($office['name']) ?></strong>
                        <div style="color:#6b7280;font-size:.92rem;"><?= e($office['address'] ?? '') ?></div>
                    </div>
                    <a class="btn btn-ghost" href="<?= e(BASE_URL . 'index.php?page=admin_doctors') ?>">← All offices</a>
                </div>

                <div id="doc-list" class="table-scroller">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width:56px;">Photo</th>
                                <th>Name</th>
                                <th>Specialty</th>
                                <th>Email</th>
                                <th>Degree</th>
                                <th>Graduate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$rows): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;color:#6b7280;">No doctors found for this office.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rows as $d): ?>
                                    <?php
                                    $docName = $d['name'] ?? $d['doctor_name'] ?? $d['full_name'] ?? ('#' . $d['doctor_id']);
                                    $editHref = BASE_URL . 'index.php?page=admin_doctor_edit&doctor=' . (int) $d['doctor_id'];
                                    ?>
                                    <tr class="doc-row" data-href="<?= e($editHref) ?>">
                                        <td>
                                            <?php if (!empty($d['photo'])): ?>
                                                <img src="<?= e($d['photo']) ?>" alt=""
                                                    style="width:40px;height:40px;border-radius:50%;object-fit:cover"
                                                    onerror="this.remove()">
                                            <?php endif; ?>
                                        </td>
                                        <td><a href="<?= e($editHref) ?>"><?= e($docName) ?></a></td>
                                        <td>
                                            <?php if (!empty($d['specialty_name'])): ?>
                                                <span class="chip"><?= e($d['specialty_name']) ?></span>
                                            <?php else: ?>—<?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($d['email'])): ?>
                                                <a href="mailto:<?= e($d['email']) ?>"><?= e($d['email']) ?></a>
                                            <?php else: ?>—<?php endif; ?>
                                        </td>
                                        <td><?= e($d['degree'] ?? '') ?></td>
                                        <td><?= e($d['graduate'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>
<script>
document.addEventListener('click', (e) => {
  const row = e.target.closest('.doc-row');
  if (!row) return;
  if (e.target.tagName === 'A') return; // don't override normal links
  const href = row.dataset.href;
  if (href) window.location.href = href;
});
</script>