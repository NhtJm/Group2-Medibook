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
            case 'profile': // CHANGE HERE
                $this->profile(); // CHANGE HERE
                break; // CHANGE HERE
            default:
                // CHANGE HERE
                header("Location: " . BASE_URL . "index.php?page=login");
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

        if ($user && password_verify($password, $user['password_hash'])) { // CHANGE HERE
            session_start();
            $_SESSION['user'] = [
                'id' => $user['user_id'],
                'name' => $user['full_name'], // CHANGE HERE
                'email' => $user['email'], // CHANGE HERE
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
        $isProfessional = isset($_POST['is_professional']) ? 'webstaff' : 'patient'; // CHANGE HERE

        if ($password !== $confirmPassword) {
            $data = ['error' => 'Passwords do not match.'];
            extract($data);
            // Already have
            require_once __DIR__ . "/../views/auth/register.php";
            return;
        }

        $result = $this->userModel->addUser($email, $password, $email, $isProfessional, $email); // CHANGE HERE

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

        $updateUser = $this->userModel->updateUser($userId, ['full_name' => $fullName]); // CHANGE HERE

        if ($user['role'] === 'patient') {
            $this->userModel->addPatient($userId, $dob, null, 'active'); // CHANGE HERE
        } else {
            $this->userModel->addStaff($userId, 'support'); // CHANGE HERE
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

        $user = $this->userModel->getUserByUsername($_SESSION['user']['email'] ?? '');
        $userId = $user['user_id'] ?? null;

        if (!$userId) {
            header("Location: " . BASE_URL . "index.php?page=login");
            exit;
        }

        $result = $this->userModel->addPhone($userId, $phone); // CHANGE HERE

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

    // CHANGE HERE: thêm controller profile để người dùng xem & cập nhật thông tin cá nhân
    private function profile()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "index.php?page=login");
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->getUserById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $data = ['full_name' => $fullName, 'email' => $email];
            if (!empty($password)) $data['password'] = $password;

            $update = $this->userModel->updateUser($userId, $data);

            if ($update['status'] === 'success') {
                $_SESSION['user']['name'] = $fullName;
                $_SESSION['user']['email'] = $email;
                $success = "Profile updated successfully.";
            } else {
                $error = $update['message'];
            }
        }

        // CHANGE HERE: Already have
        require_once __DIR__ . "/../views/user/profile.php";
    }
}
?>
