<?php
require_once __DIR__ . "/../db.php";

class ContactModel {
    private $db;

    public function __construct() {
        $this->db = Database::get_instance();
    }

    /* ==========================
       CONTACT TABLE
    ========================== */

    public function getAllContacts() {
        $sql = "SELECT * FROM Contact ORDER BY created_at DESC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getContactById($contact_id) {
        $stmt = $this->db->prepare("SELECT * FROM Contact WHERE contact_id = ?");
        $stmt->bind_param("i", $contact_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function submitForm($name, $email, $subject, $message) {
        if (empty($name) || empty($email) || empty($message)) {
            return ["status" => "fail", "message" => "Missing required fields"];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO Contact (name, email, subject, message) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "contact_id" => $stmt->insert_id]
            : ["status" => "fail", "message" => "Failed to submit contact"];
    }

    public function updateStatus($contact_id, $status) {
        $valid_status = ['new', 'read', 'replied'];
        if (!in_array($status, $valid_status)) {
            return ["status" => "fail", "message" => "Invalid status"];
        }

        if (!$this->getContactById($contact_id)) {
            return ["status" => "fail", "message" => "Contact not found"];
        }

        $stmt = $this->db->prepare("UPDATE Contact SET status = ? WHERE contact_id = ?");
        $stmt->bind_param("si", $status, $contact_id);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "contact_id" => $contact_id]
            : ["status" => "fail", "message" => "No changes made"];
    }

    public function deleteContact($contact_id) {
        if (!$this->getContactById($contact_id)) {
            return ["status" => "fail", "message" => "Contact not found"];
        }

        $stmt = $this->db->prepare("DELETE FROM Contact WHERE contact_id = ?");
        $stmt->bind_param("i", $contact_id);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Delete failed"];
    }
}
?>
