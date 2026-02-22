<?php
require_once __DIR__ . "/../model/DoctorModel.php";
require_once __DIR__ . "/../model/OfficeModel.php";
require_once __DIR__ . "/../model/AppointmentModel.php"; // CHANGE HERE

class DoctorController
{
    private $doctorModel;
    private $officeModel;
    private $appointmentModel; // CHANGE HERE

    public function __construct()
    {
        $this->doctorModel = new DoctorModel();
        $this->officeModel = new OfficeModel();
        $this->appointmentModel = new AppointmentModel(); // CHANGE HERE
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
            case 'list': // CHANGE HERE
                $this->listAllDoctors(); // CHANGE HERE
                break;
            default:
                // Handle default or not found action
                $this->listAllDoctors(); // CHANGE HERE
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

        $results = $this->doctorModel->getAllDoctors(); // CHANGE HERE

        // Giả lập lọc theo tên chuyên khoa hoặc địa chỉ phòng khám // CHANGE HERE
        if (!empty($query)) {
            $results = array_filter($results, function ($d) use ($query) {
                return stripos($d['specialty_name'], $query) !== false
                    || stripos($d['degree'], $query) !== false;
            });
        }
        if (!empty($location)) {
            $results = array_filter($results, function ($d) use ($location) {
                return stripos($d['office_name'], $location) !== false;
            });
        }

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
        $featured = $this->doctorModel->getAllDoctors(); // CHANGE HERE
        $featured = array_slice($featured, 0, 3); // CHANGE HERE

        $data = [
            'featured' => $featured,
        ];

        extract($data);
        //*******MISSING**************
        require_once __DIR__ . "/../views/doctor/featured.php";
    }

    private function listAllDoctors() // CHANGE HERE
    {
        $doctors = $this->doctorModel->getAllDoctors(); // CHANGE HERE
        $data = ['doctors' => $doctors]; // CHANGE HERE
        extract($data); // CHANGE HERE
        //*******MISSING**************
        require_once __DIR__ . "/../views/doctor/list.php"; // CHANGE HERE
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
        $allOffices = $this->officeModel->getAllOffices(); // CHANGE HERE

        $data = [
            'doctor' => $doctorProfile,
            'specialties' => $allSpecialties,
            'offices' => $allOffices, // CHANGE HERE
        ];

        extract($data);
        //*******MISSING**************
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

        // Doctor không phải là user, nên chỉ cập nhật trong Doctor table // CHANGE HERE
        $doctorData = [
            'specialty_id' => $_POST['specialty_id'] ?? null,
            'degree' => $_POST['degree'] ?? null,
            'graduate' => $_POST['graduate_year'] ?? null,
            'photo' => $_POST['current_photo'] ?? null,
            'office_id' => $_POST['office_id'] ?? null, // CHANGE HERE
        ];

        $doctorUpdateResult = $this->doctorModel->updateDoctor($doctor_id, $doctorData); // CHANGE HERE

        session_start(); // CHANGE HERE
        if ($doctorUpdateResult['status'] === 'success') {
            $_SESSION['message'] = 'Profile updated successfully.'; // CHANGE HERE
        } else {
            $_SESSION['error'] = 'Failed to update profile. Please check the logs.'; // CHANGE HERE
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
                'date' => (new DateTime($appt['start_time']))->format('Y-m-d'), // CHANGE HERE
                'time' => (new DateTime($appt['start_time']))->format('H:i'), // CHANGE HERE
                'patient_name' => $appt['patient_name'],
                'status' => $appt['status']
            ];
        }

        $data = ['summary' => $summary]; // CHANGE HERE
        extract($data); // CHANGE HERE
        //*******MISSING**************
        require_once __DIR__ . "/../views/doctor/appointment_summary.php"; // CHANGE HERE
    }
}
?>
