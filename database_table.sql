-- elmmacpaystackpayments (adjust if your schema differs)
CREATE TABLE
IF NOT EXISTS `elmmacpaystackpayments`
(
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `email` VARCHAR
(191) NOT NULL,
  `amount` INT NOT NULL,
  `currency` VARCHAR
(10) NOT NULL DEFAULT 'ZAR',
  `reference` VARCHAR
(100) NOT NULL,
  `status` VARCHAR
(32) NOT NULL DEFAULT 'initialized',
  `request_payload` LONGTEXT NULL,
  `response_payload` LONGTEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON
UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_reference`
(`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;