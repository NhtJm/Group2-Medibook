<?php
// Read query values
$clinicId = (int)($_GET['clinic'] ?? 1);
$docId    = (int)($_GET['doc'] ?? 101);
$dateStr  = $_GET['date'] ?? '';
$timeStr  = $_GET['time'] ?? '';

// Demo lookup (same ids as earlier pages)
$clinics = [
  1 => ['name'=>'Blue Horizon Medical Center','addr'=>"142 King's Road, London, UK",'phone'=>'+44 20 7132 5542','site'=>'www.bluehorizonmed.co.uk'],
  2 => ['name'=>'Green Health Clinic','addr'=>'25 Elm Street, London, UK','phone'=>'+44 20 7000 0000','site'=>'example.com'],
  3 => ['name'=>'Sunrise Family Care','addr'=>'78 Maple Avenue, London, UK','phone'=>'+44 20 7000 0001','site'=>'example.com'],
  4 => ['name'=>'Riverside Medical Practice','addr'=>'310 Bridge Street, London, UK','phone'=>'+44 20 7000 0002','site'=>'example.com'],
];
$doctors = [
  101 => ['name'=>'Dr. Michael Lee','spec'=>'General Practitioner','initials'=>'ML'],
  102 => ['name'=>'Dr. Anna Rodriguez','spec'=>'General Practitioner','initials'=>'AR'],
  103 => ['name'=>'Dr. Emma Collins','spec'=>'General Practitioner','initials'=>'EC'],
];

$clinic = $clinics[$clinicId] ?? $clinics[1];
$doc    = $doctors[$docId]    ?? $doctors[101];

// Back link
$back = BASE_URL."index.php?page=doctor&clinic=$clinicId&doc=$docId";
?>
<section class="conf-wrap">
  <a class="conf-back" href="<?= htmlspecialchars($back) ?>">← Back to time selection</a>

  <div class="conf-card">
    <div class="conf-logo">
      <img src="<?= IMAGE_PATH ?>/Logo.svg" alt="MediBook">
    </div>
    <h1 class="conf-title">Appointment Summary</h1>

    <div class="conf-grid">
      <!-- Doctor tile -->
      <div class="conf-tile">
        <div class="tile-left">
          <div class="avatar-lg"><?= htmlspecialchars($doc['initials']) ?></div>
          <div>
            <div class="tile-title"><?= htmlspecialchars($doc['name']) ?></div>
            <div class="tile-sub"><?= htmlspecialchars($doc['spec']) ?></div>
          </div>
        </div>
      </div>

      <!-- Clinic tile -->
      <div class="conf-tile">
        <div class="tile-left">
          <div class="avatar-img"></div>
          <div>
            <div class="tile-title"><?= htmlspecialchars($clinic['name']) ?></div>
            <div class="tile-sub"><?= htmlspecialchars($clinic['addr']) ?></div>
            <div class="tile-links">
              <a href="tel:<?= preg_replace('/\s+/', '', $clinic['phone']) ?>"><?= htmlspecialchars($clinic['phone']) ?></a><br>
              <a target="_blank" rel="noopener" href="//<?= htmlspecialchars($clinic['site']) ?>"><?= htmlspecialchars($clinic['site']) ?></a>
            </div>
          </div>
        </div>
      </div>

      <!-- Date/Time tile -->
      <div class="conf-tile conf-tile--wide">
        <div class="tile-title"><?= htmlspecialchars($dateStr ?: 'Select a date') ?></div>
        <div class="tile-sub"><?= htmlspecialchars($timeStr ?: '') ?></div>
      </div>
    </div>

    <form class="conf-actions" method="post" action="#">
      <button class="btn-confirm" type="submit">Confirm</button>
    </form>
  </div>
</section>