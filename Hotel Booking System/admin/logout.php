<?php
/**
 * Royal Stay - Admin Sign Out
 */

require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role']);

set_flash('info', 'Administrator signed out safely.');
header('Location: ' . base_url('admin/login.php'));
exit;
