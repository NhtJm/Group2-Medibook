<?php
require_once __DIR__ . '/../db.php';

class DoctorModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::get_instance();
    }

    /* ===================== DOCTOR ===================== */

    public function addDoctor($user_id, $photo, $degree, $graduate, $specialty_id = null) {
        // Check if user exists:
        $checkUser = $this->conn->prepare("SELECT user_id FROM Users WHERE user_id = ?");
        $checkUser->bind_param("i", $user_id);
        $checkUser->execute();
        if ($checkUser->get_result()->num_rows === 0) {
            return ["status" => "fail", "message" => "User does not exist"];
        }

        // Check if user is already a doctor
        $checkDoctor = $this->conn->prepare("SELECT doctor_id FROM Doctor WHERE user_id = ?");
        $checkDoctor->bind_param("i", $user_id);
        $checkDoctor->execute();
        if ($checkDoctor->get_result()->num_rows > 0) {
            return ["status" => "fail", "message" => "User is already a doctor"];
        }

        // Check if specialty exists (if provided)
        if (!empty($specialty_id)) {
            $checkSpec = $this->conn->prepare("SELECT specialty_id FROM Medical_specialty WHERE specialty_id = ?");
            $checkSpec->bind_param("i", $specialty_id);
            $checkSpec->execute();
            if ($checkSpec->get_result()->num_rows === 0) {
                return ["status" => "fail", "message" => "Specialty not found"];
            }
        }

        // Add doctor   
        $stmt = $this->conn->prepare("
            INSERT INTO Doctor (user_id, photo, degree, graduate, specialty_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssi", $user_id, $photo, $degree, $graduate, $specialty_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return ["status" => "success", "doctor_id" => $stmt->insert_id];
        }
        return ["status" => "fail", "message" => "Insert failed"];
    }

    public function getDoctorById($doctor_id) {
        $stmt = $this->conn->prepare("
            SELECT d.*, 
                   u.name AS user_name, 
                   u.username, 
                   s.name AS specialty_name
            FROM Doctor d
            JOIN Users u ON d.user_id = u.user_id
            LEFT JOIN Medical_specialty s ON d.specialty_id = s.specialty_id
            WHERE d.doctor_id = ?
        ");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllDoctors() {
        $query = "
            SELECT d.doctor_id, u.name AS user_name, d.degree, d.graduate, 
                   d.photo, s.name AS specialty_name
            FROM Doctor d
            JOIN Users u ON d.user_id = u.user_id
            LEFT JOIN Medical_specialty s ON d.specialty_id = s.specialty_id
        ";
        return $this->conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public function updateDoctor($doctor_id, $data = []) {
        // Check if doctor exists
        $checkDoctor = $this->conn->prepare("SELECT doctor_id FROM Doctor WHERE doctor_id = ?");
        $checkDoctor->bind_param("i", $doctor_id);
        $checkDoctor->execute();
        $checkDoctor->store_result();
        if ($checkDoctor->num_rows === 0) {
            return ["status" => "fail", "message" => "Doctor not found"];
        }

        $fields = [];
        $types = '';
        $values = [];

        // Photo
        if (isset($data['photo'])) {
            $fields[] = "photo = ?";
            $types .= 's';
            $values[] = $data['photo'];
        }

        // Degree
        if (isset($data['degree'])) {
            $fields[] = "degree = ?";
            $types .= 's';
            $values[] = $data['degree'];
        }

        // Graduate
        if (isset($data['graduate'])) {
            $fields[] = "graduate = ?";
            $types .= 's';
            $values[] = $data['graduate'];
        }

        // Specialty
        if (isset($data['specialty_id'])) {
            if (!empty($data['specialty_id'])) {
                // Check if specialty exists
                $checkSpec = $this->conn->prepare("SELECT specialty_id FROM Medical_specialty WHERE specialty_id = ?");
                $checkSpec->bind_param("i", $data['specialty_id']);
                $checkSpec->execute();
                $checkSpec->store_result();
                if ($checkSpec->num_rows === 0) {
                    return ["status" => "fail", "message" => "Invalid specialty"];
                }
            }
            $fields[] = "specialty_id = ?";
            $types .= 'i';
            $values[] = $data['specialty_id'];
        }

        if (empty($fields)) {
            return ["status" => "fail", "message" => "No data to update"];
        }

        // Build dynamic SQL
        $sql = "UPDATE Doctor SET " . implode(", ", $fields) . " WHERE doctor_id = ?";
        $types .= 'i';
        $values[] = $doctor_id;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "doctor_id" => $doctor_id]
            : ["status" => "fail", "message" => "No changes made"];
    }

    public function deleteDoctor($doctor_id) {
        $stmt = $this->conn->prepare("DELETE FROM Doctor WHERE doctor_id = ?");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Doctor not found"];
        }

    /* ===================== DOCTOR_OFFICE ===================== */

    public function assignOffice($doctor_id, $office_id) {
        // Check if doctor exists
        $checkDoc = $this->conn->prepare("SELECT doctor_id FROM Doctor WHERE doctor_id = ?");
        $checkDoc->bind_param("i", $doctor_id);
        $checkDoc->execute();
        if ($checkDoc->get_result()->num_rows === 0) {
            return ["status" => "fail", "message" => "Doctor not found"];
        }

        // Check if office exists
        $checkOffice = $this->conn->prepare("SELECT office_id FROM Office WHERE office_id = ?");
        $checkOffice->bind_param("i", $office_id);
        $checkOffice->execute();
        if ($checkOffice->get_result()->num_rows === 0) {
            return ["status" => "fail", "message" => "Office not found"];
        }

        // Check for duplicate assignment
        $checkDup = $this->conn->prepare("
            SELECT * FROM Doctor_office WHERE doctor_id = ? AND office_id = ?
        ");
        $checkDup->bind_param("ii", $doctor_id, $office_id);
        $checkDup->execute();
        if ($checkDup->get_result()->num_rows > 0) {
            return ["status" => "fail", "message" => "This office is already assigned to the doctor"];
        }

        // Assign office
        $stmt = $this->conn->prepare("
            INSERT INTO Doctor_office (doctor_id, office_id)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $doctor_id, $office_id);
        $stmt->execute();

        return $stmt->affected_rows > 0 ? ["status" => "success", "doctor_id" => $doctor_id, "office_id" => $office_id] : ["status" => "fail", "message" => "Failed to assign office"];
    }

    // Get offices assigned to a doctor
    public function getDoctorOffices($doctor_id) {
        $stmt = $this->conn->prepare("
            SELECT o.office_id, o.name, o.address, o.status
            FROM Doctor_office dof
            JOIN Office o ON dof.office_id = o.office_id
            WHERE dof.doctor_id = ?
        ");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteDoctorOffice($doctor_id, $office_id) {
        $stmt = $this->conn->prepare("
            DELETE FROM Doctor_office WHERE doctor_id = ? AND office_id = ?
        ");
        $stmt->bind_param("ii", $doctor_id, $office_id);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Assignment not found"];
    }

    /* ===================== MEDICAL_SPECIALTY ===================== */

    public function getAllSpecialties() {
        $query = "SELECT * FROM Medical_specialty";
        return $this->conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public function addSpecialty($name, $description) {
        // Check for duplicate specialty name
        $check = $this->conn->prepare("SELECT * FROM Medical_specialty WHERE name = ?");
        $check->bind_param("s", $name);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ["status" => "fail", "message" => "Specialty name already exists"];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO Medical_specialty (name, description)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "specialty_id" => $stmt->insert_id]
            : ["status" => "fail", "message" => "Insert failed"];
    }
    
    public function getSpecialtyById($specialty_id) {
        $stmt = $this->conn->prepare("SELECT * FROM Medical_specialty WHERE specialty_id = ?");
        $stmt->bind_param("i", $specialty_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateSpecialty($specialty_id, $data = []) {
        // Check if specialty exists
        $checkSpec = $this->conn->prepare("SELECT specialty_id FROM Medical_specialty WHERE specialty_id = ?");
        $checkSpec->bind_param("i", $specialty_id);
        $checkSpec->execute();
        $checkSpec->store_result();
        if ($checkSpec->num_rows === 0) {
            return ["status" => "fail", "message" => "Specialty not found"];
        }

        $fields = [];
        $types = '';
        $values = [];

        // Name
        if (isset($data['name'])) {
            // Check for duplicate name
            $checkDup = $this->conn->prepare("SELECT specialty_id FROM Medical_specialty WHERE name = ? AND specialty_id != ?");
            $checkDup->bind_param("si", $data['name'], $specialty_id);
            $checkDup->execute();
            $checkDup->store_result();
            if ($checkDup->num_rows > 0) {
                return ["status" => "fail", "message" => "Specialty name already exists"];
            }

            $fields[] = "name = ?";
            $types .= 's';
            $values[] = $data['name'];
        }

        // Description
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $types .= 's';
            $values[] = $data['description'];
        }

        if (empty($fields)) {
            return ["status" => "fail", "message" => "No data to update"];
        }

        // Build dynamic SQL
        $sql = "UPDATE Medical_specialty SET " . implode(", ", $fields) . " WHERE specialty_id = ?";
        $types .= 'i';
        $values[] = $specialty_id;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success", "specialty_id" => $specialty_id]
            : ["status" => "fail", "message" => "No changes made"];
    }

    public function deleteSpecialty($specialty_id) {
        $stmt = $this->conn->prepare("DELETE FROM Medical_specialty WHERE specialty_id = ?");
        $stmt->bind_param("i", $specialty_id);
        $stmt->execute();

        return $stmt->affected_rows > 0
            ? ["status" => "success"]
            : ["status" => "fail", "message" => "Specialty not found"];
    }
}
?>
