<?php
// app/controller/DoctorController.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Office.php';
require_once __DIR__ . '/../models/Doctor.php';
require_once __DIR__ . '/../models/AppointmentSlot.php';

class DoctorController
{
    /**
     * List all doctors in a clinic
     */
    public static function listByClinic(): array
    {
        $officeId = max(0, (int) ($_GET['clinic'] ?? 0));
        
        if ($officeId <= 0) {
            return ['clinic' => null, 'doctors' => []];
        }

        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        $clinic = Office::findOne($conn, $officeId);
        
        if ($clinic) {
            $slots = Office::nextSlots($conn, $officeId, 1);
            $clinic['is_open'] = Office::hasSlotToday($slots);
            $clinic['hours_label'] = $clinic['is_open'] ? 'Slots today' : null;
        }
        
        $doctors = $clinic ? Doctor::findByOffice($officeId) : [];
        
        return ['clinic' => $clinic, 'doctors' => $doctors];
    }

    /**
     * Calendar + slots (user-facing doctor visit page)
     */
    public static function visit(): array
    {
        $docId = (int) ($_GET['doc'] ?? 0);
        
        if ($docId <= 0) {
            return self::emptyVisitResponse();
        }

        $doctor = Doctor::findById($docId);
        
        if (!$doctor) {
            return self::emptyVisitResponse();
        }

        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        $officeId = (int) $doctor['office_id'];
        $clinic = Office::findOne($conn, $officeId);
        $slots = Office::nextSlots($conn, $officeId, 1);
        $clinic['is_open'] = Office::hasSlotToday($slots);
        $clinic['hours_label'] = $clinic['is_open'] ? 'Slots today' : null;

        $ymParam = $_GET['ym'] ?? '';
        $yearMonth = preg_match('/^\d{4}-\d{2}$/', $ymParam) ? $ymParam : date('Y-m');
        $month = AppointmentSlot::getMonthMeta($yearMonth);

        $counts = AppointmentSlot::getCountsByMonth($docId, $yearMonth);

        $selected = null;
        $dateSlots = [];
        $dateSel = $_GET['date'] ?? null;
        
        if ($dateSel && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateSel)) {
            $first = new DateTimeImmutable($yearMonth . '-01');
            $next = $first->modify('first day of next month');
            
            if ($dateSel >= $first->format('Y-m-01') && $dateSel < $next->format('Y-m-01')) {
                $selected = $dateSel;
                $dateSlots = AppointmentSlot::getForDoctorOnDate($docId, $selected);
            }
        }

        return [
            'clinic'   => $clinic,
            'doctor'   => $doctor,
            'month'    => $month,
            'counts'   => $counts,
            'selected' => $selected,
            'slots'    => $dateSlots,
            'today'    => (new DateTimeImmutable('today'))->format('Y-m-d'),
        ];
    }

    /**
     * Calendar data for AJAX requests
     */
    public static function calendarData(): array
    {
        $docId = max(0, (int) ($_GET['doc'] ?? 0));
        
        if ($docId <= 0) {
            return ['ok' => false, 'error' => 'bad doc'];
        }

        $ym = preg_match('/^\d{4}-\d{2}$/', $_GET['ym'] ?? '') ? $_GET['ym'] : date('Y-m');
        $month = AppointmentSlot::getMonthMeta($ym);

        $counts = AppointmentSlot::getCountsByMonth($docId, $ym);

        $selected = null;
        $slots = [];
        $dateSel = $_GET['date'] ?? null;
        
        if ($dateSel && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateSel)) {
            $first = new DateTimeImmutable($ym . '-01');
            $next = $first->modify('first day of next month');
            
            if ($dateSel >= $first->format('Y-m-01') && $dateSel < $next->format('Y-m-01')) {
                $selected = $dateSel;
                $slots = AppointmentSlot::getForDoctorOnDate($docId, $selected);
            }
        }

        $response = [
            'ok'       => true,
            'month'    => $month,
            'counts'   => $counts,
            'selected' => $selected,
            'slots'    => $slots,
            'today'    => (new DateTimeImmutable('today'))->format('Y-m-d'),
        ];

        if (!empty($_GET['ajax'])) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo json_encode($response);
            exit;
        }

        return $response;
    }

    private static function emptyVisitResponse(): array
    {
        return [
            'clinic'   => null,
            'doctor'   => null,
            'month'    => null,
            'counts'   => [],
            'selected' => null,
            'slots'    => [],
        ];
    }
}
