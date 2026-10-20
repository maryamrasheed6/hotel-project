<?php
/**
 * Royal Stay - Customer Logout
 */

require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset user session data
unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role']);

set_flash('info', 'You have been signed out safely. We look forward to welcoming you back soon.');
header('Location: ' . base_url());
exit;
