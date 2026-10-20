<?php
/**
 * Royal Stay - Admin Hotel Settings (admin/settings.php)
 */

$adminPageTitle = 'Resort Configuration';
$activeAdminNav = 'settings';

require_once __DIR__ . '/includes/admin-header.php';

$pdo = get_db();

// Fetch all settings
$settingsStmt = $pdo->query("SELECT setting_key, setting_value FROM hotel_settings");
$settings = $settingsStmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

function s($key, $default = '', $settingsArr = []) {
    return e($settingsArr[$key] ?? $default);
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">
            <i class="fa-solid fa-sliders" style="color: var(--admin-accent);"></i> Hotel Settings & Configuration
        </h3>
        <span style="font-size: 0.82rem; color: var(--admin-text-muted);">Manage brand identity, concierge contacts, and fiscal policies</span>
    </div>

    <div class="admin-card-body">
        
        <form action="<?= base_url('admin/actions/settings-actions.php') ?>" method="POST">
            <?= csrf_field() ?>

            <!-- 1. General Brand Identity -->
            <div style="margin-bottom: 2.5rem;">
                <h4 style="font-size: 1.1rem; color: var(--admin-sidebar); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.75rem;">
                    <i class="fa-solid fa-crown" style="color: var(--admin-accent);"></i> Resort Identity
                </h4>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="hotel_name">Resort / Property Name *</label>
                        <input type="text" name="hotel_name" id="hotel_name" class="form-control" value="<?= s('hotel_name', 'Royal Stay Luxury Hotel & Resort', $settings) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="hotel_tagline">Brand Tagline *</label>
                        <input type="text" name="hotel_tagline" id="hotel_tagline" class="form-control" value="<?= s('hotel_tagline', 'Luxury Stay Starts Here', $settings) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="hotel_address">Physical Address *</label>
                    <input type="text" name="hotel_address" id="hotel_address" class="form-control" value="<?= s('hotel_address', '100 Royal Palm Boulevard, Marina Bay, Suite 800', $settings) ?>" required>
                </div>
            </div>

            <!-- 2. Contact & Operation Hours -->
            <div style="margin-bottom: 2.5rem;">
                <h4 style="font-size: 1.1rem; color: var(--admin-sidebar); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.75rem;">
                    <i class="fa-solid fa-bell-concierge" style="color: var(--admin-accent);"></i> Concierge & Arrival Hours
                </h4>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="contact_email">Concierge / Inquiries Email *</label>
                        <input type="email" name="contact_email" id="contact_email" class="form-control" value="<?= s('contact_email', 'concierge@royalstay.com', $settings) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_phone">Concierge Hotline Phone *</label>
                        <input type="text" name="contact_phone" id="contact_phone" class="form-control" value="<?= s('contact_phone', '+1 (800) 789-7829', $settings) ?>" required>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="default_checkin_time">Standard Check-In Time</label>
                        <input type="text" name="default_checkin_time" id="default_checkin_time" class="form-control" value="<?= s('default_checkin_time', '15:00', $settings) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="default_checkout_time">Standard Check-Out Time</label>
                        <input type="text" name="default_checkout_time" id="default_checkout_time" class="form-control" value="<?= s('default_checkout_time', '11:00', $settings) ?>">
                    </div>
                </div>
            </div>

            <!-- 3. Financial & Tax Policies -->
            <div style="margin-bottom: 2.5rem;">
                <h4 style="font-size: 1.1rem; color: var(--admin-sidebar); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.75rem;">
                    <i class="fa-solid fa-coins" style="color: var(--admin-accent);"></i> Pricing & Fiscal Parameters
                </h4>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="form-label" for="currency_symbol">Currency Symbol</label>
                        <input type="text" name="currency_symbol" id="currency_symbol" class="form-control" value="<?= s('currency_symbol', '$', $settings) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="currency_code">Currency Code (ISO)</label>
                        <input type="text" name="currency_code" id="currency_code" class="form-control" value="<?= s('currency_code', 'USD', $settings) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tax_rate_percent">Tax & Resort Fee Rate (%)</label>
                        <input type="number" step="0.01" name="tax_rate_percent" id="tax_rate_percent" class="form-control" value="<?= s('tax_rate_percent', '10.00', $settings) ?>" required>
                    </div>
                </div>
            </div>

            <!-- 4. Social Links -->
            <div style="margin-bottom: 2.5rem;">
                <h4 style="font-size: 1.1rem; color: var(--admin-sidebar); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.75rem;">
                    <i class="fa-solid fa-share-nodes" style="color: var(--admin-accent);"></i> Social & Web Channels
                </h4>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="facebook_url">Facebook URL</label>
                        <input type="url" name="facebook_url" id="facebook_url" class="form-control" value="<?= s('facebook_url', 'https://facebook.com/royalstay', $settings) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="instagram_url">Instagram URL</label>
                        <input type="url" name="instagram_url" id="instagram_url" class="form-control" value="<?= s('instagram_url', 'https://instagram.com/royalstay', $settings) ?>">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="twitter_url">Twitter / X URL</label>
                        <input type="url" name="twitter_url" id="twitter_url" class="form-control" value="<?= s('twitter_url', 'https://twitter.com/royalstay', $settings) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="linkedin_url">LinkedIn URL</label>
                        <input type="url" name="linkedin_url" id="linkedin_url" class="form-control" value="<?= s('linkedin_url', 'https://linkedin.com/company/royalstay', $settings) ?>">
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--admin-border); padding-top: 1.5rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-gold" style="padding: 0.85rem 2rem; font-size: 0.95rem;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Resort Settings
                </button>
            </div>
        </form>

    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
