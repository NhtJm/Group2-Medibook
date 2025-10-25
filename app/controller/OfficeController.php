<?php
require_once __DIR__ . "/../model/OfficeModel.php";
require_once __DIR__ . "/../model/DoctorModel.php";
require_once __DIR__ . "/../model/UserModel.php"; //CHANGE HERE
require_once __DIR__ . "/../model/ProblemReportModel.php"; //CHANGE HERE

class OfficeController
{
    private $officeModel;
    private $doctorModel;
    private $userModel; //CHANGE HERE
    private $reportModel; //CHANGE HERE

    public function __construct()
    {
        $this->officeModel = new OfficeModel();
        $this->doctorModel = new DoctorModel();
        $this->userModel = new UserModel(); //CHANGE HERE
        $this->reportModel = new ProblemReportModel(); //CHANGE HERE
    }

    public function index($action)
    {
        switch ($action) {
            case 'list':
                $this->listOffices();
                break;
            case 'search':
                $this->searchOffices();
                break;
            case 'view':
                $this->viewOffice($_GET['id'] ?? null);
                break;
            case 'add': //CHANGE HERE
                $this->addOffice(); //CHANGE HERE
                break;
            case 'delete': //CHANGE HERE
                $this->deleteOffice($_GET['id'] ?? null); //CHANGE HERE
                break;
            case 'report': //CHANGE HERE
                $this->reportOffice($_GET['id'] ?? null); //CHANGE HERE
                break;
            default:
                $this->listOffices();
                break;
        }
    }

    private function listOffices()
    {
        $offices = $this->officeModel->getAllOffices();

        $data = [
            'offices' => $offices,
        ];

        extract($data);
        //Already have
        require_once __DIR__ . "/../views/clinics.php";
    }

    private function searchOffices()
    {
        $query = $_GET['q'] ?? '';
        $location = $_GET['loc'] ?? '';

        $results = $this->officeModel->searchOffices($query, $location); //CHANGE HERE (ensure exists in OfficeModel)
        $data = [
            'query' => $query,
            'location' => $location,
            'results' => $results,
        ];

        extract($data);
        //Already have
        require_once __DIR__ . "/../views/clinics.php";
    }

    private function viewOffice($office_id)
    {
        $office = $this->officeModel->getOfficeById($office_id);

        if (!$office) {
            header("Location: " . BASE_URL . "index.php?page=clinics");
            exit;
        }

        $phones = $this->officeModel->getPhonesByOffice($office_id);
        $doctors = $this->doctorModel->getAllDoctors(); //CHANGE HERE
        $specialties = $this->officeModel->getSpecialtiesByOffice($office_id); //CHANGE HERE

        $data = [
            'office' => $office,
            'phones' => $phones,
            'doctors' => array_filter($doctors, fn($d) => $d['office_name'] === $office['name']), //CHANGE HERE
            'specialties' => $specialties, //CHANGE HERE
        ];

        extract($data);
        //Already have
        require_once __DIR__ . "/../views/clinic.php";
    }

    private function addOffice() //CHANGE HERE
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_POST['user_id'];
            $name = $_POST['name'];
            $address = $_POST['address'];
            $website = $_POST['website'];
            $logo = $_POST['logo'];
            $description = $_POST['description'];

            $result = $this->officeModel->addOffice($user_id, $name, $address, $website, $logo, $description);

            if ($result['status'] === 'success') {
                header("Location: index.php?page=offices&action=list");
                exit;
            } else {
                $error = $result['message'];
            }
        }

        $users = $this->userModel->getAllUsers();
        extract(['users' => $users]);
        //MISSING****************************
        require_once __DIR__ . "/../views/admin/office_add.php"; //CHANGE HERE (new UI)
    }

    private function deleteOffice($office_id) //CHANGE HERE
    {
        if (!$office_id) {
            header("Location: index.php?page=offices&action=list");
            exit;
        }

        $result = $this->officeModel->deleteOffice($office_id);

        if ($result['status'] === 'success') {
            header("Location: index.php?page=offices&action=list");
            exit;
        } else {
            echo "<script>alert('Xóa thất bại: {$result['message']}');</script>";
        }
    }

    private function reportOffice($office_id) //CHANGE HERE
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $description = $_POST['description'];
            $res = $this->reportModel->addReport($description, null, null, $office_id);

            if ($res['status'] === 'success') {
                header("Location: index.php?page=clinics&action=view&id=$office_id");
                exit;
            } else {
                $error = $res['message'];
            }
        }

        $office = $this->officeModel->getOfficeById($office_id);
        extract(['office' => $office]);
        //MISSING****************************
        require_once __DIR__ . "/../views/report_office.php"; //CHANGE HERE (new UI)
    }

    public function getSlotsForPublicView($doctor_id)
    {
        $rawSlots = $this->officeModel->getSlotsByDoctor($doctor_id);
        $slotsByDate = [];
        
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        foreach ($rawSlots as $slot) {
            $startDateTime = new DateTime($slot['start']);
            $dateKey = $startDateTime->format('Y-m-d');
            $timeLabel = $startDateTime->format('g:i a');
            $dayOfWeek = $dayNames[$startDateTime->format('N') - 1];
            $dateLabel = $startDateTime->format('j M.');

            if (!isset($slotsByDate[$dateKey])) {
                $slotsByDate[$dateKey] = [
                    'label' => $dayOfWeek,
                    'date' => $dateLabel,
                    'slots' => [],
                    'office_name' => $slot['office_name'],
                    'office_id' => $slot['office_id'],
                ];
            }

            $slotsByDate[$dateKey]['slots'][] = [
                'time' => $timeLabel,
                'status' => $slot['status'],
                'slot_id' => $slot['slot_id'],
            ];
        }

        return array_values($slotsByDate);
    }
}
?>
