<?php
// File: app/routes.php

use App\Helpers\UrlHelper;

/**
 * File Definisi Rute Aplikasi Stay With Me POS
 *
 * @var App\Core\Router $router Instance router yang diinisialisasi di public/index.php
 */

// Pastikan variabel $router sudah ada (di-include dari index.php)
if (!isset($router) || !($router instanceof App\Core\Router)) {
    die('Error: Router instance not available in routes.php');
}

// =============================================
// == Rute Publik (Customer Facing) ==
// =============================================

// Halaman Utama
$router->addRoute('GET', '/', 'Public\\HomeController@index');

// Menu Digital (via QR Code)
$router->addRoute('GET', '/menu/table/{qr_identifier}', 'Public\\MenuController@show');

// Proses Pemesanan dan Status (Customer)
$router->addRoute('POST', '/order/place', 'Public\\OrderController@placeOrder');         // Endpoint AJAX untuk submit pesanan
$router->addRoute('GET', '/order/status/{order_id}', 'Public\\OrderController@showStatus');  // Halaman lihat status pesanan
$router->addRoute('GET', '/order/get_status/{order_id}', 'Public\\OrderController@getStatusUpdate'); // Endpoint AJAX polling status

// Customer Display System (CDS)
$router->addRoute('GET', '/cds', 'Public\\CdsController@index');               // Halaman view CDS
$router->addRoute('GET', '/cds/get_orders', 'Public\\CdsController@getOrders');       // Endpoint AJAX polling CDS

// =============================================
// == Rute Otentikasi Admin ==
// =============================================
$router->addRoute('GET', '/admin', 'Admin\\AuthController@redirectToLogin');
// Halaman utama admin (redirect ke login jika belum login)
$router->addRoute('GET', '/admin/login', 'Admin\\AuthController@showLoginForm'); // Tampilkan form login
$router->addRoute('POST', '/admin/login', 'Admin\\AuthController@login');       // Proses login
$router->addRoute('GET', '/admin/logout', 'Admin\\AuthController@logout');      // Proses logout (Idealnya POST)

// =============================================
// == Rute Panel Admin (Memerlukan Login) ==
// =============================================

// Dashboard
$router->addRoute('GET', '/admin/dashboard', 'Admin\\DashboardController@index');

// Manajemen Pesanan (Orders)
$router->addRoute('GET', '/admin/orders', 'Admin\\OrderController@index');          // Daftar pesanan (bisa filter by status via query string ?status=...)
$router->addRoute('GET', '/admin/orders/show/{order_id}', 'Admin\\OrderController@show');   // Lihat detail pesanan
$router->addRoute('GET', '/admin/orders/new', 'Admin\\OrderController@getNewOrders');    // Endpoint AJAX polling notifikasi pesanan baru
$router->addRoute('POST', '/admin/orders/update_status', 'Admin\\OrderController@updateStatus'); // Endpoint AJAX update status (umum/dari detail)
$router->addRoute('POST', '/admin/orders/pay_cash/{order_id}', 'Admin\\OrderController@processCashPayment'); // Proses pembayaran cash
$router->addRoute('GET', '/admin/orders/invoice/{order_id}', 'Admin\\OrderController@invoice'); // Lihat/cetak invoice HTML

// Kitchen Display System (KDS)
$router->addRoute('GET', '/admin/kds', 'Admin\\KdsController@index');               // Halaman view KDS
$router->addRoute('GET', '/admin/kds/get_orders', 'Admin\\KdsController@getOrders'); // Endpoint AJAX polling KDS
$router->addRoute('POST', '/admin/kds/update_status', 'Admin\\KdsController@updateOrderStatus'); // Endpoint AJAX update status dari KDS

// Manajemen Menu & Kategori
$router->addRoute('GET', '/admin/menu', 'Admin\\MenuController@index');             // Daftar item menu
$router->addRoute('GET', '/admin/menu/create', 'Admin\\MenuController@create');       // Tampilkan form tambah item menu
$router->addRoute('POST', '/admin/menu/store', 'Admin\\MenuController@store');        // Simpan item menu baru
$router->addRoute('GET', '/admin/menu/edit/{id}', 'Admin\\MenuController@edit');         // Tampilkan form edit item menu
$router->addRoute('POST', '/admin/menu/update/{id}', 'Admin\\MenuController@update');      // Proses update item menu
$router->addRoute('POST', '/admin/menu/destroy/{id}', 'Admin\\MenuController@destroy');     // Hapus item menu
$router->addRoute('POST', '/admin/menu/toggle_availability/{id}', 'Admin\\MenuController@toggleAvailability'); // Endpoint AJAX toggle ketersediaan

// Rute Kategori (ditangani oleh MenuController)
$router->addRoute('GET', '/admin/categories', 'Admin\\MenuController@categories');       // Halaman Kelola Kategori (List & Form Tambah)
$router->addRoute('POST', '/admin/categories/store', 'Admin\\MenuController@storeCategory');  // Simpan kategori baru
$router->addRoute('POST', '/admin/categories/update/{id}', 'Admin\\MenuController@updateCategory'); // Proses update kategori (dari form inline)
$router->addRoute('POST', '/admin/categories/destroy/{id}', 'Admin\\MenuController@destroyCategory'); // Hapus kategori

// Manajemen Meja (Tables)
$router->addRoute('GET', '/admin/tables', 'Admin\\TableController@index');           // Daftar meja
$router->addRoute('GET', '/admin/tables/create', 'Admin\\TableController@create');     // Tampilkan form tambah meja
$router->addRoute('POST', '/admin/tables/store', 'Admin\\TableController@store');      // Simpan meja baru
$router->addRoute('GET', '/admin/tables/edit/{id}', 'Admin\\TableController@edit');       // Tampilkan form edit meja
$router->addRoute('POST', '/admin/tables/update/{id}', 'Admin\\TableController@update');    // Proses update meja
$router->addRoute('POST', '/admin/tables/destroy/{id}', 'Admin\\TableController@destroy');   // Hapus meja
$router->addRoute('GET', '/admin/tables/qr/{id}', 'Admin\\TableController@generateQr'); // Generate/tampilkan QR code untuk meja

// Manajemen Pengguna (Users) - Akses Admin
$router->addRoute('GET', '/admin/users', 'Admin\\UserController@index');           // Daftar pengguna
$router->addRoute('GET', '/admin/users/create', 'Admin\\UserController@create');     // Tampilkan form tambah pengguna
$router->addRoute('POST', '/admin/users/store', 'Admin\\UserController@store');      // Simpan pengguna baru
$router->addRoute('GET', '/admin/users/edit/{id}', 'Admin\\UserController@edit');       // Tampilkan form edit pengguna
$router->addRoute('POST', '/admin/users/update/{id}', 'Admin\\UserController@update');    // Proses update pengguna
$router->addRoute('POST', '/admin/users/destroy/{id}', 'Admin\\UserController@destroy');   // Hapus pengguna

// Laporan (Reports)
$router->addRoute('GET', '/admin/reports', 'Admin\\ReportController@index');         // Halaman utama laporan (bisa filter via query string ?start_date=...&end_date=...)
$router->addRoute('GET', '/admin/reports/summary', 'Admin\\ReportController@summary');
// Rute Pengaturan (jika ditambahkan nanti)
// $router->addRoute('GET', '/admin/settings', 'Admin\\SettingsController@index');
// $router->addRoute('POST', '/admin/settings/update', 'Admin\\SettingsController@update');


// =============================================
// == Penanganan 404 Not Found ==
// =============================================
// Router di Core/Router.php akan secara otomatis menangani kasus jika tidak ada rute
// yang cocok dengan menampilkan pesan 404 atau view error 404 jika ada.

?>