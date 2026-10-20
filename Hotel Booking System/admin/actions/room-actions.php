<?php
/**
 * Royal Stay - Admin Room Management Actions Controller
 */

require_once __DIR__ . '/../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/rooms.php'));
    exit;
}

if (!verify_csrf_token()) {
    set_flash('error', 'Security token expired. Please try again.');
    header('Location: ' . base_url('admin/rooms.php'));
    exit;
}

$pdo = get_db();
$action = sanitize($_POST['action'] ?? '');

// ==========================================
// 1. ADD NEW ROOM
// ==========================================
if ($action === 'add_room') {
    $name = sanitize($_POST['name'] ?? '');
    $roomType = sanitize($_POST['room_type'] ?? 'Luxury Suite');
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price_per_night'] ?? 0);
    $maxGuests = max(1, (int)($_POST['max_guests'] ?? 2));
    $bedType = sanitize($_POST['bed_type'] ?? 'King Bed');
    $roomSize = sanitize($_POST['room_size'] ?? '500 sq ft');
    $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
    $status = sanitize($_POST['status'] ?? 'available');
    $facilityIds = isset($_POST['facilities']) && is_array($_POST['facilities']) ? array_map('intval', $_POST['facilities']) : [];
    $imagePath = sanitize($_POST['image_path'] ?? 'assets/images/room_default.jpg');

    if (empty($name) || $price <= 0 || empty($description)) {
        set_flash('error', 'Please provide a valid suite title, positive price, and description.');
        header('Location: ' . base_url('admin/rooms.php'));
        exit;
    }

    $slug = create_slug($name);
    // Ensure slug uniqueness
    $slugCheck = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE slug = ?");
    $slugCheck->execute([$slug]);
    if ($slugCheck->fetchColumn() > 0) {
        $slug .= '-' . rand(100, 999);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO rooms (name, slug, room_type, description, price_per_night, max_guests, bed_type, room_size, status, is_featured, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $slug, $roomType, $description, $price, $maxGuests, $bedType, $roomSize, $status, $isFeatured]);
        $roomId = (int)$pdo->lastInsertId();

        // Assign facilities
        if (!empty($facilityIds)) {
            $fStmt = $pdo->prepare("INSERT INTO room_facilities (room_id, facility_id) VALUES (?, ?)");
            foreach ($facilityIds as $fId) {
                $fStmt->execute([$roomId, $fId]);
            }
        }

        // Insert primary image
        $imgStmt = $pdo->prepare("INSERT INTO room_images (room_id, image_path, is_primary, sort_order, created_at) VALUES (?, ?, 1, 1, NOW())");
        $imgStmt->execute([$roomId, $imagePath]);

        $pdo->commit();

        set_flash('success', "New suite '{$name}' created successfully.");
        header('Location: ' . base_url('admin/rooms.php'));
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Add room error: " . $e->getMessage());
        set_flash('error', 'Error adding room: ' . $e->getMessage());
        header('Location: ' . base_url('admin/rooms.php'));
        exit;
    }
}

// ==========================================
// 2. EDIT ROOM
// ==========================================
if ($action === 'edit_room') {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $roomType = sanitize($_POST['room_type'] ?? 'Luxury Suite');
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price_per_night'] ?? 0);
    $maxGuests = max(1, (int)($_POST['max_guests'] ?? 2));
    $bedType = sanitize($_POST['bed_type'] ?? 'King Bed');
    $roomSize = sanitize($_POST['room_size'] ?? '500 sq ft');
    $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
    $status = sanitize($_POST['status'] ?? 'available');
    $facilityIds = isset($_POST['facilities']) && is_array($_POST['facilities']) ? array_map('intval', $_POST['facilities']) : [];
    $imagePath = sanitize($_POST['image_path'] ?? '');

    if ($roomId <= 0 || empty($name) || $price <= 0) {
        set_flash('error', 'Invalid room details provided.');
        header('Location: ' . base_url('admin/rooms.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $updateStmt = $pdo->prepare("
            UPDATE rooms 
            SET name = ?, room_type = ?, description = ?, price_per_night = ?, 
                max_guests = ?, bed_type = ?, room_size = ?, status = ?, is_featured = ?
            WHERE id = ?
        ");
        $updateStmt->execute([$name, $roomType, $description, $price, $maxGuests, $bedType, $roomSize, $status, $isFeatured, $roomId]);

        // Update facilities: clear and re-insert
        $delF = $pdo->prepare("DELETE FROM room_facilities WHERE room_id = ?");
        $delF->execute([$roomId]);

        if (!empty($facilityIds)) {
            $fStmt = $pdo->prepare("INSERT INTO room_facilities (room_id, facility_id) VALUES (?, ?)");
            foreach ($facilityIds as $fId) {
                $fStmt->execute([$roomId, $fId]);
            }
        }

        // Update primary image if provided
        if (!empty($imagePath)) {
            $checkImg = $pdo->prepare("SELECT id FROM room_images WHERE room_id = ? AND is_primary = 1 LIMIT 1");
            $checkImg->execute([$roomId]);
            $existingImgId = $checkImg->fetchColumn();

            if ($existingImgId) {
                $updImg = $pdo->prepare("UPDATE room_images SET image_path = ? WHERE id = ?");
                $updImg->execute([$imagePath, $existingImgId]);
            } else {
                $insImg = $pdo->prepare("INSERT INTO room_images (room_id, image_path, is_primary, sort_order, created_at) VALUES (?, ?, 1, 1, NOW())");
                $insImg->execute([$roomId, $imagePath]);
            }
        }

        $pdo->commit();

        set_flash('success', "Suite '{$name}' updated successfully.");
        header('Location: ' . base_url('admin/rooms.php'));
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Edit room error: " . $e->getMessage());
        set_flash('error', 'Error updating suite details.');
        header('Location: ' . base_url('admin/rooms.php'));
        exit;
    }
}

// ==========================================
// 3. TOGGLE STATUS
// ==========================================
if ($action === 'toggle_status') {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $statusStmt = $pdo->prepare("SELECT status, name FROM rooms WHERE id = ?");
    $statusStmt->execute([$roomId]);
    $room = $statusStmt->fetch();

    if ($room) {
        $nextStatus = $room['status'] === 'available' ? 'maintenance' : 'available';
        $upd = $pdo->prepare("UPDATE rooms SET status = ? WHERE id = ?");
        $upd->execute([$nextStatus, $roomId]);
        set_flash('success', "Suite '{$room['name']}' status switched to {$nextStatus}.");
    }

    header('Location: ' . base_url('admin/rooms.php'));
    exit;
}

// ==========================================
// 4. DELETE ROOM
// ==========================================
if ($action === 'delete_room') {
    $roomId = (int)($_POST['room_id'] ?? 0);

    // Check if room has active bookings
    $checkB = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND booking_status IN ('pending', 'confirmed')");
    $checkB->execute([$roomId]);
    if ($checkB->fetchColumn() > 0) {
        set_flash('error', 'Cannot remove suite with active reservations. Please mark it under maintenance instead.');
        header('Location: ' . base_url('admin/rooms.php'));
        exit;
    }

    $delStmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $delStmt->execute([$roomId]);

    set_flash('success', 'Suite removed from inventory successfully.');
    header('Location: ' . base_url('admin/rooms.php'));
    exit;
}

header('Location: ' . base_url('admin/rooms.php'));
exit;
