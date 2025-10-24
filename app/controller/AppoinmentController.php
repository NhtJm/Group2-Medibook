<?php
require_once __DIR__ . "/../model/AppointmentModel.php";
require_once __DIR__ . "/../model/DoctorModel.php";
require_once __DIR__ . "/../model/OfficeModel.php";
require_once __DIR__ . "/../model/UserModel.php";

class AppointmentController
{
    private $appointmentModel;
    private $doctorModel;
    private $officeModel;
    private $userModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->doctorModel = new DoctorModel();
        $this->officeModel = new OfficeModel();
        $this->userModel = new UserModel();
    }

    private function getUserId()
    {
        session_start();
        return $_SESSION['user']['id'] ?? null;
    }

    public function index($action)
    {
        $userId = $this->getUserId();
        if (!$userId && $action !== 'book') {
            header("Location: " . BASE_URL . "index.php?page=login");
            exit;
        }

        switch ($action) {
            case 'view':
                $this->viewDetails($_GET['id'] ?? null);
                break;
            case 'cancel':
                $this->cancel($_POST['appointment_id'] ?? null);
                break;
            case 'confirm':
                $this->showSummary($_GET['slot_id'] ?? null);
                break;
            case 'book':
                $this->book();
                break;
            case 'list':
                $this->listAppointments();
                break;
            default:
                $this->listAppointments();
                break;
        }
    }

    private function listAppointments()
    {
        $userId = $this->getUserId();
        $appointments = $this->appointmentModel->getAppointmentsByPatient($userId);

        $data = [
            'appointments' => $appointments
        ];
        
        extract($data);
        // Already have
        require_once __DIR__ . "/../views/appointments.php";
    }

    private function viewDetails($appointment_id)
    {
        $appointment = $this->appointmentModel->getAppointmentById($appointment_id);

        if (!$appointment) {
            header("Location: " . BASE_URL . "index.php?page=appointments");
            exit;
        }

        $data = [
            'appointment' => $appointment
        ];

        extract($data);
        //*******MISSING**************
        require_once __DIR__ . "/../views/booking/appointment_detail.php";
    }

    private function cancel($appointment_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$appointment_id) {
            header("Location: " . BASE_URL . "index.php?page=appointments");
            exit;
        }

        $result = $this->appointmentModel->deleteAppointment($appointment_id);

        if ($result['status'] === 'success') {
            $_SESSION['message'] = 'Appointment successfully cancelled.';
        } else {
            $_SESSION['error'] = 'Failed to cancel appointment: ' . $result['message'];
        }

        header("Location: " . BASE_URL . "index.php?page=appointments");
        exit;
    }

    private function book()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $slotId = $_GET['slot'] ?? null;
            if (!$slotId) {
                header("Location: " . BASE_URL . "index.php?page=dashboard");
                exit;
            }

            // Lấy thông tin chi tiết của slot, doctor và office để hiển thị xác nhận
            $slot = $this->officeModel->getSlotById($slotId);
            if (!$slot) {
                $_SESSION['error'] = 'Invalid slot ID.';
                header("Location: " . BASE_URL . "index.php?page=dashboard");
                exit;
            }

            $doctor = $this->doctorModel->getDoctorById($slot['doctor_id']);
            $office = $this->officeModel->getOfficeById($slot['office_id']);

            $data = [
                'slot' => $slot,
                'doctor' => $doctor,
                'office' => $office
            ];

            extract($data);
            require_once __DIR__ . "/../views/confirm.php";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patientId = $this->getUserId();
            $slotId = $_POST['slot_id'] ?? null;

            if (!$patientId || !$slotId) {
                $_SESSION['error'] = 'Missing patient or slot information.';
                header("Location: " . BASE_URL . "index.php?page=dashboard");
                exit;
            }

            $result = $this->appointmentModel->addAppointment($patientId, $slotId, 'booked');

            if ($result['status'] === 'success') {
                $_SESSION['message'] = 'Appointment successfully booked!';
                header("Location: " . BASE_URL . "index.php?page=appointments");
            } else {
                $_SESSION['error'] = 'Booking failed: ' . $result['message'];
                header("Location: " . BASE_URL . "index.php?page=doctor&id=" . $_POST['doctor_id']);
            }
            exit;
        }
    }

    private function showSummary($slot_id)
    {
        $patientId = $this->getUserId();

        if (!$patientId || !$slot_id) {
            header("Location: " . BASE_URL . "index.php?page=dashboard");
            exit;
        }

        $slotDetails = $this->officeModel->getSlotById($slot_id);
        $patient = $this->userModel->getUserById($patientId);

        if (!$slotDetails || $slotDetails['status'] !== 'available') {
            $_SESSION['error'] = 'Selected slot is unavailable or invalid.';
            header("Location: " . BASE_URL . "index.php?page=doctor&id=" . $slotDetails['doctor_id']);
            exit;
        }
        
        $data = [
            'slot' => $slotDetails,
            'patient' => $patient,
        ];
        
        extract($data);
        require_once __DIR__ . "/../views/confirm.php";
    }
}