<?php
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
$stats    = $data['stats']    ?? ['upcoming'=>0,'completed'=>0];
$upcoming = $data['upcoming'] ?? [];
$past     = $data['past']     ?? [];
?>
<section class="appt-hero">
  <div class="appt-wrap">
    <h1 class="appt-title">My Appointments</h1>

    <!-- Stats -->
    <div class="stat-grid">
      <div class="stat">
        <div class="icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="#1f3b72" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="3"></rect>
            <path d="M16 2v4M8 2v4M3 10h18"></path>
          </svg>
        </div>
        <div>
          <div class="num"><?= (int)$stats['upcoming'] ?></div>
          <div class="label">Upcoming Appointments</div>
        </div>
      </div>

      <div class="stat">
        <div class="icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="#1f3b72" stroke-width="2">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M12 7v6l4 2"></path>
          </svg>
        </div>
        <div>
          <div class="num"><?= (int)$stats['completed'] ?></div>
          <div class="label">Completed Appointments</div>
        </div>
      </div>
    </div>

    <!-- Upcoming -->
    <div class="panel">
      <h3>Upcoming Appointments</h3>

      <div class="appt-list">
        <?php if (!$upcoming): ?>
          <p>No upcoming appointments.</p>
        <?php else: foreach ($upcoming as $a): ?>
          <div class="appt-item">
            <div class="appt-left">
              <div class="avatar"><?= e($a['initials']) ?></div>
              <div class="doc">
                <h4><?= e($a['doctor_name']) ?></h4>
                <small><?= e($a['specialty'] ?: 'Doctor') ?></small>
              </div>
            </div>

            <div class="appt-mid">
              <div class="kv">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1f3b72" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                <span><?= e($a['date_label']) ?></span>
              </div>
              <div class="kv">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1f3b72" stroke-width="2">
                  <circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/>
                </svg>
                <span><?= e($a['time_label']) ?></span>
              </div>
            </div>

            <button class="btn-edit" type="button" data-id="<?= (int)$a['appointment_id'] ?>">Edit</button>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Past -->
    <div class="panel">
      <h3>Past Appointments</h3>
      <div class="appt-list">
        <?php if (!$past): ?>
          <p>No past appointments.</p>
        <?php else: foreach ($past as $a): ?>
          <div class="appt-item">
            <div class="appt-left">
              <div class="avatar"><?= e($a['initials']) ?></div>
              <div class="doc">
                <h4><?= e($a['doctor_name']) ?></h4>
                <small><?= e($a['specialty'] ?: 'Doctor') ?></small>
              </div>
            </div>
            <div class="appt-mid">
              <div class="kv">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1f3b72" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                <span><?= e($a['date_label']) ?></span>
              </div>
              <div class="kv">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1f3b72" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>
                <span><?= e($a['time_label']) ?></span>
              </div>
            </div>
            <span class="appt-status appt-status--past"><?= e(ucfirst($a['status'])) ?></span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Modal Edit Appointment -->
<div class="modal-overlay" id="editModal" hidden>
  <div class="modal">
    <button class="modal__close" type="button" aria-label="Close">&times;</button>
    <h2 class="modal__title">Edit Appointment</h2>
    <p class="modal__subtitle">
      <span id="modalDoctor"></span><br>
      <small id="modalDate"></small>
    </p>
    
    <div class="modal__actions">
      <button class="btn-action btn-action--reschedule" id="btnReschedule" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/>
        </svg>
        Reschedule
      </button>
      <button class="btn-action btn-action--cancel" id="btnCancel" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/>
        </svg>
        Cancel Appointment
      </button>
    </div>

    <!-- Reschedule panel -->
    <div class="modal__reschedule" id="reschedulePanel" hidden>
      <h3>Select a new time slot</h3>
      <div class="slot-list" id="slotList">
        <p class="loading">Loading available slots...</p>
      </div>
      <div class="modal__btns">
        <button class="btn-back" type="button" id="btnBack">Back</button>
        <button class="btn-confirm" type="button" id="btnConfirmReschedule" disabled>Confirm</button>
      </div>
    </div>
  </div>
</div>

<script>window.BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>js/appointments.js"></script>