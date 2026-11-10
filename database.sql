-- MariaDB dump 10.17  Distrib 10.4.11-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_isatu_pos
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `db_isatu_pos`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `u534933225_cms` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `u534933225_cms`;

--
-- Table structure for table `brew_batches`
--

DROP TABLE IF EXISTS `brew_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brew_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_date` date NOT NULL,
  `finished_item_id` bigint unsigned NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `addons_json` text COLLATE utf8mb4_unicode_ci,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Draft','Posted','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft',
  `created_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posted_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_batch_no` (`batch_no`),
  KEY `fk_brew_item` (`finished_item_id`),
  CONSTRAINT `fk_brew_item` FOREIGN KEY (`finished_item_id`) REFERENCES `inventory_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brew_batches`
--

LOCK TABLES `brew_batches` WRITE;
/*!40000 ALTER TABLE `brew_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `brew_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Hot Coffee & Classics','hot-coffee','cup-hot',1,1,'2026-10-18 16:37:00','2026-10-18 16:37:00'),(2,'Iced Espresso & Signatures','iced-espresso','cup-straw',2,1,'2026-10-18 16:37:00','2026-10-18 16:37:00'),(3,'Non-Coffee & Frappes','non-coffee','snow',3,1,'2026-10-18 16:37:00','2026-10-18 16:37:00'),(4,'Pastries & Snacks','pastries-snacks','cake2',4,1,'2026-10-18 16:37:00','2026-10-18 16:37:00');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dining_tables`
--

DROP TABLE IF EXISTS `dining_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dining_tables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `table_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Study Hub, Lounge, Main Hall, Terrace',
  `zone_id` bigint unsigned DEFAULT NULL,
  `capacity` int NOT NULL DEFAULT '2',
  `has_power_outlet` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('available','reserved','occupied','maintenance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_code` (`table_code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dining_tables`
--

LOCK TABLES `dining_tables` WRITE;
/*!40000 ALTER TABLE `dining_tables` DISABLE KEYS */;
INSERT INTO `dining_tables` VALUES (1,'POD-01','Smart Study Pod Alpha','Study Hub',1,1,1,'available','Individual sound-dampened quiet study pod with dual AC power outlet, USB fast-charger, and LED desk lamp.','2026-10-18 16:37:00','2026-10-23 03:40:40'),(2,'POD-02','Smart Study Pod Beta','Study Hub',1,1,1,'available','Individual quiet pod with campus high-speed Wi-Fi access and power outlet.','2026-10-18 16:37:00','2026-10-23 00:02:02'),(3,'POD-03','Duo Collaborative Pod','Study Hub',1,2,1,'available','Two-seater workstation equipped with power outlets for thesis pair-programming or tutoring.','2026-10-18 16:37:00','2026-10-24 02:48:03'),(4,'LNG-01','Executive Sofa Booth 1','Lounge',2,4,1,'available','Comfortable leather booth near window with garden view, power strip available.','2026-10-18 16:37:00','2026-10-23 00:02:02'),(5,'LNG-02','Coffee Corner Sofa 2','Lounge',2,2,0,'available','Low-profile coffee table with soft armchairs for relaxed conversations.','2026-10-18 16:37:00','2026-10-24 02:48:03'),(6,'HAL-01','Main Hall Four-Seater','Main Hall',3,4,0,'available','Solid acacia wooden dining table for groups and faculty dining.','2026-10-18 16:37:00','2026-10-23 00:02:02'),(7,'HAL-02','Main Hall Round Table','Main Hall',3,6,0,'available','Spacious round dining table suited for study groups and club meetings.','2026-10-18 16:37:00','2026-10-23 00:02:02'),(8,'TER-01','Outdoor Terrace Patio 1','Terrace',4,2,0,'available','Open-air al fresco dining surrounded by campus garden foliage.','2026-10-18 16:37:00','2026-10-23 04:23:27'),(9,'TER-02','Outdoor Terrace Patio 2','Terrace',4,4,0,'available','Shaded al fresco seating with fresh campus breeze.','2026-10-18 16:37:00','2026-10-23 00:02:02');
/*!40000 ALTER TABLE `dining_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_type` enum('raw','finished') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'raw' COMMENT 'raw = ingredient / raw material, finished = sellable product',
  `unit` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pc',
  `product_id` bigint unsigned DEFAULT NULL COMMENT 'Linked POS product (finished items only)',
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Optional item photo/thumbnail path',
  `unit_cost` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `reorder_level` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `current_stock` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT 'Cached running balance (mirrors ledger)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_item_code` (`item_code`),
  UNIQUE KEY `uq_item_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
INSERT INTO `inventory_items` VALUES (1,'SKU-BF-001','Burger and Fries','finished','pc',1,NULL,55.0000,10.0000,0.0000,1,'2026-10-24 02:52:00','2026-10-24 02:52:00'),(2,'RM-PATTY','Patty','raw','pc',NULL,NULL,35.0000,10.0000,0.0000,1,'2026-10-24 02:52:00','2026-10-24 02:52:00'),(3,'RM-FRIES','Fries','raw','pc',NULL,NULL,20.0000,10.0000,0.0000,1,'2026-10-24 02:52:00','2026-10-24 02:52:00');
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_ledger`
--

DROP TABLE IF EXISTS `inventory_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_ledger` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entry_date` datetime NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `txn_type` enum('STOCK_IN','STOCK_OUT','POS_SALE','RES_SALE','CONV_OUT','CONV_IN','BREW_OUT','BREW_IN') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_table` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_id` bigint unsigned NOT NULL,
  `ref_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `particulars` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty_in` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `qty_out` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `balance_after` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `unit_cost` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `posted_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ledger_item` (`item_id`,`id`),
  KEY `idx_ledger_ref` (`ref_table`,`ref_id`),
  KEY `idx_ledger_date` (`entry_date`),
  CONSTRAINT `fk_ledger_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_ledger`
--

LOCK TABLES `inventory_ledger` WRITE;
/*!40000 ALTER TABLE `inventory_ledger` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_ledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_logs`
--

DROP TABLE IF EXISTS `inventory_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `change_type` enum('Restock','Order Deduction','Waste / Loss','Manual Count') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Restock',
  `quantity_change` int NOT NULL,
  `previous_stock` int NOT NULL,
  `new_stock` int NOT NULL,
  `reference_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `performed_by` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Admin Supervisor',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inv_prod` (`product_id`),
  CONSTRAINT `fk_inv_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_logs`
--

LOCK TABLES `inventory_logs` WRITE;
/*!40000 ALTER TABLE `inventory_logs` DISABLE KEYS */;
INSERT INTO `inventory_logs` VALUES (1,1,'Restock',60,0,60,'Initial opening stock calibration','Admin Supervisor','2026-10-22 14:02:02'),(2,2,'Restock',80,0,80,'Initial opening stock calibration','Admin Supervisor','2026-10-22 11:02:02'),(3,3,'Restock',55,0,55,'Initial opening stock calibration','Admin Supervisor','2026-10-22 08:02:02'),(4,4,'Restock',45,0,45,'Initial opening stock calibration','Admin Supervisor','2026-10-22 05:02:02'),(5,5,'Restock',35,0,35,'Initial opening stock calibration','Admin Supervisor','2026-10-22 02:02:02'),(6,6,'Restock',30,0,30,'Initial opening stock calibration','Admin Supervisor','2026-10-21 23:02:02'),(7,1,'Order Deduction',-2,60,58,'POS Checkout #ORD-1001','Cashier Station','2026-10-23 04:55:02'),(9,1,'Order Deduction',-1,58,57,'POS Checkout #ORD-1002','Cashier Station','2026-10-23 04:55:02'),(11,1,'Order Deduction',-2,57,55,'POS Checkout #ORD-1003','Cashier Station','2026-10-23 04:55:39'),(13,1,'Order Deduction',-1,55,54,'POS Checkout #ORD-1004','Cashier Station','2026-10-23 04:55:39'),(15,2,'Order Deduction',-1,80,79,'POS Checkout #ORD-1001','Cashier Station','2026-10-23 07:55:21');
/*!40000 ALTER TABLE `inventory_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_conversions`
--

DROP TABLE IF EXISTS `item_conversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_conversions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conv_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conv_date` date NOT NULL,
  `source_item_id` bigint unsigned NOT NULL,
  `source_qty` decimal(14,4) NOT NULL,
  `dest_item_id` bigint unsigned NOT NULL,
  `factor` decimal(14,4) NOT NULL COMMENT '1 source unit = factor destination units',
  `dest_qty` decimal(14,4) NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Draft','Posted','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft',
  `created_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posted_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_conv_no` (`conv_no`),
  KEY `fk_conv_src` (`source_item_id`),
  KEY `fk_conv_dst` (`dest_item_id`),
  CONSTRAINT `fk_conv_dst` FOREIGN KEY (`dest_item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `fk_conv_src` FOREIGN KEY (`source_item_id`) REFERENCES `inventory_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_conversions`
--

LOCK TABLES `item_conversions` WRITE;
/*!40000 ALTER TABLE `item_conversions` DISABLE KEYS */;
/*!40000 ALTER TABLE `item_conversions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2026_01_01_000001_create_isatu_cafe_tables',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Regular (12oz)',
  `sugar_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '50%',
  `ice_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Regular Ice',
  `addons_json` json DEFAULT NULL,
  `special_notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `line_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_orditem_ord` (`order_id`),
  CONSTRAINT `fk_orditem_ord` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `cashier_id` bigint unsigned DEFAULT NULL,
  `order_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Dine-In',
  `table_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Takeout',
  `customer_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Walk-in Customer',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `vat_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `net_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Cash',
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Pending','Brewing','Ready','Completed','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Cash',
  `amount_tendered` decimal(10,2) NOT NULL DEFAULT '0.00',
  `amount_change` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Paid',
  `paid_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pay_ord` (`order_id`),
  CONSTRAINT `fk_pay_ord` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_addons`
--

DROP TABLE IF EXISTS `product_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_addons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned DEFAULT NULL COMMENT 'Null applies to all drinks',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `addon_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_addons`
--

LOCK TABLES `product_addons` WRITE;
/*!40000 ALTER TABLE `product_addons` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_addons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_item_addons`
--

DROP TABLE IF EXISTS `product_item_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_item_addons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `finished_item_id` bigint unsigned NOT NULL,
  `addon_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ingredient_item_id` bigint unsigned NOT NULL,
  `qty_per_unit` decimal(14,4) NOT NULL DEFAULT '1.0000',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pia_line` (`finished_item_id`,`addon_name`),
  KEY `idx_pia_finished` (`finished_item_id`),
  KEY `fk_pia_ingredient` (`ingredient_item_id`),
  CONSTRAINT `fk_pia_finished` FOREIGN KEY (`finished_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pia_ingredient` FOREIGN KEY (`ingredient_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_item_addons`
--

LOCK TABLES `product_item_addons` WRITE;
/*!40000 ALTER TABLE `product_item_addons` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_item_addons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_recipes`
--

DROP TABLE IF EXISTS `product_recipes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_recipes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `finished_item_id` bigint unsigned NOT NULL,
  `ingredient_item_id` bigint unsigned NOT NULL,
  `qty_per_unit` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_recipe_line` (`finished_item_id`,`ingredient_item_id`),
  KEY `fk_recipe_ingredient` (`ingredient_item_id`),
  CONSTRAINT `fk_recipe_finished` FOREIGN KEY (`finished_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recipe_ingredient` FOREIGN KEY (`ingredient_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_recipes`
--

LOCK TABLES `product_recipes` WRITE;
/*!40000 ALTER TABLE `product_recipes` DISABLE KEYS */;
INSERT INTO `product_recipes` VALUES (1,1,2,1.0000,'2026-10-24 02:52:00'),(2,1,3,1.0000,'2026-10-24 02:52:00');
/*!40000 ALTER TABLE `product_recipes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_sizes`
--

DROP TABLE IF EXISTS `product_sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_sizes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `size_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_additional` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_size_prod` (`product_id`),
  CONSTRAINT `fk_size_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_sizes`
--

LOCK TABLES `product_sizes` WRITE;
/*!40000 ALTER TABLE `product_sizes` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_sizes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `product_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `base_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cost_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock_quantity` int NOT NULL DEFAULT '0',
  `min_stock_alert` int NOT NULL DEFAULT '10',
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cups',
  `badge` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Bestseller, Signature, etc.',
  `has_customizations` tinyint(1) NOT NULL DEFAULT '1',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_code` (`product_code`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_prod_cat` (`category_id`),
  CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,4,'SKU-BF-001','Burger and Fries','burger-and-fries-sku-bf-001','Freshly grilled burger patty served with crispy golden fries.',NULL,120.00,120.00,55.00,0,10,'pc',NULL,0,1,'2026-10-24 02:52:00','2026-10-24 02:52:00');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_items`
--

DROP TABLE IF EXISTS `reservation_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservation_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Regular (12oz)',
  `sugar_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '50%',
  `ice_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Regular Ice',
  `addons_json` json DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `line_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_resitem_res` (`reservation_id`),
  CONSTRAINT `fk_resitem_res` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_items`
--

LOCK TABLES `reservation_items` WRITE;
/*!40000 ALTER TABLE `reservation_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservation_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `table_id` bigint unsigned NOT NULL,
  `customer_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_type` enum('Student','Faculty','Guest','Senior/PWD') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Student',
  `id_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `time_slot` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_count` int NOT NULL DEFAULT '1',
  `special_notes` text COLLATE utf8mb4_unicode_ci,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending (Pay at Counter)',
  `status` enum('Confirmed','Checked-In','Completed','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Confirmed',
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservation_number` (`reservation_number`),
  UNIQUE KEY `qr_token` (`qr_token`),
  KEY `idx_res_table` (`table_id`),
  CONSTRAINT `fk_res_table` FOREIGN KEY (`table_id`) REFERENCES `dining_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transaction_lines`
--

DROP TABLE IF EXISTS `stock_transaction_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transaction_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `txn_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit_cost` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_stl_txn` (`txn_id`),
  KEY `fk_stl_item` (`item_id`),
  CONSTRAINT `fk_stl_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `fk_stl_txn` FOREIGN KEY (`txn_id`) REFERENCES `stock_transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transaction_lines`
--

LOCK TABLES `stock_transaction_lines` WRITE;
/*!40000 ALTER TABLE `stock_transaction_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transaction_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transactions`
--

DROP TABLE IF EXISTS `stock_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `txn_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `txn_type` enum('IN','OUT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `txn_date` date NOT NULL,
  `reference_no` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'DR / Invoice / Memo No.',
  `party` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Supplier (IN) or Reason (OUT)',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Draft','Posted','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft',
  `created_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posted_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_txn_no` (`txn_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transactions`
--

LOCK TABLES `stock_transactions` WRITE;
/*!40000 ALTER TABLE `stock_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','cashier','barista','customer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `affiliation` enum('student','faculty','visitor','pwd_senior') COLLATE utf8mb4_unicode_ci DEFAULT 'student',
  `id_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Student or Employee ID Number',
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Supervisor','admin@isatu.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','faculty','ISATU-ADMIN-001','0917-111-2222',NULL,'2026-10-18 16:37:00','2026-10-18 16:37:00'),(2,'Staff Bea Ramos','cashier.bea@isatu.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','cashier','student','ISATU-STU-2022-049','0918-333-4444',NULL,'2026-10-18 16:37:00','2026-10-18 16:37:00'),(3,'Barista Jason Cole','barista.jason@isatu.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','barista','student','ISATU-STU-2021-118','0919-555-6666',NULL,'2026-10-18 16:37:00','2026-10-18 16:37:00'),(4,'Engr. Mark Villanueva','mark.villanueva@isatu.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','customer','faculty','ISATU-FAC-2018-091','0917-123-4567',NULL,'2026-10-18 16:37:00','2026-10-18 16:37:00'),(5,'Maria Santos','staff.maria@isatu.edu.ph','$2y$10$MxfsVVTq6wjH/kS.9lceuOwZQZ3SmylLRWcvJYNtowYRBmC/D4NVq','cashier','student','ISATU-STU-2023-012','0917-888-9999',NULL,'2026-10-22 23:04:42','2026-10-22 23:04:42');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zones`
--

DROP TABLE IF EXISTS `zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bi-grid',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `badge_color` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'primary',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zones`
--

LOCK TABLES `zones` WRITE;
/*!40000 ALTER TABLE `zones` DISABLE KEYS */;
INSERT INTO `zones` VALUES (1,'Study Hub','study-hub','bi-laptop','Individual sound-dampened quiet pods with dedicated AC outlets and high-speed Wi-Fi.','primary',1,'2026-10-23 00:02:02','2026-10-23 00:02:02'),(2,'Lounge','lounge','bi-cup-straw','Plush leather sofas and coffee tables suited for relaxed discussions.','warning',1,'2026-10-23 00:02:02','2026-10-23 00:02:02'),(3,'Main Hall','main-hall','bi-people','Spacious acacia dining tables for student study groups and faculty dining.','info',1,'2026-10-23 00:02:02','2026-10-23 00:02:02'),(4,'Terrace','terrace','bi-sun','Shaded al fresco seating overlooking campus grounds and greenery.','success',1,'2026-10-23 00:02:02','2026-10-23 00:02:02');
/*!40000 ALTER TABLE `zones` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-26  8:11:30
