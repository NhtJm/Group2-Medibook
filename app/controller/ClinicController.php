<?php
// app/controller/ClinicController.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Office.php';
require_once __DIR__ . '/../models/Doctor.php';

class ClinicController
{
    public static function show(): array
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            if (class_exists('Database') && method_exists('Database','get_instance')) {
                $conn = Database::get_instance();
            } else {
                throw new RuntimeException('DB connection not available');
            }
        }

        // Accept ?clinic= (preferred) or ?id=
        $officeId = (int)($_GET['clinic'] ?? ($_GET['id'] ?? 0));
        if ($officeId <= 0) return ['clinic' => null, 'doctors' => []];

        $clinic  = Office::findOne($conn, $officeId);
        if (!$clinic) return ['clinic' => null, 'doctors' => []];

        $doctors = Doctor::findByOffice($officeId);
        return ['clinic' => $clinic, 'doctors' => $doctors];
    }
}