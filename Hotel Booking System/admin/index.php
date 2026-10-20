<?php
/**
 * Royal Stay - Executive Admin Dashboard (admin/index.php)
 */

$adminPageTitle = 'Executive Dashboard';
$activeAdminNav = 'dashboard';

require_once __DIR__ . '/includes/admin-header.php';

$pdo = get_db();

// 1. KPI Statistics
// Total Revenue from confirmed and completed bookings
$revenueStmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE booking_status IN ('confirmed', 'completed')");
$totalRevenue = (float)$revenueStmt->fetchColumn();

// Total Bookings
$bookingsCountStmt = $pdo->query("SELECT COUNT(*) FROM bookings");
$totalBookingsCount = (int)$bookingsCountStmt->fetchColumn();

// Available Rooms
$roomsCountStmt = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'");
$availableRoomsCount = (int)$roomsCountStmt->fetchColumn();

// Unread Inquiries
$inquiriesCountStmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
$unreadInquiriesCount = (int)$inquiriesCountStmt->fetchColumn();

// 2. Status Distribution for Doughnut Chart
$statusStmt = $pdo->query("SELECT booking_status, COUNT(*) as count FROM bookings GROUP BY booking_status");
$statusData = [
    'confirmed' => 0,
    'pending'   => 0,
    'completed' => 0,
    'cancelled' => 0
];
while ($row = $statusStmt->fetch()) {
    $st = strtolower($row['booking_status']);
    if (isset($statusData[$st])) {
        $statusData[$st] = (int)$row['count'];
    }
}

// 3. Monthly Revenue and Bookings (Last 6 Months)
$monthlyLabels = [];
$monthlyRevenue = [];
$monthlyBookings = [];

for ($i = 5; $i >= 0; $i--) {
    $monthKey = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M Y', strtotime("-$i months"));
    $monthlyLabels[] = $monthLabel;

    $mStmt = $pdo->prepare("
        SELECT COUNT(*) as bookings_cnt, COALESCE(SUM(total_amount), 0) as rev_sum 
        FROM bookings 
        WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
    ");
    $mStmt->execute([$monthKey]);
    $mData = $mStmt->fetch();

    $monthlyBookings[] = (int)($mData['bookings_cnt'] ?? 0);
    $monthlyRevenue[] = (float)($mData['rev_sum'] ?? 0);
}

// 4. Recent Reservations (5 latest)
$recentStmt = $pdo->query("
    SELECT b.*, r.name as room_name, r.room_type 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    ORDER BY b.id DESC
    LIMIT 6
");
$recentBookings = $recentStmt->fetchAll();
?>

<!-- KPI Stats Grid -->
<div class="stats-grid">
    
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-gold">
            <i class="fa-solid fa-vault"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value"><?= format_price($totalRevenue) ?></div>
            <div class="stat-subtext">Confirmed & completed stays</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-primary">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Reservations</div>
            <div class="stat-value"><?= number_format($totalBookingsCount) ?></div>
            <div class="stat-subtext">Across all suites</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-success">
            <i class="fa-solid fa-door-open"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Active Suites</div>
            <div class="stat-value"><?= $availableRoomsCount ?></div>
            <div class="stat-subtext">Available for online booking</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-info">
            <i class="fa-solid fa-envelope-open-text"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">New Inquiries</div>
            <div class="stat-value"><?= $unreadInquiriesCount ?></div>
            <div class="stat-subtext">Unread concierge inquiries</div>
        </div>
    </div>

</div>

<!-- Charts Row -->
<div class="chart-grid">
    
    <!-- Monthly Performance Chart -->
    <div class="admin-card" style="margin-bottom: 0;">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fa-solid fa-chart-column" style="color: var(--admin-accent);"></i> Monthly Performance
            </h3>
            <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Last 6 Months Activity</span>
        </div>
        <div class="admin-card-body" style="height: 320px; position: relative;">
            <canvas id="monthlyPerformanceChart"></canvas>
        </div>
    </div>

    <!-- Booking Status Doughnut -->
    <div class="admin-card" style="margin-bottom: 0;">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="fa-solid fa-chart-pie" style="color: var(--admin-accent);"></i> Status Distribution
            </h3>
            <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Current Mix</span>
        </div>
        <div class="admin-card-body" style="height: 320px; position: relative;">
            <canvas id="statusDistributionChart"></canvas>
        </div>
    </div>

</div>

<!-- Recent Reservations Section -->
<div class="admin-card" style="margin-top: 2rem;">
    <div class="admin-card-header">
        <h3 class="admin-card-title">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--admin-accent);"></i> Recent Reservations
        </h3>
        <div style="display: flex; gap: 0.75rem;">
            <a href="<?= base_url('admin/bookings.php') ?>" class="btn btn-outline btn-sm">
                View All Bookings <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <div class="admin-card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Guest Details</th>
                        <th>Suite</th>
                        <th>Dates (Nights)</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentBookings)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--admin-text-muted);">
                                No reservations registered in the database yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentBookings as $bk): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--admin-sidebar); font-family: monospace; font-size: 0.95rem;">
                                        <?= e($bk['booking_reference']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--admin-text);"><?= e($bk['guest_name']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?= e($bk['guest_email']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?= e($bk['room_name']) ?></div>
                                    <div style="font-size: 0.72rem; color: var(--admin-text-muted);"><?= e($bk['room_type']) ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;"><?= date('M d', strtotime($bk['check_in'])) ?> &ndash; <?= date('M d, Y', strtotime($bk['check_out'])) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?= $bk['nights'] ?> Nights (<?= $bk['guests'] ?> Guests)</div>
                                </td>
                                <td>
                                    <strong style="color: var(--admin-sidebar);"><?= format_price($bk['total_amount']) ?></strong>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= e($bk['booking_status']) ?>">
                                        <?= ucfirst(e($bk['booking_status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= e($bk['payment_status']) ?>">
                                        <?= ucfirst(e($bk['payment_status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= base_url('admin/bookings.php?search=' . urlencode($bk['booking_reference'])) ?>" class="btn-icon" title="Manage Reservation">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="<?= base_url('booking-confirmation.php?ref=' . urlencode($bk['booking_reference'])) ?>" target="_blank" class="btn-icon" title="View Customer Voucher">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const monthlyLabels = <?= json_encode($monthlyLabels) ?>;
    const monthlyBookings = <?= json_encode($monthlyBookings) ?>;
    const monthlyRevenue = <?= json_encode($monthlyRevenue) ?>;
    const statusData = <?= json_encode($statusData) ?>;

    initDashboardCharts(monthlyLabels, monthlyBookings, monthlyRevenue, statusData);
});
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
