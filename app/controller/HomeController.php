<?php
require_once __DIR__ . "/../model/DoctorModel.php";
require_once __DIR__ . "/../model/OfficeModel.php";

class HomeController
{
    private $doctorModel;
    private $officeModel;

    public function __construct()
    {
        $this->doctorModel = new DoctorModel();
        $this->officeModel = new OfficeModel();
    }

    public function index($page)
    {
        switch ($page) {
            case 'about':
                $this->about();
                break;
            case 'contact':
                $this->contact();
                break;
            default:
                $this->homepage();
                break;
        }
    }

    private function homepage()
    {
        $popularSpecialties = $this->doctorModel->getAllSpecialties();
        $featuredOffices = $this->officeModel->getAllOffices();
        
        $data = [
            'specialties' => $popularSpecialties,
            'offices' => $featuredOffices,
        ];

        extract($data);
        require_once __DIR__ . "/../views/home.php";
    }

    private function about()
    {
        //*******MISSING**************
        require_once __DIR__ . "/../views/about.php";
    }

    private function contact()
    {
        require_once __DIR__ . '/../model/ContactModel.php';
        $contactModel = new ContactModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');

            $result = $contactModel->submitForm($name, $email, $subject, $message);

            if ($result['status'] === 'success') {
                $successMessage = "Thank for contact, we will reply soon!";
            } else {
                $errorMessage = $result['message'] ?? "Fail to submit, please try again!";
            }
        }

        if (isset($successMessage)) {
            $data['success'] = $successMessage;
        }
        if (isset($errorMessage)) {
            $data['error'] = $errorMessage;
        }

        //*******MISSING**************
        require_once __DIR__ . "/../views/contact.php";
    }
}