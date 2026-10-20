<?php
/**
 * Royal Stay - Admin Contact Messages Action Controller
 */

require_once __DIR__ . '/../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/messages.php'));
    exit;
}

if (!verify_csrf_token()) {
    set_flash('error', 'Security token expired. Please try again.');
    header('Location: ' . base_url('admin/messages.php'));
    exit;
}

$pdo = get_db();
$action = sanitize($_POST['action'] ?? '');
$messageId = (int)($_POST['message_id'] ?? 0);

if ($messageId <= 0) {
    set_flash('error', 'Invalid message reference.');
    header('Location: ' . base_url('admin/messages.php'));
    exit;
}

if ($action === 'toggle_read') {
    $stmt = $pdo->prepare("SELECT status FROM contact_messages WHERE id = ?");
    $stmt->execute([$messageId]);
    $currentStatus = $stmt->fetchColumn();

    $newStatus = $currentStatus === 'unread' ? 'read' : 'unread';
    $upd = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
    $upd->execute([$newStatus, $messageId]);

    set_flash('success', "Message marked as {$newStatus}.");
} elseif ($action === 'delete') {
    $del = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $del->execute([$messageId]);

    set_flash('success', 'Inquiry deleted successfully.');
}

header('Location: ' . base_url('admin/messages.php'));
exit;
