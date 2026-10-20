<?php
/**
 * Royal Stay - Cancel Reservation Action
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('my-bookings.php'));
    exit;
}

// 1. Verify CSRF
if (!verify_csrf_token()) {
    set_flash('error', 'Security token expired. Please try again.');
    header('Location: ' . base_url('my-bookings.php'));
    exit;
}

$ref = sanitize($_POST['booking_reference'] ?? '');
if (empty($ref)) {
    set_flash('error', 'Invalid booking reference.');
    header('Location: ' . base_url('my-bookings.php'));
    exit;
}

$pdo = get_db();
$currentUser = get_logged_in_user();

try {
    $pdo->beginTransaction();

    // Fetch booking
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_reference = ? FOR UPDATE");
    $stmt->execute([$ref]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $pdo->rollBack();
        set_flash('error', 'Booking not found.');
        header('Location: ' . base_url('my-bookings.php'));
        exit;
    }

    // Permission check: must be owner or admin
    if ($currentUser) {
        if ($currentUser['role'] !== 'admin' && (int)$booking['user_id'] !== (int)$currentUser['id'] && $booking['guest_email'] !== $currentUser['email']) {
            $pdo->rollBack();
            set_flash('error', 'Unauthorized access to cancel this reservation.');
            header('Location: ' . base_url('my-bookings.php'));
            exit;
        }
    }

    // Status check
    if (!in_array($booking['booking_status'], ['pending', 'confirmed'])) {
        $pdo->rollBack();
        set_flash('error', 'This reservation cannot be cancelled as it is already ' . $booking['booking_status'] . '.');
        header('Location: ' . base_url('my-bookings.php'));
        exit;
    }

    $oldStatus = $booking['booking_status'];

    // Update status to cancelled
    $updateStmt = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE id = ?");
    $updateStmt->execute([$booking['id']]);

    // Log history
    $changedBy = $currentUser ? "Customer: {$currentUser['name']}" : "Guest: {$booking['guest_name']}";
    log_booking_status_change($booking['id'], $oldStatus, 'cancelled', $changedBy, 'Customer requested booking cancellation online.');

    $pdo->commit();

    set_flash('success', "Reservation {$ref} has been successfully cancelled.");
    header('Location: ' . base_url('my-bookings.php'));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Cancellation error: " . $e->getMessage());
    set_flash('error', 'An error occurred while attempting cancellation. Please contact reception.');
    header('Location: ' . base_url('my-bookings.php'));
    exit;
}
