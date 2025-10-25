<?php
require_once __DIR__ . "/../model/UserModel.php";
require_once __DIR__ . "/../model/OfficeModel.php";
require_once __DIR__ . "/../model/DoctorModel.php";
require_once __DIR__ . "/../model/ProblemReportModel.php";
require_once __DIR__ . "/../model/HandledModel.php";
require_once __DIR__ . "/../model/ContactModel.php";

class AdminController
{
    private $userModel;
    private $officeModel;
    private $doctorModel;
    private $reportModel;
    private $handledModel;
    private $contactModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->officeModel = new OfficeModel();
        $this->doctorModel = new DoctorModel();
        $this->reportModel = new ProblemReportModel();
        $this->handledModel = new HandledModel();
        $this->contactModel = new ContactModel();

        // Secure this entire controller
        $this->checkAuth();
    }

    /**
     * Ensures only 'admin' or 'webstaff' can access this controller.
     */
    private function checkAuth()
    {
        session_start();
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'webstaff'])) {
            header("Location: " . BASE_URL . "index.php?page=login&error=unauthorized");
            exit;
        }
    }

    /**
     * Main router for the admin panel.
     */
    public function index($action)
    {
        switch ($action) {
            // Report & Contact Management (Web Staff)
            case 'reports':
                $this->listReports();
                break;
            case 'viewReport':
                $this->viewReport($_GET['id'] ?? null);
                break;
            case 'handleReport':
                $this->handleReport();
                break;
            case 'contacts':
                $this->listContacts();
                break;
            case 'updateContactStatus':
                $this->updateContactStatus();
                break;

            // Content & User Management (Admin)
            case 'users':
                $this->listUsers();
                break;
            case 'editUser':
                $this->editUser($_GET['id'] ?? null);
                break;
            case 'updateUser':
                $this->updateUser();
                break;
            case 'offices':
                $this->listOffices();
                break;
            case 'updateOfficeStatus':
                $this->updateOfficeStatus();
                break;
            case 'specialties':
                $this->listSpecialties();
                break;
            case 'addSpecialty':
                $this->addSpecialty();
                break;
            case 'updateSpecialty':
                $this->updateSpecialty();
                break;
            case 'deleteSpecialty':
                $this->deleteSpecialty($_GET['id'] ?? null);
                break;
            
            default:
                $this->dashboard();
                break;
        }
    }

    private function dashboard()
    {
        // Fetch summary data
        $data = [
            'reports' => $this->reportModel->getAllReports(),
            'contacts' => $this->contactModel->getAllContacts(),
            'users' => $this->userModel->getAllUsers(),
            'offices' => $this->officeModel->getAllOffices()
        ];
        extract($data);
        // Already have
        require_once __DIR__ . "/../views/admin/dashboard.php";
    }

    /* ===================== REPORTS ===================== */

    private function listReports()
    {
        $reports = $this->reportModel->getAllReports();
        $data = ['reports' => $reports];
        extract($data);
        // MISSING*******************
        require_once __DIR__ . "/../views/admin/reports.php";
    }

    private function viewReport($report_id)
    {
        if (!$report_id) {
            header("Location: " . BASE_URL . "index.php?page=admin&action=reports");
            exit;
        }
        $report = $this->reportModel->getReportById($report_id);
        $handlers = $this->handledModel->getStaffHandledReport($report_id);
        
        $data = [
            'report' => $report,
            'handlers' => $handlers
        ];
        extract($data);
        //MISSING***********************************
        require_once __DIR__ . "/../views/admin/report_view.php";
    }

    private function handleReport()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             header("Location: " . BASE_URL . "index.php?page=admin&action=reports");
             exit;
        }

        $report_id = $_POST['report_id'];
        $staff_id = $_SESSION['user']['staff_id']; // Assumes staff_id is in session
        $description = $_POST['description_action'];
        $status = $_POST['status'];

        // Check if already handled by this staff
        $existing = $this->handledModel->getHandled($staff_id, $report_id);
        if ($existing) {
            $result = $this->handledModel->updateHandled($staff_id, $report_id, [
                'description_action' => $description,
                'status' => $status
            ]);
        } else {
            $result = $this->handledModel->addHandled($staff_id, $report_id, $description, $status);
        }

        if ($result['status'] === 'success') {
             $_SESSION['message'] = "Report updated.";
        } else {
             $_SESSION['error'] = "Failed to update report: " . $result['message'];
        }

        header("Location: " . BASE_URL . "index.php?page=admin&action=viewReport&id=" . $report_id);
        exit;
    }

    /* ===================== CONTACTS ===================== */

    private function listContacts()
    {
        $contacts = $this->contactModel->getAllContacts();
        $data = ['contacts' => $contacts];
        extract($data);
        // MISSING*********************************
        require_once __DIR__ . "/../views/admin/contacts.php";
    }

    private function updateContactStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             header("Location: " . BASE_URL . "index.php?page=admin&action=contacts");
             exit;
        }

        $contact_id = $_POST['contact_id'];
        $status = $_POST['status'];

        $result = $this->contactModel->updateStatus($contact_id, $status);

        if ($result['status'] === 'success') {
             $_SESSION['message'] = "Contact status updated.";
        } else {
             $_SESSION['error'] = "Failed to update status: " . $result['message'];
        }
        
        header("Location: " . BASE_URL . "index.php?page=admin&action=contacts");
        exit;
    }

    /* ===================== USERS ===================== */

    private function listUsers()
    {
        $users = $this->userModel->getAllUsers();
        $data = ['users' => $users];
        extract($data);
        // MISSING**********************************
        require_once __DIR__ . "/../views/admin/users.php";
    }
    
    private function editUser($user_id)
    {
        $user = $this->userModel->getUserById($user_id);
        if (!$user) {
             header("Location: " . BASE_URL . "index.php?page=admin&action=users");
             exit;
        }
        
        $data = ['user' => $user];
        extract($data);
        // MISSING**********************************
        require_once __DIR__ . "/../views/admin/user_edit.php";
    }

    private function updateUser()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             header("Location: " . BASE_URL . "index.php?page=admin&action=users");
             exit;
        }
        
        $user_id = $_POST['user_id'];
        $data = [
            'full_name' => $_POST['full_name'],
            'email' => $_POST['email'],
            'role' => $_POST['role']
        ];
        
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }

        $result = $this->userModel->updateUser($user_id, $data);
        
        if ($result['status'] === 'success') {
             $_SESSION['message'] = "User updated.";
        } else {
             $_SESSION['error'] = "Failed to update user: " . $result['message'];
        }

        header("Location: " . BASE_URL . "index.php?page=admin&action=editUser&id=" . $user_id);
        exit;
    }

    /* ===================== OFFICES ===================== */
    
    private function listOffices()
    {
        $offices = $this->officeModel->getAllOffices();
        $data = ['offices' => $offices];
        extract($data);
        // MISSING**********************************
        require_once __DIR__ . "/../views/admin/offices.php";
    }
    
    private function updateOfficeStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             header("Location: " . BASE_URL . "index.php?page=admin&action=offices");
             exit;
        }
        
        $office_id = $_POST['office_id'];
        $status = $_POST['status'];
        
        $result = $this->officeModel->updateOffice($office_id, ['status' => $status]);
        
        if ($result['status'] === 'success') {
             $_SESSION['message'] = "Office status updated.";
        } else {
             $_SESSION['error'] = "Failed to update status: " . $result['message'];
        }
        
        header("Location: " . BASE_URL . "index.php?page=admin&action=offices");
        exit;
    }
    
    /* ===================== SPECIALTIES ===================== */
    
    private function listSpecialties()
    {
        $specialties = $this->doctorModel->getAllSpecialties();
        $data = ['specialties' => $specialties];
        extract($data);
        // MISSING**********************************
        require_once __DIR__ . "/../views/admin/specialties.php";
    }
    
    private function addSpecialty()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             header("Location: " . BASE_URL . "index.php?page=admin&action=specialties");
             exit;
        }
        
        $name = $_POST['name'];
        $description = $_POST['description'];
        
        $result = $this->doctorModel->addSpecialty($name, $description);
        
        if ($result['status'] === 'success') {
             $_SESSION['message'] = "Specialty added.";
        } else {
             $_SESSION['error'] = "Failed to add specialty: " . $result['message'];
        }
        
        header("Location: " . BASE_URL . "index.php?page=admin&action=specialties");
        exit;
    }
    
    private function updateSpecialty()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             header("Location: " . BASE_URL . "index.php?page=admin&action=specialties");
             exit;
        }
        
        $id = $_POST['specialty_id'];
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description']
        ];
        
        $result = $this->doctorModel->updateSpecialty($id, $data);
        
        if ($result['status'] === 'success') {
             $_SESSION['message'] = "Specialty updated.";
        } else {
             $_SESSION['error'] = "Failed to update specialty: " . $result['message'];
        }

        header("Location: " . BASE_URL . "index.php?page=admin&action=specialties");
        exit;
    }

    private function deleteSpecialty($specialty_id)
    {
        if (!$specialty_id) {
            header("Location: " . BASE_URL . "index.php?page=admin&action=specialties");
            exit;
        }

        $result = $this->doctorModel->deleteSpecialty($specialty_id);
        
        if ($result['status'] === 'success') {
             $_SESSION['message'] = "Specialty deleted.";
        } else {
             $_SESSION['error'] = "Failed to delete specialty: " . $result['message'];
        }

        header("Location: " . BASE_URL . "index.php?page=admin&action=specialties");
        exit;
    }
}
?>