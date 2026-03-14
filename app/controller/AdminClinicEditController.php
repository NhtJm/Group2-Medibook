<?php
// app/controller/AdminClinicEditController.php
require_once __DIR__ . '/../db.php';

class AdminClinicEditController
{
    public static function index(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance(); // returns mysqli
        }

        $id = (int)($_GET['clinic'] ?? 0);
        if ($id <= 0) {
            http_response_code(404);
            return ['errors' => ['global' => 'Missing clinic id']];
        }

        // Pull flash after redirect
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF
            $csrf = (string)($_POST['csrf'] ?? '');
            if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Security token invalid.'];
                header('Location: ' . BASE_URL . 'index.php?page=clinic_edit&clinic=' . $id);
                exit;
            }

            // Gather inputs
            $name        = trim((string)($_POST['name'] ?? ''));
            $slug        = strtolower(trim((string)($_POST['slug'] ?? '')));
            $address     = trim((string)($_POST['address'] ?? ''));
            $googleMap   = trim((string)($_POST['googleMap'] ?? ''));
            $website     = trim((string)($_POST['website'] ?? ''));
            $logo        = trim((string)($_POST['logo'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $rating      = (float)($_POST['rating'] ?? 0);

            // NEW: specialties posted as array of IDs
            $postedSpecs = $_POST['specialties'] ?? [];
            if (!is_array($postedSpecs)) $postedSpecs = [];
            $postedSpecs = array_values(array_unique(array_map('intval', $postedSpecs))); // unique ints

            $errors = [];
            if ($name === '') $errors['name'] = 'Name is required';
            if ($slug === '') $errors['slug'] = 'Slug is required';
            if ($rating < 0 || $rating > 5) $errors['rating'] = 'Rating must be between 0 and 5';

            // Slug uniqueness (excluding current record)
            $st = $conn->prepare('SELECT office_id FROM Office WHERE slug=? AND office_id<>? LIMIT 1');
            $st->bind_param('si', $slug, $id);
            $st->execute(); $st->store_result();
            if ($st->num_rows > 0) $errors['slug'] = 'Slug already in use';
            $st->close();

            if ($errors) {
                $office = self::fetchOffice($conn, $id) ?? [];
                return array_merge(
                    self::loadSpecialtiesData($conn, $id),
                    ['office' => $office, 'errors' => $errors, 'flash' => ['type' => 'error', 'msg' => 'Please fix the errors below']]
                );
            }

            // Save clinic basics
            $sql = 'UPDATE Office
                    SET name=?, slug=?, address=?, googleMap=?, website=?, logo=?, description=?, rating=?
                    WHERE office_id=?';
            $st = $conn->prepare($sql);
            $st->bind_param('sssssssdi', $name, $slug, $address, $googleMap, $website, $logo, $description, $rating, $id);
            $okBasics = $st->execute();
            $st->close();

            // Save specialties (uses existing pivot table Office_has_specialty)
            $okSpecs = true;
            $conn->begin_transaction();

            // 1) Clear current links
            $st = $conn->prepare('DELETE FROM Office_has_specialty WHERE office_id=?');
            $st->bind_param('i', $id);
            $okDel = $st->execute();
            $st->close();

            // 2) Filter to only existing specialty ids (avoid bad ids)
            if ($postedSpecs) {
                // build IN (?,?,?)
                $in = implode(',', array_fill(0, count($postedSpecs), '?'));
                $types = str_repeat('i', count($postedSpecs));
                $sql = "SELECT specialty_id FROM Medical_specialty WHERE specialty_id IN ($in)";
                $st = $conn->prepare($sql);
                $bind = [&$types];
                foreach ($postedSpecs as $k => $v) { $bind[] = &$postedSpecs[$k]; }
                call_user_func_array([$st, 'bind_param'], $bind);
                $st->execute();
                $valid = array_map('intval', array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'specialty_id'));
                $st->close();
            } else {
                $valid = [];
            }

            // 3) Bulk insert
            if ($okDel && $valid) {
                $pairs = implode(',', array_fill(0, count($valid), '(?,?)'));
                $types = str_repeat('ii', count($valid));
                $sql = "INSERT INTO Office_has_specialty (office_id, specialty_id) VALUES $pairs";
                $st = $conn->prepare($sql);
                $args = [];
                foreach ($valid as $sid) { $args[] = $id; $args[] = $sid; }
                // bind
                $bind = [&$types];
                foreach ($args as $i => $v) { $bind[] = &$args[$i]; }
                call_user_func_array([$st, 'bind_param'], $bind);
                $okIns = $st->execute();
                $st->close();
                $okSpecs = $okIns;
            }

            ($okBasics && $okDel && $okSpecs) ? $conn->commit() : $conn->rollback();

            $_SESSION['flash'] = ($okBasics && $okDel && $okSpecs)
                ? ['type' => 'success', 'msg' => 'Clinic updated.']
                : ['type' => 'error',   'msg' => 'Update failed.'];

            // PRG
            header('Location: ' . BASE_URL . 'index.php?page=clinic_edit&clinic=' . $id);
            exit;
        }

        // GET: show form
        $office = self::fetchOffice($conn, $id);
        if (!$office) {
            http_response_code(404);
            return ['errors' => ['global' => 'Clinic not found']];
        }

        return array_merge(
            ['office' => $office, 'flash' => $flash],
            self::loadSpecialtiesData($conn, $id)
        );
    }

    private static function fetchOffice(mysqli $conn, int $id): ?array
    {
        $st = $conn->prepare('SELECT office_id, name, slug, address, googleMap, website, logo, description, rating FROM Office WHERE office_id=?');
        $st->bind_param('i', $id);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();
        return $row ?: null;
    }

    /** Load all specialties + ids currently linked to this office (no schema changes). */
    private static function loadSpecialtiesData(mysqli $conn, int $officeId): array
    {
        // all specialties
        $rs = $conn->query("SELECT specialty_id, name, slug FROM Medical_specialty ORDER BY sort_order ASC, name ASC");
        $all = $rs ? $rs->fetch_all(MYSQLI_ASSOC) : [];

        // selected ids
        $st = $conn->prepare("SELECT specialty_id FROM Office_has_specialty WHERE office_id=?");
        $st->bind_param('i', $officeId);
        $st->execute();
        $sel = array_map('intval', array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'specialty_id'));
        $st->close();

        return [
            'all_specialties' => $all,
            'selected_specialty_ids' => $sel,
        ];
    }
}