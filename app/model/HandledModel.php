<?php
require_once __DIR__ . "/../db.php";

class HandledModel {
    private $db;

    public function __construct() {
        $this->db = Database::get_instance();
    }

    public function getAllHandled() {
        $sql = "SELECT * FROM Handled";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getHandled($staff_id, $report_id) {
        $stmt = $this->db->prepare("SELECT * FROM Handled WHERE staff_id = ? AND report_id = ?");
        $stmt->bind_param("ii", $staff_id, $report_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addHandled($staff_id, $report_id, $description_action, $status = 'open') {
        if ($this->getHandled($staff_id, $report_id)) {
            return ["status" => "fail", "message" => "This report has already been handled by this staff"];
        }

        $valid_status = ['open', 'resolved', 'dismissed'];
        if (!in_array($status, $valid_status)) {
            return ["status" => "fail", "message" => "Invalid status"];
        }

        $checkStaff = $this->db->prepare("SELECT staff_id FROM Web_staff WHERE staff_id = ?");
        $checkStaff->bind_param("i", $staff_id);
        $checkStaff->execute();
        if ($checkStaff->get_result()->num_rows === 0) {
            return ["status" => "fail", "message" => "Staff not found"];
        }

        $checkReport = $this->db->prepare("SELECT report_id FROM Problem_report WHERE report_id = ?");
        $checkReport->bind_param("i", $report_id);
        $checkReport->execute();
        if ($checkReport->get_result()->num_rows === 0) {
            return ["status" => "fail", "message" => "Report not found"];
        }

        $stmt = $this->db->prepare("INSERT INTO Handled (staff_id, report_id, description_action, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $staff_id, $report_id, $description_action, $status);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "staff_id" => $staff_id, "report_id" => $report_id]
            : ["status" => "fail", "message" => "Insert failed"];
    }

    public function updateHandled($staff_id, $report_id, $data = []) {
        $handled = $this->getHandled($staff_id, $report_id);
        if (!$handled) {
            return ["status" => "fail", "message" => "Handled record not found"];
        }

        $fields = [];
        $types = '';
        $values = [];

        if (isset($data['description_action'])) {
            $fields[] = "description_action = ?";
            $types .= 's';
            $values[] = $data['description_action'];
        }

        if (isset($data['status'])) {
            $valid_status = ['open', 'resolved', 'dismissed'];
            if (!in_array($data['status'], $valid_status)) {
                return ["status" => "fail", "message" => "Invalid status"];
            }
            $fields[] = "status = ?";
            $types .= 's';
            $values[] = $data['status'];
        }

        // CHANGE HERE — cho phép cập nhật action_date (ngày xử lý)
        if (isset($data['action_date'])) {
            $fields[] = "action_date = ?";
            $types .= 's';
            $values[] = $data['action_date'];
        }

        if (empty($fields)) {
            return ["status" => "fail", "message" => "No data to update"];
        }

        $sql = "UPDATE Handled SET " . implode(", ", $fields) . " WHERE staff_id = ? AND report_id = ?";
        $types .= 'ii';
        $values[] = $staff_id;
        $values[] = $report_id;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "staff_id" => $staff_id, "report_id" => $report_id]
            : ["status" => "fail", "message" => "No changes made"];
    }

    public function deleteHandled($staff_id, $report_id) {
        if (!$this->getHandled($staff_id, $report_id)) {
            return ["status" => "fail", "message" => "Handled record not found"];
        }

        $stmt = $this->db->prepare("DELETE FROM Handled WHERE staff_id = ? AND report_id = ?");
        $stmt->bind_param("ii", $staff_id, $report_id);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Delete failed"];
    }

    public function getHandledReportsByStaff($staff_id) {
        $stmt = $this->db->prepare("
            SELECT H.*, P.description AS report_description, P.date_report 
            FROM Handled H
            JOIN Problem_report P ON H.report_id = P.report_id
            WHERE H.staff_id = ?
        ");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getStaffHandledReport($report_id) {
        $stmt = $this->db->prepare("
            SELECT H.*, W.position, W.status AS staff_status
            FROM Handled H
            JOIN Web_staff W ON H.staff_id = W.staff_id
            WHERE H.report_id = ?
        ");
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
