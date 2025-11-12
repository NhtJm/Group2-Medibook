<?php
// app/controller/ClinicsController.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Office.php';
require_once __DIR__ . '/../models/MedicalSpecialty.php';

class ClinicsController
{
    /**
     * Rules:
     * - If q or loc present  -> search across ALL facilities (ignore specialty).
     * - Else if specialty     -> list facilities of that specialty.
     * - Else                  -> list ALL facilities (paginated).
     */
    public static function index(): array
    {
        // get mysqli conn compatible with your db.php
        global $conn;
        if (!isset($conn) || !$conn) {
            if (class_exists('Database')) {
                $conn = Database::get_instance();
            }
        }

        $q      = trim((string)($_GET['q'] ?? ''));
        $loc    = trim((string)($_GET['loc'] ?? ''));
        $slug   = trim((string)($_GET['specialty'] ?? ''));
        $page   = max(1, (int)($_GET['p'] ?? 1));
        $per    = max(1, min(24, (int)($_GET['per'] ?? 10)));

        // If user typed anything, IGNORE specialty filter → search ALL
        if ($q !== '' || $loc !== '') {
            $res = Office::searchPaginated($conn, $q, $loc, $page, $per);
            return [
                'slug'      => '',
                'specialty' => null,
                'offices'   => $res['rows'],
                'page'      => $res['page'],
                'per'       => $res['perPage'],
                'total'     => $res['total'],
                'pages'     => $res['pages'],
                'q'         => $q,
                'loc'       => $loc,
            ];
        }

        // If no q/loc but has specialty slug → list by specialty
        if ($slug !== '') {
            $spec = MedicalSpecialty::findBySlug($conn, $slug);
            if ($spec) {
                $res = Office::bySpecialtyPaginated($conn, (int)$spec['specialty_id'], $page, $per);
                return [
                    'slug'      => $slug,
                    'specialty' => $spec,
                    'offices'   => $res['rows'],
                    'page'      => $res['page'],
                    'per'       => $res['perPage'],
                    'total'     => $res['total'],
                    'pages'     => $res['pages'],
                    'q'         => '',
                    'loc'       => '',
                ];
            }
            // Bad slug → empty state
            return [
                'slug'      => $slug,
                'specialty' => null,
                'offices'   => [],
                'page'      => 1,
                'per'       => $per,
                'total'     => 0,
                'pages'     => 1,
                'q'         => '',
                'loc'       => '',
            ];
        }

        // Default: show ALL approved facilities
        $res = Office::listPaginatedAll($conn, $page, $per);
        return [
            'slug'      => '',
            'specialty' => null,
            'offices'   => $res['rows'],
            'page'      => $res['page'],
            'per'       => $res['perPage'],
            'total'     => $res['total'],
            'pages'     => $res['pages'],
            'q'         => '',
            'loc'       => '',
        ];
    }
}