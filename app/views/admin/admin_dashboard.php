<?php // app/views/admin/dashboard.php ?>

<section class="admin-shell">
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar__brand">
      <div class="brand-icon">
        <img src="<?= IMAGE_PATH ?>/Logo.svg" alt="MediBook">
      </div>
      <div class="brand-copy">
        <div class="brand-name">MediBook Admin</div>
        <div class="brand-user">Alexander Pierce</div>
      </div>
    </div>

    <nav class="admin-nav">
      <div class="admin-nav__section">
        <div class="admin-nav__label">MAIN</div>
        <a class="admin-nav__item is-active" href="#">
          <span class="admin-nav__icon">📊</span>
          <span class="admin-nav__text">Dashboard</span>
          <span class="admin-nav__badge">v2</span>
        </a>
        <a class="admin-nav__item" href="#">
          <span class="admin-nav__icon">👨‍⚕️</span>
          <span class="admin-nav__text">Doctors</span>
        </a>
        <a class="admin-nav__item" href="#">
          <span class="admin-nav__icon">🏥</span>
          <span class="admin-nav__text">Clinics</span>
        </a>
        <a class="admin-nav__item" href="#">
          <span class="admin-nav__icon">🧑</span>
          <span class="admin-nav__text">Patients</span>
        </a>
        <a class="admin-nav__item" href="#">
          <span class="admin-nav__icon">📅</span>
          <span class="admin-nav__text">Appointments</span>
        </a>
        <a class="admin-nav__item" href="#">
          <span class="admin-nav__icon">💳</span>
          <span class="admin-nav__text">Payments</span>
        </a>
      </div>

      <div class="admin-nav__section">
        <div class="admin-nav__label">REPORTS</div>
        <a class="admin-nav__item" href="#">
          <span class="admin-nav__icon">📈</span>
          <span class="admin-nav__text">Analytics</span>
        </a>
        <a class="admin-nav__item" href="#">
          <span class="admin-nav__icon">⚙️</span>
          <span class="admin-nav__text">System Logs</span>
        </a>
      </div>

      <div class="admin-nav__section">
        <div class="admin-nav__label">ACCOUNT</div>
        <a class="admin-nav__item" href="#">
          <span class="admin-nav__icon">👤</span>
          <span class="admin-nav__text">My Account</span>
        </a>
        <a class="admin-nav__item" href="#">
          <span class="admin-nav__icon">🚪</span>
          <span class="admin-nav__text">Sign Out</span>
        </a>
      </div>
    </nav>
  </aside>


  <!-- Main column -->
  <div class="admin-main">

    <!-- Top bar -->
    <header class="admin-topbar">
      <div class="admin-topbar__left">
        <span class="admin-topbar__page">Dashboard v2</span>
      </div>

      <div class="admin-topbar__center">
        <div class="admin-search">
          <input class="admin-search__input" type="text" placeholder="Search...">
          <button class="admin-search__btn" type="button">🔍</button>
        </div>
      </div>

      <div class="admin-topbar__right">
        <button class="topbar-icon-btn" title="Messages">✉️</button>
        <button class="topbar-icon-btn" title="Notifications">🔔</button>
        <button class="topbar-icon-btn" title="Settings">⚙️</button>
      </div>
    </header>


    <!-- KPI cards row -->
    <section class="admin-kpis">
      <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--blue">👨‍⚕️</div>
        <div class="kpi-card__content">
          <div class="kpi-card__value">128</div>
          <div class="kpi-card__label">Active Doctors</div>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--red">❤️</div>
        <div class="kpi-card__content">
          <div class="kpi-card__value">41,410</div>
          <div class="kpi-card__label">Patients</div>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--green">📅</div>
        <div class="kpi-card__content">
          <div class="kpi-card__value">760</div>
          <div class="kpi-card__label">Appointments Today</div>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--yellow">⭐</div>
        <div class="kpi-card__content">
          <div class="kpi-card__value">2,000</div>
          <div class="kpi-card__label">New Signups</div>
        </div>
      </div>
    </section>


    <!-- First big row: Recap + Stats -->
    <section class="admin-row">
      <!-- Monthly Recap -->
      <div class="panel panel--wide">
        <div class="panel__head">
          <div class="panel__title">Monthly Recap Report</div>
          <div class="panel__actions">
            <button class="panel__action-btn">—</button>
            <button class="panel__action-btn">✕</button>
          </div>
        </div>

        <div class="recap-chart">
          <div class="recap-chart__fakegraph">
            <div class="fake-line fake-line--a"></div>
            <div class="fake-line fake-line--b"></div>
            <div class="fake-line fake-line--c"></div>
          </div>

          <div class="recap-goals">
            <div class="goal-row">
              <span class="goal-label">Completed Appointments</span>
              <span class="goal-meter">
                <span class="goal-bar goal-bar--blue" style="width:80%"></span>
              </span>
              <span class="goal-num">160/200</span>
            </div>

            <div class="goal-row">
              <span class="goal-label">Paid Consultations</span>
              <span class="goal-meter">
                <span class="goal-bar goal-bar--red" style="width:60%"></span>
              </span>
              <span class="goal-num">310/400</span>
            </div>

            <div class="goal-row">
              <span class="goal-label">Online Check-ins</span>
              <span class="goal-meter">
                <span class="goal-bar goal-bar--yellow" style="width:90%"></span>
              </span>
              <span class="goal-num">480/500</span>
            </div>

            <div class="goal-row">
              <span class="goal-label">New Patient Leads</span>
              <span class="goal-meter">
                <span class="goal-bar goal-bar--green" style="width:50%"></span>
              </span>
              <span class="goal-num">250/500</span>
            </div>
          </div>
        </div>

        <div class="recap-stats">
          <div class="recap-stat">
            <div class="recap-stat__value">$35,230.43</div>
            <div class="recap-stat__label">TOTAL REVENUE</div>
            <div class="recap-stat__delta recap-stat__delta--green">+17%</div>
          </div>
          <div class="recap-stat">
            <div class="recap-stat__value">$10,390.90</div>
            <div class="recap-stat__label">TOTAL COST</div>
            <div class="recap-stat__delta recap-stat__delta--yellow">+9%</div>
          </div>
          <div class="recap-stat">
            <div class="recap-stat__value">$24,813.53</div>
            <div class="recap-stat__label">TOTAL PROFIT</div>
            <div class="recap-stat__delta recap-stat__delta--green">+20%</div>
          </div>
          <div class="recap-stat">
            <div class="recap-stat__value">1,200</div>
            <div class="recap-stat__label">GOAL COMPLETIONS</div>
            <div class="recap-stat__delta recap-stat__delta--red">-18%</div>
          </div>
        </div>
      </div>

      <!-- Stacked stat tiles -->
      <div class="panel panel--stats">
        <div class="stat-tile stat-tile--yellow">
          <div class="stat-tile__label">Inventory</div>
          <div class="stat-tile__value">5,200</div>
        </div>
        <div class="stat-tile stat-tile--green">
          <div class="stat-tile__label">Messages</div>
          <div class="stat-tile__value">92,050</div>
        </div>
        <div class="stat-tile stat-tile--red">
          <div class="stat-tile__label">Downloads</div>
          <div class="stat-tile__value">114,381</div>
        </div>
        <div class="stat-tile stat-tile--blue">
          <div class="stat-tile__label">Direct Messages</div>
          <div class="stat-tile__value">163,921</div>
        </div>
      </div>
    </section>


    <!-- Second row: Latest users + Browser usage -->
    <section class="admin-row">
      <!-- Latest Members -->
      <div class="panel">
        <div class="panel__head">
          <div class="panel__title">Latest Clinics</div>
          <div class="panel__meta">+ 3 new this week</div>
        </div>

        <ul class="latest-list">
          <li class="latest-item">
            <div class="latest-avatar"><span>A</span></div>
            <div class="latest-info">
              <div class="latest-name">Alpha Health Group</div>
              <div class="latest-meta">Dermatology • NY, USA</div>
            </div>
            <div class="latest-time">2h ago</div>
          </li>

          <li class="latest-item">
            <div class="latest-avatar"><span>C</span></div>
            <div class="latest-info">
              <div class="latest-name">City Heart Clinic</div>
              <div class="latest-meta">Cardiology • London, UK</div>
            </div>
            <div class="latest-time">5h ago</div>
          </li>

          <li class="latest-item">
            <div class="latest-avatar"><span>S</span></div>
            <div class="latest-info">
              <div class="latest-name">Sunrise Pediatrics</div>
              <div class="latest-meta">Pediatrics • Sydney, AU</div>
            </div>
            <div class="latest-time">1d ago</div>
          </li>
        </ul>
      </div>

      <!-- Browser Usage / traffic mix -->
      <div class="panel">
        <div class="panel__head">
          <div class="panel__title">Traffic Split</div>
          <div class="panel__meta">Last 7 days</div>
        </div>

        <div class="traffic">
          <div class="traffic__donut">
            <div class="traffic__donut-inner">
              <div class="traffic__donut-hole">62%</div>
            </div>
          </div>
          <ul class="traffic__legend">
            <li><span class="legend-dot legend-dot--blue"></span> Chrome</li>
            <li><span class="legend-dot legend-dot--green"></span> iOS App</li>
            <li><span class="legend-dot legend-dot--yellow"></span> Safari</li>
            <li><span class="legend-dot legend-dot--red"></span> Android App</li>
            <li><span class="legend-dot legend-dot--gray"></span> Other</li>
          </ul>
        </div>
      </div>
    </section>

    <footer class="admin-footer">
      <div>Copyright © <?= date('Y') ?> MediBook. All rights reserved.</div>
      <div class="admin-footer__ver">Version 1.0.0</div>
    </footer>

  </div><!-- /admin-main -->
</section>