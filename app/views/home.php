<section class="hero">
  <div class="hero__inner">
    <!-- LEFT: text -->
    <div class="hero__copy">
      <h1 class="hero__title">
        Connecting <span>patients</span> with care.
      </h1>
      <p class="hero__lead">
        MediBook makes it easy to find the right doctor, schedule appointments online,
        and manage your healthcare—all in one place
      </p>
    </div>
    <div class="hero__art">
      <img src="images/hero-doctor.png" alt="Doctor illustration">
    </div>

    <form class="search">
      <div class="search__field">
        <label>Doctor, Clinic or Specialty</label>
        <input type="text" placeholder="e.g., Dr. Clark, Cardiology, …">
      </div>
      <div class="search__field">
        <label>Location</label>
        <input type="text" placeholder="Where ?">
      </div>
      <button class="search__btn" type="button">Search</button>
    </form>
  </div>
</section>

<section class="section">
  <div class="section__inner">
    <h2 class="section__title">Popular Specialties</h2>
    <div class="section__bar"></div>

    <div class="spec-grid">
      <!-- Card 1 -->
      <article class="spec-card">
        <img class="spec-card__img" src="<?= BASE_URL ?>/public/assets/images/spec-1.jpg" alt="">
        <div class="spec-card__body">
          <h3>General Practice</h3>
          <p>Everyday care for you and your family.</p>
          <a class="spec-card__link" href="#">Find Specialists</a>
        </div>
      </article>

      <!-- Card 2 -->
      <article class="spec-card">
        <img class="spec-card__img" src="<?= BASE_URL ?>/public/assets/images/spec-2.jpg" alt="">
        <div class="spec-card__body">
          <h3>Pediatrics</h3>
          <p>Focused on children’s health and growth.</p>
          <a class="spec-card__link" href="#">Find Specialists</a>
        </div>
      </article>

      <!-- Card 3 -->
      <article class="spec-card">
        <img class="spec-card__img" src="<?= BASE_URL ?>/public/assets/images/spec-3.jpg" alt="">
        <div class="spec-card__body">
          <h3>Internal Medicine</h3>
          <p>Care for adult health and chronic issues.</p>
          <a class="spec-card__link" href="#">Find Specialists</a>
        </div>
      </article>

      <!-- Card 4 -->
      <article class="spec-card">
        <img class="spec-card__img" src="<?= BASE_URL ?>/public/assets/images/spec-4.jpg" alt="">
        <div class="spec-card__body">
          <h3>Obstetrics &amp; Gynecology</h3>
          <p>Comprehensive women’s health care.</p>
          <a class="spec-card__link" href="#">Find Specialists</a>
        </div>
      </article>

      <!-- Card 5 -->
      <article class="spec-card">
        <img class="spec-card__img" src="<?= BASE_URL ?>/public/assets/images/spec-5.jpg" alt="">
        <div class="spec-card__body">
          <h3>Dermatology</h3>
          <p>Expert care for skin, hair, and nails.</p>
          <a class="spec-card__link" href="#">Find Specialists</a>
        </div>
      </article>

      <!-- Card 6 -->
      <article class="spec-card">
        <img class="spec-card__img" src="<?= BASE_URL ?>/public/assets/images/spec-6.jpg" alt="">
        <div class="spec-card__body">
          <h3>Cardiology</h3>
          <p>Dedicated to heart and vascular health.</p>
          <a class="spec-card__link" href="#">Find Specialists</a>
        </div>
      </article>
    </div>
  </div>
</section>

<section class="why">
  <div class="why__inner">
    <h2 class="section__title">Why choose MediBook</h2>
    <div class="section__bar"></div>

    <div class="why-grid">
      <div class="why-card">
        <!-- FIX path: thêm '/' sau BASE_URL -->
        <div class="why-card__icon" style="--icon-url: url('<?= BASE_URL ?>assets/timer.png');"></div>
        <h3>Simple and fast booking</h3>
        <p>Find the right doctor and schedule your appointment in just a few clicks.</p>
      </div>

      <div class="why-card">
        <div class="why-card__icon" style="--icon-url: url('<?= BASE_URL ?>assets/shield-check.png');"></div>
        <h3>Verified doctors and clinics</h3>
        <p>All medical offices are reviewed and approved to ensure trusted, quality care.</p>
      </div>

      <div class="why-card">
        <div class="why-card__icon" style="--icon-url: url('<?= BASE_URL ?>assets/user-heart.png');"></div>
        <h3>Personalized experience</h3>
        <p>Easily manage your appointments and health information in one secure place.</p>
      </div>
    </div>
  </div>
</section>

<section class="companion">
  <div class="section__inner">
    <h2 class="section__title">Your everyday health companion</h2>

    <div class="companion-grid">
      <div class="companion-item">
        <img src="<?= BASE_URL ?>assets/comp-1.png" alt="" class="companion-item__img">
        <h4>Access care with ease</h4>
        <p>Find doctors by specialty and location, and book your appointments in just a few clicks.</p>
      </div>
      <div class="companion-item">
        <img src="<?= BASE_URL ?>assets/comp-2.png" alt="" class="companion-item__img">
        <h4>Receive personalized care</h4>
        <p>Choose the right doctor for your needs and keep track of your upcoming visits.</p>
      </div>
      <div class="companion-item">
        <img src="<?= BASE_URL ?>assets/comp-3.png" alt="" class="companion-item__img">
        <h4>Manage your health</h4>
        <p>Store and organize your health information, along with the details of your loved ones.</p>
      </div>
    </div>
  </div>
</section>
