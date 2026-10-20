<?php
/**
 * Royal Stay - Customer Reservations Dashboard (my-bookings.php)
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

// Guest must be signed in
require_login(base_url('my-bookings.php'));

$currentUser = get_logged_in_user();
$pdo = get_db();

$pageTitle = 'My Stays & Bookings';
$activeNav = 'my-bookings';

// Fetch all bookings for this user OR by email matching
$stmt = $pdo->prepare("
    SELECT b.*, r.name AS room_name, r.slug AS room_slug, r.room_type, r.bed_type, r.room_size,
           ri.image_path AS room_image
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    LEFT JOIN room_images ri ON r.id = ri.room_id AND ri.is_primary = 1
    WHERE b.user_id = :uid OR b.guest_email = :email
    ORDER BY b.id DESC
");
$stmt->execute([
    ':uid'   => $currentUser['id'],
    ':email' => $currentUser['email']
]);
$bookings = $stmt->fetchAll();

// Stats calculation
$totalBookings = count($bookings);
$activeBookings = 0;
$completedBookings = 0;
foreach ($bookings as $b) {
    if (in_array($b['booking_status'], ['pending', 'confirmed'])) {
        $activeBookings++;
    } elseif ($b['booking_status'] === 'completed') {
        $completedBookings++;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1>My Stays & Reservations</h1>
        <p>Manage your reservations, view receipts, and request bespoke services.</p>
        <div class="breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <span>My Bookings</span>
        </div>
    </div>
</div>

<div class="section">
    <div class="container">
        
        <!-- Guest Profile Stat Bar -->
        <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 1.75rem 2rem; margin-bottom: 2.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--primary); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 700;">
                    <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                </div>
                <div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--primary); margin: 0 0 0.25rem 0;">
                        <?= e($currentUser['name']) ?>
                    </h3>
                    <div style="color: var(--text-muted); font-size: 0.85rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                        <span><i class="fa-solid fa-envelope" style="color: var(--accent);"></i> <?= e($currentUser['email']) ?></span>
                        <span><i class="fa-solid fa-phone" style="color: var(--accent);"></i> <?= e($currentUser['phone'] ?? 'Not provided') ?></span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 1.5rem; border-left: 1px solid var(--border); padding-left: 1.5rem;">
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);"><?= $totalBookings ?></div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Total Stays</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #10B981;"><?= $activeBookings ?></div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Active</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent);"><?= $completedBookings ?></div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Completed</div>
                </div>
            </div>
        </div>

        <?php if (empty($bookings)): ?>
            <!-- Empty State -->
            <div style="text-align: center; padding: 4rem 2rem; background: #ffffff; border-radius: var(--radius-lg); border: 1px dashed var(--border); max-width: 600px; margin: 0 auto;">
                <div style="width: 80px; height: 80px; background: rgba(217, 164, 65, 0.1); color: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem auto;">
                    <i class="fa-solid fa-bell-concierge"></i>
                </div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--primary); margin-bottom: 0.75rem;">
                    No Stays Booked Yet
                </h3>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 2rem; line-height: 1.6;">
                    You have no active or previous reservations on record. Explore our opulent suites and reserve your luxury escape today.
                </p>
                <a href="<?= base_url('rooms.php') ?>" class="btn btn-gold">
                    <i class="fa-solid fa-calendar-plus"></i> Explore Our Suites
                </a>
            </div>
        <?php else: ?>
            
            <!-- Bookings List -->
            <div style="display: flex; flex-direction: column; gap: 1.75rem;">
                <?php foreach ($bookings as $b): ?>
                    <?php
                    $isPast = strtotime($b['check_out']) < time();
                    $canCancel = in_array($b['booking_status'], ['pending', 'confirmed']) && strtotime($b['check_in']) > time();
                    $primaryImg = !empty($b['room_image']) && file_exists(__DIR__ . '/' . $b['room_image']) ? base_url($b['room_image']) : base_url('assets/images/room_default.jpg');
                    ?>
                    <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); overflow: hidden; transition: var(--transition); display: flex; flex-wrap: wrap;">
                        
                        <!-- Room Thumbnail -->
                        <div style="flex: 0 0 280px; min-height: 200px; position: relative;">
                            <img src="<?= e($primaryImg) ?>" alt="<?= e($b['room_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <div style="position: absolute; top: 1rem; left: 1rem;">
                                <span class="status-pill status-<?= e($b['booking_status']) ?>">
                                    <?= ucfirst(e($b['booking_status'])) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Reservation Details -->
                        <div style="flex: 1; padding: 1.75rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                                    <div>
                                        <span style="font-size: 0.78rem; text-transform: uppercase; color: var(--accent); font-weight: 600; letter-spacing: 0.5px;">
                                            <?= e($b['room_type']) ?>
                                        </span>
                                        <h4 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; color: var(--primary); margin: 0.2rem 0 0 0;">
                                            <?= e($b['room_name']) ?>
                                        </h4>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="font-size: 0.8rem; color: var(--text-muted);">Total Amount</span>
                                        <div style="font-size: 1.35rem; font-weight: 700; color: var(--primary);"><?= format_price($b['total_amount']) ?></div>
                                    </div>
                                </div>

                                <!-- Booking Specs Grid -->
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 1rem; background: var(--bg-surface); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; border: 1px solid var(--border);">
                                    <div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Check-In</div>
                                        <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-main);"><?= date('M d, Y', strtotime($b['check_in'])) ?></div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Check-Out</div>
                                        <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-main);"><?= date('M d, Y', strtotime($b['check_out'])) ?></div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Duration</div>
                                        <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-main);"><?= $b['nights'] ?> Nights (<?= $b['guests'] ?> Guests)</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Payment</div>
                                        <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-main);">
                                            <?= ucfirst(e($b['payment_status'])) ?> (<?= e($b['payment_method']) ?>)
                                        </div>
                                    </div>
                                </div>

                                <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                                    <strong>Reference:</strong> <code style="color: var(--primary); background: #eee; padding: 2px 6px; border-radius: 4px;"><?= e($b['booking_reference']) ?></code> &bull; Reserved on <?= date('M d, Y', strtotime($b['created_at'])) ?>
                                </div>
                            </div>

                            <!-- Action Bar -->
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-light); padding-top: 1rem; margin-top: 0.5rem; flex-wrap: wrap; gap: 0.75rem;">
                                <div style="display: flex; gap: 0.75rem; align-items: center;">
                                    <a href="<?= base_url('booking-confirmation.php?ref=' . urlencode($b['booking_reference'])) ?>" class="btn btn-outline btn-sm">
                                        <i class="fa-solid fa-file-invoice"></i> View Voucher & Receipt
                                    </a>
                                    <a href="<?= base_url('room-details.php?id=' . $b['room_id']) ?>" class="btn btn-ghost btn-sm">
                                        <i class="fa-solid fa-eye"></i> View Suite
                                    </a>
                                </div>

                                <div style="display: flex; gap: 0.75rem; align-items: center;">
                                    <?php if ($canCancel): ?>
                                        <form action="<?= base_url('actions/cancel-booking.php') ?>" method="POST" onsubmit="return confirm('Are you sure you wish to cancel reservation <?= e($b['booking_reference']) ?>? This action cannot be reversed.');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="booking_reference" value="<?= e($b['booking_reference']) ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fa-solid fa-ban"></i> Cancel Booking
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($b['booking_status'] === 'completed'): ?>
                                        <a href="<?= base_url('reviews.php?booking_id=' . $b['id']) ?>" class="btn btn-gold btn-sm">
                                            <i class="fa-solid fa-star"></i> Leave Review
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
