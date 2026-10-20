<?php
/**
 * Royal Stay - Admin Portal Master Header & Sidebar Layout
 */

require_once __DIR__ . '/admin-auth.php';

$adminUser = get_logged_in_user();
$activeAdminNav = $activeAdminNav ?? 'dashboard';
$adminPageTitle = $adminPageTitle ?? 'Admin Dashboard';
$hotelName = get_setting('hotel_name', 'Royal Stay');

// Count unread messages and pending bookings for badges
$pdo = get_db();
try {
    $unreadCount = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
    $pendingBookingsCount = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'pending'")->fetchColumn();
} catch (Exception $e) {
    $unreadCount = 0;
    $pendingBookingsCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminPageTitle) ?> | <?= e($hotelName) ?> Admin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>👑</text></svg>">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Admin Stylesheet -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>">

    <!-- Chart.js for Dashboard Visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body">

    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <!-- Brand Header -->
        <div class="sidebar-header">
            <div class="sidebar-brand-icon">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div class="sidebar-brand-text">Royal Stay</div>
        </div>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <div class="nav-section-title">Core Management</div>

            <a href="<?= base_url('admin/index.php') ?>" class="admin-nav-item <?= $activeAdminNav === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>

            <a href="<?= base_url('admin/bookings.php') ?>" class="admin-nav-item <?= $activeAdminNav === 'bookings' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Reservations</span>
                <?php if ($pendingBookingsCount > 0): ?>
                    <span class="nav-badge" style="background: var(--warning); color: #000;"><?= $pendingBookingsCount ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= base_url('admin/rooms.php') ?>" class="admin-nav-item <?= $activeAdminNav === 'rooms' ? 'active' : '' ?>">
                <i class="fa-solid fa-door-open"></i>
                <span>Suites & Rooms</span>
            </a>

            <div class="nav-section-title">Guest Services</div>

            <a href="<?= base_url('admin/reviews.php') ?>" class="admin-nav-item <?= $activeAdminNav === 'reviews' ? 'active' : '' ?>">
                <i class="fa-solid fa-star"></i>
                <span>Guest Reviews</span>
            </a>

            <a href="<?= base_url('admin/messages.php') ?>" class="admin-nav-item <?= $activeAdminNav === 'messages' ? 'active' : '' ?>">
                <i class="fa-solid fa-envelope"></i>
                <span>Inquiries</span>
                <?php if ($unreadCount > 0): ?>
                    <span class="nav-badge" style="background: var(--accent); color: var(--admin-sidebar); font-weight: 700;"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>

            <div class="nav-section-title">Settings & System</div>

            <a href="<?= base_url('admin/settings.php') ?>" class="admin-nav-item <?= $activeAdminNav === 'settings' ? 'active' : '' ?>">
                <i class="fa-solid fa-sliders"></i>
                <span>Hotel Settings</span>
            </a>

            <a href="<?= base_url() ?>" target="_blank" class="admin-nav-item">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>View Public Site</span>
            </a>
        </nav>

        <!-- Sidebar Footer / Current Admin User -->
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    <?= strtoupper(substr($adminUser['name'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="name"><?= e(explode(' ', $adminUser['name'] ?? 'Admin')[0]) ?></div>
                    <div class="role">Executive Admin</div>
                </div>
            </div>
            <a href="<?= base_url('admin/logout.php') ?>" title="Sign Out" style="color: rgba(255,255,255,0.6); font-size: 1.1rem; transition: var(--transition);">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <!-- Main Workspace -->
    <main class="admin-main">
        
        <!-- Sticky Topbar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <button type="button" class="sidebar-toggle-btn" id="adminSidebarToggle" aria-label="Toggle Navigation">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="topbar-page-title"><?= e($adminPageTitle) ?></h1>
            </div>

            <div class="topbar-right">
                <a href="<?= base_url() ?>" target="_blank" class="topbar-action-btn">
                    <i class="fa-solid fa-globe"></i> Live Resort Site
                </a>
                <a href="<?= base_url('admin/bookings.php') ?>" class="topbar-action-btn">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($pendingBookingsCount > 0): ?>
                        <span style="background: var(--warning); color: #000; border-radius: 9999px; padding: 2px 7px; font-size: 0.72rem; font-weight: 700;"><?= $pendingBookingsCount ?> Pending</span>
                    <?php else: ?>
                        <span>Reservations</span>
                    <?php endif; ?>
                </a>
                <a href="<?= base_url('admin/logout.php') ?>" class="topbar-action-btn" style="color: var(--error);">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Exit
                </a>
            </div>
        </header>

        <!-- Flash Container -->
        <?= render_flash() ?>

        <div class="admin-content">
