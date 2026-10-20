<?php
/**
 * Royal Stay - Rooms Listing Page (rooms.php)
 * Advanced filtering, availability checking, sorting, and pagination
 */

$activeNav = 'rooms';
$pageTitle = 'Our Rooms & Luxury Suites';
$metaDescription = 'Explore our curated collection of luxury penthouses, oceanfront villas, and executive suites with bespoke amenities at Royal Stay.';

require_once __DIR__ . '/includes/header.php';

$pdo = get_db();

// 1. Collect Filter Inputs
$selectedType = sanitize($_GET['room_type'] ?? '');
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$guests = isset($_GET['guests']) && is_numeric($_GET['guests']) ? (int)$_GET['guests'] : null;
$checkIn = sanitize($_GET['check_in'] ?? '');
$checkOut = sanitize($_GET['check_out'] ?? '');
$selectedFacilities = isset($_GET['facilities']) && is_array($_GET['facilities']) ? array_map('intval', $_GET['facilities']) : [];
$sortBy = sanitize($_GET['sort_by'] ?? 'featured');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;

// 2. Build Base Query
$where = ["r.status = 'available'"];
$params = [];

if (!empty($selectedType)) {
    $where[] = "r.room_type = :room_type";
    $params[':room_type'] = $selectedType;
}

if ($minPrice !== null && $minPrice > 0) {
    $where[] = "r.price_per_night >= :min_price";
    $params[':min_price'] = $minPrice;
}

if ($maxPrice !== null && $maxPrice > 0) {
    $where[] = "r.price_per_night <= :max_price";
    $params[':max_price'] = $maxPrice;
}

if ($guests !== null && $guests > 0) {
    $where[] = "r.max_guests >= :guests";
    $params[':guests'] = $guests;
}

// Facility Filtering
if (!empty($selectedFacilities)) {
    $facCount = count($selectedFacilities);
    $inPlaceholders = implode(',', array_fill(0, $facCount, '?'));
    $where[] = "r.id IN (
        SELECT room_id FROM room_facilities 
        WHERE facility_id IN ($inPlaceholders) 
        GROUP BY room_id 
        HAVING COUNT(DISTINCT facility_id) = $facCount
    )";
}

// Date Availability Filtering
$dateConflictIds = [];
if (!empty($checkIn) && !empty($checkOut) && strtotime($checkIn) < strtotime($checkOut)) {
    $conflictStmt = $pdo->prepare("
        SELECT DISTINCT room_id FROM bookings 
        WHERE booking_status IN ('pending', 'confirmed') 
          AND NOT (check_out <= :cin OR check_in >= :cout)
    ");
    $conflictStmt->execute([':cin' => $checkIn, ':cout' => $checkOut]);
    $dateConflictIds = $conflictStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    if (!empty($dateConflictIds)) {
        $conflictList = implode(',', array_map('intval', $dateConflictIds));
        $where[] = "r.id NOT IN ($conflictList)";
    }
}

// Sorting logic
$orderBy = match ($sortBy) {
    'price_low'  => 'r.price_per_night ASC',
    'price_high' => 'r.price_per_night DESC',
    'guests'     => 'r.max_guests DESC',
    default      => 'r.is_featured DESC, r.price_per_night DESC'
};

$whereClause = implode(' AND ', $where);

// 3. Count Total Matching Rooms
$countSql = "SELECT COUNT(*) FROM rooms r WHERE {$whereClause}";
$countStmt = $pdo->prepare($countSql);
// Bind positional facility params if any
$paramIndex = 1;
if (!empty($selectedFacilities)) {
    foreach ($selectedFacilities as $fid) {
        $countStmt->bindValue($paramIndex++, $fid, PDO::PARAM_INT);
    }
}
foreach ($params as $key => $val) {
    $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$totalRooms = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalRooms / $perPage);
$offset = ($page - 1) * $perPage;

// 4. Fetch Paginated Rooms
$dataSql = "
    SELECT r.* FROM rooms r 
    WHERE {$whereClause} 
    ORDER BY {$orderBy} 
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($dataSql);
$paramIndex = 1;
if (!empty($selectedFacilities)) {
    foreach ($selectedFacilities as $fid) {
        $stmt->bindValue($paramIndex++, $fid, PDO::PARAM_INT);
    }
}
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$rooms = $stmt->fetchAll();

// 5. Fetch Filter Options
$allTypes = $pdo->query("SELECT DISTINCT room_type FROM rooms WHERE status = 'available'")->fetchAll(PDO::FETCH_COLUMN);
$allFacilities = $pdo->query("SELECT * FROM facilities WHERE status = 'active' ORDER BY name ASC")->fetchAll();
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1>Our Rooms & Luxury Suites</h1>
        <p>Comfortable stays and lavish sanctuaries tailored for every kind of traveller.</p>
        <div class="breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <span>Rooms</span>
        </div>
    </div>
</section>

<!-- Rooms & Filter Layout -->
<section class="section">
    <div class="container">
        <div class="listing-layout">
            
            <!-- Filters Sidebar -->
            <aside class="filter-sidebar">
                <div class="filter-header">
                    <h3><i class="fa-solid fa-sliders" style="color: var(--accent);"></i> Filter Rooms</h3>
                    <a href="<?= base_url('rooms.php') ?>" style="font-size: 0.8rem; color: var(--text-muted); text-decoration: underline;">Clear All</a>
                </div>

                <form action="<?= base_url('rooms.php') ?>" method="GET" id="roomFilterForm">
                    
                    <!-- Date Availability -->
                    <div class="filter-group">
                        <label class="filter-title">Dates of Stay</label>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <input type="date" name="check_in" class="search-input" value="<?= e($checkIn) ?>" placeholder="Check-in">
                            <input type="date" name="check_out" class="search-input" value="<?= e($checkOut) ?>" placeholder="Check-out">
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="filter-group">
                        <label class="filter-title">Room Category</label>
                        <select name="room_type" class="search-input">
                            <option value="">All Categories</option>
                            <?php foreach ($allTypes as $type): ?>
                                <option value="<?= e($type) ?>" <?= $selectedType === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Guest Capacity -->
                    <div class="filter-group">
                        <label class="filter-title">Guests</label>
                        <select name="guests" class="search-input">
                            <option value="">Any Capacity</option>
                            <?php for ($g = 1; $g <= 6; $g++): ?>
                                <option value="<?= $g ?>" <?= $guests === $g ? 'selected' : '' ?>><?= $g ?>+ Guests</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group">
                        <label class="filter-title">Price Range ($)</label>
                        <div class="price-inputs">
                            <input type="number" name="min_price" class="search-input" placeholder="Min" value="<?= $minPrice !== null ? e($minPrice) : '' ?>" min="0">
                            <span style="color: var(--text-muted);">-</span>
                            <input type="number" name="max_price" class="search-input" placeholder="Max" value="<?= $maxPrice !== null ? e($maxPrice) : '' ?>" min="0">
                        </div>
                    </div>

                    <!-- Facilities -->
                    <div class="filter-group">
                        <label class="filter-title">Amenities & Features</label>
                        <div class="filter-checkbox-list">
                            <?php foreach ($allFacilities as $f): ?>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="facilities[]" value="<?= $f['id'] ?>" <?= in_array($f['id'], $selectedFacilities) ? 'checked' : '' ?>>
                                    <span><?= e($f['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <input type="hidden" name="sort_by" value="<?= e($sortBy) ?>">

                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
                        <i class="fa-solid fa-filter"></i> Apply Filters
                    </button>
                </form>
            </aside>

            <!-- Room Results Main Section -->
            <div class="listing-main">
                
                <!-- Listing Toolbar -->
                <div class="listing-toolbar">
                    <div class="results-count">
                        Showing <strong><?= count($rooms) ?></strong> of <strong><?= $totalRooms ?></strong> Luxury Rooms
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <label for="sortBySelect" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Sort By:</label>
                        <select id="sortBySelect" class="sort-select" onchange="location = this.value;">
                            <?php
                            $queryParams = $_GET;
                            unset($queryParams['sort_by'], $queryParams['page']);
                            $queryString = http_build_query($queryParams);
                            $sortUrl = function($sortKey) use ($queryString) {
                                return base_url('rooms.php?' . ($queryString ? $queryString . '&' : '') . 'sort_by=' . $sortKey);
                            };
                            ?>
                            <option value="<?= $sortUrl('featured') ?>" <?= $sortBy === 'featured' ? 'selected' : '' ?>>Featured First</option>
                            <option value="<?= $sortUrl('price_low') ?>" <?= $sortBy === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="<?= $sortUrl('price_high') ?>" <?= $sortBy === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="<?= $sortUrl('guests') ?>" <?= $sortBy === 'guests' ? 'selected' : '' ?>>Max Capacity</option>
                        </select>
                    </div>
                </div>

                <!-- Rooms Grid or Empty State -->
                <?php if (empty($rooms)): ?>
                    <div style="background: #ffffff; border-radius: var(--radius-md); padding: 4rem 2rem; text-align: center; border: 1px solid var(--border);">
                        <div style="font-size: 3rem; color: var(--accent); margin-bottom: 1rem;">
                            <i class="fa-solid fa-bed"></i>
                        </div>
                        <h3 style="color: var(--primary); margin-bottom: 0.5rem;">No Rooms Found</h3>
                        <p style="color: var(--text-muted); max-width: 450px; margin: 0 auto 1.5rem auto;">
                            We couldn't find any rooms matching your exact filter preferences or dates. Try adjusting your dates, price range, or clearing filters.
                        </p>
                        <a href="<?= base_url('rooms.php') ?>" class="btn btn-outline">
                            <i class="fa-solid fa-arrow-rotate-left"></i> Reset All Filters
                        </a>
                    </div>
                <?php else: ?>
                    <div class="rooms-grid">
                        <?php foreach ($rooms as $room): ?>
                            <div class="room-card">
                                <div class="room-card-image">
                                    <img src="<?= get_room_primary_image($room['id']) ?>" alt="<?= e($room['name']) ?>">
                                    <?php if ($room['is_featured']): ?>
                                        <span class="badge badge-gold room-card-badge">Featured</span>
                                    <?php endif; ?>
                                    <div class="room-card-price">
                                        <span class="amount"><?= format_price($room['price_per_night']) ?></span> / night
                                    </div>
                                </div>

                                <div class="room-card-body">
                                    <span class="room-card-type"><?= e($room['room_type']) ?></span>
                                    <h3 class="room-card-title">
                                        <a href="<?= base_url('room-details.php?id=' . $room['id'] . (!empty($checkIn) ? '&check_in=' . urlencode($checkIn) . '&check_out=' . urlencode($checkOut) : '')) ?>">
                                            <?= e($room['name']) ?>
                                        </a>
                                    </h3>
                                    <p class="room-card-desc"><?= e($room['description']) ?></p>

                                    <div class="room-specs">
                                        <div class="room-spec-item">
                                            <i class="fa-solid fa-user-group"></i> Up to <?= $room['max_guests'] ?>
                                        </div>
                                        <div class="room-spec-item">
                                            <i class="fa-solid fa-bed"></i> <?= e($room['bed_type']) ?>
                                        </div>
                                        <div class="room-spec-item">
                                            <i class="fa-solid fa-ruler-combined"></i> <?= e($room['room_size']) ?>
                                        </div>
                                    </div>

                                    <div class="room-card-actions">
                                        <a href="<?= base_url('room-details.php?id=' . $room['id'] . (!empty($checkIn) ? '&check_in=' . urlencode($checkIn) . '&check_out=' . urlencode($checkOut) : '')) ?>" class="btn btn-outline btn-sm">
                                            View Details
                                        </a>
                                        <a href="<?= base_url('booking.php?room_id=' . $room['id'] . (!empty($checkIn) ? '&check_in=' . urlencode($checkIn) . '&check_out=' . urlencode($checkOut) . '&guests=' . urlencode($guests ?: 2) : '')) ?>" class="btn btn-gold btn-sm">
                                            Book Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <?php
                                $pageParams = $_GET;
                                $pageParams['page'] = $p;
                                $pageUrl = base_url('rooms.php?' . http_build_query($pageParams));
                                ?>
                                <a href="<?= $pageUrl ?>" class="page-link <?= $p === $page ? 'active' : '' ?>">
                                    <?= $p ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
