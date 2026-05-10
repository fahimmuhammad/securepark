<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$active_page = 'users';

$filter  = $_GET['role'] ?? 'all';
$allowed = ['all','user','admin'];
if (!in_array($filter, $allowed)) $filter = 'all';

$where = $filter !== 'all' ? "WHERE role = '$filter'" : '';

$users = $conn->query("
    SELECT u.*,
           (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) total_bookings,
           (SELECT COALESCE(SUM(amount),0) FROM bookings WHERE user_id = u.id AND payment_status='paid') total_spent
    FROM users u
    $where
    ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users — SecurePark Admin</title>
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
      <div class="nav-brand"><span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:1rem;color:var(--text-secondary)">User Management</span></div>
      <div class="ms-auto"><a href="/securepark/logout.php" class="btn-outline-custom btn-sm-custom" style="padding:7px 14px;font-size:0.82rem"><i class="fas fa-right-from-bracket"></i> Logout</a></div>
    </nav>

    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Users</h1>
          <div class="page-breadcrumb"><a href="/securepark/admin/index.php">Admin</a> / Users</div>
        </div>
      </div>

      <div class="filter-pills mb-4">
        <?php foreach (['all'=>'All Users','user'=>'Regular Users','admin'=>'Administrators'] as $v=>$l): ?>
        <a href="?role=<?= $v ?>" class="filter-pill <?= $filter===$v?'active':'' ?>"><?= $l ?></a>
        <?php endforeach; ?>
      </div>

      <div class="data-table-wrap">
        <div class="data-table-header">
          <div class="data-table-title">Users (<?= $users->num_rows ?>)</div>
          <div class="table-search-wrap">
            <i class="fas fa-search table-search-icon"></i>
            <input type="text" class="table-search" data-table="usersTable" placeholder="Search users...">
          </div>
        </div>
        <div class="table-responsive">
          <table class="sp-table" id="usersTable">
            <thead>
              <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Vehicle</th><th>Role</th><th>Bookings</th><th>Total Spent</th><th>Joined</th></tr>
            </thead>
            <tbody>
              <?php if ($users->num_rows > 0): $i=1; while ($u = $users->fetch_assoc()): ?>
              <tr>
                <td class="td-muted"><?= $i++ ?></td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;font-family:'Poppins',sans-serif;font-weight:700;font-size:0.75rem;color:#fff;flex-shrink:0">
                      <?= implode('', array_map(fn($w)=>strtoupper($w[0]), array_slice(explode(' ',trim($u['name'])),0,2))) ?>
                    </div>
                    <span style="font-weight:500"><?= htmlspecialchars($u['name']) ?></span>
                  </div>
                </td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                <td>
                  <?php if ($u['vehicle_number']): ?>
                  <span style="background:rgba(124,58,237,0.1);color:var(--primary-light);padding:3px 8px;border-radius:4px;font-size:0.78rem;font-weight:600"><?= htmlspecialchars(strtoupper($u['vehicle_number'])) ?></span>
                  <?php else: ?>
                  <span class="td-muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($u['role'] === 'admin'): ?>
                  <span class="status-badge" style="background:rgba(245,158,11,0.15);color:var(--warning)"><i class="fas fa-crown" style="font-size:0.7rem"></i> Admin</span>
                  <?php else: ?>
                  <span class="status-badge badge-confirmed">User</span>
                  <?php endif; ?>
                </td>
                <td><?= $u['total_bookings'] ?></td>
                <td><strong>$<?= number_format($u['total_spent'],2) ?></strong></td>
                <td class="td-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
              </tr>
              <?php endwhile; else: ?>
              <tr><td colspan="9" class="text-center" style="padding:40px;color:var(--text-muted)">No users found.</td></tr>
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
