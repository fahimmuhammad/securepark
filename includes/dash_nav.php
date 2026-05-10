<?php
// Requires: $user (array from getCurrentUser())
$initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', trim($user['name'])), 0, 2)));
?>
<nav class="dash-topnav">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>
    <div class="nav-brand">
        <a href="/securepark/index.php" class="brand-link">
            <span class="brand-icon"><i class="fas fa-shield-halved"></i></span>
            <span class="brand-name">SecurePark</span>
        </a>
    </div>
    <div class="nav-actions ms-auto d-flex align-items-center gap-3">
        <?php if (!isAdmin()): ?>
        <a href="/securepark/book.php" class="btn-nav-action">
            <i class="fas fa-plus"></i> <span class="d-none d-md-inline">Book Slot</span>
        </a>
        <?php endif; ?>
        <button class="theme-toggle" id="themeToggle" title="Toggle theme" aria-label="Toggle dark/light mode">
            <i class="fas fa-sun"></i>
        </button>
        <div class="nav-notification" title="Notifications">
            <i class="fas fa-bell"></i>
            <span class="notif-dot"></span>
        </div>
        <div class="nav-user-menu" id="navUserMenu">
            <div class="nav-avatar" id="navAvatarBtn">
                <span><?= htmlspecialchars($initials) ?></span>
            </div>
            <div class="nav-dropdown" id="navDropdown">
                <div class="nav-dropdown-header">
                    <div class="nav-avatar nav-avatar-lg">
                        <span><?= htmlspecialchars($initials) ?></span>
                    </div>
                    <div>
                        <div class="fw-semibold text-white"><?= htmlspecialchars($user['name']) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                </div>
                <hr class="dropdown-divider">
                <?php if (!isAdmin()): ?>
                <a href="/securepark/profile.php" class="nav-dropdown-item"><i class="fas fa-user-circle"></i> Profile</a>
                <a href="/securepark/my-bookings.php" class="nav-dropdown-item"><i class="fas fa-ticket"></i> My Bookings</a>
                <?php else: ?>
                <a href="/securepark/admin/index.php" class="nav-dropdown-item"><i class="fas fa-gauge"></i> Admin Panel</a>
                <?php endif; ?>
                <hr class="dropdown-divider">
                <a href="/securepark/logout.php" class="nav-dropdown-item text-danger"><i class="fas fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>
