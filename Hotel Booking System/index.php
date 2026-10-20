<?php
/**
 * Royal Stay - Luxury Hotel Booking Management System
 * Homepage (index.php)
 */

$activeNav = 'home';
$pageTitle = 'Luxury Stay Starts Here';
$metaDescription = 'Experience five-star luxury at Royal Stay. Opulent beachfront suites, private plunge pools, world-class dining, and tailored concierge hospitality.';

require_once __DIR__ . '/includes/header.php';

$pdo = get_db();

// 1. Fetch Featured Rooms
$featuredStmt = $pdo->query("
    SELECT * FROM rooms 
    WHERE is_featured = 1 AND status = 'available' 
    ORDER BY price_per_night DESC 
    LIMIT 3
");
$featuredRooms = $featuredStmt->fetchAll();

// 2. Fetch Active Facilities
$facilitiesStmt = $pdo->query("SELECT * FROM facilities WHERE status = 'active' ORDER BY id ASC LIMIT 8");
$facilities = $facilitiesStmt->fetchAll();

// 3. Fetch Approved Reviews
$reviewsStmt = $pdo->query("
    SELECT r.*, u.name as user_fullname 
    FROM reviews r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.status = 'approved' 
    ORDER BY r.created_at DESC 
    LIMIT 3
");
$reviews = $reviewsStmt->fetchAll();

// 4. Distinct Room Types for Search Form
$typesStmt = $pdo->query("SELECT DISTINCT room_type FROM rooms WHERE status = 'available'");
$roomTypes = $typesStmt->fetchAll(PDO::FETCH_COLUMN);

// Pre-fill search dates
$defaultCheckIn = date('Y-m-d', strtotime('+1 day'));
$defaultCheckOut = date('Y-m-d', strtotime('+3 days'));
?>

<!-- Hero Section -->
<section class="hero-section" style="background-image: url('<?= base_url('assets/images/hero_hotel.jpg') ?>');">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="hero-badge">
            <i class="fa-solid fa-gem"></i> Five-Star Luxury Destination
        </div>
        <h1 class="hero-title">
            Luxury Stay <span>Starts Here</span>
        </h1>
        <p class="hero-subtitle">
            Immerse yourself in breathtaking ocean views, world-class gastronomy, and bespoke suites designed for the world's most discerning guests.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="#bookingSearch" class="btn btn-gold btn-lg">
                <i class="fa-solid fa-calendar-check"></i> Book Your Stay
            </a>
            <a href="<?= base_url('rooms.php') ?>" class="btn btn-outline-gold btn-lg" style="color: #ffffff; border-color: #ffffff;">
                <i class="fa-solid fa-compass"></i> Explore All Rooms
            </a>
        </div>
    </div>
</section>

<!-- Booking Search Form Widget -->
<div class="container search-widget-wrapper" id="bookingSearch">
    <div class="search-widget">
        <form action="<?= base_url('rooms.php') ?>" method="GET" class="search-form">
            <div class="search-field">
                <label class="search-label" for="heroCheckIn">
                    <i class="fa-solid fa-calendar-days"></i> Check-in Date
                </label>
                <input type="date" name="check_in" id="heroCheckIn" class="search-input" value="<?= $defaultCheckIn ?>" required>
            </div>

            <div class="search-field">
                <label class="search-label" for="heroCheckOut">
                    <i class="fa-solid fa-calendar-days"></i> Check-out Date
                </label>
                <input type="date" name="check_out" id="heroCheckOut" class="search-input" value="<?= $defaultCheckOut ?>" required>
            </div>

            <div class="search-field">
                <label class="search-label" for="heroGuests">
                    <i class="fa-solid fa-user-group"></i> Guests
                </label>
                <select name="guests" id="heroGuests" class="search-input">
                    <option value="1">1 Guest</option>
                    <option value="2" selected>2 Guests</option>
                    <option value="3">3 Guests</option>
                    <option value="4">4 Guests</option>
                    <option value="5">5+ Guests</option>
                </select>
            </div>

            <div class="search-field">
                <label class="search-label" for="heroRoomType">
                    <i class="fa-solid fa-bed"></i> Room Category
                </label>
                <select name="room_type" id="heroRoomType" class="search-input">
                    <option value="">All Categories</option>
                    <?php foreach ($roomTypes as $type): ?>
                        <option value="<?= e($type) ?>"><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="height: 50px;">
                <i class="fa-solid fa-magnifying-glass"></i> Search Rooms
            </button>
        </form>
    </div>
</div>

<!-- Featured Rooms Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Exquisite Accommodations</span>
            <h2 class="section-title">Our Featured Suites & Villas</h2>
            <p class="section-desc">Each room is an architectural masterwork, pairing sumptuous comfort with breathtaking panoramic vistas and bespoke furnishings.</p>
        </div>

        <div class="rooms-grid">
            <?php foreach ($featuredRooms as $room): ?>
                <div class="room-card">
                    <div class="room-card-image">
                        <img src="<?= get_room_primary_image($room['id']) ?>" alt="<?= e($room['name']) ?>">
                        <span class="badge badge-gold room-card-badge">Featured</span>
                        <div class="room-card-price">
                            <span class="amount"><?= format_price($room['price_per_night']) ?></span> / night
                        </div>
                    </div>

                    <div class="room-card-body">
                        <span class="room-card-type"><?= e($room['room_type']) ?></span>
                        <h3 class="room-card-title">
                            <a href="<?= base_url('room-details.php?id=' . $room['id']) ?>"><?= e($room['name']) ?></a>
                        </h3>
                        <p class="room-card-desc"><?= e($room['description']) ?></p>

                        <div class="room-specs">
                            <div class="room-spec-item" title="Capacity">
                                <i class="fa-solid fa-user-group"></i> Up to <?= $room['max_guests'] ?> Guests
                            </div>
                            <div class="room-spec-item" title="Bed Type">
                                <i class="fa-solid fa-bed"></i> <?= e($room['bed_type']) ?>
                            </div>
                            <div class="room-spec-item" title="Room Dimensions">
                                <i class="fa-solid fa-ruler-combined"></i> <?= e($room['room_size']) ?>
                            </div>
                        </div>

                        <div class="room-card-actions">
                            <a href="<?= base_url('room-details.php?id=' . $room['id']) ?>" class="btn btn-outline btn-sm">
                                View Details
                            </a>
                            <a href="<?= base_url('booking.php?room_id=' . $room['id']) ?>" class="btn btn-gold btn-sm">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 3.5rem;">
            <a href="<?= base_url('rooms.php') ?>" class="btn btn-primary btn-lg">
                View All Rooms & Suites <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Facilities Section -->
<section class="section section-bg" id="facilities">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">World-Class Indulgences</span>
            <h2 class="section-title">Resort Amenities & Facilities</h2>
            <p class="section-desc">From soothing wellness rituals to fine dining gastronomy, experience hotel amenities curated for complete relaxation and bliss.</p>
        </div>

        <div class="facilities-grid">
            <?php foreach ($facilities as $fac): ?>
                <div class="facility-card">
                    <div class="facility-icon">
                        <i class="<?= e($fac['icon']) ?>"></i>
                    </div>
                    <h3 class="facility-name"><?= e($fac['name']) ?></h3>
                    <p class="facility-desc"><?= e($fac['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">The Royal Standard</span>
            <h2 class="section-title">Why Discerning Guests Choose Us</h2>
            <p class="section-desc">We elevate every touchpoint of your journey, delivering attentive service and effortless tranquility from check-in to farewell.</p>
        </div>

        <div class="features-grid">
            <div class="feature-box">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="feature-info">
                    <h4>Guaranteed Best Rates</h4>
                    <p>Direct reservations come with our best price guarantee, complimentary room upgrades where available, and flexible cancellations.</p>
                </div>
            </div>

            <div class="feature-box">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-bell-concierge"></i>
                </div>
                <div class="feature-info">
                    <h4>Bespoke Concierge</h4>
                    <p>Dedicated personal butlers and round-the-clock concierges ready to craft tailored itineraries and private celebrations.</p>
                </div>
            </div>

            <div class="feature-box">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="feature-info">
                    <h4>Safe & Seamless Booking</h4>
                    <p>Instant reservation confirmations with bank-grade encryption and convenient zero-deposit Pay at Hotel options.</p>
                </div>
            </div>

            <div class="feature-box">
                <div class="feature-icon-wrapper">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div class="feature-info">
                    <h4>Michelin-Star Dining</h4>
                    <p>Fresh organic farm-to-table breakfast spreads and candlelit beachfront dinners crafted by master culinary artisans.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Customer Testimonials Section -->
<section class="section section-bg">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Guest Stories</span>
            <h2 class="section-title">Memories Created at Royal Stay</h2>
            <p class="section-desc">Read genuine reviews from guests who made Royal Stay their private haven of luxury and calm.</p>
        </div>

        <div class="reviews-grid">
            <?php foreach ($reviews as $rev): ?>
                <div class="review-card">
                    <div class="review-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="<?= $i <= $rev['rating'] ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="review-text">"<?= e($rev['review_text']) ?>"</p>
                    <div class="review-author">
                        <div class="author-avatar">
                            <?= strtoupper(substr($rev['guest_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="author-name"><?= e($rev['guest_name']) ?></div>
                            <div class="review-date">Stayed in <?= date('F Y', strtotime($rev['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="<?= base_url('reviews.php') ?>" class="btn btn-outline">
                <i class="fa-regular fa-comment-dots"></i> Read All Reviews & Share Your Experience
            </a>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div class="cta-banner">
            <div class="cta-content">
                <span class="section-subtitle" style="color: #FFE6A8;">Reserve Your Escape</span>
                <h2 class="cta-title">Your Unforgettable <span>Luxury Stay</span> Awaits</h2>
                <p class="cta-desc">Indulge in private infinity pools, world-renowned hospitality, and sun-drenched balconies. Book direct today for exclusive perks and seamless reservations.</p>
                <a href="<?= base_url('rooms.php') ?>" class="btn btn-gold btn-lg">
                    <i class="fa-solid fa-calendar-check"></i> Book Your Reservation Now
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
