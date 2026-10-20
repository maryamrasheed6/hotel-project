-- ========================================================
-- Royal Stay - Complete Hotel Booking Management System
-- Database Schema & Sample Dataset
-- Compatible with MySQL 5.7+ / 8.0+ & MariaDB
-- ========================================================

CREATE DATABASE IF NOT EXISTS `royal_stay` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `royal_stay`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `booking_status_history`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `room_facilities`;
DROP TABLE IF EXISTS `room_images`;
DROP TABLE IF EXISTS `facilities`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `hotel_settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 1. Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(30) NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Table structure for table `rooms`
-- --------------------------------------------------------
CREATE TABLE `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL UNIQUE,
  `room_type` VARCHAR(80) NOT NULL,
  `description` TEXT NOT NULL,
  `price_per_night` DECIMAL(10,2) NOT NULL,
  `max_guests` INT NOT NULL DEFAULT 2,
  `bed_type` VARCHAR(80) NOT NULL DEFAULT 'King Bed',
  `room_size` VARCHAR(50) NOT NULL DEFAULT '450 sq ft',
  `status` ENUM('available', 'maintenance', 'inactive') NOT NULL DEFAULT 'available',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_rooms_status` (`status`),
  INDEX `idx_rooms_featured` (`is_featured`),
  INDEX `idx_rooms_type` (`room_type`),
  INDEX `idx_rooms_price` (`price_per_night`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Table structure for table `room_images`
-- --------------------------------------------------------
CREATE TABLE `room_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_room_images_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Table structure for table `facilities`
-- --------------------------------------------------------
CREATE TABLE `facilities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(60) NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Table structure for table `room_facilities`
-- --------------------------------------------------------
CREATE TABLE `room_facilities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT NOT NULL,
  `facility_id` INT NOT NULL,
  UNIQUE KEY `unique_room_facility` (`room_id`, `facility_id`),
  CONSTRAINT `fk_rf_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rf_facility` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Table structure for table `bookings`
-- --------------------------------------------------------
CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_reference` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT NULL,
  `room_id` INT NOT NULL,
  `guest_name` VARCHAR(100) NOT NULL,
  `guest_email` VARCHAR(150) NOT NULL,
  `guest_phone` VARCHAR(30) NOT NULL,
  `check_in` DATE NOT NULL,
  `check_out` DATE NOT NULL,
  `guests` INT NOT NULL DEFAULT 1,
  `price_per_night` DECIMAL(10,2) NOT NULL,
  `nights` INT NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `additional_charges` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `booking_status` ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Pay at Hotel',
  `payment_status` ENUM('unpaid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid',
  `special_requests` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bookings_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT,
  INDEX `idx_bookings_dates` (`check_in`, `check_out`),
  INDEX `idx_bookings_status` (`booking_status`),
  INDEX `idx_bookings_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Table structure for table `reviews`
-- --------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `booking_id` INT NULL,
  `guest_name` VARCHAR(100) NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `review_text` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reviews_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  INDEX `idx_reviews_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 8. Table structure for table `contact_messages`
-- --------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read') NOT NULL DEFAULT 'unread',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 9. Table structure for table `hotel_settings`
-- --------------------------------------------------------
CREATE TABLE `hotel_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(80) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 10. Table structure for table `booking_status_history`
-- --------------------------------------------------------
CREATE TABLE `booking_status_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `old_status` VARCHAR(50) NULL,
  `new_status` VARCHAR(50) NOT NULL,
  `changed_by` VARCHAR(100) NOT NULL DEFAULT 'System',
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bsh_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SAMPLE DATA INSERTION
-- ========================================================

-- Settings
INSERT INTO `hotel_settings` (`setting_key`, `setting_value`) VALUES
('hotel_name', 'Royal Stay Luxury Hotel & Resort'),
('hotel_tagline', 'Luxury Stay Starts Here'),
('contact_email', 'concierge@royalstay.com'),
('contact_phone', '+1 (800) 789-7829'),
('hotel_address', '100 Royal Palm Boulevard, Marina Bay, Suite 800'),
('currency_symbol', '$'),
('currency_code', 'USD'),
('tax_rate_percent', '10.00'),
('default_checkin_time', '15:00'),
('default_checkout_time', '11:00'),
('facebook_url', 'https://facebook.com/royalstay'),
('instagram_url', 'https://instagram.com/royalstay'),
('twitter_url', 'https://twitter.com/royalstay'),
('linkedin_url', 'https://linkedin.com/company/royalstay');

-- Facilities
INSERT INTO `facilities` (`id`, `name`, `icon`, `description`, `status`) VALUES
(1, 'Infinity Swimming Pool', 'fa-solid fa-water-ladder', 'Temperature-controlled outdoor heated infinity pool overlooking the horizon.', 'active'),
(2, 'Gourmet Restaurant & Bar', 'fa-solid fa-utensils', 'Award-winning Michelin-star cuisine, 24-hour dining, and artisanal cocktails.', 'active'),
(3, 'Luxury Spa & Wellness', 'fa-solid fa-spa', 'Holistic therapies, steam rooms, Finnish saunas, and signature aromatherapy.', 'active'),
(4, 'High-Speed Fiber Wi-Fi', 'fa-solid fa-wifi', 'Complimentary ultra-fast optical fiber connectivity throughout the property.', 'active'),
(5, 'Complimentary Valet Parking', 'fa-solid fa-square-parking', 'Secure private basement parking with EV fast-charging stations and valet.', 'active'),
(6, '24/7 Butler & Room Service', 'fa-solid fa-bell-concierge', 'Round-the-clock dedicated butler assistance and private dining delivery.', 'active'),
(7, 'Individual Climate Control', 'fa-solid fa-snowflake', 'State-of-the-art whisper-quiet air filtration and automated climate controls.', 'active'),
(8, 'Elite Fitness Center', 'fa-solid fa-dumbbell', 'Equipped with top-of-the-line Technogym machines, free weights, and trainers.', 'active');

-- Users
-- Passwords:
-- Admin: Admin@12345
-- Customer: Customer@12345
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`) VALUES
(1, 'Royal Stay Administrator', 'admin@royalstay.com', '+1 (800) 789-7829', '$2y$10$9LZiPqcOPBQr037svpZfY.iJz/c6DE1iooHtOp/rOVLTONrN./FFC', 'admin', 'active'),
(2, 'John Doe', 'john.doe@example.com', '+1 (555) 234-5678', '$2y$10$/euSJRhessJUNok05ZwA7Oy87vdFvctafSRRMbdAs5j/R4xFExpxu', 'customer', 'active'),
(3, 'Sophia Miller', 'sophia.miller@example.com', '+1 (555) 876-5432', '$2y$10$/euSJRhessJUNok05ZwA7Oy87vdFvctafSRRMbdAs5j/R4xFExpxu', 'customer', 'active');

-- Rooms
INSERT INTO `rooms` (`id`, `name`, `slug`, `room_type`, `description`, `price_per_night`, `max_guests`, `bed_type`, `room_size`, `status`, `is_featured`) VALUES
(1, 'The Royal Grand Penthouse', 'royal-grand-penthouse', 'Presidential Penthouse', 'Perched on the top floor, the Royal Grand Penthouse delivers breathtaking panoramic coastal vistas, a private plunge pool, Italian marble bath, bespoke dining quarters, and a dedicated butler team.', 490.00, 4, 'Custom Emperor King Bed', '1,450 sq ft', 'available', 1),
(2, 'Presidential Oceanfront Villa', 'presidential-oceanfront-villa', 'Luxury Villa', 'An oasis of serenity featuring direct ocean access, private sun deck, lavish walk-in rain showers, expansive open-plan lounge, and bespoke architectural fixtures crafted for discerning travelers.', 580.00, 6, '2 California King Beds', '1,750 sq ft', 'available', 1),
(3, 'Deluxe Sovereign Suite', 'deluxe-sovereign-suite', 'Deluxe Suite', 'Designed for sophisticated business and leisure stays, featuring warm wood accents, a plush king-size bed, workstation with ergonomic seating, and opulent city skyline balconies.', 280.00, 2, 'California King Bed', '680 sq ft', 'available', 1),
(4, 'Executive Lagoon Suite', 'executive-lagoon-suite', 'Executive Suite', 'Direct swim-out access to the tranquil resort lagoon. Includes a private terrace, sunken bathtub, complimentary premium espresso bar, and state-of-the-art entertainment system.', 340.00, 3, '1 King Bed + 1 Daybed', '820 sq ft', 'available', 1),
(5, 'Grand Heritage King Room', 'grand-heritage-king-room', 'Superior Room', 'Classic elegance meets contemporary luxury. Features plush organic linens, soundproof floor-to-ceiling glass, designer amenities, and peaceful interior garden views.', 195.00, 2, 'Luxury King Bed', '510 sq ft', 'available', 0),
(6, 'Premier Tropical Garden Bungalow', 'premier-tropical-garden-bungalow', 'Garden Bungalow', 'Nestled within fragrant botanical gardens, this bungalow offers total privacy, an outdoor soaking tub, shaded veranda, and bespoke natural decor.', 360.00, 4, '2 Queen Plush Beds', '920 sq ft', 'available', 0);

-- Room Facilities
INSERT INTO `room_facilities` (`room_id`, `facility_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8),
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8),
(3, 2), (3, 4), (3, 5), (3, 6), (3, 7), (3, 8),
(4, 1), (4, 4), (4, 5), (4, 6), (4, 7), (4, 8),
(5, 4), (5, 5), (5, 6), (5, 7),
(6, 1), (6, 3), (6, 4), (6, 5), (6, 6), (6, 7);

-- Room Images
INSERT INTO `room_images` (`id`, `room_id`, `image_path`, `is_primary`, `sort_order`) VALUES
(1, 1, 'assets/images/room_penthouse.jpg', 1, 1),
(2, 1, 'assets/images/room_penthouse_bath.jpg', 0, 2),
(3, 2, 'assets/images/room_ocean_villa.jpg', 1, 1),
(4, 2, 'assets/images/room_villa_deck.jpg', 0, 2),
(5, 3, 'assets/images/room_deluxe_suite.jpg', 1, 1),
(6, 4, 'assets/images/room_lagoon_suite.jpg', 1, 1),
(7, 5, 'assets/images/room_heritage_king.jpg', 1, 1),
(8, 6, 'assets/images/room_garden_bungalow.jpg', 1, 1);

-- Sample Bookings
INSERT INTO `bookings` (`id`, `booking_reference`, `user_id`, `room_id`, `guest_name`, `guest_email`, `guest_phone`, `check_in`, `check_out`, `guests`, `price_per_night`, `nights`, `subtotal`, `additional_charges`, `total_amount`, `booking_status`, `payment_method`, `payment_status`, `special_requests`, `created_at`) VALUES
(1, 'RS-2026-98101', 2, 1, 'John Doe', 'john.doe@example.com', '+1 (555) 234-5678', DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 2, 490.00, 3, 1470.00, 147.00, 1617.00, 'confirmed', 'Pay at Hotel', 'paid', 'Late check-in requested around 8 PM. High floor preferred.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'RS-2026-98102', 3, 3, 'Sophia Miller', 'sophia.miller@example.com', '+1 (555) 876-5432', DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), 2, 280.00, 2, 560.00, 56.00, 616.00, 'pending', 'Pay at Hotel', 'unpaid', 'Anniversary celebration. Complimentary champagne would be wonderful.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'RS-2026-98089', 2, 5, 'John Doe', 'john.doe@example.com', '+1 (555) 234-5678', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(CURDATE(), INTERVAL 12 DAY), 1, 195.00, 3, 585.00, 58.50, 643.50, 'completed', 'Pay at Hotel', 'paid', 'Quiet room away from elevators.', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(4, 'RS-2026-98075', NULL, 4, 'Robert Vance', 'robert.vance@example.com', '+1 (555) 345-6789', DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_SUB(CURDATE(), INTERVAL 23 DAY), 2, 340.00, 2, 680.00, 68.00, 748.00, 'cancelled', 'Pay at Hotel', 'unpaid', 'Flight cancelled due to storm.', DATE_SUB(NOW(), INTERVAL 30 DAY));

-- Booking Status History
INSERT INTO `booking_status_history` (`booking_id`, `old_status`, `new_status`, `changed_by`, `note`, `created_at`) VALUES
(1, 'pending', 'confirmed', 'Admin', 'Payment verified and reservation confirmed.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, NULL, 'pending', 'Customer', 'Initial booking submission by customer.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'confirmed', 'completed', 'System', 'Guest completed stay and checked out gracefully.', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(4, 'pending', 'cancelled', 'Admin', 'Guest requested cancellation due to flight issues.', DATE_SUB(NOW(), INTERVAL 26 DAY));

-- Reviews
INSERT INTO `reviews` (`id`, `user_id`, `booking_id`, `guest_name`, `rating`, `review_text`, `status`, `created_at`) VALUES
(1, 2, 3, 'John Doe', 5, 'An unforgettable luxury experience! The Royal Grand Penthouse was immaculate with extraordinary sunset views. Butler staff were attentive yet unobtrusive. We will definitely return!', 'approved', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2, 3, NULL, 'Sophia Miller', 5, 'Exquisite gastronomy, serene spa treatments, and world-class hospitality. Royal Stay sets the gold standard for five-star boutique resorts.', 'approved', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(3, NULL, NULL, 'Eleanor Vance', 5, 'From the moment the valet took our keys to the tranquil lagoon swim, everything was sheer perfection. Worth every single penny.', 'approved', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Contact Messages
INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'David Sterling', 'david.sterling@example.com', 'Private Wedding Venue Inquiry', 'Hello, we are planning an intimate wedding for 60 guests next spring and would love details on reserving the private beach lawn and penthouse suite.', 'unread', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(2, 'Marcus Chen', 'marcus.chen@example.com', 'Corporate Executive Retreat', 'Inquiring about booking 5 executive suites and conference boardroom facilities for a three-day executive retreat in November.', 'read', DATE_SUB(NOW(), INTERVAL 1 DAY));


