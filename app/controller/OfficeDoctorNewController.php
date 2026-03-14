<?php
// app/controller/OfficeDoctorNewController.php
if (session_status() === PHP_SESSION_NONE) session_start();

class OfficeDoctorNewController
{
    public static function index(): array
    {
        $u = $_SESSION['user'] ?? null;
        $role = $u['role'] ?? '';
        $officeId = (int)($u['profile_id'] ?? 0);

        if (!$u || $officeId <= 0 || !in_array($role, ['office','admin'], true)) {
            header('Location: ' . BASE_URL . 'index.php?page=login'); exit;
        }

        require_once __DIR__ . '/../db.php';
        global $conn;
        if (!($conn instanceof mysqli) && class_exists('Database')) {
            $conn = Database::get_instance();
        }

        // Load specialties for the <select>
        $specialties = [];
        if ($res = $conn->query("SELECT specialty_id, name FROM Medical_specialty ORDER BY name")) {
            while ($row = $res->fetch_assoc()) $specialties[] = $row;
        }

        $doctorId = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
        $errors = [];
        $old = [];

        // If editing (GET), preload the row
        if ($doctorId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $st = $conn->prepare("SELECT doctor_name, email, photo, degree, graduate, specialty_id
                                  FROM Doctor WHERE doctor_id=? AND office_id=? LIMIT 1");
            $st->bind_param('ii', $doctorId, $officeId);
            $st->execute();
            $r = $st->get_result()->fetch_assoc();
            $st->close();
            if ($r) {
                $old = [
                    'doctor_name' => $r['doctor_name'] ?? '',
                    'email'       => $r['email'] ?? '',
                    'photo'       => $r['photo'] ?? '',
                    'degree'      => $r['degree'] ?? '',
                    'graduate'    => $r['graduate'] ?? '',
                    'specialty_id'=> (int)($r['specialty_id'] ?? 0),
                ];
            } else {
                $errors[] = 'Doctor not found or not in your clinic.';
            }
        }

        // Handle submit (both create and update)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'doctor_name' => trim($_POST['doctor_name'] ?? ''),
                'email'       => trim($_POST['email'] ?? ''),
                'photo'       => trim($_POST['photo'] ?? ''),
                'degree'      => trim($_POST['degree'] ?? ''),
                'graduate'    => trim($_POST['graduate'] ?? ''),
                'specialty_id'=> (int)($_POST['specialty_id'] ?? 0),
            ];

            if ($old['doctor_name'] === '') $errors[] = 'Full name is required.';
            if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
            if ($old['specialty_id'] <= 0) $errors[] = 'Specialty is required.';

            // if editing, verify the doctor belongs to this office
            if (!$errors && $doctorId > 0) {
                $chk = $conn->prepare("SELECT 1 FROM Doctor WHERE doctor_id=? AND office_id=?");
                $chk->bind_param('ii', $doctorId, $officeId);
                $chk->execute();
                $exist = $chk->get_result()->fetch_row();
                $chk->close();
                if (!$exist) $errors[] = 'Doctor not found or not in your clinic.';
            }

            if (!$errors) {
                if ($doctorId > 0) {
                    // UPDATE
                    $sql = "UPDATE Doctor
                               SET doctor_name=?, email=?, photo=?, degree=?, graduate=?, specialty_id=?
                             WHERE doctor_id=? AND office_id=?";
                    $st = $conn->prepare($sql);
                    $st->bind_param(
                        'sssssiii',
                        $old['doctor_name'], $old['email'], $old['photo'],
                        $old['degree'], $old['graduate'], $old['specialty_id'],
                        $doctorId, $officeId
                    );
                    $ok = $st->execute();
                    $dup = ($st->errno === 1062);
                    $st->close();
                    if (!$ok) {
                        $errors[] = $dup ? 'This email already exists for another doctor.'
                                         : 'Update failed. Please try again.';
                    }
                } else {
                    // INSERT
                    $sql = "INSERT INTO Doctor (office_id, doctor_name, email, photo, degree, graduate, specialty_id)
                            VALUES (?,?,?,?,?,?,?)";
                    $st = $conn->prepare($sql);
                    $st->bind_param(
                        'isssssi',
                        $officeId, $old['doctor_name'], $old['email'],
                        $old['photo'], $old['degree'], $old['graduate'], $old['specialty_id']
                    );
                    $ok = $st->execute();
                    $dup = ($st->errno === 1062);
                    $st->close();
                    if (!$ok) {
                        $errors[] = $dup ? 'This email already exists for another doctor.'
                                         : 'Create failed. Please try again.';
                    }
                }

                if (!$errors) {
                    header('Location: ' . BASE_URL . 'index.php?page=office_dashboard');
                    exit;
                }
            }
        }

        return [
            'specialties' => $specialties,
            'errors'      => $errors,
            'old'         => $old,
            'is_edit'     => $doctorId > 0,
            'doctor_id'   => $doctorId,
        ];
    }
}