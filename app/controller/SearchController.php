<?php
// app/controller/SearchController.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Doctor.php';
require_once __DIR__ . '/../models/MedicalSpecialty.php';
require_once __DIR__ . '/../models/Office.php';

class SearchController
{
    /**
     * Return search suggestions as JSON
     */
    public static function suggest(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $q   = trim($_GET['q']   ?? '');
        $loc = trim($_GET['loc'] ?? '');

        $results = [
            'specialties' => [],
            'offices'     => [],
            'doctors'     => [],
        ];

        // Search doctors via Model
        if ($q !== '') {
            $results['doctors'] = Doctor::search($q, $loc, 10);
        }

        // Search specialties via Model
        if ($q !== '') {
            $results['specialties'] = MedicalSpecialty::search($q, 10);
        }

        // Search offices via Model
        $results['offices'] = Office::search($q, $loc, 10);

        echo json_encode(['ok' => true, 'results' => $results], JSON_UNESCAPED_UNICODE);
    }
}
