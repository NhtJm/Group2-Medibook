<?php
// app/controller/AdminUserController.php
require_once __DIR__ . '/../db.php';

class AdminUserController
{
    public static function index(): array
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        $uid = (int) ($_GET['uid'] ?? 0);
        if ($uid <= 0) {
            http_response_code(404);
            return ['error' => 'Missing or invalid user id'];
        }

        // --- POST: save edits ---

        // --- POST: save edits ---
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $csrf = (string) ($_POST['csrf'] ?? '');
            if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Security token invalid.'];
                header('Location: ' . BASE_URL . 'index.php?page=admin_user&uid=' . $uid);
                exit;
            }

            // Pull current created_at AND role so we can enforce role rules
            $st = $conn->prepare("SELECT created_at, role FROM Users WHERE user_id=?");
            $st->bind_param('i', $uid);
            $st->execute();
            $curRow = $st->get_result()->fetch_assoc();
            $st->close();
            if (!$curRow) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'User not found.'];
                header('Location: ' . BASE_URL . 'index.php?page=admin_user&uid=' . $uid);
                exit;
            }

            $errors = []; // init BEFORE pushing to it

            $currentRole = (string) ($curRow['role'] ?? 'patient');
            $canChangeRole = in_array($currentRole, ['admin', 'webstaff'], true);

            // Collect form fields
            $email = trim((string) ($_POST['email'] ?? ''));
            $uname = trim((string) ($_POST['username'] ?? ''));
            $fname = trim((string) ($_POST['full_name'] ?? ''));
            $postedRole = trim((string) ($_POST['role'] ?? $currentRole));
            $oauth_p = trim((string) ($_POST['oauth_provider'] ?? ''));
            $oauth_s = trim((string) ($_POST['oauth_sub'] ?? ''));
            $pic = trim((string) ($_POST['picture_url'] ?? ''));
            $newpass = (string) ($_POST['new_password'] ?? '');
            $created_in = trim((string) ($_POST['created_at'] ?? ''));

            if ($email === '')
                $errors[] = 'Email is required.';
            if ($uname === '')
                $errors[] = 'Username is required.';

            // Decide the effective role
            if ($canChangeRole) {
                if (!in_array($postedRole, ['admin', 'webstaff'], true)) {
                    $errors[] = 'Only Admin/Webstaff roles are allowed here.';
                }
                $effectiveRole = $postedRole;
            } else {
                // patient/office may NOT change role
                $effectiveRole = $currentRole;
            }

            // Normalize created_at
            if ($created_in !== '') {
                $ts = strtotime($created_in);
                if ($ts === false)
                    $errors[] = 'Invalid "created at" datetime.';
                $created = ($ts === false) ? $curRow['created_at'] : date('Y-m-d H:i:s', $ts);
            } else {
                $created = $curRow['created_at'];
            }

            if ($newpass !== '' && strlen($newpass) < 6) {
                $errors[] = 'Password must be ≥ 6 characters.';
            }

            if ($errors) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => implode(' ', $errors)];
                header('Location: ' . BASE_URL . 'index.php?page=admin_user&uid=' . $uid);
                exit;
            }

            // Update non-password fields
            $sql = 'UPDATE Users
            SET email=?,
                username=?,
                full_name=?,
                role=?,
                oauth_provider = NULLIF(?, ""),
                oauth_sub      = NULLIF(?, ""),
                picture_url=?,
                created_at=?
            WHERE user_id=?';
            $st = $conn->prepare($sql);
            $st->bind_param('ssssssssi', $email, $uname, $fname, $effectiveRole, $oauth_p, $oauth_s, $pic, $created, $uid);
            $ok1 = $st->execute();
            $errno1 = $st->errno;
            $st->close();

            // Update password if provided
            $ok2 = true;
            $errno2 = 0;
            if ($newpass !== '') {
                $hash = password_hash($newpass, PASSWORD_DEFAULT);
                $st = $conn->prepare('UPDATE Users SET password_hash=? WHERE user_id=?');
                $st->bind_param('si', $hash, $uid);
                $ok2 = $st->execute();
                $errno2 = $st->errno;
                $st->close();
            }

            // ---- Sync role-specific tables ----
            if ($ok1 && $canChangeRole) {
                if ($effectiveRole === 'admin') {
                    $st = $conn->prepare("INSERT IGNORE INTO Admin (user_id) VALUES (?)");
                    $st->bind_param('i', $uid);
                    $st->execute();
                    $st->close();

                    $st = $conn->prepare("DELETE FROM Web_staff WHERE user_id=?");
                    $st->bind_param('i', $uid);
                    $st->execute();
                    $st->close();

                } elseif ($effectiveRole === 'webstaff') {
                    $st = $conn->prepare("SELECT 1 FROM Web_staff WHERE user_id=? LIMIT 1");
                    $st->bind_param('i', $uid);
                    $st->execute();
                    $has = (bool) $st->get_result()->fetch_column();
                    $st->close();

                    if (!$has) {
                        $position = 'support';
                        $status = 'online';
                        $st = $conn->prepare("INSERT INTO Web_staff (user_id, position, status) VALUES (?, ?, ?)");
                        $st->bind_param('iss', $uid, $position, $status);
                        $st->execute();
                        $st->close();
                    }

                    $st = $conn->prepare("DELETE FROM Admin WHERE user_id=?");
                    $st->bind_param('i', $uid);
                    $st->execute();
                    $st->close();
                }
            } elseif ($ok1 && !$canChangeRole) {
                // Optional cleanup for locked roles
                $st = $conn->prepare("DELETE FROM Admin WHERE user_id=?");
                $st->bind_param('i', $uid);
                $st->execute();
                $st->close();

                $st = $conn->prepare("DELETE FROM Web_staff WHERE user_id=?");
                $st->bind_param('i', $uid);
                $st->execute();
                $st->close();
            }

            // Friendly duplicate key message
            if (($errno1 === 1062) || ($errno2 === 1062)) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Email or username already exists.'];
            } else {
                $_SESSION['flash'] = ($ok1 && $ok2)
                    ? ['type' => 'success', 'msg' => 'User updated successfully.']
                    : ['type' => 'error', 'msg' => 'Update failed.'];
            }

            header('Location: ' . BASE_URL . 'index.php?page=admin_user&uid=' . $uid);
            exit;
        }

        // --- GET: fetch row for display ---
        $st = $conn->prepare("SELECT * FROM Users WHERE user_id=? LIMIT 1");
        $st->bind_param('i', $uid);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();

        if (!$row) {
            http_response_code(404);
            return ['error' => 'User not found'];
        }

        // Mask secrets for "all"
        $hidden = ['password', 'password_hash', 'pwd', 'pass', 'reset_token', 'verify_token', 'two_factor_secret', 'otp', 'api_key', 'api_token', 'csrf', 'csrf_token', 'salt'];
        $all = $row;
        foreach ($hidden as $key) {
            if (array_key_exists($key, $all)) {
                $len = strlen((string) $all[$key]);
                $all[$key] = $len ? str_repeat('•', min($len, 12)) : '—';
            }
        }

        $auth = (!empty($row['oauth_provider'])) ? ('OAuth: ' . $row['oauth_provider'])
            : (!empty($row['password_hash']) ? 'Password' : '—');

        // Flash (PRG)
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return [
            'user' => $row,
            'all' => $all,
            'auth_label' => $auth,
            'flash' => $flash,
        ];
    }
}