<?php
// app/controller/OfficeDashboardController.php
require_once __DIR__ . '/../db.php';

/**
 * Controller for the Office Dashboard
 * Handles both the dashboard view and API endpoints for doctor management
 */
class OfficeDashboardController
{

    /**
     * Get the current user ID securely
     * Tries multiple sources to find the user ID
     */
    private static function getUserId(): int
    {
        $user = current_user();
        if (!$user) return 0;
        
        $userId = (int) ($user['user_id'] ?? $user['id'] ?? 0);
        if ($userId <= 0) {
            $userId = (int) ($_SESSION['user_id'] ?? ($_SESSION['user']['user_id'] ?? 0));
        }
        return $userId;
    }


    /**
     * Display the office dashboard with real data
     * Main entry point for the dashboard view
     */
    public static function index(): array
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        $user = current_user();
        if (!$user) {
            header('Location: ' . BASE_URL . 'index.php?page=login');
            exit;
        }

        $userId = self::getUserId();
        if ($userId <= 0) {
            header('Location: ' . BASE_URL . 'index.php?page=login');
            exit;
        }

        // Get the user's office
        $office = self::getOfficeByUserId($conn, $userId);

        // If no office exists, redirect to setup
        if (!$office) {
            header('Location: ' . BASE_URL . 'index.php?page=clinic_setup');
            exit;
        }

        $officeId = (int) $office['office_id'];

        // Get all doctors for this clinic
        $doctors = self::getDoctorsByOffice($conn, $officeId);

        // Get upcoming appointments
        $appointments = self::getUpcomingAppointments($conn, $officeId);

        // Get statistics
        $stats = self::getStats($conn, $officeId);

        return [
            'office' => $office,
            'doctors' => $doctors,
            'appointments' => $appointments,
            'stats' => $stats,
            'success' => isset($_GET['success'])
        ];
    }

    /**
     * Get office data by user ID
     */
    private static function getOfficeByUserId(mysqli $conn, int $userId): ?array
    {
        $sql = "SELECT o.*, op.phone 
                FROM Office o 
                LEFT JOIN Office_phone op ON op.office_id = o.office_id
                WHERE o.user_id = ? 
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }


    /**
     * Get all doctors belonging to an office
     */
    private static function getDoctorsByOffice(mysqli $conn, int $officeId): array
    {
        $sql = "SELECT d.doctor_id, d.doctor_name, d.email, d.photo, d.degree, d.graduate,
                       ms.name AS specialty_name, ms.specialty_id
                FROM Doctor d
                LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id
                WHERE d.office_id = ?
                ORDER BY d.doctor_name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $officeId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Add extra information for each doctor
        foreach ($result as &$doctor) {
            $doctor['initials'] = self::getInitials($doctor['doctor_name']);
            $doctor['next_appointment'] = self::getDoctorNextAppointment($conn, $doctor['doctor_id']);
            $doctor['today_appointments'] = self::getDoctorTodayAppointmentsCount($conn, $doctor['doctor_id']);
        }

        return $result;
    }


    /**
     * Get upcoming appointments for an office
     */
    private static function getUpcomingAppointments(mysqli $conn, int $officeId, int $limit = 10): array
    {
        $sql = "SELECT 
                    a.appointment_id,
                    a.status AS appointment_status,
                    a.booked_at,
                    slot.start_time,
                    slot.end_time,
                    d.doctor_id,
                    d.doctor_name,
                    ms.name AS specialty_name,
                    u.full_name AS patient_name,
                    u.email AS patient_email,
                    p.date_of_birth AS patient_dob
                FROM Appointment a
                JOIN Appointment_slot slot ON slot.slot_id = a.slot_id
                JOIN Doctor d ON d.doctor_id = slot.doctor_id
                LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id
                JOIN Patient p ON p.patient_id = a.patient_id
                JOIN Users u ON u.user_id = p.user_id
                WHERE d.office_id = ?
                  AND slot.start_time >= NOW()
                  AND a.status = 'booked'
                ORDER BY slot.start_time ASC
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $officeId, $limit);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Format dates for display
        foreach ($result as &$appt) {
            $startDt = new DateTime($appt['start_time']);
            $appt['date_formatted'] = $startDt->format('d-m-Y');
            $appt['time_formatted'] = $startDt->format('g:i a');
            $appt['dob_formatted'] = $appt['patient_dob'] 
                ? (new DateTime($appt['patient_dob']))->format('d/m/Y') 
                : 'N/A';
        }

        return $result;
    }


    /**
     * Get dashboard statistics for an office
     */
    private static function getStats(mysqli $conn, int $officeId): array
    {
        // Count doctors
        $sqlDoctors = "SELECT COUNT(*) AS count FROM Doctor WHERE office_id = ?";
        $stmt = $conn->prepare($sqlDoctors);
        $stmt->bind_param("i", $officeId);
        $stmt->execute();
        $doctorCount = (int) $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        // Count today's appointments
        $sqlToday = "SELECT COUNT(*) AS count 
                     FROM Appointment a
                     JOIN Appointment_slot slot ON slot.slot_id = a.slot_id
                     JOIN Doctor d ON d.doctor_id = slot.doctor_id
                     WHERE d.office_id = ?
                       AND DATE(slot.start_time) = CURDATE()
                       AND a.status = 'booked'";
        $stmt = $conn->prepare($sqlToday);
        $stmt->bind_param("i", $officeId);
        $stmt->execute();
        $todayCount = (int) $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        // Count this week's appointments
        $sqlWeek = "SELECT COUNT(*) AS count 
                    FROM Appointment a
                    JOIN Appointment_slot slot ON slot.slot_id = a.slot_id
                    JOIN Doctor d ON d.doctor_id = slot.doctor_id
                    WHERE d.office_id = ?
                      AND YEARWEEK(slot.start_time, 1) = YEARWEEK(CURDATE(), 1)
                      AND a.status = 'booked'";
        $stmt = $conn->prepare($sqlWeek);
        $stmt->bind_param("i", $officeId);
        $stmt->execute();
        $weekCount = (int) $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        // Count total appointments
        $sqlTotal = "SELECT COUNT(*) AS count 
                     FROM Appointment a
                     JOIN Appointment_slot slot ON slot.slot_id = a.slot_id
                     JOIN Doctor d ON d.doctor_id = slot.doctor_id
                     WHERE d.office_id = ?";
        $stmt = $conn->prepare($sqlTotal);
        $stmt->bind_param("i", $officeId);
        $stmt->execute();
        $totalCount = (int) $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        return [
            'doctors' => $doctorCount,
            'today' => $todayCount,
            'week' => $weekCount,
            'total' => $totalCount
        ];
    }

    /**
     * Get the next appointment for a specific doctor
     */
    private static function getDoctorNextAppointment(mysqli $conn, int $doctorId): ?string
    {
        $sql = "SELECT slot.start_time
                FROM Appointment a
                JOIN Appointment_slot slot ON slot.slot_id = a.slot_id
                WHERE slot.doctor_id = ?
                  AND slot.start_time >= NOW()
                  AND a.status = 'booked'
                ORDER BY slot.start_time ASC
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctorId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result) {
            $dt = new DateTime($result['start_time']);
            return $dt->format('D, g:ia');
        }
        return null;
    }

    /**
     * Count today's appointments for a specific doctor
     */
    private static function getDoctorTodayAppointmentsCount(mysqli $conn, int $doctorId): int
    {
        $sql = "SELECT COUNT(*) AS count
                FROM Appointment a
                JOIN Appointment_slot slot ON slot.slot_id = a.slot_id
                WHERE slot.doctor_id = ?
                  AND DATE(slot.start_time) = CURDATE()
                  AND a.status = 'booked'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctorId);
        $stmt->execute();
        $result = (int) $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        return $result;
    }

    /**
     * Generate initials from a name
     * Example: "John Smith" -> "JS"
     */
    private static function getInitials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        if (count($parts) === 0) return 'DR';
        $first = mb_strtoupper(mb_substr($parts[0], 0, 1));
        $last = mb_strtoupper(mb_substr(end($parts), 0, 1));
        return $first . $last;
    }

    // =========================================================================
    // API METHODS
    // =========================================================================

    /**
     * API entry point
     * Routes to the appropriate action based on the 'action' parameter
     */
    public static function api(): void
    {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'add_doctor':
                self::addDoctor();
                break;
            case 'delete_doctor':
                self::deleteDoctor();
                break;
            default:
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    }

    /**
     * API: Add a new doctor to the office
     * Expects FormData with: doctor_name, email, specialty_id, degree, graduate, photo (optional)
     */
    private static function addDoctor(): void
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        header('Content-Type: application/json');

        // Check authentication
        $user = current_user();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            return;
        }

        $userId = self::getUserId();
        $office = self::getOfficeByUserId($conn, $userId);

        if (!$office) {
            echo json_encode(['success' => false, 'error' => 'No clinic found']);
            return;
        }

        // Get form data
        $name = trim($_POST['doctor_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $specialtyId = (int) ($_POST['specialty_id'] ?? 0);
        $degree = trim($_POST['degree'] ?? '');
        $graduate = trim($_POST['graduate'] ?? '');

        // Validate required fields
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Doctor name is required']);
            return;
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Valid email is required']);
            return;
        }

        // Check if email already exists
        $checkSql = "SELECT doctor_id FROM Doctor WHERE email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'A doctor with this email already exists']);
            $checkStmt->close();
            return;
        }
        $checkStmt->close();

        // Handle photo upload if provided
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = self::handleDoctorPhotoUpload($_FILES['photo']);
            if ($uploadResult['success']) {
                $photoPath = $uploadResult['path'];
            } else {
                echo json_encode(['success' => false, 'error' => $uploadResult['error']]);
                return;
            }
        }

        // Insert the doctor into database
        $sql = "INSERT INTO Doctor (office_id, doctor_name, email, specialty_id, degree, graduate, photo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $specIdParam = $specialtyId > 0 ? $specialtyId : null;
        $stmt->bind_param("ississs", 
            $office['office_id'], 
            $name, 
            $email, 
            $specIdParam,
            $degree,
            $graduate,
            $photoPath
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'doctor_id' => $conn->insert_id,
                'message' => 'Doctor added successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add doctor']);
        }
        $stmt->close();
    }

    /**
     * Handle doctor photo upload
     * Validates file type and size, then saves to uploads/doctors/
     */
    private static function handleDoctorPhotoUpload(array $file): array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Verify MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Please upload a JPEG, PNG, GIF, or WebP image.'];
        }

        // Verify file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File too large. Maximum size is 5MB.'];
        }

        // Create upload directory if needed
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Group2-Medibook/public/uploads/doctors/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'doctor_' . uniqid() . '_' . time() . '.' . $extension;
        $fullPath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['success' => true, 'path' => 'uploads/doctors/' . $filename];
        }

        return ['success' => false, 'error' => 'Failed to upload file.'];
    }

    /**
     * API: Delete a doctor from the office
     * Expects JSON body with: doctor_id
     */
    private static function deleteDoctor(): void
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        header('Content-Type: application/json');

        // Check authentication
        $user = current_user();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            return;
        }

        $userId = self::getUserId();
        $office = self::getOfficeByUserId($conn, $userId);

        if (!$office) {
            echo json_encode(['success' => false, 'error' => 'No clinic found']);
            return;
        }

        // Get doctor ID from request body
        $input = json_decode(file_get_contents('php://input'), true);
        $doctorId = (int) ($input['doctor_id'] ?? $_POST['doctor_id'] ?? 0);

        if ($doctorId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid doctor ID']);
            return;
        }

        // Verify the doctor belongs to this office
        $checkSql = "SELECT doctor_id FROM Doctor WHERE doctor_id = ? AND office_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $doctorId, $office['office_id']);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Doctor not found in your clinic']);
            $checkStmt->close();
            return;
        }
        $checkStmt->close();

        // Delete the doctor
        $sql = "DELETE FROM Doctor WHERE doctor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctorId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Doctor deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete doctor']);
        }
        $stmt->close();
    }
}
