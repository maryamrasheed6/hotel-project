<?php
/**
 * Royal Stay - Admin Reservations Management (admin/bookings.php)
 */

$adminPageTitle = 'Manage Reservations';
$activeAdminNav = 'bookings';

require_once __DIR__ . '/includes/admin-header.php';

$pdo = get_db();

// Filtering
$statusFilter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$sql = "
    SELECT b.*, r.name as room_name, r.room_type, r.price_per_night as room_rate
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE 1=1
";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND b.booking_status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $sql .= " AND (b.booking_reference LIKE ? OR b.guest_name LIKE ? OR b.guest_email LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY b.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Counts for filter pills
$counts = [];
$cStmt = $pdo->query("SELECT booking_status, COUNT(*) as cnt FROM bookings GROUP BY booking_status");
while ($r = $cStmt->fetch()) {
    $counts[$r['booking_status']] = (int)$r['cnt'];
}
$totalAll = array_sum($counts);
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">
            <i class="fa-solid fa-calendar-check" style="color: var(--admin-accent);"></i> All Reservations (<?= count($bookings) ?>)
        </h3>
        
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="<?= base_url('admin/bookings.php') ?>" class="btn <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                All (<?= $totalAll ?>)
            </a>
            <a href="<?= base_url('admin/bookings.php?status=pending') ?>" class="btn <?= $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Pending (<?= $counts['pending'] ?? 0 ?>)
            </a>
            <a href="<?= base_url('admin/bookings.php?status=confirmed') ?>" class="btn <?= $statusFilter === 'confirmed' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Confirmed (<?= $counts['confirmed'] ?? 0 ?>)
            </a>
            <a href="<?= base_url('admin/bookings.php?status=completed') ?>" class="btn <?= $statusFilter === 'completed' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Completed (<?= $counts['completed'] ?? 0 ?>)
            </a>
            <a href="<?= base_url('admin/bookings.php?status=cancelled') ?>" class="btn <?= $statusFilter === 'cancelled' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Cancelled (<?= $counts['cancelled'] ?? 0 ?>)
            </a>
        </div>
    </div>

    <div class="admin-card-body">
        
        <!-- Live Search Input -->
        <div class="admin-filter-bar">
            <input type="text" class="search-bar-input data-table-search" data-target="bookingsTable" placeholder="Type to filter by reference, guest name, email, or room..." value="<?= e($search) ?>">
            <?php if (!empty($statusFilter) || !empty($search)): ?>
                <a href="<?= base_url('admin/bookings.php') ?>" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filter
                </a>
            <?php endif; ?>
        </div>

        <!-- Bookings Table -->
        <div class="table-responsive">
            <table class="admin-table" id="bookingsTable">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Guest</th>
                        <th>Suite</th>
                        <th>Dates</th>
                        <th>Amount</th>
                        <th>Booking Status</th>
                        <th>Payment</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem; color: var(--admin-text-muted);">
                                No reservations match the specified criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--admin-sidebar); font-family: monospace; font-size: 0.95rem;">
                                        <?= e($b['booking_reference']) ?>
                                    </strong>
                                    <div style="font-size: 0.72rem; color: var(--admin-text-muted);">
                                        <?= date('M d, Y', strtotime($b['created_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--admin-text);"><?= e($b['guest_name']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                                        <a href="mailto:<?= e($b['guest_email']) ?>" style="color: inherit; text-decoration: none;"><?= e($b['guest_email']) ?></a>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?= e($b['guest_phone']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--admin-sidebar);"><?= e($b['room_name']) ?></div>
                                    <div style="font-size: 0.72rem; color: var(--admin-text-muted);"><?= e($b['room_type']) ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; font-weight: 500;">
                                        <?= date('M d', strtotime($b['check_in'])) ?> &ndash; <?= date('M d, Y', strtotime($b['check_out'])) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                                        <?= $b['nights'] ?> Nights &bull; <?= $b['guests'] ?> Guests
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--admin-sidebar); font-size: 0.95rem;"><?= format_price($b['total_amount']) ?></strong>
                                    <div style="font-size: 0.72rem; color: var(--admin-text-muted);"><?= e($b['payment_method']) ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= e($b['booking_status']) ?>">
                                        <?= ucfirst(e($b['booking_status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= e($b['payment_status']) ?>">
                                        <?= ucfirst(e($b['payment_status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-btns" style="justify-content: flex-end;">
                                        <button type="button" class="btn-icon" title="Update Reservation Status" onclick="openEditBookingModal(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>)">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <a href="<?= base_url('booking-confirmation.php?ref=' . urlencode($b['booking_reference'])) ?>" target="_blank" class="btn-icon" title="Printable Customer Voucher">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Modal: Update Reservation Status -->
<div id="bookingActionModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 540px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.25); overflow: hidden; animation: fadeIn 0.2s ease;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--admin-border); display: flex; justify-content: space-between; align-items: center; background: #F8FAFC;">
            <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--admin-sidebar); margin: 0;">
                <i class="fa-solid fa-sliders" style="color: var(--admin-accent);"></i> Update Reservation <span id="modalRefTitle" style="font-family: monospace; color: var(--admin-accent);"></span>
            </h3>
            <button type="button" onclick="closeModal('bookingActionModal')" style="background: none; border: none; font-size: 1.5rem; color: var(--admin-text-muted); cursor: pointer;">&times;</button>
        </div>

        <form action="<?= base_url('admin/actions/booking-actions.php') ?>" method="POST" style="padding: 1.75rem;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="booking_id" id="modalBookingId">

            <div style="background: var(--admin-bg); padding: 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem;">
                <div><strong>Guest:</strong> <span id="modalGuest"></span> (<span id="modalEmail"></span>)</div>
                <div style="margin-top: 0.25rem;"><strong>Suite:</strong> <span id="modalSuite"></span> &bull; <strong>Total:</strong> <span id="modalTotal"></span></div>
                <div style="margin-top: 0.25rem; font-style: italic; color: var(--admin-text-muted);"><strong>Special Requests:</strong> <span id="modalSpecial">None</span></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="modalBookingStatus">Reservation Status</label>
                <select name="booking_status" id="modalBookingStatus" class="form-control" required>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="modalPaymentStatus">Payment Status</label>
                <select name="payment_status" id="modalPaymentStatus" class="form-control" required>
                    <option value="unpaid">Unpaid</option>
                    <option value="paid">Paid</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="modalAdminNote">Administrative Note (Logged to History)</label>
                <textarea name="admin_note" id="modalAdminNote" class="form-control" rows="3" placeholder="e.g. Payment verified via front-desk terminal. Upgraded to high floor."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('bookingActionModal')">Cancel</button>
                <button type="submit" class="btn btn-gold">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditBookingModal(booking) {
    document.getElementById('modalBookingId').value = booking.id;
    document.getElementById('modalRefTitle').textContent = '#' + booking.booking_reference;
    document.getElementById('modalGuest').textContent = booking.guest_name;
    document.getElementById('modalEmail').textContent = booking.guest_email;
    document.getElementById('modalSuite').textContent = booking.room_name;
    document.getElementById('modalTotal').textContent = '$' + parseFloat(booking.total_amount).toFixed(2);
    document.getElementById('modalSpecial').textContent = booking.special_requests || 'No special requests';
    
    document.getElementById('modalBookingStatus').value = booking.booking_status;
    document.getElementById('modalPaymentStatus').value = booking.payment_status;
    document.getElementById('modalAdminNote').value = '';

    openModal('bookingActionModal');
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
