-- =====================================================
-- 125 Gor Kadva Patel Samaj Matrimony - Database Schema
-- =====================================================

CREATE DATABASE IF NOT EXISTS `matrimony_125_gor` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `matrimony_125_gor`;

-- =====================================================
-- USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `gender` ENUM('Male','Female') NOT NULL,
    `dob` DATE NOT NULL,
    `age` INT DEFAULT NULL,
    `village` VARCHAR(100) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `state` VARCHAR(100) DEFAULT NULL,
    `mobile` VARCHAR(15) NOT NULL UNIQUE,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `education` VARCHAR(150) DEFAULT NULL,
    `occupation` VARCHAR(150) DEFAULT NULL,
    `annual_income` VARCHAR(50) DEFAULT NULL,
    `marital_status` ENUM('Never Married','Divorced','Widowed','Awaiting Divorce') DEFAULT 'Never Married',
    `height` VARCHAR(20) DEFAULT NULL,
    `weight` VARCHAR(20) DEFAULT NULL,
    `complexion` VARCHAR(50) DEFAULT NULL,
    `blood_group` VARCHAR(10) DEFAULT NULL,
    `religion` VARCHAR(50) DEFAULT 'Hindu',
    `sub_caste` VARCHAR(100) DEFAULT NULL,
    `gotra` VARCHAR(100) DEFAULT NULL,
    `manglik` ENUM('Yes','No','Partial','Don''t Know') DEFAULT 'Don''t Know',
    `family_type` ENUM('Joint','Nuclear') DEFAULT NULL,
    `family_status` ENUM('Middle Class','Upper Middle Class','Rich','Affluent') DEFAULT NULL,
    `father_name` VARCHAR(100) DEFAULT NULL,
    `father_occupation` VARCHAR(150) DEFAULT NULL,
    `mother_name` VARCHAR(100) DEFAULT NULL,
    `mother_occupation` VARCHAR(150) DEFAULT NULL,
    `siblings` VARCHAR(50) DEFAULT NULL,
    `hobbies` TEXT DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `partner_preferences` TEXT DEFAULT NULL,
    `profile_photo` VARCHAR(255) DEFAULT NULL,
    `aadhaar_number` VARCHAR(20) DEFAULT NULL,
    `verification_status` ENUM('pending','verified','rejected') DEFAULT 'pending',
    `profile_status` ENUM('active','inactive','blocked','deleted') DEFAULT 'active',
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_premium` TINYINT(1) DEFAULT 0,
    `last_active` DATETIME DEFAULT NULL,
    `email_verified` TINYINT(1) DEFAULT 0,
    `remember_token` VARCHAR(255) DEFAULT NULL,
    `reset_token` VARCHAR(255) DEFAULT NULL,
    `reset_token_expiry` DATETIME DEFAULT NULL,
    `otp_code` VARCHAR(10) DEFAULT NULL,
    `otp_expiry` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- USER GALLERY TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `user_gallery` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- INTERESTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `interests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sender_id` INT NOT NULL,
    `receiver_id` INT NOT NULL,
    `status` ENUM('pending','accepted','rejected') DEFAULT 'pending',
    `message` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_interest` (`sender_id`, `receiver_id`)
) ENGINE=InnoDB;

-- =====================================================
-- MESSAGES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sender_id` INT NOT NULL,
    `receiver_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `seen_status` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- NOTIFICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `from_user_id` INT DEFAULT NULL,
    `type` ENUM('interest_received','interest_accepted','interest_rejected','new_message','profile_viewed','profile_approved','profile_rejected','admin_announcement') NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT DEFAULT NULL,
    `link` VARCHAR(255) DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================
-- ADMINS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('superadmin','admin','moderator') DEFAULT 'admin',
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- REPORTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reporter_id` INT NOT NULL,
    `reported_user_id` INT NOT NULL,
    `reason` ENUM('fake_profile','inappropriate_photo','harassment','spam','other') NOT NULL,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reported_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- BLOCKED USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `blocked_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `blocker_id` INT NOT NULL,
    `blocked_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`blocker_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`blocked_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_block` (`blocker_id`, `blocked_id`)
) ENGINE=InnoDB;

-- =====================================================
-- PROFILE VIEWS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `profile_views` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `viewer_id` INT NOT NULL,
    `viewed_id` INT NOT NULL,
    `viewed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`viewer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`viewed_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- SETTINGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- SUCCESS STORIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `success_stories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `bride_name` VARCHAR(100) NOT NULL,
    `groom_name` VARCHAR(100) NOT NULL,
    `story` TEXT NOT NULL,
    `photo` VARCHAR(255) DEFAULT NULL,
    `marriage_date` DATE DEFAULT NULL,
    `is_approved` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- CONTACT MESSAGES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(15) DEFAULT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('unread','read','replied') DEFAULT 'unread',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- ANNOUNCEMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `announcements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- DEFAULT DATA
-- =====================================================

-- Default admin account (password: admin123)
INSERT INTO `admins` (`name`, `email`, `password`, `role`) VALUES
('Super Admin', 'admin@125gorsamaj.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- Default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', '125 Gor Kadva Patel Samaj Matrimony'),
('site_email', 'info@125gorsamaj.com'),
('site_phone', '+91 98765 43210'),
('site_address', 'Gujarat, India'),
('registration_enabled', '1'),
('auto_approve_profiles', '0'),
('max_gallery_images', '10'),
('maintenance_mode', '0');

-- Indexes for performance
CREATE INDEX idx_users_gender ON users(gender);
CREATE INDEX idx_users_city ON users(city);
CREATE INDEX idx_users_village ON users(village);
CREATE INDEX idx_users_status ON users(profile_status);
CREATE INDEX idx_users_verification ON users(verification_status);
CREATE INDEX idx_interests_sender ON interests(sender_id);
CREATE INDEX idx_interests_receiver ON interests(receiver_id);
CREATE INDEX idx_messages_sender ON messages(sender_id);
CREATE INDEX idx_messages_receiver ON messages(receiver_id);
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_profile_views_viewed ON profile_views(viewed_id);
