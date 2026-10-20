<?php
/**
 * Royal Stay - Room Details Page (room-details.php)
 * Dynamic room showcase, interactive image gallery, and live date availability checker
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$roomId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Room
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ? AND status = 'available'");
$stmt->execute([$roomId]);
$room = $stmt->fetch();

if (!$room) {
    set_flash('error', 'The requested room could not be found or is currently not available.');
    header('Location: ' . base_url('rooms.php'));
    exit;
}

$pageTitle = $room['name'];
$metaDescription = substr(strip_tags($room['description']), 0, 155);
$activeNav = 'rooms';

require_once __DIR__ . '/includes/header.php';

// Fetch Room Images
$images = get_room_images($room['id']);
$primaryImage = $images[0]['image_path'] ?? 'assets/images/room_default.jpg';

// Fetch Facilities
$facilities = get_room_facilities($room['id']);

// Date check parameters
$checkIn = sanitize($_GET['check_in'] ?? date('Y-m-d', strtotime('+1 day')));
$checkOut = sanitize($_GET['check_out'] ?? date('Y-m-d', strtotime('+3 days')));
$guests = max(1, min((int)($_GET['guests'] ?? 2), $room['max_guests']));

// Check availability for these dates
$isAvailable = null;
$availabilityMessage = '';
if (!empty($checkIn) && !empty($checkOut)) {
    if (strtotime($checkIn) < strtotime(date('Y-m-d'))) {
        $isAvailable = false;
        $availabilityMessage = 'Check-in date cannot be in the past.';
    } elseif (strtotime($checkIn) >= strtotime($checkOut)) {
        $isAvailable = false;
        $availabilityMessage = 'Check-out date must be after check-in date.';
    } else {
        $isAvailable = is_room_available($room['id'], $checkIn, $checkOut);
        if ($isAvailable) {
            $availabilityMessage = 'Great news! This room is available for your selected dates.';
        } else {
            $availabilityMessage = 'This room has already been reserved for some or all of the selected dates. Please choose different dates.';
        }
    }
}

// Calculate pricing
$nights = calculate_nights($checkIn, $checkOut);
$subtotal = $nights * $room['price_per_night'];
$taxPercent = (float)get_setting('tax_rate_percent', '10');
$taxAmount = ($subtotal * $taxPercent) / 100;
$totalAmount = $subtotal + $taxAmount;

// Related Rooms
$relatedStmt = $pdo->prepare("SELECT * FROM rooms WHERE id != ? AND status = 'available' ORDER BY RAND() LIMIT 3");
$relatedStmt->execute([$room['id']]);
$relatedRooms = $relatedStmt->fetchAll();
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <span class="section-subtitle" style="color: #FFE6A8;"><?= e($room['room_type']) ?></span>
        <h1><?= e($room['name']) ?></h1>
        <div class="breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <a href="<?= base_url('rooms.php') ?>">Rooms</a>
            <span>/</span>
            <span><?= e($room['name']) ?></span>
        </div>
    </div>
</section>

<!-- Room Details Main Section -->
<section class="section">
    <div class="container">
        <div class="room-details-grid">
            
            <!-- Left Column: Media & Info -->
            <div class="room-details-content">
                
                <!-- Interactive Image Gallery -->
                <div class="room-gallery">
                    <div class="gallery-main">
                        <img id="mainGalleryImage" src="<?= base_url($primaryImage) ?>" alt="<?= e($room['name']) ?>">
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="gallery-thumbnails">
                            <?php foreach ($images as $idx => $img): ?>
                                <div class="gallery-thumb <?= $idx === 0 ? 'active' : '' ?>" data-full="<?= base_url($img['image_path']) ?>">
                                    <img src="<?= base_url($img['image_path']) ?>" alt="Room photo <?= $idx + 1 ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Room Key Specifications -->
                <div class="room-meta-strip">
                    <div class="meta-box">
                        <i class="fa-solid fa-user-group"></i>
                        <div class="meta-label">Capacity</div>
                        <div class="meta-value">Up to <?= $room['max_guests'] ?> Guests</div>
                    </div>
                    <div class="meta-box">
                        <i class="fa-solid fa-bed"></i>
                        <div class="meta-label">Bed Type</div>
                        <div class="meta-value"><?= e($room['bed_type']) ?></div>
                    </div>
                    <div class="meta-box">
                        <i class="fa-solid fa-ruler-combined"></i>
                        <div class="meta-label">Dimensions</div>
                        <div class="meta-value"><?= e($room['room_size']) ?></div>
                    </div>
                    <div class="meta-box">
                        <i class="fa-solid fa-circle-check"></i>
                        <div class="meta-label">Status</div>
                        <div class="meta-value" style="color: var(--success); text-transform: capitalize;"><?= e($room['status']) ?></div>
                    </div>
                </div>

                <!-- Description -->
                <div style="margin-bottom: 3rem;">
                    <h2 style="font-size: 1.85rem; margin-bottom: 1rem; color: var(--primary);">Suite Overview</h2>
                    <p style="font-size: 1.05rem; line-height: 1.8; color: #374151;">
                        <?= nl2br(e($room['description'])) ?>
                    </p>
                </div>

                <!-- Amenities & Facilities -->
                <div style="margin-bottom: 3rem;">
                    <h2 style="font-size: 1.85rem; margin-bottom: 1.25rem; color: var(--primary);">Included Amenities</h2>
                    <div class="amenities-badges-grid">
                        <?php foreach ($facilities as $fac): ?>
                            <div class="amenity-pill">
                                <i class="<?= e($fac['icon']) ?>"></i>
                                <span><?= e($fac['name']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Hotel Policies -->
                <div style="background: #ffffff; border-radius: var(--radius-md); padding: 2rem; border: 1px solid var(--border);">
                    <h3 style="font-size: 1.4rem; margin-bottom: 1rem; color: var(--secondary);">Reservation & Stay Policies</h3>
                    <ul style="display: flex; flex-direction: column; gap: 0.6rem; color: #4b5563; font-size: 0.9rem;">
                        <li><i class="fa-solid fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i> Check-in begins at <strong><?= e(get_setting('default_checkin_time', '15:00')) ?></strong>. Early check-in available upon request.</li>
                        <li><i class="fa-solid fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i> Check-out time is until <strong><?= e(get_setting('default_checkout_time', '11:00')) ?></strong>.</li>
                        <li><i class="fa-solid fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i> Complimentary cancellation up to 48 hours prior to arrival.</li>
                        <li><i class="fa-solid fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i> Strictly 100% non-smoking luxury suites.</li>
                    </ul>
                </div>

            </div>

            <!-- Right Column: Live Booking & Availability Box -->
            <aside class="booking-sidebar">
                <div class="booking-sidebar-card" id="bookingCalculator" data-price="<?= $room['price_per_night'] ?>" data-tax="<?= $taxPercent ?>">
                    <div class="sidebar-price-header">
                        <div>
                            <span class="price"><?= format_price($room['price_per_night']) ?></span>
                            <span class="period">/ night</span>
                        </div>
                        <span class="badge badge-gold">Best Rate Guaranteed</span>
                    </div>

                    <!-- Date & Availability Form -->
                    <form action="<?= base_url('room-details.php') ?>" method="GET">
                        <input type="hidden" name="id" value="<?= $room['id'] ?>">

                        <div class="form-group">
                            <label class="form-label" for="calcCheckIn">Check-in Date</label>
                            <input type="date" name="check_in" id="calcCheckIn" class="form-control" value="<?= e($checkIn) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="calcCheckOut">Check-out Date</label>
                            <input type="date" name="check_out" id="calcCheckOut" class="form-control" value="<?= e($checkOut) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="calcGuests">Guests (Max <?= $room['max_guests'] ?>)</label>
                            <select name="guests" id="calcGuests" class="form-control">
                                <?php for ($g = 1; $g <= $room['max_guests']; $g++): ?>
                                    <option value="<?= $g ?>" <?= $guests === $g ? 'selected' : '' ?>><?= $g ?> Guest<?= $g > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-outline btn-block btn-sm" style="margin-bottom: 1.25rem;">
                            <i class="fa-solid fa-calendar-check"></i> Check Availability
                        </button>
                    </form>

                    <!-- Availability Notice -->
                    <?php if ($isAvailable !== null): ?>
                        <div style="padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.88rem; display: flex; align-items: flex-start; gap: 0.6rem; <?= $isAvailable ? 'background: var(--success-bg); color: var(--success);' : 'background: var(--error-bg); color: var(--error);' ?>">
                            <i class="fa-solid <?= $isAvailable ? 'fa-circle-check' : 'fa-circle-xmark' ?>" style="margin-top: 0.2rem;"></i>
                            <span><?= e($availabilityMessage) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Price Breakdown Summary -->
                    <div class="booking-summary-breakdown">
                        <div class="breakdown-row">
                            <span><?= format_price($room['price_per_night']) ?> &times; <span id="calcNights"><?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></span></span>
                            <span id="calcSubtotal"><?= format_price($subtotal) ?></span>
                        </div>
                        <div class="breakdown-row">
                            <span>Estimated Taxes & Fees (<?= $taxPercent ?>%)</span>
                            <span id="calcTax"><?= format_price($taxAmount) ?></span>
                        </div>
                        <div class="breakdown-row total-row">
                            <span>Estimated Total</span>
                            <span id="calcTotal"><?= format_price($totalAmount) ?></span>
                        </div>
                    </div>

                    <!-- Direct Book Button -->
                    <?php if ($isAvailable !== false): ?>
                        <a href="<?= base_url('booking.php?room_id=' . $room['id'] . '&check_in=' . urlencode($checkIn) . '&check_out=' . urlencode($checkOut) . '&guests=' . urlencode($guests)) ?>" class="btn btn-gold btn-block btn-lg">
                            <i class="fa-solid fa-lock"></i> Proceed to Reservation
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline btn-block" disabled style="opacity: 0.6; cursor: not-allowed;">
                            Unavailable for Chosen Dates
                        </button>
                    <?php endif; ?>

                    <div style="text-align: center; margin-top: 1rem; font-size: 0.78rem; color: var(--text-muted);">
                        <i class="fa-solid fa-shield-halved"></i> No upfront payment required. Pay upon arrival.
                    </div>
                </div>
            </aside>

        </div>
    </div>
</section>

<!-- Related Rooms Section -->
<?php if (!empty($relatedRooms)): ?>
<section class="section section-bg">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Discover More</span>
            <h2 class="section-title">Similar Accommodations</h2>
        </div>

        <div class="rooms-grid">
            <?php foreach ($relatedRooms as $rel): ?>
                <div class="room-card">
                    <div class="room-card-image">
                        <img src="<?= get_room_primary_image($rel['id']) ?>" alt="<?= e($rel['name']) ?>">
                        <div class="room-card-price">
                            <span class="amount"><?= format_price($rel['price_per_night']) ?></span> / night
                        </div>
                    </div>
                    <div class="room-card-body">
                        <span class="room-card-type"><?= e($rel['room_type']) ?></span>
                        <h3 class="room-card-title">
                            <a href="<?= base_url('room-details.php?id=' . $rel['id']) ?>"><?= e($rel['name']) ?></a>
                        </h3>
                        <p class="room-card-desc"><?= e($rel['description']) ?></p>
                        <div class="room-card-actions">
                            <a href="<?= base_url('room-details.php?id=' . $rel['id']) ?>" class="btn btn-outline btn-sm">
                                View Details
                            </a>
                            <a href="<?= base_url('booking.php?room_id=' . $rel['id']) ?>" class="btn btn-gold btn-sm">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
