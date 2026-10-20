<?php
/**
 * Royal Stay - Admin Guest Reviews Moderation (admin/reviews.php)
 */

$adminPageTitle = 'Moderate Guest Reviews';
$activeAdminNav = 'reviews';

require_once __DIR__ . '/includes/admin-header.php';

$pdo = get_db();
$statusFilter = sanitize($_GET['status'] ?? '');

$sql = "SELECT * FROM reviews WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// Counts
$pendingCnt = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
$approvedCnt = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'approved'")->fetchColumn();
$rejectedCnt = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'rejected'")->fetchColumn();
$totalCnt = $pendingCnt + $approvedCnt + $rejectedCnt;
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">
            <i class="fa-solid fa-star" style="color: var(--admin-accent);"></i> Guest Reviews & Ratings (<?= count($reviews) ?>)
        </h3>

        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="<?= base_url('admin/reviews.php') ?>" class="btn <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                All (<?= $totalCnt ?>)
            </a>
            <a href="<?= base_url('admin/reviews.php?status=pending') ?>" class="btn <?= $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Pending Moderation (<?= $pendingCnt ?>)
            </a>
            <a href="<?= base_url('admin/reviews.php?status=approved') ?>" class="btn <?= $statusFilter === 'approved' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Approved (<?= $approvedCnt ?>)
            </a>
            <a href="<?= base_url('admin/reviews.php?status=rejected') ?>" class="btn <?= $statusFilter === 'rejected' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Rejected (<?= $rejectedCnt ?>)
            </a>
        </div>
    </div>

    <div class="admin-card-body">
        
        <div class="admin-filter-bar">
            <input type="text" class="search-bar-input data-table-search" data-target="reviewsTable" placeholder="Filter by guest name or review remarks...">
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="reviewsTable">
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Rating</th>
                        <th>Review Comments</th>
                        <th>Submitted Date</th>
                        <th>Status</th>
                        <th style="text-align: right;">Moderation Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem; color: var(--admin-text-muted);">
                                No reviews match the current status filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--admin-sidebar);"><?= e($rev['guest_name']) ?></div>
                                    <?php if (!empty($rev['booking_id'])): ?>
                                        <div style="font-size: 0.72rem; color: var(--admin-text-muted);">Booking #<?= $rev['booking_id'] ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="color: var(--admin-accent); font-size: 0.9rem;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?= $i <= $rev['rating'] ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span style="font-size: 0.75rem; color: var(--admin-text-muted);"><?= $rev['rating'] ?> of 5 Stars</span>
                                </td>
                                <td style="max-width: 380px;">
                                    <p style="margin: 0; font-size: 0.88rem; line-height: 1.5; color: var(--admin-text);">
                                        <?= nl2br(e($rev['review_text'])) ?>
                                    </p>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; color: var(--admin-text-muted);"><?= date('M d, Y', strtotime($rev['created_at'])) ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= e($rev['status']) ?>">
                                        <?= ucfirst(e($rev['status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-btns" style="justify-content: flex-end;">
                                        <?php if ($rev['status'] !== 'approved'): ?>
                                            <form action="<?= base_url('admin/actions/review-actions.php') ?>" method="POST" style="display: inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                                <button type="submit" class="btn-icon btn-icon-success" title="Approve Review">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($rev['status'] !== 'rejected'): ?>
                                            <form action="<?= base_url('admin/actions/review-actions.php') ?>" method="POST" style="display: inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                                <button type="submit" class="btn-icon" title="Reject Review">
                                                    <i class="fa-solid fa-ban" style="color: var(--warning);"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form action="<?= base_url('admin/actions/review-actions.php') ?>" method="POST" style="display: inline;" data-confirm="Permanently delete this guest review?">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                            <button type="submit" class="btn-icon btn-icon-error" title="Delete Review">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
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

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
