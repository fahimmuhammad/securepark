<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/mailer.php';

requireAdmin();
header('Content-Type: application/json');

$input      = json_decode(file_get_contents('php://input'), true) ?? [];
$booking_id = (int)($input['booking_id'] ?? 0);
$action     = trim($input['action']      ?? '');

$transitions = [
    'confirm'  => ['pending',   'confirmed'],
    'activate' => ['confirmed', 'active'],
    'complete' => ['active',    'completed'],
    'cancel'   => ['*',         'cancelled'],
];

if (!$booking_id || !isset($transitions[$action])) {
    echo json_encode(['error' => 'Invalid booking ID or action.']);
    exit;
}

$stmt = $conn->prepare("SELECT b.id, b.status, b.booking_ref, b.amount, u.name, u.email FROM bookings b JOIN users u ON b.user_id=u.id WHERE b.id = ?");
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) { echo json_encode(['error' => 'Booking not found.']); exit; }

[$req_from, $to_status] = $transitions[$action];
if ($req_from !== '*' && $booking['status'] !== $req_from) {
    echo json_encode(['error' => "Cannot $action a booking with status: {$booking['status']}."]);
    exit;
}

$stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
$stmt->bind_param('si', $to_status, $booking_id);
if ($stmt->execute()) {
    $stmt->close();
    spNotifyBookingStatus($booking['email'], $booking['name'], ['booking_ref'=>$booking['booking_ref'],'amount'=>$booking['amount']], $to_status);
    echo json_encode(['success' => true, 'message' => "Booking $action action applied."]);
} else {
    $stmt->close();
    echo json_encode(['error' => 'Action failed. Please try again.']);
}
