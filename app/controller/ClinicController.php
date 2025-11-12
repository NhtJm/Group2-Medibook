<?php
// app/controller/ClinicController.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Office.php';

class ClinicController
{
    public static function show(): array
    {
        // ---- DB bootstrap (router should already have done this) ----
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            if (class_exists('Database') && method_exists('Database','get_instance')) {
                $conn = Database::get_instance();
            } else {
                throw new RuntimeException('DB connection not available');
            }
        }

        // ---- Read clinic id ----
        $id = max(0, (int)($_GET['id'] ?? 0));

        // ---- Fetch clinic + doctors ----
        $clinic  = Office::findOne($conn, $id);                // returns null if not found
        $doctors = $clinic ? Office::doctorsWithNext($conn, $id) : [];

        return ['clinic' => $clinic, 'doctors' => $doctors];
    }
}