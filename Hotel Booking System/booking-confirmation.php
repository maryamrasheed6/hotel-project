<?php
/**
 * Royal Stay - Booking Confirmation & Printable Reservation Voucher
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

$pdo = get_db();
$ref = sanitize($_GET['ref'] ?? '');

if (empty($ref)) {
    set_flash('error', 'No booking reference was provided.');
    header('Location: ' . base_url('rooms.php'));
    exit;
}

// Fetch booking with room details
$stmt = $pdo->prepare("
    SELECT b.*, r.name AS room_name, r.slug AS room_slug, r.room_type, r.bed_type, r.room_size,
           ri.image_path AS room_image
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    LEFT JOIN room_images ri ON r.id = ri.room_id AND ri.is_primary = 1
    WHERE b.booking_reference = ?
    LIMIT 1
");
$stmt->execute([$ref]);
$booking = $stmt->fetch();

if (!$booking) {
    set_flash('error', 'Reservation not found. Please verify your reference number.');
    header('Location: ' . base_url('rooms.php'));
    exit;
}

$pageTitle = 'Reservation Confirmation #' . $booking['booking_reference'];
$activeNav = 'rooms';

require_once __DIR__ . '/includes/header.php';

$hotelName = get_setting('hotel_name', 'Royal Stay Luxury Hotel & Resort');
$hotelAddress = get_setting('hotel_address', '100 Royal Palm Boulevard, Marina Bay, Suite 800');
$hotelPhone = get_setting('contact_phone', '+1 (800) 789-7829');
$hotelEmail = get_setting('contact_email', 'concierge@royalstay.com');
?>

<div class="page-banner">
    <div class="container">
        <h1>Reservation Confirmed</h1>
        <p>Your luxury stay at <?= e($hotelName) ?> has been successfully secured.</p>
        <div class="breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <a href="<?= base_url('rooms.php') ?>">Rooms</a>
            <span>/</span>
            <span>Confirmation</span>
        </div>
    </div>
</div>

<div class="section">
    <div class="container">
        
        <div style="max-width: 900px; margin: 0 auto;">
            
            <!-- Success Notification Card -->
            <div style="background: linear-gradient(135deg, #063B3E 0%, #0F2D30 100%); color: #ffffff; border-radius: var(--radius-lg); padding: 2.5rem; text-align: center; margin-bottom: 2.5rem; box-shadow: var(--shadow-lg); position: relative; overflow: hidden;">
                <div style="position: absolute; right: -20px; bottom: -20px; font-size: 10rem; color: rgba(255,255,255,0.03); pointer-events: none;">
                    <i class="fa-solid fa-crown"></i>
                </div>
                
                <div style="width: 72px; height: 72px; background: var(--accent); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.5rem auto; box-shadow: 0 0 25px rgba(217, 164, 65, 0.4);">
                    <i class="fa-solid fa-check"></i>
                </div>

                <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; color: #ffffff; margin-bottom: 0.5rem;">
                    Thank You, <?= e($booking['guest_name']) ?>!
                </h2>
                <p style="color: rgba(255, 255, 255, 0.85); font-size: 1.05rem; max-width: 600px; margin: 0 auto 1.75rem auto;">
                    We are honored to welcome you. An electronic voucher and receipt have been dispatched to <strong><?= e($booking['guest_email']) ?></strong>.
                </p>

                <div style="display: inline-flex; align-items: center; gap: 1rem; background: rgba(255, 255, 255, 0.1); border: 1px dashed rgba(217, 164, 65, 0.6); padding: 0.75rem 1.5rem; border-radius: var(--radius-md);">
                    <span style="color: rgba(255, 255, 255, 0.7); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Booking Reference:</span>
                    <strong style="color: var(--accent); font-size: 1.35rem; letter-spacing: 1.5px;"><?= e($booking['booking_reference']) ?></strong>
                    <button type="button" class="btn btn-outline btn-sm" onclick="navigator.clipboard.writeText('<?= e($booking['booking_reference']) ?>'); alert('Reference copied to clipboard!');" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; border-color: rgba(255,255,255,0.3); color: #fff;">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
            </div>

            <!-- Action Toolbar (Hidden in Print) -->
            <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
                <a href="<?= base_url('rooms.php') ?>" class="btn btn-outline">
                    <i class="fa-solid fa-arrow-left"></i> Explore More Suites
                </a>
                
                <div style="display: flex; gap: 1rem;">
                    <?php if (is_logged_in()): ?>
                        <a href="<?= base_url('my-bookings.php') ?>" class="btn btn-outline">
                            <i class="fa-solid fa-calendar-check"></i> My Stays
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-gold" onclick="window.print();">
                        <i class="fa-solid fa-print"></i> Print Voucher
                    </button>
                </div>
            </div>

            <!-- Printable Voucher Document -->
            <div class="printable-voucher" style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-md); padding: 3rem; margin-bottom: 2.5rem;">
                
                <!-- Voucher Header -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--border-light); padding-bottom: 2rem; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 50px; height: 50px; background: var(--primary); color: var(--accent); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                            <i class="fa-solid fa-crown"></i>
                        </div>
                        <div>
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--primary); margin: 0;"><?= e($hotelName) ?></h3>
                            <span style="color: var(--text-muted); font-size: 0.88rem;"><?= e($hotelAddress) ?></span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span class="status-pill status-<?= e($booking['booking_status']) ?>" style="font-size: 0.85rem; padding: 0.35rem 1rem; margin-bottom: 0.5rem; display: inline-block;">
                            Status: <?= ucfirst(e($booking['booking_status'])) ?>
                        </span>
                        <div style="color: var(--text-muted); font-size: 0.82rem;">
                            Booked on: <?= date('M d, Y h:i A', strtotime($booking['created_at'])) ?>
                        </div>
                    </div>
                </div>

                <!-- Stay Summary Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem; background: var(--bg-surface); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 2.5rem; border: 1px solid var(--border);">
                    <div>
                        <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.3rem;">Check-In Date</div>
                        <div style="font-size: 1.15rem; font-weight: 700; color: var(--primary);">
                            <?= date('D, M d, Y', strtotime($booking['check_in'])) ?>
                        </div>
                        <span style="font-size: 0.78rem; color: var(--text-muted);">From 3:00 PM</span>
                    </div>

                    <div>
                        <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.3rem;">Check-Out Date</div>
                        <div style="font-size: 1.15rem; font-weight: 700; color: var(--primary);">
                            <?= date('D, M d, Y', strtotime($booking['check_out'])) ?>
                        </div>
                        <span style="font-size: 0.78rem; color: var(--text-muted);">Until 11:00 AM</span>
                    </div>

                    <div>
                        <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.3rem;">Duration & Guests</div>
                        <div style="font-size: 1.15rem; font-weight: 700; color: var(--primary);">
                            <?= $booking['nights'] ?> <?= $booking['nights'] > 1 ? 'Nights' : 'Night' ?>
                        </div>
                        <span style="font-size: 0.78rem; color: var(--text-muted);"><?= $booking['guests'] ?> <?= $booking['guests'] > 1 ? 'Guests' : 'Guest' ?></span>
                    </div>

                    <div>
                        <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.3rem;">Payment Mode</div>
                        <div style="font-size: 1.15rem; font-weight: 700; color: var(--primary);">
                            <?= e($booking['payment_method']) ?>
                        </div>
                        <span class="status-pill status-<?= e($booking['payment_status']) ?>" style="font-size: 0.72rem; padding: 0.15rem 0.5rem; margin-top: 0.25rem;">
                            <?= ucfirst(e($booking['payment_status'])) ?>
                        </span>
                    </div>
                </div>

                <!-- Reserved Room Overview -->
                <div style="margin-bottom: 2.5rem;">
                    <h4 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; color: var(--primary); margin-bottom: 1.25rem;">
                        Reserved Suite
                    </h4>
                    <div style="display: flex; gap: 1.5rem; align-items: center; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.25rem; flex-wrap: wrap;">
                        <img src="<?= e(base_url($booking['room_image'] ?? 'assets/images/room_default.jpg')) ?>" alt="<?= e($booking['room_name']) ?>" style="width: 140px; height: 100px; border-radius: var(--radius-md); object-fit: cover;">
                        <div style="flex: 1;">
                            <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--accent); font-weight: 600; letter-spacing: 0.5px;"><?= e($booking['room_type']) ?></div>
                            <h4 style="font-size: 1.2rem; font-weight: 700; color: var(--primary); margin: 0.25rem 0 0.5rem 0;"><?= e($booking['room_name']) ?></h4>
                            <div style="display: flex; gap: 1.25rem; font-size: 0.85rem; color: var(--text-muted);">
                                <span><i class="fa-solid fa-bed" style="color: var(--accent);"></i> <?= e($booking['bed_type']) ?></span>
                                <span><i class="fa-solid fa-maximize" style="color: var(--accent);"></i> <?= e($booking['room_size']) ?></span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Nightly Rate</div>
                            <div style="font-size: 1.3rem; font-weight: 700; color: var(--primary);"><?= format_price($booking['price_per_night']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Guest Details & Special Requests -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem;" class="voucher-details-grid">
                    <div>
                        <h4 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--primary); margin-bottom: 1rem;">
                            Guest Particulars
                        </h4>
                        <table style="width: 100%; font-size: 0.9rem;">
                            <tr>
                                <td style="padding: 0.4rem 0; color: var(--text-muted); width: 40%;">Primary Guest:</td>
                                <td style="padding: 0.4rem 0; font-weight: 600; color: var(--text-main);"><?= e($booking['guest_name']) ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 0.4rem 0; color: var(--text-muted);">Email Address:</td>
                                <td style="padding: 0.4rem 0; color: var(--text-main);"><?= e($booking['guest_email']) ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 0.4rem 0; color: var(--text-muted);">Contact Phone:</td>
                                <td style="padding: 0.4rem 0; color: var(--text-main);"><?= e($booking['guest_phone']) ?></td>
                            </tr>
                        </table>
                    </div>

                    <div>
                        <h4 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--primary); margin-bottom: 1rem;">
                            Special Requests & Notes
                        </h4>
                        <div style="background: var(--bg-surface); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border); font-size: 0.88rem; color: var(--text-muted); min-height: 80px;">
                            <?= !empty($booking['special_requests']) ? nl2br(e($booking['special_requests'])) : 'No special requests noted for this reservation.' ?>
                        </div>
                    </div>
                </div>

                <!-- Price Invoice Breakdown -->
                <div style="border-top: 2px dashed var(--border); padding-top: 2rem; margin-bottom: 2rem;">
                    <h4 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--primary); margin-bottom: 1.25rem;">
                        Billing & Payment Statement
                    </h4>

                    <div style="max-width: 450px; margin-left: auto;">
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.92rem; color: var(--text-muted);">
                            <span><?= format_price($booking['price_per_night']) ?> &times; <?= $booking['nights'] ?> <?= $booking['nights'] > 1 ? 'Nights' : 'Night' ?></span>
                            <span style="font-weight: 600; color: var(--text-main);"><?= format_price($booking['subtotal']) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.92rem; color: var(--text-muted);">
                            <span>Taxes & Resort Hospitality Fee</span>
                            <span style="font-weight: 600; color: var(--text-main);"><?= format_price($booking['additional_charges']) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 1rem 0 0.5rem 0; margin-top: 0.5rem; border-top: 2px solid var(--primary); font-size: 1.25rem; font-weight: 700; color: var(--primary);">
                            <span>Total Payable Amount</span>
                            <span style="color: var(--accent);"><?= format_price($booking['total_amount']) ?></span>
                        </div>
                        <div style="text-align: right; font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">
                            <?= $booking['payment_status'] === 'paid' ? 'Paid in full.' : 'Due upon arrival at hotel reception in cash or card.' ?>
                        </div>
                    </div>
                </div>

                <!-- Policies & Assistance -->
                <div style="background: #FFFDF9; border: 1px solid rgba(217, 164, 65, 0.3); border-radius: var(--radius-md); padding: 1.5rem; margin-top: 2rem;">
                    <h5 style="color: var(--accent-dark); font-size: 0.95rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-circle-info"></i> Arrival & Concierge Guidelines
                    </h5>
                    <ul style="font-size: 0.85rem; color: var(--text-muted); margin: 0; padding-left: 1.25rem; line-height: 1.6;">
                        <li>Check-in begins at 3:00 PM. Early check-in is subject to suite availability upon arrival.</li>
                        <li>Please present a valid government-issued photo ID and credit card for incidentals at check-in.</li>
                        <li>Complimentary valet parking and welcome drinks await you at the grand porte-cochère.</li>
                        <li>For bespoke concierge requests, limousine airport transfers, or queries, call <strong><?= e($hotelPhone) ?></strong> or email <strong><?= e($hotelEmail) ?></strong>.</li>
                    </ul>
                </div>

            </div>

        </div>

    </div>
</div>

<style>
@media print {
    .site-header, .site-footer, .page-banner, .flash-container, .no-print {
        display: none !important;
    }
    body {
        background: #ffffff !important;
        color: #000000 !important;
    }
    .printable-voucher {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
    }
}
@media (max-width: 768px) {
    .voucher-details-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
