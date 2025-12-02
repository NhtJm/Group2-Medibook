<?php
// app/controller/ClinicSetupController.php
require_once __DIR__ . '/../db.php';

class ClinicSetupController
{
    public static function index(): array
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            $conn = Database::get_instance();
        }

        $user = current_user();
        if (!$user) {
            header('Location: ' . BASE_URL . 'index.php?page=login');
            exit;
        }

        $userId = (int) ($user['user_id'] ?? $user['id'] ?? 0);
        if ($userId <= 0) {
            $userId = (int) ($_SESSION['user_id'] ?? ($_SESSION['user']['user_id'] ?? 0));
        }
        
        if ($userId <= 0) {
            header('Location: ' . BASE_URL . 'index.php?page=login');
            exit;
        }

        $errors = [];
        $success = false;

        $existingOffice = self::getOfficeByUserId($conn, $userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clinicName = trim($_POST['clinic_name'] ?? '');
            $clinicPhone = trim($_POST['clinic_phone'] ?? '');
            $clinicAddress = trim($_POST['clinic_address'] ?? '');
            $clinicDescription = trim($_POST['clinic_description'] ?? '');

            if (empty($clinicName)) {
                $errors[] = 'Clinic name is required.';
            }
            if (empty($clinicPhone)) {
                $errors[] = 'Phone number is required.';
            }
            if (empty($clinicAddress)) {
                $errors[] = 'Address is required.';
            }

            $logoPath = $existingOffice['logo'] ?? null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = self::handleLogoUpload($_FILES['logo']);
                if ($uploadResult['success']) {
                    $logoPath = $uploadResult['path'];
                } else {
                    $errors[] = $uploadResult['error'];
                }
            }

            if (empty($errors)) {
                if ($existingOffice) {
                    $result = self::updateOffice($conn, $existingOffice['office_id'], [
                        'name' => $clinicName,
                        'address' => $clinicAddress,
                        'description' => $clinicDescription,
                        'logo' => $logoPath,
                        'phone' => $clinicPhone
                    ]);
                } else {
                    $result = self::createOffice($conn, $userId, [
                        'name' => $clinicName,
                        'address' => $clinicAddress,
                        'description' => $clinicDescription,
                        'logo' => $logoPath,
                        'phone' => $clinicPhone
                    ]);
                }

                if ($result['success']) {
                    header('Location: ' . BASE_URL . 'index.php?page=office_dashboard&success=1');
                    exit;
                } else {
                    $errors[] = $result['error'];
                }
            }
        }

        return [
            'office' => $existingOffice,
            'errors' => $errors,
            'success' => $success
        ];
    }

    private static function getOfficeByUserId(mysqli $conn, int $userId): ?array
    {
        $sql = "SELECT o.*, op.phone 
                FROM Office o 
                LEFT JOIN Office_phone op ON op.office_id = o.office_id
                WHERE o.user_id = ? 
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    private static function createOffice(mysqli $conn, int $userId, array $data): array
    {
        $conn->begin_transaction();

        try {
            $slug = self::generateUniqueSlug($conn, $data['name']);
            
            $sql = "INSERT INTO Office (user_id, name, slug, address, description, logo, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssss", 
                $userId, 
                $data['name'],
                $slug,
                $data['address'], 
                $data['description'], 
                $data['logo']
            );
            $stmt->execute();
            $officeId = $conn->insert_id;
            $stmt->close();

            if (!empty($data['phone'])) {
                $sqlPhone = "INSERT INTO Office_phone (office_id, phone) VALUES (?, ?)";
                $stmtPhone = $conn->prepare($sqlPhone);
                $stmtPhone->bind_param("is", $officeId, $data['phone']);
                $stmtPhone->execute();
                $stmtPhone->close();
            }

            $conn->commit();

            return ['success' => true, 'office_id' => $officeId];
        } catch (Exception $e) {
            $conn->rollback();
            return ['success' => false, 'error' => 'Failed to create clinic: ' . $e->getMessage()];
        }
    }

    private static function generateUniqueSlug(mysqli $conn, string $name): string
    {
        $slug = self::slugify($name);
        
        $originalSlug = $slug;
        $counter = 1;
        
        while (self::slugExists($conn, $slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    private static function slugify(string $text): string
    {
        $text = preg_replace('/[^a-zA-Z0-9]+/', '-', $text);
        $text = strtolower($text);
        $text = trim($text, '-');
        $text = substr($text, 0, 100);
        
        return $text ?: 'clinic-' . time();
    }

    private static function slugExists(mysqli $conn, string $slug): bool
    {
        $sql = "SELECT office_id FROM Office WHERE slug = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    private static function updateOffice(mysqli $conn, int $officeId, array $data): array
    {
        $conn->begin_transaction();

        try {
            $sql = "UPDATE Office SET name = ?, address = ?, description = ?, logo = ? WHERE office_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", 
                $data['name'], 
                $data['address'], 
                $data['description'], 
                $data['logo'],
                $officeId
            );
            $stmt->execute();
            $stmt->close();

            if (!empty($data['phone'])) {
                $sqlDel = "DELETE FROM Office_phone WHERE office_id = ?";
                $stmtDel = $conn->prepare($sqlDel);
                $stmtDel->bind_param("i", $officeId);
                $stmtDel->execute();
                $stmtDel->close();

                $sqlPhone = "INSERT INTO Office_phone (office_id, phone) VALUES (?, ?)";
                $stmtPhone = $conn->prepare($sqlPhone);
                $stmtPhone->bind_param("is", $officeId, $data['phone']);
                $stmtPhone->execute();
                $stmtPhone->close();
            }

            $conn->commit();

            return ['success' => true];
        } catch (Exception $e) {
            $conn->rollback();
            return ['success' => false, 'error' => 'Failed to update clinic: ' . $e->getMessage()];
        }
    }

    private static function handleLogoUpload(array $file): array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
        }

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File too large. Maximum size is 5MB.'];
        }

        $uploadDir = __DIR__ . '/../../public/uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'clinic_' . uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'path' => 'uploads/logos/' . $filename];
        }

        return ['success' => false, 'error' => 'Failed to upload file.'];
    }
}
