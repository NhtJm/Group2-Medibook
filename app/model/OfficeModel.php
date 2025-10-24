<?php
require_once __DIR__ . '/../db.php';

class OfficeModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::get_instance();
    }

    /* ===================== OFFICE ===================== */

    public function addOffice($name, $address, $website, $logo, $description, $status = 'pending') {
        // Check for duplicate office by name or address
        $check = $this->conn->prepare("SELECT * FROM Office WHERE name = ? OR address = ?");
        $check->bind_param("ss", $name, $address);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ["status" => "fail", "message" => "Office already exists"];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO Office (name, address, website, logo, description, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssss", $name, $address, $website, $logo, $description, $status);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "office_id" => $stmt->insert_id]
            : ["status" => "fail", "message" => "Insert failed"];
    }

    public function getOfficeById($office_id) {
        $stmt = $this->conn->prepare("SELECT * FROM Office WHERE office_id = ?");
        $stmt->bind_param("i", $office_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllOffices() {
        $query = "SELECT * FROM Office";
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

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $types .= 's'; // assuming all fields are strings for simplicity
            $values[] = $value;
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
        // Check if office has related slots or doctors
        $check = $this->conn->prepare("SELECT * FROM Doctor_office WHERE office_id = ?");
        $check->bind_param("i", $office_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ["status" => "fail", "message" => "Cannot delete office with assigned doctors"];
        }

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
            ? ["status" => "success", "office_id" => $office_id, "phone_id" => $stmt->insert_id]
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

    /* ===================== SLOTS FREE ===================== */

    public function addSlot($office_id, $doctor_id, $start, $end, $status = 'available') {
        // check for duplicate slot by time overlap
        $check = $this->conn->prepare("
            SELECT * FROM Slots_free 
            WHERE doctor_id = ? AND office_id = ? 
            AND ((start <= ? AND end > ?) OR (start < ? AND end >= ?))
        ");
        $check->bind_param("iissss", $doctor_id, $office_id, $start, $start, $end, $end);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ["status" => "fail", "message" => "Time slot overlaps with existing one"];
        }

        $status_array = ['available', 'unavailable'];
        if (!in_array($status, $status_array)) {
            return ["status" => "fail", "message" => "Invalid status value"];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO Slots_free (office_id, doctor_id, start, end, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisss", $office_id, $doctor_id, $start, $end, $status);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "slot_id" => $stmt->insert_id]
            : ["status" => "fail"];
    }

    public function getSlotById($slot_id) {
        $stmt = $this->conn->prepare("
            SELECT s.*, o.name AS office_name, d.doctor_id
            FROM Slots_free s
            JOIN Office o ON s.office_id = o.office_id
            JOIN Doctor d ON s.doctor_id = d.doctor_id
            WHERE s.slot_id = ?
        ");
        $stmt->bind_param("i", $slot_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getSlotsByDoctor($doctor_id) {
        $stmt = $this->conn->prepare("
            SELECT s.*, o.name AS office_name
            FROM Slots_free s
            JOIN Office o ON s.office_id = o.office_id
            WHERE s.doctor_id = ?
            ORDER BY s.start
        ");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updateSlotStatus($slot_id, $status) {
        $stmt = $this->conn->prepare("
            UPDATE Slots_free SET status = ? WHERE slot_id = ?
        ");
        $stmt->bind_param("si", $status, $slot_id);
        $stmt->execute();
        return $stmt->affected_rows > 0
            ? ["status" => "success", "slot_id" => $slot_id]
            : ["status" => "fail", "message" => "Update failed"];
    }

    public function deleteSlot($slot_id) {
        $stmt = $this->conn->prepare("DELETE FROM Slots_free WHERE slot_id = ?");
        $stmt->bind_param("i", $slot_id);
        $stmt->execute();
        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Delete failed"];
    }
}
?>
