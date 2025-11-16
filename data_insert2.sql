USE doctor_appointment_db;
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
INSERT INTO Doctor (office_id, doctor_name, email, photo, degree, graduate, specialty_id) VALUES
(1,'Dr. Nguyen Anh Minh','hung.nguyentien2004@gmail.com','photo1.jpg','MD','Hanoi Medical University',1),
(1,'Dr. Tran Thi Lan','tran.thi.lan@hch.vn','photo2.jpg','MD','Hue Medical College',2),

(2,'Dr. Le Quang Huy','le.quang.huy@sgclinic.vn','photo3.jpg','PhD','HCMC University of Medicine',3),
(2,'Dr. Pham Gia Bao','pham.gia.bao@sgclinic.vn','photo4.jpg','MD','Thai Binh Medical University',4),
(2,'Dr. Vo Kim Ngan','vo.kim.ngan@sgclinic.vn','photo5.jpg','MD','Can Tho Medical University',5),

(3,'Dr. Hoang Duc Long','hoang.duc.long@eurocare.uk','photo6.jpg','MD','Hanoi Medical University',6),
(3,'Dr. Mai Yuki','mai.yuki@eurocare.uk','photo7.jpg','PhD','University of Tokyo',1),
(3,'Dr. Park Ji Soo','park.ji.soo@eurocare.uk','photo8.jpg','MD','Seoul National University',2),

(4,'Dr. John Smith','john.smith@nygh.com','photo9.jpg','MD','Harvard Medical School',3),
(4,'Dr. Emily Brown','emily.brown@nygh.com','photo10.jpg','MD','Oxford University',4),
(4,'Dr. William Green','william.green@nygh.com','photo11.jpg','MD','Cambridge University',5),

(1,'Dr. Sarah Johnson','sarah.johnson@hch.vn','photo12.jpg','PhD','Stanford University',6),
(1,'Dr. Nguyen Thu Trang','nguyen.thu.trang@hch.vn','photo13.jpg','MD','University of Toronto',1),

(2,'Dr. David Nguyen','david.nguyen@sgclinic.vn','photo14.jpg','MD','University of Sydney',2),

(3,'Dr. Phan Minh Chau','phan.minh.chau@eurocare.uk','photo15.jpg','MD','Yale University',3),
(3,'Dr. Tran Hoai Nam','tran.hoai.nam@eurocare.uk','photo16.jpg','MD','Columbia University',4);

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


-- 1) Add columns (slug nullable for now)
ALTER TABLE Office
  ADD COLUMN slug VARCHAR(120) NULL AFTER name,
  ADD COLUMN googleMap TEXT NULL AFTER address,
  ADD COLUMN rating DECIMAL(2,1) UNSIGNED NOT NULL DEFAULT 0.0 AFTER description;

-- 2) Backfill slug for existing rows (temporary scheme — adjust to your real slugs)
UPDATE Office
SET slug = CONCAT('office-', office_id)
WHERE slug IS NULL;

-- 3) Enforce NOT NULL + UNIQUE on slug
ALTER TABLE Office
  MODIFY COLUMN slug VARCHAR(120) NOT NULL,
  ADD UNIQUE KEY uniq_office_slug (slug);


ALTER TABLE Appointment_slot
  MODIFY status ENUM('available','unavailable') NOT NULL DEFAULT 'available';

ALTER TABLE Appointment_slot
  ADD UNIQUE KEY uniq_doctor_start (doctor_id, start_time);

ALTER TABLE Appointment_slot
  ADD INDEX idx_slot_doctor_status_time (doctor_id, status, start_time);
  
INSERT INTO Users (email, username, password_hash, full_name, role) VALUES
('benh-vien-dai-hoc-y-duoc-tphcm-co-so-2@mail.com','benh-vien-dai-hoc-y-duoc-tphcm-co-so-2','hash_benh-vien-da_001','Bệnh viện Đại học Y Dược TP.HCM (Cơ sở 2)','office'),
('benh-vien-benh-nhiet-doi@mail.com','benh-vien-benh-nhiet-doi','hash_benh-vien-be_002','Bệnh viện Bệnh Nhiệt đới','office'),
('bvndgiadinh@mail.com','bvndgiadinh','hash_bvndgiadinh_003','Bệnh viện Nhân Dân Gia Định','office'),
('benh-vien-nhan-dan-115@mail.com','benh-vien-nhan-dan-115','hash_benh-vien-nh_004','Bệnh viện Nhân Dân 115','office'),
('hoanmytd@mail.com','hoanmytd','hash_hoanmytd_005','Bệnh viện Đa khoa Quốc Tế Hoàn Mỹ Thủ Đức','office'),
('benh-vien-da-khoa-singapore@mail.com','benh-vien-da-khoa-singapore','hash_benh-vien-da_006','Bệnh viện đa khoa Singapore (Singapore General Hospital)','office'),
('benh-vien-fv@mail.com','benh-vien-fv','hash_benh-vien-fv_007','Bệnh viện FV (FV Hospital)','office'),
('benh-vien-hoan-my-sai-gon@mail.com','benh-vien-hoan-my-sai-gon','hash_benh-vien-ho_008','Bệnh viện Hoàn Mỹ Sài Gòn','office'),
('bv-ung-buou-hung-viet@mail.com','bv-ung-buou-hung-viet','hash_bv-ung-buou-_009','Bệnh viện Ung bướu Hưng Việt','office'),
('phong-kham-acc-ben-thanh@mail.com','phong-kham-acc-ben-thanh','hash_phong-kham-a_010','Phòng khám ACC Bến Thành - HCM','office'),
('phong-kham-acc-cho-lon@mail.com','phong-kham-acc-cho-lon','hash_phong-kham-a_011','Phòng khám ACC Chợ Lớn - HCM','office'),
('phong-kham-acc-ha-noi@mail.com','phong-kham-acc-ha-noi','hash_phong-kham-a_012','Phòng khám ACC Hà Nội','office'),
('phong-kham-co-xuong-khop-bac-si-tri@mail.com','phong-kham-co-xuong-khop-bac-si-tri','hash_phong-kham-c_013','Phòng khám Cơ xương khớp chuyên sâu TTƯT.BS.CK2 Dương Minh Trí','office'),
('phong-kham-da-lieu-dr-choice@mail.com','phong-kham-da-lieu-dr-choice','hash_phong-kham-d_014','Phòng Khám Chuyên Khoa Da Liễu Dr. Choice Clinic','office'),
('dym-medical-center-ha-noi@mail.com','dym-medical-center-ha-noi','hash_dym-medical-_015','DYM Medical Center Hà Nội','office'),
('dym-medical-center-phu-my-hung@mail.com','dym-medical-center-phu-my-hung','hash_dym-medical-_016','DYM Medical Center Phú Mỹ Hưng','office'),
('dym-medical-center-sai-gon@mail.com','dym-medical-center-sai-gon','hash_dym-medical-_017','DYM Medical Center Sài Gòn','office'),
('phong-kham-giam-can-medfit@mail.com','phong-kham-giam-can-medfit','hash_phong-kham-g_018','Phòng khám MedFit - Phòng khám giảm cân chuyên sâu','office'),
('trung-tam-mat-nam-viet@mail.com','trung-tam-mat-nam-viet','hash_trung-tam-ma_019','Trung Tâm Mắt Kỹ Thuật Cao Nam Việt','office'),
('drcheck@mail.com','drcheck','hash_drcheck_020','Trung Tâm Nội Soi Tiêu Hoá Doctor Check','office'),
('nhidong1@mail.com','nhidong1','hash_nhidong1_021','Bệnh viện Nhi Đồng 1','office'),
('bvmathcm@mail.com','bvmathcm','hash_bvmathcm_022','Bệnh Viện Mắt','office'),
('binhthanhhcm@mail.com','binhthanhhcm','hash_binhthanhhcm_023','Bệnh Viện Quận Bình Thạnh','office'),
('benh-vien-da-khoa-trung-my-tay@mail.com','benh-vien-da-khoa-trung-my-tay','hash_benh-vien-da_024','Bệnh viện Đa Khoa Trung Mỹ Tây','office'),
('dalieuhcm@mail.com','dalieuhcm','hash_dalieuhcm_025','Bệnh viện Da Liễu TP.HCM','office'),
('benh-vien-phu-san-thanh-pho-can-tho@mail.com','benh-vien-phu-san-thanh-pho-can-tho','hash_benh-vien-ph_026','Bệnh viện Phụ sản Thành Phố Cần Thơ','office'),
('trungvuong@mail.com','trungvuong','hash_trungvuong_027','Bệnh viện Trưng Vương','office'),
('bv-vung-tau@mail.com','bv-vung-tau','hash_bv-vung-tau_028','Bệnh viện Vũng Tàu','office'),
('benh-vien-chan-thuong-chinh-hinh-hcm@mail.com','benh-vien-chan-thuong-chinh-hinh-hcm','hash_benh-vien-ch_029','Bệnh viện Chấn Thương Chỉnh Hình TP.HCM','office'),
('gensolution2@mail.com','gensolution2','hash_gensolution2_030','Bệnh viện Đa khoa Tiền Giang','office'),
('benh-vien-nguyen-trai@mail.com','benh-vien-nguyen-trai','hash_benh-vien-ng_031','Bệnh viện Nguyễn Trãi','office'),
('nhidonghcm@mail.com','nhidonghcm','hash_nhidonghcm_032','Bệnh viện Nhi Đồng Thành Phố','office'),
('benh-vien-quan-1@mail.com','benh-vien-quan-1','hash_benh-vien-qu_033','Bệnh viện Quận 1 - Cơ sở 1','office'),
('benh-vien-dai-hoc-y-duoc-co-so-3@mail.com','benh-vien-dai-hoc-y-duoc-co-so-3','hash_benh-vien-da_034','Bệnh viện Đại học Y Dược TP.HCM (Y học cổ truyền kết hợp y học hiện đại)','office'),
('cho-ray@mail.com','cho-ray','hash_cho-ray_035','Bệnh viện Chợ Rẫy','office'),
('benh-vien-tim-mach-thanh-pho-can-tho@mail.com','benh-vien-tim-mach-thanh-pho-can-tho','hash_benh-vien-ti_036','Bệnh viện Tim mạch Thành phố Cần Thơ','office'),
('phong-kham-nhi-bs-my-na@mail.com','phong-kham-nhi-bs-my-na','hash_phong-kham-n_037','Phòng khám Nhi BS Võ Thị My Na','office'),
('benh-vien-quan-go-vap@mail.com','benh-vien-quan-go-vap','hash_benh-vien-qu_038','Bệnh viện Đa Khoa Gò Vấp','office'),
('benh-vien-thong-nhat@mail.com','benh-vien-thong-nhat','hash_benh-vien-th_039','Bệnh viện Thống Nhất','office'),
('bvnhidongct@mail.com','bvnhidongct','hash_bvnhidongct_040','Bệnh viện Nhi đồng Thành phố Cần Thơ','office'),
('benh-vien-da-khoa-thanh-pho-can-tho@mail.com','benh-vien-da-khoa-thanh-pho-can-tho','hash_benh-vien-da_041','Bệnh viện Đa khoa Thành phố Cần Thơ','office'),
('benh-vien-da-khoa-dong-nai@mail.com','benh-vien-da-khoa-dong-nai','hash_benh-vien-da_042','Bệnh viện Đa khoa Đồng Nai','office'),
('bvungbuouct@mail.com','bvungbuouct','hash_bvungbuouct_043','Bệnh Viện Ung Bướu Thành Phố Cần Thơ','office'),
('benh-vien-quan-1-cs2@mail.com','benh-vien-quan-1-cs2','hash_benh-vien-qu_044','Bệnh viện Quận 1 - Cơ sở 2','office'),
('dat-kham-khu-vuc-an-giang@mail.com','dat-kham-khu-vuc-an-giang','hash_dat-kham-khu_045','Bệnh viện Đa khoa Khu vực Tỉnh An Giang','office'),
('van-hanh@mail.com','van-hanh','hash_van-hanh_046','Bệnh viện Đa khoa Vạn Hạnh','office'),
('dkanphuoc@mail.com','dkanphuoc','hash_dkanphuoc_047','Bệnh viện Đa Khoa An Phước','office'),
('bvdkhanoi@mail.com','bvdkhanoi','hash_bvdkhanoi_048','Bệnh Viện Đa Khoa Hà Nội','office'),
('benh-vien-da-khoa-thot-not@mail.com','benh-vien-da-khoa-thot-not','hash_benh-vien-da_049','Trung tâm Y tế khu vực Thốt Nốt','office'),
('thanhvubl@mail.com','thanhvubl','hash_thanhvubl_050','Bệnh viện Đa khoa Thanh Vũ Medic Bạc Liêu','office'),
('bvnhattan@mail.com','bvnhattan','hash_bvnhattan_051','Bệnh viện Nhật Tân','office'),
('pkdkivy@mail.com','pkdkivy','hash_pkdkivy_052','Phòng Khám Đa Khoa Quốc Tế Ivy Health International Clinic','office'),
('benh-vien-da-khoa-o-mon@mail.com','benh-vien-da-khoa-o-mon','hash_benh-vien-da_053','Trung tâm Y tế Khu vực Ô Môn','office'),
('benh-vien-huyet-hoc-truyen-mau-can-tho@mail.com','benh-vien-huyet-hoc-truyen-mau-can-tho','hash_benh-vien-hu_054','Bệnh viện Huyết học - Truyền máu Thành phố Cần Thơ','office'),
('benh-vien-quan-dan-y-can-tho@mail.com','benh-vien-quan-dan-y-can-tho','hash_benh-vien-qu_055','Bệnh viện Quân Dân Y Thành Phố Cần Thơ','office'),
('benh-vien-tam-than-can-tho@mail.com','benh-vien-tam-than-can-tho','hash_benh-vien-ta_056','Bệnh viện Tâm Thần Thành phố Cần Thơ','office'),
('benh-vien-y-hoc-co-truyen-can-tho@mail.com','benh-vien-y-hoc-co-truyen-can-tho','hash_benh-vien-y-_057','Bệnh viện Y học cổ truyền Cần Thơ','office'),
('phong-kham-da-khoa-phap-anh@mail.com','phong-kham-da-khoa-phap-anh','hash_phong-kham-d_058','Phòng Khám Đa Khoa Pháp Anh','office'),
('phong-kham-da-khoa-hang-xanh@mail.com','phong-kham-da-khoa-hang-xanh','hash_phong-kham-d_059','Phòng khám Đa khoa Quốc tế Hàng Xanh','office'),
('ttytcairang@mail.com','ttytcairang','hash_ttytcairang_060','Trung tâm Y tế Quận Cái Răng','office'),
('trung-tam-y-te-vinh-thanh@mail.com','trung-tam-y-te-vinh-thanh','hash_trung-tam-y-_061','Trung Tâm Y Tế Khu Vực Vĩnh Thạnh','office'),
('tytthanhtienct@mail.com','tytthanhtienct','hash_tytthanhtien_062','Trạm Y tế xã Thạnh Tiến','office'),
('tram-y-te-phuong-thuan-hung-can-tho@mail.com','tram-y-te-phuong-thuan-hung-can-tho','hash_tram-y-te-ph_063','Trạm Y tế phường Thuận Hưng','office'),
('phong-kham-y-hoc-co-truyen-y-khoa-thai-duong@mail.com','phong-kham-y-hoc-co-truyen-y-khoa-thai-duong','hash_phong-kham-y_064','Phòng khám Y Học Cổ Truyền Y Khoa Thái Dương','office'),
('bvdalieuct@mail.com','bvdalieuct','hash_bvdalieuct_065','Bệnh viện Da Liễu Cần Thơ','office'),
('cdcqltcct@mail.com','cdcqltcct','hash_cdcqltcct_066','Trung tâm Kiểm soát Bệnh tật Cần Thơ - Tiêm chủng','office'),
('bvbuudien@mail.com','bvbuudien','hash_bvbuudien_067','Bệnh viện Bưu Điện','office'),
('benh-vien-quan-11@mail.com','benh-vien-quan-11','hash_benh-vien-qu_068','Bệnh viện Đa khoa Lãnh Binh Thăng','office'),
('benhvien199@mail.com','benhvien199','hash_benhvien199_069','Bệnh Viện 199','office'),
('cdcct@mail.com','cdcct','hash_cdcct_070','Trung tâm Kiểm soát Bệnh tật Cần Thơ - Khám bệnh','office'),
('bvlaophoict@mail.com','bvlaophoict','hash_bvlaophoict_071','Bệnh viện Lao và Bệnh phổi Cần Thơ','office'),
('bvnsaigon@mail.com','bvnsaigon','hash_bvnsaigon_072','Bệnh viện Đa khoa Quốc tế Nam Sài Gòn','office'),
('ishiisaigon@mail.com','ishiisaigon','hash_ishiisaigon_073','Phòng khám đa khoa Nhật Bản - Ishii Sài Gòn','office'),
('bvthiennhandn@mail.com','bvthiennhandn','hash_bvthiennhand_074','Trung tâm Chẩn đoán Y khoa Kỹ thuật cao Thiện Nhân','office'),
('gentis@mail.com','gentis','hash_gentis_075','Trung tâm xét nghiệm Gentis','office'),
('pkduclinhlab@mail.com','pkduclinhlab','hash_pkduclinhlab_076','Phòng khám Đa khoa DUCLINH LAB','office'),
('tytthuongthanhct@mail.com','tytthuongthanhct','hash_tytthuongtha_077','Trạm Y tế phường Thường Thạnh','office'),
('hoanmyvp1@mail.com','hoanmyvp1','hash_hoanmyvp1_078','Bệnh viện Hoàn Mỹ Bình Dương (Vạn Phúc 1)','office'),
('quoctecity@mail.com','quoctecity','hash_quoctecity_079','Bệnh viện Quốc tế City - CIH','office'),
('bvgiaan@mail.com','bvgiaan','hash_bvgiaan_080','Bệnh viện Gia An 115','office'),
('bvmedicbd@mail.com','bvmedicbd','hash_bvmedicbd_081','Bệnh viện Đa Khoa Medic Bình Dương','office'),
('minhanh@mail.com','minhanh','hash_minhanh_082','Bệnh viện Quốc tế Minh Anh','office'),
('bvdhtt@mail.com','bvdhtt','hash_bvdhtt_083','BỆNH VIỆN ĐẠI HỌC Y TÂN TẠO','office'),
('bvthucuc@mail.com','bvthucuc','hash_bvthucuc_084','BỆNH VIỆN ĐA KHOA QUỐC TẾ THU CÚC','office'),
('bvanviet@mail.com','bvanviet','hash_bvanviet_085','Bệnh Viện Đa Khoa An Việt','office'),
('bvphusanquoctesaigon@mail.com','bvphusanquoctesaigon','hash_bvphusanquoc_086','Bệnh viện Phụ Sản Quốc Tế Sài Gòn','office'),
('bvhongha@mail.com','bvhongha','hash_bvhongha_087','Bệnh viện đa khoa Hồng Hà','office'),
('pkhieuphuc@mail.com','pkhieuphuc','hash_pkhieuphuc_088','Phòng Khám Nhi Đồng Hiếu Phúc','office'),
('trung-tam-y-khoa-pasteur-da-lat@mail.com','trung-tam-y-khoa-pasteur-da-lat','hash_trung-tam-y-_089','Trung tâm Y khoa Pasteur Đà Lạt','office'),
('sghealthcare@mail.com','sghealthcare','hash_sghealthcare_090','Phòng khám đa khoa Saigon Healthcare','office'),
('pkmenhealth@mail.com','pkmenhealth','hash_pkmenhealth_091','Trung Tâm Sức Khỏe Nam giới Men''s Health','office'),
('chac@mail.com','chac','hash_chac_092','Trung Tâm Chăm Sóc Sức Khoẻ Cộng Đồng - CHAC','office'),
('simmed@mail.com','simmed','hash_simmed_093','Phòng khám Đa khoa SIM Med','office'),
('pkdkanhao@mail.com','pkdkanhao','hash_pkdkanhao_094','Phòng Khám Đa Khoa Quốc Tế An Hảo','office'),
('medicsaigon@mail.com','medicsaigon','hash_medicsaigon_095','Phòng Khám Medic Sài Gòn','office'),
('pkdkgolden@mail.com','pkdkgolden','hash_pkdkgolden_096','Phòng Khám Đa khoa Quốc Tế Golden Healthcare','office'),
('pkdkdiamond@mail.com','pkdkdiamond','hash_pkdkdiamond_097','Phòng Khám Đa khoa Diamond','office'),
('bernard@mail.com','bernard','hash_bernard_098','Trung tâm Y khoa Chuyên sâu Quốc tế Bernard','office'),
('medicsaigon5@mail.com','medicsaigon5','hash_medicsaigon5_099','Phòng khám Medic Sài Gòn Cơ Sở 5','office'),
('medicsaigon3@mail.com','medicsaigon3','hash_medicsaigon3_100','Phòng khám Medic Sài Gòn Cơ Sở 3','office');


INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (29, 'Bệnh viện Đại học Y Dược TP.HCM (Cơ sở 2)', 'benh-vien-dai-hoc-y-duoc-tphcm-co-so-2', 'CS2: 201 Nguyễn Chí Thanh - Phường Chợ Lớn - TP. Hồ Chí Minh (Địa chỉ cũ: 201 Nguyễn Chí Thanh, Phường 12, Quận 5, TP. Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6592.097836827651!2d106.65788221568286!3d10.758755674850875!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752ef1d207e647%3A0x1bcd85bdaf5050f5!2zMjAxIE5ndXnhu4VuIENow60gVGhhbmgsIFBoxrDhu51uZyAxMiwgUXXhuq1uIDUsIEjhu5MgQ2jDrSBNaW5oLCBWaWV0bmFt!5e0!3m2!1sen!2s!4v1593595660777!5m2!1sen!2s', NULL, 'https://bo-api.medpro.com.vn/static/images/umc2/web/logo.png', 'clean quality clinic community reliable comfortable trusted professional friendly treatment', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (30, 'Bệnh viện Bệnh Nhiệt đới', 'benh-vien-benh-nhiet-doi', '764 Võ Văn Kiệt, Phường Chợ Quán, TP. Hồ Chí Minh (Địa chỉ cũ: 764 Võ Văn Kiệt, Phường 1, Quận 5, TP.Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.7640337436264!2d106.67891639999999!3d10.752660599999997!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f0186de2bb7%3A0x1392b898bea9c54!2zQuG7h25oIHZp4buHbiBC4buHbmggTmhp4buHdCDEkeG7m2k!5e0!3m2!1svi!2s!4v1757297397111!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/aa438828-15f9-4789-a461-d82b882b0f16-logo-benh-vien-nhiet-doi-1.png', 'department patient comprehensive professional health treatment care service focused medical', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (31, 'Bệnh viện Nhân Dân Gia Định', 'bvndgiadinh', 'Số 1 Nơ Trang Long, Phường Gia Định, TP. Hồ Chí Minh (Địa chỉ cũ: Số 1 Nơ Trang Long, Phường 7, Quận Bình Thạnh, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15676.395067245752!2d106.6941388!3d10.8037471!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528c663a9375f%3A0x342683919bcbff10!2sNhan%20dan%20Gia%20Dinh%20Hospital!5e0!3m2!1sen!2s!4v1731396910564!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/721f45d6-348b-4ce2-bce0-260516ad21a0-logo_512x512px.png', 'clinic trusted service accessible modern advanced reliable department care city', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (32, 'Bệnh viện Nhân Dân 115', 'benh-vien-nhan-dan-115', '527 Sư Vạn Hạnh, Phường Hòa Hưng, Thành phố Hồ Chí Minh (Địa chỉ cũ: 527 Sư Vạn Hạnh, Phường 12, Quận 10, Thành phố Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4867693100214!2d106.66152748885496!3d10.773981100000006!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752fa0cad4da25%3A0x6170fb9603ff966!2zQuG7h25oIHZp4buHbiBOaMOibiBEw6JuIDExNQ!5e0!3m2!1svi!2s!4v1757053492618!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/dd396389-8755-4942-ad6c-c9ba3d4337c0-logo-benh-vien-nhan-dan-115.jpg', 'comfortable service advanced patient staff care efficient facility center reliable', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (33, 'Bệnh viện Đa khoa Quốc Tế Hoàn Mỹ Thủ Đức', 'hoanmytd', '241 Quốc lộ 1K, Phường Linh Xuân, TP. Hồ Chí Minh (Địa chỉ cũ: 241 Quốc lộ 1K, Linh Xuân, Thành Phố Thủ Đức, Thành phố Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15671.985263619534!2d106.7737158!3d10.8878839!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xf13a4a703b669bc8!2zQuG7h25oIHZp4buHbiDEkGEga2hvYSBRdeG7kWMgdOG6vyBIb8OgbiBN4bu5IFRo4bunIMSQ4bupYw!5e0!3m2!1svi!2s!4v1637231558564!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/hoanmytd/app/image/logo_circle.png?t=8888888', 'district friendly community comprehensive facility reliable efficient medical professional patient', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (34, 'Bệnh viện đa khoa Singapore (Singapore General Hospital)', 'benh-vien-da-khoa-singapore', 'Bukit Merah, Central Region, Singapore', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.8226312435063!2d103.83322440840124!3d1.2800648691130114!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31da196f954c6cd5%3A0x5c6009b161544a96!2sSingapore%20General%20Hospital!5e0!3m2!1sen!2s!4v1710232768924!5m2!1sen!2s" width="600" height="450" style="border:0', NULL, 'https://cdn-pkh.longvan.net/prod-partner/6e45965e-09bd-4a35-b396-5df82f3e443e-logo_sgh_512x512_(2).png', 'patient comfortable treatment central focused support service professional quality expert', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (35, 'Bệnh viện FV (FV Hospital)', 'benh-vien-fv', '6 Nguyễn Lương Bằng, Phường Tân Mỹ, TP. Hồ Chí Minh (Địa chỉ cũ: Số 6 Nguyễn Lương Bằng, P.Tân Phú, Quận 7, Tp.HCM)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15680.102703893059!2d106.718007!3d10.732503!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f88b610ea25%3A0xb75a943c2008990b!2zQuG7h25oIHZp4buHbiBGVg!5e0!3m2!1svi!2sus!4v1757067431496!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/2c03fcb6-5c64-4df8-858b-47ec089ff643-screenshot_2025-07-21_132746.png', 'modern quality comfortable staff patient reliable health medical accessible expert', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (36, 'Bệnh viện Hoàn Mỹ Sài Gòn', 'benh-vien-hoan-my-sai-gon', '60 - 60A Phan Xích Long, Phường Cầu Kiệu, TP. Hồ Chí Minh (Địa chỉ cũ: 60-60A Phan Xích Long, Phường 1, Phú Nhuận, Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15676.583942180805!2d106.684241!3d10.800129!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528d0911a5c55%3A0xef4a80f1f2c1a13b!2zQuG7h25oIHZp4buHbiDEkGEgS2hvYSBIb8OgbiBN4bu5IFPDoGkgR8Oybg!5e0!3m2!1svi!2sus!4v1757067472640!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/a89bd8f8-42b7-4522-a5ff-6f0b814122f8-screenshot_2025-07-09_130558.png', 'district support accessible medical professional health department trusted care staff', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (37, 'Bệnh viện Ung bướu Hưng Việt', 'bv-ung-buou-hung-viet', '34 và 40 Đại Cồ Việt, Phường Hai Bà Trưng, Hà Nội', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3724.5949400441173!2d105.8486805!3d21.0088681!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab8a6fddf42b%3A0xd0445afec1bc0c6!2zQuG7h25oIFZp4buHbiBVbmcgQsaw4bubdSBIxrBuZyBWaeG7h3Q!5e0!3m2!1svi!2s!4v1754985624208!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/75e18f02-3920-4ea1-8fb7-00922f5fa354-picture1.png', 'service expert department professional reliable friendly modern support district comfortable', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (38, 'Phòng khám ACC Bến Thành - HCM', 'phong-kham-acc-ben-thanh', '99 Nguyễn Du, P. Bến Thành, TP.HCM (Địa chỉ cũ: 99 Nguyễn Du, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d501692.4612091097!2d106.11997277343752!3d10.77508850000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f3d0b148ac3%3A0x4d2a04294ea6b77d!2sPh%C3%B2ng%20kh%C3%A1m%20ACC%20-%20Chiropractic%20Qu%E1%BA%ADn%201%20TPHCM!5e0!3m2!1svi!2sus!4v1757067594914!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/5dfc30e2-fea2-4fdc-92d9-3766230e39d0-imgpsh_fullsize_anim11.png', 'advanced comfortable city modern expert focused service department support health', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (39, 'Phòng khám ACC Chợ Lớn - HCM', 'phong-kham-acc-cho-lon', 'Lầu 1, Tản Đà Court, 86 Tản Đà, Phường Chợ Lớn, TP.HCM (Địa chỉ cũ: Lầu 1, Tản Đà Court, 86 Tản Đà, Phường 11, Quận 5, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15679.018228638502!2d106.66443!3d10.753390000000001!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f0918ff66cd%3A0x2a76c55e7569a3ca!2sPh%C3%B2ng%20kh%C3%A1m%20ACC%20-%20Chiropractic%20Qu%E1%BA%ADn%205%20TPHCM!5e0!3m2!1svi!2sus!4v1757067652813!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/58193597-cf5a-4f89-953f-a063bc6208b6-imgpsh_fullsize_anim11.png', 'service advanced patient support health center district quality trusted reliable', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (40, 'Phòng khám ACC Hà Nội', 'phong-kham-acc-ha-noi', ' Lầu 1 & 2 - Tòa nhà HDI Tower, 55 Lê Đại Hành, Phường Hai Bà Trưng, Hà Nội (Địa chỉ cũ: Lầu 1 & 2 - Tòa nhà HDI Tower, 55 Lê Đại Hành, Q.Hai Bà Trưng, Hà Nội)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14898.04211511614!2d105.847298!3d21.012249!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135aba8bba6a56b%3A0x147270b76f2be216!2sHDI%20TOWER!5e0!3m2!1svi!2sus!4v1757297592479!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/810363c1-60d9-4f16-9028-ed487459d1ba-imgpsh_fullsize_anim11.png', 'patient advanced clinic professional community department city comprehensive efficient friendly', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (41, 'Phòng khám Cơ xương khớp chuyên sâu TTƯT.BS.CK2 Dương Minh Trí', 'phong-kham-co-xuong-khop-bac-si-tri', '182B Lê Văn Sỹ, Phường Phú Nhuận, TP Hồ Chí Minh (Địa chỉ cũ: 182B Lê Văn Sỹ, Phường 10, Quận Phú Nhuận, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15676.960555206946!2d106.670316!3d10.792911!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175292ba7bf89bf%3A0xcc88934f7c25d58f!2zUGjDsm5nIGtow6FtIGNodXnDqm4gc8OidSBDxqEgWMawxqFuZyBLaOG7m3AgLSBUaOG6p3kgVGh14buRYyDGr3UgVMO6LkJTLkNLMiBExrDGoW5nIE1pbmggVHLDrQ!5e0!3m2!1svi!2sus!4v1757297686138!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/d06668e8-da7e-41e0-829c-952bd613efeb-screenshot_2025-06-10_101337.png', 'comprehensive district friendly city community care reliable efficient center facility', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (42, 'Phòng Khám Chuyên Khoa Da Liễu Dr. Choice Clinic', 'phong-kham-da-lieu-dr-choice', '92 Đường D5, Phường Thạnh Mỹ Tây (cũ Phường 25, Quận Bình Thạnh), TP. Hồ Chí Minh.', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15676.280717975047!2d106.715227!3d10.805937!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175297a6c66b51b%3A0xcc1daba36e269229!2zVHLhu4sgU-G6uW8gUuG7lyAtIFPhurlvIEzhu5NpIC0gVHLhu4sgVGjDom0gLVRy4buLIE3hu6VuIC0gRHIuIENob2ljZSBjbGluaWM!5e0!3m2!1svi!2sus!4v1757297881982!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/a033fe7f-01f6-4036-808b-2303a018e989-logo-drc-02.png', 'patient care focused department support city health modern comfortable district', 4.8);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (43, 'DYM Medical Center Hà Nội', 'dym-medical-center-ha-noi', 'Tầng Hầm B1, Toà Epic Tower, Ngõ 19 Duy Tân, Phường Cầu Giấy, TP. Hà Nội (Địa chỉ cũ: Tầng hầm B1, tòa Epic Tower, ngõ 19 Duy Tân, Phường Mỹ Đình 2, Quận Nam Từ Liêm, TP Hà Nội)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.058933513122!2d105.7814861!3d21.0303278!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x313455002a968a15%3A0xb302ba205f90ac7c!2zUGjDsm5nIEtow6FtIMSQYSBLaG9hIERZTSBNZWRpY2FsIENlbnRlciBIw6AgTuG7mWk!5e0!3m2!1svi!2s!4v1757058293174!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/d1ae64e8-b41e-4632-baa9-f59b6d13be1a-picture1_(2).png', 'community efficient advanced support expert facility trusted medical care comfortable', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (44, 'DYM Medical Center Phú Mỹ Hưng', 'dym-medical-center-phu-my-hung', 'Phòng 3A01, Tầng 3A, Tòa nhà The Grace, 71 Hoàng Văn Thái, Phường Tân Mỹ, TP. Hồ Chí Minh (Địa chỉ cũ: Phòng 3A01, Tầng 3A, Tòa nhà The Grace, 71 Hoàng Văn Thái, Khu phố 1, Phường Tân Phú, Quận 7, Thành phố Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3920.047646891835!2d106.7235497!3d10.7308086!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175251a039aadf1%3A0x87312c23f09a3749!2zUGjDsm5nIEtow6FtIMSQYSBLaG9hIERZTSAtIFBow7ogTeG7uSBIxrBuZw!5e0!3m2!1svi!2s!4v1757056188857!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/ca4d188d-65fe-4a83-b584-553f45268df3-picture1_(2).png', 'care center expert professional reliable quality district treatment medical central', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (45, 'DYM Medical Center Sài Gòn', 'dym-medical-center-sai-gon', 'Phòng B103, tầng hầm 1, toà nhà mPlaza Saigon, 39 Lê Duẩn, Phường Sài Gòn, TP.HCM (Địa chỉ cũ: Phòng B103, Tầng hầm 1, Tòa nhà mPlaza Saigon, 39 Lê Duẩn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1959.6953232008664!2d106.6984596848488!3d10.78136283210189!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752fa404084e29%3A0xfedcaec77fd24ed7!2smPlaza%20Saigon!5e0!3m2!1svi!2s!4v1759304673935!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/1f26eb20-0d2a-4bed-9819-f1433602eea2-picture1_(2).png', 'central medical trusted service health patient comprehensive staff accessible facility', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (46, 'Phòng khám MedFit - Phòng khám giảm cân chuyên sâu', 'phong-kham-giam-can-medfit', '462/2 Nguyễn Tri Phương, Phường Vườn Lài, TP. Hồ Chí Minh (Địa chỉ cũ: Số 462/2 đường Nguyễn Tri Phương, Phường 09, Quận 10, Thành phố Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15678.353493848048!2d106.667893!3d10.766173!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752febc7e7f1c5%3A0x2d31c6165de32915!2zUGjDsm5nIGtow6FtIEdp4bqjbSBjw6JuIGNodXnDqm4gc8OidSBNZWRGaXQ!5e0!3m2!1svi!2sus!4v1757297928562!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/4e23e3de-5c90-48f4-bd0c-2dd7624b7903-logo_medfit_fix.png', 'advanced treatment service comprehensive care quality patient reliable center professional', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (47, 'Trung Tâm Mắt Kỹ Thuật Cao Nam Việt', 'trung-tam-mat-nam-viet', '18 - 20 Phước Hưng, Phường An Đông, Thành phố Hồ Chí Minh (Địa chỉ cũ: 18 - 20 Phước Hưng, Phường 8, Quận 5, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15678.930131027622!2d106.668901!3d10.755085!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752efb989904cf%3A0x3bf992b81d6fe4b!2zVHJ1bmcgVMOibSBN4bqvdCBL4bu5IFRodeG6rXQgQ2FvIE5hbSBWaeG7h3Q!5e0!3m2!1svi!2sus!4v1757297462638!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/ecda04b6-d646-4257-81be-4df38eafabd9-logo_nam_viet.png', 'patient accessible district health friendly modern staff community facility expert', 4.8);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (48, 'Trung Tâm Nội Soi Tiêu Hoá Doctor Check', 'drcheck', ' 429 Tô Hiến Thành, Phường 14, Quận 10, Thành phố Hồ Chí Minh', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4706612933487!2d106.6601678245749!3d10.775218459216921!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f3eacecd9a9%3A0x800b5bd5427447e7!2zRG9jdG9yIENoZWNrIC0gVOG6p20gU2_DoXQgQuG7h25oIMSQ4buDIFPhu5FuZyBUaOG7jSBIxqFu!5e0!3m2!1svi!2s!4v1731555829534!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/4db1fb51-2669-492f-b3d7-f08005451770-mark_dc-removebg-preview.png', 'center support accessible community district comfortable patient friendly advanced department', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (49, 'Bệnh viện Nhi Đồng 1', 'nhidong1', '341 Sư Vạn Hạnh, Phường Vườn Lài, TP.Hồ Chí Minh (Địa chỉ cũ: 341 Sư Vạn Hạnh, Phường 10, Quận 10, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.5573161637585!2d106.66797021474885!3d10.76856029232692!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752ede1ad51af1%3A0x520c34a578581e8!2zQuG7h25oIHZp4buHbiBOaGkgxJHhu5NuZyAx!5e0!3m2!1svi!2s!4v1598335496158!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/nhidong1/app/image/logo_circle.png?t=11111', 'support comfortable medical trusted friendly advanced clinic patient efficient modern', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (50, 'Bệnh Viện Mắt', 'bvmathcm', '280 Điện Biên Phủ, phường Xuân Hòa, TP. Hồ Chí Minh (Địa chỉ cũ: 280 Điện Biên Phủ, Phường Võ Thị Sáu, Quận 3, TP. Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15677.698761809208!2d106.684992!3d10.778749!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x35859d11c9f06b3a!2zQuG7h25oIHZp4buHbiBN4bqvdCBUUC5IQ00!5e0!3m2!1svi!2s!4v1655180973714!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/bvmathcm/web/logo.png?t=11', 'facility comprehensive treatment medical district city reliable expert patient accessible', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (51, 'Bệnh Viện Quận Bình Thạnh', 'binhthanhhcm', '132 Lê Văn Duyệt, Phường Gia Định, TP.Hồ Chí Minh (Địa chỉ cũ: 132 Lê Văn Duyệt, Phường 1, Bình Thạnh, Thành phố Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d644.558618200372!2d106.696233585318!3d10.797895558225296!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528c903366a63%3A0x765e53d437be3f6f!2zQuG7h25oIFZp4buHbiBCw6xuaCBUaOG6oW5o!5e0!3m2!1svi!2s!4v1611976803757!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/binhthanhhcm/web/logo.png?t=Tue%20Sep%2013%202022%2010:08:08%20GMT+0700%20(Indochina%20Time)', 'department support care staff treatment clinic friendly focused professional trusted', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (52, 'Bệnh viện Đa Khoa Trung Mỹ Tây', 'benh-vien-da-khoa-trung-my-tay', '111 Dương Thị Mười, Phường Trung Mỹ Tây, TP.HCM (Địa chỉ cũ: 111 Dương Thị Mười, phường Tân Chánh Hiệp, Quận 12)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15673.418401466171!2d106.6307806!3d10.860611!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175294388cf7971:0x63eaecc944c04ef1!2zQuG7h25oIFZp4buHbiBRdeG6rW4gMTI!5e0!3m2!1sen!2s!4v1695181104302!5m2!1sen!2s&gidzl=LrFW1Qcf5cnHJE81fR0NOrjCuWkuvHOn4HQsKx_i5Z4M7hPSkR0IPKHCv0pjiH1k4KxiL3dDoyLRgwaJRW', NULL, 'https://cdn.medpro.vn/prod-partner/36b3af6f-fd82-4046-bf91-ef9fd68ab4e3-z6928458371135_df6b2b0e97d37bcc611e1c2c535ccb15.jpg', 'department staff treatment central quality clean reliable health patient advanced', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (53, 'Bệnh viện Da Liễu TP.HCM', 'dalieuhcm', '02 Nguyễn Thông, Phường Xuân Hòa, TP.Hồ Chí Minh (Địa chỉ cũ: 2 Nguyễn Thông, Phường Võ Thị Sáu, Quận 3, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.448176009465!2d106.68480041526027!3d10.776945462129946!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f251ff33743%3A0x13e32b16699020bb!2zQuG7h25oIHZp4buHbiBEYSBsaeG7hXUgVGjDoG5oIHBo4buRIEjhu5MgQ2jDrSBNaW5o!5e0!3m2!1svi!2s!4v1605856153767!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/dalieuhcm/app/image/logo_circle.png?t=123', 'staff friendly treatment comfortable health advanced city support district care', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (54, 'Bệnh viện Phụ sản Thành Phố Cần Thơ', 'benh-vien-phu-san-thanh-pho-can-tho', '106 Cách Mạng Tháng Tám, Phường Cái Khế, TP. Cần Thơ (Địa chỉ cũ: 106 Cách Mạng Tháng Tám, Phường Cái Khế, Quận Ninh Kiều, TP. Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.5883344005297!2d105.7769629!3d10.0507884!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a087e38456545b%3A0x97b485abb704d807!2zQuG7h25oIHZp4buHbiBQaOG7pSBz4bqjbiBUUC4gQ-G6p24gVGjGoQ!5e0!3m2!1svi!2s!4v1683775819533!5m2!1svi!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/88db2c0f-c134-428e-9fb6-0cb043214cd9-logo-ps-900.png', 'center staff facility modern city clean advanced efficient expert support', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (55, 'Bệnh viện Trưng Vương', 'trungvuong', '266 Lý Thường Kiệt, Phường Diên Hồng, TP.Hồ Chí Minh (Địa chỉ cũ: 266 Lý Thường Kiệt, Phường 14, Quận 10, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15678.125245989395!2d106.6594844!3d10.7705588!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xcb83e2a72eb77ac2!2zVHLGsG5nIFbGsMahbmcgSG9zcGl0YWw!5e0!3m2!1sen!2s!4v1591006540481!5m2!1sen!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/trungvuong/web/logo.png?t=222222', 'health efficient department medical quality support community modern comfortable staff', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (56, 'Bệnh viện Vũng Tàu', 'bv-vung-tau', '27 Đường 2/9, Phường Phước Thắng, TP. Hồ Chí Minh (Địa chỉ cũ: Số 27, Đường 2/9, Phường 11, Thành phố Vũng Tàu)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3924.366908676929!2d107.12802071479749!3d10.392405292582827!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317571bf86ee9b79%3A0x54ad8c256fcb9585!2zQuG7h25oIFZp4buHbiDEkGEgS2hvYSBWxaluZyBUw6B1!5e0!3m2!1svi!2s!4v1646900128451!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/leloi/app/image/logo_circle.png?t=1111111', 'professional center support city quality central department focused clinic district', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (57, 'Bệnh viện Chấn Thương Chỉnh Hình TP.HCM', 'benh-vien-chan-thuong-chinh-hinh-hcm', '929 Trần Hưng Đạo, Phường Chợ Quán, TP. Hồ Chí Minh (Địa chỉ cũ: 929 Trần Hưng Đạo, Phường 1, Quận 5, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.744396961695!2d106.6761446152603!3d10.754171962543682!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f025ba50e13%3A0x5af3f7880d575b09!2zQuG7h25oIHZp4buHbiBDaOG6pW4gVGjGsMahbmcgQ2jhu4luaCBIw6xuaA!5e0!3m2!1svi!2s!4v1600410661219!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/ctchhcm/web/logo.png?1657159777132?t=123', 'central friendly efficient comprehensive expert advanced comfortable care health trusted', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (58, 'Bệnh viện Đa khoa Tiền Giang', 'gensolution2', 'Số 315, QL1A, Khu phố Long Hưng, Phường Phước Thạnh, Tỉnh Đồng Tháp (Địa chỉ cũ: Số 315, QL1A, ấp Long Hưng, xã Phước Thạnh, TP.Mỹ Tho, Tiền Giang)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3924.368845546065!2d106.314134!3d10.3922511!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310abba848245fc9%3A0x600c8ac7b23eb7b5!2zQuG7h25oIHZp4buHbiDEkGEga2hvYSB04buJbmggVGnhu4FuIEdpYW5n!5e0!3m2!1svi!2s!4v1757065884504!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/d4726dec-6bad-478e-9143-572afe2ada0f-logo_tiengiang_copy.png', 'community central city trusted expert district medical treatment reliable department', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (59, 'Bệnh viện Nguyễn Trãi', 'benh-vien-nguyen-trai', '314 Nguyễn Trãi, Phường An Đông, TP.Hồ Chí Minh (Địa chỉ cũ: 314 Nguyễn Trãi, Phường 8, Quận 5, TP.Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.7219967385363!2d106.67263047496678!3d10.755895759572203!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752efd6e464c49%3A0x7a58988104f47754!2zQuG7h25oIHZp4buHbiBOZ3V54buFbiBUcsOjaQ!5e0!3m2!1sen!2s!4v1682047803576!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/28da7b66-3643-4224-906e-4330491c2f44-310988115_501122428699790_1399222391851240979_n.jpg', 'community facility expert center quality department comprehensive care service support', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (60, 'Bệnh viện Nhi Đồng Thành Phố', 'nhidonghcm', '15 Võ Trần Chí, xã Tân Nhựt, TP.Hồ Chí Minh (Địa chỉ cũ: Số 15 Võ Trần Chí, Xã Tân Kiên, Huyện Bình Chánh, TP. Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3920.3143261132386!2d106.57218061480017!3d10.710221142366434!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175332fbc27a219%3A0xfe866d0daaacf7ed!2zQlYgTmhpIMSR4buTbmcgVHA!5e0!3m2!1svi!2s!4v1632366084400!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/nhidonghcm/app/image/logo_circle.png?t=1111', 'friendly comprehensive efficient support facility trusted focused patient quality city', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (61, 'Bệnh viện Quận 1 - Cơ sở 1', 'benh-vien-quan-1', '338 Hai Bà Trưng, Phường Tân Định, TP Hồ Chí Minh (Địa chỉ cũ: 338 Hai Bà Trưng, Phường Tân Định, Quận 1, Thành phố Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.2763670589234!2d106.68701937552481!3d10.790132389359513!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528d291862b6d%3A0x997e5580a2358736!2zQuG7h25oIHZp4buHbiBRdeG6rW4gMQ!5e0!3m2!1svi!2s!4v1711420775923!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/5f66f5d4-1914-40a6-ac60-9720190b75e1-logobvquan1.png', 'advanced community clinic staff efficient quality modern patient facility support', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (62, 'Bệnh viện Đại học Y Dược TP.HCM (Y học cổ truyền kết hợp y học hiện đại)', 'benh-vien-dai-hoc-y-duoc-co-so-3', 'CS3: 221B Hoàng Văn Thụ, Phường Phú Nhuận, TP.HCM (Địa chỉ cũ: 221B Hoàng Văn Thụ, Phường 8, Quận Phú Nhuận, TP. Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.155774761317!2d106.66830527465915!3d10.799378758770692!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752928d11288b5%3A0x3eb12d1bf7e2191e!2zQ8ahIHPhu58gMyAtIELhu4duaCB2aeG7h24gxJDhuqFpIGjhu41jIFkgRMaw4bujYyBUUEhDTQ!5e0!3m2!1svi!2s!4v1700132255688!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/b55d8e1c-4ca9-41f1-9827-c2574a188ffa-logo_(1).png', 'facility clinic clean treatment medical accessible reliable care support professional', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (63, 'Bệnh viện Chợ Rẫy', 'cho-ray', '201B Nguyễn Chí Thanh, Phường Chợ Lớn, TP.Hồ Chí Minh (Địa chỉ cũ: 201B Nguyễn Chí Thanh, Phường 12, Quận 5, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.707076818123!2d106.65754181428684!3d10.757043762493844!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752ef1efebf7d7%3A0x9014ce53b8910a58!2sCho%20Ray%20Hospital!5e0!3m2!1sen!2s!4v1594603824129!5m2!1sen!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/choray/web/logo.png?t=22222222', 'patient trusted central advanced reliable department efficient medical comfortable city', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (64, 'Bệnh viện Tim mạch Thành phố Cần Thơ', 'benh-vien-tim-mach-thanh-pho-can-tho', 'Số 204 Trần Hưng Đạo, P. Ninh Kiều, TP. Cần Thơ (Địa chỉ cũ: 204 Trần Hưng Đạo, P. An Nghiệp, Q. Ninh Kiều, TP. Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d982.1949508415921!2d105.77406155872191!3d10.035021100000003!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0881f16e44dcf%3A0x33ea1bcc72f8819e!2sCan%20Tho%20Heart%20Hospital!5e0!3m2!1sen!2s!4v1703582354799!5m2!1sen!2s', NULL, 'https://cdn-pkh.longvan.net/prod-partner/ab48f0a0-1770-487f-a7c3-74a08d39961c-logo_tim_mach_can_tho.jpg', 'department accessible service clinic care patient staff medical health reliable', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (65, 'Phòng khám Nhi BS Võ Thị My Na', 'phong-kham-nhi-bs-my-na', '2/41 Đường ĐT 743B, Khu Phố Bình Đức, Phường Bình Hòa , Thành Phố Thuận An, Tỉnh Bình Dương', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15671.041084387725!2d106.729288!3d10.905815!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3174d76f33d191d1%3A0xad2f837a58c110e3!2zUGjDsm5nIEtow6FtIENodXnDqm4gS2hvYSBOaGktIEJTIE15TmEtQuG7h25oIFZp4buHbiBOaGkgxJDhu5NuZyAyIFRQSENN!5e0!3m2!1svi!2sus!4v1757298053220!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/c9341635-070c-4184-a471-26c0514b4d95-my_na_logo_(1)_(1).png', 'modern department quality treatment center district advanced accessible friendly care', 5.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (66, 'Bệnh viện Đa Khoa Gò Vấp', 'benh-vien-quan-go-vap', '641 Quang Trung, Phường Thông Tây Hội, TP.Hồ Chí Minh (Địa chỉ cũ: 641 Quang Trung, P.11, Q.Gò Vấp, TPHCM)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15674.779354763037!2d106.66155!3d10.834649!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317529b20b24ded9%3A0x7b40c078f2939ca8!2zQuG7h25oIHZp4buHbiBHw7IgVuG6pXA!5e0!3m2!1svi!2sus!4v1757066691607!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/2db00850-bd75-45ef-8433-a2e25f181669-abeece55-2f43-41c3-b65b-cdb45e75d0ea.webp', 'treatment support expert service advanced patient department care clinic health', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (67, 'Bệnh viện Thống Nhất', 'benh-vien-thong-nhat', 'Số 1 Lý Thường Kiệt, Phường Tân Sơn Nhất, TP. Hồ Chí Minh (Địa chỉ cũ: Số 1 Lý Thường Kiệt, Phường 7, Quận Tân Bình, TP Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15677.033360262658!2d106.6534539!3d10.7915151!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752ecaab26b129%3A0x6a3e66ad2406aa1c!2sThong%20Nhat%20Hospital!5e0!3m2!1sen!2s!4v1726026656231!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/b8c744bb-3743-4f59-aa13-ccd6631baa83-logo-benh-vien-thong-nhat.png', 'expert care patient friendly health comprehensive treatment quality facility central', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (68, 'Bệnh viện Nhi đồng Thành phố Cần Thơ', 'bvnhidongct', 'Số 345 Nguyễn Văn Cừ, Phường An Bình, Thành phố Cần Thơ (Địa chỉ cũ: Số 345 Nguyễn Văn Cừ, P.An Bình, Q.Ninh Kiều, TP.Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15715.987099458025!2d105.739197!3d10.017124!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a08818d0bd58c1%3A0x1ef60c56212aad!2zQuG7h25oIHZp4buHbiBOaGkgxJHhu5NuZyBD4bqnbiBUaMah!5e0!3m2!1svi!2sus!4v1719385396024!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/d275b022-d8c3-44ac-9330-5d31c8cddb4c-images_(1).png', 'central expert facility focused treatment modern reliable department center support', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (69, 'Bệnh viện Đa khoa Thành phố Cần Thơ', 'benh-vien-da-khoa-thanh-pho-can-tho', '04 Châu Văn Liêm, Phường Ninh Kiều, Thành phố Cần Thơ (Địa chỉ cũ: 04 Châu Văn Liêm, Phường Tân An, Quận Ninh Kiều, TP. Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d72655.25685428308!2d105.75992135045946!3d10.040179449672985!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0629fd926d1ef%3A0x5e9fb64bc1dd41b8!2zQuG7h25oIHZp4buHbiDEkGEga2hvYSBUaMOgbmggcGjhu5EgQ-G6p24gVGjGoQ!5e0!3m2!1svi!2sus!4v1757066521983!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/5a2117f1-561f-4b73-8360-f8b946d6e243-logo.jpg', 'treatment professional department patient medical reliable expert city accessible health', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (70, 'Bệnh viện Đa khoa Đồng Nai', 'benh-vien-da-khoa-dong-nai', ' 02 Đồng Khởi, Phường Tam Hiệp, Tỉnh Đồng Nai (Địa chỉ cũ: 02 Đồng Khởi, Phường Tam Hòa, TP. Biên Hòa, Tỉnh Đồng Nai)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3917.141415551196!2d106.86567231526091!3d10.952688558909248!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3174de7f754a8663%3A0x23f79485b6ab37c4!2zQuG7h25oIFZp4buHbiDEkGEgS2hvYSDEkOG7k25nIE5haQ!5e0!3m2!1svi!2s!4v1598491778537!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/dkdongnai/web/logo.png?t=22', 'quality advanced service trusted district patient modern professional focused community', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (71, 'Bệnh Viện Ung Bướu Thành Phố Cần Thơ', 'bvungbuouct', '4 Châu Văn Liêm, Phường Ninh Kiều, Thành phố Cần Thơ (Địa chỉ cũ: 4 Châu Văn Liêm, Tân An, Ninh Kiều, Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.8308965832002!2d105.78071567525119!3d10.03080947250336!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0629fdd5c1ccb%3A0x92adb1dc6cbdc5b9!2zQuG7h25oIHZp4buHbiBVbmcgYsaw4bubdSBUUC4gQ-G6p24gVGjGoQ!5e0!3m2!1sen!2s!4v1726709690145!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/4a7b8e90-7fe1-4099-a074-16f6fc397d5e-images_(1).jfif', 'comfortable focused service advanced comprehensive professional expert trusted medical center', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (72, 'Bệnh viện Quận 1 - Cơ sở 2', 'benh-vien-quan-1-cs2', '235 – 237 Trần Hưng Đạo, Phường Cầu Ông Lãnh, Thành phố Hồ Chí Minh (Địa chỉ cũ: 235 – 237 Trần Hưng Đạo, phường Cô Giang, Quận 1, Thành phố Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.602968044619!2d106.6932350287053!3d10.765050972368453!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f1733d97a93%3A0xad705a3d87702abb!2zQuG7h25oIFZp4buHbiBRdeG6rW4gMSAtIEPGoSBz4bufIDI!5e0!3m2!1sen!2s!4v1711422004571!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/4dbeab8b-8dbf-4387-b0ea-fd98b070f0f9-logo_bv_quaaoan_1.jpg', 'city central facility clinic department medical accessible advanced treatment clean', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (73, 'Bệnh viện Đa khoa Khu vực Tỉnh An Giang', 'dat-kham-khu-vuc-an-giang', '917 Tôn Đức Thắng, Phường Châu Đốc, Tỉnh An Giang (Địa chỉ cũ: 917 Tôn Đức Thắng, Phường Vĩnh Mỹ, TP. Châu Đốc, Tỉnh An Giang)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3920.595878404254!2d105.14527561411582!3d10.688442963735893!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310a23f33cf6e2f5%3A0x1165046806c02d00!2zQuG7h25oIHZp4buHbiDEkGEga2hvYSBraHUgduG7sWMgdOG7iW5oIEFuIEdpYW5n!5e0!3m2!1svi!2s!4v1592531223832!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/dkkvangiang/web/logo.png?t=222', 'district community focused city department care staff professional efficient medical', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (74, 'Bệnh viện Đa khoa Vạn Hạnh', 'van-hanh', '781/B1-B3-B5 Lê Hồng Phong, Phường Hoà Hưng, Thành Phố Hồ Chí Minh (Địa chỉ cũ: 781/B1-B3-B5 Lê Hồng Phong nối dài, Phường 12, Quận 10, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.5154427620882!2d106.6691896149606!3d10.771778162224562!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752ede89c401cf%3A0xe3f4ee72083f1fbe!2zQuG7h25oIHZp4buHbiDEkGEga2hvYSBW4bqhbiBI4bqhbmg!5e0!3m2!1svi!2s!4v1597636588504!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/vanhanh/web/logo.png?t=2222222', 'service clinic central friendly efficient city center reliable community health', 4.3);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (75, 'Bệnh viện Đa Khoa An Phước', 'dkanphuoc', '235 Trần Phú, Phường Phan Thiết, Tỉnh Lâm Đồng (Địa chỉ cũ: 235 Trần Phú, TP. Phan Thiết, Tỉnh Bình Thuận)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3917.4102584832017!2d108.09551241411762!3d10.932350159287243!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31768302480eaba3%3A0xc86b72336e695143!2zQuG7h25oIFZp4buHbiDEkGEgS2hvYSBBTiBQSMav4buaQw!5e0!3m2!1svi!2s!4v1603858313333!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/dkanphuoc/web/logo.png?t=222222', 'efficient department facility district modern treatment focused medical staff comfortable', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (76, 'Bệnh Viện Đa Khoa Hà Nội', 'bvdkhanoi', 'Số 29 Hàn Thuyên, Hai Bà Trưng, Hà Nội', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.360305116599!2d105.85303707585956!3d21.01826458062837!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135abf278c3f183%3A0x46cfd3983e522b6c!2sHanoi%20Hospital!5e0!3m2!1sen!2s!4v1703218776005!5m2!1sen!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/b2b6c6f5-2b71-4cdc-907e-b9b488a337d6-bv_djk_ha_noi_1.png', 'modern quality efficient reliable patient professional focused care clean friendly', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (77, 'Trung tâm Y tế khu vực Thốt Nốt', 'benh-vien-da-khoa-thot-not', ' Số 62, Quốc lộ 91, khu vực Phụng Thạnh 1, Phường Thuận Hưng, tTP. Cần Thơ (Địa chỉ cũ: Số 62, quốc lộ 91, KV Phụng Thạnh 1, phường Thốt Nốt, quận Thốt Nốt, TP Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d62815.164562762286!2d105.540665!3d10.265791!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310a7649429abaa1%3A0x20b6cfc00469f890!2zVGjhu5F0IE7hu5F0IERpc3RyaWN0IEhvc3BpdGFs!5e0!3m2!1sen!2sus!4v1757066861441!5m2!1sen!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/41ddd360-e6c4-453f-a072-909853dda306-z6858665507529_23b2ef34a7ec7ce766d1b3f77daea91c.jpg', 'patient professional community accessible clean focused care department reliable advanced', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (78, 'Bệnh viện Đa khoa Thanh Vũ Medic Bạc Liêu', 'thanhvubl', 'Số 02DN, đường tránh Quốc lộ 1A, Khóm 21, Phường Bạc Liêu, Tỉnh Cà Mau', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3937.251000755831!2d105.7104506!3d9.3110139!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a1091770d8c16f%3A0x6edb121a1e5e69d7!2zQuG7h25oIHZp4buHbiDEkGEga2hvYSBUaGFuaCBWxakgTWVkaWMgQuG6oWMgTGnDqnU!5e0!3m2!1svi!2s!4v1760337093484!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/c125b033-ee27-41a7-aa81-ce3d16b5166e-thanhvubl-removebg-preview_(1).png', 'health accessible trusted facility advanced quality community comfortable center reliable', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (79, 'Bệnh viện Nhật Tân', 'bvnhattan', '32, Phạm Ngọc Thạch, Tổ 14 Khóm Châu Long 7, Phường Châu Đốc, An Giang (Địa chỉ cũ: 32, Phạm Ngọc Thạch, Tổ 14 Khóm Châu Long 7, Phường Châu Phú B, TP.Châu Đốc, An Giang)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3920.3937955136576!2d105.1237933756759!3d10.704078589440359!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310a21212b8b100d%3A0x3169e6bfeecb1911!2zQuG7h25oIHZp4buHbiDEkWEga2hvYSBOaOG6rXQgVMOibg!5e0!3m2!1sen!2s!4v1703221009540!5m2!1sen!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/8c192fe5-c70e-4332-9d90-4f71264dc0a0-z4534580361527_9f269f2d02eb30f812cf83576ecdc7c8.jpg', 'clean focused reliable community clinic professional quality comprehensive advanced modern', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (80, 'Phòng Khám Đa Khoa Quốc Tế Ivy Health International Clinic', 'pkdkivy', '120 Nguyễn Trãi, Phường Bến Thành, Quận 1, TP HCM', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.541496590963!2d106.69088579999999!3d10.7697761!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752faad58d342b%3A0x2ff0965f4c703c80!2zUGjDsm5nIGtow6FtIMSQYSBraG9hIFF14buRYyBU4bq_IEl2eSBIZWFsdGggSW50ZXJuYXRpb25hbCBDbGluaWM!5e0!3m2!1svi!2s!4v1757312809596!5m2!1svi!2s', NULL, 'https://cdn-pkh.longvan.net/prod-partner/08f1fa6b-5deb-4bdc-a6d6-ad350656d5a5-zyro-image_(12)_1.png', 'facility expert modern clean focused accessible center community quality service', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (81, 'Trung tâm Y tế Khu vực Ô Môn', 'benh-vien-da-khoa-o-mon', 'Số 83, CMT8, P. Ô Môn, TP. Cần Thơ (Địa chỉ cũ: Số 83, CMT8, P. Châu Văn Liêm, Q. Ô Môn, TP. Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15711.2735124292!2d105.623017!3d10.113948!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a084e663b757a9%3A0x5a196c6879c30caf!2zQuG7h25oIFZp4buHbiDEkGEgS2hvYSBRdeG6rW4gw5QgTcO0bg!5e0!3m2!1svi!2sus!4v1757066759038!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/561b8194-4686-4d94-9881-10a4f3957a68-z6925985934255_0289ca84b9ff2d5a1c02720762cd256c.jpg', 'modern community focused accessible department staff clinic patient clean center', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (82, 'Bệnh viện Huyết học - Truyền máu Thành phố Cần Thơ', 'benh-vien-huyet-hoc-truyen-mau-can-tho', '317 Nguyễn Văn Linh, Phường Tân An, TP. Cần Thơ', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.846130474731!2d105.7543034!3d10.029553400000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a088414f4e8bdd%3A0xa142d730cdfe7488!2zQuG7h25oIHZp4buHbiBIdXnhur90IGjhu41jIC0gVHJ1eeG7gW4gbcOhdSBD4bqnbiBUaMah!5e0!3m2!1svi!2s!4v1761116701707!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/eab6caa6-8862-4a1c-827a-455ac806b0db-z7139627597838_bce1e2d065f42eee81c9bcf5d7443e1d-removebg-preview.png', 'trusted staff facility district medical community focused modern comprehensive patient', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (83, 'Bệnh viện Quân Dân Y Thành Phố Cần Thơ', 'benh-vien-quan-dan-y-can-tho', 'Ấp Thới Bình, Xã Cờ Đỏ, TP. Cần Thơ (Địa chỉ cũ: Ấp Thới Bình, TT. Cờ Đỏ, H. Cờ Đỏ, TP. Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15712.615757080093!2d105.435344!3d10.08647!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a09814c20aac75%3A0xbac4c00f61b85cda!2zQuG7h25oIFZp4buHbiBRdcOibiBEw6JuIFkgVFAuIEPhuqduIFRoxqE!5e0!3m2!1svi!2sus!4v1757066903750!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/553d268a-85d8-4f5d-b01c-90afbb140cb8-logo.png', 'care patient accessible focused modern quality staff clean facility trusted', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (84, 'Bệnh viện Tâm Thần Thành phố Cần Thơ', 'benh-vien-tam-than-can-tho', 'KV. Bình Hòa A, Phường Phước Thới, TP. Cần Thơ (Địa chỉ cũ: KV. Bình Hòa A, Phường Phước Thới, Quận Ô Môn, TP. Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15711.805703718153!2d105.670675!3d10.103062!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0884abe61bf25%3A0xdc5c3c5c8a1764eb!2zQuG7h25oIHZp4buHbiBUw6JtIHRo4bqnbiBUUC4gQ-G6p24gVGjGoQ!5e0!3m2!1svi!2sus!4v1757066948796!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/342372f0-ec4d-4064-9519-98f567971677-z6922256147316_bcb51dc64962fca0506805c622bffc71-removebg-preview.png', 'service staff support district comprehensive patient accessible comfortable health focused', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (85, 'Bệnh viện Y học cổ truyền Cần Thơ', 'benh-vien-y-hoc-co-truyen-can-tho', 'Số 768 Đường 30/4, Phường Tân An, Thành Phố Cần Thơ (Địa chỉ cũ: Số 768 đường 30 tháng 04, Phường Hưng Lợi, Quận Ninh Kiều, TP. Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15716.203721186619!2d105.758355!3d10.012652!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a089b552eb9b55%3A0x3dbe041f626b4294!2zQuG7h25oIFZp4buHbiBZIEjhu41jIEPhu5UgVHJ1eeG7gW4gQ-G6p24gVGjGoQ!5e0!3m2!1svi!2sus!4v1757066815077!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/1275c464-28a1-40cc-a60f-8b6d1b3e31f7-z6922256147287_1f222d661d8ef6f36668d5a6472b4234-removebg-preview.png', 'expert support city medical treatment community reliable care accessible clean', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (86, 'Phòng Khám Đa Khoa Pháp Anh', 'phong-kham-da-khoa-phap-anh', '222-224-226 Nguyễn Duy Dương   Phường Vườn Lài, TP.HCM (Địa chỉ cũ: 222-224-226 Nguyễn Duy Dương, Phường 4, Quận 10, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.6322413498247!2d106.67056889999999!3d10.7628001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752ee3ff692425%3A0xef1ff7f0b7b2e6dc!2sPolyclinics%20FRANCE%20ENGLAND!5e0!3m2!1sen!2s!4v1704850001701!5m2!1sen!2s', NULL, 'https://cdn-pkh.longvan.net/prod-partner/0cfb3bc7-0735-40a2-9e3d-9af3965e0462-zyro-image_(14)_1.png', 'clinic expert staff facility city health comprehensive care modern trusted', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (87, 'Phòng khám Đa khoa Quốc tế Hàng Xanh', 'phong-kham-da-khoa-hang-xanh', '393-395-397-399 Điện Biên Phủ, Phường Thạnh Mỹ Tây, Thành phố Hồ Chí Minh (Địa chỉ cũ: 393-395-397-399 Điện Biên Phủ, Phường 25, Quận Bình Thạnh, Thành phố Hồ Chí Minh, Việt Nam)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15676.494630988464!2d106.712384!3d10.80184!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528a52ee28255%3A0x8cfadae18d6b5dcd!2zUEjDkk5HIEtIw4FNIMSQQSBLSE9BIEjDgE5HIFhBTkg!5e0!3m2!1svi!2sus!4v1757298268073!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/8e8622a2-6c59-4203-af31-7df30f7bacdb-z6516578651975_6c1ed84f2b977ce61a1455523fb54062.jpg', 'department care expert district central focused comprehensive efficient health clean', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (88, 'Trung tâm Y tế Quận Cái Răng', 'ttytcairang', 'Đường Trần Chiên, khu vực Thạnh Mỹ, phường Lê Bình, quận Cái Răng, thành phố Cần Thơ', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15716.87540422083!2d105.758958!3d9.998773!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a089c79c62d345%3A0xdf428513acbe1f5d!2zVHJ1bmcgdMOibSB5IHThur8gcXXhuq1uIEPDoWkgUsSDbmc!5e0!3m2!1svi!2sus!4v1757298370708!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/26f88115-0930-4026-8afc-4546a2cfae9a-a9b78abf-700c-4954-807e-a7bac5d2e9fd-aaanh_maaan_hiaanh_2024-10-18_luaac_16.45.05.png', 'focused care modern patient treatment central advanced professional friendly community', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (89, 'Trung Tâm Y Tế Khu Vực Vĩnh Thạnh', 'trung-tam-y-te-vinh-thanh', 'Số 283, QL80, ấp Vĩnh Tiến, xã Vĩnh Thạnh, TP Cần Thơ (Địa chỉ cũ: Số 283, QL80, Vĩnh Tiến, Thị trấn Vĩnh Thạnh, Huyện Vĩnh Thạnh, Thành phố Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15706.029647988022!2d105.387775!3d10.220598!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0a01cd2eb3b15%3A0x35c98069544a9ed6!2zQuG7h25oIHZp4buHbiDEkGEga2hvYSBIdXnhu4duIFbEqW5oIFRo4bqhbmg!5e0!3m2!1svi!2sus!4v1757298573771!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/0caf90b7-8e5c-4a5e-b260-66fc81b361e2-z6918269314916_90e14454c95e43806d2f8f3b0b4bea23.jpg', 'friendly advanced reliable expert center city district support modern health', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (90, 'Trạm Y tế xã Thạnh Tiến', 'tytthanhtienct', 'Ấp Phụng Phụng, xã Thạnh Tiến, huyện Vĩnh Thạnh, TP Cần Thơ', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15707.756809002145!2d105.34178371002483!3d10.185592939541058!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0a14000000001%3A0xd2878bb7e2b9f54c!2zVHLhuqFtIFkgVOG6vyBYw6MgVGjhuqFuaCBUaeG6v24!5e0!3m2!1sen!2s!4v1729349579319!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/4a320423-98a3-4e89-8b69-c2cbfa345a47-aaanh_maaan_hiaanh_2024-10-18_luaac_16.45.05.png', 'comprehensive reliable care city center quality friendly trusted district medical', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (91, 'Trạm Y tế phường Thuận Hưng', 'tram-y-te-phuong-thuan-hung-can-tho', 'Số 490 - Đường Mai Văn Bộ, khu vực Tân Phú, phường Thuận Hưng, quận Thốt Nốt, thành phố Cần Thơ', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15706.178713912022!2d105.5779273!3d10.2175815!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a09d6818eb698b%3A0xe030fae3503006b9!2zVHLhuqFtIHkgdOG6vyBQaMaw4budbmcgVGh14bqtbiBIxrBuZw!5e0!3m2!1sen!2s!4v1729222977828!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/a9b78abf-700c-4954-807e-a7bac5d2e9fd-aaanh_maaan_hiaanh_2024-10-18_luaac_16.45.05.png', 'service accessible central health district professional clean community patient medical', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (92, 'Phòng khám Y Học Cổ Truyền Y Khoa Thái Dương', 'phong-kham-y-hoc-co-truyen-y-khoa-thai-duong', '15A Trần Khánh Dư, P. Ninh Kiều, TP. Cần Thơ (Địa chỉ cũ: 15A Đ. Trần Khánh Dư, Xuân Khánh, Ninh Kiều, Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d251449.1542604571!2d105.774185!3d10.025685!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a088244652c3b5%3A0xc4f2fd9add381a99!2zUGjDsm5nIEtow6FtIFkga2hvYSBUaMOhaSBExrDGoW5n!5e0!3m2!1svi!2sus!4v1757298625146!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/7169a6d4-9299-437f-8445-cdf91f64aea5-y_khoa_thai_duong_(1).png', 'support expert community city center district treatment medical quality health', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (93, 'Bệnh viện Da Liễu Cần Thơ', 'bvdalieuct', 'Số 12/1, đường 3/2, Phường Tân An, Thành phố Cần Thơ (Địa chỉ cũ: Số 12/1, đường 3/2, P. Hưng Lợi, Q. Ninh Kiều, TP. Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15716.178663454171!2d105.7552167!3d10.0131694!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0884b26082bb7%3A0x45bdfaf0af8ecb7f!2sCan%20Tho%20Dermatology%20Hospital!5e0!3m2!1sen!2s!4v1726208459700!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/d0607325-69c3-40d9-8332-a4309e19160f-screenshot-20240809-110956.png', 'medical friendly central facility reliable comprehensive focused professional treatment comfortable', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (94, 'Trung tâm Kiểm soát Bệnh tật Cần Thơ - Tiêm chủng', 'cdcqltcct', 'Số 01 Ngô Đức Kế, Phường Tân An, TP Cần Thơ (Địa chỉ cũ: Số 01 Ngô Đức Kế, P. Tân An, Q.Ninh Kiều, TP.Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3929.0147537702974!2d105.73439167525096!3d10.015639572764984!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a08990cafc4175%3A0x9eaaf870ba75bf92!2zUGjDsm5nIHRpw6ptIG5n4burYSBk4buLY2ggduG7pSAtIENEQyBD4bqnbiBUaMah!5e0!3m2!1sen!2s!4v1730949071560!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/efbc17c6-4793-4f5c-867a-75857bf79a96-cdc-logo-final-2-120x120_(1).png', 'clean health quality accessible community expert efficient medical reliable department', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (95, 'Bệnh viện Bưu Điện', 'bvbuudien', 'Lô B9, Đ.Thành Thái, Phường Hòa Hưng, Thành phố Hồ Chí Minh (Địa chỉ cũ: Lô B9, Đ.Thành Thái, Phường 15, Quận 10, TP. HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.40486818061!2d106.65787487476518!3d10.780270989368697!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752ec58996bda3%3A0x7d7bb62c7f2635db!2zQuG7h25oIHZp4buHbiDEkGEgS2hvYSBCxrB1IMSQaeG7h24!5e0!3m2!1svi!2s!4v1733904054642!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/b37d0c5b-5eb4-421c-b78b-c09584b28696-pandt.png', 'department focused center reliable medical service staff support quality health', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (96, 'Bệnh viện Đa khoa Lãnh Binh Thăng', 'benh-vien-quan-11', '72 Đường số 5, Phường Bình Thới, TP. Hồ Chí Minh (Địa chỉ cũ: 72 Đường số 5 - Cư xá Bình Thới, Phường 8, Quận 11, Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7839.3224664219715!2d106.63910910487179!3d10.76057040973467!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f90d969433b%3A0x9bd571c18375e5b0!2zQuG7h25oIHZp4buHbiBMw6NuaCBCaW5oIFRoxINuZw!5e0!3m2!1svi!2s!4v1757067364976!5m2!1svi!2s', NULL, 'https://cdn.medpro.vn/prod-partner/747ccdf2-dcd0-4735-8be6-2af31c0dd11a-z6908651526323_af0739ca6057049556194737232fd20f.jpg', 'staff community quality trusted support patient advanced clean reliable service', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (97, 'Bệnh Viện 199', 'benhvien199', '216 Nguyễn Công Trứ, Phường An Hải, Thành phố Đà Nẵng (Địa chỉ cũ: 216 Nguyễn Công Trứ, Quận Sơn Trà, Đà Nẵng)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3833.988051900439!2d108.2362052!3d16.066109800000003!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3142182a592ea333%3A0x4b9afc99cc59a06c!2zQuG7h25oIHZp4buHbiAxOTkgLSBC4buZIEPDtG5nIEFu!5e0!3m2!1svi!2s!4v1702271139054!5m2!1svi!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/6f701d9d-f1ee-4001-bee9-a884daa27624-199_logo.png', 'staff reliable clean professional trusted quality medical treatment focused efficient', 4.5);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (98, 'Trung tâm Kiểm soát Bệnh tật Cần Thơ - Khám bệnh', 'cdcct', 'Số 400 Nguyễn Văn Cừ Nối dài, Phường An Bình, TP Cần Thơ (Địa chỉ cũ: Số 400 Nguyễn Văn Cừ Nối dài, An Bình, Ninh Kiều, TP Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d982.2541352450239!2d105.737467!3d10.015492!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0898604788c05%3A0xba409d8ef80c4bdd!2zVHJ1bmcgVMOibSBLaeG7g20gU2_DoXQgQuG7h25oIFThuq10IHRow6BuaCBwaOG7kSBD4bqnbiBUaMah!5e0!3m2!1svi!2sus!4v1724226682420!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/50629ca6-4781-48a6-b5bf-c3bc9b2b2a58-cdc-logo-final-2-120x120.png', 'care professional community department service clinic support advanced clean center', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (99, 'Bệnh viện Lao và Bệnh phổi Cần Thơ', 'bvlaophoict', 'Khu vực Bình Hòa A, Phường Phước Thới, Thành phố Cần Thơ (Địa chỉ cũ: KV. Bình Hòa A, Phường Phước Thới, Quận Ô Môn, TP. Cần Thơ)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7855.880740991512!2d105.668588!3d10.103967!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a08434ce6038d3%3A0x6aac74e99677480b!2zQuG7h25oIHZp4buHbiBMYW8gdsOgIELhu4duaCBwaOG7lWkgVFAuIEPhuqduIFRoxqE!5e0!3m2!1svi!2sus!4v1724226307937!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/f8a7b79c-0c40-46a5-9ac5-72e765f045c6-logo.png', 'comfortable central district facility care modern health trusted community quality', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (100, 'Bệnh viện Đa khoa Quốc tế Nam Sài Gòn', 'bvnsaigon', 'Số 88, Đường số 8, Khu dân cư Trung Sơn, xã Bình Hưng, TP Hồ Chí Minh (Địa chỉ cũ: Số 88, Đường số 8, Khu dân cư Trung Sơn, Xã Bình Hưng, Huyện Bình Chánh, TP. Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.978854588012!2d106.68822687567626!3d10.736112989410195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752fb26cff77af%3A0x324f3dabf8102e47!2zQuG7h25oIFZp4buHbiDEkGEgS2hvYSBRdeG7kWMgVOG6vyBOYW0gU8OgaSBHw7Ju!5e0!3m2!1sen!2s!4v1703218430299!5m2!1sen!2s', NULL, 'https://cdn-pkh.longvan.net/prod-partner/2784355c-85d1-4abb-ba43-5f44492af084-z4628247786517_aed053f6ed942af52b7a15a051b543b9.jpg', 'health accessible center department support care professional service friendly comprehensive', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (101, 'Phòng khám đa khoa Nhật Bản - Ishii Sài Gòn', 'ishiisaigon', '616A Nguyễn Chí Thanh, Phường 4, Quận 11, Thành phố Hồ Chí Minh', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15678.76804236592!2d106.6593076!3d10.7582029!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752fd8dfb7ed85%3A0x3ae7b288855e9b15!2zSXNoaWkgU8OgaSBHw7JuIC0gUGjDsm5nIEtow6FtIMSQYSBLaG9hIE5o4bqtdCBC4bqjbg!5e0!3m2!1sen!2s!4v1733368849741!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/98c38140-d7f2-47d2-929b-6b1230841770-logo-vuong-2000.jpg', 'clinic facility advanced support service staff health focused district department', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (102, 'Trung tâm Chẩn đoán Y khoa Kỹ thuật cao Thiện Nhân', 'bvthiennhandn', '276-278-280 Đống Đa, P. Hải Châu, TP. Đà Nẵng (Địa chỉ cũ: 276-278-280 Đống Đa, P.Thanh Bình, Q.Hải Châu, Đà Nẵng)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3833.7863625665714!2d108.21299447532445!3d16.076572239240928!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x314218383b61dfa5%3A0x55e6c1c8afe2c7af!2zVGhp4buHbiBOaMOibiBIb3NwaXRhbA!5e0!3m2!1sen!2s!4v1734072579323!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/d81be613-db08-4cc0-ae41-a048ce25cd7b-logo-new-t10-1.png', 'health clinic community comfortable center medical city clean support service', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (103, 'Trung tâm xét nghiệm Gentis', 'gentis', '8/24 Nguyễn Đình Khơi, Phường 4, Quận Tân Bình, TP.HCM', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1959.6061153175021!2d106.656242!3d10.795051!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317529344746ffff%3A0x8246aea3e499f5ec!2sTrung%20t%C3%A2m%20Gentis!5e0!3m2!1svi!2sus!4v1730881148505!5m2!1svi!2sus', NULL, 'https://cdn.medpro.vn/prod-partner/834e6603-bc42-420b-9367-32035f4adb4f-logo-1.png', 'advanced facility trusted patient focused health central city expert staff', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (104, 'Phòng khám Đa khoa DUCLINH LAB', 'pkduclinhlab', 'Số 46 đường DT 766, Thôn 4, Xã Hoài Đức, Tỉnh Lâm Đồng (Địa chỉ cũ: Số 46 Đường DT766, Thôn 4, Xã Đức Hạnh, Huyện Đức Linh, Tỉnh Bình Thuận)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3914.5687678209592!2d107.50130197526113!3d11.145459652277953!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317443c0c6823f81%3A0xceada8294241727e!2zUGjDsm5nIEtow6FtIMSQYSBLaG9hIFF14buRYyBU4bq_IER1Y2xpbmggTGFi!5e0!3m2!1sen!2s!4v1733798692434!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/ef9331a1-e1bd-46d1-b3ea-ac2400652f4e-images_(2).png', 'comfortable trusted focused clinic staff medical patient professional accessible community', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (105, 'Trạm Y tế phường Thường Thạnh', 'tytthuongthanhct', 'Khu vực Phú Quới, phường Thường Thạnh, quận Cái Răng, thành phố Cần Thơ.', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15718.81738568163!2d105.7612436!3d9.9585381!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a08b5021b572a7%3A0x907b453e065fe16c!2zVHLhuqFtIFkgdOG6vyBwaMaw4budbmcgVGjGsOG7nW5nIFRo4bqhbmg!5e0!3m2!1sen!2s!4v1729244192730!5m2!1sen!2s', NULL, 'https://cdn.medpro.vn/prod-partner/381d76e8-6971-418b-bce5-b49f8a832ed2-aaanh_maaan_hiaanh_2024-10-18_luaac_16.45.05.png', 'efficient trusted friendly professional medical quality city expert center clinic', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (106, 'Bệnh viện Hoàn Mỹ Bình Dương (Vạn Phúc 1)', 'hoanmyvp1', '45 Hồ văn Cống, Phường Chánh Hiệp, Thành phố Hồ Chí Minh (Địa chỉ cũ: 45 Hồ văn Cống, Khu Phố 4, P. Tương Bình Hiệp TP. Thủ Dầu Một, Bình Dương)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15665.832280379285!2d106.6418142!3d11.0042176!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xaaecdded25cae2b0!2zQuG7h25oIFZp4buHbiDEkGEgS2hvYSBW4bqhbiBQaMO6Yw!5e0!3m2!1svi!2s!4v1642565921377!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/hoanmyvp1/app/image/logo_circle.png?t=123', 'quality department central trusted treatment patient care medical friendly efficient', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (107, 'Bệnh viện Quốc tế City - CIH', 'quoctecity', '03 Đường 17A, phường An Lạc, TP. Hồ Chí Minh (Địa chỉ cũ: Số 3, Đường 17A, P.Bình Trị Đông B, Q. Bình Tân, TP. Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.9158466638382!2d106.60655997567633!3d10.740969089405615!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752dc8efe35abb%3A0xd63a1887de44cbb5!2sCity%20International%20Hospital!5e0!3m2!1sen!2s!4v1703218064310!5m2!1sen!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/quoctecity/web/logo.png?t=Wed%20Mar%2008%202023%2015:58:38%20GMT+0700%20(Indochina%20Time)', 'service reliable health efficient advanced comprehensive central city department comfortable', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (108, 'Bệnh viện Gia An 115', 'bvgiaan', '05 Đường 17A, Khu phố 11, phường An Lạc, TP. Hồ Chí Minh (Địa chỉ cũ: Số 05, Đường 17A, Khu phố 11, P. Bình Trị Đông B, Q. Bình Tân, TP. HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.9206613843344!2d106.6046169756763!3d10.740598089406058!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752dde89c8cc79%3A0xbdfa522ff3d97b86!2zQuG7h25oIHZp4buHbiBHaWEgQW4gMTE1!5e0!3m2!1sen!2s!4v1703218116386!5m2!1sen!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/5dba7374-502a-4acf-9676-64fbeb981dc6-logo_bv_gia_an_115_gai_kh-01_%282%29.png', 'comprehensive support treatment city medical facility clean efficient health central', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (109, 'Bệnh viện Đa Khoa Medic Bình Dương', 'bvmedicbd', '14A Nguyễn An Ninh, Phường Thủ Dầu Một, Tỉnh Bình Dương (Địa chỉ cũ: 14A Nguyễn An Ninh, phường Phú Cường, Tp Thủ Dầu Một, Tỉnh Bình Dương)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3916.8335321960576!2d106.6569932!3d10.9759347!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3174d13742517043%3A0x68362bdf30ed8309!2zQuG7hk5IIFZJ4buGTiDEkEEgS0hPQSBNRURJQyBCw4xOSCBExq_GoE5H!5e0!3m2!1svi!2s!4v1666681718367!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/bvmedicbd/app/image/logo_phieu_kham.png?t=Thu%20Dec%2022%202022%2009:38:53%20GMT+0700%20(Indochina%20Time)', 'expert department comprehensive focused clean staff professional efficient accessible quality', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (110, 'Bệnh viện Quốc tế Minh Anh', 'minhanh', '36 Đường số 1B, Phường An Lạc, TP.Hồ Chí Minh (Địa chỉ cũ: 36 Đường số 1B, Phường Bình Trị Đông, Quận Bình Tân, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15679.31584504729!2d106.6145104!3d10.7476619!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x79f6044a501443e0!2zQuG7h25oIHZp4buHbiBRdeG7kWMgdOG6vyBNaW5oIEFuaA!5e0!3m2!1svi!2s!4v1628756761333!5m2!1svi!2s', NULL, 'https://api-v2.medpro.com.vn:5000/st/hospitals/minhanh-logo.png', 'comfortable focused treatment clinic expert comprehensive efficient center professional staff', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (111, 'BỆNH VIỆN ĐẠI HỌC Y TÂN TẠO', 'bvdhtt', 'Lô 10 đường Đức Hoà Hạ, KCN Tân Đức, xã Đức Hoà, tỉnh Tây Ninh (Địa chỉ cũ: Lô 10 Đ. Số 1, Đức Hòa Hạ, Đức Hòa, Long An)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.230636432425!2d106.47440617567693!3d10.793639689356224!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310ad3860911521b%3A0xa8198cad7e066035!2sTan%20Tao%20Medical%20University%20Hospital!5e0!3m2!1sen!2s!4v1703218707708!5m2!1sen!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/bvdhtt/web/logo.png?t=Mon%20Feb%2013%202023%2015:11:38%20GMT+0700%20(Indochina%20Time)', 'district comfortable medical clean patient treatment comprehensive city efficient central', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (112, 'BỆNH VIỆN ĐA KHOA QUỐC TẾ THU CÚC', 'bvthucuc', '286 Thụy Khuê, Phường Tây Hồ, Thành phố Hà Nội (Địa chỉ cũ: 286 Thụy Khuê, Tây Hồ, Hà Nội)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.688954758927!2d105.81189247586026!3d21.045128180608295!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab106314aebf%3A0x850c9776536470f4!2sThu%20Cuc%20International%20General%20Hospital!5e0!3m2!1sen!2s!4v1703219122380!5m2!1sen!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/41e23b05-2306-46e3-b242-a1cd0d5d8cf6-z4528799593792_e3f3029161d06103a8f6ea0ccff4651f.jpg', 'efficient staff community friendly medical patient center care district advanced', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (113, 'Bệnh Viện Đa Khoa An Việt', 'bvanviet', 'Số 1E Trường Chinh, Phường Tương Mai, Thành phố Hà Nội (Địa chỉ cũ: Số 1E Trường Chinh, Thanh Xuân, Hà Nội)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.886276795468!2d105.83982047585911!3d20.99719528064417!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ad41926e395b%3A0xbea94536706a6300!2zQuG7h25oIFZp4buHbiDEkGEgS2hvYSBBbiBWaeG7h3Q!5e0!3m2!1sen!2s!4v1703220373216!5m2!1sen!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/076f68b1-2f13-408c-894a-0f89dbc8e54e-an_viet_logo.png', 'advanced professional medical city clinic quality facility district department treatment', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (114, 'Bệnh viện Phụ Sản Quốc Tế Sài Gòn', 'bvphusanquoctesaigon', '63 Bùi Thị Xuân, Phường Bến Thành, Thành phố Hồ Chí Minh (Địa chỉ cũ: 63 Bùi Thị Xuân, Phường Phạm Ngũ Lão, Quận 1, Thành phố Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.541781698275!2d106.68602797567654!3d10.769754189378595!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f3d302e7dc5%3A0x26c135da33a4c039!2sSI%20Hospital!5e0!3m2!1sen!2s!4v1703221452852!5m2!1sen!2s" width="600" height="450" style="border:0;', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/8bc156ea-e3fb-44d3-a2cb-4a2fea90f47f-logo-sihospital-512x512.png', 'community staff city accessible health medical district support friendly quality', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (115, 'Bệnh viện đa khoa Hồng Hà', 'bvhongha', '16 Nguyễn Như Đổ - Văn Miếu - Đống Đa - Hà Nội', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14896.589027629812!2d105.839084!3d21.0267931!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab998d7fb6a5%3A0x931a195055921e1e!2sHong%20Ha%20General%20Hospital!5e0!3m2!1sen!2s!4v1703228344476!5m2!1sen!2s', NULL, 'https://cdn-pkh.longvan.net/prod-partner/dffe4e92-3109-4529-8994-5f96a7e37d13-haaang_haa_.png', 'health friendly reliable comfortable care trusted service central department accessible', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (116, 'Phòng Khám Nhi Đồng Hiếu Phúc', 'pkhieuphuc', '489 Minh Phụng, phường Bình Thới, TP. Hồ Chí Minh (Địa chỉ cũ: 489 Minh Phụng, Phường 10, Quận 11, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.645104821542!2d106.64179297399916!3d10.761810859465273!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752e91af119079%3A0xfcb15e926a09462d!2zNDg5IMSQLiBNaW5oIFBo4bulbmcsIFBoxrDhu51uZyAxMCwgUXXhuq1uIDExLCBUaMOgbmggcGjhu5EgSOG7kyBDaMOtIE1pbmgsIFZpZXRuYW0!5e0!3m2!1sen!2s!4v1692260318648!5m2!1sen!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/f8279831-1eb4-4566-bfb4-40282070e6ff-logo_pk_hieu_phuc.png', 'health reliable quality central patient department medical facility clean district', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (117, 'Trung tâm Y khoa Pasteur Đà Lạt', 'trung-tam-y-khoa-pasteur-da-lat', '5 Thống Nhất; Xã Đức Trọng; Tỉnh Lâm Đồng (Địa chỉ cũ: 5 Thống Nhất, TT. Liên Nghĩa, H. Đức Trọng, T. Lâm Đồng)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3906.4075691047087!2d108.3741737!3d11.736311099999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3171414377b76909%3A0xd5079b8084019ae5!2zNSBUaOG7kW5nIE5o4bqldCwgTGnDqm4gTmdoxKlhLCDEkOG7qWMgVHLhu41uZywgTMOibSDEkOG7k25nIDAyNjMz!5e0!3m2!1sen!2s!4v1695607673625!5m2!1sen!2s', NULL, 'https://cdn-pkh.longvan.net/prod-partner/1c9a0384-9f72-4328-8f11-c1ed78baa346-z4668682726648_6cbe1bd3bbfa7dd5237805ab8e62016b.jpg', 'advanced central clinic treatment service center patient community clean accessible', 4.7);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (118, 'Phòng khám đa khoa Saigon Healthcare', 'sghealthcare', '45 Thành Thái, phường Diên Hồng, TP. Hồ Chí Minh (Địa chỉ cũ: 45 Thành Thái, Phường 14, Quận 10, Thành phố Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.513772475733!2d106.6652146!3d10.771906500000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f759705daad%3A0xfd3afa7b5bedfa9f!2sPh%C3%B2ng%20Kh%C3%A1m%20%C4%90a%20Khoa%20SaiGon%20Healthcare!5e0!3m2!1svi!2s!4v1757299042088!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/sghealthcare/web/logo.png?1678267424855?t=Wed%20Mar%2008%202023%2016:23:44%20GMT+0700%20(Indochina%20Time)', 'professional accessible health comprehensive city expert comfortable quality clinic advanced', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (119, 'Trung Tâm Sức Khỏe Nam giới Men''s Health', 'pkmenhealth', '7B/31 Thành Thái, Phường Diên Hồng, TP. Hồ Chí Minh (Địa chỉ cũ: 7B/31 Thành Thái, P14, Quận 10, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.5499673458303!2d106.661549!3d10.7691251!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752ee9d334c611%3A0x781ffcf0f7019265!2zVHJ1bmcgdMOibSBz4bupYyBraOG7j2UgTmFtIGdp4bubaSBNZW4ncyBIZWFsdGg!5e0!3m2!1svi!2s!4v1757299102672!5m2!1svi!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/cd5cb4e2-729b-473f-a871-00b49fea875d-logo_menhealth_1.png', 'facility expert trusted clean medical center reliable advanced clinic district', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (120, 'Trung Tâm Chăm Sóc Sức Khoẻ Cộng Đồng - CHAC', 'chac', '110A Ngô Quyền, phường An Đông, TP. Hồ Chí Minh (Địa chỉ cũ: 110A Ngô Quyền, phường 8, quận 5, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.7405308054226!2d106.66656549999999!3d10.754469499999997!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752efa465f16f9%3A0x4659dbaf1c3c039c!2zUGjDsm5nIEtow6FtIMSQYSBLaG9hIENoYWM!5e0!3m2!1svi!2s!4v1757299270174!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/chac/web/logo.png?1671769876976?t=Fri%20Dec%2023%202022%2011:31:16%20GMT+0700%20(Indochina%20Time)', 'treatment patient modern staff facility expert trusted professional care focused', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (121, 'Phòng khám Đa khoa SIM Med', 'simmed', 'Toà nhà Richstar 2 - RS5, 239-241 Đường Hòa Bình, phường Phú Thạnh, TP. Hồ Chí Minh (Địa chỉ cũ: Toà nhà Richstar 2 - RS5, 239-241 đường Hòa Bình, P. Hiệp Tân, Q.Tân Phú,TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.508445929988!2d106.62245457457487!3d10.77231575927045!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752d48fad16ee7%3A0x723f5c70747ff4e2!2sTh%C3%A1p%20RS5-%20Richstar%20Khu%202!5e0!3m2!1svi!2s!4v1728982554246!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/simmed/app/image/logo_circle.png?t=Mon%20Dec%2005%202022%2014:06:12%20GMT+0700%20(Indochina%20Time)', 'focused efficient health modern staff advanced comprehensive treatment facility center', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (122, 'Phòng Khám Đa Khoa Quốc Tế An Hảo', 'pkdkanhao', '107C Ngô Quyền, Phường Chợ Lớn, TP. Hồ Chí Minh (Địa chỉ cũ: 107C Ngô Quyền, Phường 11, Quận 5, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.7320923334523!2d106.66416821515294!3d10.75511889233605!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752efa5c7b6aa3%3A0x14b5dbce6dfb4333!2zMTA3YyBOZ8O0IFF1eeG7gW4sIFBoxrDhu51uZyAxMSwgUXXhuq1uIDUsIFRow6BuaCBwaOG7kSBI4buTIENow60gTWluaCwgVmnhu4d0IE5hbQ!5e0!3m2!1svi!2s!4v1603265439127!5m2!1svi!2s', NULL, 'https://bo-api.medpro.com.vn:5000/static/images/pkdkanhao/app/image/logo_circle.png?t=Mon%20Dec%2005%202022%2011:04:38%20GMT+0700%20(Indochina%20Time)', 'advanced community support central center staff professional modern comfortable clean', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (123, 'Phòng Khám Medic Sài Gòn', 'medicsaigon', 'Số 41/4 ấp Phú Bình, Xã Hòa Hiệp, Thành phố Hồ Chí Minh (Địa chỉ cũ: Số 41/4 ấp Phú Bình, Xã Hòa Hiệp, Huyện Xuyên Mộc, Tỉnh Bà Rịa - Vũng Tàu)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d125488.01811185952!2d107.372024276648!3d10.618166200585144!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175b024beda88a9%3A0xeabb33d9fe80ba05!2zUGjDsm5nIEtow6FtIMSQYSBLaG9hIE1lZGljIFPDoGkgR8Oybg!5e0!3m2!1svi!2s!4v1731556201188!5m2!1svi!2s', NULL, 'https://resource.medpro.com.vn/static/images/medicsaigon/web/logo.png', 'service advanced district reliable central staff trusted city efficient comfortable', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (124, 'Phòng Khám Đa khoa Quốc Tế Golden Healthcare', 'pkdkgolden', '37 Hoàng Hoa Thám, phường Tân Bình, TP. Hồ Chí Minh (Địa chỉ cũ: Số 37 Hoàng Hoa Thám, P. 13, Q.Tân Bình, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.1576426336937!2d106.6471382!3d10.799235600000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175299465a6aa4d%3A0x736056bf971601aa!2zUGjDsm5nIEtow6FtIMSQYSBLaG9hIFF14buRYyBU4bq_IEdvbGRlbiBIZWFsdGhjYXJl!5e0!3m2!1svi!2s!4v1757299448156!5m2!1svi!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/c22cfe55-487e-446a-a585-00993303511c-logo_golden_health_1.png', 'department comprehensive medical central support accessible professional service center trusted', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (125, 'Phòng Khám Đa khoa Diamond', 'pkdkdiamond', '181 Võ Thị Sáu, phường Xuân Hòa, TP. Hồ Chí Minh (Địa chỉ cũ: 181 Võ Thị Sáu, P.Võ Thị Sáu, Q.3, TP.HCM)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.384009849318!2d106.6861266!3d10.7818723!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f999ac5b45b%3A0x13371a9bc38ff371!2zUGjDsm5nIGtow6FtIMSQYSBraG9hIERpYW1vbmQ!5e0!3m2!1svi!2s!4v1757299517456!5m2!1svi!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/0c3a03d6-4667-4723-b11f-48a456b8d617-logo_diamond_1.png', 'center modern friendly department treatment care facility quality staff support', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (126, 'Trung tâm Y khoa Chuyên sâu Quốc tế Bernard', 'bernard', '201 Nam Kỳ Khởi Nghĩa, phường Xuân Hòa, TP. Hồ Chí Minh (Địa chỉ cũ: 201 Nam Kỳ Khởi Nghĩa, Phường 7, Quận 3, TP Hồ Chí Minh)', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.3131658012935!2d106.68630739999999!3d10.787309299999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f6e4576351b%3A0x11b24d9d5ca66b55!2zVHJ1bmcgdMOibSBZIGtob2EgQ2h1ecOqbiBzw6J1IFF14buRYyB04bq_IEJlcm5hcmQ!5e0!3m2!1svi!2s!4v1757299747008!5m2!1svi!2s', NULL, 'https://prod-partner.s3-hcm-r1.longvan.net/2a468ed7-0c82-43a9-abdc-36fcfc6f9ba1-bernard.png', 'focused service comprehensive clinic care friendly support trusted district medical', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (127, 'Phòng khám Medic Sài Gòn Cơ Sở 5', 'medicsaigon5', '637C Đường 30 Tháng 4, Rạch Dừa, TP. Vũng Tàu, Tỉnh Bà Rịa – Vũng Tàu', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3924.2667608405836!2d107.1158885!3d10.4003749!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317571914b9db709%3A0xb221eabf06fdb284!2zUGjDsm5nIEtow6FtIMSQYSBLaG9hIE1lZGljIFPDoGkgR8OybiA1!5e0!3m2!1svi!2s!4v1757299874821!5m2!1svi!2s', NULL, 'https://resource.medpro.com.vn/static/images/medicsaigon/web/logo.png', 'district modern trusted expert center community facility comprehensive medical service', 4.0);
INSERT INTO `Office` (`user_id`,`name`,`slug`,`address`,`googleMap`,`website`,`logo`,`description`,`rating`) VALUES (128, 'Phòng khám Medic Sài Gòn Cơ Sở 3', 'medicsaigon3', '1288 Hải Sơn, Phước Hưng, Long Điền, Tỉnh Bà Rịa - Vũng Tàu', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1004745.4267636312!2d105.54447378921037!3d10.888243811903218!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317575fb55ec50e1%3A0x2ebbffe2cccb548e!2zUGjDsm5nIEtow6FtIMSQYSBLaG9hICYgUGjDsm5nIFRpw6ptIENo4bunbmcgTUVESUMgU8OASSBHw5JOIDM!5e0!3m2!1svi!2s!4v1757300404877!5m2!1svi!2s', NULL, 'https://resource.medpro.com.vn/static/images/medicsaigon/web/logo.png', 'department accessible health medical facility community care central efficient clean', 4.0);







INSERT INTO `Doctor` (`office_id`,`doctor_name`,`email`,`photo`,`degree`,`graduate`,`specialty_id`) VALUES
(359, 'Dr. Tuan Anh', 'tuan.anh.1@clinicmail.com', 'doctor_359_1.jpg', 'MD', 'University of Tokyo', 23),
(359, 'Dr. Tran Long', 'tran.long.2@clinicmail.com', 'doctor_359_2.jpg', 'PhD', 'Stanford University', 8),
(360, 'Dr. Green Anh', 'green.anh.3@clinicmail.com', 'doctor_360_1.jpg', 'DO', 'Hanoi Medical University', 24),
(360, 'Dr. Duc Yuki', 'duc.yuki.4@clinicmail.com', 'doctor_360_2.jpg', 'PhD', 'Stanford University', 3),
(361, 'Dr. Emily Tuan', 'emily.tuan.5@clinicmail.com', 'doctor_361_1.jpg', 'MD', 'University of Sydney', 17),
(361, 'Dr. Duc Brown', 'duc.brown.6@clinicmail.com', 'doctor_361_2.jpg', 'MD, PhD', 'Stanford University', 23),
(362, 'Dr. Nam Nam', 'nam.nam.7@clinicmail.com', 'doctor_362_1.jpg', 'PhD', 'Karolinska Institute', 22),
(362, 'Dr. William Anh', 'william.anh.8@clinicmail.com', 'doctor_362_2.jpg', 'MD, PhD', 'Can Tho Medical University', 5),
(363, 'Dr. Green Khoa', 'green.khoa.9@clinicmail.com', 'doctor_363_1.jpg', 'DO', 'Thai Binh Medical University', 8),
(363, 'Dr. Mai Ngan', 'mai.ngan.10@clinicmail.com', 'doctor_363_2.jpg', 'MBBS', 'Columbia University', 22),
(364, 'Dr. Nam Thao', 'nam.thao.11@clinicmail.com', 'doctor_364_1.jpg', 'MBBS', 'University of Sydney', 1),
(364, 'Dr. An Anh', 'an.anh.12@clinicmail.com', 'doctor_364_2.jpg', 'DO', 'Seoul National University', 21),
(365, 'Dr. Hoa Anh', 'hoa.anh.13@clinicmail.com', 'doctor_365_1.jpg', 'MBBS', 'National University of Singapore', 1),
(365, 'Dr. Emily David', 'emily.david.14@clinicmail.com', 'doctor_365_2.jpg', 'MBBS', 'HCMC University of Medicine', 1),
(366, 'Dr. Vo Green', 'vo.green.15@clinicmail.com', 'doctor_366_1.jpg', 'DO', 'Imperial College London', 13),
(366, 'Dr. Yen Mai', 'yen.mai.16@clinicmail.com', 'doctor_366_2.jpg', 'MBBS', 'University of Hong Kong', 14),
(367, 'Dr. My Tuan', 'my.tuan.17@clinicmail.com', 'doctor_367_1.jpg', 'MD', 'Imperial College London', 6),
(367, 'Dr. John Anh', 'john.anh.18@clinicmail.com', 'doctor_367_2.jpg', 'MD', 'Oxford University', 14),
(368, 'Dr. Green Chau', 'green.chau.19@clinicmail.com', 'doctor_368_1.jpg', 'PhD', 'Hanoi Medical University', 21),
(368, 'Dr. An Nguyen', 'an.nguyen.20@clinicmail.com', 'doctor_368_2.jpg', 'MD, PhD', 'HCMC University of Medicine', 19),
(369, 'Dr. Nam Hoa', 'nam.hoa.21@clinicmail.com', 'doctor_369_1.jpg', 'PhD', 'Columbia University', 20),
(369, 'Dr. Long Hieu', 'long.hieu.22@clinicmail.com', 'doctor_369_2.jpg', 'PhD', 'Hue Medical College', 6),
(370, 'Dr. Le Long', 'le.long.23@clinicmail.com', 'doctor_370_1.jpg', 'DO', 'Fudan University', 23),
(370, 'Dr. Huy Son', 'huy.son.24@clinicmail.com', 'doctor_370_2.jpg', 'PhD', 'University of Sydney', 11),
(371, 'Dr. Vo Thao', 'vo.thao.25@clinicmail.com', 'doctor_371_1.jpg', 'MD', 'Yale University', 8),
(371, 'Dr. Giang Trang', 'giang.trang.26@clinicmail.com', 'doctor_371_2.jpg', 'MBBS', 'Karolinska Institute', 2),
(372, 'Dr. Duc David', 'duc.david.27@clinicmail.com', 'doctor_372_1.jpg', 'DO', 'Yale University', 24),
(372, 'Dr. Tuan Tuan', 'tuan.tuan.28@clinicmail.com', 'doctor_372_2.jpg', 'MD, PhD', 'Can Tho Medical University', 6),
(373, 'Dr. Sarah Ngan', 'sarah.ngan.29@clinicmail.com', 'doctor_373_1.jpg', 'MD, PhD', 'HCMC University of Medicine', 18),
(373, 'Dr. Duc Khoa', 'duc.khoa.30@clinicmail.com', 'doctor_373_2.jpg', 'MBBS', 'Thai Binh Medical University', 4),
(374, 'Dr. Yuki Yuki', 'yuki.yuki.31@clinicmail.com', 'doctor_374_1.jpg', 'MD, PhD', 'Thai Binh Medical University', 14),
(374, 'Dr. Tran Hanh', 'tran.hanh.32@clinicmail.com', 'doctor_374_2.jpg', 'MBBS', 'Columbia University', 24),
(375, 'Dr. Giang Nguyen', 'giang.nguyen.33@clinicmail.com', 'doctor_375_1.jpg', 'MD, PhD', 'Karolinska Institute', 13),
(375, 'Dr. Phan Green', 'phan.green.34@clinicmail.com', 'doctor_375_2.jpg', 'DO', 'Peking University', 9),
(376, 'Dr. Emily Ngan', 'emily.ngan.35@clinicmail.com', 'doctor_376_1.jpg', 'MD, PhD', 'Harvard Medical School', 13),
(376, 'Dr. Johnson Soo', 'johnson.soo.36@clinicmail.com', 'doctor_376_2.jpg', 'MBBS', 'Oxford University', 23),
(377, 'Dr. Huy Dung', 'huy.dung.37@clinicmail.com', 'doctor_377_1.jpg', 'PhD', 'University of Hong Kong', 22),
(377, 'Dr. Khanh Vy', 'khanh.vy.38@clinicmail.com', 'doctor_377_2.jpg', 'MD, PhD', 'Imperial College London', 24),
(378, 'Dr. Minh Quang', 'minh.quang.39@clinicmail.com', 'doctor_378_1.jpg', 'MD, PhD', 'University of Tokyo', 8),
(378, 'Dr. Phuc Tuan', 'phuc.tuan.40@clinicmail.com', 'doctor_378_2.jpg', 'MD, PhD', 'Hanoi Medical University', 3),
(379, 'Dr. Trang Smith', 'trang.smith.41@clinicmail.com', 'doctor_379_1.jpg', 'MBBS', 'HCMC University of Medicine', 20),
(379, 'Dr. Tuan Brown', 'tuan.brown.42@clinicmail.com', 'doctor_379_2.jpg', 'MD', 'University of Tokyo', 7),
(380, 'Dr. Park Chau', 'park.chau.43@clinicmail.com', 'doctor_380_1.jpg', 'PhD', 'Seoul National University', 5),
(380, 'Dr. Le Linh', 'le.linh.44@clinicmail.com', 'doctor_380_2.jpg', 'DO', 'Yale University', 19),
(381, 'Dr. Emily An', 'emily.an.45@clinicmail.com', 'doctor_381_1.jpg', 'MD, PhD', 'University of Melbourne', 17),
(381, 'Dr. Brown Green', 'brown.green.46@clinicmail.com', 'doctor_381_2.jpg', 'PhD', 'Thai Binh Medical University', 3),
(382, 'Dr. Long Hanh', 'long.hanh.47@clinicmail.com', 'doctor_382_1.jpg', 'DO', 'Cambridge University', 3),
(382, 'Dr. Soo Brown', 'soo.brown.48@clinicmail.com', 'doctor_382_2.jpg', 'PhD', 'Hue Medical College', 19),
(383, 'Dr. Vo Phuc', 'vo.phuc.49@clinicmail.com', 'doctor_383_1.jpg', 'MD, PhD', 'Karolinska Institute', 22),
(383, 'Dr. Sarah Ngan', 'sarah.ngan.50@clinicmail.com', 'doctor_383_2.jpg', 'MD, PhD', 'HCMC University of Medicine', 21),
(384, 'Dr. Bao Anh', 'bao.anh.51@clinicmail.com', 'doctor_384_1.jpg', 'DO', 'Oxford University', 1),
(384, 'Dr. Huy An', 'huy.an.52@clinicmail.com', 'doctor_384_2.jpg', 'DO', 'Duke University', 18),
(385, 'Dr. Duc Tuan', 'duc.tuan.53@clinicmail.com', 'doctor_385_1.jpg', 'MD', 'University of Hong Kong', 21),
(385, 'Dr. Phuc Bao', 'phuc.bao.54@clinicmail.com', 'doctor_385_2.jpg', 'DO', 'Johns Hopkins University', 4),
(386, 'Dr. Truong Phuc', 'truong.phuc.55@clinicmail.com', 'doctor_386_1.jpg', 'PhD', 'Yale University', 24),
(386, 'Dr. An Ngan', 'an.ngan.56@clinicmail.com', 'doctor_386_2.jpg', 'DO', 'Stanford University', 2),
(387, 'Dr. My Smith', 'my.smith.57@clinicmail.com', 'doctor_387_1.jpg', 'MBBS', 'University of Tokyo', 22),
(387, 'Dr. Thao Ngan', 'thao.ngan.58@clinicmail.com', 'doctor_387_2.jpg', 'MD, PhD', 'Thai Binh Medical University', 23),
(388, 'Dr. Truong Dung', 'truong.dung.59@clinicmail.com', 'doctor_388_1.jpg', 'PhD', 'Karolinska Institute', 10),
(388, 'Dr. Tuan Tri', 'tuan.tri.60@clinicmail.com', 'doctor_388_2.jpg', 'MD, PhD', 'Fudan University', 5),
(389, 'Dr. Soo Brown', 'soo.brown.61@clinicmail.com', 'doctor_389_1.jpg', 'DO', 'Karolinska Institute', 8),
(389, 'Dr. Minh Long', 'minh.long.62@clinicmail.com', 'doctor_389_2.jpg', 'DO', 'Seoul National University', 20),
(390, 'Dr. Hoang Johnson', 'hoang.johnson.63@clinicmail.com', 'doctor_390_1.jpg', 'PhD', 'Hue Medical College', 22),
(390, 'Dr. Hoai Son', 'hoai.son.64@clinicmail.com', 'doctor_390_2.jpg', 'MBBS', 'Peking University', 21),
(391, 'Dr. Hoai Minh', 'hoai.minh.65@clinicmail.com', 'doctor_391_1.jpg', 'MD, PhD', 'University of Sydney', 6),
(391, 'Dr. Nguyen Hieu', 'nguyen.hieu.66@clinicmail.com', 'doctor_391_2.jpg', 'MD, PhD', 'Hanoi Medical University', 17),
(392, 'Dr. Green Green', 'green.green.67@clinicmail.com', 'doctor_392_1.jpg', 'DO', 'National University of Singapore', 14),
(392, 'Dr. Yuki Quang', 'yuki.quang.68@clinicmail.com', 'doctor_392_2.jpg', 'MBBS', 'University of Sydney', 10),
(393, 'Dr. Bao Trang', 'bao.trang.69@clinicmail.com', 'doctor_393_1.jpg', 'MBBS', 'Can Tho Medical University', 23),
(393, 'Dr. Sarah Hieu', 'sarah.hieu.70@clinicmail.com', 'doctor_393_2.jpg', 'DO', 'Cambridge University', 6),
(394, 'Dr. Chau Huy', 'chau.huy.71@clinicmail.com', 'doctor_394_1.jpg', 'MBBS', 'Karolinska Institute', 5),
(394, 'Dr. David Huy', 'david.huy.72@clinicmail.com', 'doctor_394_2.jpg', 'MD', 'Johns Hopkins University', 9),
(395, 'Dr. Duc Nguyen', 'duc.nguyen.73@clinicmail.com', 'doctor_395_1.jpg', 'PhD', 'Fudan University', 5),
(395, 'Dr. Phan Johnson', 'phan.johnson.74@clinicmail.com', 'doctor_395_2.jpg', 'MD', 'Hue Medical College', 1),
(396, 'Dr. Bao Smith', 'bao.smith.75@clinicmail.com', 'doctor_396_1.jpg', 'MD', 'University of Toronto', 7),
(396, 'Dr. Tran An', 'tran.an.76@clinicmail.com', 'doctor_396_2.jpg', 'DO', 'Thai Binh Medical University', 5),
(397, 'Dr. Khanh Soo', 'khanh.soo.77@clinicmail.com', 'doctor_397_1.jpg', 'MBBS', 'Stanford University', 2),
(397, 'Dr. Giang Huy', 'giang.huy.78@clinicmail.com', 'doctor_397_2.jpg', 'MD', 'Yale University', 23),
(398, 'Dr. Soo Vy', 'soo.vy.79@clinicmail.com', 'doctor_398_1.jpg', 'DO', 'Johns Hopkins University', 22),
(398, 'Dr. Khanh Giang', 'khanh.giang.80@clinicmail.com', 'doctor_398_2.jpg', 'MBBS', 'University of Toronto', 11),
(399, 'Dr. Duc Dung', 'duc.dung.81@clinicmail.com', 'doctor_399_1.jpg', 'DO', 'Karolinska Institute', 22),
(399, 'Dr. Trang Nam', 'trang.nam.82@clinicmail.com', 'doctor_399_2.jpg', 'MD', 'University of Melbourne', 11),
(400, 'Dr. Hoai Huy', 'hoai.huy.83@clinicmail.com', 'doctor_400_1.jpg', 'PhD', 'Harvard Medical School', 23),
(400, 'Dr. Nguyen Nguyen', 'nguyen.nguyen.84@clinicmail.com', 'doctor_400_2.jpg', 'DO', 'University of Melbourne', 8),
(401, 'Dr. Nguyen Giang', 'nguyen.giang.85@clinicmail.com', 'doctor_401_1.jpg', 'DO', 'Harvard Medical School', 2),
(401, 'Dr. Vo David', 'vo.david.86@clinicmail.com', 'doctor_401_2.jpg', 'MBBS', 'Duke University', 9),
(402, 'Dr. Khanh Hanh', 'khanh.hanh.87@clinicmail.com', 'doctor_402_1.jpg', 'DO', 'Stanford University', 19),
(402, 'Dr. Park Tri', 'park.tri.88@clinicmail.com', 'doctor_402_2.jpg', 'MD, PhD', 'HCMC University of Medicine', 11),
(403, 'Dr. Yuki Huy', 'yuki.huy.89@clinicmail.com', 'doctor_403_1.jpg', 'PhD', 'University of Sydney', 15),
(403, 'Dr. Truong Huy', 'truong.huy.90@clinicmail.com', 'doctor_403_2.jpg', 'MD', 'National University of Singapore', 10),
(404, 'Dr. Phuc Bao', 'phuc.bao.91@clinicmail.com', 'doctor_404_1.jpg', 'DO', 'Peking University', 24),
(404, 'Dr. Hoa Hieu', 'hoa.hieu.92@clinicmail.com', 'doctor_404_2.jpg', 'MD', 'Thai Binh Medical University', 18),
(405, 'Dr. Truong An', 'truong.an.93@clinicmail.com', 'doctor_405_1.jpg', 'MD, PhD', 'Harvard Medical School', 11),
(405, 'Dr. Emily An', 'emily.an.94@clinicmail.com', 'doctor_405_2.jpg', 'MBBS', 'University of Tokyo', 18),
(406, 'Dr. Hoa Mai', 'hoa.mai.95@clinicmail.com', 'doctor_406_1.jpg', 'MBBS', 'Johns Hopkins University', 22),
(406, 'Dr. Chau Phuc', 'chau.phuc.96@clinicmail.com', 'doctor_406_2.jpg', 'PhD', 'Harvard Medical School', 22),
(407, 'Dr. Lan Smith', 'lan.smith.97@clinicmail.com', 'doctor_407_1.jpg', 'MD, PhD', 'Johns Hopkins University', 17),
(407, 'Dr. Yen Dung', 'yen.dung.98@clinicmail.com', 'doctor_407_2.jpg', 'MD, PhD', 'Harvard Medical School', 2),
(408, 'Dr. Phuc Brown', 'phuc.brown.99@clinicmail.com', 'doctor_408_1.jpg', 'MD', 'Hanoi Medical University', 9),
(408, 'Dr. Emily Huy', 'emily.huy.100@clinicmail.com', 'doctor_408_2.jpg', 'DO', 'Cambridge University', 10),
(409, 'Dr. Soo Mai', 'soo.mai.101@clinicmail.com', 'doctor_409_1.jpg', 'PhD', 'Yale University', 13),
(409, 'Dr. John Nguyen', 'john.nguyen.102@clinicmail.com', 'doctor_409_2.jpg', 'MBBS', 'Imperial College London', 22),
(410, 'Dr. Johnson Nam', 'johnson.nam.103@clinicmail.com', 'doctor_410_1.jpg', 'MD', 'Seoul National University', 23),
(410, 'Dr. An Yuki', 'an.yuki.104@clinicmail.com', 'doctor_410_2.jpg', 'MD', 'Seoul National University', 18),
(411, 'Dr. Long Hanh', 'long.hanh.105@clinicmail.com', 'doctor_411_1.jpg', 'MBBS', 'Duke University', 9),
(411, 'Dr. Anh Quang', 'anh.quang.106@clinicmail.com', 'doctor_411_2.jpg', 'MBBS', 'Columbia University', 22),
(412, 'Dr. Chau Thao', 'chau.thao.107@clinicmail.com', 'doctor_412_1.jpg', 'MD', 'Johns Hopkins University', 8),
(412, 'Dr. David Dung', 'david.dung.108@clinicmail.com', 'doctor_412_2.jpg', 'PhD', 'Thai Binh Medical University', 13),
(413, 'Dr. Khanh Khoa', 'khanh.khoa.109@clinicmail.com', 'doctor_413_1.jpg', 'MD, PhD', 'Imperial College London', 11),
(413, 'Dr. Long David', 'long.david.110@clinicmail.com', 'doctor_413_2.jpg', 'MD, PhD', 'Imperial College London', 14),
(414, 'Dr. William Lan', 'william.lan.111@clinicmail.com', 'doctor_414_1.jpg', 'MBBS', 'University of Sydney', 10),
(414, 'Dr. My Giang', 'my.giang.112@clinicmail.com', 'doctor_414_2.jpg', 'MBBS', 'Peking University', 20),
(415, 'Dr. Green Giang', 'green.giang.113@clinicmail.com', 'doctor_415_1.jpg', 'DO', 'University of Sydney', 15),
(415, 'Dr. Tran Brown', 'tran.brown.114@clinicmail.com', 'doctor_415_2.jpg', 'MD, PhD', 'National University of Singapore', 1),
(416, 'Dr. Nguyen Long', 'nguyen.long.115@clinicmail.com', 'doctor_416_1.jpg', 'MBBS', 'Can Tho Medical University', 12),
(416, 'Dr. Yen An', 'yen.an.116@clinicmail.com', 'doctor_416_2.jpg', 'MD', 'Fudan University', 12),
(417, 'Dr. Yuki Son', 'yuki.son.117@clinicmail.com', 'doctor_417_1.jpg', 'DO', 'Columbia University', 24),
(417, 'Dr. Tran Son', 'tran.son.118@clinicmail.com', 'doctor_417_2.jpg', 'MD, PhD', 'Duke University', 1),
(418, 'Dr. Duc Tuan', 'duc.tuan.119@clinicmail.com', 'doctor_418_1.jpg', 'PhD', 'Stanford University', 23),
(418, 'Dr. Thanh Linh', 'thanh.linh.120@clinicmail.com', 'doctor_418_2.jpg', 'MD', 'Seoul National University', 16),
(419, 'Dr. Duc Johnson', 'duc.johnson.121@clinicmail.com', 'doctor_419_1.jpg', 'MD, PhD', 'Imperial College London', 8),
(419, 'Dr. Sarah Thao', 'sarah.thao.122@clinicmail.com', 'doctor_419_2.jpg', 'PhD', 'Columbia University', 12),
(420, 'Dr. Emily Thao', 'emily.thao.123@clinicmail.com', 'doctor_420_1.jpg', 'MD', 'Oxford University', 5),
(420, 'Dr. Minh Nam', 'minh.nam.124@clinicmail.com', 'doctor_420_2.jpg', 'MD', 'Stanford University', 19),
(421, 'Dr. Chau Nguyen', 'chau.nguyen.125@clinicmail.com', 'doctor_421_1.jpg', 'MBBS', 'Stanford University', 18),
(421, 'Dr. Na An', 'na.an.126@clinicmail.com', 'doctor_421_2.jpg', 'MD, PhD', 'National University of Singapore', 10),
(422, 'Dr. Bao Hieu', 'bao.hieu.127@clinicmail.com', 'doctor_422_1.jpg', 'MD', 'Hanoi Medical University', 19),
(422, 'Dr. Na Soo', 'na.soo.128@clinicmail.com', 'doctor_422_2.jpg', 'PhD', 'Stanford University', 4),
(423, 'Dr. Hoa Tri', 'hoa.tri.129@clinicmail.com', 'doctor_423_1.jpg', 'DO', 'National University of Singapore', 17),
(423, 'Dr. Linh Soo', 'linh.soo.130@clinicmail.com', 'doctor_423_2.jpg', 'MD, PhD', 'University of Melbourne', 24),
(424, 'Dr. An David', 'an.david.131@clinicmail.com', 'doctor_424_1.jpg', 'MBBS', 'Imperial College London', 19),
(424, 'Dr. Green Phuc', 'green.phuc.132@clinicmail.com', 'doctor_424_2.jpg', 'MBBS', 'Duke University', 23),
(425, 'Dr. Trang Soo', 'trang.soo.133@clinicmail.com', 'doctor_425_1.jpg', 'DO', 'Hue Medical College', 3),
(425, 'Dr. Yen Tuan', 'yen.tuan.134@clinicmail.com', 'doctor_425_2.jpg', 'PhD', 'HCMC University of Medicine', 20),
(426, 'Dr. William Nguyen', 'william.nguyen.135@clinicmail.com', 'doctor_426_1.jpg', 'MD, PhD', 'Cambridge University', 15),
(426, 'Dr. An Quang', 'an.quang.136@clinicmail.com', 'doctor_426_2.jpg', 'DO', 'Columbia University', 11),
(427, 'Dr. Emily Phuc', 'emily.phuc.137@clinicmail.com', 'doctor_427_1.jpg', 'MD', 'Oxford University', 13),
(427, 'Dr. Minh Green', 'minh.green.138@clinicmail.com', 'doctor_427_2.jpg', 'MBBS', 'Peking University', 16),
(428, 'Dr. Johnson Bao', 'johnson.bao.139@clinicmail.com', 'doctor_428_1.jpg', 'PhD', 'University of Toronto', 15),
(428, 'Dr. Phan Chau', 'phan.chau.140@clinicmail.com', 'doctor_428_2.jpg', 'MD, PhD', 'Peking University', 12),
(429, 'Dr. Truong Tuan', 'truong.tuan.141@clinicmail.com', 'doctor_429_1.jpg', 'MD', 'Thai Binh Medical University', 14),
(429, 'Dr. Hoa Phuc', 'hoa.phuc.142@clinicmail.com', 'doctor_429_2.jpg', 'MD, PhD', 'Yale University', 24),
(430, 'Dr. Emily Trang', 'emily.trang.143@clinicmail.com', 'doctor_430_1.jpg', 'MD, PhD', 'Hue Medical College', 6),
(430, 'Dr. Le Phuc', 'le.phuc.144@clinicmail.com', 'doctor_430_2.jpg', 'DO', 'Yale University', 11),
(431, 'Dr. Yen Phuc', 'yen.phuc.145@clinicmail.com', 'doctor_431_1.jpg', 'MD', 'University of Toronto', 20),
(431, 'Dr. Na Son', 'na.son.146@clinicmail.com', 'doctor_431_2.jpg', 'MBBS', 'Johns Hopkins University', 16),
(432, 'Dr. Thanh Hanh', 'thanh.hanh.147@clinicmail.com', 'doctor_432_1.jpg', 'DO', 'Seoul National University', 17),
(432, 'Dr. Phan Johnson', 'phan.johnson.148@clinicmail.com', 'doctor_432_2.jpg', 'MBBS', 'Hanoi Medical University', 13),
(433, 'Dr. Tran Soo', 'tran.soo.149@clinicmail.com', 'doctor_433_1.jpg', 'MD', 'Oxford University', 11),
(433, 'Dr. Tuan Minh', 'tuan.minh.150@clinicmail.com', 'doctor_433_2.jpg', 'MBBS', 'Hanoi Medical University', 18),
(434, 'Dr. Le Vy', 'le.vy.151@clinicmail.com', 'doctor_434_1.jpg', 'MD, PhD', 'Fudan University', 15),
(434, 'Dr. Yen Linh', 'yen.linh.152@clinicmail.com', 'doctor_434_2.jpg', 'MD', 'National University of Singapore', 9),
(435, 'Dr. Lan Phuc', 'lan.phuc.153@clinicmail.com', 'doctor_435_1.jpg', 'MD', 'University of Melbourne', 24),
(435, 'Dr. Huy Tri', 'huy.tri.154@clinicmail.com', 'doctor_435_2.jpg', 'PhD', 'Can Tho Medical University', 2),
(436, 'Dr. Chau Bao', 'chau.bao.155@clinicmail.com', 'doctor_436_1.jpg', 'PhD', 'University of Hong Kong', 11),
(436, 'Dr. Linh Phuc', 'linh.phuc.156@clinicmail.com', 'doctor_436_2.jpg', 'MD', 'Duke University', 15),
(437, 'Dr. William Nam', 'william.nam.157@clinicmail.com', 'doctor_437_1.jpg', 'MD', 'National University of Singapore', 20),
(437, 'Dr. Hoang Johnson', 'hoang.johnson.158@clinicmail.com', 'doctor_437_2.jpg', 'PhD', 'Imperial College London', 5),
(438, 'Dr. Nam Bao', 'nam.bao.159@clinicmail.com', 'doctor_438_1.jpg', 'MBBS', 'Karolinska Institute', 6),
(438, 'Dr. Trang Trang', 'trang.trang.160@clinicmail.com', 'doctor_438_2.jpg', 'MD', 'Peking University', 3),
(439, 'Dr. Soo Quang', 'soo.quang.161@clinicmail.com', 'doctor_439_1.jpg', 'MBBS', 'University of Hong Kong', 15),
(439, 'Dr. Chau An', 'chau.an.162@clinicmail.com', 'doctor_439_2.jpg', 'MD, PhD', 'University of Hong Kong', 16),
(440, 'Dr. Hoai Anh', 'hoai.anh.163@clinicmail.com', 'doctor_440_1.jpg', 'MBBS', 'Yale University', 20),
(440, 'Dr. Phuc Hoa', 'phuc.hoa.164@clinicmail.com', 'doctor_440_2.jpg', 'MD', 'Columbia University', 14),
(441, 'Dr. Hoai Long', 'hoai.long.165@clinicmail.com', 'doctor_441_1.jpg', 'MD, PhD', 'National University of Singapore', 11),
(441, 'Dr. Giang Nam', 'giang.nam.166@clinicmail.com', 'doctor_441_2.jpg', 'PhD', 'University of Tokyo', 4),
(442, 'Dr. Quang Soo', 'quang.soo.167@clinicmail.com', 'doctor_442_1.jpg', 'MD', 'Can Tho Medical University', 2),
(442, 'Dr. Mai Phuc', 'mai.phuc.168@clinicmail.com', 'doctor_442_2.jpg', 'MBBS', 'University of Sydney', 9),
(443, 'Dr. John Giang', 'john.giang.169@clinicmail.com', 'doctor_443_1.jpg', 'DO', 'Can Tho Medical University', 8),
(443, 'Dr. Johnson Hieu', 'johnson.hieu.170@clinicmail.com', 'doctor_443_2.jpg', 'PhD', 'Can Tho Medical University', 19),
(444, 'Dr. Hoa Ngan', 'hoa.ngan.171@clinicmail.com', 'doctor_444_1.jpg', 'DO', 'Peking University', 23),
(444, 'Dr. Truong An', 'truong.an.172@clinicmail.com', 'doctor_444_2.jpg', 'MD, PhD', 'HCMC University of Medicine', 24),
(445, 'Dr. Bao Vy', 'bao.vy.173@clinicmail.com', 'doctor_445_1.jpg', 'MBBS', 'Karolinska Institute', 12),
(445, 'Dr. Anh An', 'anh.an.174@clinicmail.com', 'doctor_445_2.jpg', 'MD', 'University of Toronto', 17),
(446, 'Dr. Na Nam', 'na.nam.175@clinicmail.com', 'doctor_446_1.jpg', 'MBBS', 'University of Sydney', 7),
(446, 'Dr. Thao Vy', 'thao.vy.176@clinicmail.com', 'doctor_446_2.jpg', 'MD', 'Harvard Medical School', 2),
(447, 'Dr. Phan Lan', 'phan.lan.177@clinicmail.com', 'doctor_447_1.jpg', 'DO', 'University of Toronto', 10),
(447, 'Dr. Thao Hieu', 'thao.hieu.178@clinicmail.com', 'doctor_447_2.jpg', 'DO', 'University of Tokyo', 9),
(448, 'Dr. Yen Minh', 'yen.minh.179@clinicmail.com', 'doctor_448_1.jpg', 'MD, PhD', 'Cambridge University', 10),
(448, 'Dr. Le Anh', 'le.anh.180@clinicmail.com', 'doctor_448_2.jpg', 'MD, PhD', 'Yale University', 20),
(449, 'Dr. Anh Anh', 'anh.anh.181@clinicmail.com', 'doctor_449_1.jpg', 'MD, PhD', 'Hanoi Medical University', 14),
(449, 'Dr. William Yuki', 'william.yuki.182@clinicmail.com', 'doctor_449_2.jpg', 'MBBS', 'University of Sydney', 1),
(450, 'Dr. Nguyen Son', 'nguyen.son.183@clinicmail.com', 'doctor_450_1.jpg', 'DO', 'Hanoi Medical University', 13),
(450, 'Dr. Anh Ngan', 'anh.ngan.184@clinicmail.com', 'doctor_450_2.jpg', 'MD', 'Imperial College London', 20),
(451, 'Dr. An Tri', 'an.tri.185@clinicmail.com', 'doctor_451_1.jpg', 'MD', 'University of Melbourne', 23),
(451, 'Dr. Huy Brown', 'huy.brown.186@clinicmail.com', 'doctor_451_2.jpg', 'MD', 'Johns Hopkins University', 3),
(452, 'Dr. Sarah Bao', 'sarah.bao.187@clinicmail.com', 'doctor_452_1.jpg', 'PhD', 'Can Tho Medical University', 21),
(452, 'Dr. Soo David', 'soo.david.188@clinicmail.com', 'doctor_452_2.jpg', 'MBBS', 'Imperial College London', 22),
(453, 'Dr. Long Hieu', 'long.hieu.189@clinicmail.com', 'doctor_453_1.jpg', 'MBBS', 'HCMC University of Medicine', 4),
(453, 'Dr. Brown Phuc', 'brown.phuc.190@clinicmail.com', 'doctor_453_2.jpg', 'DO', 'University of Toronto', 24),
(454, 'Dr. Sarah Minh', 'sarah.minh.191@clinicmail.com', 'doctor_454_1.jpg', 'MBBS', 'Hanoi Medical University', 16),
(454, 'Dr. Soo Hanh', 'soo.hanh.192@clinicmail.com', 'doctor_454_2.jpg', 'MD', 'Karolinska Institute', 11),
(455, 'Dr. Brown Chau', 'brown.chau.193@clinicmail.com', 'doctor_455_1.jpg', 'PhD', 'Cambridge University', 3),
(455, 'Dr. Hoa Son', 'hoa.son.194@clinicmail.com', 'doctor_455_2.jpg', 'DO', 'Seoul National University', 1),
(456, 'Dr. Tuan Bao', 'tuan.bao.195@clinicmail.com', 'doctor_456_1.jpg', 'MD', 'Hue Medical College', 9),
(456, 'Dr. Hoang Chau', 'hoang.chau.196@clinicmail.com', 'doctor_456_2.jpg', 'MD, PhD', 'Johns Hopkins University', 14),
(457, 'Dr. Tran Brown', 'tran.brown.197@clinicmail.com', 'doctor_457_1.jpg', 'DO', 'Stanford University', 8),
(457, 'Dr. Long Hieu', 'long.hieu.198@clinicmail.com', 'doctor_457_2.jpg', 'MD, PhD', 'Thai Binh Medical University', 9),
(458, 'Dr. Nam Tuan', 'nam.tuan.199@clinicmail.com', 'doctor_458_1.jpg', 'MD', 'Hue Medical College', 18),
(458, 'Dr. An Nguyen', 'an.nguyen.200@clinicmail.com', 'doctor_458_2.jpg', 'PhD', 'Cambridge University', 16);






INSERT INTO Office_phone (office_id, phone) VALUES
(359,'0897280569'),
(360,'0796101361'),
(361,'0880881330'),
(362,'02858091833'),
(363,'0880128519'),
(364,'0707925878'),
(365,'02819690593'),
(366,'0858942454'),
(367,'0852484920'),
(368,'0929898892'),
(369,'0795834908'),
(370,'+650333327924'),
(371,'0359632235'),
(372,'0982552639'),
(373,'0248236598'),
(374,'0975855875'),
(375,'0342487969'),
(376,'02813924153'),
(377,'0288112459'),
(378,'0950919954'),
(379,'0709430689'),
(380,'0934845347'),
(381,'02408978216'),
(382,'0786014118'),
(383,'0855141558'),
(384,'0952874154'),
(385,'0907095440'),
(386,'0324460258'),
(387,'0782229340'),
(388,'02823391642'),
(389,'0704306882'),
(390,'0893308157'),
(391,'0320420352'),
(392,'0950212023'),
(393,'0939921803'),
(394,'0241535259'),
(395,'0282583934'),
(396,'0765297560'),
(397,'0243054298'),
(398,'0846791657'),
(399,'0355219519'),
(400,'0324144520'),
(401,'0981939052'),
(402,'0845584391'),
(403,'0822908412'),
(404,'02428626714'),
(405,'0243906024'),
(406,'0356796782'),
(407,'0285409891'),
(408,'0763774832'),
(409,'0242404403'),
(410,'0992041445'),
(411,'0357882756'),
(412,'0815899146'),
(413,'0782573508'),
(414,'0976920659'),
(415,'+656977830843'),
(416,'0990790131'),
(417,'0777705726'),
(418,'0794368716'),
(419,'02497649838'),
(420,'0794636780'),
(421,'0370729660'),
(422,'0338810550'),
(423,'0864838457'),
(424,'02881846959'),
(425,'0986264375'),
(426,'0345175225'),
(427,'0246938128'),
(428,'0359501082'),
(429,'0386726307'),
(430,'0835503550'),
(431,'0811529558'),
(432,'0884514296'),
(433,'+6580128765'),
(434,'0866286079'),
(435,'0858912142'),
(436,'0934641283'),
(437,'0831767103'),
(438,'0861305640'),
(439,'02480707192'),
(440,'+44678927113'),
(441,'0286260616'),
(442,'0888892216'),
(443,'+1891339636'),
(444,'02499400342'),
(445,'0947775242'),
(446,'02814141283'),
(447,'+445092866275'),
(448,'0923743120'),
(449,'0929526200'),
(450,'0283481309'),
(451,'+44728283623'),
(452,'0373147304'),
(453,'0345860495'),
(454,'0936885227'),
(455,'0992725540'),
(456,'0702213694'),
(457,'0286293794'),
(458,'0360511033');





INSERT INTO Office_has_specialty (office_id, specialty_id) VALUES
(359,23),
(359,22),
(359,1),
(360,1),
(360,6),
(360,23),
(361,1),
(361,4),
(361,5),
(362,11),
(362,8),
(362,16),
(363,19),
(363,1),
(363,17),
(364,1),
(364,24),
(364,18),
(365,4),
(365,6),
(365,11),
(366,3),
(366,6),
(366,12),
(367,2),
(367,13),
(367,17),
(368,18),
(368,24),
(368,6),
(369,12),
(369,11),
(369,23),
(370,21),
(370,9),
(370,8),
(371,19),
(371,22),
(371,7),
(372,1),
(372,11),
(372,5),
(373,5),
(373,16),
(373,13),
(374,20),
(374,4),
(374,8),
(375,4),
(375,3),
(375,15),
(376,15),
(376,22),
(376,21),
(377,10),
(377,13),
(377,1),
(378,10),
(378,18),
(378,22),
(379,20),
(379,7),
(379,21),
(380,22),
(380,1),
(380,15),
(381,16),
(381,1),
(381,6),
(382,8),
(382,13),
(382,3),
(383,1),
(383,3),
(383,21),
(384,6),
(384,19),
(384,20),
(385,13),
(385,22),
(385,17),
(386,16),
(386,24),
(386,14),
(387,14),
(387,12),
(387,1),
(388,20),
(388,6),
(388,1),
(389,2),
(389,9),
(389,14),
(390,16),
(390,9),
(390,6),
(391,1),
(391,21),
(391,10),
(392,21),
(392,8),
(392,22),
(393,22),
(393,23),
(393,10),
(394,3),
(394,19),
(394,21),
(395,17),
(395,6),
(395,15),
(396,20),
(396,19),
(396,15),
(397,5),
(397,2),
(397,6),
(398,2),
(398,4),
(398,21),
(399,19),
(399,23),
(399,20),
(400,14),
(400,5),
(400,13),
(401,11),
(401,24),
(401,3),
(402,10),
(402,2),
(402,14),
(403,8),
(403,13),
(403,7),
(404,16),
(404,19),
(404,2),
(405,18),
(405,19),
(405,8),
(406,20),
(406,14),
(406,24),
(407,23),
(407,12),
(407,5),
(408,6),
(408,7),
(408,19),
(409,3),
(409,12),
(409,18),
(410,18),
(410,24),
(410,21),
(411,13),
(411,24),
(411,14),
(412,4),
(412,5),
(412,11),
(413,4),
(413,14),
(413,1),
(414,16),
(414,14),
(414,15),
(415,24),
(415,13),
(415,8),
(416,9),
(416,19),
(416,13),
(417,8),
(417,18),
(417,6),
(418,19),
(418,22),
(418,9),
(419,6),
(419,3),
(419,11),
(420,8),
(420,13),
(420,16),
(421,5),
(421,14),
(421,9),
(422,23),
(422,20),
(422,15),
(423,8),
(423,24),
(423,22),
(424,20),
(424,16),
(424,12),
(425,20),
(425,24),
(425,9),
(426,13),
(426,9),
(426,6),
(427,8),
(427,23),
(427,19),
(428,12),
(428,9),
(428,1),
(429,3),
(429,18),
(429,21),
(430,5),
(430,15),
(430,3),
(431,20),
(431,24),
(431,23),
(432,6),
(432,1),
(432,7),
(433,24),
(433,5),
(433,9),
(434,5),
(434,7),
(434,2),
(435,11),
(435,24),
(435,17),
(436,14),
(436,19),
(436,6),
(437,21),
(437,20),
(437,11),
(438,10),
(438,17),
(438,15),
(439,23),
(439,6),
(439,4),
(440,3),
(440,19),
(440,16),
(441,17),
(441,10),
(441,3),
(442,15),
(442,6),
(442,7),
(443,2),
(443,19),
(443,3),
(444,12),
(444,9),
(444,19),
(445,22),
(445,7),
(445,19),
(446,3),
(446,12),
(446,21),
(447,12),
(447,1),
(447,17),
(448,9),
(448,1),
(448,20),
(449,11),
(449,19),
(449,21),
(450,18),
(450,12),
(450,3),
(451,24),
(451,21),
(451,23),
(452,3),
(452,19),
(452,18),
(453,4),
(453,17),
(453,12),
(454,8),
(454,14),
(454,24),
(455,10),
(455,3),
(455,23),
(456,20),
(456,11),
(456,2),
(457,14),
(457,5),
(457,13),
(458,6),
(458,22),
(458,13);

