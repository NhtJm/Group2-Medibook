DROP DATABASE IF EXISTS doctor_appointment_db;
CREATE DATABASE doctor_appointment_db;
USE doctor_appointment_db;

CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('admin','webstaff','office','patient') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE User_phone (
    user_id INT,
    phone VARCHAR(20),
    PRIMARY KEY (user_id, phone),
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Web_staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    position ENUM('support','developer','designer') NOT NULL,
    status ENUM('online','offline') DEFAULT 'offline',
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Patient (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    date_of_birth DATE,
    status ENUM('active','inactive') DEFAULT 'active',
    payment VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Medical_specialty (
    specialty_id INT AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(120) NOT NULL UNIQUE,
    name        VARCHAR(100) NOT NULL,
    blurb       VARCHAR(255) NULL,
    description TEXT NULL,
    image_url   VARCHAR(255) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order  INT NOT NULL DEFAULT 0
);

CREATE TABLE Office (
    office_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    website VARCHAR(255),
    logo VARCHAR(255),
    description TEXT,
    status ENUM('approved','pending','deactivated') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Office_has_specialty (
    office_id INT NOT NULL,
    specialty_id INT NOT NULL,
    PRIMARY KEY (office_id, specialty_id),
    FOREIGN KEY (office_id) REFERENCES Office(office_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (specialty_id) REFERENCES Medical_specialty(specialty_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Office_phone (
    office_id INT,
    phone VARCHAR(20),
    PRIMARY KEY (office_id, phone),
    FOREIGN KEY (office_id) REFERENCES Office(office_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Doctor (
  doctor_id    INT AUTO_INCREMENT PRIMARY KEY,
  office_id    INT NOT NULL,
  doctor_name  VARCHAR(150) NOT NULL,
  photo        VARCHAR(255),
  degree       VARCHAR(100),
  graduate     VARCHAR(100),
  specialty_id INT NULL,
  FOREIGN KEY (office_id)    REFERENCES Office(office_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (specialty_id) REFERENCES Medical_specialty(specialty_id)
    ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE Appointment_slot (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time   DATETIME NOT NULL,
    status ENUM('available','unavailable') DEFAULT 'available',
    FOREIGN KEY (doctor_id) REFERENCES Doctor(doctor_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Appointment (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    slot_id INT NOT NULL,
    status ENUM('booked','canceled','completed','no-show') DEFAULT 'booked',
    booked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES Patient(patient_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES Appointment_slot(slot_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Problem_report (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT,
    date_report DATETIME DEFAULT CURRENT_TIMESTAMP,
    patient_id INT NULL,
    doctor_id INT NULL,
    office_id INT NULL,
    FOREIGN KEY (patient_id) REFERENCES Patient(patient_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES Doctor(doctor_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (office_id) REFERENCES Office(office_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CHECK (
        (patient_id IS NOT NULL) + (doctor_id IS NOT NULL) + (office_id IS NOT NULL) = 1
    )
);

CREATE TABLE Handled (
    staff_id INT,
    report_id INT,
    description_action TEXT,
    action_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('open','resolved','dismissed') DEFAULT 'open',
    PRIMARY KEY (staff_id, report_id),
    FOREIGN KEY (staff_id) REFERENCES Web_staff(staff_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (report_id) REFERENCES Problem_report(report_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Contact (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new','read','replied') DEFAULT 'new'
);
