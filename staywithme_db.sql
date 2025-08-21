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

-- Additional categories
INSERT INTO `categories` (`name`, `description`, `sort_order`) VALUES
('Hot Drink', 'Minuman Panas', 2),
('Main Course', 'Menu Utama', 3),
('Snack', 'Makanan Ringan', 4),
('Dessert', 'Makanan Penutup', 5);

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detail item menu yang dijual';

-- Dumping data for table staywithme_db.menu_items: ~1 rows (approximately)
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `cost`, `image_path`, `is_available`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Dark Chocolate Ice', 'Dark Chocolate yang manis', 15000.00, 7000.00, 'assets/uploads/menu/menu_680ab5e714fcb8.22453703.jpeg', 1, '2025-04-24 22:06:31', '2025-04-24 22:06:31');

-- Additional menu items
INSERT INTO `menu_items` (`category_id`, `name`, `description`, `price`, `cost`, `is_available`) VALUES
(1, 'Matcha Ice', 'Es matcha premium', 18000.00, 8000.00, 1),
(1, 'Taro Ice', 'Es taro creamy', 16000.00, 7500.00, 1),
(2, 'Hot Cappuccino', 'Kopi cappuccino panas', 20000.00, 9000.00, 1),
(2, 'Hot Chocolate', 'Coklat panas premium', 18000.00, 8000.00, 1),
(3, 'Nasi Goreng Spesial', 'Nasi goreng dengan telur dan ayam', 25000.00, 12000.00, 1),
(3, 'Mie Goreng', 'Mie goreng dengan telur', 22000.00, 10000.00, 1),
(4, 'French Fries', 'Kentang goreng crispy', 15000.00, 6000.00, 1),
(4, 'Chicken Wings', 'Sayap ayam goreng', 25000.00, 12000.00, 1),
(5, 'Ice Cream', 'Es krim vanilla/coklat/strawberry', 12000.00, 5000.00, 1),
(5, 'Pudding', 'Pudding susu', 10000.00, 4000.00, 1);

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
  `shipping_cost` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Ongkos kirim',
  `service_charge` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya layanan',
  `mdr_service_fee` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya layanan MDR',
  `rounding` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Pembulatan',
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Pajak',
  `other_revenue` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Pendapatan lainnya',
  `purchase_promo` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Promo pembelian',
  `product_promo` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Promo produk',
  `complimentary` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Komplimen',
  `admin_fee` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya administrasi',
  `refunds` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Pengembalian',
  `mdr_fee` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya MDR',
  `commission` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Komisi',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `fk_orders_tables_idx` (`table_id`),
  KEY `idx_orders_order_number` (`order_number`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_order_time` (`order_time`),
  CONSTRAINT `fk_orders_tables` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menyimpan data header pesanan pelanggan';

-- Dumping data for table staywithme_db.orders: ~0 rows (approximately)

-- Orders for the past week with various statuses and financial details
INSERT INTO `orders` (
    `table_id`, `order_number`, `total_amount`, `status`, 
    `shipping_cost`, `service_charge`, `mdr_service_fee`, `tax`, 
    `purchase_promo`, `product_promo`, `admin_fee`, `commission`, `created_at`
) VALUES
-- Today
(1, 'STW-20250424-001', 75000.00, 'paid', 0, 7500, 1500, 7500, 5000, 0, 1000, 2000, CURRENT_DATE),
(2, 'STW-20250424-002', 120000.00, 'paid', 0, 12000, 2400, 12000, 10000, 5000, 1500, 3000, CURRENT_DATE),
(3, 'STW-20250424-003', 45000.00, 'preparing', 0, 4500, 900, 4500, 0, 0, 1000, 1500, CURRENT_DATE),
-- Yesterday
(1, 'STW-20250423-001', 95000.00, 'paid', 0, 9500, 1900, 9500, 7500, 2500, 1200, 2500, DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)),
(2, 'STW-20250423-002', 150000.00, 'paid', 0, 15000, 3000, 15000, 12500, 7500, 2000, 4000, DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)),
-- 2 days ago
(3, 'STW-20250422-001', 85000.00, 'paid', 0, 8500, 1700, 8500, 5000, 2500, 1000, 2000, DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY)),
(4, 'STW-20250422-002', 130000.00, 'paid', 0, 13000, 2600, 13000, 10000, 5000, 1500, 3500, DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY)),
-- 3-7 days ago
(5, 'STW-20250421-001', 100000.00, 'paid', 0, 10000, 2000, 10000, 7500, 2500, 1200, 2500, DATE_SUB(CURRENT_DATE, INTERVAL 3 DAY)),
(1, 'STW-20250420-001', 110000.00, 'paid', 0, 11000, 2200, 11000, 8000, 3000, 1300, 3000, DATE_SUB(CURRENT_DATE, INTERVAL 4 DAY)),
(2, 'STW-20250419-001', 90000.00, 'paid', 0, 9000, 1800, 9000, 6000, 2000, 1100, 2200, DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY)),
(3, 'STW-20250418-001', 140000.00, 'paid', 0, 14000, 2800, 14000, 10000, 5000, 1500, 3500, DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY)),
(4, 'STW-20250417-001', 80000.00, 'paid', 0, 8000, 1600, 8000, 5000, 2000, 1000, 2000, DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY));

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

-- Order items
INSERT INTO `order_items` (`order_id`, `menu_item_id`, `quantity`, `price_at_order`, `subtotal`) 
SELECT 
    o.id,
    FLOOR(1 + RAND() * 10) as menu_item_id,
    FLOOR(1 + RAND() * 5) as quantity,
    m.price as price_at_order,
    FLOOR(1 + RAND() * 5) * m.price as subtotal
FROM orders o
CROSS JOIN menu_items m
WHERE o.id <= 12
LIMIT 24;

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

-- Additional tables
INSERT INTO `tables` (`table_number`, `qr_code_identifier`, `description`, `is_active`) VALUES
('T01', 't01-abc123', 'Meja Indoor 1', 1),
('T02', 't02-def456', 'Meja Indoor 2', 1),
('T03', 't03-ghi789', 'Meja Indoor 3', 1),
('T04', 't04-jkl012', 'Meja Outdoor 1', 1),
('T05', 't05-mno345', 'Meja Outdoor 2', 1);

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

-- Additional users
INSERT INTO `users` (`username`, `password`, `name`, `role`, `is_active`) VALUES
('staff1', '$2y$12$mg.bv19q4Gxp07mx0HMTmOoR8/oaqavyqEnRemFdAv/JG2n1UCSmy', 'Kasir 1', 'staff', 1),
('staff2', '$2y$12$mg.bv19q4Gxp07mx0HMTmOoR8/oaqavyqEnRemFdAv/JG2n1UCSmy', 'Kasir 2', 'staff', 1),
('kitchen1', '$2y$12$mg.bv19q4Gxp07mx0HMTmOoR8/oaqavyqEnRemFdAv/JG2n1UCSmy', 'Dapur 1', 'kitchen', 1),
('kitchen2', '$2y$12$mg.bv19q4Gxp07mx0HMTmOoR8/oaqavyqEnRemFdAv/JG2n1UCSmy', 'Dapur 2', 'kitchen', 1);

-- Dumping data for sample orders
INSERT INTO `orders` (`id`, `table_id`, `order_number`, `total_amount`, `status`, `notes`, `shipping_cost`, `service_charge`, `mdr_service_fee`, `rounding`, `tax`, `other_revenue`, `purchase_promo`, `product_promo`, `complimentary`, `admin_fee`, `refunds`, `mdr_fee`, `commission`) VALUES
(1, 1, 'STW-20250424-001', 45000.00, 'paid', NULL, 5000.00, 2000.00, 1000.00, 0.00, 4500.00, 0.00, 5000.00, 0.00, 0.00, 1000.00, 0.00, 500.00, 2000.00),
(2, 1, 'STW-20250424-002', 75000.00, 'paid', NULL, 5000.00, 3000.00, 1500.00, 0.00, 7500.00, 1000.00, 10000.00, 5000.00, 0.00, 1500.00, 0.00, 750.00, 3000.00),
(3, 1, 'STW-20250424-003', 30000.00, 'paid', NULL, 5000.00, 1500.00, 750.00, 0.00, 3000.00, 0.00, 0.00, 0.00, 0.00, 1000.00, 0.00, 300.00, 1500.00);

-- Dumping data for sample order items
INSERT INTO `order_items` (`order_id`, `menu_item_id`, `quantity`, `price_at_order`, `subtotal`, `notes`) VALUES
(1, 1, 3, 15000.00, 45000.00, NULL),
(2, 1, 5, 15000.00, 75000.00, NULL),
(3, 1, 2, 15000.00, 30000.00, NULL);

-- Dumping data for sample payments
INSERT INTO `payments` (`order_id`, `payment_method`, `amount_paid`, `processed_by_user_id`) VALUES
(1, 'cash', 45000.00, 1),
(2, 'qris', 75000.00, 1),
(3, 'card', 30000.00, 1);

-- Sample data for orders (last 7 days)
INSERT INTO `orders` (`id`, `table_id`, `order_number`, `total_amount`, `status`, `shipping_cost`, `service_charge`, `tax`, `created_at`) VALUES
(1, 1, 'STW-20250424-001', 45000.00, 'paid', 0, 4500, 4950, DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY)),
(2, 1, 'STW-20250424-002', 75000.00, 'paid', 0, 7500, 8250, DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY)),
(3, 1, 'STW-20250424-003', 60000.00, 'paid', 0, 6000, 6600, DATE_SUB(CURRENT_DATE, INTERVAL 4 DAY)),
(4, 1, 'STW-20250424-004', 90000.00, 'paid', 0, 9000, 9900, DATE_SUB(CURRENT_DATE, INTERVAL 3 DAY)),
(5, 1, 'STW-20250424-005', 30000.00, 'paid', 0, 3000, 3300, DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY)),
(6, 1, 'STW-20250424-006', 120000.00, 'paid', 0, 12000, 13200, DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)),
(7, 1, 'STW-20250424-007', 45000.00, 'paid', 0, 4500, 4950, CURRENT_DATE);

-- Sample data for order_items
INSERT INTO `order_items` (`order_id`, `menu_item_id`, `quantity`, `price_at_order`, `subtotal`) VALUES
(1, 1, 3, 15000.00, 45000.00),
(2, 1, 5, 15000.00, 75000.00),
(3, 1, 4, 15000.00, 60000.00),
(4, 1, 6, 15000.00, 90000.00),
(5, 1, 2, 15000.00, 30000.00),
(6, 1, 8, 15000.00, 120000.00),
(7, 1, 3, 15000.00, 45000.00);

-- Sample data for payments with different methods
INSERT INTO `payments` (`order_id`, `payment_method`, `amount_paid`, `processed_by_user_id`) VALUES
(1, 'cash', 45000.00, 1),
(2, 'qris', 75000.00, 1),
(3, 'card', 60000.00, 1),
(4, 'transfer', 90000.00, 1),
(5, 'cash', 30000.00, 1),
(6, 'qris', 120000.00, 1),
(7, 'card', 45000.00, 1);

-- Add more sample orders with varied financial details
INSERT INTO `orders` (
    `table_id`, `order_number`, `total_amount`, `status`,
    `shipping_cost`, `service_charge`, `mdr_service_fee`, `tax`,
    `purchase_promo`, `product_promo`, `complimentary`, `admin_fee`,
    `refunds`, `mdr_fee`, `commission`, `created_at`
) VALUES
-- Today's orders
(1, 'STW-20250424-008', 185000.00, 'paid', 0, 18500, 3700, 18500, 15000, 5000, 0, 2000, 0, 1850, 4625, CURRENT_DATE),
(2, 'STW-20250424-009', 225000.00, 'paid', 0, 22500, 4500, 22500, 20000, 7500, 0, 2500, 0, 2250, 5625, CURRENT_DATE),
(3, 'STW-20250424-010', 145000.00, 'paid', 0, 14500, 2900, 14500, 10000, 3000, 0, 1500, 0, 1450, 3625, CURRENT_DATE),

-- Yesterday's orders
(1, 'STW-20250423-003', 195000.00, 'paid', 0, 19500, 3900, 19500, 15000, 5000, 0, 2000, 0, 1950, 4875, DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)),
(2, 'STW-20250423-004', 235000.00, 'paid', 0, 23500, 4700, 23500, 20000, 7500, 0, 2500, 0, 2350, 5875, DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)),

-- Two days ago
(3, 'STW-20250422-003', 165000.00, 'paid', 0, 16500, 3300, 16500, 12000, 4000, 0, 1800, 0, 1650, 4125, DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY));

-- Add corresponding order items
INSERT INTO `order_items` (`order_id`, `menu_item_id`, `quantity`, `price_at_order`, `subtotal`) VALUES
-- Today's items
(13, 3, 4, 18000.00, 72000.00),
(13, 5, 3, 25000.00, 75000.00),
(13, 8, 2, 25000.00, 50000.00),
(14, 6, 5, 22000.00, 110000.00),
(14, 9, 3, 12000.00, 36000.00),
(14, 4, 4, 18000.00, 72000.00),
(15, 2, 3, 16000.00, 48000.00),
(15, 7, 4, 15000.00, 60000.00),

-- Yesterday's items
(16, 1, 5, 15000.00, 75000.00),
(16, 4, 3, 18000.00, 54000.00),
(16, 8, 3, 25000.00, 75000.00),
(17, 3, 4, 18000.00, 72000.00),
(17, 6, 5, 22000.00, 110000.00),
(17, 10, 4, 10000.00, 40000.00),

-- Two days ago items
(18, 2, 4, 16000.00, 64000.00),
(18, 5, 2, 25000.00, 50000.00),
(18, 7, 3, 15000.00, 45000.00);

-- Add corresponding payments
INSERT INTO `payments` (`order_id`, `payment_method`, `amount_paid`, `processed_by_user_id`, `payment_time`) VALUES
(13, 'qris', 185000.00, 1, CURRENT_TIMESTAMP),
(14, 'card', 225000.00, 2, CURRENT_TIMESTAMP),
(15, 'cash', 145000.00, 1, CURRENT_TIMESTAMP),
(16, 'transfer', 195000.00, 2, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)),
(17, 'qris', 235000.00, 1, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)),
(18, 'cash', 165000.00, 2, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 DAY));

-- Additional sample data for order variations
INSERT INTO `orders` (
    `table_id`, `order_number`, `total_amount`, `status`,
    `shipping_cost`, `service_charge`, `mdr_service_fee`, `tax`,
    `purchase_promo`, `product_promo`, `complimentary`, `admin_fee`,
    `refunds`, `mdr_fee`, `commission`, `created_at`
) VALUES
-- Orders with different scenarios
(4, 'STW-20250424-011', 95000.00, 'paid', 5000, 9500, 1900, 9500, 0, 0, 0, 1000, 0, 950, 2375, CURRENT_DATE),
(5, 'STW-20250424-012', 175000.00, 'paid', 0, 17500, 3500, 17500, 25000, 10000, 5000, 2000, 0, 1750, 4375, CURRENT_DATE),
(1, 'STW-20250424-013', 135000.00, 'paid', 0, 13500, 2700, 13500, 5000, 2500, 0, 1500, 15000, 1350, 3375, CURRENT_DATE);

-- Add order items for these variations
INSERT INTO `order_items` (`order_id`, `menu_item_id`, `quantity`, `price_at_order`, `subtotal`) VALUES
(19, 1, 2, 15000.00, 30000.00),
(19, 3, 2, 18000.00, 36000.00),
(19, 7, 2, 15000.00, 30000.00),
(20, 5, 3, 25000.00, 75000.00),
(20, 8, 2, 25000.00, 50000.00),
(20, 9, 4, 12000.00, 48000.00),
(21, 4, 3, 18000.00, 54000.00),
(21, 6, 3, 22000.00, 66000.00),
(21, 10, 2, 10000.00, 20000.00);

-- Add payments for these variations
INSERT INTO `payments` (`order_id`, `payment_method`, `amount_paid`, `processed_by_user_id`, `payment_time`) VALUES
(19, 'transfer', 95000.00, 1, CURRENT_TIMESTAMP),
(20, 'card', 175000.00, 2, CURRENT_TIMESTAMP),
(21, 'cash', 135000.00, 1, CURRENT_TIMESTAMP);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
