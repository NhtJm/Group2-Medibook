<?php
require_once __DIR__ . '/../app/model/HandledModel.php';

$handledModel = new HandledModel();
$conn = Database::get_instance();

echo "<h2>=== TESTING HandledModel ===</h2>";

/* =============================
   STEP 0: Setup test data
   ============================= */
echo "<h3>== Step 0: Setup test data ==</h3>";

// --- Create web staff user ---
$conn->query("INSERT INTO Users (email, username, password_hash, full_name, role)
              VALUES ('staff_test@demo.com', 'staff_test', '123', 'Staff Demo', 'webstaff')");
$user_staff_id = $conn->insert_id;
$conn->query("INSERT INTO Web_staff (user_id, position, status)
              VALUES ($user_staff_id, 'support', 'online')");
$staff_id = $conn->insert_id;

// --- Create patient user ---
$conn->query("INSERT INTO Users (email, username, password_hash, full_name, role)
              VALUES ('patient_test@demo.com', 'patient_test', '123', 'Patient Demo', 'patient')");
$user_patient_id = $conn->insert_id;
$conn->query("INSERT INTO Patient (user_id, date_of_birth, status, payment)
              VALUES ($user_patient_id, '1990-01-01', 'active', 'cash')");
$patient_id = $conn->insert_id;

// --- Create office + doctor for valid foreign key ---
$conn->query("INSERT INTO Users (email, username, password_hash, full_name, role)
              VALUES ('office_test@demo.com', 'office_test', '123', 'Office Demo', 'office')");
$user_office_id = $conn->insert_id;
$conn->query("INSERT INTO Office (user_id, name, address, website, logo, description, status)
              VALUES ($user_office_id, 'Demo Office', 'HCM', 'office.vn', 'logo.png', 'Testing office', 'approved')");
$office_id = $conn->insert_id;

$conn->query("INSERT INTO Medical_specialty (name, description)
              VALUES ('Cardiology', 'Heart issues')");
$specialty_id = $conn->insert_id;

$conn->query("INSERT INTO Doctor (office_id, photo, degree, graduate, specialty_id)
              VALUES ($office_id, 'photo.png', 'MD', 'University A', $specialty_id)");
$doctor_id = $conn->insert_id;

// --- Create a problem report (patient type) ---
$conn->query("INSERT INTO Problem_report (description, patient_id)
              VALUES ('Test report: system bug', $patient_id)");
$report_id = $conn->insert_id;

echo "✅ Setup done: staff_id=$staff_id, report_id=$report_id<br>";

/* =============================
   TEST 1: Add handled record
   ============================= */
echo "<h3>== Test 1: Add Handled ==</h3>";
$result1 = $handledModel->addHandled($staff_id, $report_id, "Investigating the reported issue");
print_r($result1);

/* =============================
   TEST 2: Get all handled
   ============================= */
echo "<h3>== Test 2: Get All Handled ==</h3>";
print_r($handledModel->getAllHandled());

/* =============================
   TEST 3: Get specific handled
   ============================= */
echo "<h3>== Test 3: Get One Handled ==</h3>";
print_r($handledModel->getHandled($staff_id, $report_id));

/* =============================
   TEST 4: Update description
   ============================= */
echo "<h3>== Test 4: Update Description ==</h3>";
$update1 = $handledModel->updateHandled($staff_id, $report_id, [
    "description_action" => "Reviewed and escalated to admin"
]);
print_r($update1);

/* =============================
   TEST 5: Update status + date
   ============================= */
echo "<h3>== Test 5: Update Status + Date ==</h3>";
$update2 = $handledModel->updateHandled($staff_id, $report_id, [
    "status" => "resolved",
    "action_date" => date("Y-m-d H:i:s", strtotime("+1 hour"))
]);
print_r($update2);

/* =============================
   TEST 6: Get handled reports by staff
   ============================= */
echo "<h3>== Test 6: Get Handled Reports By Staff ==</h3>";
print_r($handledModel->getHandledReportsByStaff($staff_id));

/* =============================
   TEST 7: Get staff handling report
   ============================= */
echo "<h3>== Test 7: Get Staff Handling Report ==</h3>";
print_r($handledModel->getStaffHandledReport($report_id));

/* =============================
   TEST 8: Delete handled
   ============================= */
echo "<h3>== Test 8: Delete Handled ==</h3>";
print_r($handledModel->deleteHandled($staff_id, $report_id));

/* =============================
   TEST 9: Check all handled after delete
   ============================= */
echo "<h3>== Test 9: Get All After Delete ==</h3>";
print_r($handledModel->getAllHandled());

/* =============================
   CLEANUP TEST DATA
   ============================= */
echo "<h3>== Cleanup Test Data ==</h3>";
$conn->query("DELETE FROM Handled");
$conn->query("DELETE FROM Problem_report");
$conn->query("DELETE FROM Doctor WHERE doctor_id = $doctor_id");
$conn->query("DELETE FROM Medical_specialty WHERE specialty_id = $specialty_id");
$conn->query("DELETE FROM Office WHERE office_id = $office_id");
$conn->query("DELETE FROM Patient WHERE patient_id = $patient_id");
$conn->query("DELETE FROM Web_staff WHERE staff_id = $staff_id");
$conn->query("DELETE FROM Users WHERE user_id IN ($user_staff_id, $user_patient_id, $user_office_id)");
echo "✅ Cleanup completed.<br>";

echo "<h2>=== ALL TESTS COMPLETED SUCCESSFULLY ===</h2>";
?>
