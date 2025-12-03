<?php // app/views/user/office_dashboard.php
if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
if (session_status() === PHP_SESSION_NONE)
    session_start();
$officeId = (int) ($_SESSION['user']['profile_id'] ?? 0);
$stats = $data['stats'] ?? ['doctors' => 0, 'today' => 0, 'avg_rating' => null, 'avg_wait' => null];
$docs = $data['doctors'] ?? [];
$appts = $data['appointments'] ?? [];
$spec = $data['specMap'] ?? [];
$clinicUrl = $data['clinic_url'] ?? (rtrim(BASE_URL, '/') . '/index.php?page=clinics');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Clinic Dashboard • MediBook</title>

</head>

<body class="office-body">

    <main class="office-main" role="main">
        <div class="office-main__inner">

            <!-- Header / hero -->
            <section class="office-hero">
                <div class="office-hero__title">
                    <h1>Clinic Dashboard</h1>
                    <div class="office-heading__bar" aria-hidden="true"></div>
                    <p class="office-hero__sub">Manage your clinic page, doctors, and schedules.</p>
                </div>

                <!-- Quick actions -->
                <nav class="office-actions" aria-label="Quick actions">
                    <a class="qa-btn" href="<?= $clinicUrl ?>">View Public Page</a>
                    <a class="qa-btn" href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=clinic_setup">Edit Clinic</a>
                    <a class="qa-btn" href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=appointments">Create Appointment</a>
                </nav>
            </section>

            <!-- At-a-glance stats -->
            <section class="office-stats" aria-label="Summary">
                <article class="office-stats__card office-stats__card--soft">
                    <div class="office-stats__icon" aria-hidden="true">⭐</div>
                    <div class="office-stats__meta">
                        <div class="office-stats__number">
                            <?= isset($stats['avg_rating']) ? number_format((float) $stats['avg_rating'], 1) : '—' ?>
                        </div>

                        <div class="office-stats__label">Avg. Rating</div>
                    </div>
                </article>
                <article class="office-stats__card" aria-live="polite">
                    <div class="office-stats__icon" aria-hidden="true">👥</div>
                    <div class="office-stats__meta">
                        <div class="office-stats__number"><?= (int) ($stats['doctors'] ?? 0) ?></div>
                        <div class="office-stats__label">Doctors</div>
                    </div>
                </article>

                <article class="office-stats__card" aria-live="polite">
                    <div class="office-stats__icon" aria-hidden="true">📅</div>
                    <div class="office-stats__meta">
                        <div class="office-stats__number"><?= (int) ($stats['today'] ?? 0) ?>
                        </div>
                        <div class="office-stats__label">Appointments Today</div>
                    </div>
                </article>

                <article class="office-stats__card office-stats__card--soft">
                    <div class="office-stats__icon" aria-hidden="true">✅</div>
                    <div class="office-stats__meta">
                        <div class="office-stats__number">
                            <?= (int) ($stats['avail_today'] ?? 0) ?>
                        </div>
                        <div class="office-stats__label">Slots Available Today</div>
                    </div>
                </article>
            </section>

            <!-- Doctors panel -->
            <section class="office-panel" aria-labelledby="doctorsTitle">
                <div class="office-panel__head">
                    <h2 id="doctorsTitle" class="office-panel__title">Doctors</h2>
                    <a class="pill-btn pill-btn--dark"
                        href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=office_doctor_new">
                        Add a Doctor
                    </a>
                </div>

                <div class="doctor-list">
                    <?php foreach ($docs as $d): ?>
                        <article class="doctor-item">
                            <div class="doctor-item__info">
                                <div class="doctor-item__avatar">
                                    <img src="https://via.placeholder.com/96x96.png?text=Dr"
                                        alt="Avatar of <?= e($d['name']) ?>">
                                </div>
                                <div class="doctor-item__text">
                                    <div class="doctor-item__name">
                                        <?= e($d['name']) ?>
                                        <?php if (!empty($d['status'])): ?>
                                            <span
                                                class="chip <?= $d['status'] === 'online' ? 'chip--online' : 'chip--offline' ?>">
                                                <?= e(ucfirst((string) $d['status'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="doctor-item__spec">
                                        <?= e($spec[(int) ($d['specialty_id'] ?? 0)] ?? '—') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="doctor-item__actions">
                                <a class="mini-pill-btn"
                                    href="<?= BASE_URL ?>index.php?page=office_doctor_new&doctor_id=<?= (int) $d['doctor_id'] ?>">Edit</a>
                                <a class="mini-pill-btn"
                                    href="<?= BASE_URL ?>index.php?page=appointments&doctor_id=<?= (int) $d['doctor_id'] ?>">Future
                                    Appointments</a>
                                <a class="mini-pill-btn"
                                    href="<?= BASE_URL ?>index.php?page=doctor_schedule&doctor_id=<?= (int) $d['doctor_id'] ?>">Manage
                                    Schedule</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>


            </section>

            <!-- Next Appointments -->
            <section class="office-panel" aria-labelledby="nextApptTitle">
                <div class="office-panel__head">
                    <h2 id="nextApptTitle" class="office-panel__title">Next Appointments</h2>
                </div>

                <div class="appt-list">
                    <header class="appt-head appt-row" aria-hidden="true">
                        <div>Patient</div>
                        <div>Doctor</div>
                        <div>Date &amp; time</div>
                        <div>Action</div>
                    </header>

                    <?php foreach ($appts as $a): ?>
                        <article class="appt-item appt-row">
                            <div class="appt-item__patient">
                                <div class="appt-item__name"><?= e($a['patient_name'] ?? '—') ?></div>
                            </div>
                            <div class="appt-item__doctor">
                                <div class="appt-item__docname"><?= e($a['doctor_name'] ?? '—') ?></div>
                                <div class="appt-item__spec">
                                    <?= e($spec[(int) ($a['specialty_id'] ?? 0)] ?? '—') ?>
                                </div>
                            </div>
                            <div class="appt-item__datetime">
                                <div class="appt-item__date"><span class="appt-icon">📅</span>
                                    <?= e(date('d-m-Y', strtotime($a['start_time']))) ?>
                                </div>
                                <div class="appt-item__time"><span class="appt-icon">⏰</span>
                                    <?= e(date('g:i a', strtotime($a['start_time']))) ?>
                                </div>
                            </div>
                            <div class="appt-item__action">
                                <a class="mini-pill-btn"
                                    href="<?= BASE_URL ?>index.php?page=appointments&appointment_id=<?= (int) $a['appointment_id'] ?>">Edit</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

        </div><!-- /office-main__inner -->
    </main>

</body>

</html>