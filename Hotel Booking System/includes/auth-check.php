<?php
/**
 * Royal Stay - Customer Authentication Helpers
 */

require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is currently logged in
 *
 * @return bool
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an administrator
 *
 * @return bool
 */
function is_admin(): bool {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Fetch current logged-in user details from database
 *
 * @return array|null
 */
function get_logged_in_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }

    static $currentUser = null;
    if ($currentUser === null) {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT id, name, email, phone, role, status FROM users WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch() ?: null;

        if (!$currentUser) {
            // User was deleted or deactivated
            unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name'], $_SESSION['user_email']);
        }
    }

    return $currentUser;
}

/**
 * Require customer login, redirect if not logged in
 *
 * @param string|null $redirectAfter
 */
function require_login(?string $redirectAfter = null): void {
    if (!is_logged_in()) {
        if ($redirectAfter) {
            $_SESSION['redirect_after_login'] = $redirectAfter;
        }
        set_flash('warning', 'Please sign in or register to continue.');
        header('Location: ' . base_url('auth.php'));
        exit;
    }
}
