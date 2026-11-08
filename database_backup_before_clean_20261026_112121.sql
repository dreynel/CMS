-- Database Backup Before Clean: 2026-10-26 11:21:21
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `brew_batches`;
CREATE TABLE `brew_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_no` varchar(50) NOT NULL,
  `batch_date` date NOT NULL,
  `finished_item_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `addons_json` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Draft','Posted','Cancelled') NOT NULL DEFAULT 'Draft',
  `created_by` varchar(100) DEFAULT NULL,
  `posted_by` varchar(100) DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_batch_no` (`batch_no`),
  KEY `fk_brew_item` (`finished_item_id`),
  CONSTRAINT `fk_brew_item` FOREIGN KEY (`finished_item_id`) REFERENCES `inventory_items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `brew_batches` (`id`, `batch_no`, `batch_date`, `finished_item_id`, `quantity`, `addons_json`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('1', 'BR-20261025-0001', '2026-10-25', '6', '10.0000', NULL, 'Barista Station Batch Brew', 'Posted', 'Barista Jason Cole', 'Barista Jason Cole', '2026-10-25 14:28:35', '2026-10-25 14:28:35');
INSERT INTO `brew_batches` (`id`, `batch_no`, `batch_date`, `finished_item_id`, `quantity`, `addons_json`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('2', 'BR-20261025-0002', '2026-10-25', '6', '1.0000', NULL, 'Barista Station Batch Brew', 'Posted', 'Barista Jason Cole', 'Barista Jason Cole', '2026-10-25 14:29:12', '2026-10-25 14:29:12');
INSERT INTO `brew_batches` (`id`, `batch_no`, `batch_date`, `finished_item_id`, `quantity`, `addons_json`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('3', 'BR-20261025-0003', '2026-10-25', '6', '1.0000', NULL, 'Barista Station Batch Brew', 'Posted', 'Barista Jason Cole', 'Barista Jason Cole', '2026-10-25 14:33:14', '2026-10-25 14:33:11');

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'Hot Coffee & Classics', 'hot-coffee', 'cup-hot', '1', '1', '2026-10-19 00:37:00', '2026-10-19 00:37:00');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'Iced Espresso & Signatures', 'iced-espresso', 'cup-straw', '2', '1', '2026-10-19 00:37:00', '2026-10-19 00:37:00');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'Non-Coffee & Frappes', 'non-coffee', 'snow', '3', '1', '2026-10-19 00:37:00', '2026-10-19 00:37:00');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('4', 'Pastries & Snacks', 'pastries-snacks', 'cake2', '4', '1', '2026-10-19 00:37:00', '2026-10-19 00:37:00');

DROP TABLE IF EXISTS `dining_tables`;
CREATE TABLE `dining_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `zone` varchar(100) NOT NULL COMMENT 'Study Hub, Lounge, Main Hall, Terrace',
  `zone_id` bigint(20) unsigned DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 2,
  `has_power_outlet` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('available','reserved','occupied','maintenance') NOT NULL DEFAULT 'available',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_code` (`table_code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dining_tables` (`id`, `table_code`, `name`, `zone`, `zone_id`, `capacity`, `has_power_outlet`, `status`, `description`, `created_at`, `updated_at`) VALUES ('1', 'POD-01', 'Smart Study Pod Alpha', 'Study Hub', '1', '1', '1', 'available', 'Individual sound-dampened quiet study pod with dual AC power outlet, USB fast-charger, and LED desk lamp.', '2026-10-19 00:37:00', '2026-10-25 15:07:24');
INSERT INTO `dining_tables` (`id`, `table_code`, `name`, `zone`, `zone_id`, `capacity`, `has_power_outlet`, `status`, `description`, `created_at`, `updated_at`) VALUES ('2', 'POD-02', 'Smart Study Pod Beta', 'Study Hub', '1', '1', '1', 'occupied', 'Individual quiet pod with campus high-speed Wi-Fi access and power outlet.', '2026-10-19 00:37:00', '2026-10-26 10:53:11');
INSERT INTO `dining_tables` (`id`, `table_code`, `name`, `zone`, `zone_id`, `capacity`, `has_power_outlet`, `status`, `description`, `created_at`, `updated_at`) VALUES ('3', 'POD-03', 'Duo Collaborative Pod', 'Study Hub', '1', '2', '1', 'available', 'Two-seater workstation equipped with power outlets for thesis pair-programming or tutoring.', '2026-10-19 00:37:00', '2026-10-24 10:48:03');
INSERT INTO `dining_tables` (`id`, `table_code`, `name`, `zone`, `zone_id`, `capacity`, `has_power_outlet`, `status`, `description`, `created_at`, `updated_at`) VALUES ('4', 'LNG-01', 'Executive Sofa Booth 1', 'Lounge', '2', '4', '1', 'occupied', 'Comfortable leather booth near window with garden view, power strip available.', '2026-10-19 00:37:00', '2026-10-26 10:49:24');
INSERT INTO `dining_tables` (`id`, `table_code`, `name`, `zone`, `zone_id`, `capacity`, `has_power_outlet`, `status`, `description`, `created_at`, `updated_at`) VALUES ('5', 'LNG-02', 'Coffee Corner Sofa 2', 'Lounge', '2', '2', '0', 'available', 'Low-profile coffee table with soft armchairs for relaxed conversations.', '2026-10-19 00:37:00', '2026-10-25 15:11:51');
INSERT INTO `dining_tables` (`id`, `table_code`, `name`, `zone`, `zone_id`, `capacity`, `has_power_outlet`, `status`, `description`, `created_at`, `updated_at`) VALUES ('6', 'HAL-01', 'Main Hall Four-Seater', 'Main Hall', '3', '4', '0', 'available', 'Solid acacia wooden dining table for groups and faculty dining.', '2026-10-19 00:37:00', '2026-10-23 08:02:02');
INSERT INTO `dining_tables` (`id`, `table_code`, `name`, `zone`, `zone_id`, `capacity`, `has_power_outlet`, `status`, `description`, `created_at`, `updated_at`) VALUES ('7', 'HAL-02', 'Main Hall Round Table', 'Main Hall', '3', '6', '0', 'available', 'Spacious round dining table suited for study groups and club meetings.', '2026-10-19 00:37:00', '2026-10-23 08:02:02');
INSERT INTO `dining_tables` (`id`, `table_code`, `name`, `zone`, `zone_id`, `capacity`, `has_power_outlet`, `status`, `description`, `created_at`, `updated_at`) VALUES ('8', 'TER-01', 'Outdoor Terrace Patio 1', 'Terrace', '4', '2', '0', 'available', 'Open-air al fresco dining surrounded by campus garden foliage.', '2026-10-19 00:37:00', '2026-10-23 12:23:27');
INSERT INTO `dining_tables` (`id`, `table_code`, `name`, `zone`, `zone_id`, `capacity`, `has_power_outlet`, `status`, `description`, `created_at`, `updated_at`) VALUES ('9', 'TER-02', 'Outdoor Terrace Patio 2', 'Terrace', '4', '4', '0', 'available', 'Shaded al fresco seating with fresh campus breeze.', '2026-10-19 00:37:00', '2026-10-25 15:07:27');

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

DROP TABLE IF EXISTS `inventory_items`;
CREATE TABLE `inventory_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `name` varchar(191) NOT NULL,
  `item_type` enum('raw','finished') NOT NULL DEFAULT 'raw' COMMENT 'raw = ingredient / raw material, finished = sellable product',
  `unit` varchar(30) NOT NULL DEFAULT 'pc',
  `product_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Linked POS product (finished items only)',
  `unit_cost` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `reorder_level` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `current_stock` decimal(14,4) NOT NULL DEFAULT 0.0000 COMMENT 'Cached running balance (mirrors ledger)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_item_code` (`item_code`),
  UNIQUE KEY `uq_item_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inventory_items` (`id`, `item_code`, `name`, `item_type`, `unit`, `product_id`, `unit_cost`, `reorder_level`, `current_stock`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'SKU-BF-001', 'Burger and Fries', 'finished', 'pc', '1', '55.0000', '10.0000', '0.0000', '1', '2026-10-24 10:52:00', '2026-10-24 10:52:00');
INSERT INTO `inventory_items` (`id`, `item_code`, `name`, `item_type`, `unit`, `product_id`, `unit_cost`, `reorder_level`, `current_stock`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'RM-PATTY', 'Patty', 'raw', 'pc', NULL, '35.0000', '10.0000', '11.0000', '1', '2026-10-24 10:52:00', '2026-10-26 11:13:54');
INSERT INTO `inventory_items` (`id`, `item_code`, `name`, `item_type`, `unit`, `product_id`, `unit_cost`, `reorder_level`, `current_stock`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'RM-FRIES-G', 'Fries', 'raw', 'g', NULL, '20.0000', '10.0000', '1026.0000', '1', '2026-10-24 10:52:00', '2026-10-25 14:24:44');
INSERT INTO `inventory_items` (`id`, `item_code`, `name`, `item_type`, `unit`, `product_id`, `unit_cost`, `reorder_level`, `current_stock`, `is_active`, `created_at`, `updated_at`) VALUES ('4', 'RM-BUNS-PCK', 'Buns', 'raw', 'pck', NULL, '70.0000', '10.0000', '20.0000', '1', '2026-10-24 12:03:55', '2026-10-25 15:56:46');
INSERT INTO `inventory_items` (`id`, `item_code`, `name`, `item_type`, `unit`, `product_id`, `unit_cost`, `reorder_level`, `current_stock`, `is_active`, `created_at`, `updated_at`) VALUES ('5', 'RM-BUNS-PC', 'Buns', 'raw', 'pc', NULL, '10.0000', '10.0000', '20.0000', '1', '2026-10-24 12:46:19', '2026-10-26 11:13:54');
INSERT INTO `inventory_items` (`id`, `item_code`, `name`, `item_type`, `unit`, `product_id`, `unit_cost`, `reorder_level`, `current_stock`, `is_active`, `created_at`, `updated_at`) VALUES ('6', 'SKU-B-002', 'Burger', 'finished', 'pc', '2', '45.0000', '10.0000', '0.0000', '1', '2026-10-24 12:50:25', '2026-10-25 15:14:39');
INSERT INTO `inventory_items` (`id`, `item_code`, `name`, `item_type`, `unit`, `product_id`, `unit_cost`, `reorder_level`, `current_stock`, `is_active`, `created_at`, `updated_at`) VALUES ('7', 'RM-FRIES-KG', 'fries', 'raw', 'kg', NULL, '150.0000', '10.0000', '11.0000', '1', '2026-10-25 14:21:05', '2026-10-25 15:53:41');

DROP TABLE IF EXISTS `inventory_ledger`;
CREATE TABLE `inventory_ledger` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entry_date` datetime NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `txn_type` enum('STOCK_IN','STOCK_OUT','POS_SALE','RES_SALE','CONV_OUT','CONV_IN','BREW_OUT','BREW_IN') NOT NULL,
  `ref_table` varchar(50) NOT NULL,
  `ref_id` bigint(20) unsigned NOT NULL,
  `ref_no` varchar(50) NOT NULL,
  `particulars` varchar(255) DEFAULT NULL,
  `qty_in` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `qty_out` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `balance_after` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `unit_cost` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `posted_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ledger_item` (`item_id`,`id`),
  KEY `idx_ledger_ref` (`ref_table`,`ref_id`),
  KEY `idx_ledger_date` (`entry_date`),
  CONSTRAINT `fk_ledger_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('1', '2026-10-24 12:04:33', '4', 'STOCK_IN', 'stock_transactions', '1', 'SI-20261024-0001', 'Stock In', '1.0000', '0.0000', '1.0000', '10.0000', 'Admin Supervisor', '2026-10-24 12:04:33');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('2', '2026-10-24 12:07:40', '3', 'STOCK_IN', 'stock_transactions', '2', 'SI-20261024-0002', 'Stock In', '1.0000', '0.0000', '1.0000', '20.0000', 'Admin Supervisor', '2026-10-24 12:07:40');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('3', '2026-10-24 12:07:50', '2', 'STOCK_IN', 'stock_transactions', '3', 'SI-20261024-0003', 'Stock In', '1.0000', '0.0000', '1.0000', '35.0000', 'Admin Supervisor', '2026-10-24 12:07:50');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('4', '2026-10-24 12:12:29', '4', 'STOCK_IN', 'stock_transactions', '4', 'SI-20261024-0004', 'Stock In', '25.0000', '0.0000', '26.0000', '10.0000', 'Admin Supervisor', '2026-10-24 12:12:29');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('5', '2026-10-24 12:13:03', '2', 'STOCK_IN', 'stock_transactions', '5', 'SI-20261024-0005', 'Stock In', '25.0000', '0.0000', '26.0000', '35.0000', 'Admin Supervisor', '2026-10-24 12:13:03');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('6', '2026-10-24 12:15:23', '3', 'STOCK_IN', 'stock_transactions', '6', 'SI-20261024-0006', 'Stock In', '25.0000', '0.0000', '26.0000', '20.0000', 'Admin Supervisor', '2026-10-24 12:15:23');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('7', '2026-10-24 12:46:46', '4', 'CONV_OUT', 'item_conversions', '1', 'CV-20261024-0001', 'Convert 1 pck Buns -> 25 pc Buns', '0.0000', '1.0000', '25.0000', '30.0000', 'Admin Supervisor', '2026-10-24 12:46:46');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('8', '2026-10-24 12:46:46', '5', 'CONV_IN', 'item_conversions', '1', 'CV-20261024-0001', 'Convert 1 pck Buns -> 25 pc Buns', '25.0000', '0.0000', '25.0000', '1.2000', 'Admin Supervisor', '2026-10-24 12:46:46');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('9', '2026-10-25 14:21:43', '7', 'STOCK_IN', 'stock_transactions', '7', 'SI-20261025-0001', 'Stock In', '1.0000', '0.0000', '1.0000', '100.0000', 'Admin Supervisor', '2026-10-25 14:21:43');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('10', '2026-10-25 14:22:10', '7', 'STOCK_IN', 'stock_transactions', '8', 'SI-20261025-0002', 'Stock In', '9.0000', '0.0000', '10.0000', '100.0000', 'Admin Supervisor', '2026-10-25 14:22:10');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('11', '2026-10-25 14:22:16', '7', 'STOCK_IN', 'stock_transactions', '9', 'SI-20261025-0003', 'Stock In', '1.0000', '0.0000', '11.0000', '100.0000', 'Admin Supervisor', '2026-10-25 14:22:16');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('12', '2026-10-25 14:24:44', '7', 'CONV_OUT', 'item_conversions', '2', 'CV-20261025-0001', 'Convert 1 kg fries -> 1,000 g Fries', '0.0000', '1.0000', '10.0000', '100.0000', 'Admin Supervisor', '2026-10-25 14:24:44');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('13', '2026-10-25 14:24:44', '3', 'CONV_IN', 'item_conversions', '2', 'CV-20261025-0001', 'Convert 1 kg fries -> 1,000 g Fries', '1000.0000', '0.0000', '1026.0000', '0.1000', 'Admin Supervisor', '2026-10-25 14:24:44');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('14', '2026-10-25 14:26:40', '4', 'CONV_OUT', 'item_conversions', '3', 'CV-20261025-0002', 'Convert 1 pck Buns -> 10 pc Buns', '0.0000', '1.0000', '24.0000', '30.0000', 'Admin Supervisor', '2026-10-25 14:26:40');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('15', '2026-10-25 14:26:40', '5', 'CONV_IN', 'item_conversions', '3', 'CV-20261025-0002', 'Convert 1 pck Buns -> 10 pc Buns', '10.0000', '0.0000', '35.0000', '3.0000', 'Admin Supervisor', '2026-10-25 14:26:40');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('16', '2026-10-25 14:28:35', '5', 'BREW_OUT', 'brew_batches', '1', 'BR-20261025-0001', 'Brew 10 x Burger (Base Recipe)', '0.0000', '10.0000', '25.0000', '10.0000', 'Barista Jason Cole', '2026-10-25 14:28:35');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('17', '2026-10-25 14:28:35', '2', 'BREW_OUT', 'brew_batches', '1', 'BR-20261025-0001', 'Brew 10 x Burger (Base Recipe)', '0.0000', '10.0000', '16.0000', '35.0000', 'Barista Jason Cole', '2026-10-25 14:28:35');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('18', '2026-10-25 14:29:12', '5', 'BREW_OUT', 'brew_batches', '2', 'BR-20261025-0002', 'Brew 1 x Burger (Base Recipe)', '0.0000', '1.0000', '24.0000', '10.0000', 'Barista Jason Cole', '2026-10-25 14:29:12');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('19', '2026-10-25 14:29:12', '2', 'BREW_OUT', 'brew_batches', '2', 'BR-20261025-0002', 'Brew 1 x Burger (Base Recipe)', '0.0000', '1.0000', '15.0000', '35.0000', 'Barista Jason Cole', '2026-10-25 14:29:12');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('20', '2026-10-25 14:33:14', '5', 'BREW_OUT', 'brew_batches', '3', 'BR-20261025-0003', 'Brew 1 x Burger (Base Recipe)', '0.0000', '1.0000', '23.0000', '10.0000', 'Barista Jason Cole', '2026-10-25 14:33:14');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('21', '2026-10-25 14:33:14', '2', 'BREW_OUT', 'brew_batches', '3', 'BR-20261025-0003', 'Brew 1 x Burger (Base Recipe)', '0.0000', '1.0000', '14.0000', '35.0000', 'Barista Jason Cole', '2026-10-25 14:33:14');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('22', '2026-10-25 14:42:21', '2', 'BREW_OUT', 'orders', '1', 'ORD-1001', 'Kitchen Brew & Serve ORD-1001 [Dine-In] (1x Burger)', '0.0000', '1.0000', '13.0000', '35.0000', 'System', '2026-10-25 14:42:21');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('23', '2026-10-25 14:42:21', '5', 'BREW_OUT', 'orders', '1', 'ORD-1001', 'Kitchen Brew & Serve ORD-1001 [Dine-In] (1x Burger)', '0.0000', '1.0000', '22.0000', '10.0000', 'System', '2026-10-25 14:42:21');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('24', '2026-10-25 15:52:51', '4', 'STOCK_IN', 'stock_transactions', '10', 'SI-20261025-0004', 'Stock In', '5.0000', '0.0000', '29.0000', '250.0000', 'Admin Supervisor', '2026-10-25 15:52:51');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('25', '2026-10-25 15:53:41', '7', 'STOCK_IN', 'stock_transactions', '11', 'SI-20261025-0005', 'Stock In', '1.0000', '0.0000', '11.0000', '150.0000', 'Admin Supervisor', '2026-10-25 15:53:41');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('26', '2026-10-25 15:56:02', '4', 'STOCK_OUT', 'stock_transactions', '12', 'SO-20261025-0001', 'Stock Out - Expired', '0.0000', '9.0000', '20.0000', '250.0000', 'Admin Supervisor', '2026-10-25 15:56:02');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('27', '2026-10-26 10:04:54', '2', 'BREW_OUT', 'orders', '2', 'ORD-PRE-A69B1', 'Kitchen Brew & Serve ORD-PRE-A69B1 [Scheduled Pre-order] (1x Burger)', '0.0000', '1.0000', '12.0000', '35.0000', 'System', '2026-10-26 10:04:54');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('28', '2026-10-26 10:04:54', '5', 'BREW_OUT', 'orders', '2', 'ORD-PRE-A69B1', 'Kitchen Brew & Serve ORD-PRE-A69B1 [Scheduled Pre-order] (1x Burger)', '0.0000', '1.0000', '21.0000', '10.0000', 'System', '2026-10-26 10:04:54');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('29', '2026-10-26 11:13:54', '2', 'BREW_OUT', 'orders', '3', 'ORD-1003', 'Kitchen Brew & Serve ORD-1003 [Dine-In] (1x Burger)', '0.0000', '1.0000', '11.0000', '35.0000', 'System', '2026-10-26 11:13:54');
INSERT INTO `inventory_ledger` (`id`, `entry_date`, `item_id`, `txn_type`, `ref_table`, `ref_id`, `ref_no`, `particulars`, `qty_in`, `qty_out`, `balance_after`, `unit_cost`, `posted_by`, `created_at`) VALUES ('30', '2026-10-26 11:13:54', '5', 'BREW_OUT', 'orders', '3', 'ORD-1003', 'Kitchen Brew & Serve ORD-1003 [Dine-In] (1x Burger)', '0.0000', '1.0000', '20.0000', '10.0000', 'System', '2026-10-26 11:13:54');

DROP TABLE IF EXISTS `inventory_logs`;
CREATE TABLE `inventory_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `change_type` enum('Restock','Order Deduction','Waste / Loss','Manual Count') NOT NULL DEFAULT 'Restock',
  `quantity_change` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `reference_note` varchar(255) DEFAULT NULL,
  `performed_by` varchar(100) NOT NULL DEFAULT 'Admin Supervisor',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inv_prod` (`product_id`),
  CONSTRAINT `fk_inv_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inventory_logs` (`id`, `product_id`, `change_type`, `quantity_change`, `previous_stock`, `new_stock`, `reference_note`, `performed_by`, `created_at`) VALUES ('1', '1', 'Restock', '60', '0', '60', 'Initial opening stock calibration', 'Admin Supervisor', '2026-10-22 22:02:02');
INSERT INTO `inventory_logs` (`id`, `product_id`, `change_type`, `quantity_change`, `previous_stock`, `new_stock`, `reference_note`, `performed_by`, `created_at`) VALUES ('2', '2', 'Restock', '80', '0', '80', 'Initial opening stock calibration', 'Admin Supervisor', '2026-10-22 19:02:02');
INSERT INTO `inventory_logs` (`id`, `product_id`, `change_type`, `quantity_change`, `previous_stock`, `new_stock`, `reference_note`, `performed_by`, `created_at`) VALUES ('3', '3', 'Restock', '55', '0', '55', 'Initial opening stock calibration', 'Admin Supervisor', '2026-10-22 16:02:02');
INSERT INTO `inventory_logs` (`id`, `product_id`, `change_type`, `quantity_change`, `previous_stock`, `new_stock`, `reference_note`, `performed_by`, `created_at`) VALUES ('4', '4', 'Restock', '45', '0', '45', 'Initial opening stock calibration', 'Admin Supervisor', '2026-10-22 13:02:02');
INSERT INTO `inventory_logs` (`id`, `product_id`, `change_type`, `quantity_change`, `previous_stock`, `new_stock`, `reference_note`, `performed_by`, `created_at`) VALUES ('5', '5', 'Restock', '35', '0', '35', 'Initial opening stock calibration', 'Admin Supervisor', '2026-10-22 10:02:02');
INSERT INTO `inventory_logs` (`id`, `product_id`, `change_type`, `quantity_change`, `previous_stock`, `new_stock`, `reference_note`, `performed_by`, `created_at`) VALUES ('6', '6', 'Restock', '30', '0', '30', 'Initial opening stock calibration', 'Admin Supervisor', '2026-10-22 07:02:02');
INSERT INTO `inventory_logs` (`id`, `product_id`, `change_type`, `quantity_change`, `previous_stock`, `new_stock`, `reference_note`, `performed_by`, `created_at`) VALUES ('7', '1', 'Order Deduction', '-2', '60', '58', 'POS Checkout #ORD-1001', 'Cashier Station', '2026-10-23 12:55:02');
INSERT INTO `inventory_logs` (`id`, `product_id`, `change_type`, `quantity_change`, `previous_stock`, `new_stock`, `reference_note`, `performed_by`, `created_at`) VALUES ('9', '1', 'Order Deduction', '-1', '58', '57', 'POS Checkout #ORD-1002', 'Cashier Station', '2026-10-23 12:55:02');
INSERT INTO `inventory_logs` (`id`, `product_id`, `change_type`, `quantity_change`, `previous_stock`, `new_stock`, `reference_note`, `performed_by`, `created_at`) VALUES ('11', '1', 'Order Deduction', '-2', '57', '55', 'POS Checkout #ORD-1003', 'Cashier Station', '2026-10-23 12:55:39');
INSERT INTO `inventory_logs` (`id`, `product_id`, `change_type`, `quantity_change`, `previous_stock`, `new_stock`, `reference_note`, `performed_by`, `created_at`) VALUES ('13', '1', 'Order Deduction', '-1', '55', '54', 'POS Checkout #ORD-1004', 'Cashier Station', '2026-10-23 12:55:39');
INSERT INTO `inventory_logs` (`id`, `product_id`, `change_type`, `quantity_change`, `previous_stock`, `new_stock`, `reference_note`, `performed_by`, `created_at`) VALUES ('15', '2', 'Order Deduction', '-1', '80', '79', 'POS Checkout #ORD-1001', 'Cashier Station', '2026-10-23 15:55:21');

DROP TABLE IF EXISTS `item_conversions`;
CREATE TABLE `item_conversions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conv_no` varchar(50) NOT NULL,
  `conv_date` date NOT NULL,
  `source_item_id` bigint(20) unsigned NOT NULL,
  `source_qty` decimal(14,4) NOT NULL,
  `dest_item_id` bigint(20) unsigned NOT NULL,
  `factor` decimal(14,4) NOT NULL COMMENT '1 source unit = factor destination units',
  `dest_qty` decimal(14,4) NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Draft','Posted','Cancelled') NOT NULL DEFAULT 'Draft',
  `created_by` varchar(100) DEFAULT NULL,
  `posted_by` varchar(100) DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_conv_no` (`conv_no`),
  KEY `fk_conv_src` (`source_item_id`),
  KEY `fk_conv_dst` (`dest_item_id`),
  CONSTRAINT `fk_conv_dst` FOREIGN KEY (`dest_item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `fk_conv_src` FOREIGN KEY (`source_item_id`) REFERENCES `inventory_items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `item_conversions` (`id`, `conv_no`, `conv_date`, `source_item_id`, `source_qty`, `dest_item_id`, `factor`, `dest_qty`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('1', 'CV-20261024-0001', '2026-10-24', '4', '1.0000', '5', '25.0000', '25.0000', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-24 12:46:46', '2026-10-24 12:46:46');
INSERT INTO `item_conversions` (`id`, `conv_no`, `conv_date`, `source_item_id`, `source_qty`, `dest_item_id`, `factor`, `dest_qty`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('2', 'CV-20261025-0001', '2026-10-25', '7', '1.0000', '3', '1000.0000', '1000.0000', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-25 14:24:44', '2026-10-25 14:24:44');
INSERT INTO `item_conversions` (`id`, `conv_no`, `conv_date`, `source_item_id`, `source_qty`, `dest_item_id`, `factor`, `dest_qty`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('3', 'CV-20261025-0002', '2026-10-25', '4', '1.0000', '5', '10.0000', '10.0000', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-25 14:26:40', '2026-10-25 14:26:40');

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('1', '2014_10_12_000000_create_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('2', '2014_10_12_100000_create_password_reset_tokens_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('3', '2019_08_19_000000_create_failed_jobs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('4', '2019_12_14_000001_create_personal_access_tokens_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('5', '2026_01_01_000001_create_isatu_cafe_tables', '1');

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `product_name` varchar(191) NOT NULL,
  `size_name` varchar(50) DEFAULT 'Regular (12oz)',
  `sugar_level` varchar(50) DEFAULT '50%',
  `ice_level` varchar(50) DEFAULT 'Regular Ice',
  `addons_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`addons_json`)),
  `special_notes` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_orditem_ord` (`order_id`),
  CONSTRAINT `fk_orditem_ord` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `special_notes`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('1', '1', '2', 'Burger', 'Standard Serving', 'Pastry', 'Warm / Lightly Toasted', '[]', '', '1', '60.00', '60.00', '2026-10-25 14:41:22', '2026-10-25 14:41:22');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `special_notes`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('2', '2', '2', 'Burger', 'Standard Serving', 'Pastry', 'Warm / Lightly Toasted', '[]', 'Scheduled Pre-Order (Arrival 01:00 PM / Prep 12:45 PM)', '1', '60.00', '60.00', '2026-10-26 10:04:23', '2026-10-26 10:04:23');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `special_notes`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('3', '3', '2', 'Burger', 'Standard Serving', 'Pastry', 'Warm / Lightly Toasted', '[]', 'Pre-order from QR Pass', '1', '60.00', '60.00', '2026-10-26 10:49:24', '2026-10-26 10:49:24');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `special_notes`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('4', '4', '2', 'Burger', 'Standard Serving', 'Pastry', 'Warm / Lightly Toasted', '[]', 'Pre-order from QR Pass', '1', '60.00', '60.00', '2026-10-26 10:53:11', '2026-10-26 10:53:11');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `special_notes`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('5', '5', '2', 'Burger', 'Standard Serving', 'Pastry', 'Warm / Lightly Toasted', '[]', '', '1', '60.00', '60.00', '2026-10-26 10:57:05', '2026-10-26 10:57:05');

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `cashier_id` bigint(20) unsigned DEFAULT NULL,
  `order_type` varchar(50) NOT NULL DEFAULT 'Dine-In',
  `table_number` varchar(100) NOT NULL DEFAULT 'Takeout',
  `customer_name` varchar(191) NOT NULL DEFAULT 'Walk-in Customer',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` varchar(50) NOT NULL DEFAULT 'None',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vat_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL DEFAULT 'Cash',
  `payment_reference` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Brewing','Ready','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`, `order_number`, `reservation_id`, `cashier_id`, `order_type`, `table_number`, `customer_name`, `subtotal`, `discount_type`, `discount_amount`, `vat_amount`, `net_total`, `payment_method`, `payment_reference`, `status`, `created_at`, `updated_at`) VALUES ('1', 'ORD-1001', NULL, '2', 'Dine-In', 'Counter', 'p', '60.00', 'Student 10%', '6.00', '5.79', '54.00', 'Cash', '', 'Completed', '2026-10-25 14:41:22', '2026-10-25 14:42:21');
INSERT INTO `orders` (`id`, `order_number`, `reservation_id`, `cashier_id`, `order_type`, `table_number`, `customer_name`, `subtotal`, `discount_type`, `discount_amount`, `vat_amount`, `net_total`, `payment_method`, `payment_reference`, `status`, `created_at`, `updated_at`) VALUES ('2', 'ORD-PRE-A69B1', '6', NULL, 'Scheduled Pre-order', 'Table #2 (POD-02)', 'Pr', '60.00', 'None', '0.00', '6.43', '60.00', 'Pay at Counter', 'RES-20261026-001 | Staff Concierge Check-In | Arrival 01:00 PM (Prep 12:45 PM)', 'Completed', '2026-10-26 10:04:23', '2026-10-26 10:04:54');
INSERT INTO `orders` (`id`, `order_number`, `reservation_id`, `cashier_id`, `order_type`, `table_number`, `customer_name`, `subtotal`, `discount_type`, `discount_amount`, `vat_amount`, `net_total`, `payment_method`, `payment_reference`, `status`, `created_at`, `updated_at`) VALUES ('3', 'ORD-1003', '8', '1', 'Dine-In', 'LNG-01', 'Mari', '60.00', 'None', '0.00', '6.43', '60.00', 'Cash', '', 'Completed', '2026-10-26 10:49:24', '2026-10-26 11:13:54');
INSERT INTO `orders` (`id`, `order_number`, `reservation_id`, `cashier_id`, `order_type`, `table_number`, `customer_name`, `subtotal`, `discount_type`, `discount_amount`, `vat_amount`, `net_total`, `payment_method`, `payment_reference`, `status`, `created_at`, `updated_at`) VALUES ('4', 'ORD-1004', '9', '2', 'Dine-In', 'POD-02', 'Pr', '60.00', 'None', '0.00', '6.43', '60.00', 'Cash', '', 'Pending', '2026-10-26 10:53:11', '2026-10-26 10:53:11');
INSERT INTO `orders` (`id`, `order_number`, `reservation_id`, `cashier_id`, `order_type`, `table_number`, `customer_name`, `subtotal`, `discount_type`, `discount_amount`, `vat_amount`, `net_total`, `payment_method`, `payment_reference`, `status`, `created_at`, `updated_at`) VALUES ('5', 'ORD-1005', NULL, '2', 'Dine-In', 'Counter', 'Walk-In Customer', '60.00', 'None', '0.00', '6.43', '60.00', 'Cash', '', 'Pending', '2026-10-26 10:57:05', '2026-10-26 10:57:05');

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'Cash',
  `amount_tendered` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_change` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_reference` varchar(100) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Paid',
  `paid_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pay_ord` (`order_id`),
  CONSTRAINT `fk_pay_ord` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payments` (`id`, `order_id`, `reservation_id`, `payment_method`, `amount_tendered`, `amount_change`, `payment_reference`, `status`, `paid_at`, `created_at`, `updated_at`) VALUES ('1', '1', NULL, 'Cash', '54.00', '0.00', '', 'Paid', '2026-10-25 14:41:22', '2026-10-25 14:41:22', '2026-10-25 14:41:22');
INSERT INTO `payments` (`id`, `order_id`, `reservation_id`, `payment_method`, `amount_tendered`, `amount_change`, `payment_reference`, `status`, `paid_at`, `created_at`, `updated_at`) VALUES ('2', '3', NULL, 'Cash', '60.00', '0.00', '', 'Paid', '2026-10-26 10:49:24', '2026-10-26 10:49:24', '2026-10-26 10:49:24');
INSERT INTO `payments` (`id`, `order_id`, `reservation_id`, `payment_method`, `amount_tendered`, `amount_change`, `payment_reference`, `status`, `paid_at`, `created_at`, `updated_at`) VALUES ('3', '4', NULL, 'Cash', '60.00', '0.00', '', 'Paid', '2026-10-26 10:53:11', '2026-10-26 10:53:11', '2026-10-26 10:53:11');
INSERT INTO `payments` (`id`, `order_id`, `reservation_id`, `payment_method`, `amount_tendered`, `amount_change`, `payment_reference`, `status`, `paid_at`, `created_at`, `updated_at`) VALUES ('4', '5', NULL, 'Cash', '60.00', '0.00', '', 'Paid', '2026-10-26 10:57:05', '2026-10-26 10:57:05', '2026-10-26 10:57:05');

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `product_addons`;
CREATE TABLE `product_addons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Null applies to all drinks',
  `name` varchar(100) NOT NULL,
  `addon_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `product_item_addons`;
CREATE TABLE `product_item_addons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `finished_item_id` bigint(20) unsigned NOT NULL,
  `addon_name` varchar(100) NOT NULL,
  `ingredient_item_id` bigint(20) unsigned NOT NULL,
  `qty_per_unit` decimal(14,4) NOT NULL DEFAULT 1.0000,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pia_line` (`finished_item_id`,`addon_name`),
  KEY `idx_pia_finished` (`finished_item_id`),
  KEY `fk_pia_ingredient` (`ingredient_item_id`),
  CONSTRAINT `fk_pia_finished` FOREIGN KEY (`finished_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pia_ingredient` FOREIGN KEY (`ingredient_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `product_recipes`;
CREATE TABLE `product_recipes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `finished_item_id` bigint(20) unsigned NOT NULL,
  `ingredient_item_id` bigint(20) unsigned NOT NULL,
  `qty_per_unit` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_recipe_line` (`finished_item_id`,`ingredient_item_id`),
  KEY `fk_recipe_ingredient` (`ingredient_item_id`),
  CONSTRAINT `fk_recipe_finished` FOREIGN KEY (`finished_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recipe_ingredient` FOREIGN KEY (`ingredient_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_recipes` (`id`, `finished_item_id`, `ingredient_item_id`, `qty_per_unit`, `created_at`) VALUES ('1', '1', '2', '1.0000', '2026-10-24 10:52:00');
INSERT INTO `product_recipes` (`id`, `finished_item_id`, `ingredient_item_id`, `qty_per_unit`, `created_at`) VALUES ('2', '1', '3', '1.0000', '2026-10-24 10:52:00');
INSERT INTO `product_recipes` (`id`, `finished_item_id`, `ingredient_item_id`, `qty_per_unit`, `created_at`) VALUES ('3', '6', '5', '1.0000', '2026-10-24 12:51:07');
INSERT INTO `product_recipes` (`id`, `finished_item_id`, `ingredient_item_id`, `qty_per_unit`, `created_at`) VALUES ('4', '6', '2', '1.0000', '2026-10-24 12:51:07');

DROP TABLE IF EXISTS `product_sizes`;
CREATE TABLE `product_sizes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `size_name` varchar(50) NOT NULL,
  `price_additional` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_size_prod` (`product_id`),
  CONSTRAINT `fk_size_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock_alert` int(11) NOT NULL DEFAULT 10,
  `unit` varchar(50) NOT NULL DEFAULT 'cups',
  `badge` varchar(50) DEFAULT NULL COMMENT 'Bestseller, Signature, etc.',
  `has_customizations` tinyint(1) NOT NULL DEFAULT 1,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_code` (`product_code`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_prod_cat` (`category_id`),
  CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `category_id`, `product_code`, `name`, `slug`, `description`, `image_url`, `price`, `base_price`, `cost_price`, `stock_quantity`, `min_stock_alert`, `unit`, `badge`, `has_customizations`, `is_available`, `created_at`, `updated_at`) VALUES ('1', '4', 'SKU-BF-001', 'Burger and Fries', 'burger-and-fries-sku-bf-001', 'Freshly grilled burger patty served with crispy golden fries.', NULL, '120.00', '120.00', '55.00', '0', '10', 'pc', NULL, '0', '1', '2026-10-24 10:52:00', '2026-10-24 10:52:00');
INSERT INTO `products` (`id`, `category_id`, `product_code`, `name`, `slug`, `description`, `image_url`, `price`, `base_price`, `cost_price`, `stock_quantity`, `min_stock_alert`, `unit`, `badge`, `has_customizations`, `is_available`, `created_at`, `updated_at`) VALUES ('2', '4', 'SKU-BURGER-002', 'Burger', 'burger-sku-burger-002', '', NULL, '60.00', '60.00', '45.00', '0', '10', 'pc', NULL, '1', '1', '2026-10-24 12:50:25', '2026-10-24 12:50:25');

DROP TABLE IF EXISTS `reservation_items`;
CREATE TABLE `reservation_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `product_name` varchar(191) NOT NULL,
  `size_name` varchar(50) DEFAULT 'Regular (12oz)',
  `sugar_level` varchar(50) DEFAULT '50%',
  `ice_level` varchar(50) DEFAULT 'Regular Ice',
  `addons_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`addons_json`)),
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_resitem_res` (`reservation_id`),
  CONSTRAINT `fk_resitem_res` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reservation_items` (`id`, `reservation_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('1', '1', '2', 'Burger', 'Standard Serving', 'Pastry', 'Warm / Lightly Toasted', '[]', '1', '60.00', '60.00', '2026-10-24 12:53:17', '2026-10-24 12:53:17');
INSERT INTO `reservation_items` (`id`, `reservation_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('2', '2', '2', 'Burger', 'Standard Serving', 'Pastry', 'Warm / Lightly Toasted', '[]', '1', '60.00', '60.00', '2026-10-25 14:32:15', '2026-10-25 14:32:15');
INSERT INTO `reservation_items` (`id`, `reservation_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('3', '5', '1', 'Burger and Fries', 'Standard Serving', 'Pastry', 'Serve As-Is (Room Temp)', '[]', '1', '120.00', '120.00', '2026-10-25 15:44:00', '2026-10-25 15:44:00');
INSERT INTO `reservation_items` (`id`, `reservation_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('4', '6', '2', 'Burger', 'Standard Serving', 'Pastry', 'Warm / Lightly Toasted', '[]', '1', '60.00', '60.00', '2026-10-26 10:03:40', '2026-10-26 10:03:40');
INSERT INTO `reservation_items` (`id`, `reservation_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('5', '7', '1', 'Burger and Fries', 'Standard Serving', 'Pastry', 'Warm / Lightly Toasted', '[]', '1', '120.00', '120.00', '2026-10-26 10:12:44', '2026-10-26 10:12:44');
INSERT INTO `reservation_items` (`id`, `reservation_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('6', '8', '2', 'Burger', 'Standard Serving', 'Pastry', 'Warm / Lightly Toasted', '[]', '1', '60.00', '60.00', '2026-10-26 10:46:58', '2026-10-26 10:46:58');
INSERT INTO `reservation_items` (`id`, `reservation_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `addons_json`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES ('7', '9', '2', 'Burger', 'Standard Serving', 'Pastry', 'Warm / Lightly Toasted', '[]', '1', '60.00', '60.00', '2026-10-26 10:52:42', '2026-10-26 10:52:42');

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_number` varchar(50) NOT NULL,
  `qr_token` varchar(100) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `table_id` bigint(20) unsigned NOT NULL,
  `customer_name` varchar(191) NOT NULL,
  `customer_type` enum('Student','Faculty','Guest','Senior/PWD') NOT NULL DEFAULT 'Student',
  `id_number` varchar(100) DEFAULT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `time_slot` varchar(100) NOT NULL,
  `guest_count` int(11) NOT NULL DEFAULT 1,
  `special_notes` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(50) NOT NULL DEFAULT 'Pending (Pay at Counter)',
  `status` enum('Confirmed','Checked-In','Completed','Cancelled','No-Show') NOT NULL DEFAULT 'Confirmed',
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservation_number` (`reservation_number`),
  UNIQUE KEY `qr_token` (`qr_token`),
  KEY `idx_res_table` (`table_id`),
  CONSTRAINT `fk_res_table` FOREIGN KEY (`table_id`) REFERENCES `dining_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reservations` (`id`, `reservation_number`, `qr_token`, `user_id`, `table_id`, `customer_name`, `customer_type`, `id_number`, `phone`, `email`, `reservation_date`, `time_slot`, `guest_count`, `special_notes`, `total_amount`, `payment_status`, `status`, `checked_in_at`, `created_at`, `updated_at`) VALUES ('1', 'RES-20261024-001', 'ISATU-RES-20261024-001-E9FD', NULL, '2', 'Mr. P', 'Student', '2023-2319-C', '0995896950', 'hesitantthyme@gmail.com', '2026-10-24', '01:00 PM - 03:00 PM', '1', '', '60.00', 'Pending (Pay at Counter)', 'Completed', NULL, '2026-10-24 12:53:17', '2026-10-25 15:13:17');
INSERT INTO `reservations` (`id`, `reservation_number`, `qr_token`, `user_id`, `table_id`, `customer_name`, `customer_type`, `id_number`, `phone`, `email`, `reservation_date`, `time_slot`, `guest_count`, `special_notes`, `total_amount`, `payment_status`, `status`, `checked_in_at`, `created_at`, `updated_at`) VALUES ('2', 'RES-20261025-001', 'ISATU-RES-20261025-001-7C48', NULL, '1', 'P', 'Student', '2023-2319-C', '0995896950', 'hesitantthyme@gmail.com', '2026-10-25', '01:00 PM - 03:00 PM', '1', '', '60.00', 'Pending (Pay at Counter)', 'Completed', '2026-10-25 15:00:11', '2026-10-25 14:32:15', '2026-10-25 15:07:24');
INSERT INTO `reservations` (`id`, `reservation_number`, `qr_token`, `user_id`, `table_id`, `customer_name`, `customer_type`, `id_number`, `phone`, `email`, `reservation_date`, `time_slot`, `guest_count`, `special_notes`, `total_amount`, `payment_status`, `status`, `checked_in_at`, `created_at`, `updated_at`) VALUES ('3', 'RES-20261025-002', 'ISATU-RES-20261025-002-F517', NULL, '9', 'vinz', 'Student', '2023-2319-C', '09071326371', 'vinz@gmail.com', '2026-10-25', '01:00 PM - 03:00 PM', '4', '', '0.00', 'Reserved', 'Completed', '2026-10-25 14:52:55', '2026-10-25 14:50:51', '2026-10-25 15:07:27');
INSERT INTO `reservations` (`id`, `reservation_number`, `qr_token`, `user_id`, `table_id`, `customer_name`, `customer_type`, `id_number`, `phone`, `email`, `reservation_date`, `time_slot`, `guest_count`, `special_notes`, `total_amount`, `payment_status`, `status`, `checked_in_at`, `created_at`, `updated_at`) VALUES ('4', 'RES-20261025-003', 'ISATU-RES-20261025-003-C092', NULL, '4', 'jericho', 'Student', '2023-2287-C', '09943692306', 'jericho@gmail.com', '2026-10-25', '01:00 PM - 03:00 PM', '2', '', '0.00', 'Reserved', 'Completed', '2026-10-25 15:00:03', '2026-10-25 14:55:17', '2026-10-25 15:07:20');
INSERT INTO `reservations` (`id`, `reservation_number`, `qr_token`, `user_id`, `table_id`, `customer_name`, `customer_type`, `id_number`, `phone`, `email`, `reservation_date`, `time_slot`, `guest_count`, `special_notes`, `total_amount`, `payment_status`, `status`, `checked_in_at`, `created_at`, `updated_at`) VALUES ('5', 'RES-20261025-004', 'ISATU-RES-20261025-004-9721', NULL, '4', 'Mark Kyle', 'Guest', '', '09123456879', 'mark@gmail.com', '2026-10-25', '10:00 AM - 12:00 PM', '2', '', '120.00', 'Pending (Pay at Counter)', 'Completed', '2026-10-25 15:45:53', '2026-10-25 15:44:00', '2026-10-25 15:46:06');
INSERT INTO `reservations` (`id`, `reservation_number`, `qr_token`, `user_id`, `table_id`, `customer_name`, `customer_type`, `id_number`, `phone`, `email`, `reservation_date`, `time_slot`, `guest_count`, `special_notes`, `total_amount`, `payment_status`, `status`, `checked_in_at`, `created_at`, `updated_at`) VALUES ('6', 'RES-20261026-001', 'ISATU-RES-20261026-001-6151', NULL, '2', 'Pr', 'Student', '2023-2319-C', '09935896950', '', '2026-10-26', '01:00 PM - 03:00 PM', '1', '', '60.00', 'Pending (Pay at Counter)', 'Completed', '2026-10-26 10:04:23', '2026-10-26 10:03:40', '2026-10-26 10:07:00');
INSERT INTO `reservations` (`id`, `reservation_number`, `qr_token`, `user_id`, `table_id`, `customer_name`, `customer_type`, `id_number`, `phone`, `email`, `reservation_date`, `time_slot`, `guest_count`, `special_notes`, `total_amount`, `payment_status`, `status`, `checked_in_at`, `created_at`, `updated_at`) VALUES ('7', 'RES-20261026-002', 'ISATU-RES-20261026-002-25B7', NULL, '2', 'Pr', 'Guest', '', '09935896950', '', '2026-10-26', '01:00 PM - 03:00 PM', '1', '', '120.00', 'Pending (Pay at Counter)', 'Cancelled', NULL, '2026-10-26 10:12:44', '2026-10-26 10:14:49');
INSERT INTO `reservations` (`id`, `reservation_number`, `qr_token`, `user_id`, `table_id`, `customer_name`, `customer_type`, `id_number`, `phone`, `email`, `reservation_date`, `time_slot`, `guest_count`, `special_notes`, `total_amount`, `payment_status`, `status`, `checked_in_at`, `created_at`, `updated_at`) VALUES ('8', 'RES-20261026-003', 'ISATU-RES-20261026-003-6BD1', NULL, '4', 'Mari', 'Student', '2023-1333-A', '091245678901', 'maria@gmail.com', '2026-10-26', '10:00 AM - 12:00 PM', '1', '', '60.00', 'Pending (Pay at Counter)', 'Checked-In', '2026-10-26 10:49:24', '2026-10-26 10:46:58', '2026-10-26 10:49:24');
INSERT INTO `reservations` (`id`, `reservation_number`, `qr_token`, `user_id`, `table_id`, `customer_name`, `customer_type`, `id_number`, `phone`, `email`, `reservation_date`, `time_slot`, `guest_count`, `special_notes`, `total_amount`, `payment_status`, `status`, `checked_in_at`, `created_at`, `updated_at`) VALUES ('9', 'RES-20261026-004', 'ISATU-RES-20261026-004-E4B7', NULL, '2', 'Pr', 'Guest', '', '09071326371', '', '2026-10-26', '10:00 AM - 12:00 PM', '1', '', '60.00', 'Pending (Pay at Counter)', 'Checked-In', '2026-10-26 10:53:11', '2026-10-26 10:52:42', '2026-10-26 10:53:11');

DROP TABLE IF EXISTS `stock_transaction_lines`;
CREATE TABLE `stock_transaction_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `txn_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit_cost` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_stl_txn` (`txn_id`),
  KEY `fk_stl_item` (`item_id`),
  CONSTRAINT `fk_stl_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `fk_stl_txn` FOREIGN KEY (`txn_id`) REFERENCES `stock_transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('1', '1', '4', '1.0000', '10.0000', '');
INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('2', '2', '3', '1.0000', '20.0000', '');
INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('3', '3', '2', '1.0000', '35.0000', '');
INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('4', '4', '4', '25.0000', '10.0000', '');
INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('5', '5', '2', '25.0000', '35.0000', '');
INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('6', '6', '3', '25.0000', '20.0000', '');
INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('7', '7', '7', '1.0000', '0.0000', '');
INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('8', '8', '7', '9.0000', '0.0000', '');
INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('9', '9', '7', '1.0000', '0.0000', '');
INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('10', '10', '4', '5.0000', '250.0000', 'no more');
INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('11', '11', '7', '1.0000', '150.0000', '');
INSERT INTO `stock_transaction_lines` (`id`, `txn_id`, `item_id`, `quantity`, `unit_cost`, `remarks`) VALUES ('12', '12', '4', '9.0000', '250.0000', '');

DROP TABLE IF EXISTS `stock_transactions`;
CREATE TABLE `stock_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `txn_no` varchar(50) NOT NULL,
  `txn_type` enum('IN','OUT') NOT NULL,
  `txn_date` date NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL COMMENT 'DR / Invoice / Memo No.',
  `party` varchar(191) DEFAULT NULL COMMENT 'Supplier (IN) or Reason (OUT)',
  `remarks` text DEFAULT NULL,
  `status` enum('Draft','Posted','Cancelled') NOT NULL DEFAULT 'Draft',
  `created_by` varchar(100) DEFAULT NULL,
  `posted_by` varchar(100) DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_txn_no` (`txn_no`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('1', 'SI-20261024-0001', 'IN', '2026-10-24', '', '', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-24 12:04:33', '2026-10-24 12:04:33');
INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('2', 'SI-20261024-0002', 'IN', '2026-10-24', '', '', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-24 12:07:40', '2026-10-24 12:07:40');
INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('3', 'SI-20261024-0003', 'IN', '2026-10-24', '', '', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-24 12:07:50', '2026-10-24 12:07:50');
INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('4', 'SI-20261024-0004', 'IN', '2026-10-24', '', '', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-24 12:12:29', '2026-10-24 12:12:29');
INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('5', 'SI-20261024-0005', 'IN', '2026-10-24', '', '', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-24 12:13:03', '2026-10-24 12:13:03');
INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('6', 'SI-20261024-0006', 'IN', '2026-10-24', '', '', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-24 12:15:23', '2026-10-24 12:15:23');
INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('7', 'SI-20261025-0001', 'IN', '2026-10-25', '', '', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-25 14:21:43', '2026-10-25 14:21:43');
INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('8', 'SI-20261025-0002', 'IN', '2026-10-25', '', '', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-25 14:22:10', '2026-10-25 14:22:10');
INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('9', 'SI-20261025-0003', 'IN', '2026-10-25', '', '', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-25 14:22:16', '2026-10-25 14:22:16');
INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('10', 'SI-20261025-0004', 'IN', '2026-10-25', '', '', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-25 15:52:51', '2026-10-25 15:52:51');
INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('11', 'SI-20261025-0005', 'IN', '2026-10-25', '', '', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-25 15:53:41', '2026-10-25 15:53:41');
INSERT INTO `stock_transactions` (`id`, `txn_no`, `txn_type`, `txn_date`, `reference_no`, `party`, `remarks`, `status`, `created_by`, `posted_by`, `posted_at`, `created_at`) VALUES ('12', 'SO-20261025-0001', 'OUT', '2026-10-25', '', 'Expired', '', 'Posted', 'Admin Supervisor', 'Admin Supervisor', '2026-10-25 15:56:02', '2026-10-25 15:56:02');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier','barista','customer') NOT NULL DEFAULT 'customer',
  `affiliation` enum('student','faculty','visitor','pwd_senior') DEFAULT 'student',
  `id_number` varchar(100) DEFAULT NULL COMMENT 'Student or Employee ID Number',
  `phone` varchar(50) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `affiliation`, `id_number`, `phone`, `remember_token`, `created_at`, `updated_at`) VALUES ('1', 'Admin Supervisor', 'admin@isatu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'faculty', 'ISATU-ADMIN-001', '0917-111-2222', NULL, '2026-10-19 00:37:00', '2026-10-19 00:37:00');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `affiliation`, `id_number`, `phone`, `remember_token`, `created_at`, `updated_at`) VALUES ('2', 'Staff Bea Ramos', 'cashier.bea@isatu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier', 'student', 'ISATU-STU-2022-049', '0918-333-4444', NULL, '2026-10-19 00:37:00', '2026-10-19 00:37:00');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `affiliation`, `id_number`, `phone`, `remember_token`, `created_at`, `updated_at`) VALUES ('3', 'Barista Jason Cole', 'barista.jason@isatu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'barista', 'student', 'ISATU-STU-2021-118', '0919-555-6666', NULL, '2026-10-19 00:37:00', '2026-10-19 00:37:00');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `affiliation`, `id_number`, `phone`, `remember_token`, `created_at`, `updated_at`) VALUES ('4', 'Engr. Mark Villanueva', 'mark.villanueva@isatu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'faculty', 'ISATU-FAC-2018-091', '0917-123-4567', NULL, '2026-10-19 00:37:00', '2026-10-19 00:37:00');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `affiliation`, `id_number`, `phone`, `remember_token`, `created_at`, `updated_at`) VALUES ('5', 'Maria Santos', 'staff.maria@isatu.edu.ph', '$2y$10$MxfsVVTq6wjH/kS.9lceuOwZQZ3SmylLRWcvJYNtowYRBmC/D4NVq', 'cashier', 'student', 'ISATU-STU-2023-012', '0917-888-9999', NULL, '2026-10-23 07:04:42', '2026-10-23 07:04:42');
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `affiliation`, `id_number`, `phone`, `remember_token`, `created_at`, `updated_at`) VALUES ('6', 'Jericho Gamarcha', 'JG@gmail.com', '$2y$10$elQ39M.Cv6CHia9nj1uq.e33JCkS9.TGha3fV2j2OWWZSuTA0waWa', 'barista', 'student', '2023-2287-C', '09010101010', NULL, '2026-10-25 15:02:51', '2026-10-25 15:02:51');

DROP TABLE IF EXISTS `zones`;
CREATE TABLE `zones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'bi-grid',
  `description` varchar(255) DEFAULT NULL,
  `badge_color` varchar(50) NOT NULL DEFAULT 'primary',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `zones` (`id`, `name`, `slug`, `icon`, `description`, `badge_color`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'Study Hub', 'study-hub', 'bi-laptop', 'Individual sound-dampened quiet pods with dedicated AC outlets and high-speed Wi-Fi.', 'primary', '1', '2026-10-23 08:02:02', '2026-10-23 08:02:02');
INSERT INTO `zones` (`id`, `name`, `slug`, `icon`, `description`, `badge_color`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'Lounge', 'lounge', 'bi-cup-straw', 'Plush leather sofas and coffee tables suited for relaxed discussions.', 'warning', '1', '2026-10-23 08:02:02', '2026-10-23 08:02:02');
INSERT INTO `zones` (`id`, `name`, `slug`, `icon`, `description`, `badge_color`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'Main Hall', 'main-hall', 'bi-people', 'Spacious acacia dining tables for student study groups and faculty dining.', 'info', '1', '2026-10-23 08:02:02', '2026-10-23 08:02:02');
INSERT INTO `zones` (`id`, `name`, `slug`, `icon`, `description`, `badge_color`, `is_active`, `created_at`, `updated_at`) VALUES ('4', 'Terrace', 'terrace', 'bi-sun', 'Shaded al fresco seating overlooking campus grounds and greenery.', 'success', '1', '2026-10-23 08:02:02', '2026-10-23 08:02:02');

SET FOREIGN_KEY_CHECKS = 1;
