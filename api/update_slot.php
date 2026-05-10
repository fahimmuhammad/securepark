<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

requireAdmin();
header('Content-Type: application/json');

$input   = json_decode(file_get_contents('php://input'), true) ?? [];
$slot_id = (int)($input['slot_id'] ?? 0);
$status  = trim($input['status']   ?? '');

$allowed = ['available','occupied','reserved','maintenance'];
if (!$slot_id || !in_array($status, $allowed)) {
    echo json_encode(['error' => 'Invalid slot ID or status.']);
    exit;
}

$stmt = $conn->prepare("UPDATE parking_slots SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $slot_id);
if ($stmt->execute()) {
    $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Slot status updated.']);
} else {
    $stmt->close();
    echo json_encode(['error' => 'Failed to update slot.']);
}
