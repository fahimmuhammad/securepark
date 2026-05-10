<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
requireLogin();

$user        = getCurrentUser();
$active_page = 'bookings';
$uid         = (int)$user['id'];

$filter = $_GET['status'] ?? 'all';
$allowed_filters = ['all','pending','confirmed','active','completed','cancelled'];
if (!in_array($filter, $allowed_filters)) $filter = 'all';

$where = $filter !== 'all' ? "AND b.status = '$filter'" : '';

$bookings = $conn->query("
    SELECT b.*, pz.name zone_name, pz.floor, ps.slot_number, ps.slot_type, ps.hourly_rate
    FROM bookings b
    JOIN parking_slots ps ON b.slot_id = ps.id
    JOIN parking_zones pz ON ps.zone_id = pz.id
    WHERE b.user_id = $uid $where
    ORDER BY b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings — SecurePark</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/securepark/assets/css/style.css">
</head>
<body>
<div class="dash-layout">
  <?php include __DIR__ . '/includes/dash_sidebar.php'; ?>
  <div class="dash-content" id="dashContent">
    <?php include __DIR__ . '/includes/dash_nav.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">My Bookings</h1>
          <div class="page-breadcrumb"><a href="/securepark/dashboard.php">Dashboard</a> / My Bookings</div>
        </div>
        <a href="/securepark/book.php" class="btn-primary-custom btn-sm-custom"><i class="fas fa-plus"></i> New Booking</a>
      </div>

      <!-- Filter Pills -->
      <div class="filter-pills mb-4">
        <?php foreach (['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','active'=>'Active','completed'=>'Completed','cancelled'=>'Cancelled'] as $val=>$lbl): ?>
        <a href="?status=<?= $val ?>" class="filter-pill <?= $filter===$val?'active':'' ?>"><?= $lbl ?></a>
        <?php endforeach; ?>
      </div>

      <?php if ($bookings->num_rows > 0): ?>
      <!-- Desktop Table -->
      <div class="data-table-wrap d-none d-md-block">
        <div class="data-table-header">
          <div class="data-table-title">Bookings (<?= $bookings->num_rows ?>)</div>
          <div class="table-search-wrap">
            <i class="fas fa-search table-search-icon"></i>
            <input type="text" class="table-search" data-table="bookingsTable" placeholder="Search bookings...">
          </div>
        </div>
        <div class="table-responsive">
          <table class="sp-table" id="bookingsTable">
            <thead>
              <tr>
                <th>Booking Ref</th><th>Slot / Zone</th><th>Vehicle</th>
                <th>Start Time</th><th>End Time</th><th>Duration</th>
                <th>Amount</th><th>Status</th><th>Payment</th><th>Actions</th><th>Receipt</th>
              </tr>
            </thead>
            <tbody>
              <?php $bookings->data_seek(0); while ($b = $bookings->fetch_assoc()): ?>
              <tr>
                <td><span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:0.8rem;color:var(--primary-light)"><?= htmlspecialchars($b['booking_ref']) ?></span></td>
                <td>
                  <div class="fw-600"><?= htmlspecialchars($b['slot_number']) ?></div>
                  <div class="td-muted"><?= htmlspecialchars($b['zone_name']) ?></div>
                </td>
                <td>
                  <div><?= htmlspecialchars(strtoupper($b['vehicle_number'])) ?></div>
                  <div class="td-muted"><?= ucfirst($b['vehicle_type']) ?></div>
                </td>
                <td>
                  <div><?= date('M d, Y', strtotime($b['start_time'])) ?></div>
                  <div class="td-muted"><?= date('h:i A', strtotime($b['start_time'])) ?></div>
                </td>
                <td>
                  <div><?= date('M d, Y', strtotime($b['end_time'])) ?></div>
                  <div class="td-muted"><?= date('h:i A', strtotime($b['end_time'])) ?></div>
                </td>
                <td><?= number_format($b['duration_hours'],1) ?> hr<?= $b['duration_hours']!=1?'s':'' ?></td>
                <td><strong>$<?= number_format($b['amount'],2) ?></strong></td>
                <td><span class="status-badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                <td>
                  <?php if ($b['payment_status'] === 'paid'): ?>
                  <span class="status-badge badge-active">Paid</span>
                  <?php elseif ($b['payment_status'] === 'refunded'): ?>
                  <span class="status-badge badge-completed">Refunded</span>
                  <?php else: ?>
                  <span class="status-badge badge-pending">Unpaid</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (in_array($b['status'], ['pending','confirmed'])): ?>
                  <button class="btn-danger-custom" onclick="cancelBooking(<?= $b['id'] ?>)">
                    <i class="fas fa-xmark me-1"></i>Cancel
                  </button>
                  <?php else: ?>
                  <span class="td-muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="/securepark/booking-receipt.php?ref=<?= urlencode($b['booking_ref']) ?>" class="btn-outline-custom btn-sm-custom" style="padding:4px 10px;font-size:0.75rem" title="View Receipt">
                    <i class="fas fa-receipt"></i>
                  </a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Mobile Cards -->
      <div class="d-md-none">
        <?php $bookings->data_seek(0); while ($b = $bookings->fetch_assoc()): ?>
        <div class="booking-card mb-3">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div>
              <div style="font-family:'Poppins',sans-serif;font-weight:700;font-size:0.85rem;color:var(--primary-light)"><?= htmlspecialchars($b['booking_ref']) ?></div>
              <div style="font-size:0.82rem;color:var(--text-muted)"><?= date('M d, Y · h:i A', strtotime($b['start_time'])) ?></div>
            </div>
            <span class="status-badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
          </div>
          <div class="row g-2" style="font-size:0.85rem">
            <div class="col-6"><span style="color:var(--text-muted)">Slot</span><div class="fw-600"><?= htmlspecialchars($b['slot_number']) ?> · <?= htmlspecialchars($b['zone_name']) ?></div></div>
            <div class="col-6"><span style="color:var(--text-muted)">Vehicle</span><div class="fw-600"><?= htmlspecialchars(strtoupper($b['vehicle_number'])) ?></div></div>
            <div class="col-6"><span style="color:var(--text-muted)">Duration</span><div class="fw-600"><?= number_format($b['duration_hours'],1) ?> hrs</div></div>
            <div class="col-6"><span style="color:var(--text-muted)">Amount</span><div class="fw-600" style="color:var(--primary-light)">$<?= number_format($b['amount'],2) ?></div></div>
          </div>
          <div class="mt-3 d-flex gap-2">
            <?php if (in_array($b['status'], ['pending','confirmed'])): ?>
            <button class="btn-danger-custom" onclick="cancelBooking(<?= $b['id'] ?>)"><i class="fas fa-xmark me-1"></i>Cancel</button>
            <?php endif; ?>
            <a href="/securepark/booking-receipt.php?ref=<?= urlencode($b['booking_ref']) ?>" class="btn-outline-custom btn-sm-custom" style="padding:7px 14px;font-size:0.82rem">
              <i class="fas fa-receipt me-1"></i>Receipt
            </a>
          </div>
        </div>
        <?php endwhile; ?>
      </div>

      <?php else: ?>
      <div class="section-card">
        <div class="section-card-body">
          <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-ticket"></i></div>
            <div class="empty-title">No <?= $filter !== 'all' ? ucfirst($filter) : '' ?> Bookings Found</div>
            <p class="empty-desc"><?= $filter !== 'all' ? 'No bookings with this status.' : 'You haven\'t made any bookings yet.' ?></p>
            <a href="/securepark/book.php" class="btn-primary-custom btn-sm-custom"><i class="fas fa-plus"></i> Book a Slot</a>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/securepark/assets/js/main.js"></script>
</body>
</html>
