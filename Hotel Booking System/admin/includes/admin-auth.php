<?php
/**
 * Royal Stay - Admin Authorization Enforcement
 */

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth-check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require valid administrator session
 */
function require_admin(): void {
    if (!is_logged_in() || !is_admin()) {
        set_flash('error', 'Administrator credentials required to access this portal.');
        header('Location: ' . base_url('admin/login.php'));
        exit;
    }

    // Verify admin is still active in database
    $user = get_logged_in_user();
    if (!$user || $user['role'] !== 'admin' || $user['status'] !== 'active') {
        session_unset();
        session_destroy();
        set_flash('error', 'Your administrative privileges have been revoked or account is inactive.');
        header('Location: ' . base_url('admin/login.php'));
        exit;
    }
}

// Automatically enforce admin authorization when included in admin pages (except admin/login.php)
$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
if ($currentScript !== 'login.php') {
    require_admin();
}
