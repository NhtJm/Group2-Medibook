<?php
$clinicId = (int)($_GET['clinic'] ?? 1);
$docId    = (int)($_GET['doc'] ?? 101);

/* Clinic names (for back link text) */
$clinicNames = [
  1 => 'Blue Horizon Medical Center',
  2 => 'Green Health Clinic',
  3 => 'Sunrise Family Care',
  4 => 'Riverside Medical Practice',
];
$clinicName = $clinicNames[$clinicId] ?? 'Clinic';

/* Demo doctor directory (small) */
$doctorDB = [
  101 => ['init'=>'ML','name'=>'Dr. Michael Lee','spec'=>'General Practitioner'],
  102 => ['init'=>'AR','name'=>'Dr. Anna Rodriguez','spec'=>'General Practitioner'],
  103 => ['init'=>'EC','name'=>'Dr. Emma Collins','spec'=>'General Practitioner'],
];
$doc = $doctorDB[$docId] ?? $doctorDB[101];

/* Simple 7-day window labels (static for UI) */
$days = [
  ['label'=>'Monday','date'=>'6 Oct.','slots'=>['9:00am','9:30am','10:00am','10:30am','12:30pm','1:00pm','1:30pm','2:00pm','3:30pm','4:00pm','4:30pm','5:00pm','5:30pm','6:00pm','6:30pm','7:00pm']],
  ['label'=>'Tuesday','date'=>'7 Oct.','slots'=>['9:00am','9:30am','10:00am','10:30am','—','—','—','—','1:00pm','1:30pm','2:00pm','3:30pm','4:00pm','4:30pm','5:00pm','5:30pm','6:00pm','6:30pm','7:00pm']],
  ['label'=>'Wednesday','date'=>'9 Oct.','slots'=>['9:00am','9:30am','10:00am','10:30am','11:30am','12:00pm','12:30pm','1:30pm','2:00pm','2:30pm','3:00pm','4:00pm','4:30pm','5:00pm','5:30pm','6:00pm','6:30pm','7:00pm']],
  ['label'=>'Thursday','date'=>'10 Oct.','slots'=>['9:00am','9:30am','10:00am','10:30am','—','—','—','—','12:30pm','1:00pm','1:30pm','2:00pm','3:30pm','4:00pm','4:30pm','5:00pm','5:30pm','6:00pm','6:30pm','7:00pm']],
  ['label'=>'Friday','date'=>'11 Oct.','slots'=>['9:00am','9:30am','10:00am','10:30am','12:30pm','1:00pm','1:30pm','2:00pm','3:30pm','4:00pm','4:30pm','5:00pm','5:30pm','6:00pm','6:30pm','7:00pm']],
  ['label'=>'Saturday','date'=>'12 Oct.','slots'=>['9:00am','9:30am','10:00am','—','—','—','1:00pm','1:30pm','2:00pm','3:30pm','4:00pm','4:30pm','5:00pm','5:30pm','6:00pm','6:30pm','7:00pm']],
  ['label'=>'Sunday','date'=>'13 Oct.','slots'=>['—','—','—','—','—','—']],
];

/* Back target */
$backHref = BASE_URL . "index.php?page=clinic&id=" . $clinicId;
?>
<section class="dv-hero">
  <div class="dv-wrap">
    <a class="dv-back" href="<?= htmlspecialchars($backHref) ?>">← Back to <?= htmlspecialchars($clinicName) ?></a>

    <div class="dv-head">
      <div class="avatar-lg"><?= htmlspecialchars($doc['init']) ?></div>
      <div class="meta">
        <h1 class="name"><?= htmlspecialchars($doc['name']) ?></h1>
        <p class="spec"><?= htmlspecialchars($doc['spec']) ?></p>
      </div>
    </div>

    <div class="dv-bio">
      <p>Dr. Michael Lee is an experienced general practitioner dedicated to providing high-quality, patient-centered care. He focuses on building trusted relationships and taking a holistic approach to diagnosis and treatment.</p>
      <p>His main areas of interest include preventive medicine, chronic disease management, and family health. Dr. Lee is passionate about helping patients achieve long-term well-being through personalized care and clear communication.</p>
    </div>
  </div>
</section>

<section class="dv-schedule">
  <div class="dv-wrap">
    <header class="row-head">
      <button class="nav-btn" id="prevDays" aria-label="Previous days">‹</button>
      <div class="spacer"></div>
      <button class="nav-btn" id="nextDays" aria-label="Next days">›</button>
    </header>

    <div class="days" id="daysScroll">
      <?php foreach ($days as $d): ?>
        <?php $dateLabel = $d['label'].' '.$d['date']; ?>
        <div class="day-col">
          <div class="day-head">
            <div class="dow"><?= htmlspecialchars($d['label']) ?></div>
            <div class="date"><?= htmlspecialchars($d['date']) ?></div>
          </div>
          <div class="slot-list">
            <?php foreach ($d['slots'] as $s): ?>
              <?php if ($s === '—'): ?>
                <span class="gap"></span>
              <?php else: ?>
                <?php
                  $url = BASE_URL . 'index.php?page=confirm'
                       . '&clinic=' . $clinicId
                       . '&doc='    . $docId
                       . '&date='   . rawurlencode($dateLabel)
                       . '&time='   . rawurlencode($s);
                ?>
                <a href="<?= htmlspecialchars($url) ?>" class="slot"><?= htmlspecialchars($s) ?></a>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
  (function(){
    const scroller = document.getElementById('daysScroll');
    document.getElementById('prevDays').addEventListener('click', () => {
      scroller.scrollBy({ left: -320, behavior: 'smooth' });
    });
    document.getElementById('nextDays').addEventListener('click', () => {
      scroller.scrollBy({ left:  320, behavior: 'smooth' });
    });
  })();
</script>