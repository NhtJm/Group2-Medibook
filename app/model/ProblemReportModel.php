<?php
require_once __DIR__ . '/../db.php';

class ProblemReportModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::get_instance();
    }

    /* ===================== PROBLEM REPORT ===================== */

    public function addReport($description, $patient_id = null, $doctor_id = null, $office_id = null) {
        // Check description not empty
        if (empty(trim($description))) {
            return ['status' => 'fail', 'message' => 'The description cannot be empty'];
        }

        // Check only one type selected
        $nonNullCount = 0;
        foreach ([$patient_id, $doctor_id, $office_id] as $id) {
            if (!is_null($id)) $nonNullCount++;
        }
        if ($nonNullCount !== 1) {
            return ['status' => 'fail', 'message' => 'Can only report as one type: patient, doctor, or office'];
        }

        // Check existence of referenced IDs
        if (!is_null($patient_id)) {
            $check = $this->conn->prepare("SELECT patient_id FROM Patient WHERE patient_id = ?");
            $check->bind_param("i", $patient_id);
            $check->execute();
            if ($check->get_result()->num_rows === 0) {
                return ['status' => 'fail', 'message' => 'Patient does not exist'];
            }
        }
        if (!is_null($doctor_id)) {
            $check = $this->conn->prepare("SELECT doctor_id FROM Doctor WHERE doctor_id = ?");
            $check->bind_param("i", $doctor_id);
            $check->execute();
            if ($check->get_result()->num_rows === 0) {
                return ['status' => 'fail', 'message' => 'Doctor does not exist'];
            }
        }
        if (!is_null($office_id)) {
            $check = $this->conn->prepare("SELECT office_id FROM Office WHERE office_id = ?");
            $check->bind_param("i", $office_id);
            $check->execute();
            if ($check->get_result()->num_rows === 0) {
                return ['status' => 'fail', 'message' => 'Office does not exist'];
            }
        }

        // Add report
        $stmt = $this->conn->prepare("
            INSERT INTO Problem_report (description, patient_id, doctor_id, office_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("siii", $description, $patient_id, $doctor_id, $office_id);
        $success = $stmt->execute();

        return $stmt->affected_rows > 0
        ? ['status' => 'success', 'report_id' => $this->conn->insert_id] 
        : ['status' => 'fail', 'message' => 'Error adding report'];
    }

    public function getAllReports() {
        $query = "
            SELECT 
                pr.report_id,
                pr.description,
                pr.date_report,
                pr.patient_id,
                pr.doctor_id,
                pr.office_id,
                COALESCE(pu.name, du.name, o.name) AS reporter_name,
                CASE
                    WHEN pr.patient_id IS NOT NULL THEN 'Patient'
                    WHEN pr.doctor_id IS NOT NULL THEN 'Doctor'
                    WHEN pr.office_id IS NOT NULL THEN 'Office'
                END AS reporter_type
            FROM Problem_report pr
            LEFT JOIN Patient p ON pr.patient_id = p.patient_id
            LEFT JOIN Users pu ON p.user_id = pu.user_id
            LEFT JOIN Doctor d ON pr.doctor_id = d.doctor_id
            LEFT JOIN Users du ON d.user_id = du.user_id
            LEFT JOIN Office o ON pr.office_id = o.office_id
            ORDER BY pr.date_report DESC
        ";
        return $this->conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public function getReportById($report_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                pr.*,
                COALESCE(pu.name, du.name, o.name) AS reporter_name,
                CASE
                    WHEN pr.patient_id IS NOT NULL THEN 'Patient'
                    WHEN pr.doctor_id IS NOT NULL THEN 'Doctor'
                    WHEN pr.office_id IS NOT NULL THEN 'Office'
                END AS reporter_type
            FROM Problem_report pr
            LEFT JOIN Patient p ON pr.patient_id = p.patient_id
            LEFT JOIN Users pu ON p.user_id = pu.user_id
            LEFT JOIN Doctor d ON pr.doctor_id = d.doctor_id
            LEFT JOIN Users du ON d.user_id = du.user_id
            LEFT JOIN Office o ON pr.office_id = o.office_id
            WHERE pr.report_id = ?
        ");
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getReportsByPatient($patient_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM Problem_report WHERE patient_id = ?
            ORDER BY date_report DESC
        ");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getReportsByDoctor($doctor_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM Problem_report WHERE doctor_id = ?
            ORDER BY date_report DESC
        ");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getReportsByOffice($office_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM Problem_report WHERE office_id = ?
            ORDER BY date_report DESC
        ");
        $stmt->bind_param("i", $office_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updateReportDescription($report_id, $description) {
        // Check existence
        $check = $this->getReportById($report_id);
        if (!$check) {
            return ['status' => "fail", 'message' => 'Report does not exist'];
        }
        if (empty(trim($description))) {
            return ['status' => "fail", 'message' => 'Description cannot be empty'];
        }

        $stmt = $this->conn->prepare("
            UPDATE Problem_report
            SET description = ?
            WHERE report_id = ?
        ");
        $stmt->bind_param("si", $description, $report_id);
        $success = $stmt->execute();

        return $stmt->affected_rows > 0
            ? ['status' => 'success', 'report_id' => $report_id]
            : ['status' => 'fail', 'message' => 'No changes made to the report'];
    }

    public function deleteReport($report_id) {
        // Check existence
        $check = $this->getReportById($report_id);
        if (!$check) {
            return ['status' => 'fail', 'message' => 'Report does not exist'];
        }

        $stmt = $this->conn->prepare("DELETE FROM Problem_report WHERE report_id = ?");
        $stmt->bind_param("i", $report_id);
        $success = $stmt->execute();

        return $stmt->affected_rows > 0
            ?  ['status' => 'success', 'message' => "Sucess delete"] 
        : ['status' => 'fail', 'message' => 'Failed to delete'];
    }
}
?>
