<?php // app/views/clinic/doctor_schedule.php ?>
<section class="schedule-shell">
  <!-- Top bar / header region -->
  <header class="schedule-header">
    <div class="schedule-header__left">
      <div class="brand">
        <img class="brand__icon" src="<?= IMAGE_PATH ?>/Logo.svg" alt="MediBook">
        <span class="brand__name">MEDIBOOK</span>
      </div>
    </div>

    <div class="schedule-header__right">
      <div class="facility-switcher">
        <span class="facility-switcher__name">Blue Horizon Medical Center</span>
        <span class="facility-switcher__chevron">▼</span>
      </div>
    </div>
  </header>

  <!-- White card content area -->
  <main class="schedule-card">
    <!-- Doctor row -->
    <div class="schedule-docrow">
      <div class="schedule-docrow__info">
        <div class="doc-avatar">
          <img src="https://via.placeholder.com/64x64.png" alt="Dr. Avatar">
        </div>
        <div class="doc-meta">
          <div class="doc-meta__name">Dr. Michael Lee</div>
          <div class="doc-meta__role">General Practitioner</div>
        </div>
      </div>

      <button class="save-btn" type="button">Save</button>

      <div class="user-badge">
        <span class="user-badge__circle">L</span>
      </div>
    </div>

    <!-- Scrollable schedule content -->
    <div class="schedule-gridwrap">
      <!-- arrows + day columns -->
      <button class="nav-arrow nav-arrow--left" aria-label="Previous days">‹</button>

      <div class="schedule-columns">
        <!-- Repeat this .day-col for each day -->
        <div class="day-col">
          <div class="day-col__head">
            <div class="day-col__weekday">Monday</div>
            <div class="day-col__date">6 Oct.</div>
          </div>

          <div class="day-col__slots">
            <!-- time slots -->
            <button class="slot-btn">9:00am</button>
            <button class="slot-btn">9:30am</button>
            <button class="slot-btn">10:00am</button>
            <button class="slot-btn">10:30am</button>

            <div class="break-lines">
              <span class="break-line"></span>
              <span class="break-line"></span>
              <span class="break-line"></span>
            </div>

            <button class="slot-btn">12:30pm</button>
            <button class="slot-btn">1:00pm</button>
            <button class="slot-btn">1:30pm</button>
            <button class="slot-btn">2:00pm</button>
            <button class="slot-btn">2:30pm</button>

            <div class="break-lines">
              <span class="break-line"></span>
              <span class="break-line"></span>
            </div>

            <button class="slot-btn">3:30pm</button>
            <button class="slot-btn">4:00pm</button>
            <button class="slot-btn">4:30pm</button>
            <button class="slot-btn">5:00pm</button>
            <button class="slot-btn">5:30pm</button>
            <button class="slot-btn">6:00pm</button>
            <button class="slot-btn">6:30pm</button>
            <button class="slot-btn">7:00pm</button>
          </div>
        </div>

        <div class="day-col">
          <div class="day-col__head">
            <div class="day-col__weekday">Tuesday</div>
            <div class="day-col__date">7 Oct.</div>
          </div>

          <div class="day-col__slots">
            <button class="slot-btn">9:00am</button>
            <button class="slot-btn">9:30am</button>
            <button class="slot-btn">10:00am</button>
            <button class="slot-btn">10:30am</button>

            <div class="break-lines">
              <span class="break-line"></span>
              <span class="break-line"></span>
              <span class="break-line"></span>
            </div>

            <button class="slot-btn">1:00pm</button>
            <button class="slot-btn">1:30pm</button>
            <button class="slot-btn">2:00pm</button>
            <button class="slot-btn">2:30pm</button>
            <button class="slot-btn">3:30pm</button>
            <button class="slot-btn">4:00pm</button>
            <button class="slot-btn">4:30pm</button>
            <button class="slot-btn">5:00pm</button>
            <button class="slot-btn">5:30pm</button>
            <button class="slot-btn">6:00pm</button>
            <button class="slot-btn">6:30pm</button>
            <button class="slot-btn">7:00pm</button>
          </div>
        </div>

        <div class="day-col">
          <div class="day-col__head">
            <div class="day-col__weekday">Wednesday</div>
            <div class="day-col__date">9 Oct.</div>
          </div>

          <div class="day-col__slots">
            <button class="slot-btn">9:00am</button>
            <button class="slot-btn">9:30am</button>
            <button class="slot-btn">10:00am</button>
            <button class="slot-btn">10:30am</button>
            <button class="slot-btn">11:00am</button>
            <button class="slot-btn">11:30am</button>
            <button class="slot-btn">12:00pm</button>
            <button class="slot-btn">12:30pm</button>
            <button class="slot-btn">1:00pm</button>
            <button class="slot-btn">1:30pm</button>
            <button class="slot-btn">2:00pm</button>
            <button class="slot-btn">2:30pm</button>
            <button class="slot-btn">3:00pm</button>
            <button class="slot-btn">3:30pm</button>
            <button class="slot-btn">4:00pm</button>
            <button class="slot-btn">4:30pm</button>
            <button class="slot-btn">5:00pm</button>
            <button class="slot-btn">5:30pm</button>
            <button class="slot-btn">6:00pm</button>
            <button class="slot-btn">6:30pm</button>
            <button class="slot-btn">7:00pm</button>
          </div>
        </div>

        <div class="day-col">
          <div class="day-col__head">
            <div class="day-col__weekday">Thursday</div>
            <div class="day-col__date">10 Oct.</div>
          </div>

          <div class="day-col__slots">
            <button class="slot-btn">9:00am</button>
            <button class="slot-btn">9:30am</button>
            <button class="slot-btn">9:30am</button>
            <button class="slot-btn">10:00am</button>
            <button class="slot-btn">10:30am</button>
            <button class="slot-btn">11:00am</button>
            <button class="slot-btn">11:30am</button>
            <button class="slot-btn">12:00pm</button>
            <button class="slot-btn">12:30pm</button>
            <button class="slot-btn">1:00pm</button>
            <button class="slot-btn">1:30pm</button>
            <button class="slot-btn">2:00pm</button>
            <button class="slot-btn">2:30pm</button>
            <button class="slot-btn">3:30pm</button>
            <button class="slot-btn">4:00pm</button>
            <button class="slot-btn">4:30pm</button>
            <button class="slot-btn">5:00pm</button>
            <button class="slot-btn">5:30pm</button>
            <button class="slot-btn">6:00pm</button>
            <button class="slot-btn">6:30pm</button>
            <button class="slot-btn">7:00pm</button>
          </div>
        </div>
      </div>

      <button class="nav-arrow nav-arrow--right" aria-label="Next days">›</button>
    </div>
  </main>
</section>