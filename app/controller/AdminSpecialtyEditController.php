<?php
// app/controller/AdminSpecialtyEditController.php
require_once __DIR__ . '/../db.php';

class AdminSpecialtyEditController
{
    public static function index(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) $conn = Database::get_instance();

        $id = (int)($_GET['sid'] ?? ($_GET['specialty'] ?? 0));
        $errors = [];
        $flash  = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf = (string)($_POST['csrf'] ?? '');
            if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
                $errors['csrf'] = 'Invalid session, please reload.';
            } else {
                $name  = trim((string)($_POST['name'] ?? ''));
                $slug  = trim((string)($_POST['slug'] ?? ''));
                $blurb = trim((string)($_POST['blurb'] ?? ''));
                $desc  = trim((string)($_POST['description'] ?? ''));
                $img   = trim((string)($_POST['image_url'] ?? ''));
                $feat  = isset($_POST['is_featured']) ? 1 : 0;
                $sort  = (int)($_POST['sort_order'] ?? 0);

                if ($name === '') $errors['name'] = 'Name is required.';
                if ($slug === '') $errors['slug'] = 'Slug is required.';

                if (!$errors) {
                    if ($id > 0) {
                        $st = $conn->prepare("UPDATE Medical_specialty
                            SET name=?, slug=?, blurb=?, description=?, image_url=?, is_featured=?, sort_order=?
                            WHERE specialty_id=?");
                        $st->bind_param('ssssssii', $name, $slug, $blurb, $desc, $img, $feat, $sort, $id);
                        $ok = $st->execute(); $st->close();
                        $flash = ['type'=>$ok?'success':'error','msg'=>$ok?'Saved':'Save failed'];
                    } else {
                        $st = $conn->prepare("INSERT INTO Medical_specialty
                            (name, slug, blurb, description, image_url, is_featured, sort_order)
                            VALUES (?,?,?,?,?,?,?)");
                        $st->bind_param('ssssssi', $name, $slug, $blurb, $desc, $img, $feat, $sort);
                        $ok = $st->execute();
                        $newId = $ok ? $st->insert_id : 0; $st->close();
                        if ($ok) {
                            header('Location: ' . BASE_URL . 'index.php?page=admin_specialty_edit&sid=' . $newId);
                            exit;
                        }
                        $flash = ['type'=>'error','msg'=>'Create failed'];
                    }
                }
            }
        }

        // load record (existing or empty)
        if ($id > 0) {
            $st = $conn->prepare("SELECT * FROM Medical_specialty WHERE specialty_id=?");
            $st->bind_param('i', $id);
            $st->execute();
            $rec = $st->get_result()->fetch_assoc();
            $st->close();
        } else {
            $rec = [
                'specialty_id'=>0, 'name'=>'', 'slug'=>'', 'blurb'=>'', 'description'=>'',
                'image_url'=>'', 'is_featured'=>0, 'sort_order'=>0
            ];
        }

        return ['specialty'=>$rec, 'errors'=>$errors, 'flash'=>$flash];
    }
}