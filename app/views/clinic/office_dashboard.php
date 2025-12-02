<?php 
// app/views/clinic/office_dashboard.php 
$office = $data['office'] ?? null;
$doctors = $data['doctors'] ?? [];
$appointments = $data['appointments'] ?? [];
$stats = $data['stats'] ?? ['doctors' => 0, 'today' => 0, 'week' => 0, 'total' => 0];
$success = $data['success'] ?? false;
?>

<main class="office-main">
    <div class="office-main__inner">

        <?php if ($success): ?>
        <div class="success-message">
            ✅ Clinic information saved successfully!
        </div>
        <?php endif; ?>

        <!-- Dashboard Title / stat cards row -->
        <section class="office-heading">
            <div class="office-heading__top">
                <div>
                    <h1 class="office-heading__title">Dashboard</h1>
                    <p class="office-heading__subtitle"><?= e($office['name'] ?? 'Your Clinic') ?></p>
                    <?php if ($office && $office['status'] === 'pending'): ?>
                    <span class="status-badge status-badge--pending">Pending Approval</span>
                    <?php elseif ($office && $office['status'] === 'approved'): ?>
                    <span class="status-badge status-badge--approved">Approved</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="office-heading__bar"></div>

            <div class="office-stats">
                <div class="office-stats__card">
                    <div class="office-stats__icon">
                        <div class="icon-people" aria-hidden="true">👥</div>
                    </div>
                    <div class="office-stats__meta">
                        <div class="office-stats__number"><?= $stats['doctors'] ?></div>
                        <div class="office-stats__label">Doctors</div>
                    </div>
                </div>

                <div class="office-stats__card">
                    <div class="office-stats__icon">
                        <div class="icon-calendar" aria-hidden="true">📅</div>
                    </div>
                    <div class="office-stats__meta">
                        <div class="office-stats__number"><?= $stats['today'] ?></div>
                        <div class="office-stats__label">Appointments Today</div>
                    </div>
                </div>

                <div class="office-stats__card">
                    <div class="office-stats__icon">
                        <div class="icon-week" aria-hidden="true">📊</div>
                    </div>
                    <div class="office-stats__meta">
                        <div class="office-stats__number"><?= $stats['week'] ?></div>
                        <div class="office-stats__label">This Week</div>
                    </div>
                </div>

                <div class="office-stats__card">
                    <div class="office-stats__icon">
                        <div class="icon-total" aria-hidden="true">📋</div>
                    </div>
                    <div class="office-stats__meta">
                        <div class="office-stats__number"><?= $stats['total'] ?></div>
                        <div class="office-stats__label">Total Appointments</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Doctors panel -->
        <section class="office-panel">
            <div class="office-panel__head">
                <h2 class="office-panel__title">Doctors (<?= count($doctors) ?>)</h2>
                <button class="pill-btn pill-btn--dark" id="addDoctorBtn" type="button">
                    Add a Doctor
                </button>
            </div>

            <div class="doctor-list">
                <?php if (empty($doctors)): ?>
                <p class="empty-message">
                    No doctors yet. Click "Add a Doctor" to get started.
                </p>
                <?php else: ?>
                    <?php foreach ($doctors as $doc): ?>
                    <div class="doctor-item" data-doctor-id="<?= $doc['doctor_id'] ?>">
                        <div class="doctor-item__info">
                            <div class="doctor-item__avatar">
                                <?php if ($doc['photo'] && str_starts_with($doc['photo'], 'uploads/')): ?>
                                <img src="<?= BASE_URL ?><?= e($doc['photo']) ?>" alt="<?= e($doc['doctor_name']) ?>">
                                <?php else: ?>
                                <span class="doctor-item__initials">
                                    <?= e($doc['initials']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="doctor-item__text">
                                <div class="doctor-item__name"><?= e($doc['doctor_name']) ?></div>
                                <div class="doctor-item__spec"><?= e($doc['specialty_name'] ?? 'General') ?></div>
                            </div>
                        </div>

                        <div class="doctor-item__actions">
                            <a href="<?= BASE_URL ?>index.php?page=doctor_schedule&doctor_id=<?= $doc['doctor_id'] ?>" class="mini-pill-btn">
                                Manage Schedule
                            </a>
                            <button class="mini-pill-btn delete-doctor-btn" data-id="<?= $doc['doctor_id'] ?>">Delete</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Next Appointments panel -->
        <section class="office-panel">
            <div class="office-panel__head">
                <h2 class="office-panel__title">Upcoming Appointments</h2>
            </div>

            <div class="appt-list">
                <?php if (empty($appointments)): ?>
                <p class="empty-message">
                    No upcoming appointments.
                </p>
                <?php else: ?>
                    <?php foreach ($appointments as $appt): ?>
                    <div class="appt-item">
                        <div class="appt-item__patient">
                            <div class="appt-item__name"><?= e($appt['patient_name']) ?></div>
                            <div class="appt-item__dob"><?= e($appt['dob_formatted']) ?></div>
                        </div>

                        <div class="appt-item__doctor">
                            <div class="appt-item__docname"><?= e($appt['doctor_name']) ?></div>
                            <div class="appt-item__spec"><?= e($appt['specialty_name'] ?? 'General') ?></div>
                        </div>

                        <div class="appt-item__datetime">
                            <div class="appt-item__date">
                                <span class="appt-icon" aria-hidden="true">📅</span>
                                <span><?= e($appt['date_formatted']) ?></span>
                            </div>
                            <div class="appt-item__time">
                                <span class="appt-icon" aria-hidden="true">⏰</span>
                                <span><?= e($appt['time_formatted']) ?></span>
                            </div>
                        </div>

                        <div class="appt-item__action">
                            <span class="mini-pill-btn mini-pill-btn--success">Booked</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>
</main>

<!-- Modal pour ajouter un docteur -->
<div id="addDoctorModal" class="modal" style="display: none;">
    <div class="modal__backdrop"></div>
    <div class="modal__content">
        <div class="modal__header">
            <h2>Add New Doctor</h2>
            <button class="modal__close" type="button">&times;</button>
        </div>
        <form id="addDoctorForm" enctype="multipart/form-data">
            <!-- Photo upload -->
            <div class="form__group form__group--photo">
                <div class="doctor-photo-preview" id="doctorPhotoPreview">
                    <span id="photoPlaceholder">👨‍⚕️</span>
                    <img id="photoPreviewImg" src="" alt="" class="doctor-photo-preview__img">
                </div>
                <label class="btn btn--secondary btn--upload">
                    <input type="file" name="photo" accept="image/*" id="doctorPhotoInput">
                    📷 Upload Photo
                </label>
                <button type="button" id="removePhotoBtn" class="btn btn--link btn--remove-photo">Remove</button>
            </div>

            <div class="form__group">
                <label class="form__label">
                    Doctor Name *
                    <input type="text" name="doctor_name" required placeholder="Dr. John Smith">
                </label>
            </div>
            <div class="form__group">
                <label class="form__label">
                    Email *
                    <input type="email" name="email" required placeholder="doctor@example.com">
                </label>
            </div>
            <div class="form__group">
                <label class="form__label">
                    Specialty
                    <select name="specialty_id">
                        <option value="">-- Select Specialty --</option>
                        <?php
                        global $conn;
                        $specSql = "SELECT specialty_id, name FROM Medical_specialty ORDER BY name";
                        $specResult = $conn->query($specSql);
                        while ($spec = $specResult->fetch_assoc()):
                        ?>
                        <option value="<?= $spec['specialty_id'] ?>"><?= e($spec['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </label>
            </div>
            <div class="form__group">
                <label class="form__label">
                    Degree
                    <input type="text" name="degree" placeholder="MD, PhD">
                </label>
            </div>
            <div class="form__group">
                <label class="form__label">
                    Graduate From
                    <input type="text" name="graduate" placeholder="Harvard Medical School">
                </label>
            </div>
            <div class="form__actions">
                <button type="button" class="btn btn--secondary" id="cancelAddDoctor">Cancel</button>
                <button type="submit" class="btn btn--primary">Add Doctor</button>
            </div>
        </form>
    </div>
</div>

<script>
window.OFFICE_DASHBOARD_CONFIG = {
    apiUrl: '<?= BASE_URL ?>index.php?page=office_api'
};
</script>
<script src="<?= BASE_URL ?>js/office_dashboard.js"></script>