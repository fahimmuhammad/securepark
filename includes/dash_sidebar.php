<?php
// Requires: $user (array), $active_page (string)
?>
<aside class="dash-sidebar" id="dashSidebar">
    <div class="sidebar-brand">
        <span class="brand-icon"><i class="fas fa-shield-halved"></i></span>
        <span class="brand-name">SecurePark</span>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Main</div>
        <a href="/securepark/dashboard.php" class="sidebar-link <?= $active_page === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-gauge-high"></i> <span>Dashboard</span>
        </a>
        <a href="/securepark/book.php" class="sidebar-link <?= $active_page === 'book' ? 'active' : '' ?>">
            <i class="fas fa-square-parking"></i> <span>Book a Slot</span>
        </a>
        <a href="/securepark/my-bookings.php" class="sidebar-link <?= $active_page === 'bookings' ? 'active' : '' ?>">
            <i class="fas fa-ticket"></i> <span>My Bookings</span>
        </a>

        <div class="sidebar-section-label mt-3">Account</div>
        <a href="/securepark/profile.php" class="sidebar-link <?= $active_page === 'profile' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i> <span>Profile</span>
        </a>
        <a href="/securepark/logout.php" class="sidebar-link">
            <i class="fas fa-right-from-bracket"></i> <span>Logout</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user-card">
            <div class="su-avatar"><?= implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', trim($user['name'])), 0, 2))) ?></div>
            <div class="su-info">
                <div class="su-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="su-role">User</div>
            </div>
        </div>
    </div>
</aside>
