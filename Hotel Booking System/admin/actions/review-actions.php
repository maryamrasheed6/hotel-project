<?php
/**
 * Royal Stay - Admin Review Moderation Actions Controller
 */

require_once __DIR__ . '/../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/reviews.php'));
    exit;
}

if (!verify_csrf_token()) {
    set_flash('error', 'Security token expired. Please try again.');
    header('Location: ' . base_url('admin/reviews.php'));
    exit;
}

$pdo = get_db();
$action = sanitize($_POST['action'] ?? '');
$reviewId = (int)($_POST['review_id'] ?? 0);

if ($reviewId <= 0) {
    set_flash('error', 'Invalid review record.');
    header('Location: ' . base_url('admin/reviews.php'));
    exit;
}

if ($action === 'approve') {
    $stmt = $pdo->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
    $stmt->execute([$reviewId]);
    set_flash('success', 'Review has been approved and published to the website.');
} elseif ($action === 'reject') {
    $stmt = $pdo->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$reviewId]);
    set_flash('info', 'Review has been rejected and hidden from public display.');
} elseif ($action === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$reviewId]);
    set_flash('success', 'Review deleted from database.');
}

header('Location: ' . base_url('admin/reviews.php'));
exit;
