<?php
/**
 * Royal Stay - Submit Review Action
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('reviews.php'));
    exit;
}

// 1. Verify CSRF
if (!verify_csrf_token()) {
    set_flash('error', 'Security token expired. Please try submitting again.');
    header('Location: ' . base_url('reviews.php'));
    exit;
}

$pdo = get_db();
$currentUser = get_logged_in_user();

$guestName = sanitize($_POST['guest_name'] ?? '');
$rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
$reviewText = sanitize($_POST['review_text'] ?? '');
$bookingId = !empty($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;
$userId = $currentUser ? (int)$currentUser['id'] : null;

if (empty($guestName) || empty($reviewText)) {
    set_flash('error', 'Please provide both your name and review remarks.');
    header('Location: ' . base_url('reviews.php'));
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO reviews (user_id, booking_id, guest_name, rating, review_text, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'approved', NOW())
    ");
    $stmt->execute([$userId, $bookingId, $guestName, $rating, $reviewText]);

    set_flash('success', 'Thank you for your valuable feedback! Your review has been published.');
    header('Location: ' . base_url('reviews.php'));
    exit;

} catch (Exception $e) {
    error_log("Review error: " . $e->getMessage());
    set_flash('error', 'An error occurred while recording your review. Please try again.');
    header('Location: ' . base_url('reviews.php'));
    exit;
}
