<?php
// Read which clinic to show
$id = (int) ($_GET['id'] ?? 1);

/* Demo data (static for UI) */
$clinics = [
  1 => [
    'code' => 'BH',
    'name' => 'Blue Horizon Medical Center',
    'addr' => "142 King's Road, London, UK",
    'open' => true,
    'hours' => 'Closes 5:00pm',
    'phone' => '+44 20 7132 5542',
    'site' => 'www.bluehorizonmed.co.uk',
    'about' => [
      'We are a team of experienced general practitioners dedicated to delivering high-quality medical care for individuals and families.',
      'Our mission is to provide comprehensive healthcare with a holistic approach to diagnosis, treatment, and disease prevention.',
      'We offer a wide range of services, including health assessments, chronic disease management, family medicine, women’s and men’s health, vaccinations, and minor procedures.',
      'Located in the heart of London, our clinic is easily accessible and committed to serving the local community with respect, compassion, and excellence.',
      'Booking an appointment is simple and can be done online at any time.'
    ],
    'doctors' => [
      ['id' => 101, 'init' => 'ML', 'name' => 'Dr. Michael Lee', 'spec' => 'General Practitioner', 'next' => 'Mon, 9:00am'],
      ['id' => 102, 'init' => 'AR', 'name' => 'Dr. Anna Rodriguez', 'spec' => 'General Practitioner', 'next' => 'Mon, 11:30am'],
      ['id' => 103, 'init' => 'EC', 'name' => 'Dr. Emma Collins', 'spec' => 'General Practitioner', 'next' => 'Tue, 9:00am'],
    ],
  ],
  2 => ['code' => 'GH', 'name' => 'Green Health Clinic', 'addr' => '25 Elm Street, London, UK', 'open' => false, 'hours' => 'Opens Mon 8:30am', 'phone' => '+44 20 7000 0000', 'site' => 'example.com', 'about' => ['Coming soon…'], 'doctors' => []],
  3 => ['code' => 'SF', 'name' => 'Sunrise Family Care', 'addr' => '78 Maple Avenue, London, UK', 'open' => false, 'hours' => 'Opens Mon 8:30am', 'phone' => '+44 20 7000 0001', 'site' => 'example.com', 'about' => ['Coming soon…'], 'doctors' => []],
  4 => ['code' => 'RM', 'name' => 'Riverside Medical Practice', 'addr' => '310 Bridge Street, London, UK', 'open' => false, 'hours' => 'Opens Mon 8:30am', 'phone' => '+44 20 7000 0002', 'site' => 'example.com', 'about' => ['Coming soon…'], 'doctors' => []],
];

$clinic = $clinics[$id] ?? null;
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
      <div class="clv-head">
        <div class="clv-logo"><?= htmlspecialchars($clinic['code']) ?></div>

        <div class="clv-meta">
          <h1 class="clv-name"><?= htmlspecialchars($clinic['name']) ?></h1>
          <p class="clv-addr"><?= htmlspecialchars($clinic['addr']) ?></p>

          <p class="clv-status <?= $clinic['open'] ? 'is-open' : 'is-closed' ?>">
            <span class="dot"></span>
            <?= $clinic['open'] ? 'Open' : 'Closed' ?>
            · <?= htmlspecialchars($clinic['hours']) ?>
          </p>

          <p class="clv-contact">
            <a
              href="tel:<?= preg_replace('/\s+/', '', $clinic['phone']) ?>"><?= htmlspecialchars($clinic['phone']) ?></a><br>
            <a target="_blank" rel="noopener"
              href="//<?= htmlspecialchars($clinic['site']) ?>"><?= htmlspecialchars($clinic['site']) ?></a>
          </p>
        </div>
      </div>

      <div class="clv-about panel">
        <?php foreach ($clinic['about'] as $p): ?>
          <p><?= htmlspecialchars($p) ?></p>
        <?php endforeach; ?>
      </div>

      <div class="clv-doctors panel">
        <?php foreach ($clinic['doctors'] as $d): ?>
          <div class="doc-row">
            <div class="doc-left">
              <div class="avatar"><?= htmlspecialchars($d['init']) ?></div>
              <div class="doc-meta">
                <h3><?= htmlspecialchars($d['name']) ?></h3>
                <small><?= htmlspecialchars($d['spec']) ?></small>
              </div>
            </div>

            <div class="doc-mid">
              <div class="kv">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1f3b72" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="3" />
                  <path d="M16 2v4M8 2v4M3 10h18" />
                </svg>
                <span>Next availability : <strong><?= htmlspecialchars($d['next']) ?></strong></span>
              </div>
            </div>

            <a class="btn-make" href="<?= BASE_URL ?>index.php?page=doctor&clinic=<?= $id ?>&doc=<?= $d['id'] ?>">
              Make Appointment
            </a>
          </div>
        <?php endforeach; ?>
        <?php if (empty($clinic['doctors'])): ?>
          <p class="subtitle">No doctors listed yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>
<?php endif; ?>