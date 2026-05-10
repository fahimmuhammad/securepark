<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
requireLogin();

$user        = getCurrentUser();
$active_page = 'dashboard';

// Fetch stats
$uid   = (int)$user['id'];
$stats = [];

$r = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id=$uid AND status IN ('pending','confirmed','active')");
$stats['active'] = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id=$uid");
$stats['total'] = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COALESCE(SUM(amount),0) s FROM bookings WHERE user_id=$uid AND payment_status='paid'");
$stats['spent'] = number_format($r->fetch_assoc()['s'], 2);

$r = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id=$uid AND status='completed'");
$stats['completed'] = $r->fetch_assoc()['c'];

// Recent bookings
$recent = $conn->query("
    SELECT b.*, pz.name zone_name, ps.slot_number, ps.slot_type
    FROM bookings b
    JOIN parking_slots ps ON b.slot_id = ps.id
    JOIN parking_zones pz ON ps.zone_id = pz.id
    WHERE b.user_id = $uid
    ORDER BY b.created_at DESC
    LIMIT 5
");

// Upcoming booking
$upcoming = $conn->query("
    SELECT b.*, pz.name zone_name, ps.slot_number, pz.floor
    FROM bookings b
    JOIN parking_slots ps ON b.slot_id = ps.id
    JOIN parking_zones pz ON ps.zone_id = pz.id
    WHERE b.user_id = $uid AND b.status IN ('confirmed','pending') AND b.start_time > NOW()
    ORDER BY b.start_time ASC
    LIMIT 1
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — SecurePark</title>
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
          <h1 class="page-title">Welcome back, <?= htmlspecialchars(explode(' ',$user['name'])[0]) ?>! 👋</h1>
          <div class="page-breadcrumb">Dashboard / Overview</div>
        </div>
        <a href="/securepark/book.php" class="btn-primary-custom btn-sm-custom">
          <i class="fas fa-plus"></i> Book a Slot
        </a>
      </div>

      <!-- Stats Grid -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
          <div class="dash-stat-card">
            <div class="stat-icon-box icon-purple"><i class="fas fa-ticket"></i></div>
            <div class="stat-info">
              <div class="stat-value"><?= $stats['active'] ?></div>
              <div class="stat-title">Active Bookings</div>
              <div class="stat-change up"><i class="fas fa-arrow-trend-up me-1"></i>This month</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="dash-stat-card">
            <div class="stat-icon-box icon-cyan"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-info">
              <div class="stat-value"><?= $stats['total'] ?></div>
              <div class="stat-title">Total Bookings</div>
              <div class="stat-change up"><i class="fas fa-arrow-trend-up me-1"></i>All time</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="dash-stat-card">
            <div class="stat-icon-box icon-green"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-info">
              <div class="stat-value">$<?= $stats['spent'] ?></div>
              <div class="stat-title">Total Spent</div>
              <div class="stat-change"><i class="fas fa-circle-info me-1"></i>Paid bookings</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="dash-stat-card">
            <div class="stat-icon-box icon-amber"><i class="fas fa-flag-checkered"></i></div>
            <div class="stat-info">
              <div class="stat-value"><?= $stats['completed'] ?></div>
              <div class="stat-title">Completed</div>
              <div class="stat-change up"><i class="fas fa-circle-check me-1"></i>All time</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Upcoming Booking -->
        <div class="col-lg-4">
          <?php if ($upcoming): ?>
          <div class="section-card h-100">
            <div class="section-card-header">
              <div class="section-card-title"><i class="fas fa-calendar-star me-2" style="color:var(--accent)"></i>Upcoming Booking</div>
              <span class="status-badge badge-<?= $upcoming['status'] ?>"><?= ucfirst($upcoming['status']) ?></span>
            </div>
            <div class="section-card-body">
              <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:56px;height:56px;background:var(--gradient);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff">
                  <i class="fas fa-square-parking"></i>
                </div>
                <div>
                  <div style="font-size:1.2rem;font-weight:700"><?= htmlspecialchars($upcoming['slot_number']) ?></div>
                  <div style="font-size:0.85rem;color:var(--text-muted)"><?= htmlspecialchars($upcoming['zone_name']) ?> · <?= htmlspecialchars($upcoming['floor']) ?></div>
                </div>
              </div>
              <?php
              $rows = [
                ['fas fa-hashtag',     'Booking Ref',  $upcoming['booking_ref']],
                ['fas fa-calendar',    'Date',         date('M d, Y', strtotime($upcoming['start_time']))],
                ['fas fa-clock',       'Time',         date('h:i A', strtotime($upcoming['start_time'])) . ' – ' . date('h:i A', strtotime($upcoming['end_time']))],
                ['fas fa-car',         'Vehicle',      strtoupper($upcoming['vehicle_number'])],
                ['fas fa-dollar-sign', 'Amount',       '$' . number_format($upcoming['amount'],2)],
              ];
              foreach ($rows as [$icon,$label,$val]):
              ?>
              <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border);font-size:0.85rem">
                <span style="color:var(--text-muted)"><i class="<?= $icon ?> me-2"></i><?= $label ?></span>
                <span class="fw-500"><?= htmlspecialchars($val) ?></span>
              </div>
              <?php endforeach; ?>
              <a href="/securepark/booking-receipt.php?ref=<?= urlencode($upcoming['booking_ref']) ?>" class="btn-outline-custom w-100 justify-content-center mt-3" style="font-size:0.82rem">
                <i class="fas fa-receipt me-1"></i> View Receipt
              </a>
            </div>
          </div>
          <?php else: ?>
          <div class="section-card h-100">
            <div class="section-card-header"><div class="section-card-title"><i class="fas fa-calendar-star me-2" style="color:var(--accent)"></i>Upcoming Booking</div></div>
            <div class="section-card-body">
              <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-calendar-xmark"></i></div>
                <div class="empty-title">No Upcoming Booking</div>
                <p class="empty-desc">You have no upcoming reservations.</p>
                <a href="/securepark/book.php" class="btn-primary-custom btn-sm-custom"><i class="fas fa-plus"></i> Book a Slot</a>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Recent Bookings Table -->
        <div class="col-lg-8">
          <div class="data-table-wrap">
            <div class="data-table-header">
              <div class="data-table-title"><i class="fas fa-clock-rotate-left me-2" style="color:var(--primary-light)"></i>Recent Bookings</div>
              <a href="/securepark/my-bookings.php" style="font-size:0.82rem;color:var(--primary-light)">View All <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <?php if ($recent->num_rows > 0): ?>
            <div class="table-responsive">
              <table class="sp-table" id="recentTable">
                <thead>
                  <tr>
                    <th>Ref</th><th>Slot</th><th>Date</th><th>Amount</th><th>Status</th><th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($b = $recent->fetch_assoc()): ?>
                  <tr>
                    <td><span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:0.82rem;color:var(--primary-light)"><?= htmlspecialchars($b['booking_ref']) ?></span></td>
                    <td>
                      <div><?= htmlspecialchars($b['slot_number']) ?></div>
                      <div class="td-muted"><?= htmlspecialchars($b['zone_name']) ?></div>
                    </td>
                    <td>
                      <div><?= date('M d, Y', strtotime($b['start_time'])) ?></div>
                      <div class="td-muted"><?= date('h:i A', strtotime($b['start_time'])) ?></div>
                    </td>
                    <td><strong>$<?= number_format($b['amount'],2) ?></strong></td>
                    <td><span class="status-badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                    <td><a href="/securepark/booking-receipt.php?ref=<?= urlencode($b['booking_ref']) ?>" title="View Receipt" style="color:var(--text-muted);font-size:0.82rem"><i class="fas fa-receipt"></i></a></td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
              <div class="empty-icon"><i class="fas fa-ticket"></i></div>
              <div class="empty-title">No Bookings Yet</div>
              <p class="empty-desc">Start by booking your first parking spot.</p>
              <a href="/securepark/book.php" class="btn-primary-custom btn-sm-custom"><i class="fas fa-plus"></i> Book Now</a>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-12">
          <div class="section-card">
            <div class="section-card-header"><div class="section-card-title">Quick Actions</div></div>
            <div class="section-card-body">
              <div class="row g-3">
                <?php $actions = [
                  ['/securepark/book.php',        'fas fa-square-parking','Book a Slot',    'Find & reserve a parking spot now.',            'var(--primary)'],
                  ['/securepark/my-bookings.php', 'fas fa-ticket',        'My Bookings',    'View and manage all your bookings.',             'var(--accent)'],
                  ['/securepark/profile.php',     'fas fa-user-circle',   'Edit Profile',   'Update your personal and vehicle info.',         'var(--success)'],
                  ['#',                           'fas fa-headset',       'Get Support',    '24/7 support — we\'re here to help.',           'var(--warning)'],
                ]; foreach ($actions as [$url,$icon,$title,$desc,$color]): ?>
                <div class="col-6 col-md-3">
                  <a href="<?= $url ?>" style="display:block;padding:20px;background:var(--glass-bg);border:1px solid var(--border);border-radius:var(--radius-md);text-decoration:none;transition:var(--transition)" onmouseover="this.style.borderColor='<?= $color ?>'" onmouseout="this.style.borderColor='var(--border)'">
                    <div style="width:44px;height:44px;background:rgba(255,255,255,0.06);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:<?= $color ?>;margin-bottom:12px"><i class="<?= $icon ?>"></i></div>
                    <div style="font-weight:600;font-size:0.9rem;margin-bottom:4px"><?= $title ?></div>
                    <div style="font-size:0.78rem;color:var(--text-muted)"><?= $desc ?></div>
                  </a>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/securepark/assets/js/main.js"></script>
</body>
</html>
