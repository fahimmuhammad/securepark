<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

$res   = $conn->query("SELECT id, status FROM parking_slots ORDER BY id");
$slots = [];
while ($row = $res->fetch_assoc()) {
    $slots[(int)$row['id']] = $row['status'];
}
echo json_encode(['slots' => $slots]);
