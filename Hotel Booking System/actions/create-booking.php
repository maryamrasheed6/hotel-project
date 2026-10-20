<?php
/**
 * Royal Stay - Process Room Reservation Action
 * Concurrency-safe booking creation with transaction locking
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('rooms.php'));
    exit;
}

// 1. Verify CSRF
if (!verify_csrf_token()) {
    set_flash('error', 'Security token expired or invalid. Please try submitting the reservation again.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? base_url('rooms.php')));
    exit;
}

$pdo = get_db();

// 2. Sanitize and retrieve inputs
$roomId = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
$guestName = sanitize($_POST['guest_name'] ?? '');
$guestEmail = filter_var(trim($_POST['guest_email'] ?? ''), FILTER_VALIDATE_EMAIL);
$guestPhone = sanitize($_POST['guest_phone'] ?? '');
$checkIn = sanitize($_POST['check_in'] ?? '');
$checkOut = sanitize($_POST['check_out'] ?? '');
$guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;
$specialRequests = sanitize($_POST['special_requests'] ?? '');
$paymentMethod = sanitize($_POST['payment_method'] ?? 'Pay at Hotel');

$refererUrl = base_url("booking.php?room_id={$roomId}&check_in=" . urlencode($checkIn) . "&check_out=" . urlencode($checkOut) . "&guests=" . urlencode($guests));

// 3. Basic Validation
if (empty($guestName) || !$guestEmail || empty($guestPhone) || empty($checkIn) || empty($checkOut)) {
    set_flash('error', 'Please complete all required fields with valid information.');
    header("Location: {$refererUrl}");
    exit;
}

$today = date('Y-m-d');
if (strtotime($checkIn) < strtotime($today)) {
    set_flash('error', 'Check-in date cannot be in the past.');
    header("Location: {$refererUrl}");
    exit;
}

if (strtotime($checkIn) >= strtotime($checkOut)) {
    set_flash('error', 'Check-out date must be after check-in date.');
    header("Location: {$refererUrl}");
    exit;
}

try {
    // 4. Begin Database Transaction
    $pdo->beginTransaction();

    // Verify room status and fetch actual price
    $roomStmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ? FOR UPDATE");
    $roomStmt->execute([$roomId]);
    $room = $roomStmt->fetch();

    if (!$room || $room['status'] !== 'available') {
        $pdo->rollBack();
        set_flash('error', 'The selected room is unavailable or currently under maintenance.');
        header('Location: ' . base_url('rooms.php'));
        exit;
    }

    if ($guests > (int)$room['max_guests']) {
        $pdo->rollBack();
        set_flash('error', "The selected suite accommodates a maximum of {$room['max_guests']} guests.");
        header("Location: {$refererUrl}");
        exit;
    }

    // 5. Concurrency Check: Check for overlapping active reservations with FOR UPDATE lock
    $conflictStmt = $pdo->prepare("
        SELECT id FROM bookings 
        WHERE room_id = :room_id 
          AND booking_status IN ('pending', 'confirmed') 
          AND NOT (check_out <= :check_in OR check_in >= :check_out)
        FOR UPDATE
    ");
    $conflictStmt->execute([
        ':room_id'   => $roomId,
        ':check_in'  => $checkIn,
        ':check_out' => $checkOut
    ]);

    if ($conflictStmt->fetch()) {
        $pdo->rollBack();
        set_flash('error', 'Apologies, this room was just reserved by another guest for the selected dates. Please choose alternative dates or another suite.');
        header("Location: " . base_url('room-details.php?id=' . $roomId));
        exit;
    }

    // 6. Calculate Pricing on Server (Never trust client-side prices)
    $nights = calculate_nights($checkIn, $checkOut);
    $pricePerNight = (float)$room['price_per_night'];
    $subtotal = $nights * $pricePerNight;
    $taxRate = (float)get_setting('tax_rate_percent', '10.00');
    $taxAmount = ($subtotal * $taxRate) / 100;
    $totalAmount = $subtotal + $taxAmount;

    // Check if user is logged in
    $userId = is_logged_in() ? (int)$_SESSION['user_id'] : null;

    // Generate unique reference
    $bookingReference = generate_booking_reference();

    // 7. Insert Booking
    $insertSql = "
        INSERT INTO bookings (
            booking_reference, user_id, room_id, guest_name, guest_email, guest_phone,
            check_in, check_out, guests, price_per_night, nights, subtotal,
            additional_charges, total_amount, booking_status, payment_method,
            payment_status, special_requests, created_at
        ) VALUES (
            :ref, :uid, :rid, :name, :email, :phone,
            :cin, :cout, :guests, :rate, :nights, :subtotal,
            :charges, :total, 'confirmed', :pay_method,
            'unpaid', :notes, NOW()
        )
    ";

    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        ':ref'        => $bookingReference,
        ':uid'        => $userId,
        ':rid'        => $roomId,
        ':name'       => $guestName,
        ':email'      => $guestEmail,
        ':phone'      => $guestPhone,
        ':cin'        => $checkIn,
        ':cout'       => $checkOut,
        ':guests'     => $guests,
        ':rate'       => $pricePerNight,
        ':nights'     => $nights,
        ':subtotal'   => $subtotal,
        ':charges'    => $taxAmount,
        ':total'      => $totalAmount,
        ':pay_method' => $paymentMethod,
        ':notes'      => $specialRequests
    ]);

    $bookingId = (int)$pdo->lastInsertId();

    // 8. Log Booking Status History
    $historyStmt = $pdo->prepare("
        INSERT INTO booking_status_history (booking_id, old_status, new_status, changed_by, note, created_at)
        VALUES (?, NULL, 'confirmed', ?, ?, NOW())
    ");
    $historyStmt->execute([
        $bookingId,
        $userId ? 'Customer (Account #' . $userId . ')' : 'Guest (' . $guestEmail . ')',
        'Reservation submitted online via website. Payment due upon arrival.'
    ]);

    // Commit transaction
    $pdo->commit();

    // 9. Store reference in session and redirect to confirmation
    $_SESSION['confirmed_booking_ref'] = $bookingReference;
    set_flash('success', "Your reservation {$bookingReference} has been successfully confirmed!");
    header('Location: ' . base_url('booking-confirmation.php?ref=' . urlencode($bookingReference)));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Booking creation error: " . $e->getMessage());
    set_flash('error', 'A system error occurred while securing your reservation. Please try again or contact concierge.');
    header("Location: {$refererUrl}");
    exit;
}
