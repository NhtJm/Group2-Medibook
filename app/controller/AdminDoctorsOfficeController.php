<?php
// app/controller/AdminDoctorsOfficeController.php
require_once __DIR__ . '/../db.php';

class AdminDoctorsOfficeController
{
    public static function index(): array
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        $officeId = (int) ($_GET['office'] ?? 0);
        if ($officeId <= 0) {
            http_response_code(404);
            return ['error' => 'Missing or invalid office id'];
        }

        // 1) Fetch the office (clinic) header
        $st = $conn->prepare("
            SELECT office_id, name, address, slug, logo, status, rating
            FROM Office
            WHERE office_id = ?
            LIMIT 1
        ");
        $st->bind_param('i', $officeId);
        $st->execute();
        $office = $st->get_result()->fetch_assoc();
        $st->close();

        if (!$office) {
            http_response_code(404);
            return ['error' => 'Office not found'];
        }

        // 2) Fetch doctors in this office
        // NOTE: columns per your schema:
        // Doctor(doctor_id, office_id, name, email, photo, degree, graduate, specialty_id)
        // Medical_specialty(specialty_id, slug, name, ...)
        // app/controller/AdminDoctorsOfficeController.php (inside index(), step 2)
        // $officeId already validated as int

        $sql = "
  SELECT
    d.*,                                    -- avoid naming a column that may not exist
    s.name AS specialty_name,
    s.slug AS specialty_slug
  FROM Doctor AS d
  LEFT JOIN Medical_specialty AS s
         ON s.specialty_id = d.specialty_id
  WHERE d.office_id = ?
  ORDER BY d.doctor_id ASC                   -- don't ORDER BY a maybe-missing column
";
        $st = $conn->prepare($sql);
        $st->bind_param('i', $officeId);
        $st->execute();
        $doctors = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st->close();

        // Optional: compute total
        $total = count($doctors);

        return [
            'office' => $office,
            'doctors' => $doctors,
            'total' => $total,
        ];
    }
}