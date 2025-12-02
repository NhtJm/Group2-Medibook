<?php 
// app/views/clinic/doctor_schedule.php 
$office = $data['office'] ?? null;
$doctor = $data['doctor'] ?? null;
$days = $data['days'] ?? [];
$slots = $data['slots'] ?? [];
$weekOffset = $data['weekOffset'] ?? 0;

$timeSlots = [];
for ($h = 8; $h <= 19; $h++) {
    $timeSlots[] = sprintf('%02d:00', $h);
    $timeSlots[] = sprintf('%02d:30', $h);
}

$now = new DateTime();
$currentDate = $now->format('Y-m-d');
$currentTime = $now->format('H:i');
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/doctor_schedule.css">

<section class="schedule-shell">
  <!-- White card content area -->
  <main class="schedule-card">
    <!-- Breadcrumb -->
    <div class="schedule-breadcrumb">
      <a href="<?= BASE_URL ?>index.php?page=office_dashboard" class="schedule-breadcrumb__link">
        ← Back to Dashboard
      </a>
      <span class="schedule-breadcrumb__sep">/</span>
      <span class="schedule-breadcrumb__current"><?= e($office['name'] ?? 'Clinic') ?></span>
    </div>

    <!-- Doctor row -->
    <div class="schedule-docrow">
      <div class="schedule-docrow__info">
        <div class="doc-avatar">
          <?php if ($doctor['photo'] && str_starts_with($doctor['photo'], 'uploads/')): ?>
          <img src="<?= BASE_URL ?><?= e($doctor['photo']) ?>" alt="<?= e($doctor['doctor_name']) ?>">
          <?php else: ?>
          <span class="doc-avatar__initials">
            <?= e(strtoupper(substr($doctor['doctor_name'], 0, 2))) ?>
          </span>
          <?php endif; ?>
        </div>
        <div class="doc-meta">
          <div class="doc-meta__name"><?= e($doctor['doctor_name']) ?></div>
          <div class="doc-meta__role"><?= e($doctor['specialty_name'] ?? 'General Practitioner') ?></div>
        </div>
      </div>

      <div style="display: flex; gap: 1rem; align-items: center;">
        <button class="save-btn" type="button" id="bulkAddBtn">Bulk Add Slots</button>
      </div>
    </div>

    <!-- Week navigation -->
    <div class="week-nav">
      <a href="<?= BASE_URL ?>index.php?page=doctor_schedule&doctor_id=<?= $doctor['doctor_id'] ?>&week=<?= $weekOffset - 1 ?>" 
         class="week-nav__btn">‹ Previous</a>
      
      <span class="week-nav__label">
        <?= (new DateTime($days[0]['date'] ?? 'now'))->format('M j') ?> - <?= (new DateTime($days[6]['date'] ?? 'now'))->format('M j, Y') ?>
      </span>
      
      <a href="<?= BASE_URL ?>index.php?page=doctor_schedule&doctor_id=<?= $doctor['doctor_id'] ?>&week=<?= $weekOffset + 1 ?>" 
         class="week-nav__btn">Next ›</a>
    </div>

    <!-- Scrollable schedule content -->
    <div class="schedule-gridwrap">
      <div class="schedule-columns">
        <?php foreach ($days as $day): 
            $isPastDay = $day['date'] < $currentDate;
        ?>
        <div class="day-col <?= $isPastDay ? 'day-col--past' : '' ?>" data-date="<?= $day['date'] ?>">
          <div class="day-col__head <?= $day['isToday'] ? 'day-col__head--today' : '' ?>">
            <div class="day-col__weekday"><?= substr($day['weekday'], 0, 3) ?></div>
            <div class="day-col__date"><?= $day['display'] ?></div>
          </div>

          <div class="day-col__slots">
            <?php 
            $daySlots = $slots[$day['date']] ?? [];
            $daySlotTimes = array_column($daySlots, 'time24');
            
            foreach ($timeSlots as $time):
                $slotIndex = array_search($time, $daySlotTimes);
                $isActive = $slotIndex !== false;
                $isBooked = $isActive && $daySlots[$slotIndex]['booked'];
                $slotId = $isActive ? $daySlots[$slotIndex]['slot_id'] : null;
                
                $isPast = ($day['date'] < $currentDate) || ($day['date'] === $currentDate && $time < $currentTime);
                
                $dt = DateTime::createFromFormat('H:i', $time);
                $timeDisplay = $dt->format('g:ia');
            ?>
            <button class="slot-btn <?= $isActive ? 'slot-btn--active' : '' ?> <?= $isBooked ? 'slot-btn--booked' : '' ?> <?= $isPast ? 'slot-btn--past' : '' ?>"
                    data-time="<?= $time ?>"
                    data-date="<?= $day['date'] ?>"
                    data-slot-id="<?= $slotId ?>"
                    <?= ($isBooked || $isPast) ? 'disabled' : '' ?>
                    title="<?= $isPast ? 'Past slot' : ($isBooked ? 'This slot is booked' : ($isActive ? 'Click to remove' : 'Click to add')) ?>">
              <?= $timeDisplay ?>
              <?php if ($isBooked): ?><span class="booked-badge">📅</span><?php endif; ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</section>

<!-- Bulk Add Slots Modal -->
<div id="bulkAddModal" class="modal" style="display: none;">
    <div class="modal__backdrop"></div>
    <div class="modal__content">
        <div class="modal__header">
            <h2>Add Slots for a Day</h2>
            <button class="modal__close" type="button">&times;</button>
        </div>
        <form id="bulkAddForm">
            <div class="form__group">
                <label class="form__label">
                    Select Date
                    <input type="date" name="date" required min="<?= date('Y-m-d') ?>">
                </label>
            </div>
            <div class="form__group">
                <label class="form__label">
                    Start Time
                    <select name="start_time" required>
                        <?php foreach ($timeSlots as $time): ?>
                        <option value="<?= $time ?>"><?= DateTime::createFromFormat('H:i', $time)->format('g:i A') ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="form__group">
                <label class="form__label">
                    End Time
                    <select name="end_time" required>
                        <?php foreach ($timeSlots as $time): ?>
                        <option value="<?= $time ?>" <?= $time === '17:00' ? 'selected' : '' ?>><?= DateTime::createFromFormat('H:i', $time)->format('g:i A') ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="form__actions">
                <button type="button" class="btn btn--secondary" id="cancelBulkAdd">Cancel</button>
                <button type="submit" class="btn btn--primary">Add Slots</button>
            </div>
        </form>
    </div>
</div>

<script>
window.DOCTOR_SCHEDULE_CONFIG = {
    doctorId: <?= $doctor['doctor_id'] ?>,
    apiUrl: '<?= BASE_URL ?>index.php?page=doctor_schedule_api'
};
</script>
<script src="<?= BASE_URL ?>js/doctor_schedule.js"></script>