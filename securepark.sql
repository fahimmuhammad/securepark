-- ============================================================
-- SecurePark Database
-- Import this file in phpMyAdmin OR run: setup.php
-- Demo: admin@securepark.com / admin123 | john@example.com / user123
-- ============================================================

CREATE DATABASE IF NOT EXISTS `securepark_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `securepark_db`;

-- ------------------------------------------------------------
-- USERS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(120)    NOT NULL,
  `email`          VARCHAR(180)    NOT NULL UNIQUE,
  `password`       VARCHAR(255)    NOT NULL,
  `phone`          VARCHAR(30)     DEFAULT NULL,
  `vehicle_number` VARCHAR(30)     DEFAULT NULL,
  `role`           ENUM('user','admin') NOT NULL DEFAULT 'user',
  `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- PARKING ZONES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `parking_zones` (
  `id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`  VARCHAR(80)  NOT NULL,
  `floor` VARCHAR(40)  NOT NULL,
  `color` VARCHAR(30)  NOT NULL DEFAULT '#4f46e5',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- PARKING SLOTS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `parking_slots` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `zone_id`     INT UNSIGNED NOT NULL,
  `slot_number` VARCHAR(10)  NOT NULL,
  `slot_type`   ENUM('standard','compact','large','handicap','ev') NOT NULL DEFAULT 'standard',
  `status`      ENUM('available','occupied','reserved','maintenance') NOT NULL DEFAULT 'available',
  `hourly_rate` DECIMAL(6,2) NOT NULL DEFAULT 2.50,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slot` (`zone_id`,`slot_number`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_slot_zone` FOREIGN KEY (`zone_id`) REFERENCES `parking_zones`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- BOOKINGS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `booking_ref`    VARCHAR(20)   NOT NULL UNIQUE,
  `user_id`        INT UNSIGNED  NOT NULL,
  `slot_id`        INT UNSIGNED  NOT NULL,
  `vehicle_number` VARCHAR(30)   NOT NULL,
  `vehicle_type`   ENUM('car','motorcycle','suv','truck','van','ev') NOT NULL DEFAULT 'car',
  `start_time`     DATETIME      NOT NULL,
  `end_time`       DATETIME      NOT NULL,
  `duration_hours` DECIMAL(6,2)  NOT NULL,
  `amount`         DECIMAL(10,2) NOT NULL,
  `status`         ENUM('pending','confirmed','active','completed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `payment_method` VARCHAR(40)   DEFAULT NULL,
  `notes`          TEXT          DEFAULT NULL,
  `created_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user`   (`user_id`),
  KEY `idx_slot`   (`slot_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_slot` FOREIGN KEY (`slot_id`) REFERENCES `parking_slots`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- PAYMENTS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `booking_id`     INT UNSIGNED  NOT NULL,
  `amount`         DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(40)   NOT NULL,
  `transaction_id` VARCHAR(100)  DEFAULT NULL,
  `status`         ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at`        DATETIME      DEFAULT NULL,
  `created_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking` (`booking_id`),
  CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Users (passwords: admin123 / user123)
INSERT INTO `users` (`name`,`email`,`password`,`phone`,`vehicle_number`,`role`) VALUES
('Admin User',   'admin@securepark.com', '$2y$10$98QQNBz75Mo5m11k17b7OOBbKaloCKiSjfr24H5.mPwqbJUTKHExi', '+1-555-0100', 'ADM-001', 'admin'),
('John Smith',   'john@example.com',     '$2y$10$OtX0XALvH7whrFwsGLmureti64Akyw8NiAMtYUrMItZfNmwu6QtIC', '+1-555-0101', 'NYC-1234', 'user'),
('Sarah Johnson','sarah@example.com',    '$2y$10$OtX0XALvH7whrFwsGLmureti64Akyw8NiAMtYUrMItZfNmwu6QtIC', '+1-555-0102', 'NYC-5678', 'user'),
('Mike Davis',   'mike@example.com',     '$2y$10$OtX0XALvH7whrFwsGLmureti64Akyw8NiAMtYUrMItZfNmwu6QtIC', '+1-555-0103', 'NYC-9012', 'user');

-- Parking Zones
INSERT INTO `parking_zones` (`name`,`floor`,`color`) VALUES
('Zone A', 'Ground Floor', '#4f46e5'),
('Zone B', 'First Floor',  '#06b6d4'),
('Zone C', 'Second Floor', '#10b981'),
('Zone D', 'Third Floor',  '#f59e0b');

-- Zone A slots (Ground Floor)
INSERT INTO `parking_slots` (`zone_id`,`slot_number`,`slot_type`,`status`,`hourly_rate`) VALUES
(1,'A01','standard','available',2.50),(1,'A02','standard','occupied',2.50),(1,'A03','standard','available',2.50),
(1,'A04','standard','available',2.50),(1,'A05','standard','reserved',2.50),(1,'A06','standard','available',2.50),
(1,'A07','standard','available',2.50),(1,'A08','standard','occupied',2.50),(1,'A09','standard','available',2.50),
(1,'A10','standard','available',2.50),(1,'A11','compact','available',2.00),(1,'A12','compact','available',2.00),
(1,'A13','compact','occupied',2.00),(1,'A14','large','available',3.50),(1,'A15','large','available',3.50),
(1,'A16','handicap','available',1.50),(1,'A17','handicap','maintenance',1.50),(1,'A18','ev','available',3.00),
(1,'A19','ev','available',3.00),(1,'A20','standard','available',2.50);

-- Zone B slots (First Floor)
INSERT INTO `parking_slots` (`zone_id`,`slot_number`,`slot_type`,`status`,`hourly_rate`) VALUES
(2,'B01','standard','available',2.50),(2,'B02','standard','available',2.50),(2,'B03','standard','occupied',2.50),
(2,'B04','standard','available',2.50),(2,'B05','standard','available',2.50),(2,'B06','standard','reserved',2.50),
(2,'B07','standard','available',2.50),(2,'B08','standard','occupied',2.50),(2,'B09','standard','available',2.50),
(2,'B10','standard','available',2.50),(2,'B11','compact','available',2.00),(2,'B12','compact','occupied',2.00),
(2,'B13','compact','available',2.00),(2,'B14','large','available',3.50),(2,'B15','large','reserved',3.50),
(2,'B16','handicap','available',1.50),(2,'B17','ev','available',3.00),(2,'B18','ev','occupied',3.00),
(2,'B19','standard','available',2.50),(2,'B20','standard','maintenance',2.50);

-- Zone C slots (Second Floor)
INSERT INTO `parking_slots` (`zone_id`,`slot_number`,`slot_type`,`status`,`hourly_rate`) VALUES
(3,'C01','standard','available',2.50),(3,'C02','standard','occupied',2.50),(3,'C03','standard','available',2.50),
(3,'C04','standard','available',2.50),(3,'C05','standard','available',2.50),(3,'C06','compact','available',2.00),
(3,'C07','compact','occupied',2.00),(3,'C08','compact','available',2.00),(3,'C09','large','available',3.50),
(3,'C10','large','available',3.50),(3,'C11','standard','reserved',2.50),(3,'C12','standard','available',2.50),
(3,'C13','standard','occupied',2.50),(3,'C14','standard','available',2.50),(3,'C15','standard','available',2.50),
(3,'C16','handicap','available',1.50),(3,'C17','ev','available',3.00),(3,'C18','ev','occupied',3.00),
(3,'C19','standard','available',2.50),(3,'C20','standard','available',2.50);

-- Zone D slots (Third Floor)
INSERT INTO `parking_slots` (`zone_id`,`slot_number`,`slot_type`,`status`,`hourly_rate`) VALUES
(4,'D01','standard','available',2.50),(4,'D02','standard','available',2.50),(4,'D03','standard','available',2.50),
(4,'D04','standard','occupied',2.50),(4,'D05','standard','available',2.50),(4,'D06','compact','available',2.00),
(4,'D07','compact','available',2.00),(4,'D08','compact','reserved',2.00),(4,'D09','large','available',3.50),
(4,'D10','large','occupied',3.50),(4,'D11','standard','available',2.50),(4,'D12','standard','available',2.50),
(4,'D13','standard','available',2.50),(4,'D14','standard','maintenance',2.50),(4,'D15','standard','available',2.50),
(4,'D16','handicap','available',1.50),(4,'D17','ev','available',3.00),(4,'D18','ev','available',3.00),
(4,'D19','standard','occupied',2.50),(4,'D20','standard','available',2.50);

-- Sample bookings (user id=2 = john@example.com)
INSERT INTO `bookings` (`booking_ref`,`user_id`,`slot_id`,`vehicle_number`,`vehicle_type`,`start_time`,`end_time`,`duration_hours`,`amount`,`status`,`payment_status`,`payment_method`) VALUES
('SPK-2025-0001', 2,  2, 'NYC-1234', 'car', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY) + INTERVAL 3 HOUR, 3.00, 7.50,  'completed', 'paid',   'credit_card'),
('SPK-2025-0002', 2,  8, 'NYC-1234', 'car', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY) + INTERVAL 2 HOUR, 2.00, 5.00,  'completed', 'paid',   'credit_card'),
('SPK-2025-0003', 2,  5, 'NYC-1234', 'car', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 4 HOUR, 4.00, 10.00, 'confirmed', 'paid',   'paypal'),
('SPK-2025-0004', 3, 23, 'NYC-5678', 'suv', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY) + INTERVAL 5 HOUR, 5.00, 12.50, 'completed', 'paid',   'credit_card'),
('SPK-2025-0005', 4, 41, 'NYC-9012', 'car', DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 3 HOUR, 3.00, 7.50,  'pending',   'unpaid',  NULL);
