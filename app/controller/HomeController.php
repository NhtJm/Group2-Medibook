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
            case 'doctors': //CHANGE HERE
                $this->doctors(); //CHANGE HERE
                break;
            case 'offices': //CHANGE HERE
                $this->offices(); //CHANGE HERE
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
        //Already have
        require_once __DIR__ . "/../views/home.php";
    }

    private function about()
    {
        //*******MISSING**************
        $teamMembers = [ //CHANGE HERE
            ['name' => 'Dr. John Smith', 'role' => 'Founder & Chief Doctor', 'bio' => 'Over 20 years of experience in healthcare.'], //CHANGE HERE
            ['name' => 'Anna Nguyen', 'role' => 'Lead Web Developer', 'bio' => 'Responsible for platform development and UI/UX.'], //CHANGE HERE
            ['name' => 'David Tran', 'role' => 'Marketing Manager', 'bio' => 'Handles brand communication and outreach.'], //CHANGE HERE
        ]; //CHANGE HERE

        $data = ['team' => $teamMembers]; //CHANGE HERE
        extract($data); //CHANGE HERE
        //MISSING*************************
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
        $contactList = $contactModel->getAllContacts(); //CHANGE HERE
        $data['contacts'] = $contactList; //CHANGE HERE
        extract($data); //CHANGE HERE
        //MISSING*************************
        require_once __DIR__ . "/../views/contact.php";
    }

    private function doctors() //CHANGE HERE
    {
        $doctors = $this->doctorModel->getAllDoctors(); //CHANGE HERE
        $specialties = $this->doctorModel->getAllSpecialties(); //CHANGE HERE

        $data = [
            'doctors' => $doctors,
            'specialties' => $specialties,
        ]; //CHANGE HERE

        extract($data); //CHANGE HERE
        //MISSING*************************
        require_once __DIR__ . "/../views/doctors.php"; //CHANGE HERE
    }

    private function offices() //CHANGE HERE
    {
        $offices = $this->officeModel->getAllOffices(); //CHANGE HERE

        foreach ($offices as &$office) { //CHANGE HERE
            $office['specialties'] = $this->officeModel->getSpecialtiesByOffice($office['office_id']); //CHANGE HERE
            $office['phones'] = $this->officeModel->getPhonesByOffice($office['office_id']); //CHANGE HERE
        } //CHANGE HERE

        $data = ['offices' => $offices]; //CHANGE HERE
        extract($data); //CHANGE HERE
        //MISSING*******************
        require_once __DIR__ . "/../views/offices.php"; //CHANGE HERE
    }
}
?>
