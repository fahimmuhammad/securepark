<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$active_page = 'dashboard';

// Stats
$total_bookings    = $conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c'];
$active_bookings   = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status IN ('active','confirmed')")->fetch_assoc()['c'];
$total_revenue     = $conn->query("SELECT COALESCE(SUM(amount),0) s FROM bookings WHERE payment_status='paid'")->fetch_assoc()['s'];
$total_users       = $conn->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch_assoc()['c'];
$total_slots       = $conn->query("SELECT COUNT(*) c FROM parking_slots")->fetch_assoc()['c'];
$available_slots   = $conn->query("SELECT COUNT(*) c FROM parking_slots WHERE status='available'")->fetch_assoc()['c'];
$occupied_slots    = $conn->query("SELECT COUNT(*) c FROM parking_slots WHERE status='occupied'")->fetch_assoc()['c'];
$today_bookings    = $conn->query("SELECT COUNT(*) c FROM bookings WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];

// Chart: revenue + bookings last 7 days
$chart_labels   = [];
$chart_revenue  = [];
$chart_bookings = [];
for ($i = 6; $i >= 0; $i--) {
    $chart_labels[]   = date('M d', strtotime("-$i days"));
    $chart_revenue[]  = 0;
    $chart_bookings[] = 0;
}
$cr = $conn->query("
    SELECT DATE(created_at) d, COALESCE(SUM(amount),0) rev, COUNT(*) cnt
    FROM bookings
    WHERE created_at >= CURDATE() - INTERVAL 6 DAY
    GROUP BY DATE(created_at)
");
while ($row = $cr->fetch_assoc()) {
    $idx = 6 - (int)round((strtotime('today') - strtotime($row['d'])) / 86400);
    if ($idx >= 0 && $idx < 7) {
        $chart_revenue[$idx]  = (float)$row['rev'];
        $chart_bookings[$idx] = (int)$row['cnt'];
    }
}

// Chart: booking status distribution
$sc_map = ['pending'=>0,'confirmed'=>0,'active'=>0,'completed'=>0,'cancelled'=>0];
$sr2 = $conn->query("SELECT status, COUNT(*) c FROM bookings GROUP BY status");
while ($r = $sr2->fetch_assoc()) { if (isset($sc_map[$r['status']])) $sc_map[$r['status']] = (int)$r['c']; }

// Zone occupancy
$zones_occ = $conn->query("
    SELECT pz.name, pz.color,
           COUNT(ps.id) total,
           SUM(ps.status='occupied' OR ps.status='reserved') busy,
           SUM(ps.status='available') free
    FROM parking_zones pz
    LEFT JOIN parking_slots ps ON pz.id = ps.zone_id
    GROUP BY pz.id
");

// Recent bookings
$recent = $conn->query("
    SELECT b.*, u.name user_name, pz.name zone_name, ps.slot_number
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN parking_slots ps ON b.slot_id = ps.id
    JOIN parking_zones pz ON ps.zone_id = pz.id
    ORDER BY b.created_at DESC
    LIMIT 8
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — SecurePark</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/securepark/assets/css/style.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head>
<body>
<div class="dash-layout">
  <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
  <div class="dash-content" id="dashContent">
    <!-- Admin topnav -->
    <nav class="dash-topnav">
      <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
      <div class="nav-brand">
        <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:1rem;color:var(--text-secondary)">Admin Panel</span>
      </div>
      <div class="ms-auto d-flex align-items-center gap-3">
        <span style="font-size:0.82rem;color:var(--text-muted)"><i class="fas fa-circle" style="color:var(--success);font-size:0.5rem;margin-right:5px"></i>System Online</span>
        <a href="/securepark/logout.php" class="btn-outline-custom btn-sm-custom" style="padding:7px 14px;font-size:0.82rem">
          <i class="fas fa-right-from-bracket"></i> Logout
        </a>
      </div>
    </nav>

    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Admin Dashboard</h1>
          <div class="page-breadcrumb">Admin / Overview · <?= date('l, F j, Y') ?></div>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="row g-3 mb-4">
        <?php $stats = [
          [$total_revenue, 'Total Revenue',     'fas fa-dollar-sign', 'icon-green',  '$', '', 'paid bookings'],
          [$total_bookings,'Total Bookings',    'fas fa-ticket',      'icon-purple', '',  '', 'all time'],
          [$active_bookings,'Active Now',       'fas fa-car',         'icon-cyan',   '',  '', 'confirmed+active'],
          [$total_users,   'Registered Users',  'fas fa-users',       'icon-amber',  '',  '', 'user accounts'],
          [$available_slots,'Available Slots',  'fas fa-circle-check','icon-green',  '',  '', "of $total_slots total"],
          [$today_bookings, 'Today\'s Bookings','fas fa-calendar-day','icon-purple', '',  '', 'new today'],
        ]; foreach ($stats as [$val,$title,$icon,$cls,$pre,$suf,$sub]): ?>
        <div class="col-6 col-lg-4 col-xl-2">
          <div class="dash-stat-card">
            <div class="stat-icon-box <?= $cls ?>"><i class="<?= $icon ?>"></i></div>
            <div class="stat-info">
              <div class="stat-value" style="font-size:1.4rem"><?= $pre ?><?= is_float($val) ? number_format($val,2) : $val ?><?= $suf ?></div>
              <div class="stat-title"><?= $title ?></div>
              <div class="stat-change"><?= $sub ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-4">
        <!-- Zone Occupancy -->
        <div class="col-lg-4">
          <div class="section-card h-100">
            <div class="section-card-header">
              <div class="section-card-title"><i class="fas fa-chart-pie me-2" style="color:var(--accent)"></i>Zone Occupancy</div>
            </div>
            <div class="section-card-body">
              <?php while ($z = $zones_occ->fetch_assoc()):
                $pct = $z['total'] > 0 ? round(($z['busy'] / $z['total']) * 100) : 0;
              ?>
              <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <div style="width:10px;height:10px;border-radius:50%;background:<?= htmlspecialchars($z['color']) ?>"></div>
                    <span style="font-weight:600;font-size:0.88rem"><?= htmlspecialchars($z['name']) ?></span>
                  </div>
                  <div style="font-size:0.8rem;color:var(--text-muted)"><?= $z['free'] ?> free / <?= $z['total'] ?> total</div>
                </div>
                <div style="height:6px;background:var(--bg-elevated);border-radius:3px;overflow:hidden">
                  <div style="height:100%;width:<?= $pct ?>%;background:<?= $z['color'] ?>;border-radius:3px;transition:width 1s ease"></div>
                </div>
                <div style="font-size:0.72rem;color:var(--text-muted);margin-top:4px"><?= $pct ?>% occupied</div>
              </div>
              <?php endwhile; ?>
            </div>
          </div>
        </div>

        <!-- Recent Bookings -->
        <div class="col-lg-8">
          <div class="data-table-wrap">
            <div class="data-table-header">
              <div class="data-table-title"><i class="fas fa-clock-rotate-left me-2" style="color:var(--primary-light)"></i>Recent Bookings</div>
              <a href="/securepark/admin/bookings.php" style="font-size:0.82rem;color:var(--primary-light)">View All <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div class="table-responsive">
              <table class="sp-table">
                <thead>
                  <tr><th>Ref</th><th>User</th><th>Slot</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                  <?php while ($b = $recent->fetch_assoc()): ?>
                  <tr>
                    <td><span style="font-size:0.78rem;font-family:'Poppins',sans-serif;font-weight:600;color:var(--primary-light)"><?= htmlspecialchars($b['booking_ref']) ?></span></td>
                    <td><?= htmlspecialchars($b['user_name']) ?></td>
                    <td>
                      <div><?= htmlspecialchars($b['slot_number']) ?></div>
                      <div class="td-muted"><?= htmlspecialchars($b['zone_name']) ?></div>
                    </td>
                    <td>
                      <div><?= date('M d', strtotime($b['start_time'])) ?></div>
                      <div class="td-muted"><?= date('h:i A', strtotime($b['start_time'])) ?></div>
                    </td>
                    <td><strong>$<?= number_format($b['amount'],2) ?></strong></td>
                    <td><span class="status-badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                    <td>
                      <?php if ($b['status'] === 'pending'): ?>
                      <button class="btn-accent btn-sm-custom" style="padding:5px 10px;font-size:0.75rem;border-radius:6px" onclick="bookingAction(<?= $b['id'] ?>,'confirm')">Confirm</button>
                      <?php elseif ($b['status'] === 'confirmed'): ?>
                      <button class="btn-accent btn-sm-custom" style="padding:5px 10px;font-size:0.75rem;border-radius:6px" onclick="bookingAction(<?= $b['id'] ?>,'activate')">Activate</button>
                      <?php else: ?>
                      <span class="td-muted">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="col-lg-8">
          <div class="section-card">
            <div class="section-card-header">
              <div class="section-card-title"><i class="fas fa-chart-line me-2" style="color:var(--accent)"></i>Revenue & Bookings (Last 7 Days)</div>
            </div>
            <div class="section-card-body" style="padding:16px 24px">
              <canvas id="revenueChart" height="100"></canvas>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="section-card">
            <div class="section-card-header">
              <div class="section-card-title"><i class="fas fa-chart-donut me-2" style="color:var(--primary-light)"></i>Booking Status</div>
            </div>
            <div class="section-card-body" style="display:flex;align-items:center;justify-content:center;padding:20px">
              <canvas id="statusChart" width="220" height="220" style="max-width:220px"></canvas>
            </div>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="col-12">
          <div class="section-card">
            <div class="section-card-header"><div class="section-card-title">Quick Actions</div></div>
            <div class="section-card-body">
              <div class="row g-3">
                <?php $qlinks = [
                  ['/securepark/admin/bookings.php','fas fa-calendar-check','Manage Bookings', 'View, confirm, and manage all bookings.','var(--primary)'],
                  ['/securepark/admin/slots.php',   'fas fa-map',           'Parking Slots',   'Monitor and update slot statuses.',       'var(--accent)'],
                  ['/securepark/admin/users.php',   'fas fa-users',         'User Management', 'View registered users and their roles.', 'var(--success)'],
                  ['/securepark/index.php',         'fas fa-globe',         'View Website',    'Open the public-facing website.',         'var(--warning)'],
                ]; foreach ($qlinks as [$url,$icon,$title,$desc,$col]): ?>
                <div class="col-6 col-md-3">
                  <a href="<?= $url ?>" target="<?= str_contains($url,'index')&&!str_contains($url,'admin')?'_blank':'' ?>" style="display:block;padding:20px;background:var(--glass-bg);border:1px solid var(--border);border-radius:var(--radius-md);text-decoration:none;transition:var(--transition)" onmouseover="this.style.borderColor='<?= $col ?>'" onmouseout="this.style.borderColor='var(--border)'">
                    <div style="width:44px;height:44px;background:rgba(255,255,255,0.06);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:<?= $col ?>;margin-bottom:12px"><i class="<?= $icon ?>"></i></div>
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
<script>
Chart.defaults.color = '#94a3b8';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family = "'Inter', sans-serif";

// Revenue + Bookings line chart
new Chart(document.getElementById('revenueChart'), {
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [
            {
                type: 'bar',
                label: 'Revenue ($)',
                data: <?= json_encode($chart_revenue) ?>,
                backgroundColor: 'rgba(124,58,237,0.25)',
                borderColor: '#7c3aed',
                borderWidth: 2,
                borderRadius: 6,
                yAxisID: 'y',
            },
            {
                type: 'line',
                label: 'Bookings',
                data: <?= json_encode($chart_bookings) ?>,
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6,182,212,0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#06b6d4',
                pointRadius: 4,
                tension: 0.4,
                fill: true,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { labels: { boxWidth: 12, padding: 16 } } },
        scales: {
            x:  { grid: { color: 'rgba(255,255,255,0.05)' } },
            y:  { position: 'left',  grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: v => '$' + v } },
            y1: { position: 'right', grid: { display: false }, ticks: { stepSize: 1 } }
        }
    }
});

// Status doughnut chart
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Pending','Confirmed','Active','Completed','Cancelled'],
        datasets: [{
            data: <?= json_encode(array_values($sc_map)) ?>,
            backgroundColor: ['#f59e0b','#06b6d4','#10b981','#7c3aed','#ef4444'],
            borderColor: '#0d1424',
            borderWidth: 3,
            hoverOffset: 8,
        }]
    },
    options: {
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 12 } } }
        }
    }
});
</script>
</body>
</html>
