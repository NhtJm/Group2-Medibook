<?php
// app/controller/BookingController.php
require_once __DIR__ . '/../db.php';

class BookingController {
  public static function confirm(): array {
    
    // must be logged in (router already guards 'confirm')
    require_auth_or_redirect();

    global $conn;
    if (!isset($conn) || !($conn instanceof mysqli)) {
      $conn = Database::get_instance();
    }

    $slotId = (int)($_POST['slot'] ?? 0);
    if ($slotId <= 0) return ['error' => 'Missing time slot.'];

    // Who is booking?
    $u   = current_user();
    $uid = (int)($u['user_id'] ?? 0);
    if ($uid <= 0) { header('Location: ' . BASE_URL . 'index.php?page=login'); exit; }

    // Resolve patient_id (create one if the user isn’t a patient yet)
    $pid = null;
    $st = $conn->prepare("SELECT patient_id FROM Patient WHERE user_id = ? LIMIT 1");
    $st->bind_param('i', $uid);
    $st->execute();
    $st->bind_result($pidTmp);
    if ($st->fetch()) $pid = (int)$pidTmp;
    $st->close();

    if (!$pid) {
      $st = $conn->prepare("INSERT INTO Patient (user_id, status) VALUES (?, 'active')");
      $st->bind_param('i', $uid);
      if (!$st->execute()) return ['error' => 'Could not create patient profile.'];
      $pid = (int)$conn->insert_id;
      $st->close();
    }

    // Validate slot: exists, available, not in the past
    $st = $conn->prepare(
      "SELECT slot_id, start_time
         FROM Appointment_slot
        WHERE slot_id = ? AND status = 'available' AND start_time > NOW()
        LIMIT 1"
    );
    $st->bind_param('i', $slotId);
    $st->execute();
    $slot = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$slot) return ['error' => 'This slot is no longer available.'];

    // Book it
    $conn->begin_transaction();
    try {
      $st = $conn->prepare(
        "INSERT INTO Appointment (patient_id, slot_id, status, booked_at)
         VALUES (?, ?, 'booked', NOW())"
      );
      $st->bind_param('ii', $pid, $slotId);
      if (!$st->execute()) throw new Exception($st->error);
      $st->close();

      $st = $conn->prepare(
        "UPDATE Appointment_slot
            SET status = 'booked'
          WHERE slot_id = ? AND status = 'available'"
      );
      $st->bind_param('i', $slotId);
      if (!$st->execute() || $st->affected_rows !== 1) throw new Exception('Slot taken.');
      $st->close();

      $conn->commit();

      header('Location: ' . BASE_URL . 'index.php?page=appointments&ok=1');
      exit;
    } catch (Throwable $e) {
      $conn->rollback();
      return ['error' => 'Could not book this slot. Please pick another one.'];
    }
  }
}