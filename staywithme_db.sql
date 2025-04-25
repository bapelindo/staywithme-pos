-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               11.7.2-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for staywithme_db
CREATE DATABASE IF NOT EXISTS `staywithme_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `staywithme_db`;

-- Dumping structure for table staywithme_db.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Nama kategori (e.g., Makanan, Minuman, Snack)',
  `description` text DEFAULT NULL COMMENT 'Deskripsi kategori',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Urutan tampil kategori',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_categories_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kategori untuk item menu';

-- Dumping data for table staywithme_db.categories: ~1 rows (approximately)
INSERT INTO `categories` (`id`, `name`, `description`, `sort_order`, `created_at`, `updated_at`) VALUES
	(1, 'Ice Drink', 'Minuman Dingin', 1, '2025-04-24 22:05:17', '2025-04-24 22:05:17');

-- Dumping structure for table staywithme_db.menu_items
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL COMMENT 'Relasi ke tabel categories',
  `name` varchar(150) NOT NULL COMMENT 'Nama item menu',
  `description` text DEFAULT NULL COMMENT 'Deskripsi item menu',
  `price` decimal(12,2) NOT NULL COMMENT 'Harga item menu (gunakan DECIMAL untuk uang)',
  `image_path` varchar(255) DEFAULT NULL COMMENT 'Path ke file gambar item menu',
  `is_available` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status ketersediaan item (1=Tersedia, 0=Habis)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_menu_items_categories_idx` (`category_id`),
  KEY `idx_menu_items_name` (`name`),
  CONSTRAINT `fk_menu_items_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detail item menu yang dijual';

-- Dumping data for table staywithme_db.menu_items: ~1 rows (approximately)
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_available`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Dark Chocolate Ice', 'Dark Chocolate yang manis', 15000.00, 'assets/uploads/menu/menu_680ab5e714fcb8.22453703.jpeg', 1, '2025-04-24 22:06:31', '2025-04-24 22:06:31');

-- Dumping structure for table staywithme_db.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_id` int(10) unsigned NOT NULL COMMENT 'Relasi ke meja tempat pesanan dibuat',
  `order_number` varchar(100) NOT NULL COMMENT 'Nomor unik pesanan (e.g., STW-YYYYMMDD-NNN)',
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total harga pesanan (dihitung dari order_items)',
  `status` enum('pending','received','preparing','ready','served','paid','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Status progres pesanan',
  `notes` text DEFAULT NULL COMMENT 'Catatan tambahan dari pelanggan untuk keseluruhan pesanan',
  `order_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Waktu pesanan dibuat',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `fk_orders_tables_idx` (`table_id`),
  KEY `idx_orders_order_number` (`order_number`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_order_time` (`order_time`),
  CONSTRAINT `fk_orders_tables` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menyimpan data header pesanan pelanggan';

-- Dumping data for table staywithme_db.orders: ~0 rows (approximately)

-- Dumping structure for table staywithme_db.order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL COMMENT 'Relasi ke tabel orders',
  `menu_item_id` int(10) unsigned NOT NULL COMMENT 'Relasi ke tabel menu_items',
  `quantity` int(10) unsigned NOT NULL DEFAULT 1 COMMENT 'Jumlah item yang dipesan',
  `price_at_order` decimal(12,2) NOT NULL COMMENT 'Harga item pada saat dipesan (antisipasi perubahan harga menu)',
  `subtotal` decimal(15,2) NOT NULL COMMENT 'Total harga untuk item ini (quantity * price_at_order)',
  `notes` varchar(255) DEFAULT NULL COMMENT 'Catatan spesifik untuk item ini (e.g., tanpa gula)',
  PRIMARY KEY (`id`),
  KEY `fk_order_items_orders_idx` (`order_id`),
  KEY `fk_order_items_menu_items_idx` (`menu_item_id`),
  CONSTRAINT `fk_order_items_menu_items` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menyimpan detail item per pesanan';

-- Dumping data for table staywithme_db.order_items: ~0 rows (approximately)

-- Dumping structure for table staywithme_db.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL COMMENT 'Relasi ke order yang dibayar (anggap 1 pembayaran per order)',
  `payment_method` enum('cash','qris','card','transfer') NOT NULL DEFAULT 'cash' COMMENT 'Metode pembayaran',
  `amount_paid` decimal(15,2) NOT NULL COMMENT 'Jumlah yang dibayar',
  `payment_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Waktu pembayaran',
  `processed_by_user_id` int(10) unsigned DEFAULT NULL COMMENT 'Staff (user) yang memproses pembayaran',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `fk_payments_orders_idx` (`order_id`),
  KEY `fk_payments_users_idx` (`processed_by_user_id`),
  CONSTRAINT `fk_payments_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_users` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mencatat detail pembayaran';

-- Dumping data for table staywithme_db.payments: ~0 rows (approximately)

-- Dumping structure for table staywithme_db.tables
CREATE TABLE IF NOT EXISTS `tables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table_number` varchar(50) NOT NULL COMMENT 'Nomor atau Nama Meja (e.g., T01, T02, VIP 1)',
  `qr_code_identifier` varchar(100) NOT NULL COMMENT 'Identifier unik untuk URL QR Code (bisa UUID atau random string)',
  `description` varchar(255) DEFAULT NULL COMMENT 'Deskripsi tambahan meja',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Apakah meja ini aktif digunakan',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_number` (`table_number`),
  UNIQUE KEY `qr_code_identifier` (`qr_code_identifier`),
  KEY `idx_tables_table_number` (`table_number`),
  KEY `idx_tables_qr_code_identifier` (`qr_code_identifier`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menyimpan data meja fisik di cafe';

-- Dumping data for table staywithme_db.tables: ~1 rows (approximately)
INSERT INTO `tables` (`id`, `table_number`, `qr_code_identifier`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'VIP 1', 'vip-1-e653293a', 'Samping Tangga', 1, '2025-04-24 22:04:41', '2025-04-24 22:04:41');

-- Dumping structure for table staywithme_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT 'Username untuk login',
  `password` varchar(255) NOT NULL COMMENT 'HARUS disimpan dalam bentuk hash!',
  `name` varchar(100) NOT NULL COMMENT 'Nama lengkap pengguna',
  `role` enum('admin','staff','kitchen') NOT NULL DEFAULT 'staff' COMMENT 'Peran pengguna (admin, staff kasir, staff dapur)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status aktif pengguna',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_users_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menyimpan data pengguna sistem (admin, staff)';

-- Dumping data for table staywithme_db.users: ~1 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'admin', '$2y$12$mg.bv19q4Gxp07mx0HMTmOoR8/oaqavyqEnRemFdAv/JG2n1UCSmy', 'Administrator Utama', 'admin', 1, '2025-04-23 06:49:00', '2025-04-23 06:51:27');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
