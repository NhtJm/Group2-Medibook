<?php
require_once __DIR__ . "/../model/OfficeModel.php";
require_once __DIR__ . "/../model/DoctorModel.php";

class OfficeController
{
    private $officeModel;
    private $doctorModel;

    public function __construct()
    {
        $this->officeModel = new OfficeModel();
        // Inject DoctorModel to get doctors affiliated with the office
        $this->doctorModel = new DoctorModel();
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
        require_once __DIR__ . "/../views/clinics.php";
    }

    private function searchOffices()
    {
        $query = $_GET['q'] ?? '';
        $location = $_GET['loc'] ?? '';

        $results = $this->officeModel->searchOffices($query, $location);

        $data = [
            'query' => $query,
            'location' => $location,
            'results' => $results,
        ];

        extract($data);
        require_once __DIR__ . "/../views/clinics.php";
    }

    private function viewOffice($office_id)
    {
        $office = $this->officeModel->getOfficeById($office_id);

        if (!$office) {
            // Handle Office Not Found (404)
            // Or redirect to search page
            header("Location: " . BASE_URL . "index.php?page=clinics");
            exit;
        }

        $phones = $this->officeModel->getPhonesByOffice($office_id);
        $doctors = $this->doctorModel->getDoctorsByOffice($office_id);

        $data = [
            'office' => $office,
            'phones' => $phones,
            'doctors' => $doctors,
        ];

        extract($data);
        require_once __DIR__ . "/../views/clinic.php";
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
            $dayOfWeek = $dayNames[$startDateTime->format('N') - 1]; // 1 (Mon) to 7 (Sun)
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

            // Status is fetched from Slots_free table (which is either 'available' or 'booked'/'unavailable')
            $slotsByDate[$dateKey]['slots'][] = [
                'time' => $timeLabel,
                'status' => $slot['status'],
                'slot_id' => $slot['slot_id'],
            ];
        }

        return array_values($slotsByDate);
    }
}