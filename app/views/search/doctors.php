<?php
if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
$clinic = $data['clinic'] ?? null;
$doctors = $data['doctors'] ?? [];
$rating = isset($clinic['rating']) ? (float) $clinic['rating'] : 0.0;
$rating = max(0.0, min(5.0, $rating));
$ratingPct = $rating > 0 ? ($rating / 5) * 100 : 0;
?>

<?php if (!$clinic): ?>
    <section class="wall">
        <div class="card">
            <h1 class="title">Clinic not found</h1>
        </div>
    </section>
<?php else: ?>


    <section class="clv-hero">
        <div class="clv-wrap">
            <div class="clv-card">
                <div class="clv-card__logo" aria-hidden="true">
                    <?php if (!empty($clinic['logo'])): ?>
                        <img src="<?= e($clinic['logo']) ?>" alt="<?= e($clinic['name']) ?>">
                    <?php else: ?>
                        <span class="badge"><?= e($clinic['code'] ?? $clinic['badge'] ?? 'CL') ?></span>
                    <?php endif; ?>
                </div>

                <div class="clv-card__meta">
                    <h1 class="clv-card__name">
                        <?= e($clinic['name']) ?>
                        <?php if ($rating > 0): ?>
                            <span class="rating" aria-label="Rating <?= number_format($rating, 1) ?>/5">
                                <span class="rstars">
                                    <span class="rstars-bg">★★★★★</span>
                                    <span class="rstars-fg" style="width:<?= $ratingPct ?>%">★★★★★</span>
                                </span>
                                <span class="rating-num"><?= number_format($rating, 1) ?></span>
                            </span>
                        <?php endif; ?>
                    </h1>

                    <?php if (!empty($clinic['address'])): ?>
                        <p class="clv-card__addr"><?= e($clinic['address']) ?></p>
                    <?php endif; ?>

                    <?php
                    $open = !empty($clinic['is_open']);
                    $hours = $clinic['hours_label'] ?? null;
                    ?>
                    <p class="clv-card__status <?= $open ? 'is-open' : 'is-closed' ?>">
                        <span class="dot"></span><?= $open ? 'Open' : 'Closed' ?><?= $hours ? ' · ' . e($hours) : '' ?>
                    </p>
                </div>

                <div class="clv-card__cta">
                    <?php if (!empty($clinic['phone'])):
                        $raw = trim((string) $clinic['phone']);
                        $tel = preg_replace('/[^\d+]/', '', $raw);
                        $disp = $raw;
                        ?>
                        <a class="btn-call" href="tel:<?= e($tel) ?>" aria-label="Call <?= e($disp) ?>">
                            <span class="btn-call__label">Call</span>
                            <svg class="ico ico-right" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M22 16.92v2a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.92 4.18 2 2 0 0 1 4.92 2h2a2 2 0 0 1 2 1.72c.12.9.32 1.78.59 2.63a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.45-1.45a2 2 0 0 1 2.11-.45c.85.27 1.73.47 2.63.59A2 2 0 0 1 22 16.92z" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- White content area -->
    <section class="clv-white">
        <div class="dv-wrap dv-grid">
            <!-- Left: clinic info card -->
            <aside class="card dv-clinic">
                <div class="dv-clinic-head"><strong>Info</strong></div>
                <div class="dv-clinic-name">
                    <span><?= e($clinic['name']) ?></span><span class="dv-verified" title="Đã xác minh">✓</span>
                </div>
                <?php if (!empty($clinic['address'])): ?>
                    <p class="dv-clinic-addr"><?= e($clinic['address']) ?></p>
                <?php endif; ?>
                <?php if (!empty($clinic['phone'])): ?>
                    <p class="dv-clinic-addr"><a
                            href="tel:<?= e(preg_replace('/\s+/', '', $clinic['phone'])) ?>"><?= e($clinic['phone']) ?></a></p>
                <?php endif; ?>
            </aside>

            <!-- Right: choose doctor panel -->
            <section class="card dv-panel">
                <h2 class="dv-title">Select your doctor</h2>

                <!-- Search & filters (pill style) -->
                <div class="dv-controls">
                    <div class="dv-search">
                        <input id="dv-q" type="text" placeholder="Find your doc" aria-label="Tìm bác sĩ">
                        <button id="dv-q-btn" aria-label="Tìm">
                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M21 21l-4.3-4.3M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" stroke="#2b456d"
                                    stroke-width="2" fill="none" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="dv-filters">
                        <select id="flt-degree">
                            <option value="">Degrees</option>
                            <?php $degrees = array_values(array_unique(array_filter(array_map(fn($d) => $d['degree'] ?? '', $doctors))));
                            foreach ($degrees as $deg)
                                echo '<option value="' . e($deg) . '">' . e($deg) . '</option>'; ?>
                        </select>
                        <select id="flt-spec">
                            <option value="">Specialties</option>
                            <?php $specs = array_values(array_unique(array_filter(array_map(fn($d) => $d['spec'] ?? '', $doctors))));
                            foreach ($specs as $sp)
                                echo '<option value="' . e($sp) . '">' . e($sp) . '</option>'; ?>
                        </select>
                    </div>
                </div>

                <!-- Doctors list -->
                <?php if (!$doctors): ?>
                    <div class="dv-empty">No doctors are listed for this clinic yet.</div>
                <?php else: ?>
                    <div class="dv-scroll" id="dv-list">
                        <?php foreach ($doctors as $d):
                            $name = $d['name'] ?? '';
                            $spec = $d['spec'] ?? '';
                            $degree = $d['degree'] ?? '';
                            $gender = $d['gender'] ?? '';
                            $fee = $d['fee'] ?? '500.000đ';
                            $next = $d['next'] ?? '—';
                            ?>
                            <article class="dv-doc" data-name="<?= e(mb_strtolower($name)) ?>" data-spec="<?= e($spec) ?>"
                                data-degree="<?= e($degree) ?>" data-gender="<?= e($gender) ?>">
                                <div class="dv-doc-left">
                                    <div class="dv-doc-avatar"><?= e($d['init'] ?? 'Dr') ?></div>
                                </div>

                                <div class="dv-doc-main">
                                    <h3 class="dv-doc-name">
                                        <a
                                            href="<?= BASE_URL ?>index.php?page=doctor&clinic=<?= (int) $clinic['office_id'] ?>&doc=<?= (int) $d['id'] ?>">
                                            <?= e($name) ?>             <?= $degree ? ' - ' . e($degree) : '' ?>
                                        </a>
                                    </h3>

                                    <?php if ($spec): ?>
                                        <div class="kv-line"><strong>Specialty:</strong> <?= e($spec) ?></div>
                                    <?php endif; ?>

                                    <div class="kv-line"><strong>Available:</strong> <?= e($next) ?></div>

                                    <?php if ($fee): ?>
                                        <div class="kv-line"><strong>Fee:</strong> <?= e($fee) ?> <em class="muted">(Pay directly at the office)</em></div>
                                    <?php endif; ?>
                                </div>

                                <div class="dv-doc-cta">
                                    <a class="btn-make"
                                        href="<?= BASE_URL ?>index.php?page=doctor&clinic=<?= (int) $clinic['office_id'] ?>&doc=<?= (int) $d['id'] ?>">
                                        Make appointments
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <a class="dv-backlink" href="<?= BASE_URL ?>index.php?page=clinic&id=<?= (int) $clinic['office_id'] ?>">Back</a>
            </section>
        </div>
    </section>

<?php endif; ?>

<script>
    (function () {
        const q = document.getElementById('dv-q');
        const qBtn = document.getElementById('dv-q-btn');
        const list = document.getElementById('dv-list');
        const degree = document.getElementById('flt-degree');
        const spec = document.getElementById('flt-spec');
        const gender = document.getElementById('flt-gender');
        if (!list) return;

        const norm = s => (s || '').toLowerCase().trim();
        function apply() {
            const qv = norm(q?.value);
            const dv = norm(degree?.value);
            const sv = norm(spec?.value);
            const gv = norm(gender?.value);
            list.querySelectorAll('.dv-doc').forEach(card => {
                const show =
                    (!qv || (card.dataset.name || '').includes(qv)) &&
                    (!dv || norm(card.dataset.degree) === dv) &&
                    (!sv || norm(card.dataset.spec) === sv) &&
                    (!gv || norm(card.dataset.gender) === gv);
                card.style.display = show ? '' : 'none';
            });
        }
        [q, degree, spec, gender].forEach(el => el && el.addEventListener('input', apply));
        qBtn && qBtn.addEventListener('click', apply);
    })();
</script>