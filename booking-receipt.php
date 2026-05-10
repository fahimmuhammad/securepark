<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
requireLogin();

$ref  = trim($_GET['ref'] ?? '');
$user = getCurrentUser();

if (!$ref) { header('Location: /securepark/my-bookings.php'); exit; }

// Fetch booking — user can view own; admin can view any
if ($user['role'] === 'admin') {
    $stmt = $conn->prepare("
        SELECT b.*, u.name user_name, u.email user_email, u.phone user_phone,
               ps.slot_number, ps.slot_type, ps.hourly_rate,
               pz.name zone_name, pz.floor zone_floor
        FROM bookings b
        JOIN users u        ON b.user_id  = u.id
        JOIN parking_slots ps ON b.slot_id = ps.id
        JOIN parking_zones pz ON ps.zone_id = pz.id
        WHERE b.booking_ref = ?
    ");
    $stmt->bind_param('s', $ref);
} else {
    $uid = (int)$user['id'];
    $stmt = $conn->prepare("
        SELECT b.*, u.name user_name, u.email user_email, u.phone user_phone,
               ps.slot_number, ps.slot_type, ps.hourly_rate,
               pz.name zone_name, pz.floor zone_floor
        FROM bookings b
        JOIN users u        ON b.user_id  = u.id
        JOIN parking_slots ps ON b.slot_id = ps.id
        JOIN parking_zones pz ON ps.zone_id = pz.id
        WHERE b.booking_ref = ? AND b.user_id = ?
    ");
    $stmt->bind_param('si', $ref, $uid);
}
$stmt->execute();
$b = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$b) { header('Location: /securepark/my-bookings.php'); exit; }

$statusColors = [
    'pending'   => '#f59e0b',
    'confirmed' => '#06b6d4',
    'active'    => '#10b981',
    'completed' => '#7c3aed',
    'cancelled' => '#ef4444',
];
$sc = $statusColors[$b['status']] ?? '#94a3b8';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receipt <?= htmlspecialchars($b['booking_ref']) ?> — SecurePark</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/securepark/assets/css/style.css">
  <style>
    .receipt-wrap { max-width: 680px; margin: 0 auto; padding: 32px 16px; }
    .receipt-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; }
    .receipt-header { background: var(--gradient); padding: 32px 40px; text-align: center; color: #fff; }
    .receipt-header .brand { font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.5px; }
    .receipt-header .sub { font-size: 0.88rem; opacity: 0.8; margin-top: 4px; }
    .receipt-ref { background: rgba(0,0,0,0.25); border-radius: 10px; padding: 14px 24px; margin-top: 20px; display: inline-block; }
    .receipt-ref .lbl { font-size: 0.72rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 1px; }
    .receipt-ref .val { font-size: 1.15rem; font-weight: 700; font-family: 'Poppins', sans-serif; letter-spacing: 1px; }
    .receipt-body { padding: 32px 40px; }
    .r-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border); font-size: 0.88rem; }
    .r-row:last-child { border: none; }
    .r-lbl { color: var(--text-muted); display: flex; align-items: center; gap: 8px; }
    .r-val { font-weight: 600; color: var(--text-primary); text-align: right; }
    .qr-section { text-align: center; padding: 28px 40px; border-top: 1px dashed var(--border); }
    .qr-box { background: #fff; border-radius: 12px; display: inline-flex; padding: 16px; margin-bottom: 10px; }
    .receipt-footer { padding: 20px 40px; background: var(--surface); text-align: center; }
    .total-row { background: rgba(124,58,237,0.08); border-radius: var(--radius-md); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; margin: 16px 0; }
    @media print {
      .no-print { display: none !important; }
      body { background: #fff !important; }
      .receipt-card { border: 1px solid #ddd !important; box-shadow: none !important; }
      .receipt-body { color: #111 !important; }
      .r-lbl { color: #555 !important; }
      .r-val { color: #111 !important; }
    }
    @media (max-width: 576px) {
      .receipt-body, .qr-section, .receipt-footer, .receipt-header { padding-left: 20px; padding-right: 20px; }
    }
  </style>
</head>
<body>
<div class="receipt-wrap">

  <!-- Back button -->
  <div class="d-flex align-items-center justify-content-between mb-4 no-print">
    <a href="/securepark/my-bookings.php" class="btn-outline-custom btn-sm-custom" style="padding:8px 16px;font-size:0.82rem">
      <i class="fas fa-arrow-left me-1"></i> Back
    </a>
    <button onclick="window.print()" class="btn-primary-custom btn-sm-custom" style="padding:8px 20px;font-size:0.82rem">
      <i class="fas fa-print me-1"></i> Print Receipt
    </button>
  </div>

  <div class="receipt-card">
    <!-- Header -->
    <div class="receipt-header">
      <div class="brand"><i class="fas fa-shield-halved me-2"></i>SecurePark</div>
      <div class="sub">Smart Parking — Booking Receipt</div>
      <div class="receipt-ref">
        <div class="lbl">Booking Reference</div>
        <div class="val"><?= htmlspecialchars($b['booking_ref']) ?></div>
      </div>
    </div>

    <!-- Body -->
    <div class="receipt-body">
      <!-- Status Badge -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div style="font-size:0.82rem;color:var(--text-muted)">
          <i class="fas fa-calendar-check me-1"></i>
          Issued <?= date('M d, Y \a\t h:i A', strtotime($b['created_at'])) ?>
        </div>
        <span style="background:<?= $sc ?>22;color:<?= $sc ?>;padding:5px 14px;border-radius:50px;font-size:0.8rem;font-weight:600;border:1px solid <?= $sc ?>44">
          <?= ucfirst($b['status']) ?>
        </span>
      </div>

      <!-- Customer Info -->
      <div style="margin-bottom:8px;font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Customer</div>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-user"></i>Name</span>
        <span class="r-val"><?= htmlspecialchars($b['user_name']) ?></span>
      </div>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-envelope"></i>Email</span>
        <span class="r-val"><?= htmlspecialchars($b['user_email']) ?></span>
      </div>
      <?php if ($b['user_phone']): ?>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-phone"></i>Phone</span>
        <span class="r-val"><?= htmlspecialchars($b['user_phone']) ?></span>
      </div>
      <?php endif; ?>

      <!-- Parking Info -->
      <div style="margin:20px 0 8px;font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Parking Details</div>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-square-parking"></i>Slot</span>
        <span class="r-val"><?= htmlspecialchars($b['slot_number']) ?></span>
      </div>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-layer-group"></i>Zone / Floor</span>
        <span class="r-val"><?= htmlspecialchars($b['zone_name']) ?> · <?= htmlspecialchars($b['zone_floor']) ?></span>
      </div>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-tag"></i>Slot Type</span>
        <span class="r-val"><?= ucfirst($b['slot_type']) ?></span>
      </div>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-car"></i>Vehicle</span>
        <span class="r-val"><?= htmlspecialchars(strtoupper($b['vehicle_number'])) ?> · <?= ucfirst($b['vehicle_type']) ?></span>
      </div>

      <!-- Time Info -->
      <div style="margin:20px 0 8px;font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Schedule</div>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-calendar-arrow-down"></i>Check-in</span>
        <span class="r-val"><?= date('M d, Y', strtotime($b['start_time'])) ?> · <?= date('h:i A', strtotime($b['start_time'])) ?></span>
      </div>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-calendar-arrow-up"></i>Check-out</span>
        <span class="r-val"><?= date('M d, Y', strtotime($b['end_time'])) ?> · <?= date('h:i A', strtotime($b['end_time'])) ?></span>
      </div>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-clock"></i>Duration</span>
        <span class="r-val"><?= number_format($b['duration_hours'], 1) ?> hour<?= $b['duration_hours'] != 1 ? 's' : '' ?></span>
      </div>

      <!-- Payment -->
      <div style="margin:20px 0 8px;font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Payment</div>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-coins"></i>Rate</span>
        <span class="r-val">$<?= number_format($b['hourly_rate'], 2) ?>/hr</span>
      </div>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-receipt"></i>Payment Status</span>
        <span class="r-val"><?= ucfirst($b['payment_status'] ?? 'unpaid') ?></span>
      </div>
      <?php if ($b['notes']): ?>
      <div class="r-row">
        <span class="r-lbl"><i class="fas fa-note-sticky"></i>Notes</span>
        <span class="r-val" style="max-width:60%;word-break:break-word"><?= htmlspecialchars($b['notes']) ?></span>
      </div>
      <?php endif; ?>

      <!-- Total -->
      <div class="total-row">
        <span style="font-size:0.95rem;font-weight:600"><i class="fas fa-dollar-sign me-2" style="color:var(--primary-light)"></i>Total Amount</span>
        <span style="font-size:1.4rem;font-weight:800;font-family:'Poppins',sans-serif;color:var(--primary-light)">$<?= number_format($b['amount'], 2) ?></span>
      </div>
    </div>

    <!-- QR Code Section -->
    <div class="qr-section">
      <div style="font-size:0.82rem;color:var(--text-muted);margin-bottom:16px">
        <i class="fas fa-qrcode me-1"></i>Scan at entrance gate
      </div>
      <div class="qr-box">
        <div id="qrcode"></div>
      </div>
      <div style="font-size:0.72rem;color:var(--text-muted);margin-top:8px;font-family:'Poppins',sans-serif;letter-spacing:1px">
        <?= htmlspecialchars($b['booking_ref']) ?>
      </div>
    </div>

    <!-- Footer -->
    <div class="receipt-footer">
      <div style="font-size:0.78rem;color:var(--text-muted)">
        <i class="fas fa-shield-halved me-1" style="color:var(--primary-light)"></i>
        SecurePark · Smart Parking Solutions · <span style="color:var(--accent)">24/7 Support</span>
      </div>
      <div style="font-size:0.72rem;color:var(--text-muted);margin-top:6px">
        This receipt is valid only for the booking reference shown above.
      </div>
    </div>
  </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById('qrcode'), {
    text: '<?= addslashes($b['booking_ref']) ?>',
    width:  160,
    height: 160,
    colorDark:  '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H
});
</script>
</body>
</html>
