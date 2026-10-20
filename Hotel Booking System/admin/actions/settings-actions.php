<?php
/**
 * Royal Stay - Admin Hotel Settings Action Controller
 */

require_once __DIR__ . '/../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/settings.php'));
    exit;
}

if (!verify_csrf_token()) {
    set_flash('error', 'Security token expired. Please try again.');
    header('Location: ' . base_url('admin/settings.php'));
    exit;
}

$pdo = get_db();

// Allowed editable setting keys
$allowedKeys = [
    'hotel_name',
    'hotel_tagline',
    'contact_email',
    'contact_phone',
    'hotel_address',
    'currency_symbol',
    'currency_code',
    'tax_rate_percent',
    'default_checkin_time',
    'default_checkout_time',
    'facebook_url',
    'instagram_url',
    'twitter_url',
    'linkedin_url'
];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO hotel_settings (setting_key, setting_value, updated_at)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
    ");

    foreach ($allowedKeys as $key) {
        if (isset($_POST[$key])) {
            $val = sanitize($_POST[$key]);
            $stmt->execute([$key, $val]);
        }
    }

    $pdo->commit();

    set_flash('success', 'Resort configuration and hotel settings updated successfully.');
    header('Location: ' . base_url('admin/settings.php'));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Settings update error: " . $e->getMessage());
    set_flash('error', 'Failed to update hotel settings: ' . $e->getMessage());
    header('Location: ' . base_url('admin/settings.php'));
    exit;
}
