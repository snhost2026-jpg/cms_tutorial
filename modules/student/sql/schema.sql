-- Student module (prep school edition): stu_info + stu_master
-- MySQL 5.7+ / MariaDB 10.3+, InnoDB, utf8mb4.
-- No foreign keys. Every column except the primary keys (and is_status, which
-- has a default) accepts NULL, so no field is mandatory.
-- Existing tables with the same names are left untouched (IF NOT EXISTS).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `stu_info` (
  `stu_info_id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stu_unique_id`           VARCHAR(30)  NULL COMMENT 'Registration no',
  `stu_info_stu_master_id`  INT UNSIGNED NULL COMMENT 'Points to stu_master.stu_master_id',
  `application_no`          VARCHAR(50)  NULL,
  `stu_title`               VARCHAR(15)  NULL,
  `stu_first_name`          VARCHAR(150) NULL,
  `stu_middle_name`         VARCHAR(150) NULL,
  `stu_last_name`           VARCHAR(150) NULL,
  `stu_gender`              VARCHAR(20)  NULL,
  `stu_dob`                 DATE         NULL,
  `stu_birthplace`          VARCHAR(100) NULL,
  `stu_religion`            VARCHAR(50)  NULL,
  `stu_aadhaar`             VARCHAR(12)  NULL,
  `stu_mobile_no`           VARCHAR(15)  NULL,
  `stu_whatsapp_no`         VARCHAR(15)  NULL,
  `stu_email_id`            VARCHAR(150) NULL,
  `stu_admission_date`      DATE         NULL,
  `father_name`             VARCHAR(100) NULL,
  `father_mobile`           VARCHAR(15)  NULL,
  `father_whatsapp`         VARCHAR(15)  NULL,
  `father_email`            VARCHAR(150) NULL,
  `father_occupation`       VARCHAR(100) NULL,
  `mother_name`             VARCHAR(100) NULL,
  `mother_mobile`           VARCHAR(15)  NULL,
  `mother_whatsapp`         VARCHAR(15)  NULL,
  `mother_email`            VARCHAR(150) NULL,
  `mother_occupation`       VARCHAR(100) NULL,
  `guardian_name`           VARCHAR(100) NULL,
  `guardian_relation`       VARCHAR(30)  NULL,
  `guardian_mobile`         VARCHAR(15)  NULL,
  `address_line`            VARCHAR(255) NULL,
  `city`                    VARCHAR(80)  NULL,
  `state`                   VARCHAR(80)  NULL,
  `pincode`                 VARCHAR(10)  NULL,
  `prev_school_name`        VARCHAR(150) NULL,
  `prev_class`              VARCHAR(30)  NULL,
  `emergency_contact_name`  VARCHAR(100) NULL,
  `emergency_contact_phone` VARCHAR(15)  NULL,
  PRIMARY KEY (`stu_info_id`),
  UNIQUE KEY `uq_stu_info_unique_id` (`stu_unique_id`),   -- NULLs may repeat
  UNIQUE KEY `uq_stu_info_aadhaar`   (`stu_aadhaar`),
  UNIQUE KEY `uq_stu_info_email`     (`stu_email_id`),
  KEY `idx_stu_info_master` (`stu_info_stu_master_id`),
  KEY `idx_stu_info_name`   (`stu_first_name`, `stu_last_name`),
  KEY `idx_stu_info_mobile` (`stu_mobile_no`),
  KEY `idx_stu_info_dob`    (`stu_dob`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stu_master` (
  `stu_master_id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stu_master_stu_info_id`  INT UNSIGNED NULL,
  `stu_master_user_id`      INT UNSIGNED NULL,
  `stu_master_course_id`    INT UNSIGNED NULL,
  `stu_master_batch_id`     INT UNSIGNED NULL,
  `stu_master_section_id`   INT UNSIGNED NULL,
  `created_at`              DATETIME     NULL,
  `created_by`              INT UNSIGNED NULL,
  `updated_at`              DATETIME     NULL,
  `updated_by`              INT UNSIGNED NULL,
  `is_status`               TINYINT      NOT NULL DEFAULT 1 COMMENT '0 inactive, 1 active, 2 deleted',
  PRIMARY KEY (`stu_master_id`),
  KEY `idx_stu_master_info`    (`stu_master_stu_info_id`),
  KEY `idx_stu_master_user`    (`stu_master_user_id`),
  KEY `idx_stu_master_course`  (`stu_master_course_id`),
  KEY `idx_stu_master_batch`   (`stu_master_batch_id`),
  KEY `idx_stu_master_section` (`stu_master_section_id`),
  KEY `idx_stu_master_status`  (`is_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
