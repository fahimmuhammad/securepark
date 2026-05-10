<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$active_page = 'bookings';

$filter  = $_GET['status'] ?? 'all';
$allowed = ['all','pending','confirmed','active','completed','cancelled'];
if (!in_array($filter, $allowed)) $filter = 'all';

$where = $filter !== 'all' ? "WHERE b.status = '$filter'" : '';

$bookings = $conn->query("
    SELECT b.*, u.name user_name, u.email user_email,
           pz.name zone_name, ps.slot_number, ps.slot_type
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN parking_slots ps ON b.slot_id = ps.id
    JOIN parking_zones pz ON ps.zone_id = pz.id
    $where
    ORDER BY b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Bookings — SecurePark Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/securepark/assets/css/style.css">
</head>
<body>
<div class="dash-layout">
  <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
  <div class="dash-content" id="dashContent">
    <nav class="dash-topnav">
      <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
      <div class="nav-brand"><span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:1rem;color:var(--text-secondary)">Booking Management</span></div>
      <div class="ms-auto"><a href="/securepark/logout.php" class="btn-outline-custom btn-sm-custom" style="padding:7px 14px;font-size:0.82rem"><i class="fas fa-right-from-bracket"></i> Logout</a></div>
    </nav>

    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Manage Bookings</h1>
          <div class="page-breadcrumb"><a href="/securepark/admin/index.php">Admin</a> / Bookings</div>
        </div>
      </div>

      <div class="filter-pills mb-4">
        <?php foreach (['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','active'=>'Active','completed'=>'Completed','cancelled'=>'Cancelled'] as $v=>$l): ?>
        <a href="?status=<?= $v ?>" class="filter-pill <?= $filter===$v?'active':'' ?>"><?= $l ?></a>
        <?php endforeach; ?>
      </div>

      <div class="data-table-wrap">
        <div class="data-table-header">
          <div class="data-table-title">Bookings (<?= $bookings->num_rows ?>)</div>
          <div class="table-search-wrap">
            <i class="fas fa-search table-search-icon"></i>
            <input type="text" class="table-search" data-table="adminBookingsTable" placeholder="Search bookings...">
          </div>
        </div>
        <div class="table-responsive">
          <table class="sp-table" id="adminBookingsTable">
            <thead>
              <tr>
                <th>Ref</th><th>User</th><th>Slot / Zone</th><th>Vehicle</th>
                <th>Start</th><th>End</th><th>Duration</th><th>Amount</th>
                <th>Status</th><th>Payment</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($bookings->num_rows > 0): while ($b = $bookings->fetch_assoc()): ?>
              <tr>
                <td><span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:0.78rem;color:var(--primary-light)"><?= htmlspecialchars($b['booking_ref']) ?></span></td>
                <td>
                  <div style="font-size:0.88rem"><?= htmlspecialchars($b['user_name']) ?></div>
                  <div class="td-muted"><?= htmlspecialchars($b['user_email']) ?></div>
                </td>
                <td>
                  <div><?= htmlspecialchars($b['slot_number']) ?></div>
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
                <td><?= number_format($b['duration_hours'],1) ?> hrs</td>
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
                  <div class="d-flex gap-1 flex-wrap">
                    <?php if ($b['status'] === 'pending'): ?>
                    <button class="btn-accent btn-sm-custom" style="padding:4px 10px;font-size:0.75rem;border-radius:6px" onclick="bookingAction(<?= $b['id'] ?>,'confirm')">Confirm</button>
                    <button class="btn-danger-custom" style="font-size:0.75rem" onclick="bookingAction(<?= $b['id'] ?>,'cancel')">Cancel</button>
                    <?php elseif ($b['status'] === 'confirmed'): ?>
                    <button class="btn-accent btn-sm-custom" style="padding:4px 10px;font-size:0.75rem;border-radius:6px" onclick="bookingAction(<?= $b['id'] ?>,'activate')">Activate</button>
                    <button class="btn-danger-custom" style="font-size:0.75rem" onclick="bookingAction(<?= $b['id'] ?>,'cancel')">Cancel</button>
                    <?php elseif ($b['status'] === 'active'): ?>
                    <button class="btn-accent btn-sm-custom" style="padding:4px 10px;font-size:0.75rem;border-radius:6px;background:var(--success)" onclick="bookingAction(<?= $b['id'] ?>,'complete')">Complete</button>
                    <?php else: ?>
                    <span class="td-muted">—</span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endwhile; else: ?>
              <tr><td colspan="11" class="text-center" style="padding:40px;color:var(--text-muted)"><i class="fas fa-ticket" style="font-size:2rem;display:block;margin-bottom:10px"></i>No bookings found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/securepark/assets/js/main.js"></script>
</body>
</html>
