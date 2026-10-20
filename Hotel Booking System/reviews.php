<?php
/**
 * Royal Stay - Guest Reviews & Testimonials Showcase
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

$pdo = get_db();
$pageTitle = 'Guest Reviews & Experiences';
$activeNav = 'reviews';

$starFilter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;

// Fetch reviews
$sql = "SELECT * FROM reviews WHERE status = 'approved'";
$params = [];

if ($starFilter >= 1 && $starFilter <= 5) {
    $sql .= " AND rating = ?";
    $params[] = $starFilter;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// Overall Stats
$statsStmt = $pdo->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total_count FROM reviews WHERE status = 'approved'");
$stats = $statsStmt->fetch();
$avgRating = $stats['avg_rating'] ? round((float)$stats['avg_rating'], 1) : 5.0;
$totalCount = (int)($stats['total_count'] ?? 0);

$currentUser = get_logged_in_user();
$prefillName = $currentUser ? $currentUser['name'] : '';
$prefillBookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : '';

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1>Guest Reflections</h1>
        <p>Read authenticated stories from travelers who have called Royal Stay their home.</p>
        <div class="breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <span>Guest Reviews</span>
        </div>
    </div>
</div>

<div class="section">
    <div class="container">
        
        <!-- Score Banner & Write Review CTA -->
        <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 2.5rem; margin-bottom: 3rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 2rem;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <div style="text-align: center; background: rgba(6, 59, 62, 0.05); padding: 1.25rem 2rem; border-radius: var(--radius-md); border: 1px solid var(--border);">
                    <div style="font-size: 3rem; font-weight: 700; color: var(--primary); line-height: 1;"><?= number_format($avgRating, 1) ?></div>
                    <div style="color: var(--accent); font-size: 1rem; margin: 0.35rem 0;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="<?= $i <= round($avgRating) ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Overall Rating</div>
                </div>

                <div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--primary); margin: 0 0 0.5rem 0;">
                        Exceptional Guest Satisfaction
                    </h3>
                    <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0; max-width: 480px;">
                        Based on <?= $totalCount ?> verified guest stays. Every review is inspected by our guest relations team to maintain authenticity.
                    </p>
                </div>
            </div>

            <div>
                <button type="button" class="btn btn-gold" onclick="document.getElementById('reviewModal').style.display = 'flex';">
                    <i class="fa-solid fa-pen-nib"></i> Share Your Experience
                </button>
            </div>
        </div>

        <!-- Rating Filter Tabs -->
        <div style="display: flex; gap: 0.75rem; margin-bottom: 2.5rem; flex-wrap: wrap; align-items: center;">
            <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Filter By:</span>
            <a href="<?= base_url('reviews.php') ?>" class="btn <?= $starFilter === 0 ? 'btn-primary' : 'btn-outline' ?> btn-sm">All Ratings</a>
            <a href="<?= base_url('reviews.php?rating=5') ?>" class="btn <?= $starFilter === 5 ? 'btn-primary' : 'btn-outline' ?> btn-sm">5 Stars ★★★★★</a>
            <a href="<?= base_url('reviews.php?rating=4') ?>" class="btn <?= $starFilter === 4 ? 'btn-primary' : 'btn-outline' ?> btn-sm">4 Stars ★★★★</a>
            <a href="<?= base_url('reviews.php?rating=3') ?>" class="btn <?= $starFilter === 3 ? 'btn-primary' : 'btn-outline' ?> btn-sm">3 Stars ★★★</a>
        </div>

        <!-- Reviews Grid -->
        <?php if (empty($reviews)): ?>
            <div style="text-align: center; padding: 4rem 2rem; background: #ffffff; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
                <p style="color: var(--text-muted); font-size: 1.05rem;">No reviews found for the selected filter.</p>
                <a href="<?= base_url('reviews.php') ?>" class="btn btn-outline btn-sm">View All Reviews</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 2rem;">
                <?php foreach ($reviews as $rev): ?>
                    <div style="background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 2rem; display: flex; flex-direction: column; justify-content: space-between; transition: var(--transition);">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                                <div style="display: flex; align-items: center; gap: 0.85rem;">
                                    <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(6, 59, 62, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem;">
                                        <?= strtoupper(substr($rev['guest_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: var(--text-main); font-size: 1rem;"><?= e($rev['guest_name']) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('M d, Y', strtotime($rev['created_at'])) ?></div>
                                    </div>
                                </div>

                                <div style="color: var(--accent); font-size: 0.88rem;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?= $i <= $rev['rating'] ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.7; font-style: italic; margin-bottom: 1.5rem;">
                                &ldquo;<?= nl2br(e($rev['review_text'])) ?>&rdquo;
                            </p>
                        </div>

                        <div style="border-top: 1px solid var(--border-light); padding-top: 0.85rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; color: var(--text-muted);">
                            <span><i class="fa-solid fa-circle-check" style="color: #10B981;"></i> Verified Stay</span>
                            <span>Royal Stay Resort</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Write Review Modal -->
<div id="reviewModal" style="display: <?= !empty($prefillBookingId) ? 'flex' : 'none' ?>; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 560px; width: 100%; box-shadow: var(--shadow-lg); overflow: hidden; animation: fadeIn 0.25s ease;">
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--primary); margin: 0;">
                <i class="fa-solid fa-star" style="color: var(--accent);"></i> Share Your Experience
            </h3>
            <button type="button" onclick="document.getElementById('reviewModal').style.display = 'none';" style="background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
        </div>

        <form action="<?= base_url('actions/submit-review.php') ?>" method="POST" style="padding: 2rem;">
            <?= csrf_field() ?>
            <input type="hidden" name="booking_id" value="<?= e($prefillBookingId) ?>">

            <div class="form-group">
                <label class="form-label" for="revGuestName">Your Name *</label>
                <input type="text" name="guest_name" id="revGuestName" class="form-control" value="<?= e($prefillName) ?>" placeholder="e.g. Sophia Miller" required>
            </div>

            <div class="form-group">
                <label class="form-label">Overall Rating *</label>
                <div style="display: flex; gap: 0.5rem; font-size: 1.75rem; color: #E2E8F0; cursor: pointer;" id="starRatingSelector">
                    <i class="fa-solid fa-star selected" data-value="1" onclick="setRating(1)"></i>
                    <i class="fa-solid fa-star selected" data-value="2" onclick="setRating(2)"></i>
                    <i class="fa-solid fa-star selected" data-value="3" onclick="setRating(3)"></i>
                    <i class="fa-solid fa-star selected" data-value="4" onclick="setRating(4)"></i>
                    <i class="fa-solid fa-star selected" data-value="5" onclick="setRating(5)"></i>
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="5">
            </div>

            <div class="form-group">
                <label class="form-label" for="revText">Review Comments *</label>
                <textarea name="review_text" id="revText" class="form-control" rows="4" placeholder="Tell us about your suite, culinary journey, and hospitality experience..." required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('reviewModal').style.display = 'none';">Cancel</button>
                <button type="submit" class="btn btn-gold">
                    <i class="fa-solid fa-paper-plane"></i> Submit Review
                </button>
            </div>
        </form>
    </div>
</div>

<style>
#starRatingSelector .fa-star {
    transition: color 0.15s ease;
}
#starRatingSelector .fa-star.selected {
    color: var(--accent);
}
</style>

<script>
function setRating(val) {
    document.getElementById('ratingInput').value = val;
    const stars = document.querySelectorAll('#starRatingSelector .fa-star');
    stars.forEach((s, idx) => {
        if (idx < val) {
            s.classList.add('selected');
        } else {
            s.classList.remove('selected');
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
