-- ==========================
-- USERS (no 'doctor' users)
-- ==========================
INSERT INTO Users (email, username, password_hash, full_name, role)
VALUES
('admin01@mail.com',   'admin01',   'hash_pw1',  'Nguyen Van Admin',     'admin'),
('support01@mail.com', 'support01', 'hash_pw2',  'Tran Thi Support',     'webstaff'),
('dev01@mail.com',     'dev01',     'hash_pw3',  'John Doe',             'webstaff'),
('design01@mail.com',  'design01',  'hash_pw4',  'Emma Brown',           'webstaff'),

('patient01@mail.com', 'patient01', 'hash_pw5',  'Nguyen Van An',        'patient'),
('patient02@mail.com', 'patient02', 'hash_pw6',  'Tran Thi Bich',        'patient'),
('patient03@mail.com', 'patient03', 'hash_pw7',  'Le Van Cuong',         'patient'),
('patient04@mail.com', 'patient04', 'hash_pw8',  'Pham Thi Dao',         'patient'),
('patient05@mail.com', 'patient05', 'hash_pw9',  'Hoang Van Duc',        'patient'),
('patient06@mail.com', 'patient06', 'hash_pw10', 'Do Thi Lan',           'patient'),
('patient07@mail.com', 'patient07', 'hash_pw11', 'Bui Van Hung',         'patient'),
('patient08@mail.com', 'patient08', 'hash_pw12', 'Dang Thi Mai',         'patient'),
('patient09@mail.com', 'patient09', 'hash_pw13', 'Nguyen Van Nam',       'patient'),
('patient10@mail.com', 'patient10', 'hash_pw14', 'Tran Thi Oanh',        'patient'),
('patient11@mail.com', 'patient11', 'hash_pw15', 'Oliver Smith',         'patient'),
('patient12@mail.com', 'patient12', 'hash_pw16', 'Sophia Johnson',       'patient'),
('patient13@mail.com', 'patient13', 'hash_pw17', 'James Miller',         'patient'),
('patient14@mail.com', 'patient14', 'hash_pw18', 'Isabella Davis',       'patient'),
('patient15@mail.com', 'patient15', 'hash_pw19', 'William Wilson',       'patient'),
('patient16@mail.com', 'patient16', 'hash_pw20', 'Mia Taylor',           'patient'),
('patient17@mail.com', 'patient17', 'hash_pw21', 'Benjamin Anderson',    'patient'),
('patient18@mail.com', 'patient18', 'hash_pw22', 'Charlotte Thomas',     'patient'),
('patient19@mail.com', 'patient19', 'hash_pw23', 'Lucas Martinez',       'patient'),
('patient20@mail.com', 'patient20', 'hash_pw24', 'Amelia Garcia',        'patient'),

('office01@mail.com',  'office01',  'hash_pw25', 'Hanoi Central Hospital','office'),
('office02@mail.com',  'office02',  'hash_pw26', 'Saigon Clinic',        'office'),
('office03@mail.com',  'office03',  'hash_pw27', 'EuroCare Medical Center','office'),
('office04@mail.com',  'office04',  'hash_pw28', 'New York General Hospital','office');

-- user_id: 1..28 theo thứ tự trên

-- ==========================
-- USER PHONE (1..28)
-- ==========================
INSERT INTO User_phone (user_id, phone)
VALUES
(1,'0901111111'), (2,'0902222222'), (3,'0903333333'), (4,'0904444444'),
(5,'0905555555'), (6,'0906666666'), (7,'0907777777'), (8,'0908888888'),
(9,'0909999999'), (10,'0910000000'), (11,'0911111111'), (12,'0912222222'),
(13,'0913333333'), (14,'0914444444'), (15,'0915555555'), (16,'0916666666'),
(17,'0917777777'), (18,'0918888888'), (19,'0919999999'), (20,'0920000000'),
(21,'0921111111'), (22,'0922222222'), (23,'0923333333'), (24,'0924444444'),
(25,'0925555555'), (26,'0926666666'), (27,'0927777777'), (28,'0928888888');

-- ==========================
-- ADMIN / STAFF PROFILES
-- ==========================
INSERT INTO Admin (user_id) VALUES (1);

INSERT INTO Web_staff (user_id, position, status) VALUES
(2,'support','online'),
(3,'developer','offline'),
(4,'designer','online');

-- ==========================
-- SPECIALTIES
-- ==========================
INSERT INTO Medical_specialty (slug, name, blurb, description, image_url, is_featured, sort_order) VALUES
('general-practice', 'General Practice', 'Everyday care for you and your family.', NULL, 'images/general-practice.jpg', 1, 10),
('pediatrics', 'Pediatrics', 'Focused on children’s health and growth.', NULL, 'images/pediatrics.jpg', 1, 20),
('internal-medicine', 'Internal Medicine', 'Care for adult health and chronic issues.', NULL, 'images/internal-medicine.jpg', 1, 30),
('obgyn', 'Obstetrics & Gynecology', 'Comprehensive women’s health care.', NULL, 'images/obgyn.jpg', 1, 40),
('dermatology', 'Dermatology', 'Expert care for skin, hair, and nails.', NULL, 'images/dermatology.jpg', 1, 50),
('cardiology', 'Cardiology', 'Dedicated to heart and vascular health.', NULL, 'images/cardiology.jpg', 1, 60);

-- ==========================
-- OFFICES (users 25..28)
-- ==========================
INSERT INTO Office (user_id, name, address, website, logo, description, status) VALUES
(25,'Hanoi Central Hospital','123 Pho Hue, Hanoi','www.hch.vn','logo1.png','Top hospital in Hanoi','approved'),
(26,'Saigon Clinic','456 Le Loi, HCMC','www.sgclinic.vn','logo2.png','Private clinic in Saigon','approved'),
(27,'EuroCare Medical Center','12 Oxford St, London','www.eurocare.uk','logo3.png','European medical center','approved'),
(28,'New York General Hospital','789 Broadway, NYC','www.nygh.com','logo4.png','US based hospital','approved');

-- ==========================
-- OFFICE SPECIALTIES / PHONES
-- ==========================
INSERT INTO Office_has_specialty (office_id, specialty_id) VALUES
(1,1),(1,2),
(2,3),(2,4),
(3,1),(3,5),
(4,2),(4,3);

INSERT INTO Office_phone (office_id, phone) VALUES
(1,'0241111111'),
(2,'0282222222'),
(3,'+442033333333'),
(4,'+12124444444');

-- ==========================
-- DOCTORS (no user_id)
-- ==========================
INSERT INTO Doctor (office_id, doctor_name, photo, degree, graduate, specialty_id) VALUES
(1,'Dr. Nguyen Anh Minh','photo1.jpg','MD','Hanoi Medical University',1),
(1,'Dr. Tran Thi Lan','photo2.jpg','MD','Hue Medical College',2),
(2,'Dr. Le Quang Huy','photo3.jpg','PhD','HCMC University of Medicine',3),
(2,'Dr. Pham Gia Bao','photo4.jpg','MD','Thai Binh Medical University',4),
(2,'Dr. Vo Kim Ngan','photo5.jpg','MD','Can Tho Medical University',5),
(3,'Dr. Hoang Duc Long','photo6.jpg','MD','Hanoi Medical University',6),
(3,'Dr. Mai Yuki','photo7.jpg','PhD','University of Tokyo',1),
(3,'Dr. Park Ji Soo','photo8.jpg','MD','Seoul National University',2),
(4,'Dr. John Smith','photo9.jpg','MD','Harvard Medical School',3),
(4,'Dr. Emily Brown','photo10.jpg','MD','Oxford University',4),
(4,'Dr. William Green','photo11.jpg','MD','Cambridge University',5),
(1,'Dr. Sarah Johnson','photo12.jpg','PhD','Stanford University',6),
(1,'Dr. Nguyen Thu Trang','photo13.jpg','MD','University of Toronto',1),
(2,'Dr. David Nguyen','photo14.jpg','MD','University of Sydney',2),
(3,'Dr. Phan Minh Chau','photo15.jpg','MD','Yale University',3),
(3,'Dr. Tran Hoai Nam','photo16.jpg','MD','Columbia University',4);

-- ==========================
-- PATIENT PROFILES (users 5..24)
-- ==========================
INSERT INTO Patient (user_id, date_of_birth, status, payment) VALUES
(5 ,'1990-01-10','active','insurance'),
(6 ,'1985-02-15','active','cash'),
(7 ,'2000-03-20','inactive','insurance'),
(8 ,'1995-04-25','active','credit card'),
(9 ,'1988-05-30','active','cash'),
(10,'1992-06-05','inactive','insurance'),
(11,'1991-07-12','active','insurance'),
(12,'1987-08-18','active','cash'),
(13,'1993-09-24','active','credit card'),
(14,'1994-10-02','inactive','insurance'),
(15,'1996-11-11','active','insurance'),
(16,'1989-12-19','active','cash'),
(17,'1997-01-21','active','credit card'),
(18,'1998-02-28','inactive','insurance'),
(19,'1990-03-15','active','insurance'),
(20,'1992-04-07','active','cash'),
(21,'1995-05-05','active','insurance'),
(22,'1996-06-06','active','cash'),
(23,'1997-07-07','active','credit card'),
(24,'1998-08-08','inactive','insurance');

-- ==========================
-- APPOINTMENT SLOTS (doctor_id only)
-- ==========================
INSERT INTO Appointment_slot (doctor_id, start_time, end_time, status) VALUES
(1,'2025-10-01 09:00:00','2025-10-01 09:30:00','available'),
(1,'2025-10-01 10:00:00','2025-10-01 10:30:00','available'),
(3,'2025-10-02 09:00:00','2025-10-02 09:30:00','available'),
(3,'2025-10-02 10:00:00','2025-10-02 10:30:00','unavailable'),
(7,'2025-10-03 09:00:00','2025-10-03 09:30:00','available'),
(7,'2025-10-03 10:00:00','2025-10-03 10:30:00','available'),
(9,'2025-10-04 09:00:00','2025-10-04 09:30:00','available'),
(9,'2025-10-04 10:00:00','2025-10-04 10:30:00','unavailable');

-- ==========================
-- APPOINTMENTS
-- ==========================
INSERT INTO Appointment (patient_id, slot_id, status) VALUES
(1,1,'booked'),
(2,2,'booked'),
(3,3,'completed'),
(4,4,'canceled'),
(5,5,'booked'),
(6,6,'no-show'),
(7,7,'booked'),
(8,8,'completed');

-- ==========================
-- REPORTS / HANDLED
-- ==========================
INSERT INTO Problem_report (description, patient_id, doctor_id, office_id) VALUES
('Late appointment',1,NULL,NULL),
('Doctor absent',NULL,1,NULL),
('Office closed without notice',NULL,NULL,1),
('Billing issue',2,NULL,NULL),
('Rude staff',NULL,NULL,2),
('Doctor misdiagnosis',NULL,3,NULL),
('Insurance rejected',3,NULL,NULL),
('System error booking',4,NULL,NULL),
('Office too crowded',NULL,NULL,3),
('Payment double charged',5,NULL,NULL);

INSERT INTO Handled (staff_id, report_id, description_action, status) VALUES
(1,1,'Checked with doctor','resolved'),
(1,2,'Updated schedule','resolved'),
(2,3,'Fixed office info','resolved'),
(2,4,'Escalated billing','open'),
(3,5,'Investigated staff report','dismissed'),
(3,6,'Requested re-evaluation','open'),
(2,7,'Contacted insurance','open'),
(1,8,'Restarted booking system','resolved');

-- ==========================
-- CONTACTS
-- ==========================
INSERT INTO Contact (name, email, subject, message) VALUES
('Nguyen Thanh','nthanh@mail.com','Inquiry','I want to know about cardiology service'),
('Tran Hoa','thoa@mail.com','Feedback','The booking page is very helpful'),
('David Brown','dbrown@mail.com','Complaint','Doctor was late for my appointment'),
('Le Minh','lminh@mail.com','Support','Cannot log into my account');
