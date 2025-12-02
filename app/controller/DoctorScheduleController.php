<?php
// app/controller/DoctorScheduleController.php
require_once __DIR__ . '/../db.php';

class DoctorScheduleController
{
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
        
        $doctorId = (int) ($_GET['doctor_id'] ?? 0);

        $office = self::getOfficeByUserId($conn, $userId);
        if (!$office) {
            header('Location: ' . BASE_URL . 'index.php?page=clinic_setup');
            exit;
        }

        $doctor = self::getDoctorById($conn, $doctorId, $office['office_id']);
        if (!$doctor) {
            header('Location: ' . BASE_URL . 'index.php?page=office_dashboard');
            exit;
        }

        $weekOffset = (int) ($_GET['week'] ?? 0);
        $startOfWeek = new DateTime('monday this week');
        $startOfWeek->modify("+{$weekOffset} weeks");

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = clone $startOfWeek;
            $day->modify("+{$i} days");
            $days[] = [
                'date' => $day->format('Y-m-d'),
                'weekday' => $day->format('l'),
                'display' => $day->format('j M'),
                'isToday' => $day->format('Y-m-d') === (new DateTime())->format('Y-m-d')
            ];
        }

        $endOfWeek = clone $startOfWeek;
        $endOfWeek->modify('+6 days 23:59:59');
        $slots = self::getSlotsByDoctorAndWeek($conn, $doctorId, $startOfWeek, $endOfWeek);

        return [
            'office' => $office,
            'doctor' => $doctor,
            'days' => $days,
            'slots' => $slots,
            'weekOffset' => $weekOffset,
            'weekStart' => $startOfWeek->format('Y-m-d'),
            'weekEnd' => $endOfWeek->format('Y-m-d')
        ];
    }

    public static function api(): void
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        header('Content-Type: application/json');

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

        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            case 'add_slot':
                self::addSlot($conn, $office, $input);
                break;
            case 'remove_slot':
                self::removeSlot($conn, $office, $input);
                break;
            case 'toggle_slot':
                self::toggleSlot($conn, $office, $input);
                break;
            case 'bulk_add':
                self::bulkAddSlots($conn, $office, $input);
                break;
            case 'get_slots':
                self::getSlots($conn, $office, $input);
                break;
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    }

    private static function addSlot(mysqli $conn, array $office, array $input): void
    {
        $doctorId = (int) ($input['doctor_id'] ?? 0);
        $startTime = $input['start_time'] ?? '';
        $endTime = $input['end_time'] ?? '';

        $doctor = self::getDoctorById($conn, $doctorId, $office['office_id']);
        if (!$doctor) {
            echo json_encode(['success' => false, 'error' => 'Doctor not found']);
            return;
        }

        if (empty($startTime) || empty($endTime)) {
            echo json_encode(['success' => false, 'error' => 'Start and end time required']);
            return;
        }

        $checkSql = "SELECT slot_id FROM Appointment_slot WHERE doctor_id = ? AND start_time = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("is", $doctorId, $startTime);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'Slot already exists']);
            $checkStmt->close();
            return;
        }
        $checkStmt->close();

        $sql = "INSERT INTO Appointment_slot (doctor_id, start_time, end_time, status) VALUES (?, ?, ?, 'available')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $doctorId, $startTime, $endTime);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'slot_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add slot']);
        }
        $stmt->close();
    }

    private static function removeSlot(mysqli $conn, array $office, array $input): void
    {
        $slotId = (int) ($input['slot_id'] ?? 0);

        if ($slotId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid slot ID']);
            return;
        }

        $checkSql = "SELECT s.slot_id 
                     FROM Appointment_slot s
                     JOIN Doctor d ON d.doctor_id = s.doctor_id
                     WHERE s.slot_id = ? AND d.office_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $slotId, $office['office_id']);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Slot not found']);
            $checkStmt->close();
            return;
        }
        $checkStmt->close();

        $apptCheck = "SELECT appointment_id FROM Appointment WHERE slot_id = ? AND status = 'booked'";
        $apptStmt = $conn->prepare($apptCheck);
        $apptStmt->bind_param("i", $slotId);
        $apptStmt->execute();
        if ($apptStmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'Cannot delete slot with booked appointment']);
            $apptStmt->close();
            return;
        }
        $apptStmt->close();

        $sql = "DELETE FROM Appointment_slot WHERE slot_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $slotId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete slot']);
        }
        $stmt->close();
    }

    private static function toggleSlot(mysqli $conn, array $office, array $input): void
    {
        $doctorId = (int) ($input['doctor_id'] ?? 0);
        $startTime = $input['start_time'] ?? '';
        
        $duration = 30;

        $doctor = self::getDoctorById($conn, $doctorId, $office['office_id']);
        if (!$doctor) {
            echo json_encode(['success' => false, 'error' => 'Doctor not found']);
            return;
        }

        if (empty($startTime)) {
            echo json_encode(['success' => false, 'error' => 'Start time required']);
            return;
        }

        $checkSql = "SELECT slot_id FROM Appointment_slot WHERE doctor_id = ? AND start_time = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("is", $doctorId, $startTime);
        $checkStmt->execute();
        $existing = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if ($existing) {
            self::removeSlot($conn, $office, ['slot_id' => $existing['slot_id']]);
        } else {
            $endTime = (new DateTime($startTime))->modify("+{$duration} minutes")->format('Y-m-d H:i:s');
            self::addSlot($conn, $office, [
                'doctor_id' => $doctorId,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
        }
    }

    private static function bulkAddSlots(mysqli $conn, array $office, array $input): void
    {
        $doctorId = (int) ($input['doctor_id'] ?? 0);
        $date = $input['date'] ?? '';
        $slots = $input['slots'] ?? [];
        
        $duration = 30;

        $doctor = self::getDoctorById($conn, $doctorId, $office['office_id']);
        if (!$doctor) {
            echo json_encode(['success' => false, 'error' => 'Doctor not found']);
            return;
        }

        if (empty($date) || empty($slots)) {
            echo json_encode(['success' => false, 'error' => 'Date and slots required']);
            return;
        }

        $added = 0;
        $errors = [];

        foreach ($slots as $time) {
            $startTime = $date . ' ' . $time . ':00';
            $endTime = (new DateTime($startTime))->modify("+{$duration} minutes")->format('Y-m-d H:i:s');

            $checkSql = "SELECT slot_id FROM Appointment_slot WHERE doctor_id = ? AND start_time = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("is", $doctorId, $startTime);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                $checkStmt->close();
                continue;
            }
            $checkStmt->close();

            $sql = "INSERT INTO Appointment_slot (doctor_id, start_time, end_time, status) VALUES (?, ?, ?, 'available')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $doctorId, $startTime, $endTime);
            if ($stmt->execute()) {
                $added++;
            } else {
                $errors[] = $time;
            }
            $stmt->close();
        }

        echo json_encode([
            'success' => true,
            'added' => $added,
            'errors' => $errors
        ]);
    }

    private static function getSlots(mysqli $conn, array $office, array $input): void
    {
        $doctorId = (int) ($input['doctor_id'] ?? 0);
        $startDate = $input['start_date'] ?? '';
        $endDate = $input['end_date'] ?? '';

        $doctor = self::getDoctorById($conn, $doctorId, $office['office_id']);
        if (!$doctor) {
            echo json_encode(['success' => false, 'error' => 'Doctor not found']);
            return;
        }

        $sql = "SELECT s.slot_id, s.start_time, s.end_time, s.status,
                       CASE WHEN a.appointment_id IS NOT NULL THEN 1 ELSE 0 END AS has_appointment
                FROM Appointment_slot s
                LEFT JOIN Appointment a ON a.slot_id = s.slot_id AND a.status = 'booked'
                WHERE s.doctor_id = ?
                  AND DATE(s.start_time) >= ?
                  AND DATE(s.start_time) <= ?
                ORDER BY s.start_time";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $doctorId, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $slotsByDay = [];
        foreach ($result as $slot) {
            $day = (new DateTime($slot['start_time']))->format('Y-m-d');
            if (!isset($slotsByDay[$day])) {
                $slotsByDay[$day] = [];
            }
            $slotsByDay[$day][] = [
                'slot_id' => $slot['slot_id'],
                'time' => (new DateTime($slot['start_time']))->format('H:i'),
                'status' => $slot['status'],
                'booked' => (bool) $slot['has_appointment']
            ];
        }

        echo json_encode(['success' => true, 'slots' => $slotsByDay]);
    }

    private static function getOfficeByUserId(mysqli $conn, int $userId): ?array
    {
        $sql = "SELECT * FROM Office WHERE user_id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    private static function getDoctorById(mysqli $conn, int $doctorId, int $officeId): ?array
    {
        $sql = "SELECT d.*, ms.name AS specialty_name 
                FROM Doctor d
                LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id
                WHERE d.doctor_id = ? AND d.office_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $doctorId, $officeId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    private static function getSlotsByDoctorAndWeek(mysqli $conn, int $doctorId, DateTime $start, DateTime $end): array
    {
        $sql = "SELECT s.slot_id, s.start_time, s.end_time, s.status,
                       CASE WHEN a.appointment_id IS NOT NULL THEN 1 ELSE 0 END AS has_appointment
                FROM Appointment_slot s
                LEFT JOIN Appointment a ON a.slot_id = s.slot_id AND a.status = 'booked'
                WHERE s.doctor_id = ?
                  AND s.start_time >= ?
                  AND s.start_time <= ?
                ORDER BY s.start_time";
        
        $startStr = $start->format('Y-m-d 00:00:00');
        $endStr = $end->format('Y-m-d 23:59:59');
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $doctorId, $startStr, $endStr);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $slotsByDay = [];
        foreach ($result as $slot) {
            $day = (new DateTime($slot['start_time']))->format('Y-m-d');
            if (!isset($slotsByDay[$day])) {
                $slotsByDay[$day] = [];
            }
            $slotsByDay[$day][] = [
                'slot_id' => $slot['slot_id'],
                'time' => (new DateTime($slot['start_time']))->format('g:ia'),
                'time24' => (new DateTime($slot['start_time']))->format('H:i'),
                'status' => $slot['status'],
                'booked' => (bool) $slot['has_appointment']
            ];
        }

        return $slotsByDay;
    }
}
