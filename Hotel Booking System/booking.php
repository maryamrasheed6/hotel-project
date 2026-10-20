<?php
/**
 * Royal Stay - Hotel Room Booking Page (booking.php)
 * Guest details form, reservation summary, and live price recalculations
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

$pdo = get_db();
$roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

// Fetch Room
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ? AND status = 'available'");
$stmt->execute([$roomId]);
$room = $stmt->fetch();

if (!$room) {
    set_flash('error', 'Please select an available room to begin your reservation.');
    header('Location: ' . base_url('rooms.php'));
    exit;
}

$pageTitle = 'Reserve ' . $room['name'];
$activeNav = 'rooms';

require_once __DIR__ . '/includes/header.php';

// Prefill values
$currentUser = get_logged_in_user();
$guestName = $currentUser ? $currentUser['name'] : '';
$guestEmail = $currentUser ? $currentUser['email'] : '';
$guestPhone = $currentUser ? ($currentUser['phone'] ?? '') : '';

$checkIn = sanitize($_GET['check_in'] ?? date('Y-m-d', strtotime('+1 day')));
$checkOut = sanitize($_GET['check_out'] ?? date('Y-m-d', strtotime('+3 days')));
$guests = max(1, min((int)($_GET['guests'] ?? 2), $room['max_guests']));

$nights = calculate_nights($checkIn, $checkOut);
$subtotal = $nights * $room['price_per_night'];
$taxPercent = (float)get_setting('tax_rate_percent', '10');
$taxAmount = ($subtotal * $taxPercent) / 100;
$totalAmount = $subtotal + $taxAmount;
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1>Secure Room Reservation</h1>
        <p>Complete your booking details below. No upfront payment required.</p>
        <div class="breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <a href="<?= base_url('rooms.php') ?>">Rooms</a>
            <span>/</span>
            <span>Reservation</span>
        </div>
    </div>
</section>

<!-- Booking Main Section -->
<section class="section">
    <div class="container">
        
        <?php if (!$currentUser): ?>
            <div style="background: #ffffff; border-radius: var(--radius-md); padding: 1.25rem 1.75rem; border: 1px solid var(--border); margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fa-solid fa-circle-user" style="font-size: 1.5rem; color: var(--accent);"></i>
                    <span>Already have a Royal Stay account? Sign in for express checkout and booking management.</span>
                </div>
                <a href="<?= base_url('auth.php?redirect=' . urlencode($_SERVER['REQUEST_URI'])) ?>" class="btn btn-outline btn-sm">
                    Sign In
                </a>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('actions/create-booking.php') ?>" method="POST" id="bookingForm">
            <?= csrf_field() ?>
            <input type="hidden" name="room_id" value="<?= $room['id'] ?>">

            <div class="booking-layout">
                
                <!-- Left Column: Forms -->
                <div>
                    
                    <!-- 1. Guest Information -->
                    <div class="form-card">
                        <h3 class="form-card-title">
                            <i class="fa-solid fa-user-pen"></i> Guest Information
                        </h3>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label" for="guestName">Full Name *</label>
                                <input type="text" name="guest_name" id="guestName" class="form-control" value="<?= e($guestName) ?>" placeholder="e.g. John Doe" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="guestEmail">Email Address *</label>
                                <input type="email" name="guest_email" id="guestEmail" class="form-control" value="<?= e($guestEmail) ?>" placeholder="e.g. guest@example.com" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="guestPhone">Contact Phone Number *</label>
                            <input type="tel" name="guest_phone" id="guestPhone" class="form-control" value="<?= e($guestPhone) ?>" placeholder="e.g. +1 (555) 234-5678" required>
                        </div>
                    </div>

                    <!-- 2. Stay Dates & Party -->
                    <div class="form-card" id="bookingCalculator" data-price="<?= $room['price_per_night'] ?>" data-tax="<?= $taxPercent ?>">
                        <h3 class="form-card-title">
                            <i class="fa-solid fa-calendar-days"></i> Reservation Schedule
                        </h3>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label" for="bookCheckIn">Check-in Date *</label>
                                <input type="date" name="check_in" id="bookCheckIn" class="form-control" value="<?= e($checkIn) ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="bookCheckOut">Check-out Date *</label>
                                <input type="date" name="check_out" id="bookCheckOut" class="form-control" value="<?= e($checkOut) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="bookGuests">Number of Guests (Max <?= $room['max_guests'] ?>)</label>
                            <select name="guests" id="bookGuests" class="form-control">
                                <?php for ($g = 1; $g <= $room['max_guests']; $g++): ?>
                                    <option value="<?= $g ?>" <?= $guests === $g ? 'selected' : '' ?>><?= $g ?> Guest<?= $g > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="specialRequests">Special Requests (Optional)</label>
                            <textarea name="special_requests" id="specialRequests" class="form-control" placeholder="Flight arrival details, dietary preferences, celebratory requests, or high-floor room preference..."></textarea>
                        </div>
                    </div>

                    <!-- 3. Payment Method -->
                    <div class="form-card">
                        <h3 class="form-card-title">
                            <i class="fa-solid fa-credit-card"></i> Payment Selection
                        </h3>

                        <div class="payment-options-grid">
                            <label class="payment-radio-card active">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <input type="radio" name="payment_method" value="Pay at Hotel" checked>
                                    <strong style="color: var(--primary);">Pay at Hotel</strong>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0; padding-left: 1.75rem;">
                                    Settle your bill upon arrival via Cash, Visa, MasterCard, or Amex.
                                </p>
                            </label>

                            <label class="payment-radio-card" style="opacity: 0.7; cursor: not-allowed;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <input type="radio" name="payment_method" value="Online Payment" disabled>
                                    <strong>Online Credit Card</strong>
                                    <span class="badge badge-warning" style="font-size: 0.7rem;">Offline</span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0; padding-left: 1.75rem;">
                                    Direct online gateway currently under maintenance. Please use Pay at Hotel.
                                </p>
                            </label>
                        </div>
                    </div>

                    <!-- Terms agreement & submit -->
                    <div style="margin-bottom: 2rem;">
                        <label style="display: flex; align-items: flex-start; gap: 0.6rem; font-size: 0.9rem; cursor: pointer;">
                            <input type="checkbox" name="agree_terms" required checked style="margin-top: 0.25rem;">
                            <span>I agree to Royal Stay's reservation policies, privacy terms, and standard hotel guidelines.</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-gold btn-block btn-lg" id="submitBookingBtn">
                        <i class="fa-solid fa-lock"></i> Confirm & Complete Reservation
                    </button>

                </div>

                <!-- Right Column: Room & Financial Summary -->
                <aside>
                    <div class="booking-sidebar-card">
                        <div style="border-radius: var(--radius-sm); overflow: hidden; height: 180px; margin-bottom: 1.25rem;">
                            <img src="<?= get_room_primary_image($room['id']) ?>" alt="<?= e($room['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>

                        <span class="room-card-type"><?= e($room['room_type']) ?></span>
                        <h3 style="font-size: 1.35rem; color: var(--primary); margin-bottom: 0.5rem;"><?= e($room['name']) ?></h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                            <i class="fa-solid fa-bed" style="color: var(--accent);"></i> <?= e($room['bed_type']) ?> &nbsp;|&nbsp; 
                            <i class="fa-solid fa-ruler-combined" style="color: var(--accent);"></i> <?= e($room['room_size']) ?>
                        </p>

                        <div class="booking-summary-breakdown">
                            <div class="breakdown-row">
                                <span>Room Rate</span>
                                <span><?= format_price($room['price_per_night']) ?> / night</span>
                            </div>
                            <div class="breakdown-row">
                                <span>Duration</span>
                                <span id="calcNights"><?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></span>
                            </div>
                            <div class="breakdown-row">
                                <span>Subtotal</span>
                                <span id="calcSubtotal"><?= format_price($subtotal) ?></span>
                            </div>
                            <div class="breakdown-row">
                                <span>Estimated Taxes (<?= $taxPercent ?>%)</span>
                                <span id="calcTax"><?= format_price($taxAmount) ?></span>
                            </div>
                            <div class="breakdown-row total-row">
                                <span>Total Amount</span>
                                <span id="calcTotal"><?= format_price($totalAmount) ?></span>
                            </div>
                        </div>

                        <div style="padding: 1rem; background: var(--bg); border-radius: var(--radius-sm); font-size: 0.82rem; color: #4b5563;">
                            <div style="font-weight: 600; color: var(--primary); margin-bottom: 0.35rem;">
                                <i class="fa-solid fa-shield-heart" style="color: var(--accent);"></i> Booking Guarantee
                            </div>
                            Instant confirmation voucher sent to your inbox. Free cancellation up to 48 hours prior to check-in.
                        </div>
                    </div>
                </aside>

            </div>
        </form>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
