<?php
/**
 * Royal Stay - Submit Contact Inquiry Action
 */

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('contact.php'));
    exit;
}

// 1. Verify CSRF
if (!verify_csrf_token()) {
    set_flash('error', 'Security token expired. Please try submitting your inquiry again.');
    header('Location: ' . base_url('contact.php'));
    exit;
}

$name = sanitize($_POST['name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$subject = sanitize($_POST['subject'] ?? 'General Inquiry');
$message = sanitize($_POST['message'] ?? '');

if (empty($name) || !$email || empty($message)) {
    set_flash('error', 'Please complete all required fields with a valid email.');
    header('Location: ' . base_url('contact.php'));
    exit;
}

try {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, subject, message, status, created_at)
        VALUES (?, ?, ?, ?, 'unread', NOW())
    ");
    $stmt->execute([$name, $email, $subject, $message]);

    set_flash('success', "Thank you, {$name}. Your inquiry has been routed to our Head Concierge. We will reply to {$email} promptly.");
    header('Location: ' . base_url('contact.php'));
    exit;

} catch (Exception $e) {
    error_log("Contact submission error: " . $e->getMessage());
    set_flash('error', 'An unexpected error occurred while transmitting your message. Please reach us via phone.');
    header('Location: ' . base_url('contact.php'));
    exit;
}
