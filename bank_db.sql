-- BankLite student project database for phpMyAdmin / MySQL.
-- Import creates bank_db, its tables, 100 banking sample rows, and one demo employee.
-- Re-import replaces only customers CUS0001-CUS0020 and their related account/transaction rows.
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `bank_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `bank_db`;

CREATE TABLE IF NOT EXISTS `customers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_code` VARCHAR(20) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `birth_date` DATE NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_customer_code_unique` (`customer_code`),
  UNIQUE KEY `customers_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `account_number` VARCHAR(20) NOT NULL,
  `account_type` VARCHAR(20) NOT NULL DEFAULT 'savings',
  `balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `opened_at` DATE NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounts_account_number_unique` (`account_number`),
  KEY `accounts_customer_id_status_index` (`customer_id`, `status`),
  CONSTRAINT `accounts_customer_id_foreign`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` BIGINT UNSIGNED NOT NULL,
  `transaction_code` VARCHAR(30) NOT NULL,
  `transaction_type` VARCHAR(20) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `description` VARCHAR(255) NULL,
  `transaction_date` DATETIME NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'completed',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_transaction_code_unique` (`transaction_code`),
  KEY `transactions_account_id_transaction_date_index` (`account_id`, `transaction_date`),
  CONSTRAINT `transactions_account_id_foreign`
    FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employees` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `position` VARCHAR(80) NOT NULL DEFAULT 'เจ้าหน้าที่ธนาคาร',
  `role` VARCHAR(30) NOT NULL DEFAULT 'staff',
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_employee_code_unique` (`employee_code`),
  UNIQUE KEY `employees_email_unique` (`email`),
  KEY `employees_status_role_index` (`status`, `role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

DELETE FROM `customers`
WHERE `customer_code` BETWEEN 'CUS0001' AND 'CUS0020';

INSERT INTO `customers`
  (`customer_code`, `first_name`, `last_name`, `email`, `phone`, `birth_date`, `created_at`, `updated_at`)
VALUES
  ('CUS0001', 'กิตติพงษ์', 'วัฒนากุล', 'kittipong.w@example.test', '081-XXX-1042', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 29 YEAR), INTERVAL 13 DAY), NOW(), NOW()),
  ('CUS0002', 'ณัฐธิดา', 'ศรีสุข', 'nattida.s@example.test', '081-XXX-2186', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 36 YEAR), INTERVAL 26 DAY), NOW(), NOW()),
  ('CUS0003', 'ปวีณา', 'จันทร์เพ็ญ', 'paweena.j@example.test', '081-XXX-3075', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 43 YEAR), INTERVAL 39 DAY), NOW(), NOW()),
  ('CUS0004', 'ธนกร', 'ภูมิรักษ์', 'thanakorn.p@example.test', '081-XXX-4193', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 50 YEAR), INTERVAL 52 DAY), NOW(), NOW()),
  ('CUS0005', 'ศิริพร', 'แสงทอง', 'siriporn.s@example.test', '081-XXX-5271', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 22 YEAR), INTERVAL 65 DAY), NOW(), NOW()),
  ('CUS0006', 'ภาคิน', 'วงศ์ประเสริฐ', 'phakhin.w@example.test', '081-XXX-6384', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 29 YEAR), INTERVAL 78 DAY), NOW(), NOW()),
  ('CUS0007', 'พิมพ์ชนก', 'นาคเจริญ', 'phimchanok.n@example.test', '081-XXX-7462', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 36 YEAR), INTERVAL 91 DAY), NOW(), NOW()),
  ('CUS0008', 'อนุชา', 'คงมั่น', 'anucha.k@example.test', '081-XXX-8529', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 43 YEAR), INTERVAL 104 DAY), NOW(), NOW()),
  ('CUS0009', 'วรัญญา', 'บุญเรือง', 'waranya.b@example.test', '081-XXX-9641', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 50 YEAR), INTERVAL 117 DAY), NOW(), NOW()),
  ('CUS0010', 'ชยพล', 'เกษมสุข', 'chayaphon.k@example.test', '081-XXX-0753', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 22 YEAR), INTERVAL 130 DAY), NOW(), NOW()),
  ('CUS0011', 'กมลวรรณ', 'รัตนชัย', 'kamonwan.r@example.test', '081-XXX-1860', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 29 YEAR), INTERVAL 143 DAY), NOW(), NOW()),
  ('CUS0012', 'จักรพันธ์', 'สุวรรณดี', 'jakkaphan.s@example.test', '081-XXX-2948', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 36 YEAR), INTERVAL 156 DAY), NOW(), NOW()),
  ('CUS0013', 'อรทัย', 'มณีรัตน์', 'orathai.m@example.test', '081-XXX-3057', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 43 YEAR), INTERVAL 169 DAY), NOW(), NOW()),
  ('CUS0014', 'ธีรภัทร', 'สมบูรณ์', 'theeraphat.s@example.test', '081-XXX-4176', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 50 YEAR), INTERVAL 182 DAY), NOW(), NOW()),
  ('CUS0015', 'มนัสวี', 'ชูศักดิ์', 'manatsawi.c@example.test', '081-XXX-5283', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 22 YEAR), INTERVAL 195 DAY), NOW(), NOW()),
  ('CUS0016', 'ปกรณ์', 'ตั้งเจริญ', 'pakorn.t@example.test', '081-XXX-6391', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 29 YEAR), INTERVAL 208 DAY), NOW(), NOW()),
  ('CUS0017', 'นภัสสร', 'พูนผล', 'napatsorn.p@example.test', '081-XXX-7405', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 36 YEAR), INTERVAL 221 DAY), NOW(), NOW()),
  ('CUS0018', 'เอกภพ', 'อินทร์ทอง', 'ekkapop.i@example.test', '081-XXX-8514', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 43 YEAR), INTERVAL 234 DAY), NOW(), NOW()),
  ('CUS0019', 'สุภาวดี', 'สายใจ', 'suphawadi.s@example.test', '081-XXX-9637', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 50 YEAR), INTERVAL 247 DAY), NOW(), NOW()),
  ('CUS0020', 'รณชัย', 'คำแสน', 'ronnachai.k@example.test', '081-XXX-2749', DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 22 YEAR), INTERVAL 260 DAY), NOW(), NOW());

INSERT INTO `accounts`
  (`customer_id`, `account_number`, `account_type`, `balance`, `status`, `opened_at`, `created_at`, `updated_at`)
SELECT
  `customers`.`id`,
  CONCAT(
    '102-',
    MOD(CAST(SUBSTRING(`customers`.`customer_code`, 4) AS UNSIGNED), 9) + 1,
    '-',
    LPAD(12000 + (379 * CAST(SUBSTRING(`customers`.`customer_code`, 4) AS UNSIGNED)), 5, '0'),
    '-',
    MOD(CAST(SUBSTRING(`customers`.`customer_code`, 4) AS UNSIGNED) * 7, 10)
  ),
  IF(MOD(CAST(SUBSTRING(`customers`.`customer_code`, 4) AS UNSIGNED), 5) = 0, 'current', 'savings'),
  20000 + (2750 * CAST(SUBSTRING(`customers`.`customer_code`, 4) AS UNSIGNED))
    + 3200 + MOD(CAST(SUBSTRING(`customers`.`customer_code`, 4) AS UNSIGNED) * 2375, 28000)
    - 450 - MOD(CAST(SUBSTRING(`customers`.`customer_code`, 4) AS UNSIGNED) * 791, 10500),
  'active',
  DATE_SUB(CURDATE(), INTERVAL (3 + MOD(CAST(SUBSTRING(`customers`.`customer_code`, 4) AS UNSIGNED) * 7, 100)) MONTH),
  NOW(),
  NOW()
FROM `customers`
WHERE `customers`.`customer_code` BETWEEN 'CUS0001' AND 'CUS0020';

INSERT INTO `transactions`
  (`account_id`, `transaction_code`, `transaction_type`, `amount`, `description`, `transaction_date`, `status`, `created_at`, `updated_at`)
SELECT
  `accounts`.`id`,
  CONCAT('TXN-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-', LPAD(((`customer_numbers`.`customer_number` - 1) * 3) + `sequences`.`sequence_no`, 6, '0')),
  CASE `sequences`.`sequence_no` WHEN 3 THEN 'withdrawal' ELSE 'deposit' END,
  CASE `sequences`.`sequence_no`
    WHEN 1 THEN 20000 + (2750 * `customer_numbers`.`customer_number`)
    WHEN 2 THEN 3200 + MOD(`customer_numbers`.`customer_number` * 2375, 28000)
    ELSE 450 + MOD(`customer_numbers`.`customer_number` * 791, 10500)
  END,
  CASE `sequences`.`sequence_no`
    WHEN 1 THEN 'เปิดบัญชี • ฝากเงินสด'
    WHEN 2 THEN 'เงินเดือน • โอนเข้า'
    ELSE CASE MOD(`customer_numbers`.`customer_number`, 3)
      WHEN 0 THEN 'ชำระค่าสาธารณูปโภค • Mobile Banking'
      WHEN 1 THEN 'ชำระค่าสินค้า • Mobile Banking'
      ELSE 'ถอนเงินสด • ATM'
    END
  END,
  CASE `sequences`.`sequence_no`
    WHEN 1 THEN DATE_SUB(CURDATE(), INTERVAL (3 + MOD(`customer_numbers`.`customer_number` * 7, 100)) MONTH)
    WHEN 2 THEN DATE_SUB(NOW(), INTERVAL (1 + MOD(`customer_numbers`.`customer_number`, 20)) DAY)
    ELSE DATE_SUB(NOW(), INTERVAL MOD(`customer_numbers`.`customer_number`, 6) DAY)
  END,
  'completed',
  NOW(),
  NOW()
FROM `accounts`
INNER JOIN (
  SELECT `id`, CAST(SUBSTRING(`customer_code`, 4) AS UNSIGNED) AS `customer_number`
  FROM `customers`
  WHERE `customer_code` BETWEEN 'CUS0001' AND 'CUS0020'
) AS `customer_numbers` ON `customer_numbers`.`id` = `accounts`.`customer_id`
CROSS JOIN (
  SELECT 1 AS `sequence_no`
  UNION ALL SELECT 2
  UNION ALL SELECT 3
) AS `sequences`;

INSERT INTO `employees`
  (`employee_code`, `name`, `email`, `phone`, `position`, `role`, `status`, `password`, `created_at`, `updated_at`)
VALUES
  ('EMP0001', 'ผู้ดูแลระบบ', 'staff@banklite.test', '02-XXX-4521', 'ผู้ดูแลระบบ', 'admin', 'active', '$2y$10$D4f79pRI6wivZXeEHFus8euM1m6niGJT6HpKKFmgXvazmYZ4rCFOa', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `email` = VALUES(`email`),
  `phone` = VALUES(`phone`),
  `position` = VALUES(`position`),
  `role` = VALUES(`role`),
  `status` = VALUES(`status`),
  `password` = VALUES(`password`),
  `updated_at` = VALUES(`updated_at`);

COMMIT;
