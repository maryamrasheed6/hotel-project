<?php
/**
 * Royal Stay - Main Public Header
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth-check.php';

$hotelName = get_setting('hotel_name', 'Royal Stay Luxury Hotel & Resort');
$hotelTagline = get_setting('hotel_tagline', 'Luxury Stay Starts Here');
$pageTitle = isset($pageTitle) ? "{$pageTitle} | {$hotelName}" : "{$hotelName} | {$hotelTagline}";
$metaDescription = $metaDescription ?? "Experience ultimate five-star luxury at {$hotelName}. Oceanfront suites, private penthouses, Michelin-star dining, and bespoke concierge hospitality.";
$currentUser = get_logged_in_user();
$activeNav = $activeNav ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>👑</text></svg>">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Core Stylesheets -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>">
</head>
<body>

    <!-- Flash Notifications Container -->
    <?= render_flash() ?>

    <!-- Sticky Luxury Header -->
    <header class="site-header">
        <div class="container">
            <div class="nav-wrapper">
                
                <!-- Logo -->
                <a href="<?= base_url() ?>" class="brand-logo" id="mainBrandLogo">
                    <div class="logo-icon">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div class="logo-text">
                        <span class="logo-title">Royal Stay</span>
                        <span class="logo-tagline">Luxury Resort & Suites</span>
                    </div>
                </a>

                <!-- Navigation Links -->
                <nav class="nav-links" id="mainNavLinks">
                    <a href="<?= base_url() ?>" class="nav-link <?= $activeNav === 'home' ? 'active' : '' ?>">Home</a>
                    <a href="<?= base_url('rooms.php') ?>" class="nav-link <?= $activeNav === 'rooms' ? 'active' : '' ?>">Rooms & Suites</a>
                    <a href="<?= base_url('about.php') ?>" class="nav-link <?= $activeNav === 'about' ? 'active' : '' ?>">About</a>
                    <a href="<?= base_url('reviews.php') ?>" class="nav-link <?= $activeNav === 'reviews' ? 'active' : '' ?>">Guest Reviews</a>
                    <a href="<?= base_url('contact.php') ?>" class="nav-link <?= $activeNav === 'contact' ? 'active' : '' ?>">Contact</a>
                </nav>

                <!-- Actions / Auth -->
                <div class="nav-actions">
                    <?php if ($currentUser): ?>
                        <div class="nav-user-dropdown">
                            <button type="button" class="nav-user-btn" id="userMenuBtn">
                                <div class="avatar"><i class="fa-solid fa-user"></i></div>
                                <span><?= e(explode(' ', $currentUser['name'])[0]) ?></span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: var(--text-muted);"></i>
                            </button>
                            <div class="dropdown-menu">
                                <div style="padding: 0.5rem 1.25rem; font-size: 0.8rem; color: var(--text-muted); border-bottom: 1px solid var(--border);">
                                    Signed in as<br><strong style="color: var(--primary);"><?= e($currentUser['email']) ?></strong>
                                </div>
                                <a href="<?= base_url('my-bookings.php') ?>" class="dropdown-item">
                                    <i class="fa-solid fa-calendar-check" style="color: var(--accent);"></i> My Bookings
                                </a>
                                <?php if ($currentUser['role'] === 'admin'): ?>
                                    <a href="<?= base_url('admin/index.php') ?>" class="dropdown-item">
                                        <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> Admin Panel
                                    </a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a href="<?= base_url('logout.php') ?>" class="dropdown-item" style="color: var(--error);">
                                    <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= base_url('auth.php') ?>" class="btn btn-outline btn-sm">
                            <i class="fa-regular fa-user"></i> Sign In
                        </a>
                    <?php endif; ?>

                    <a href="<?= base_url('rooms.php') ?>" class="btn btn-gold btn-sm d-none-mobile">
                        <i class="fa-solid fa-calendar-plus"></i> Book Now
                    </a>

                    <!-- Mobile Menu Hamburger -->
                    <button type="button" class="mobile-nav-toggle" aria-label="Toggle navigation menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>

            </div>
        </div>
    </header>

    <main>
