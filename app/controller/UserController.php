<?php
require_once __DIR__ . "/../model/UserModel.php";

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index($action)
    {
        switch ($action) {
            case 'login':
                $this->login();
                break;
            case 'register':
                $this->register();
                break;
            case 'register2':
                $this->register2();
                break;
            case 'verifyPhone':
                $this->verifyPhone();
                break;
            case 'logout':
                $this->logout();
                break;
            default:
                break;
        }
    }

    private function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Already have
            require_once __DIR__ . "/../views/auth/login.php";
            return;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->getUserByUsername($email);

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user'] = [
                'id' => $user['user_id'],
                'name' => $user['name'],
                'email' => $user['username'],
                'role' => $user['role'] ?? 'patient'
            ];
            // Already have
            header("Location: " . BASE_URL . "index.php?page=dashboard");
            exit;
        }

        $data = ['error' => 'Invalid email or password.'];
        extract($data);
        // Already have
        require_once __DIR__ . "/../views/auth/login.php";
    }

    private function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Already have
            require_once __DIR__ . "/../views/auth/register.php";
            return;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password_confirm'] ?? '';
        $isProfessional = isset($_POST['is_professional']) ? 'staff' : 'patient';

        if ($password !== $confirmPassword) {
            $data = ['error' => 'Passwords do not match.'];
            extract($data);
            // Already have
            require_once __DIR__ . "/../views/auth/register.php";
            return;
        }

        $result = $this->userModel->addUser($email, $password, null, $isProfessional);

        if ($result['status'] === 'success') {
            session_start();
            $_SESSION['temp_user_id'] = $result['user_id'];
            // Already have
            header("Location: " . BASE_URL . "index.php?page=register2");
            exit;
        }

        $data = ['error' => $result['message']];
        extract($data);
        require_once __DIR__ . "/../views/auth/register.php";
    }

    private function register2()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Already have
            require_once __DIR__ . "/../views/auth/register2.php";
            return;
        }
        
        session_start();
        $userId = $_SESSION['temp_user_id'] ?? null;

        if (!$userId) {
            // Already have
            header("Location: " . BASE_URL . "index.php?page=register");
            exit;
        }

        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $dob = $_POST['dob'] ?? null;
        $sex = $_POST['sex'] ?? null;
        $fullName = trim($firstName . ' ' . $lastName);
        $user = $this->userModel->getUserById($userId);
        
        // Update User name
        $updateUser = $this->userModel->updateUser($userId, ['name' => $fullName]);
        
        // Add Patient record (assuming all non-staff users are patients for now)
        if ($user['role'] === 'patient') {
            $this->userModel->addPatient($userId, $dob, $sex);
        } else {
             // If professional, update web_staff record (if implemented)
            $this->userModel->addStaff($userId, $user['role']);
        }

        unset($_SESSION['temp_user_id']);
        $_SESSION['temp_name'] = $fullName;
        //*******MISSING**************
        header("Location: " . BASE_URL . "index.php?page=verifyPhone");
        exit;
    }

    private function verifyPhone()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            //*******MISSING**************
            require_once __DIR__ . "/../views/auth/verifyPhone.php";
            return;
        }

        session_start();
        $phone = $_POST['phone_number'] ?? '';
        // Assuming we rely on the final logged-in user session if registration is complete
        $user = $this->userModel->getUserByUsername($_SESSION['user']['email'] ?? '');
        $userId = $user['user_id'] ?? null;
        
        if (!$userId) {
            // Should not happen if previous steps worked, redirect to login
            header("Location: " . BASE_URL . "index.php?page=login");
            exit;
        }
        
        // The UserModel::addPhone function expects a user_id
        $result = $this->userModel->addPhone($userId, $phone, 'primary');

        if ($result['status'] === 'success') {
            header("Location: " . BASE_URL . "index.php?page=dashboard");
            exit;
        }

        $data = ['error' => $result['message']];
        extract($data);
        //*******MISSING**************
        require_once __DIR__ . "/../views/auth/verifyPhone.php";
    }

    private function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "index.php?page=home");
        exit;
    }
}