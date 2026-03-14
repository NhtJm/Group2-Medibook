<?php
require_once __DIR__ . '/../db.php';

class AdminDoctorEditController
{
    public static function index(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        global $conn; if (!isset($conn) || !($conn instanceof mysqli)) $conn = Database::get_instance();

        $doctorId = (int)($_GET['doctor'] ?? 0);
        if ($doctorId <= 0) { http_response_code(404); return ['error' => 'Missing or invalid doctor id']; }

        // dropdowns
        $offs  = $conn->query("SELECT office_id, name FROM Office ORDER BY name")->fetch_all(MYSQLI_ASSOC);
        $specs = $conn->query("SELECT specialty_id, name FROM Medical_specialty ORDER BY name")->fetch_all(MYSQLI_ASSOC);

        // Save
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $name   = trim((string)($_POST['name'] ?? ''));
            $email  = trim((string)($_POST['email'] ?? ''));
            $photo  = trim((string)($_POST['photo'] ?? '')); // may be ''
            $degree = trim((string)($_POST['degree'] ?? ''));
            $grad   = trim((string)($_POST['graduate'] ?? ''));
            $oid    = (int)($_POST['office_id'] ?? 0);
            $sid    = (int)($_POST['specialty_id'] ?? 0);
            $clearPhoto = !empty($_POST['clear_photo']);

            $err = [];
            if ($name==='')   $err[]='Full name is required.';
            if ($email==='')  $err[]='Email is required.';
            if ($oid<=0)      $err[]='Office is required.';

            if ($err) {
                $_SESSION['flash'] = ['type'=>'error','msg'=>implode(' ', $err)];
            } else {
                if ($clearPhoto) {
                    $sql = "UPDATE Doctor SET doctor_name=?, email=?, photo=NULL, degree=?, graduate=?, specialty_id=?, office_id=? WHERE doctor_id=?";
                    $st  = $conn->prepare($sql);
                    $st->bind_param('ssssiii', $name, $email, $degree, $grad, $sid, $oid, $doctorId);
                } else {
                    $sql = "UPDATE Doctor
                              SET doctor_name=?, email=?,
                                  photo = COALESCE(NULLIF(?, ''), photo),
                                  degree=?, graduate=?, specialty_id=?, office_id=?
                            WHERE doctor_id=?";
                    $st  = $conn->prepare($sql);
                    $st->bind_param('sssssiii', $name, $email, $photo, $degree, $grad, $sid, $oid, $doctorId);
                }
                $st->execute(); $st->close();

                $_SESSION['flash'] = ['type'=>'success','msg'=>'Saved.'];
                header('Location: '.BASE_URL.'index.php?page=admin_doctor_edit&doctor='.$doctorId);
                exit;
            }
        }

        // load doctor
        $st = $conn->prepare("
            SELECT d.doctor_id, d.office_id, d.doctor_name, d.email, d.photo, d.degree, d.graduate, d.specialty_id,
                   o.name AS office_name
            FROM Doctor d
            JOIN Office o ON o.office_id = d.office_id
            WHERE d.doctor_id = ?
            LIMIT 1
        ");
        $st->bind_param('i', $doctorId);
        $st->execute();
        $doc = $st->get_result()->fetch_assoc();
        $st->close();

        if (!$doc) { http_response_code(404); return ['error'=>'Doctor not found']; }

        $flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

        return ['doctor'=>$doc, 'offices'=>$offs, 'specialties'=>$specs, 'flash'=>$flash];
    }
}