-- ==============================================================================
-- SARGNEXUS CMS - Database Schema & RBAC Initial Seed
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- Table structure for `user`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(150) NOT NULL UNIQUE,
  `auth_key` VARCHAR(32) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `password_reset_token` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `status` SMALLINT NOT NULL DEFAULT 10,
  `created_at` INT NOT NULL,
  `updated_at` INT NOT NULL,
  `otp_code` VARCHAR(10) DEFAULT NULL,
  `otp_expiry` DATETIME DEFAULT NULL,
  `otp_verified` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for `user_login_history`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `user_login_history`;
CREATE TABLE `user_login_history` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `login_at` DATETIME DEFAULT NULL,
  `logout_at` DATETIME DEFAULT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `session_id` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `logout_reason` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_login_history_user_id` (`user_id`),
  CONSTRAINT `fk_user_login_history_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for `auth_rule`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE `auth_rule` (
  `name` VARCHAR(64) NOT NULL,
  `data` BLOB,
  `created_at` INT DEFAULT NULL,
  `updated_at` INT DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for `auth_item`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `auth_item`;
CREATE TABLE `auth_item` (
  `name` VARCHAR(64) NOT NULL,
  `type` SMALLINT NOT NULL,
  `description` TEXT,
  `rule_name` VARCHAR(64) DEFAULT NULL,
  `data` BLOB,
  `created_at` INT DEFAULT NULL,
  `updated_at` INT DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `idx_auth_item_type` (`type`),
  CONSTRAINT `fk_auth_item_rule_name` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for `auth_item_child`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `auth_item_child`;
CREATE TABLE `auth_item_child` (
  `parent` VARCHAR(64) NOT NULL,
  `child` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `fk_auth_item_child_child` (`child`),
  CONSTRAINT `fk_auth_item_child_parent` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_auth_item_child_child` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for `auth_assignment`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `auth_assignment`;
CREATE TABLE `auth_assignment` (
  `item_name` VARCHAR(64) NOT NULL,
  `user_id` VARCHAR(64) NOT NULL,
  `created_at` INT DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`),
  KEY `idx_auth_assignment_user_id` (`user_id`),
  CONSTRAINT `fk_auth_assignment_item_name` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for `menu` (mdmsoft/yii2-admin)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL,
  `parent` INT DEFAULT NULL,
  `route` VARCHAR(255) DEFAULT NULL,
  `order` INT DEFAULT NULL,
  `data` BLOB,
  PRIMARY KEY (`id`),
  KEY `fk_menu_parent` (`parent`),
  CONSTRAINT `fk_menu_parent` FOREIGN KEY (`parent`) REFERENCES `menu` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Initial Seed Data: Roles, Permissions, Routes
-- ------------------------------------------------------------------------------
-- 1 = Role, 2 = Permission / Route
INSERT INTO `auth_item` (`name`, `type`, `description`, `created_at`, `updated_at`) VALUES
('SuperAdmin', 1, 'Super Administrator with unrestricted access', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin', 1, 'Administrator with full management access', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('manager', 1, 'Manager access', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('employee', 1, 'Standard employee access', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr', 1, 'Human Resources access', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('/*', 2, 'Full application route access', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('/rights/*', 2, 'Rights / RBAC management routes', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('/users/*', 2, 'Users module routes', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('/user/*', 2, 'User CRUD routes', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('/auth-assignment/*', 2, 'Role and route assignment routes', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('/site/*', 2, 'Public and common site routes', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('manageRbac', 2, 'Manage RBAC and user assignments capability', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('viewOwnProfile', 2, 'View own profile capability', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('/academic/*', 2, 'Academic module full route access', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('/student/*', 2, 'Student module full route access', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- Assign wildcard and permissions to SuperAdmin and admin
INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
('SuperAdmin', '/*'),
('SuperAdmin', 'manageRbac'),
('admin', '/*'),
('admin', 'manageRbac'),
('manager', '/rights/*'),
('manager', '/users/*'),
('manager', '/academic/*'),
('manager', '/student/*'),
('manager', 'manageRbac'),
('employee', '/site/*'),
('employee', 'viewOwnProfile');

-- ------------------------------------------------------------------------------
-- Default SuperAdmin User: admin / Admin@12345
-- ------------------------------------------------------------------------------
-- Password hash for 'Admin@12345': '$2y$13$j3E.Bq/xO.6w4lM97p7qCeY1gqK3Y334iIsqJ28ZzX57y3mN2CqQ6' (or generated via Yii Security)
INSERT INTO `user` (`id`, `username`, `auth_key`, `password_hash`, `email`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'q9bYVb3M7N_pLt5mZ91R0k8Xw4T2vAs6', '$2y$13$sD9Z7W2aW2YqKj7cO3gEreC7oZ5kK1p6L8qJ4vW8xX2zX57y3mN2C', 'admin@sargnexus.com', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- Assign SuperAdmin & admin roles to admin user
INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES
('SuperAdmin', '1', UNIX_TIMESTAMP()),
('admin', '1', UNIX_TIMESTAMP());

-- ------------------------------------------------------------------------------
-- Table structure for Academic Module: courses, subjects, batches, sections
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `sections`;
DROP TABLE IF EXISTS `batches`;
DROP TABLE IF EXISTS `subjects`;
DROP TABLE IF EXISTS `courses`;

CREATE TABLE `courses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) DEFAULT NULL,
  `code` VARCHAR(50) DEFAULT NULL,
  `description` TEXT,
  `fee_amount` DECIMAL(12,2) DEFAULT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `created_by` INT DEFAULT NULL,
  `created_at` INT NOT NULL,
  `updated_by` INT DEFAULT NULL,
  `updated_at` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `subjects` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `course_id` INT DEFAULT NULL,
  `name` VARCHAR(160) DEFAULT NULL,
  `code` VARCHAR(50) DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` INT NOT NULL,
  `updated_by` INT DEFAULT NULL,
  `updated_at` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-subjects-course_id` (`course_id`),
  CONSTRAINT `fk-subjects-course_id` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `batches` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `course_id` INT DEFAULT NULL,
  `name` VARCHAR(150) DEFAULT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `capacity` INT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` INT NOT NULL,
  `updated_by` INT DEFAULT NULL,
  `updated_at` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-batches-course_id` (`course_id`),
  CONSTRAINT `fk-batches-course_id` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sections` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `course_id` INT DEFAULT NULL,
  `batch_id` INT DEFAULT NULL,
  `name` VARCHAR(70) DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` INT NOT NULL,
  `updated_by` INT DEFAULT NULL,
  `updated_at` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-sections-course_id` (`course_id`),
  KEY `idx-sections-batch_id` (`batch_id`),
  CONSTRAINT `fk-sections-course_id` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk-sections-batch_id` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for Student Module: stu_info, stu_master
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `stu_master`;
DROP TABLE IF EXISTS `stu_info`;

CREATE TABLE `stu_info` (
  `stu_info_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stu_unique_id` VARCHAR(30) DEFAULT NULL,
  `stu_info_stu_master_id` INT UNSIGNED DEFAULT NULL,
  `application_no` VARCHAR(50) DEFAULT NULL,
  `stu_title` VARCHAR(15) DEFAULT NULL,
  `stu_first_name` VARCHAR(150) DEFAULT NULL,
  `stu_middle_name` VARCHAR(150) DEFAULT NULL,
  `stu_last_name` VARCHAR(150) DEFAULT NULL,
  `stu_gender` VARCHAR(20) DEFAULT NULL,
  `stu_dob` DATE DEFAULT NULL,
  `stu_birthplace` VARCHAR(100) DEFAULT NULL,
  `stu_religion` VARCHAR(50) DEFAULT NULL,
  `stu_aadhaar` VARCHAR(12) DEFAULT NULL,
  `stu_mobile_no` VARCHAR(15) DEFAULT NULL,
  `stu_whatsapp_no` VARCHAR(15) DEFAULT NULL,
  `stu_email_id` VARCHAR(150) DEFAULT NULL,
  `stu_admission_date` DATE DEFAULT NULL,
  `father_name` VARCHAR(100) DEFAULT NULL,
  `father_mobile` VARCHAR(15) DEFAULT NULL,
  `father_whatsapp` VARCHAR(15) DEFAULT NULL,
  `father_email` VARCHAR(150) DEFAULT NULL,
  `father_occupation` VARCHAR(100) DEFAULT NULL,
  `mother_name` VARCHAR(100) DEFAULT NULL,
  `mother_mobile` VARCHAR(15) DEFAULT NULL,
  `mother_whatsapp` VARCHAR(15) DEFAULT NULL,
  `mother_email` VARCHAR(150) DEFAULT NULL,
  `mother_occupation` VARCHAR(100) DEFAULT NULL,
  `guardian_name` VARCHAR(100) DEFAULT NULL,
  `guardian_relation` VARCHAR(30) DEFAULT NULL,
  `guardian_mobile` VARCHAR(15) DEFAULT NULL,
  `address_line` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(80) DEFAULT NULL,
  `state` VARCHAR(80) DEFAULT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `prev_school_name` VARCHAR(150) DEFAULT NULL,
  `prev_class` VARCHAR(30) DEFAULT NULL,
  `emergency_contact_name` VARCHAR(100) DEFAULT NULL,
  `emergency_contact_phone` VARCHAR(15) DEFAULT NULL,
  PRIMARY KEY (`stu_info_id`),
  UNIQUE KEY `uq_stu_info_unique_id` (`stu_unique_id`),
  UNIQUE KEY `uq_stu_info_aadhaar` (`stu_aadhaar`),
  UNIQUE KEY `uq_stu_info_email` (`stu_email_id`),
  KEY `idx_stu_info_master` (`stu_info_stu_master_id`),
  KEY `idx_stu_info_name` (`stu_first_name`, `stu_last_name`),
  KEY `idx_stu_info_mobile` (`stu_mobile_no`),
  KEY `idx_stu_info_dob` (`stu_dob`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stu_master` (
  `stu_master_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stu_master_stu_info_id` INT UNSIGNED DEFAULT NULL,
  `stu_master_user_id` INT UNSIGNED DEFAULT NULL,
  `stu_master_course_id` INT UNSIGNED DEFAULT NULL,
  `stu_master_batch_id` INT UNSIGNED DEFAULT NULL,
  `stu_master_section_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `updated_by` INT UNSIGNED DEFAULT NULL,
  `is_status` SMALLINT NOT NULL DEFAULT 1,
  PRIMARY KEY (`stu_master_id`),
  KEY `idx_stu_master_info` (`stu_master_stu_info_id`),
  KEY `idx_stu_master_user` (`stu_master_user_id`),
  KEY `idx_stu_master_course` (`stu_master_course_id`),
  KEY `idx_stu_master_batch` (`stu_master_batch_id`),
  KEY `idx_stu_master_section` (`stu_master_section_id`),
  KEY `idx_stu_master_status` (`is_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for Employee Module: emp_info, emp_master
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `emp_master`;
DROP TABLE IF EXISTS `emp_info`;

CREATE TABLE `emp_info` (
  `emp_info_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `emp_info_emp_master_id` INT UNSIGNED DEFAULT NULL,
  `employee_code` VARCHAR(32) DEFAULT NULL,
  `emp_application_no` VARCHAR(50) DEFAULT NULL,
  `emp_title` VARCHAR(15) DEFAULT NULL,
  `emp_first_name` VARCHAR(150) DEFAULT NULL,
  `emp_middle_name` VARCHAR(150) DEFAULT NULL,
  `emp_last_name` VARCHAR(150) DEFAULT NULL,
  `emp_gender` VARCHAR(20) DEFAULT NULL,
  `emp_dob` DATE DEFAULT NULL,
  `emp_birthplace` VARCHAR(100) DEFAULT NULL,
  `emp_religion` VARCHAR(50) DEFAULT NULL,
  `emp_aadhaar` VARCHAR(12) DEFAULT NULL,
  `emp_mobile_no` VARCHAR(30) DEFAULT NULL,
  `emp_whatsapp_no` VARCHAR(15) DEFAULT NULL,
  `emp_email_id` VARCHAR(150) DEFAULT NULL,
  `emp_joining_date` DATE DEFAULT NULL,
  `emp_father_name` VARCHAR(100) DEFAULT NULL,
  `emp_father_mobile` VARCHAR(15) DEFAULT NULL,
  `emp_father_whatsapp` VARCHAR(15) DEFAULT NULL,
  `emp_father_email` VARCHAR(150) DEFAULT NULL,
  `emp_father_occupation` VARCHAR(100) DEFAULT NULL,
  `emp_mother_name` VARCHAR(100) DEFAULT NULL,
  `emp_mother_mobile` VARCHAR(15) DEFAULT NULL,
  `emp_mother_whatsapp` VARCHAR(15) DEFAULT NULL,
  `emp_mother_email` VARCHAR(150) DEFAULT NULL,
  `emp_mother_occupation` VARCHAR(100) DEFAULT NULL,
  `emp_guardian_name` VARCHAR(100) DEFAULT NULL,
  `emp_guardian_relation` VARCHAR(30) DEFAULT NULL,
  `emp_guardian_mobile` VARCHAR(15) DEFAULT NULL,
  `address_line` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(80) DEFAULT NULL,
  `state` VARCHAR(80) DEFAULT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `previous_employer_name` VARCHAR(150) DEFAULT NULL,
  `previous_designation` VARCHAR(100) DEFAULT NULL,
  `emergency_contact_name` VARCHAR(100) DEFAULT NULL,
  `emergency_contact_phone` VARCHAR(15) DEFAULT NULL,
  `employee_type` VARCHAR(20) NOT NULL,
  `designation` VARCHAR(100) DEFAULT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`emp_info_id`),
  UNIQUE KEY `uq-emp-info-employee-code` (`employee_code`),
  UNIQUE KEY `uq-emp-info-master` (`emp_info_emp_master_id`),
  KEY `idx-emp-info-type` (`employee_type`),
  KEY `idx-emp-info-name` (`emp_first_name`, `emp_last_name`),
  KEY `idx-emp-info-mobile` (`emp_mobile_no`),
  KEY `idx-emp-info-dob` (`emp_dob`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `emp_master` (
  `emp_master_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `emp_master_emp_info_id` INT UNSIGNED DEFAULT NULL,
  `emp_master_user_id` INT UNSIGNED DEFAULT NULL,
  `created_at` INT NOT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `updated_at` INT NOT NULL,
  `updated_by` INT UNSIGNED DEFAULT NULL,
  `is_status` SMALLINT NOT NULL DEFAULT 1,
  PRIMARY KEY (`emp_master_id`),
  UNIQUE KEY `uq-emp-master-info` (`emp_master_emp_info_id`),
  KEY `idx-emp-master-user` (`emp_master_user_id`),
  KEY `idx-emp-master-status` (`is_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table structure for Timetable Module: `coaching_timetable`
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `coaching_timetable`;
CREATE TABLE `coaching_timetable` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `academic_session_id` INT NOT NULL,
  `course_id` INT NOT NULL,
  `batch_id` INT NOT NULL,
  `section_id` INT DEFAULT NULL,
  `subject_id` INT NOT NULL,
  `employee_id` INT NOT NULL,
  `day_of_week` TINYINT NOT NULL COMMENT '1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday, 6=Saturday, 7=Sunday',
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `room_id` INT DEFAULT NULL,
  `class_type` ENUM('Theory', 'Practical', 'Revision', 'Test', 'Doubt Clearing') DEFAULT 'Theory',
  `effective_from` DATE NOT NULL,
  `effective_to` DATE DEFAULT NULL,
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_by` INT DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CHECK (`day_of_week` BETWEEN 1 AND 7),
  CHECK (`start_time` < `end_time`),
  CHECK (`effective_to` IS NULL OR `effective_to` >= `effective_from`),
  INDEX `idx_batch_day` (`batch_id`, `day_of_week`),
  INDEX `idx_employee_day` (`employee_id`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `auth_item` (`name`, `type`, `description`, `created_at`, `updated_at`) VALUES
('/timetable/*', 2, 'Timetable module full route access', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('manageTimetable', 2, 'Manage coaching weekly timetable capability', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT IGNORE INTO `auth_item_child` (`parent`, `child`) VALUES
('manager', '/timetable/*'),
('manageTimetable', '/timetable/*');

SET FOREIGN_KEY_CHECKS = 1;
