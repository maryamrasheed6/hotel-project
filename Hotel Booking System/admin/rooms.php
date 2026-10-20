<?php
/**
 * Royal Stay - Admin Suites & Rooms Inventory Management (admin/rooms.php)
 */

$adminPageTitle = 'Suites & Rooms Inventory';
$activeAdminNav = 'rooms';

require_once __DIR__ . '/includes/admin-header.php';

$pdo = get_db();

// Fetch all rooms with their primary images
$roomsStmt = $pdo->query("
    SELECT r.*, ri.image_path as primary_image
    FROM rooms r
    LEFT JOIN room_images ri ON r.id = ri.room_id AND ri.is_primary = 1
    ORDER BY r.id ASC
");
$rooms = $roomsStmt->fetchAll();

// Fetch all active facilities
$facilitiesStmt = $pdo->query("SELECT * FROM facilities WHERE status = 'active' ORDER BY id ASC");
$allFacilities = $facilitiesStmt->fetchAll();

// Fetch facility mapping per room
$roomFacilitiesMap = [];
$rfStmt = $pdo->query("SELECT room_id, facility_id FROM room_facilities");
while ($row = $rfStmt->fetch()) {
    $roomFacilitiesMap[$row['room_id']][] = (int)$row['facility_id'];
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">
            <i class="fa-solid fa-door-open" style="color: var(--admin-accent);"></i> All Suites & Rooms (<?= count($rooms) ?>)
        </h3>
        <button type="button" class="btn btn-gold btn-sm" onclick="openModal('addRoomModal')">
            <i class="fa-solid fa-plus"></i> Add New Suite
        </button>
    </div>

    <div class="admin-card-body">
        
        <!-- Live Search -->
        <div class="admin-filter-bar">
            <input type="text" class="search-bar-input data-table-search" data-target="roomsTable" placeholder="Filter by suite title, room type, or bed configuration...">
        </div>

        <!-- Rooms Table -->
        <div class="table-responsive">
            <table class="admin-table" id="roomsTable">
                <thead>
                    <tr>
                        <th style="width: 70px;">Image</th>
                        <th>Suite Name & Type</th>
                        <th>Nightly Rate</th>
                        <th>Capacity</th>
                        <th>Size & Bed</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $rm): ?>
                        <?php
                        $img = !empty($rm['primary_image']) && file_exists(__DIR__ . '/../' . $rm['primary_image']) ? base_url($rm['primary_image']) : base_url('assets/images/room_default.jpg');
                        $facIds = $roomFacilitiesMap[$rm['id']] ?? [];
                        $roomJson = array_merge($rm, ['facility_ids' => $facIds, 'img_url' => $img]);
                        ?>
                        <tr>
                            <td>
                                <img src="<?= e($img) ?>" alt="<?= e($rm['name']) ?>" class="table-img">
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--admin-sidebar); font-size: 0.95rem;">
                                    <?= e($rm['name']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--admin-accent); font-weight: 600; text-transform: uppercase;">
                                    <?= e($rm['room_type']) ?>
                                </div>
                            </td>
                            <td>
                                <strong style="font-size: 1rem; color: var(--admin-sidebar);"><?= format_price($rm['price_per_night']) ?></strong>
                                <span style="font-size: 0.72rem; color: var(--admin-text-muted);">/ night</span>
                            </td>
                            <td>
                                <div style="font-size: 0.88rem; font-weight: 500;">
                                    <i class="fa-solid fa-user-group" style="color: var(--admin-accent);"></i> <?= $rm['max_guests'] ?> Guests
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;"><?= e($rm['bed_type']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?= e($rm['room_size']) ?></div>
                            </td>
                            <td>
                                <span class="status-badge status-<?= e($rm['status']) ?>">
                                    <?= ucfirst(e($rm['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($rm['is_featured']): ?>
                                    <span style="background: rgba(217, 164, 65, 0.15); color: #B45309; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.72rem; font-weight: 700;">
                                        ★ Featured
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--admin-text-muted); font-size: 0.8rem;">Standard</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-btns" style="justify-content: flex-end;">
                                    <!-- Edit Suite -->
                                    <button type="button" class="btn-icon" title="Edit Suite Details" onclick="openEditRoomModal(<?= htmlspecialchars(json_encode($roomJson), ENT_QUOTES, 'UTF-8') ?>)">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <!-- Quick Toggle Status -->
                                    <form action="<?= base_url('admin/actions/room-actions.php') ?>" method="POST" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="room_id" value="<?= $rm['id'] ?>">
                                        <button type="submit" class="btn-icon" title="Toggle Available / Maintenance">
                                            <i class="fa-solid <?= $rm['status'] === 'available' ? 'fa-toggle-on' : 'fa-toggle-off' ?>" style="color: <?= $rm['status'] === 'available' ? '#10B981' : '#64748B' ?>;"></i>
                                        </button>
                                    </form>

                                    <!-- View Live Single Page -->
                                    <a href="<?= base_url('room-details.php?id=' . $rm['id']) ?>" target="_blank" class="btn-icon" title="View Public Page">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <!-- Delete Suite -->
                                    <form action="<?= base_url('admin/actions/room-actions.php') ?>" method="POST" style="display: inline;" data-confirm="Are you sure you want to remove '<?= e($rm['name']) ?>' from inventory?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_room">
                                        <input type="hidden" name="room_id" value="<?= $rm['id'] ?>">
                                        <button type="submit" class="btn-icon btn-icon-error" title="Delete Suite">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Modal 1: Add New Suite -->
<div id="addRoomModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 680px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.25);">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--admin-border); display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; position: sticky; top: 0; z-index: 10;">
            <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--admin-sidebar); margin: 0;">
                <i class="fa-solid fa-plus" style="color: var(--admin-accent);"></i> Add New Suite to Inventory
            </h3>
            <button type="button" onclick="closeModal('addRoomModal')" style="background: none; border: none; font-size: 1.5rem; color: var(--admin-text-muted); cursor: pointer;">&times;</button>
        </div>

        <form action="<?= base_url('admin/actions/room-actions.php') ?>" method="POST" style="padding: 2rem;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_room">

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="addRoomName">Suite Title *</label>
                    <input type="text" name="name" id="addRoomName" class="form-control" placeholder="e.g. Sapphire Ocean Penthouse" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="addRoomType">Room Category *</label>
                    <select name="room_type" id="addRoomType" class="form-control" required>
                        <option value="Presidential Penthouse">Presidential Penthouse</option>
                        <option value="Luxury Villa">Luxury Villa</option>
                        <option value="Deluxe Suite">Deluxe Suite</option>
                        <option value="Executive Suite">Executive Suite</option>
                        <option value="Superior Room">Superior Room</option>
                        <option value="Garden Bungalow">Garden Bungalow</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label" for="addPrice">Nightly Rate ($) *</label>
                    <input type="number" step="0.01" name="price_per_night" id="addPrice" class="form-control" placeholder="350.00" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="addMaxGuests">Max Guests *</label>
                    <input type="number" name="max_guests" id="addMaxGuests" class="form-control" value="2" min="1" max="10" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="addRoomSize">Room Size *</label>
                    <input type="text" name="room_size" id="addRoomSize" class="form-control" value="750 sq ft" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="addBedType">Bed Configuration *</label>
                    <input type="text" name="bed_type" id="addBedType" class="form-control" value="California King Bed" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="addStatus">Initial Status</label>
                    <select name="status" id="addStatus" class="form-control">
                        <option value="available">Available</option>
                        <option value="maintenance">Under Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="addImagePath">Primary Image Asset</label>
                <select name="image_path" id="addImagePath" class="form-control">
                    <option value="assets/images/room_penthouse.jpg">Room Penthouse (assets/images/room_penthouse.jpg)</option>
                    <option value="assets/images/room_ocean_villa.jpg">Ocean Villa (assets/images/room_ocean_villa.jpg)</option>
                    <option value="assets/images/room_deluxe_suite.jpg">Deluxe Suite (assets/images/room_deluxe_suite.jpg)</option>
                    <option value="assets/images/room_lagoon_suite.jpg">Lagoon Suite (assets/images/room_lagoon_suite.jpg)</option>
                    <option value="assets/images/room_heritage_king.jpg">Heritage King (assets/images/room_heritage_king.jpg)</option>
                    <option value="assets/images/room_garden_bungalow.jpg">Garden Bungalow (assets/images/room_garden_bungalow.jpg)</option>
                    <option value="assets/images/room_default.jpg">Default Room Image</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="addDesc">Full Suite Description *</label>
                <textarea name="description" id="addDesc" class="form-control" rows="3" placeholder="Describe the panoramic views, interior finishes, and bespoke amenities..." required></textarea>
            </div>

            <!-- Facilities Checkboxes -->
            <div class="form-group">
                <label class="form-label">Inclusive Suite Facilities</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; background: var(--admin-bg); padding: 1rem; border-radius: 8px;">
                    <?php foreach ($allFacilities as $fac): ?>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer;">
                            <input type="checkbox" name="facilities[]" value="<?= $fac['id'] ?>" checked>
                            <i class="<?= e($fac['icon']) ?>" style="color: var(--admin-sidebar); width: 16px;"></i>
                            <span><?= e($fac['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1" checked>
                    <span style="font-weight: 600; color: var(--admin-sidebar);">Mark as Featured Suite on Homepage</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('addRoomModal')">Cancel</button>
                <button type="submit" class="btn btn-gold">
                    <i class="fa-solid fa-plus"></i> Save Suite
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Suite -->
<div id="editRoomModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 680px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.25);">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--admin-border); display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; position: sticky; top: 0; z-index: 10;">
            <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--admin-sidebar); margin: 0;">
                <i class="fa-solid fa-pen" style="color: var(--admin-accent);"></i> Edit Suite Details
            </h3>
            <button type="button" onclick="closeModal('editRoomModal')" style="background: none; border: none; font-size: 1.5rem; color: var(--admin-text-muted); cursor: pointer;">&times;</button>
        </div>

        <form action="<?= base_url('admin/actions/room-actions.php') ?>" method="POST" style="padding: 2rem;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="edit_room">
            <input type="hidden" name="room_id" id="editRoomId">

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="editRoomName">Suite Title *</label>
                    <input type="text" name="name" id="editRoomName" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editRoomType">Room Category *</label>
                    <select name="room_type" id="editRoomType" class="form-control" required>
                        <option value="Presidential Penthouse">Presidential Penthouse</option>
                        <option value="Luxury Villa">Luxury Villa</option>
                        <option value="Deluxe Suite">Deluxe Suite</option>
                        <option value="Executive Suite">Executive Suite</option>
                        <option value="Superior Room">Superior Room</option>
                        <option value="Garden Bungalow">Garden Bungalow</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label" for="editPrice">Nightly Rate ($) *</label>
                    <input type="number" step="0.01" name="price_per_night" id="editPrice" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editMaxGuests">Max Guests *</label>
                    <input type="number" name="max_guests" id="editMaxGuests" class="form-control" min="1" max="10" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editRoomSize">Room Size *</label>
                    <input type="text" name="room_size" id="editRoomSize" class="form-control" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="editBedType">Bed Configuration *</label>
                    <input type="text" name="bed_type" id="editBedType" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editStatus">Status</label>
                    <select name="status" id="editStatus" class="form-control">
                        <option value="available">Available</option>
                        <option value="maintenance">Under Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="editImagePath">Primary Image Asset</label>
                <select name="image_path" id="editImagePath" class="form-control">
                    <option value="assets/images/room_penthouse.jpg">Room Penthouse (assets/images/room_penthouse.jpg)</option>
                    <option value="assets/images/room_ocean_villa.jpg">Ocean Villa (assets/images/room_ocean_villa.jpg)</option>
                    <option value="assets/images/room_deluxe_suite.jpg">Deluxe Suite (assets/images/room_deluxe_suite.jpg)</option>
                    <option value="assets/images/room_lagoon_suite.jpg">Lagoon Suite (assets/images/room_lagoon_suite.jpg)</option>
                    <option value="assets/images/room_heritage_king.jpg">Heritage King (assets/images/room_heritage_king.jpg)</option>
                    <option value="assets/images/room_garden_bungalow.jpg">Garden Bungalow (assets/images/room_garden_bungalow.jpg)</option>
                    <option value="assets/images/room_default.jpg">Default Room Image</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="editDesc">Full Description *</label>
                <textarea name="description" id="editDesc" class="form-control" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Inclusive Suite Facilities</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; background: var(--admin-bg); padding: 1rem; border-radius: 8px;">
                    <?php foreach ($allFacilities as $fac): ?>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer;">
                            <input type="checkbox" name="facilities[]" value="<?= $fac['id'] ?>" class="edit-facility-check" id="editFac_<?= $fac['id'] ?>">
                            <i class="<?= e($fac['icon']) ?>" style="color: var(--admin-sidebar); width: 16px;"></i>
                            <span><?= e($fac['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
                    <input type="checkbox" name="is_featured" id="editFeatured" value="1">
                    <span style="font-weight: 600; color: var(--admin-sidebar);">Mark as Featured Suite on Homepage</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('editRoomModal')">Cancel</button>
                <button type="submit" class="btn btn-gold">
                    <i class="fa-solid fa-check"></i> Update Suite
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditRoomModal(room) {
    document.getElementById('editRoomId').value = room.id;
    document.getElementById('editRoomName').value = room.name;
    document.getElementById('editRoomType').value = room.room_type;
    document.getElementById('editPrice').value = parseFloat(room.price_per_night).toFixed(2);
    document.getElementById('editMaxGuests').value = room.max_guests;
    document.getElementById('editRoomSize').value = room.room_size;
    document.getElementById('editBedType').value = room.bed_type;
    document.getElementById('editStatus').value = room.status;
    document.getElementById('editDesc').value = room.description;
    document.getElementById('editFeatured').checked = parseInt(room.is_featured) === 1;

    if (room.primary_image) {
        document.getElementById('editImagePath').value = room.primary_image;
    }

    // Set facility checkboxes
    document.querySelectorAll('.edit-facility-check').forEach(cb => {
        const fId = parseInt(cb.value);
        cb.checked = Array.isArray(room.facility_ids) && room.facility_ids.includes(fId);
    });

    openModal('editRoomModal');
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
