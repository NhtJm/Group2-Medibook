<?php
require_once __DIR__ . '/../db.php';

class OfficeModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::get_instance();
    }

    /* ===================== OFFICE ===================== */

    public function addOffice($user_id, $name, $address, $website, $logo, $description, $status = 'pending') { // CHANGE HERE
        // Check if user exists // CHANGE HERE
        $checkUser = $this->conn->prepare("SELECT user_id FROM Users WHERE user_id = ?"); // CHANGE HERE
        $checkUser->bind_param("i", $user_id); // CHANGE HERE
        $checkUser->execute(); // CHANGE HERE
        if ($checkUser->get_result()->num_rows === 0) { // CHANGE HERE
            return ["status" => "fail", "message" => "User not found"]; // CHANGE HERE
        }

        // Check for duplicate office by name or address
        $check = $this->conn->prepare("SELECT * FROM Office WHERE name = ? OR address = ?");
        $check->bind_param("ss", $name, $address);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ["status" => "fail", "message" => "Office already exists"];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO Office (user_id, name, address, website, logo, description, status)  -- CHANGE HERE
            VALUES (?, ?, ?, ?, ?, ?, ?)  -- CHANGE HERE
        ");
        $stmt->bind_param("issssss", $user_id, $name, $address, $website, $logo, $description, $status); // CHANGE HERE
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "office_id" => $stmt->insert_id]
            : ["status" => "fail", "message" => "Insert failed"];
    }

    public function getOfficeById($office_id) {
        $stmt = $this->conn->prepare("
            SELECT o.*, u.username, u.email  -- CHANGE HERE
            FROM Office o
            JOIN Users u ON o.user_id = u.user_id  -- CHANGE HERE
            WHERE o.office_id = ?  -- CHANGE HERE
        ");
        $stmt->bind_param("i", $office_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllOffices() {
        $query = "
            SELECT o.office_id, o.name, o.address, o.status, u.username, u.email  -- CHANGE HERE
            FROM Office o
            JOIN Users u ON o.user_id = u.user_id  -- CHANGE HERE
        ";
        return $this->conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public function updateOffice($office_id, $data = []) {
        // Check if office exists before updating
        $existing = $this->getOfficeById($office_id);
        if (!$existing) {
            return ["status" => "fail", "message" => "Office not found"];
        }

        $fields = [];
        $types = '';
        $values = [];

        // Allow updating user_id if provided // CHANGE HERE
        if (isset($data['user_id'])) { // CHANGE HERE
            $checkUser = $this->conn->prepare("SELECT user_id FROM Users WHERE user_id = ?"); // CHANGE HERE
            $checkUser->bind_param("i", $data['user_id']); // CHANGE HERE
            $checkUser->execute(); // CHANGE HERE
            if ($checkUser->get_result()->num_rows === 0) { // CHANGE HERE
                return ["status" => "fail", "message" => "Invalid user_id"]; // CHANGE HERE
            }
            $fields[] = "user_id = ?"; // CHANGE HERE
            $types .= 'i'; // CHANGE HERE
            $values[] = $data['user_id']; // CHANGE HERE
        }

        foreach ($data as $key => $value) {
            if ($key === 'user_id') continue; // CHANGE HERE
            $fields[] = "$key = ?";
            $types .= 's';
            $values[] = $value;
        }

        if (empty($fields)) {
            return ["status" => "fail", "message" => "No data to update"];
        }

        $values[] = $office_id;
        $types .= 'i';

        $sql = "UPDATE Office SET " . implode(", ", $fields) . " WHERE office_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "office_id" => $office_id]
            : ["status" => "fail", "message" => "No change made"];
    }

    public function deleteOffice($office_id) {
        $stmt = $this->conn->prepare("DELETE FROM Office WHERE office_id = ?");
        $stmt->bind_param("i", $office_id);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Delete failed"];
    }

    /* ===================== OFFICE PHONE ===================== */

    public function addPhone($office_id, $phone) {
        // Check for duplicate phone number within the same office
        $check = $this->conn->prepare("SELECT * FROM Office_phone WHERE office_id = ? AND phone = ?");
        $check->bind_param("is", $office_id, $phone);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ["status" => "fail", "message" => "Phone number already exists for this office"];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO Office_phone (office_id, phone)
            VALUES (?, ?)
        ");
        $stmt->bind_param("is", $office_id, $phone);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "office_id" => $office_id]
            : ["status" => "fail"];
    }

    public function getPhonesByOffice($office_id) {
        $stmt = $this->conn->prepare("
            SELECT phone FROM Office_phone WHERE office_id = ?
        ");
        $stmt->bind_param("i", $office_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updatePhone($office_id, $old_phone, $new_phone) {
        // Check for duplicate new phone number within the same office
        $check = $this->conn->prepare("SELECT * FROM Office_phone WHERE office_id = ? AND phone = ?");
        $check->bind_param("is", $office_id, $new_phone);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ["status" => "fail", "message" => "New phone number already exists for this office"];
        }

        $stmt = $this->conn->prepare("
            UPDATE Office_phone SET phone = ? 
            WHERE office_id = ? AND phone = ?
        ");
        $stmt->bind_param("sis", $new_phone, $office_id, $old_phone);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "office_id" => $office_id, "phone" => $new_phone]
            : ["status" => "fail", "message" => "Update failed"];
    }

    public function deletePhone($office_id, $phone) {
        $stmt = $this->conn->prepare("
            DELETE FROM Office_phone WHERE office_id = ? AND phone = ?
        ");
        $stmt->bind_param("is", $office_id, $phone);
        $stmt->execute();
        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail"," message" => "Delete failed"];
    }

    /* ===================== OFFICE HAS SPECIALTY ===================== */ // CHANGE HERE

    public function addOfficeSpecialty($office_id, $specialty_id) { // CHANGE HERE
        $stmt = $this->conn->prepare("
            INSERT INTO Office_has_specialty (office_id, specialty_id)
            VALUES (?, ?)  -- CHANGE HERE
        ");
        $stmt->bind_param("ii", $office_id, $specialty_id); // CHANGE HERE
        $stmt->execute();
        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Insert failed"];
    }

    public function deleteOfficeSpecialty($office_id, $specialty_id) { // CHANGE HERE
        $stmt = $this->conn->prepare("
            DELETE FROM Office_has_specialty
            WHERE office_id = ? AND specialty_id = ?  -- CHANGE HERE
        ");
        $stmt->bind_param("ii", $office_id, $specialty_id); // CHANGE HERE
        $stmt->execute();
        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Delete failed"];
    }

    public function getSpecialtiesByOffice($office_id) { // CHANGE HERE
        $stmt = $this->conn->prepare("
            SELECT s.specialty_id, s.name
            FROM Office_has_specialty ohs
            JOIN Medical_specialty s ON ohs.specialty_id = s.specialty_id
            WHERE ohs.office_id = ?  -- CHANGE HERE
        ");
        $stmt->bind_param("i", $office_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
