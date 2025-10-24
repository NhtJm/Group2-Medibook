DROP DATABASE IF EXISTS doctor_appointment_db;

CREATE DATABASE doctor_appointment_db;

USE doctor_appointment_db;

CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE User_phone (
    user_id INT,
    phone VARCHAR(20),
    PRIMARY KEY (user_id, phone),
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Web_staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('admin','support','developer','designer') NOT NULL,
    status ENUM('online','offline') DEFAULT 'offline',
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Patient (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_of_birth DATE,
    status ENUM('active','inactive') DEFAULT 'active',
    payment VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Medical_specialty (
    specialty_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE Office (
    office_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    website VARCHAR(255),
    logo VARCHAR(255),
    description TEXT,
    status ENUM('approved','pending','deactivated') DEFAULT 'pending'
);

CREATE TABLE Office_phone (
    office_id INT,
    phone VARCHAR(20),
    PRIMARY KEY (office_id, phone),
    FOREIGN KEY (office_id) REFERENCES Office(office_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Doctor (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    photo VARCHAR(255),
    degree VARCHAR(100),
    graduate VARCHAR(100),
    specialty_id INT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (specialty_id) REFERENCES Medical_specialty(specialty_id)
        ON DELETE SET NULL ON UPDATE CASCADE
);


# Change log: Allow doctors to be associated with multiple offices
CREATE TABLE Doctor_office (
    doctor_id INT,
    office_id INT,
    PRIMARY KEY (doctor_id, office_id),
    FOREIGN KEY (doctor_id) REFERENCES Doctor(doctor_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (office_id) REFERENCES Office(office_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Slots_free (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    office_id INT NOT NULL,
    doctor_id INT NOT NULL,
    start DATETIME NOT NULL,
    end DATETIME NOT NULL,
    status ENUM('available','unavailable') DEFAULT 'available',
    FOREIGN KEY (office_id) REFERENCES Office(office_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES Doctor(doctor_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Appointment (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    slot_id INT NOT NULL,
    status ENUM('booked','canceled','completed','no-show') DEFAULT 'booked',
    FOREIGN KEY (patient_id) REFERENCES Patient(patient_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES Slots_free(slot_id)
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

-- CREATE TABLE Problem_report (
--     report_id INT AUTO_INCREMENT PRIMARY KEY,
--     description_action TEXT,
--     status ENUM('open','resolved','dismissed') DEFAULT 'open',
--     action_date DATETIME DEFAULT CURRENT_TIMESTAMP
-- );

-- CREATE TABLE Patient_report (
--     patient_id INT,
--     report_id INT,
--     PRIMARY KEY (patient_id, report_id),
--     FOREIGN KEY (patient_id) REFERENCES Patient(patient_id)
--         ON DELETE CASCADE ON UPDATE CASCADE,
--     FOREIGN KEY (report_id) REFERENCES Problem_report(report_id)
--         ON DELETE CASCADE ON UPDATE CASCADE
-- );

-- CREATE TABLE Doctor_report (
--     doctor_id INT,
--     report_id INT,
--     PRIMARY KEY (doctor_id, report_id),
--     FOREIGN KEY (doctor_id) REFERENCES Doctor(doctor_id)
--         ON DELETE CASCADE ON UPDATE CASCADE,
--     FOREIGN KEY (report_id) REFERENCES Problem_report(report_id)
--         ON DELETE CASCADE ON UPDATE CASCADE
-- );

-- CREATE TABLE Office_report (
--     office_id INT,
--     report_id INT,
--     PRIMARY KEY (office_id, report_id),
--     FOREIGN KEY (office_id) REFERENCES Office(office_id)
--         ON DELETE CASCADE ON UPDATE CASCADE,
--     FOREIGN KEY (report_id) REFERENCES Problem_report(report_id)
--         ON DELETE CASCADE ON UPDATE CASCADE
-- );
