<?php
/**
 * Royal Stay - Core Helper Functions
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/csrf.php';

/**
 * Escape output for safe HTML display
 *
 * @param mixed $value
 * @return string
 */
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize string input
 *
 * @param string|null $input
 * @return string
 */
function sanitize(?string $input): string {
    return trim(strip_tags((string)$input));
}

/**
 * Detect base application URL
 *
 * @param string $path
 * @return string
 */
function base_url(string $path = ''): string {
    static $base = null;
    if ($base === null) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Find path relative to project root
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = str_replace('\\', '/', dirname($script));
        
        // Repeatedly strip subdirectories (admin, actions, includes, etc.) until app root
        while (in_array(basename($dir), ['admin', 'actions', 'includes'], true)) {
            $parent = dirname($dir);
            if ($parent === $dir) break;
            $dir = $parent;
        }
        $dir = rtrim($dir, '/\\');
        
        $base = $protocol . $host . ($dir === '' ? '' : $dir);
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Fetch a setting from hotel_settings table with runtime caching
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function get_setting(string $key, string $default = ''): string {
    static $settings = null;

    if ($settings === null) {
        try {
            $pdo = get_db();
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM hotel_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        } catch (PDOException $e) {
            $settings = [];
        }
    }

    return $settings[$key] ?? $default;
}

/**
 * Format currency with configured symbol
 *
 * @param float|int|string $amount
 * @return string
 */
function format_price($amount): string {
    $symbol = get_setting('currency_symbol', '$');
    return $symbol . number_format((float)$amount, 2);
}

/**
 * Set a flash message into session
 *
 * @param string $type success|error|warning|info
 * @param string $message
 */
function set_flash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type'    => in_array($type, ['success', 'error', 'warning', 'info']) ? $type : 'info',
        'message' => $message
    ];
}

/**
 * Retrieve and clear flash message
 *
 * @return array|null
 */
function get_flash(): ?array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render flash message as modern alert toast
 *
 * @return string
 */
function render_flash(): string {
    $flash = get_flash();
    if (!$flash) {
        return '';
    }

    $type = e($flash['type']);
    $msg = e($flash['message']);
    $icon = match($type) {
        'success' => 'fa-check-circle',
        'error'   => 'fa-triangle-exclamation',
        'warning' => 'fa-circle-exclamation',
        default   => 'fa-circle-info'
    };

    return "
    <div class='flash-container'>
        <div class='flash-alert flash-{$type}'>
            <div class='flash-icon'><i class='fa-solid {$icon}'></i></div>
            <div class='flash-content'>{$msg}</div>
            <button type='button' class='flash-close' onclick='this.parentElement.remove()'>&times;</button>
        </div>
    </div>";
}

/**
 * Generate a unique booking reference
 *
 * @return string
 */
function generate_booking_reference(): string {
    $year = date('Y');
    $random = strtoupper(bin2hex(random_bytes(3))); // 6 hex chars
    return "RS-{$year}-{$random}";
}

/**
 * Check if a room is available for given dates
 *
 * @param int $roomId
 * @param string $checkIn (YYYY-MM-DD)
 * @param string $checkOut (YYYY-MM-DD)
 * @param int|null $excludeBookingId
 * @return bool
 */
function is_room_available(int $roomId, string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool {
    $pdo = get_db();

    // Check room status first
    $roomStmt = $pdo->prepare("SELECT status FROM rooms WHERE id = ?");
    $roomStmt->execute([$roomId]);
    $roomStatus = $roomStmt->fetchColumn();

    if ($roomStatus !== 'available') {
        return false;
    }

    // Check date validity
    if (strtotime($checkIn) >= strtotime($checkOut)) {
        return false;
    }

    $sql = "SELECT COUNT(*) FROM bookings 
            WHERE room_id = :room_id 
              AND booking_status IN ('pending', 'confirmed')
              AND NOT (check_out <= :check_in OR check_in >= :check_out)";

    $params = [
        ':room_id'   => $roomId,
        ':check_in'  => $checkIn,
        ':check_out' => $checkOut
    ];

    if ($excludeBookingId !== null) {
        $sql .= " AND id != :exclude_id";
        $params[':exclude_id'] = $excludeBookingId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $conflicts = (int)$stmt->fetchColumn();

    return $conflicts === 0;
}

/**
 * Calculate nights between two dates
 *
 * @param string $checkIn
 * @param string $checkOut
 * @return int
 */
function calculate_nights(string $checkIn, string $checkOut): int {
    $in = new DateTime($checkIn);
    $out = new DateTime($checkOut);
    $diff = $in->diff($out);
    return max(1, (int)$diff->days);
}

/**
 * Get primary image for a room
 *
 * @param int $roomId
 * @return string
 */
function get_room_primary_image(int $roomId): string {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT image_path FROM room_images WHERE room_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC LIMIT 1");
    $stmt->execute([$roomId]);
    $image = $stmt->fetchColumn();

    if (!empty($image) && file_exists(__DIR__ . '/../' . $image)) {
        return base_url($image);
    }
    // Fallback room image
    return base_url('assets/images/room_default.jpg');
}

/**
 * Get all images for a room
 *
 * @param int $roomId
 * @return array
 */
function get_room_images(int $roomId): array {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM room_images WHERE room_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC");
    $stmt->execute([$roomId]);
    $images = $stmt->fetchAll();

    if (empty($images)) {
        return [[
            'id' => 0,
            'room_id' => $roomId,
            'image_path' => 'assets/images/room_default.jpg',
            'is_primary' => 1
        ]];
    }

    return $images;
}

/**
 * Get facilities for a room
 *
 * @param int $roomId
 * @return array
 */
function get_room_facilities(int $roomId): array {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT f.* FROM facilities f
        INNER JOIN room_facilities rf ON f.id = rf.facility_id
        WHERE rf.room_id = ? AND f.status = 'active'
        ORDER BY f.id ASC
    ");
    $stmt->execute([$roomId]);
    return $stmt->fetchAll();
}

/**
 * Log status change for a booking
 *
 * @param int $bookingId
 * @param string|null $oldStatus
 * @param string $newStatus
 * @param string $changedBy
 * @param string|null $note
 * @return void
 */
function log_booking_status_change(int $bookingId, ?string $oldStatus, string $newStatus, string $changedBy = 'System', ?string $note = null): void {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO booking_status_history (booking_id, old_status, new_status, changed_by, note, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$bookingId, $oldStatus, $newStatus, $changedBy, $note]);
}

/**
 * Generate URL safe slug
 *
 * @param string $text
 * @return string
 */
function create_slug(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}
