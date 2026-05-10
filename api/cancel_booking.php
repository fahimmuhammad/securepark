<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/mailer.php';

requireLogin();
header('Content-Type: application/json');

$input      = json_decode(file_get_contents('php://input'), true) ?? [];
$booking_id = (int)($input['booking_id'] ?? 0);
$uid        = (int)$_SESSION['user_id'];

if (!$booking_id) { echo json_encode(['error' => 'Invalid booking ID.']); exit; }

// Verify ownership
$stmt = $conn->prepare("SELECT b.id, b.status, b.booking_ref, b.amount, u.name, u.email FROM bookings b JOIN users u ON b.user_id=u.id WHERE b.id = ? AND b.user_id = ?");
$stmt->bind_param('ii', $booking_id, $uid);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) { echo json_encode(['error' => 'Booking not found.']); exit; }
if (!in_array($booking['status'], ['pending','confirmed'])) {
    echo json_encode(['error' => 'Only pending or confirmed bookings can be cancelled.']);
    exit;
}

$stmt2 = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
$stmt2->bind_param('i', $booking_id);
if ($stmt2->execute()) {
    $stmt2->close();
    spNotifyBookingStatus($booking['email'], $booking['name'], ['booking_ref'=>$booking['booking_ref'],'amount'=>$booking['amount']], 'cancelled');
    echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully.']);
} else {
    $stmt2->close();
    echo json_encode(['error' => 'Failed to cancel booking.']);
}
