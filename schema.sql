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
('viewOwnProfile', 2, 'View own profile capability', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- Assign wildcard and permissions to SuperAdmin and admin
INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
('SuperAdmin', '/*'),
('SuperAdmin', 'manageRbac'),
('admin', '/*'),
('admin', 'manageRbac'),
('manager', '/rights/*'),
('manager', '/users/*'),
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

SET FOREIGN_KEY_CHECKS = 1;
