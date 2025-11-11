<?php
// app/controller/ClinicsController.php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Office.php';

class ClinicsController
{
    public static function index(): array
    {
        // Get mysqli connection (compatible with your db.php style)
        if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
            $conn = $GLOBALS['conn'];
        } elseif (class_exists('Database')) {
            $conn = Database::get_instance();
        } else {
            throw new RuntimeException('DB connection not available');
        }

        $q   = isset($_GET['q'])   ? trim((string)$_GET['q'])   : null;
        $loc = isset($_GET['loc']) ? trim((string)$_GET['loc']) : null;
        $p   = isset($_GET['p'])   ? (int)$_GET['p'] : 1;

        // 9 per page like your sample grid pacing, adjust if you wish
        $perPage = 10;

        $result = Office::searchPaginated($conn, $q, $loc, $p, $perPage);

        // Shape data for the view
        return [
            'clinics' => $result['rows'],
            'total'   => $result['total'],
            'page'    => $result['page'],
            'pages'   => $result['pages'],
            'q'       => $q,
            'loc'     => $loc,
        ];
    }
}