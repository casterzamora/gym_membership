-- Database: gym_membership
-- Backup Date: 2026-04-18 11:32:06
-- Total Tables: 23

SET FOREIGN_KEY_CHECKS=0;

-- Table: attendance
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `member_id` bigint(20) unsigned NOT NULL,
  `schedule_id` bigint(20) unsigned NOT NULL,
  `attendance_status` enum('Present','Absent','Late') NOT NULL DEFAULT 'Present',
  `attendance_notes` text DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`member_id`,`schedule_id`),
  KEY `attendance_schedule_id_foreign` (`schedule_id`),
  CONSTRAINT `attendance_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `class_schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: certifications
DROP TABLE IF EXISTS `certifications`;
CREATE TABLE `certifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cert_name` varchar(255) NOT NULL,
  `issuing_organization` varchar(255) NOT NULL,
  `cert_number` varchar(255) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certifications_cert_name_unique` (`cert_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `certifications` (`id`, `cert_name`, `issuing_organization`, `cert_number`, `issue_date`, `expiry_date`, `created_at`, `updated_at`) VALUES
('1', 'CPR', 'Professional Org', NULL, NULL, NULL, '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('2', 'NASM', 'Professional Org', NULL, NULL, NULL, '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('3', 'ACE', 'Professional Org', NULL, NULL, NULL, '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('4', 'ISSF', 'Professional Org', NULL, NULL, NULL, '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('5', 'IYASA', 'Professional Org', NULL, NULL, NULL, '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('6', 'AFAA', 'Professional Org', NULL, NULL, NULL, '2026-04-06 07:39:34', '2026-04-06 07:39:34');

-- Table: class_schedules
DROP TABLE IF EXISTS `class_schedules`;
CREATE TABLE `class_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint(20) unsigned NOT NULL,
  `class_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `recurrence_type` varchar(255) DEFAULT 'none',
  `recurrence_end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_schedules_class_id_class_date_start_time_unique` (`class_id`,`class_date`,`start_time`),
  CONSTRAINT `class_schedules_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `fitness_classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: equipment
DROP TABLE IF EXISTS `equipment`;
CREATE TABLE `equipment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `equipment_name` varchar(255) NOT NULL,
  `status` enum('Available','Maintenance','Out of Service') NOT NULL DEFAULT 'Available',
  `acquisition_date` date DEFAULT '2026-04-06',
  `last_maintenance` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `equipment` (`id`, `equipment_name`, `status`, `acquisition_date`, `last_maintenance`, `created_at`, `updated_at`) VALUES
('2', 'Treadmill', 'Available', '2026-04-06', '2026-03-30', '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('3', 'Elliptical', 'Available', '2026-04-06', '2026-03-30', '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('4', 'Stationary Bike', 'Available', '2026-04-06', '2026-03-30', '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('5', 'Yoga Mat', 'Available', '2026-04-06', '2026-03-30', '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('6', 'Bench Press', 'Available', '2026-04-06', '2026-03-30', '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('7', 'Rowing Machine', 'Available', '2026-04-06', '2026-03-30', '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('8', 'Pull-up Bar', 'Available', '2026-04-06', '2026-03-30', '2026-04-06 07:39:34', '2026-04-06 07:39:34');

-- Table: equipment_tracking
DROP TABLE IF EXISTS `equipment_tracking`;
CREATE TABLE `equipment_tracking` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint(20) unsigned NOT NULL,
  `equipment_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `status` enum('required','in_use','returned') NOT NULL DEFAULT 'required',
  `used_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `returned_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `equipment_tracking_class_id_index` (`class_id`),
  KEY `equipment_tracking_equipment_id_index` (`equipment_id`),
  KEY `equipment_tracking_status_index` (`status`),
  KEY `equipment_tracking_used_at_index` (`used_at`),
  KEY `equipment_tracking_user_id_index` (`user_id`),
  KEY `equipment_tracking_assigned_by_index` (`assigned_by`),
  KEY `equipment_tracking_returned_by_index` (`returned_by`),
  CONSTRAINT `equipment_tracking_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_tracking_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `fitness_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipment_tracking_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipment_tracking_returned_by_foreign` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_tracking_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: fitness_classes
DROP TABLE IF EXISTS `fitness_classes`;
CREATE TABLE `fitness_classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `trainer_id` bigint(20) unsigned NOT NULL,
  `max_participants` int(11) NOT NULL,
  `difficulty_level` varchar(255) NOT NULL DEFAULT 'Beginner',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fitness_classes_trainer_id_foreign` (`trainer_id`),
  CONSTRAINT `fitness_classes_trainer_id_foreign` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `fitness_classes` (`id`, `class_name`, `description`, `trainer_id`, `max_participants`, `difficulty_level`, `created_at`, `updated_at`) VALUES
('17', 'yoga', 'asdasd', '10', '13', 'Beginner', '2026-04-17 14:09:02', '2026-04-17 14:09:02');

-- Table: job_batches
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: jobs
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_at_available_at_index` (`queue`,`reserved_at`,`available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: members
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `fitness_goal` varchar(255) DEFAULT NULL,
  `health_notes` text DEFAULT NULL,
  `registration_type` varchar(255) NOT NULL DEFAULT 'standard',
  `date_of_birth` date NOT NULL,
  `plan_id` bigint(20) unsigned DEFAULT NULL,
  `membership_start` date DEFAULT NULL,
  `membership_end` date DEFAULT NULL,
  `membership_status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `members_email_unique` (`email`),
  UNIQUE KEY `members_username_unique` (`username`),
  KEY `members_plan_id_foreign` (`plan_id`),
  KEY `1` (`user_id`),
  CONSTRAINT `1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `members_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `membership_plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `members` (`id`, `user_id`, `first_name`, `last_name`, `email`, `username`, `password_hash`, `phone`, `fitness_goal`, `health_notes`, `registration_type`, `date_of_birth`, `plan_id`, `membership_start`, `membership_end`, `membership_status`, `created_at`, `updated_at`) VALUES
('1', NULL, 'TestUpdate2', 'Test', 'member1@gym.com', 'member1', '$2y$12$GIDdfuBee.AeoD/ViHZhtuye10fUonbI6ZorspuPY.Rl2O929zjte', '555-0101', 'Build Muscle', 'Cleared for all exercises', 'standard', '2000-04-06', '2', NULL, NULL, 'active', '2026-04-06 07:39:34', '2026-04-16 09:35:17'),
('2', NULL, 'Member2', 'Test', 'member2@gym.com', 'member2', '$2y$12$oZmMogj8.DjaApTLEDoMIeArwpDpr65q2Ige3B41o8FQV30mN27f6', '555-0102', 'General Fitness', 'Cleared for all exercises', 'standard', '1999-04-06', '3', '2026-02-06', '2026-10-06', 'active', '2026-04-06 07:39:35', '2026-04-16 09:31:21'),
('3', NULL, 'Member3', 'Test', 'member3@gym.com', 'member3', '$2y$12$p/RBP2Ku3VTW6sEw45a8b.GkE7UR1ThTxBGjCzVE.dqKqXwheFaji', '555-0103', 'Weight Loss', 'Cleared for all exercises', 'standard', '1998-04-06', '3', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:35', '2026-04-06 07:39:35'),
('4', NULL, 'Member4', 'Test', 'member4@gym.com', 'member4', '$2y$12$bNKrioi9Lz7OoQAU4QfjgeLC0.hpcx1gDCmKs/vjrk2/YuiDp4ISG', '555-0104', 'Build Muscle', 'Cleared for all exercises', 'standard', '1997-04-06', '1', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:35', '2026-04-06 07:39:35'),
('5', NULL, 'Member5', 'Test', 'member5@gym.com', 'member5', '$2y$12$SxCfS5x1U1FKKJS5TmEklerfRXxX.x7p9u7APu4s/EJdxqvMm2H.q', '555-0105', 'General Fitness', 'Cleared for all exercises', 'standard', '1996-04-06', '2', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:35', '2026-04-06 07:39:35'),
('6', NULL, 'Member6', 'Test', 'member6@gym.com', 'member6', '$2y$12$kULQJAAsgrwmVWfXApJ1GuL0fRB3Dt7anTm1.qEklOMPIFZpAht2a', '555-0106', 'Weight Loss', 'Cleared for all exercises', 'standard', '1995-04-06', '3', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:35', '2026-04-06 07:39:35'),
('7', NULL, 'Member7', 'Test', 'member7@gym.com', 'member7', '$2y$12$CsyO7c5Rig0O61i/Svy5x.Gs8ornn1QxETK68eATGsvCgcoZW.8VW', '555-0107', 'Build Muscle', 'Cleared for all exercises', 'standard', '1994-04-06', '1', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:35', '2026-04-06 07:39:35'),
('8', NULL, 'Member8', 'Test', 'member8@gym.com', 'member8', '$2y$12$4UqzNQrmP7wv/tyfioRxEOdqvyXkB47CDLbkRZNt/qqwFd2zSQPLi', '555-0108', 'General Fitness', 'Cleared for all exercises', 'standard', '1993-04-06', '2', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:36', '2026-04-06 07:39:36'),
('9', NULL, 'Member9', 'Test', 'member9@gym.com', 'member9', '$2y$12$7jDM6N.UiCvQQ0LDRNatK.X967B/vwgjFofgqNDMH9nh1GhLse6e2', '555-0109', 'Weight Loss', 'Cleared for all exercises', 'standard', '1992-04-06', '3', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:36', '2026-04-06 07:39:36'),
('10', NULL, 'Member10', 'Test', 'member10@gym.com', 'member10', '$2y$12$kbUm.HW7BIXvjXDX/4a3x.gVaLUVV4YhOd5WK79skEu0X1ry.teoK', '555-01010', 'Build Muscle', 'Cleared for all exercises', 'standard', '1991-04-06', '1', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:36', '2026-04-06 07:39:36'),
('11', NULL, 'Member11', 'Test', 'member11@gym.com', 'member11', '$2y$12$UU0k2TbuSy9Pzhc1amG.e.zMqZ47w6WJk0LSInm7KYy7j6otzR582', '555-01011', 'General Fitness', 'Cleared for all exercises', 'standard', '1990-04-06', '2', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:36', '2026-04-06 07:39:36'),
('12', NULL, 'Member12', 'Test', 'member12@gym.com', 'member12', '$2y$12$PfTyLlrNZLlEveFW3./t7eCqa/j5o2pFOlqov34SNAShJaGl7iX0S', '555-01012', 'Weight Loss', 'Cleared for all exercises', 'standard', '1989-04-06', '3', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:36', '2026-04-06 07:39:36'),
('13', NULL, 'Member13', 'Test', 'member13@gym.com', 'member13', '$2y$12$EsvP25drwvLVtU55./He4e9oOS7dmCwgs6XbNl5JYk422.qaQL4Ci', '555-01013', 'Build Muscle', 'Cleared for all exercises', 'standard', '1988-04-06', '1', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:37', '2026-04-06 07:39:37'),
('14', NULL, 'Member14', 'Test', 'member14@gym.com', 'member14', '$2y$12$F5./rq1mG5mCwhx9wSfLi.l4e93.7jdf4LAGX9mzOP2RwBCdD/37G', '555-01014', 'General Fitness', 'Cleared for all exercises', 'standard', '1987-04-06', '2', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:37', '2026-04-06 07:39:37'),
('15', NULL, 'Member15', 'Test', 'member15@gym.com', 'member15', '$2y$12$5LQSgvySNWEEi8hdaYNWfuu5fOFRiUDcdZ.EMIXQHdlYSILIJ10QK', '555-01015', 'Weight Loss', 'Cleared for all exercises', 'standard', '1986-04-06', '3', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:37', '2026-04-06 07:39:37'),
('17', NULL, 'Member17', 'Test', 'member17@gym.com', 'member17', '$2y$12$PzBj48duTCk1z4peSYLfvORuigS9F24511GEt5HVajC8YlWv13O1u', '555-01017', 'General Fitness', 'Cleared for all exercises', 'standard', '1984-04-06', '2', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:37', '2026-04-06 07:39:37'),
('19', NULL, 'Member19', 'Test', 'member19@gym.com', 'member19', '$2y$12$vLugpeSsMu.HtLS7GOdo/OD75C9pRsVvyWFe0tGZxa7hnj26KTS1.', '555-01019', 'Build Muscle', 'Cleared for all exercises', 'standard', '1982-04-06', '1', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('20', NULL, 'Member20', 'Test', 'member20@gym.com', 'member20', '$2y$12$ti2skBRD2OfdY4PNcWb6S.5AamjGmWrTuXzBd29ROK9u2K.0BQ.zW', '555-01020', 'General Fitness', 'Cleared for all exercises', 'standard', '1981-04-06', '2', '2026-02-06', '2026-08-06', 'active', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('21', NULL, 'Demo', 'User', 'demo@gym.com', 'demo', '$2y$12$rPgn7noRiPxAiTGffTWK1um7sFL3M5dURACJDr6vHLY7FF.KTHOke', '555-0000', 'General Fitness', 'No restrictions', 'standard', '1996-04-06', '1', '2026-04-06', '2026-07-06', 'active', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('24', NULL, 'jhon', 'Doe', 'john.doe@example.com', 'johndoe', '$2y$12$2pfYhdC3M3QtpignBl0GseFkAG3r6RS9MIkKEaDzkdORiMyUyAgG6', '09123132', 'Weight Loss', 'No injuries', 'online', '2001-04-16', '1', '2026-04-16', '2026-05-16', 'active', '2026-04-16 09:14:17', '2026-04-16 16:44:51'),
('25', NULL, 'Jane', 'Smith', 'jane.smith@example.com', 'janesmith', '$2y$12$2NlFg5MkQnOgohwq3fTx0.KEbqjDqeAPJbpisUO86.J5xYEG/rFFW', '09234567890', 'Build Muscle', 'Regular gym member', 'walk-in', '1996-04-16', '2', '2026-04-16', '2026-05-16', 'active', '2026-04-16 09:14:17', '2026-04-16 09:14:17'),
('26', NULL, 'Mike', 'Johnson', 'mike.johnson@example.com', 'mikejohnson', '$2y$12$CYyG0/S023hcuUB23o769OobD/nBKj0FhICdUS1/71oC2luT6S9B.', '09345678901', 'Flexibility', 'Recovering from injury', 'referral', '1991-04-16', '1', '2026-04-16', '2026-05-16', 'active', '2026-04-16 09:14:17', '2026-04-16 09:14:17');

-- Table: membership_plans
DROP TABLE IF EXISTS `membership_plans`;
CREATE TABLE `membership_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_months` int(11) NOT NULL,
  `max_classes_per_week` int(11) NOT NULL DEFAULT 3,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `membership_plans_plan_name_unique` (`plan_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `membership_plans` (`id`, `plan_name`, `price`, `duration_months`, `max_classes_per_week`, `description`, `created_at`, `updated_at`) VALUES
('1', 'Bronze', '750.00', '1', '4', 'Perfect for beginners', '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('2', 'Silver', '1000.00', '1', '8', 'For regular gym-goers', '2026-04-06 07:39:34', '2026-04-06 07:39:34'),
('3', 'Gold', '1500.00', '1', '999', 'Premium membership', '2026-04-06 07:39:34', '2026-04-06 07:39:34');

-- Table: membership_upgrades
DROP TABLE IF EXISTS `membership_upgrades`;
CREATE TABLE `membership_upgrades` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` bigint(20) unsigned NOT NULL,
  `old_plan_id` bigint(20) unsigned DEFAULT NULL,
  `new_plan_id` bigint(20) unsigned NOT NULL,
  `upgrade_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `membership_upgrades_old_plan_id_foreign` (`old_plan_id`),
  KEY `membership_upgrades_new_plan_id_foreign` (`new_plan_id`),
  KEY `membership_upgrades_member_id_foreign` (`member_id`),
  CONSTRAINT `membership_upgrades_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `membership_upgrades_new_plan_id_foreign` FOREIGN KEY (`new_plan_id`) REFERENCES `membership_plans` (`id`),
  CONSTRAINT `membership_upgrades_old_plan_id_foreign` FOREIGN KEY (`old_plan_id`) REFERENCES `membership_plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `membership_upgrades` (`id`, `member_id`, `old_plan_id`, `new_plan_id`, `upgrade_date`, `created_at`, `updated_at`) VALUES
('1', '1', '1', '2', '2026-04-16', '2026-04-16 09:26:46', '2026-04-16 09:26:46'),
('2', '2', '2', '1', '2026-04-16', '2026-04-16 09:27:00', '2026-04-16 09:27:00'),
('3', '2', '1', '3', '2026-04-16', '2026-04-16 09:31:21', '2026-04-16 09:31:21');

-- Table: migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2026_03_20_000001_create_membership_plans_table', '1'),
('5', '2026_03_20_000002_create_trainers_table', '1'),
('6', '2026_03_20_000003_create_certifications_table', '1'),
('7', '2026_03_20_000004_create_trainer_certifications_table', '1'),
('8', '2026_03_20_000005_create_members_table', '1'),
('9', '2026_03_20_000006_create_fitness_classes_table', '1'),
('10', '2026_03_20_000007_create_class_schedules_table', '1'),
('11', '2026_03_20_000008_create_equipment_table', '1'),
('12', '2026_03_20_000009_create_class_equipment_table', '1'),
('13', '2026_03_20_000010_create_attendances_table', '1'),
('14', '2026_03_20_000011_create_payments_table', '1'),
('15', '2026_03_20_000012_create_equipment_usages_table', '1'),
('16', '2026_03_20_000013_create_membership_upgrades_table', '1'),
('17', '2026_03_20_000015_add_role_to_users_table', '1'),
('18', '2026_03_20_075626_create_personal_access_tokens_table', '1'),
('19', '2026_03_20_100000_add_email_verification_to_users', '1'),
('20', '2026_04_01_000001_add_cascade_delete_to_member_foreign_keys', '1'),
('21', '2026_04_01_000002_fix_trainer_cascade_delete_comprehensive', '1'),
('22', '2026_04_01_000003_fix_trainer_user_id_foreign_key', '1'),
('23', '2026_04_01_000004_fix_class_schedule_cascade_delete', '1'),
('24', '2026_04_01_000005_fix_attendance_cascade_delete', '1'),
('25', '2026_04_06_000001_create_payment_methods_table', '1'),
('26', '2026_04_06_000002_restructure_members_table', '1'),
('27', '2026_04_06_000003_restructure_trainers_table', '1'),
('28', '2026_04_06_000004_add_max_classes_to_membership_plans', '1'),
('29', '2026_04_06_000005_restructure_payments_table', '1'),
('30', '2026_04_06_000006_add_difficulty_level_to_fitness_classes', '1'),
('31', '2026_04_06_000007_add_recurrence_to_class_schedules', '1'),
('32', '2026_04_06_000008_add_attendance_notes', '1'),
('33', '2026_04_08_000001_add_hourly_rate_to_trainers', '2'),
('34', '2026_04_08_000002_add_cascade_delete_to_fitness_classes_trainer_id', '3'),
('35', '2026_04_08_000003_add_cascade_delete_to_schedules_and_equipment_usage', '4'),
('36', '2026_04_11_000001_restore_trainer_certification_fk', '5'),
('37', '2026_04_17_000001_create_equipment_tracking_table', '5'),
('40', '2026_04_17_000002_migrate_to_equipment_tracking', '6'),
('42', '2026_04_17_000003_drop_old_equipment_tables', '7'),
('43', '2026_04_18_000001_add_user_tracking_to_equipment_tracking', '8'),
('44', '2026_04_18_000001_expand_users_table_for_consolidation', '9'),
('45', '2026_04_18_000002_add_user_id_to_members_table', '9');

-- Table: password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payment_methods
DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE `payment_methods` (
  `payment_method_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `method_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`payment_method_id`),
  UNIQUE KEY `payment_methods_method_name_unique` (`method_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_methods` (`payment_method_id`, `method_name`, `created_at`, `updated_at`) VALUES
('1', 'Cash', NULL, NULL),
('2', 'Credit Card', NULL, NULL),
('3', 'Debit Card', NULL, NULL),
('4', 'Bank Transfer', NULL, NULL),
('5', 'GCash', NULL, NULL),
('6', 'PayMaya', NULL, NULL);

-- Table: payments
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` bigint(20) unsigned NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method_id` bigint(20) unsigned DEFAULT NULL,
  `coverage_start` date NOT NULL,
  `coverage_end` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_member_id_foreign` (`member_id`),
  KEY `payments_payment_method_id_foreign` (`payment_method_id`),
  CONSTRAINT `payments_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payments` (`id`, `member_id`, `amount_paid`, `payment_date`, `payment_method_id`, `coverage_start`, `coverage_end`, `created_at`, `updated_at`) VALUES
('1', '1', '1029.00', '2026-04-01', '4', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('2', '2', '1129.00', '2026-04-01', '4', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('3', '3', '1231.00', '2026-04-01', '1', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('4', '4', '927.00', '2026-04-01', '2', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('5', '5', '1236.00', '2026-04-01', '2', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('6', '6', '801.00', '2026-04-01', '1', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('7', '7', '1131.00', '2026-04-01', '5', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('8', '8', '888.00', '2026-04-01', '2', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('9', '9', '1200.00', '2026-04-01', '4', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('10', '10', '769.00', '2026-04-01', '4', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('11', '11', '857.00', '2026-04-01', '2', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('12', '12', '751.00', '2026-04-01', '2', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('13', '13', '1180.00', '2026-04-01', '1', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('14', '14', '1161.00', '2026-04-01', '4', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('15', '15', '1166.00', '2026-04-01', '2', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('17', '17', '750.00', '2026-04-01', '5', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('19', '19', '1024.00', '2026-04-01', '5', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38'),
('20', '20', '1028.00', '2026-04-01', '4', '2026-04-06', '2026-05-06', '2026-04-06 07:39:38', '2026-04-06 07:39:38');

-- Table: personal_access_tokens
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
('1', 'App\\Models\\Member', '21', 'api-token', '8c0cff4027da3928f21e6ce07b14bdb39aed771595d71f7cd33fc0f8c8f7de02', '[\"*\"]', '2026-04-06 09:37:37', NULL, '2026-04-06 09:11:57', '2026-04-06 09:37:37'),
('2', 'App\\Models\\Member', '22', 'api-token', '56c157e78950f9d5fb2eb1574c0063704e632d21db1ffdbb15f3894442b5e6b8', '[\"*\"]', NULL, NULL, '2026-04-06 09:17:16', '2026-04-06 09:17:16'),
('3', 'App\\Models\\Member', '21', 'api-token', '5c2e552a5cc471ed348e4d79ff0f7d98635129fba8c6544761847209a14604c4', '[\"*\"]', NULL, NULL, '2026-04-06 09:17:43', '2026-04-06 09:17:43'),
('4', 'App\\Models\\Member', '21', 'api-token', 'f4496205a44f5f0cefb6f0ce1984ff67e21d0e386ccca5c6717ad9d50278190e', '[\"*\"]', NULL, NULL, '2026-04-06 09:17:48', '2026-04-06 09:17:48'),
('5', 'App\\Models\\Member', '21', 'api-token', '879b96a6f7c6f39658e5ef28fb58468e45329fdcc2e1b620dea3549f66f31cb7', '[\"*\"]', NULL, NULL, '2026-04-06 09:17:56', '2026-04-06 09:17:56'),
('6', 'App\\Models\\Member', '21', 'api-token', '0f830834ca69e6f671dbe090343e3c59c501513e39eb534367ecc5f896af67b8', '[\"*\"]', NULL, NULL, '2026-04-06 09:20:33', '2026-04-06 09:20:33'),
('7', 'App\\Models\\Member', '21', 'api-token', 'e32997ac19c6c398c5cf09b60e4070e9049b56f64b69fa75f81a277e83a1cba0', '[\"*\"]', NULL, NULL, '2026-04-06 09:20:37', '2026-04-06 09:20:37'),
('8', 'App\\Models\\Member', '22', 'api-token', 'b0eeeb9ceb250a62bfb4e7924025cfc665473b6f5f58c4a811d3615690650313', '[\"*\"]', NULL, NULL, '2026-04-06 09:21:20', '2026-04-06 09:21:20'),
('9', 'App\\Models\\Member', '22', 'api-token', '938423aba85c4c36e7c9327d81caac3dc5ddffb7165a76c741ccf31f08b566e3', '[\"*\"]', NULL, NULL, '2026-04-06 09:26:32', '2026-04-06 09:26:32'),
('10', 'App\\Models\\Member', '21', 'api-token', '1d28fe92afdb240b09b1f88adebf14a3127847340c00199301a4ef703024d628', '[\"*\"]', NULL, NULL, '2026-04-06 09:26:41', '2026-04-06 09:26:41'),
('11', 'App\\Models\\Member', '23', 'api-token', '87e87f869d1d24ee934a7c88cc04f67b8cb2c8833afbbea6084d43d1101a91cd', '[\"*\"]', '2026-04-06 09:30:41', NULL, '2026-04-06 09:30:40', '2026-04-06 09:30:41'),
('12', 'App\\Models\\Member', '23', 'api-token', 'd927021ba1cfeaef6ca85043bb34e499ca28925115d87f472ac5065fe3da5845', '[\"*\"]', '2026-04-06 09:38:39', NULL, '2026-04-06 09:38:37', '2026-04-06 09:38:39'),
('13', 'App\\Models\\Member', '22', 'api-token', 'c1fbabb3ab4af6c964b4863486723926f9cfa3d122aa7427a2e573d69edb02fa', '[\"*\"]', '2026-04-06 09:39:04', NULL, '2026-04-06 09:38:58', '2026-04-06 09:39:04'),
('14', 'App\\Models\\Member', '21', 'api-token', '3c008d7aebbdbc84cab2a4cfdfdda26a9dae206c5a3e292d5a91f676e0ba1235', '[\"*\"]', NULL, NULL, '2026-04-06 09:45:01', '2026-04-06 09:45:01'),
('15', 'App\\Models\\User', '1', 'api-token', '45bcbd9eecbec4f08a537c65969c5412924a1380af272e5394fe07d1eca27760', '[\"*\"]', NULL, NULL, '2026-04-06 09:46:13', '2026-04-06 09:46:13'),
('16', 'App\\Models\\Member', '21', 'api-token', '0dce5fa6bc4c879005788a8780c608432530320ed931b57b513e7f9a36d0c132', '[\"*\"]', NULL, NULL, '2026-04-06 09:46:22', '2026-04-06 09:46:22'),
('17', 'App\\Models\\User', '1', 'api-token', 'a72dfcd44a7a19c94df60c3d60fd5a043a0436169e795a21771309f2115206eb', '[\"*\"]', NULL, NULL, '2026-04-06 09:46:43', '2026-04-06 09:46:43'),
('18', 'App\\Models\\User', '1', 'api-token', 'a903753f20c924d9f2dad67441b025117a97101d4a70f0ee8dd6352fee234466', '[\"*\"]', NULL, NULL, '2026-04-06 09:46:56', '2026-04-06 09:46:56'),
('19', 'App\\Models\\User', '1', 'api-token', '2ddcc216ab14b68b027fa7e622ec51164a7925c3a3c31369402b4333981896e9', '[\"*\"]', NULL, NULL, '2026-04-06 09:49:34', '2026-04-06 09:49:34'),
('20', 'App\\Models\\User', '1', 'api-token', 'eeec0905456d284f92f2960a536408bd7de5e2280aab8585242f64b5743322b4', '[\"*\"]', NULL, NULL, '2026-04-06 09:49:44', '2026-04-06 09:49:44'),
('21', 'App\\Models\\User', '1', 'api-token', '6bd821515921939835788a909347bcfb94867acacd61e63d842e77fd4f61c648', '[\"*\"]', NULL, NULL, '2026-04-06 09:52:08', '2026-04-06 09:52:08'),
('22', 'App\\Models\\User', '1', 'api-token', '906849a0fba8355edca8d284b2da538b7b9024804c6b6c61429c9a23cccd2609', '[\"*\"]', NULL, NULL, '2026-04-06 09:52:15', '2026-04-06 09:52:15'),
('23', 'App\\Models\\User', '1', 'api-token', '7237de9efb2f180ad3f8d7e70fe612aa409ded1f8e67f8cb1b0b6b74ecfe5144', '[\"*\"]', NULL, NULL, '2026-04-06 09:52:31', '2026-04-06 09:52:31'),
('24', 'App\\Models\\User', '1', 'api-token', '17a03b21a0bd89818c668f5b7810fa4e45af2f7ec029c5d7bc5135663f802df8', '[\"*\"]', NULL, NULL, '2026-04-06 09:53:13', '2026-04-06 09:53:13'),
('25', 'App\\Models\\User', '1', 'api-token', '6830e99f351a7b672680aead9ce09bd1c9c1933928328fcdd7b85c878be5abcc', '[\"*\"]', NULL, NULL, '2026-04-06 09:53:21', '2026-04-06 09:53:21'),
('26', 'App\\Models\\User', '1', 'api-token', 'd5aae1d18ff2104cfe364434caba6f7411b541d55b1e493a8c9439009dbd0f16', '[\"*\"]', NULL, NULL, '2026-04-08 11:10:35', '2026-04-08 11:10:35'),
('27', 'App\\Models\\Member', '21', 'api-token', '470e3d421977eac002fb6e60df102f8f9dcd7f54934096ddbca06d9fa0aad7a3', '[\"*\"]', NULL, NULL, '2026-04-08 11:12:00', '2026-04-08 11:12:00'),
('28', 'App\\Models\\Member', '21', 'api-token', 'ca7ef941a04f50392f2b5f60197b4d13392474a74b2e6c817fb614cb0f90a23f', '[\"*\"]', NULL, NULL, '2026-04-08 11:12:18', '2026-04-08 11:12:18'),
('29', 'App\\Models\\Member', '21', 'api-token', '48a81e9468dc08b67de39294c97d8fe1af53060640229c6e55a3904d0bc0ba48', '[\"*\"]', NULL, NULL, '2026-04-08 11:12:35', '2026-04-08 11:12:35'),
('30', 'App\\Models\\Member', '21', 'api-token', 'ee0db7f6e37a1bca8ec10641ef4c2d8f46c2c3d7acbc58e0539d6fa2cae0c920', '[\"*\"]', NULL, NULL, '2026-04-08 11:13:01', '2026-04-08 11:13:01'),
('31', 'App\\Models\\Member', '21', 'api-token', '094e12c20c71935854082d0f18ea91f2a94f16d6994d6eb7c09846b7974d28c2', '[\"*\"]', NULL, NULL, '2026-04-08 11:14:08', '2026-04-08 11:14:08'),
('32', 'App\\Models\\Member', '21', 'api-token', '947b3a90889e7d7aebe9dc9a6810f64444ca995e9b430655b37e587c22664d9f', '[\"*\"]', NULL, NULL, '2026-04-08 11:14:24', '2026-04-08 11:14:24'),
('33', 'App\\Models\\Member', '21', 'api-token', 'faccb2ead46caf923c43bf2027ac7b52f80e1f30632633162575e9af68aeb212', '[\"*\"]', '2026-04-08 11:15:46', NULL, '2026-04-08 11:15:45', '2026-04-08 11:15:46'),
('34', 'App\\Models\\User', '1', 'api-token', '1524ac0c2b8699fc2eb850eee7375778c2479b8711085a4d1234317858f931db', '[\"*\"]', NULL, NULL, '2026-04-08 11:16:11', '2026-04-08 11:16:11'),
('35', 'App\\Models\\Member', '21', 'api-token', '33560dc699e798caf0cc66854d6a58a284d8c717144d628ba5efa29747acffd1', '[\"*\"]', NULL, NULL, '2026-04-08 11:17:07', '2026-04-08 11:17:07'),
('36', 'App\\Models\\User', '1', 'api-token', 'f6b5facd280114e1e8b86b2d7db759d6445b3259a1e051d389b6caf44b120e4f', '[\"*\"]', NULL, NULL, '2026-04-08 11:17:08', '2026-04-08 11:17:08'),
('37', 'App\\Models\\Member', '21', 'api-token', '2e3c899265b8531b0e057f2c7eb2f29a9f4dc160710b55374ab897793d7ec23b', '[\"*\"]', NULL, NULL, '2026-04-08 11:17:21', '2026-04-08 11:17:21'),
('38', 'App\\Models\\User', '1', 'api-token', '22757b6497594c07af3005abc4309a31078af6b5dd455fbb68e84ecaf2cc6369', '[\"*\"]', NULL, NULL, '2026-04-08 11:17:22', '2026-04-08 11:17:22'),
('39', 'App\\Models\\User', '1', 'api-token', 'a51d15573acfb46316df8a6ed7f6881641693b0c52ed1e00be75f597d306cee3', '[\"*\"]', NULL, NULL, '2026-04-08 11:18:14', '2026-04-08 11:18:14'),
('40', 'App\\Models\\Member', '21', 'api-token', '8d9df285dd1f86b8fe52c5ab60c5ffbc7f5654493bc6a0f8446b1517d7db9039', '[\"*\"]', NULL, NULL, '2026-04-08 11:19:43', '2026-04-08 11:19:43'),
('41', 'App\\Models\\User', '1', 'api-token', '6403d0bc5d535b5686a5175507b12df8978f825da0f49e85a1badfdce9dc72fd', '[\"*\"]', NULL, NULL, '2026-04-08 11:19:44', '2026-04-08 11:19:44'),
('42', 'App\\Models\\Member', '21', 'api-token', '0123b121d0b8bc95e139071aec7c34b004175b402144da57984e07fc3f4d591c', '[\"*\"]', NULL, NULL, '2026-04-08 11:20:46', '2026-04-08 11:20:46'),
('43', 'App\\Models\\User', '1', 'api-token', '279d855c637bce184f667c6ed48eed92f1fab3a6fba3d058b47f6f95b97fc728', '[\"*\"]', NULL, NULL, '2026-04-08 11:20:46', '2026-04-08 11:20:46'),
('44', 'App\\Models\\User', '1', 'api-token', '9b95b1fddc7d3071d4349031d2fa11b7ac713c5efaed1397407cf10873246d3d', '[\"*\"]', NULL, NULL, '2026-04-08 11:22:58', '2026-04-08 11:22:58'),
('45', 'App\\Models\\User', '1', 'api-token', '381b42c98579b399e6239e592c8eb0ded8effe664779646dc4a93a8122d0c07e', '[\"*\"]', NULL, NULL, '2026-04-08 11:28:13', '2026-04-08 11:28:13'),
('46', 'App\\Models\\User', '1', 'api-token', 'edb97023cb9c92141d4e61712d46bc86a145b52c25cf9a9ca4fd3ca6f42a2a52', '[\"*\"]', NULL, NULL, '2026-04-08 11:28:49', '2026-04-08 11:28:49'),
('47', 'App\\Models\\User', '1', 'api-token', '08c590c888f6d2884845cfaf4c7b995a2ebfa7414e31486db44a9d76d32c5836', '[\"*\"]', NULL, NULL, '2026-04-08 11:32:39', '2026-04-08 11:32:39'),
('48', 'App\\Models\\User', '1', 'api-token', '01369bfc4983d253edd9e02a2581b6887b6e66b366b0ecb83b0c4f69eee565e9', '[\"*\"]', NULL, NULL, '2026-04-08 11:32:47', '2026-04-08 11:32:47'),
('49', 'App\\Models\\User', '1', 'api-token', '86e1f4dd82e337ff1b05383c1a1ec9c55b9d95dcec2191e06e360a24cbf10936', '[\"*\"]', NULL, NULL, '2026-04-08 11:33:26', '2026-04-08 11:33:26'),
('50', 'App\\Models\\User', '1', 'api-token', '34ccba2b3ee301f8aede22f61cd51a1301df02c04ef4d38c5f83849bc8c7a38f', '[\"*\"]', NULL, NULL, '2026-04-08 11:35:46', '2026-04-08 11:35:46'),
('51', 'App\\Models\\User', '1', 'api-token', '60417803acf89080821e075cd8cce68f131e029c85878d5228daa20bdad98a50', '[\"*\"]', NULL, NULL, '2026-04-08 11:36:17', '2026-04-08 11:36:17'),
('52', 'App\\Models\\User', '1', 'api-token', 'f1d6999a920a5832b9ad706b290b9c140737345986be6612600e2fed7cc0d31b', '[\"*\"]', NULL, NULL, '2026-04-08 11:38:12', '2026-04-08 11:38:12'),
('53', 'App\\Models\\User', '1', 'api-token', '658689b30beca1d7714b49382aa31b372c0bee1aa3559e203d767e9f4dd7de9e', '[\"*\"]', NULL, NULL, '2026-04-08 11:39:17', '2026-04-08 11:39:17'),
('54', 'App\\Models\\User', '1', 'api-token', '65671ef1dc9f102bc02bedb2f4ca76dcf0163ea66f0999c1f308384daf95efa1', '[\"*\"]', NULL, NULL, '2026-04-08 11:41:24', '2026-04-08 11:41:24'),
('55', 'App\\Models\\User', '1', 'api-token', '4cee8dc0f78042f08b1bbef0b977d688332d2e2f5334e613dd0f84dc6fce4100', '[\"*\"]', NULL, NULL, '2026-04-08 11:41:56', '2026-04-08 11:41:56'),
('56', 'App\\Models\\User', '1', 'api-token', '9e2d25d6bee5b0acee884c5f2f98e23aa0b91e97a6261a331daa7ce5fa945696', '[\"*\"]', NULL, NULL, '2026-04-08 11:42:37', '2026-04-08 11:42:37'),
('57', 'App\\Models\\User', '1', 'api-token', 'c77c7fb77d1e6fa307ed4e1141088e55fb33a8230a475dd1347c0b80569142f2', '[\"*\"]', NULL, NULL, '2026-04-08 11:44:51', '2026-04-08 11:44:51'),
('58', 'App\\Models\\User', '1', 'api-token', 'c5fe48c1c204b813a00def030ce4f2d43c6c8f25ed0321db03983a0096ebc95f', '[\"*\"]', NULL, NULL, '2026-04-08 11:45:44', '2026-04-08 11:45:44'),
('59', 'App\\Models\\User', '1', 'api-token', 'e7892a79f7385b922137ad22954c7982f73a9ef03b10f99fcf28656439714170', '[\"*\"]', NULL, NULL, '2026-04-08 11:46:42', '2026-04-08 11:46:42'),
('60', 'App\\Models\\User', '1', 'api-token', '614c6c242d9c9f8ba9ac425fe48caf0f99cfe5410bc43035edd56ec851cecec0', '[\"*\"]', NULL, NULL, '2026-04-08 11:47:46', '2026-04-08 11:47:46'),
('61', 'App\\Models\\User', '1', 'api-token', 'd2aa5edb65c1766817cd2266631f4e89dae5ad8fee4e01b0e8cdb38e5e159031', '[\"*\"]', NULL, NULL, '2026-04-08 11:49:39', '2026-04-08 11:49:39'),
('62', 'App\\Models\\User', '1', 'api-token', 'dbbb9239187f4c7612502b7afea54bb343b7836df1a2bfca59b33321bcd0d39a', '[\"*\"]', NULL, NULL, '2026-04-08 11:50:41', '2026-04-08 11:50:41'),
('63', 'App\\Models\\User', '1', 'api-token', 'fa5013752d85f2e09c7cad5c17bccf660083baf21d75d7809db4def4046cbe1c', '[\"*\"]', NULL, NULL, '2026-04-08 11:51:06', '2026-04-08 11:51:06'),
('64', 'App\\Models\\User', '1', 'api-token', '08572906db4281c5dd75c864f8c2f72647d8002eff6b63c32c5632d75c6c60a8', '[\"*\"]', NULL, NULL, '2026-04-08 11:52:31', '2026-04-08 11:52:31'),
('65', 'App\\Models\\User', '1', 'api-token', '4865bf67f05ad670be9bb5361b00bcd4f54e0555f0fe3565de7188070e7c64d1', '[\"*\"]', NULL, NULL, '2026-04-08 11:53:13', '2026-04-08 11:53:13'),
('66', 'App\\Models\\User', '1', 'api-token', 'f91fa75175af0ea2e9e3cf9abc6819a4e984f4083efc6b5222c23eca32dea188', '[\"*\"]', NULL, NULL, '2026-04-08 11:53:23', '2026-04-08 11:53:23'),
('67', 'App\\Models\\User', '1', 'api-token', '21466a517768fa87ce7d2effee63b3034368642f6039787d9b64a0cdca98c850', '[\"*\"]', NULL, NULL, '2026-04-08 11:54:11', '2026-04-08 11:54:11'),
('68', 'App\\Models\\User', '1', 'api-token', '9524eb353391cb96d59d5f12dd6704993a2a91c21e37e3412e0ca216890fe3b6', '[\"*\"]', NULL, NULL, '2026-04-08 11:54:45', '2026-04-08 11:54:45'),
('69', 'App\\Models\\User', '1', 'api-token', '47648ec1f194184a0a2e1c51df17cbabb26d67e16bcc20c088524395a6ca605a', '[\"*\"]', NULL, NULL, '2026-04-08 11:55:25', '2026-04-08 11:55:25'),
('70', 'App\\Models\\User', '1', 'api-token', '67c29564c2e17b79246391b8a2f10c3bc80ff9b7c9f5785c41ad77a42d082a7f', '[\"*\"]', NULL, NULL, '2026-04-08 11:55:34', '2026-04-08 11:55:34'),
('71', 'App\\Models\\User', '1', 'api-token', '3b66ba000fa1992ec3a89377b04fff172ecb223e4327da16c742db02ab942845', '[\"*\"]', NULL, NULL, '2026-04-08 11:56:09', '2026-04-08 11:56:09'),
('72', 'App\\Models\\User', '1', 'api-token', '60d9645432feb6d27e0bb14235507ee7bf1e6bd12a1e3fd285f3ca311d067d30', '[\"*\"]', NULL, NULL, '2026-04-08 11:57:25', '2026-04-08 11:57:25'),
('73', 'App\\Models\\User', '1', 'api-token', '79fea7281020e3d0dc0bff1806ae261deaefe5bb25d9b5cdad14a18f780d3c3e', '[\"*\"]', NULL, NULL, '2026-04-08 11:58:05', '2026-04-08 11:58:05'),
('74', 'App\\Models\\User', '1', 'api-token', '05c649795ae8474274dad3ebde5782c75d5a8f37d1e6619754bffc46c00ae0ff', '[\"*\"]', NULL, NULL, '2026-04-08 11:58:36', '2026-04-08 11:58:36'),
('75', 'App\\Models\\User', '1', 'api-token', '24a0f14e2c48b3f13ddc26526aa0fc18b94b142b4989ed50a82621ab51e781c9', '[\"*\"]', NULL, NULL, '2026-04-08 11:59:27', '2026-04-08 11:59:27'),
('76', 'App\\Models\\User', '1', 'api-token', 'b4113cd22c2aa2d69d3b9cc8f863374bde0ed9f51c1ec45a883cd4c64e2dde56', '[\"*\"]', NULL, NULL, '2026-04-08 11:59:54', '2026-04-08 11:59:54'),
('77', 'App\\Models\\User', '1', 'api-token', 'f8d807f7929bd5f0246e48897758a3c503b0fa346191a31787db82622289041b', '[\"*\"]', NULL, NULL, '2026-04-08 12:02:10', '2026-04-08 12:02:10'),
('78', 'App\\Models\\User', '1', 'api-token', '30c26153e279d4b623447578e72ba63c94b5cc0aa63c40a5898e4e035ea082f5', '[\"*\"]', NULL, NULL, '2026-04-16 09:05:33', '2026-04-16 09:05:33'),
('79', 'App\\Models\\User', '1', 'api-token', 'a4822680e90b1404b25242765436de527c27970df678d8d4810441c420691379', '[\"*\"]', NULL, NULL, '2026-04-16 09:07:04', '2026-04-16 09:07:04'),
('80', 'App\\Models\\User', '1', 'api-token', '47a211e945f595acbf6a6b2b2c3d637f66830cc9a5b91584a54266d12d4c5445', '[\"*\"]', NULL, NULL, '2026-04-16 09:17:05', '2026-04-16 09:17:05'),
('81', 'App\\Models\\User', '1', 'api-token', '39b93ae23fe5f4430ce320acd4f60d0a34f6fbdfe4f537971f99981664314a33', '[\"*\"]', NULL, NULL, '2026-04-16 09:32:45', '2026-04-16 09:32:45'),
('82', 'App\\Models\\User', '1', 'api-token', 'c1d0ccb6a49f87b6fb4d201637ce760781c92d4f295a202bb679deca86cc815d', '[\"*\"]', NULL, NULL, '2026-04-16 09:38:38', '2026-04-16 09:38:38'),
('83', 'App\\Models\\User', '1', 'api-token', '192fbaf09ed7c53a943d4666d8d39273cd1ac289ee07dcfe91d0685e25a2bfa4', '[\"*\"]', NULL, NULL, '2026-04-16 09:46:00', '2026-04-16 09:46:00'),
('84', 'App\\Models\\User', '1', 'api-token', '8e3a8d3a31f35cf9b67f34f617fbd556c2dd80385bcae29db98081681a6a988a', '[\"*\"]', NULL, NULL, '2026-04-16 16:44:39', '2026-04-16 16:44:39'),
('85', 'App\\Models\\User', '1', 'api-token', 'e0b757cd1fa4210f2016f48c9f63ac125b01a38dd9b69b8af46426044800f8e1', '[\"*\"]', NULL, NULL, '2026-04-16 17:10:39', '2026-04-16 17:10:39'),
('86', 'App\\Models\\User', '1', 'api-token', '3da04708ad95324de1f4165641edf6fe473529cff649b991512492e461df83a1', '[\"*\"]', NULL, NULL, '2026-04-16 17:33:42', '2026-04-16 17:33:42'),
('87', 'App\\Models\\User', '1', 'api-token', 'f9e668819a510b6b31169acd3d236581c8b43a23c69608ffb016937ac1741a62', '[\"*\"]', NULL, NULL, '2026-04-17 13:16:00', '2026-04-17 13:16:00'),
('88', 'App\\Models\\User', '1', 'api-token', '47ee9c9d44fb374359105d02811feb6ba48d466fc2b1ce36d5b7a640e2e085e9', '[\"*\"]', NULL, NULL, '2026-04-17 13:26:17', '2026-04-17 13:26:17'),
('89', 'App\\Models\\User', '1', 'api-token', '5f8bd2f71f88549e18a1cfc62a1a63e3ddc6bf925d361d12becc948bcacc3373', '[\"*\"]', NULL, NULL, '2026-04-17 13:44:24', '2026-04-17 13:44:24'),
('90', 'App\\Models\\User', '1', 'api-token', '3afb50ec19e5b6dd18494cc00924ef6bed80e18bfb5ac556eaaf68b796cae2d3', '[\"*\"]', NULL, NULL, '2026-04-17 13:57:15', '2026-04-17 13:57:15');

-- Table: sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: trainer_certifications
DROP TABLE IF EXISTS `trainer_certifications`;
CREATE TABLE `trainer_certifications` (
  `trainer_id` bigint(20) unsigned NOT NULL,
  `certification_id` bigint(20) unsigned NOT NULL,
  `date_obtained` date DEFAULT '2026-04-06',
  `expires_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`trainer_id`,`certification_id`),
  KEY `trainer_certifications_certification_id_foreign` (`certification_id`),
  CONSTRAINT `trainer_certifications_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certifications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trainer_certifications_trainer_id_foreign` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trainer_certifications` (`trainer_id`, `certification_id`, `date_obtained`, `expires_at`, `created_at`, `updated_at`) VALUES
('5', '4', '2026-04-06', NULL, '2026-04-06 07:39:34', '2026-04-06 07:39:34');

-- Table: trainers
DROP TABLE IF EXISTS `trainers`;
CREATE TABLE `trainers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `hourly_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trainers_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trainers` (`id`, `user_id`, `first_name`, `last_name`, `email`, `specialization`, `phone`, `hourly_rate`, `created_at`, `updated_at`) VALUES
('5', NULL, 'David', 'Wilson', 'trainer4@gym.com', 'Fitness', '555-0104', '90.00', '2026-04-06 07:39:34', '2026-04-08 11:32:26'),
('10', NULL, 'dfgdfg', 'Johnson', 'trainer1@gym.com', 'fit', '555-0101', '60.00', '2026-04-08 11:42:55', '2026-04-08 11:42:55'),
('11', NULL, 'Test', 'Trainer', 'test195445@test.com', 'Test', '1234567890', '100.00', '2026-04-08 11:54:46', '2026-04-08 11:54:46'),
('12', NULL, 'heyy', 'Brown', 'trainer2@gym.com', 'yoga', '555-0102', '70.00', '2026-04-16 09:08:19', '2026-04-16 09:08:19'),
('13', NULL, 'robert', 'Davis', 'trainer3@gym.com', 'hiit', '555-0103', '80.00', '2026-04-16 16:45:39', '2026-04-16 16:45:55'),
('14', NULL, 'robert', 'Davis', 'trainer8@gym.com', 'hiit', '555-0103', '80.00', '2026-04-17 14:07:06', '2026-04-17 14:07:06'),
('15', NULL, 'robert', 'Davis', 'trainer17@gym.com', 'hiit', '555-0103', '80.00', '2026-04-17 14:07:19', '2026-04-17 14:07:19');

-- Table: users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `hourly_rate` decimal(8,2) DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','trainer','member') NOT NULL DEFAULT 'member',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`),
  KEY `users_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `first_name`, `last_name`, `phone`, `specialization`, `hourly_rate`, `is_active`, `deleted_at`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`, `email_verification_token`, `password_reset_token`, `password_reset_expires_at`) VALUES
('1', 'Admin User', NULL, NULL, NULL, NULL, '0.00', '1', NULL, 'admin@gym.com', NULL, '$2y$12$0Wr.UH4m4cYPI8xarE9j1.VoTKow7hQAd06OVPAhtUXTGm7IgbQZC', 'admin', NULL, '2026-04-06 09:42:01', '2026-04-06 09:42:01', NULL, NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;
