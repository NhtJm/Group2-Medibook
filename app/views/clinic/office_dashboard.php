<?php // app/views/user/office_dashboard.php ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Clinic Dashboard • MediBook</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/office_dashboard.css">
</head>

<body class="office-body">


    <main class="office-main">
        <div class="office-main__inner">

            <!-- Dashboard Title / stat cards row -->
            <section class="office-heading">
                <h1 class="office-heading__title">Dashboard</h1>
                <div class="office-heading__bar"></div>

                <div class="office-stats">
                    <div class="office-stats__card">
                        <div class="office-stats__icon">
                            <!-- simple user-group icon -->
                            <div class="icon-people" aria-hidden="true">👥</div>
                        </div>
                        <div class="office-stats__meta">
                            <div class="office-stats__number">2</div>
                            <div class="office-stats__label">Doctors</div>
                        </div>
                    </div>

                    <div class="office-stats__card">
                        <div class="office-stats__icon">
                            <div class="icon-calendar" aria-hidden="true">📅</div>
                        </div>
                        <div class="office-stats__meta">
                            <div class="office-stats__number">4</div>
                            <div class="office-stats__label">Appointments Today</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Doctors panel -->
            <section class="office-panel">
                <div class="office-panel__head">
                    <h2 class="office-panel__title">Doctors</h2>

                    <button class="pill-btn pill-btn--dark">
                        Add a Doctor
                    </button>
                </div>

                <div class="doctor-list">

                    <!-- Doctor row -->
                    <div class="doctor-item">
                        <div class="doctor-item__info">
                            <div class="doctor-item__avatar">
                                <img src="https://via.placeholder.com/48x48.png?text=Dr" alt="Dr. Michael Lee">
                            </div>
                            <div class="doctor-item__text">
                                <div class="doctor-item__name">Dr. Michael Lee</div>
                                <div class="doctor-item__spec">Dermatologist</div>
                            </div>
                        </div>

                        <div class="doctor-item__actions">
                            <button class="mini-pill-btn">Edit</button>
                            <button class="mini-pill-btn">Future Appointments</button>
                            <button class="mini-pill-btn">Manage Schedule</button>
                        </div>
                    </div>

                    <!-- Doctor row -->
                    <div class="doctor-item">
                        <div class="doctor-item__info">
                            <div class="doctor-item__avatar">
                                <img src="https://via.placeholder.com/48x48.png?text=Dr" alt="Dr. Robert Harris">
                            </div>
                            <div class="doctor-item__text">
                                <div class="doctor-item__name">Dr. Robert Harris</div>
                                <div class="doctor-item__spec">Psychiatrist</div>
                            </div>
                        </div>

                        <div class="doctor-item__actions">
                            <button class="mini-pill-btn">Edit</button>
                            <button class="mini-pill-btn">Future Appointments</button>
                            <button class="mini-pill-btn"><a class="btn-schedule"
                                    href="<?= rtrim(BASE_URL, '/') ?>/index.php?page=doctor_schedule&doctor_id=123">
                                    Manage Schedule
                                </a></button>
                        </div>
                    </div>

                </div><!-- /doctor-list -->
            </section>

            <!-- Next Appointments panel -->
            <section class="office-panel">
                <div class="office-panel__head">
                    <h2 class="office-panel__title">Next Appointments</h2>
                </div>

                <div class="appt-list">

                    <!-- Appointment row -->
                    <div class="appt-item">
                        <div class="appt-item__patient">
                            <div class="appt-item__name">Emma Johnson</div>
                            <div class="appt-item__dob">12/08/1997</div>
                        </div>

                        <div class="appt-item__doctor">
                            <div class="appt-item__docname">Dr. Michael Lee</div>
                            <div class="appt-item__spec">Dermatologist</div>
                        </div>

                        <div class="appt-item__datetime">
                            <div class="appt-item__date">
                                <span class="appt-icon" aria-hidden="true">📅</span>
                                <span>23-08-2025</span>
                            </div>
                            <div class="appt-item__time">
                                <span class="appt-icon" aria-hidden="true">⏰</span>
                                <span>3:00 pm</span>
                            </div>
                        </div>

                        <div class="appt-item__action">
                            <button class="mini-pill-btn">Edit</button>
                        </div>
                    </div>

                    <!-- Appointment row -->
                    <div class="appt-item">
                        <div class="appt-item__patient">
                            <div class="appt-item__name">Daniel Harris</div>
                            <div class="appt-item__dob">30/08/2000</div>
                        </div>

                        <div class="appt-item__doctor">
                            <div class="appt-item__docname">Dr. Robert Harris</div>
                            <div class="appt-item__spec">Psychiatrist</div>
                        </div>

                        <div class="appt-item__datetime">
                            <div class="appt-item__date">
                                <span class="appt-icon" aria-hidden="true">📅</span>
                                <span>23-08-2025</span>
                            </div>
                            <div class="appt-item__time">
                                <span class="appt-icon" aria-hidden="true">⏰</span>
                                <span>3:00 pm</span>
                            </div>
                        </div>

                        <div class="appt-item__action">
                            <button class="mini-pill-btn">Edit</button>
                        </div>
                    </div>

                    <!-- Appointment row -->
                    <div class="appt-item">
                        <div class="appt-item__patient">
                            <div class="appt-item__name">Sophia Miller</div>
                            <div class="appt-item__dob">23/05/1979</div>
                        </div>

                        <div class="appt-item__doctor">
                            <div class="appt-item__docname">Dr. Michael Lee</div>
                            <div class="appt-item__spec">Dermatologist</div>
                        </div>

                        <div class="appt-item__datetime">
                            <div class="appt-item__date">
                                <span class="appt-icon" aria-hidden="true">📅</span>
                                <span>23-08-2025</span>
                            </div>
                            <div class="appt-item__time">
                                <span class="appt-icon" aria-hidden="true">⏰</span>
                                <span>3:30 pm</span>
                            </div>
                        </div>

                        <div class="appt-item__action">
                            <button class="mini-pill-btn">Edit</button>
                        </div>
                    </div>

                    <!-- Appointment row -->
                    <div class="appt-item">
                        <div class="appt-item__patient">
                            <div class="appt-item__name">James Wilson</div>
                            <div class="appt-item__dob">03/07/2001</div>
                        </div>

                        <div class="appt-item__doctor">
                            <div class="appt-item__docname">Dr. Robert Harris</div>
                            <div class="appt-item__spec">Psychiatrist</div>
                        </div>

                        <div class="appt-item__datetime">
                            <div class="appt-item__date">
                                <span class="appt-icon" aria-hidden="true">📅</span>
                                <span>23-08-2025</span>
                            </div>
                            <div class="appt-item__time">
                                <span class="appt-icon" aria-hidden="true">⏰</span>
                                <span>4:00 pm</span>
                            </div>
                        </div>

                        <div class="appt-item__action">
                            <button class="mini-pill-btn">Edit</button>
                        </div>
                    </div>

                </div><!-- /appt-list -->
            </section>

        </div><!-- /office-main__inner -->
    </main>

</body>

</html>