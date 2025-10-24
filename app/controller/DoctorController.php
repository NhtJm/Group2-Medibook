<?php
require_once __DIR__ . "/../model/DoctorModel.php";
require_once __DIR__ . "/../model/OfficeModel.php";

class DoctorController
{
    private $doctorModel;
    private $officeModel;

    public function __construct()
    {
        $this->doctorModel = new DoctorModel();
        $this->officeModel = new OfficeModel();
    }

    public function index($action)
    {
        switch ($action) {
            case 'specialties':
                $this->listSpecialties();
                break;
            case 'search':
                $this->searchDoctors();
                break;
            case 'featured':
                $this->getFeaturedDoctors();
                break;
            default:
                // Handle default or not found action
                break;
        }
    }

    private function listSpecialties()
    {
        $specialties = $this->doctorModel->getAllSpecialties();

        $data = [
            'specialties' => $specialties,
        ];

        extract($data);
        //*******MISSING**************
        require_once __DIR__ . "/../views/search/specialty_list.php";
    }

    private function searchDoctors()
    {
        $query = $_GET['q'] ?? '';
        $location = $_GET['loc'] ?? '';

        $results = $this->doctorModel->searchDoctors($query, $location);

        $data = [
            'query' => $query,
            'location' => $location,
            'results' => $results,
        ];

        extract($data);
        // Already have
        require_once __DIR__ . "/../views/clinics.php";
    }

    private function getFeaturedDoctors()
    {
        $featured = $this->doctorModel->getFeaturedDoctors(3);

        $data = [
            'featured' => $featured,
        ];

        extract($data);
        //*******MISSING**************
        require_once __DIR__ . "/../views/doctor/featured.php";
    }

    public function editProfile($doctor_id)
    {
        if (empty($doctor_id)) {
            header("Location: " . BASE_URL . "index.php?page=dashboard");
            exit;
        }

        $doctorProfile = $this->doctorModel->getDoctorById($doctor_id);

        if (!$doctorProfile) {
            header("Location: " . BASE_URL . "index.php?page=dashboard");
            exit;
        }

        $allSpecialties = $this->doctorModel->getAllSpecialties();

        $data = [
            'doctor' => $doctorProfile,
            'specialties' => $allSpecialties,
        ];

        extract($data);
        require_once __DIR__ . "/../views/doctor/edit_profile.php";
    }

    public function updateProfile($doctor_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($doctor_id)) {
            header("Location: " . BASE_URL . "index.php?page=doctor&action=edit&id=" . $doctor_id);
            exit;
        }
        
        $doctor = $this->doctorModel->getDoctorById($doctor_id);

        if (!$doctor) {
            header("Location: " . BASE_URL . "index.php?page=dashboard");
            exit;
        }

        $userId = $doctor['user_id'];

        $userData = [
            'name' => $_POST['full_name'] ?? null,
            'username' => $_POST['email'] ?? null,
        ];
        
        $doctorData = [
            'specialty_id' => $_POST['specialty_id'] ?? null,
            'degree' => $_POST['degree'] ?? null,
            'graduate' => $_POST['graduate_year'] ?? null,
            'photo' => $_POST['current_photo'] ?? null,
        ];

        $userUpdateResult = $this->userModel->updateUser($userId, $userData);
        $doctorUpdateResult = $this->doctorModel->updateDoctor($doctor_id, $doctorData);

        if ($userUpdateResult['status'] === 'success' || $doctorUpdateResult['status'] === 'success') {
            session_start();
            $_SESSION['message'] = 'Profile updated successfully.';
        } else {
            session_start();
            $_SESSION['error'] = 'Failed to update profile. Please check the logs.';
        }

        header("Location: " . BASE_URL . "index.php?page=doctor&action=edit&id=" . $doctor_id);
        exit;
    }

    public function getOfficeAppointmentSummary($doctor_id)
    {
        $appointments = $this->appointmentModel->getAppointmentsByDoctor($doctor_id);

        $summary = [];
        foreach ($appointments as $appt) {
            $summary[] = [
                'date' => (new DateTime($appt['start']))->format('Y-m-d'),
                'time' => (new DateTime($appt['start']))->format('H:i'),
                'patient_name' => $appt['patient_name'],
                'status' => $appt['status']
            ];
        }
        return $summary;
    }   
}