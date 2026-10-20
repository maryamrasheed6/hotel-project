<?php
/**
 * Royal Stay - Admin Booking Actions Controller
 */

require_once __DIR__ . '/../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/bookings.php'));
    exit;
}

if (!verify_csrf_token()) {
    set_flash('error', 'Security token expired. Please try again.');
    header('Location: ' . base_url('admin/bookings.php'));
    exit;
}

$pdo = get_db();
$adminUser = get_logged_in_user();
$action = sanitize($_POST['action'] ?? 'update_status');

if ($action === 'update_status') {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $newStatus = sanitize($_POST['booking_status'] ?? '');
    $newPaymentStatus = sanitize($_POST['payment_status'] ?? '');
    $note = sanitize($_POST['admin_note'] ?? '');

    $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    $validPayment = ['unpaid', 'paid', 'refunded'];

    if (!in_array($newStatus, $validStatuses) || !in_array($newPaymentStatus, $validPayment)) {
        set_flash('error', 'Invalid status selections provided.');
        header('Location: ' . base_url('admin/bookings.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? FOR UPDATE");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch();

        if (!$booking) {
            $pdo->rollBack();
            set_flash('error', 'Reservation record not found.');
            header('Location: ' . base_url('admin/bookings.php'));
            exit;
        }

        $oldStatus = $booking['booking_status'];

        $updateStmt = $pdo->prepare("
            UPDATE bookings 
            SET booking_status = :status, payment_status = :pay_status 
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':status'     => $newStatus,
            ':pay_status' => $newPaymentStatus,
            ':id'         => $bookingId
        ]);

        // If status changed or note provided, log history
        if ($oldStatus !== $newStatus || !empty($note)) {
            $adminName = $adminUser ? $adminUser['name'] : 'Admin';
            log_booking_status_change($bookingId, $oldStatus, $newStatus, "Admin: {$adminName}", $note ?: "Status updated to {$newStatus} (Payment: {$newPaymentStatus}).");
        }

        $pdo->commit();

        set_flash('success', "Reservation #{$booking['booking_reference']} updated successfully.");
        header('Location: ' . base_url('admin/bookings.php'));
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Admin booking action error: " . $e->getMessage());
        set_flash('error', 'Failed to update booking status.');
        header('Location: ' . base_url('admin/bookings.php'));
        exit;
    }
}

header('Location: ' . base_url('admin/bookings.php'));
exit;
