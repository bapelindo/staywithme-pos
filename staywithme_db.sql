-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               11.7.2-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.11.0.7065
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kategori untuk item menu';

-- Dumping data for table staywithme_db.categories: ~5 rows (approximately)
INSERT INTO `categories` (`id`, `name`, `description`, `sort_order`, `created_at`, `updated_at`) VALUES
	(1, 'Ice Drink', 'Minuman Dingin', 1, '2025-08-22 13:30:41', '2025-08-22 13:30:41'),
	(2, 'Hot Drink', 'Minuman Panas', 2, '2025-08-22 13:30:41', '2025-08-22 13:30:41'),
	(3, 'Main Course', 'Menu Utama', 3, '2025-08-22 13:30:41', '2025-08-22 13:30:41'),
	(4, 'Snack', 'Makanan Ringan', 4, '2025-08-22 13:30:41', '2025-08-22 13:30:41'),
	(5, 'Dessert', 'Makanan Penutup', 5, '2025-08-22 13:30:41', '2025-08-22 13:30:41');

-- Dumping structure for table staywithme_db.menu_items
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL COMMENT 'Relasi ke tabel categories',
  `name` varchar(150) NOT NULL COMMENT 'Nama item menu',
  `description` text DEFAULT NULL COMMENT 'Deskripsi item menu',
  `price` decimal(12,2) NOT NULL COMMENT 'Harga item menu (gunakan DECIMAL untuk uang)',
  `cost` decimal(12,2) DEFAULT 0.00 COMMENT 'Harga pokok penjualan (HPP) per item',
  `image_path` varchar(255) DEFAULT NULL COMMENT 'Path ke file gambar item menu',
  `is_available` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status ketersediaan item (1=Tersedia, 0=Habis)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_menu_items_categories_idx` (`category_id`),
  KEY `idx_menu_items_name` (`name`),
  CONSTRAINT `fk_menu_items_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detail item menu yang dijual';

-- Dumping data for table staywithme_db.menu_items: ~29 rows (approximately)
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `cost`, `image_path`, `is_available`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Es Kopi Susu Gula Aren', 'Kopi susu dengan manisnya gula aren asli.', 18000.00, 8000.00, NULL, 0, '2025-08-22 13:32:29', '2025-08-22 14:12:58'),
	(2, 1, 'Ice Americano', 'Espresso shot disajikan dingin dengan air mineral.', 15000.00, 5000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:48:14'),
	(3, 1, 'Ice Matcha Latte', 'Bubuk matcha premium dengan susu segar.', 22000.00, 10000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:48:16'),
	(4, 1, 'Ice Red Velvet', 'Rasa kue red velvet dalam segelas minuman dingin.', 20000.00, 9000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(5, 1, 'Lemon Tea Squash', 'Teh lemon dengan tambahan soda yang menyegarkan.', 16000.00, 6000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(6, 1, 'Virgin Mojito', 'Minuman soda dengan daun mint dan perasan jeruk nipis.', 19000.00, 8500.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(7, 2, 'Hot Cappuccino', 'Espresso, steamed milk, dan busa susu tebal.', 20000.00, 9000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(8, 2, 'Hot Caffe Latte', 'Espresso dengan steamed milk yang lebih banyak.', 20000.00, 9000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(9, 2, 'Hot Chocolate', 'Coklat premium yang dilelehkan dengan susu panas.', 18000.00, 8000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(10, 2, 'V60 Manual Brew', 'Kopi seduh manual dengan biji kopi pilihan hari ini.', 25000.00, 11000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(11, 2, 'Teh Jahe Hangat', 'Teh hangat dengan irisan jahe untuk menghangatkan tubuh.', 15000.00, 5000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(12, 3, 'Nasi Goreng Spesial', 'Nasi goreng dengan toping telur, sosis, dan ayam suwir.', 28000.00, 14000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(13, 3, 'Spaghetti Bolognese', 'Pasta spaghetti dengan saus daging sapi cincang khas Italia.', 35000.00, 16000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(14, 3, 'Chicken Katsu Curry', 'Nasi dengan kari Jepang dan potongan ayam katsu renyah.', 38000.00, 18000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(15, 3, 'Fish and Chips', 'Ikan dori goreng tepung dengan kentang goreng.', 36000.00, 17000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(16, 3, 'Sop Buntut', 'Sop buntut sapi dengan kuah bening kaya rempah.', 45000.00, 22000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(17, 3, 'Rawon Daging', 'Rawon khas Jawa Timur dengan daging sapi empuk.', 35000.00, 17000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(18, 4, 'Kentang Goreng Original', 'Kentang goreng renyah dengan garam.', 15000.00, 6000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(19, 4, 'Tahu Cabe Garam', 'Tahu krispi dengan bumbu bawang, cabe, dan garam.', 18000.00, 8000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(20, 4, 'Chicken Wings BBQ', 'Sayap ayam dengan saus barbekyu pedas manis.', 25000.00, 12000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(21, 4, 'Onion Rings', 'Bawang bombay goreng tepung renyah.', 17000.00, 7000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(22, 4, 'Cireng Rujak', 'Cireng kenyal disajikan dengan sambal rujak.', 16000.00, 7500.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(23, 4, 'Pisang Goreng Keju', 'Pisang goreng dengan taburan keju dan susu kental manis.', 20000.00, 9000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(24, 4, 'Dimsum Mentai', 'Dimsum ayam kukus dengan saus mentai bakar.', 24000.00, 11000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(25, 5, 'Chocolate Lava Cake', 'Kue coklat dengan lelehan coklat di dalamnya.', 25000.00, 12000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(26, 5, 'Cheesecake Slice', 'Sepotong kue keju lembut dengan saus blueberry.', 28000.00, 14000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(27, 5, 'Panna Cotta', 'Puding krim Italia dengan saus stroberi.', 22000.00, 10000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(28, 5, 'Affogato', 'Satu skup es krim vanila disiram dengan shot espresso panas.', 23000.00, 11000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(29, 5, 'Waffle Ice Cream', 'Waffle renyah dengan satu skup es krim dan saus coklat.', 26000.00, 13000.00, NULL, 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29');

-- Dumping structure for table staywithme_db.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_id` int(10) unsigned NOT NULL COMMENT 'Relasi ke meja tempat pesanan dibuat',
  `order_number` varchar(100) NOT NULL COMMENT 'Nomor unik pesanan (e.g., STW-YYYYMMDD-NNN)',
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total harga pesanan (dihitung dari order_items)',
  `status` varchar(25) NOT NULL DEFAULT 'pending_payment' COMMENT 'Status progres pesanan',
  `notes` text DEFAULT NULL COMMENT 'Catatan tambahan dari pelanggan untuk keseluruhan pesanan',
  `order_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Waktu pesanan dibuat',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `shipping_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(15,2) NOT NULL DEFAULT 0.00,
  `mdr_service_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `rounding` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `other_revenue` decimal(15,2) NOT NULL DEFAULT 0.00,
  `purchase_promo` decimal(15,2) NOT NULL DEFAULT 0.00,
  `product_promo` decimal(15,2) NOT NULL DEFAULT 0.00,
  `complimentary` decimal(15,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `refunds` decimal(15,2) NOT NULL DEFAULT 0.00,
  `mdr_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `commission` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `fk_orders_tables_idx` (`table_id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_order_time` (`order_time`),
  CONSTRAINT `fk_orders_tables` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menyimpan data header pesanan pelanggan';

-- Dumping data for table staywithme_db.orders: ~2 rows (approximately)
INSERT INTO `orders` (`id`, `table_id`, `order_number`, `total_amount`, `status`, `notes`, `order_time`, `created_at`, `updated_at`, `shipping_cost`, `service_charge`, `mdr_service_fee`, `rounding`, `tax`, `other_revenue`, `purchase_promo`, `product_promo`, `complimentary`, `admin_fee`, `refunds`, `mdr_fee`, `commission`) VALUES
	(7, 1, 'SWM-20250822-0001', 50000.00, 'served', NULL, '2025-08-22 15:31:52', '2025-08-22 15:31:52', '2025-08-22 15:32:14', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
	(8, 1, 'SWM-20250822-0002', 15000.00, 'served', NULL, '2025-08-22 15:32:27', '2025-08-22 15:32:27', '2025-08-22 15:32:48', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

-- Dumping structure for table staywithme_db.order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL COMMENT 'Relasi ke tabel orders',
  `menu_item_id` int(10) unsigned NOT NULL COMMENT 'Relasi ke tabel menu_items',
  `quantity` int(10) unsigned NOT NULL DEFAULT 1 COMMENT 'Jumlah item yang dipesan',
  `price_at_order` decimal(12,2) NOT NULL COMMENT 'Harga item pada saat dipesan',
  `subtotal` decimal(15,2) NOT NULL COMMENT 'Total harga (quantity * price_at_order)',
  `notes` varchar(255) DEFAULT NULL COMMENT 'Catatan spesifik untuk item ini',
  PRIMARY KEY (`id`),
  KEY `fk_order_items_orders_idx` (`order_id`),
  KEY `fk_order_items_menu_items_idx` (`menu_item_id`),
  CONSTRAINT `fk_order_items_menu_items` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menyimpan detail item per pesanan';

-- Dumping data for table staywithme_db.order_items: ~12 rows (approximately)
INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `quantity`, `price_at_order`, `subtotal`, `notes`) VALUES
	(1, 1, 1, 1, 18000.00, 18000.00, ''),
	(2, 1, 2, 1, 15000.00, 15000.00, ''),
	(3, 1, 3, 2, 22000.00, 44000.00, ''),
	(4, 2, 3, 1, 22000.00, 22000.00, ''),
	(5, 2, 4, 1, 20000.00, 20000.00, ''),
	(6, 3, 3, 1, 22000.00, 22000.00, ''),
	(7, 3, 5, 1, 16000.00, 16000.00, ''),
	(8, 3, 6, 1, 19000.00, 19000.00, ''),
	(9, 4, 7, 1, 20000.00, 20000.00, ''),
	(10, 4, 9, 1, 18000.00, 18000.00, ''),
	(13, 7, 10, 2, 25000.00, 50000.00, ''),
	(14, 8, 11, 1, 15000.00, 15000.00, '');

-- Dumping structure for table staywithme_db.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL COMMENT 'Relasi ke order yang dibayar',
  `payment_method` enum('cash','qris','card','transfer') NOT NULL DEFAULT 'cash' COMMENT 'Metode pembayaran',
  `amount_paid` decimal(15,2) NOT NULL COMMENT 'Jumlah yang dibayar',
  `payment_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Waktu pembayaran',
  `processed_by_user_id` int(10) unsigned DEFAULT NULL COMMENT 'Staff (user) yang memproses pembayaran',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `fk_payments_users` (`processed_by_user_id`),
  CONSTRAINT `fk_payments_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_users` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mencatat detail pembayaran';

-- Dumping data for table staywithme_db.payments: ~2 rows (approximately)
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount_paid`, `payment_time`, `processed_by_user_id`, `created_at`, `updated_at`) VALUES
	(4, 7, 'cash', 50000.00, '2025-08-22 15:32:07', 1, '2025-08-22 15:32:07', '2025-08-22 15:32:07'),
	(5, 8, 'cash', 15000.00, '2025-08-22 15:32:45', 1, '2025-08-22 15:32:45', '2025-08-22 15:32:45');

-- Dumping structure for table staywithme_db.settings
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table staywithme_db.settings: ~7 rows (approximately)
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
	('cogs_percentage', '40'),
	('default_admin_fee', '1000'),
	('default_commission_percentage', '0'),
	('default_mdr_fee_percentage', '0'),
	('default_promo_percentage', '0'),
	('service_charge_percentage', '5'),
	('tax_percentage', '11');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menyimpan data meja fisik di cafe';

-- Dumping data for table staywithme_db.tables: ~5 rows (approximately)
INSERT INTO `tables` (`id`, `table_number`, `qr_code_identifier`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'T01', 't01-a1b2c3d4', 'Dekat Jendela', 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(2, 'T02', 't02-e5f6g7h8', 'Area Indoor', 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(3, 'T03', 't03-i9j0k1l2', 'Area Indoor', 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(4, 'L01', 'l01-m3n4o5p6', 'Lesehan Outdoor', 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29'),
	(5, 'V01', 'v01-q7r8s9t0', 'VIP Room', 1, '2025-08-22 13:32:29', '2025-08-22 13:32:29');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menyimpan data pengguna sistem (admin, staff)';

-- Dumping data for table staywithme_db.users: ~1 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'admin', '$2y$12$mg.bv19q4Gxp07mx0HMTmOoR8/oaqavyqEnRemFdAv/JG2n1UCSmy', 'Administrator Utama', 'admin', 1, '2025-08-22 13:30:41', '2025-08-22 13:30:41');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;