<?php
// Requires: $active_page (string)
?>
<aside class="dash-sidebar" id="dashSidebar">
    <div class="sidebar-brand">
        <span class="brand-icon"><i class="fas fa-shield-halved"></i></span>
        <span class="brand-name">SecurePark</span>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Admin Panel</div>
        <a href="/securepark/admin/index.php" class="sidebar-link <?= $active_page === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-gauge-high"></i> <span>Dashboard</span>
        </a>
        <a href="/securepark/admin/bookings.php" class="sidebar-link <?= $active_page === 'bookings' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i> <span>Bookings</span>
        </a>
        <a href="/securepark/admin/slots.php" class="sidebar-link <?= $active_page === 'slots' ? 'active' : '' ?>">
            <i class="fas fa-map"></i> <span>Parking Slots</span>
        </a>
        <a href="/securepark/admin/users.php" class="sidebar-link <?= $active_page === 'users' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> <span>Users</span>
        </a>

        <div class="sidebar-section-label mt-3">System</div>
        <a href="/securepark/index.php" class="sidebar-link" target="_blank">
            <i class="fas fa-globe"></i> <span>View Website</span>
        </a>
        <a href="/securepark/logout.php" class="sidebar-link">
            <i class="fas fa-right-from-bracket"></i> <span>Logout</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user-card">
            <div class="su-avatar" style="background: linear-gradient(135deg,#f59e0b,#ef4444)">A</div>
            <div class="su-info">
                <div class="su-name">Administrator</div>
                <div class="su-role" style="color:#f59e0b">Admin</div>
            </div>
        </div>
    </div>
</aside>
