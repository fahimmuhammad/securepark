<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/mailer.php';

if (session_status() === PHP_SESSION_NONE) session_start();
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$uid            = (int)$_SESSION['user_id'];
$slot_id        = (int)($_POST['slot_id']        ?? 0);
$vehicle_number = trim($_POST['vehicle_number']  ?? '');
$vehicle_type   = trim($_POST['vehicle_type']    ?? 'car');
$start_time     = trim($_POST['start_time']      ?? '');
$end_time       = trim($_POST['end_time']        ?? '');
$notes          = trim($_POST['notes']           ?? '');

if (!$slot_id || !$vehicle_number || !$start_time || !$end_time) {
    echo json_encode(['error' => 'All required fields must be filled.']);
    exit;
}

$allowed_types = ['car','motorcycle','suv','truck','van','ev'];
if (!in_array($vehicle_type, $allowed_types)) $vehicle_type = 'car';

$start = strtotime($start_time);
$end   = strtotime($end_time);

if (!$start || !$end) { echo json_encode(['error' => 'Invalid date/time format.']); exit; }
if ($end <= $start)   { echo json_encode(['error' => 'End time must be after start time.']); exit; }

$duration = round(($end - $start) / 3600, 2);
if ($duration < 0.5)  { echo json_encode(['error' => 'Minimum booking duration is 30 minutes.']); exit; }
if ($duration > 168)  { echo json_encode(['error' => 'Maximum booking duration is 7 days.']); exit; }

// Verify slot exists and is available
$stmt = $conn->prepare("SELECT id, hourly_rate, status FROM parking_slots WHERE id = ?");
$stmt->bind_param('i', $slot_id);
$stmt->execute();
$slot = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$slot)                        { echo json_encode(['error' => 'Slot not found.']);        exit; }
if ($slot['status'] !== 'available') { echo json_encode(['error' => 'Slot is not available.']); exit; }

// Check for overlapping bookings on same slot
$stmt = $conn->prepare("
    SELECT id FROM bookings
    WHERE slot_id = ? AND status NOT IN ('cancelled','completed')
    AND NOT (end_time <= ? OR start_time >= ?)
");
$stmt->bind_param('iss', $slot_id, $start_time, $end_time);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    echo json_encode(['error' => 'This slot is already booked for the selected time period.']);
    exit;
}
$stmt->close();

$amount   = round($duration * $slot['hourly_rate'], 2);
$ref      = generateBookingRef();
$start_dt = date('Y-m-d H:i:s', $start);
$end_dt   = date('Y-m-d H:i:s', $end);

$stmt = $conn->prepare("
    INSERT INTO bookings
        (booking_ref, user_id, slot_id, vehicle_number, vehicle_type, start_time, end_time, duration_hours, amount, status, payment_status, notes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', 'unpaid', ?)
");
$stmt->bind_param('siissssdds', $ref, $uid, $slot_id, $vehicle_number, $vehicle_type, $start_dt, $end_dt, $duration, $amount, $notes);

if ($stmt->execute()) {
    $new_bid = $conn->insert_id;
    $stmt->close();
    // Send confirmation email (non-blocking, silent fail)
    $uRow  = $conn->query("SELECT name, email FROM users WHERE id = $uid")->fetch_assoc();
    $slRow = $conn->query("SELECT slot_number FROM parking_slots WHERE id = $slot_id")->fetch_assoc();
    $zRow  = $conn->query("SELECT pz.name FROM parking_zones pz JOIN parking_slots ps ON ps.zone_id=pz.id WHERE ps.id = $slot_id")->fetch_assoc();
    if ($uRow && $slRow && $zRow) {
        $bMail = ['booking_ref'=>$ref,'start_time'=>$start_dt,'end_time'=>$end_dt,'duration_hours'=>$duration,'amount'=>$amount];
        spNotifyBookingCreated($uRow['email'], $uRow['name'], $bMail, $slRow['slot_number'], $zRow['name']);
    }
    echo json_encode(['success' => true, 'message' => "Booking confirmed! Reference: $ref", 'booking_ref' => $ref]);
} else {
    $stmt->close();
    echo json_encode(['error' => 'Booking failed. Please try again.']);
}
