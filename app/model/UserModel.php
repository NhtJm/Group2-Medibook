<?php
require_once __DIR__ . "/../db.php";

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::get_instance();
    }

    // ==========================
    //  USERS TABLE
    // ==========================

    public function getAllUsers() {
        $sql = "SELECT * FROM Users";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUserById($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getUserByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addUser($username, $password, $full_name, $role, $email = null) { // CHANGE HERE
        if ($this->getUserByUsername($username)) {
            return ["status" => "fail", "message" => "Username already exists"];
        }

        if (empty($username) || empty($password) || empty($full_name) || empty($role)) { // CHANGE HERE
            return ["status" => "fail", "message" => "Missing required fields"];
        }

        $valid_roles = ['admin', 'webstaff', 'office', 'patient']; // CHANGE HERE
        if (!in_array($role, $valid_roles)) {
            return ["status" => "fail", "message" => "Invalid role"];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO Users (email, username, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)"); // CHANGE HERE
        $stmt->bind_param("sssss", $email, $username, $passwordHash, $full_name, $role); // CHANGE HERE
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "user_id" => $stmt->insert_id]
            : ["status" => "fail", "message" => "Failed to add user"];
    }


    public function updateUser($user_id, $data = []) {
        $user = $this->getUserById($user_id);
        if (!$user) {
            return ["status" => "fail", "message" => "User not found"];
        }

        $fields = [];
        $types = '';
        $values = [];

        if (isset($data['full_name'])) { // CHANGE HERE
            $fields[] = "full_name = ?"; // CHANGE HERE
            $types .= 's';
            $values[] = $data['full_name']; // CHANGE HERE
        }

        if (isset($data['username'])) {
            $stmt = $this->db->prepare("SELECT user_id FROM Users WHERE username = ? AND user_id != ?");
            $stmt->bind_param("si", $data['username'], $user_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                return ["status" => "fail", "message" => "Username already taken"];
            }
            $fields[] = "username = ?";
            $types .= 's';
            $values[] = $data['username'];
        }

        if (isset($data['password'])) {
            $fields[] = "password_hash = ?";
            $types .= 's';
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (isset($data['email'])) { // CHANGE HERE
            $fields[] = "email = ?"; // CHANGE HERE
            $types .= 's';
            $values[] = $data['email']; // CHANGE HERE
        }

        if (isset($data['role'])) { // CHANGE HERE
            $valid_roles = ['admin', 'webstaff', 'office', 'patient']; // CHANGE HERE
            if (!in_array($data['role'], $valid_roles)) {
                return ["status" => "fail", "message" => "Invalid role"];
            }
            $fields[] = "role = ?"; // CHANGE HERE
            $types .= 's';
            $values[] = $data['role']; // CHANGE HERE
        }

        if (empty($fields)) {
            return ["status" => "fail", "message" => "No data to update"];
        }

        $sql = "UPDATE Users SET " . implode(", ", $fields) . " WHERE user_id = ?";
        $types .= 'i';
        $values[] = $user_id;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "user_id" => $user_id]
            : ["status" => "fail", "message" => "No changes made"];
    }

    public function deleteUser($user_id) {
        if (!$this->getUserById($user_id)) {
            return ["status" => "fail", "message" => "User not found"];
        }

        // Optional: delete cascade (phones, staff, patient)
        $this->db->query("DELETE FROM User_phone WHERE user_id = $user_id");
        $this->db->query("DELETE FROM Web_staff WHERE user_id = $user_id");
        $this->db->query("DELETE FROM Patient WHERE user_id = $user_id");
        $this->db->query("DELETE FROM Admin WHERE user_id = $user_id"); // CHANGE HERE
        $this->db->query("DELETE FROM Office WHERE user_id = $user_id"); // CHANGE HERE

        $stmt = $this->db->prepare("DELETE FROM Users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Delete failed"];
    }

    // ==========================
    //  USER_PHONE TABLE
    // ==========================

    public function getPhonesByUser($user_id) {
        $stmt = $this->db->prepare("SELECT phone FROM User_phone WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addPhone($user_id, $phone) {
        if (!$this->getUserById($user_id)) {
            return ["status" => "fail", "message" => "User not found"];
        }

        // Prevent duplicate phone for same user
        $stmt = $this->db->prepare("SELECT * FROM User_phone WHERE user_id = ? AND phone = ?");
        $stmt->bind_param("is", $user_id, $phone);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ["status" => "fail", "message" => "Phone already exists"];
        }

        $stmt = $this->db->prepare("INSERT INTO User_phone (user_id, phone) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $phone);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Failed to add phone"];
    }

    public function updatePhone($user_id, $old_phone, $new_phone) {
        if (!$this->getUserById($user_id)) {
            return ["status" => "fail", "message" => "User not found"];
        }

        $stmt = $this->db->prepare("SELECT * FROM User_phone WHERE user_id = ? AND phone = ?");
        $stmt->bind_param("is", $user_id, $old_phone);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return ["status" => "fail", "message" => "Old phone not found"];
        }

        $stmt = $this->db->prepare("SELECT * FROM User_phone WHERE user_id = ? AND phone = ?");
        $stmt->bind_param("is", $user_id, $new_phone);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ["status" => "fail", "message" => "New phone already exists"];
        }

        $stmt = $this->db->prepare("UPDATE User_phone SET phone = ? WHERE user_id = ? AND phone = ?");
        $stmt->bind_param("sis", $new_phone, $user_id, $old_phone);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "user_id" => $user_id]
            : ["status" => "fail", "message" => "No changes made"];
    }


    public function deletePhone($user_id, $phone) {
        $stmt = $this->db->prepare("DELETE FROM User_phone WHERE user_id = ? AND phone = ?");
        $stmt->bind_param("is", $user_id, $phone);
        $stmt->execute();
        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Delete failed"];}

    // ==========================
    //  WEB_STAFF TABLE
    // ==========================

    public function getStaffByUser($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM Web_staff WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addStaff($user_id, $position, $status = "offline") { // CHANGE HERE
        if (!$this->getUserById($user_id)) {
            return ["status" => "fail", "message" => "User not found"];
        }

        $valid_status = ['online', 'offline'];
        if (!in_array($status, $valid_status)) {
            return ["status" => "fail", "message" => "Invalid status"];
        }

        $valid_positions = ['support','developer','designer']; // CHANGE HERE
        if (!in_array($position, $valid_positions)) {
            return ["status" => "fail", "message" => "Invalid position"];
        }

        if ($this->getStaffByUser($user_id)) {
            return ["status" => "fail", "message" => "Staff already exists"];
        }

        $stmt = $this->db->prepare("INSERT INTO Web_staff (user_id, position, status) VALUES (?, ?, ?)"); // CHANGE HERE
        $stmt->bind_param("iss", $user_id, $position, $status); // CHANGE HERE
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "staff_id" => $stmt->insert_id]
            : ["status" => "fail"];
    }

    public function updateStaff($staff_id, $data = []) {
        $stmt = $this->db->prepare("SELECT * FROM Web_staff WHERE staff_id = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return ["status" => "fail", "message" => "Staff not found"];
        }

        $fields = [];
        $types = '';
        $values = [];

        if (isset($data['status'])) {
            $valid_status = ['online', 'offline'];
            if (!in_array($data['status'], $valid_status)) {
                return ["status" => "fail", "message" => "Invalid status"];
            }
            $fields[] = "status = ?";
            $types .= 's';
            $values[] = $data['status'];
        }

        if (isset($data['position'])) { // CHANGE HERE
            $valid_positions = ['support','developer','designer']; // CHANGE HERE
            if (!in_array($data['position'], $valid_positions)) {
                return ["status" => "fail", "message" => "Invalid position"];
            }
            $fields[] = "position = ?"; // CHANGE HERE
            $types .= 's';
            $values[] = $data['position']; // CHANGE HERE
        }

        if (empty($fields)) {
            return ["status" => "fail", "message" => "No data to update"];
        }

        $sql = "UPDATE Web_staff SET " . implode(", ", $fields) . " WHERE staff_id = ?";
        $types .= 'i';
        $values[] = $staff_id;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "staff_id" => $staff_id]
            : ["status" => "fail", "message" => "No changes made"];
    }


    public function deleteStaff($staff_id) {
        $stmt = $this->db->prepare("DELETE FROM Web_staff WHERE staff_id = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Delete failed"];
    }

    // ==========================
    //  PATIENT TABLE
    // ==========================

    public function getPatientByUser($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM Patient WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addPatient($user_id, $date_of_birth, $payment = null, $status = "active") {
        if (!$this->getUserById($user_id)) {
            return ["status" => "fail", "message" => "User not found"];
        }

        if ($this->getPatientByUser($user_id)) {
            return ["status" => "fail", "message" => "Patient record already exists"];
        }

        $status_array = ['active', 'inactive'];
        if (!in_array($status, $status_array)) {
            return ["status" => "fail", "message" => "Invalid status"];
        }

        $stmt = $this->db->prepare("INSERT INTO Patient (user_id, date_of_birth, status, payment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $date_of_birth, $status, $payment);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "patient_id" => $stmt->insert_id]
            : ["status" => "fail"];
    }

    public function updatePatient($patient_id, $data = []) {
        $stmt = $this->db->prepare("SELECT * FROM Patient WHERE patient_id = ?");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return ["status" => "fail", "message" => "Patient not found"];
        }

        $fields = [];
        $types = '';
        $values = [];

        if (isset($data['status'])) {
            $valid_status = ['active', 'inactive'];
            if (!in_array($data['status'], $valid_status)) {
                return ["status" => "fail", "message" => "Invalid status"];
            }
            $fields[] = "status = ?";
            $types .= 's';
            $values[] = $data['status'];
        }

        if (isset($data['date_of_birth'])) {
            $fields[] = "date_of_birth = ?";
            $types .= 's';
            $values[] = $data['date_of_birth']; // YYYY-MM-DD
        }

        if (isset($data['payment'])) {
            $fields[] = "payment = ?";
            $types .= 's';
            $values[] = $data['payment'];
        }

        if (empty($fields)) {
            return ["status" => "fail", "message" => "No data to update"];
        }

        // Build SQL
        $sql = "UPDATE Patient SET " . implode(", ", $fields) . " WHERE patient_id = ?";
        $types .= 'i';
        $values[] = $patient_id;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "No changes made"];
    }


    public function deletePatient($patient_id) {
        $stmt = $this->db->prepare("DELETE FROM Patient WHERE patient_id = ?");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Delete failed"];
    }
}
?>
