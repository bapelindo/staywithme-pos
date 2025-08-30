<?php
use App\Helpers\UrlHelper;

/**
 * File Definisi Rute Aplikasi Stay With Me POS
 */

if (!isset($router) || !($router instanceof App\Core\Router)) {
    die('Error: Router instance not available in routes.php');
}

// ... Rute Publik ...
$router->addRoute('GET', '/', 'Public\\HomeController@index');
$router->addRoute('GET', '/menu/table/{qr_identifier}', 'Public\\MenuController@show');
$router->addRoute('POST', '/order/place', 'Public\\OrderController@placeOrder');
$router->addRoute('GET', '/order/status/{order_id}', 'Public\\OrderController@showStatus');
$router->addRoute('GET', '/order/get_status/{order_id}', 'Public\\OrderController@getStatusUpdate');
$router->addRoute('GET', '/cds', 'Public\\CdsController@index');
$router->addRoute('GET', '/cds/get_orders', 'Public\\CdsController@getOrders');

// ... Rute Otentikasi Admin ...
$router->addRoute('GET', '/admin', 'Admin\\AuthController@redirectToLogin');
$router->addRoute('GET', '/admin/login', 'Admin\\AuthController@showLoginForm');
$router->addRoute('POST', '/admin/login', 'Admin\\AuthController@login');
$router->addRoute('GET', '/admin/logout', 'Admin\\AuthController@logout');

// ... Rute Panel Admin ...
$router->addRoute('GET', '/admin/dashboard', 'Admin\\DashboardController@index');

// ... Rute Manajemen Pesanan (Orders) ...
$router->addRoute('GET', '/admin/orders', 'Admin\\OrderController@index');
$router->addRoute('GET', '/admin/orders/show/{order_id}', 'Admin\\OrderController@show');
$router->addRoute('GET', '/admin/orders/new', 'Admin\\OrderController@getNewOrders');
$router->addRoute('POST', '/admin/orders/update_status', 'Admin\\OrderController@updateStatus');
$router->addRoute('POST', '/admin/orders/pay_cash/{order_id}', 'Admin\\OrderController@processCashPayment');
$router->addRoute('GET', '/admin/orders/invoice/{order_id}', 'Admin\\OrderController@invoice');

// ... Rute KDS ...
$router->addRoute('GET', '/admin/kds', 'Admin\\KdsController@index');
$router->addRoute('GET', '/admin/kds/get_orders', 'Admin\\KdsController@getOrders');
$router->addRoute('POST', '/admin/kds/update_status', 'Admin\\KdsController@updateOrderStatus');

// ... Rute Manajemen Menu & Kategori ...
$router->addRoute('GET', '/admin/menu', 'Admin\\MenuController@index');
$router->addRoute('GET', '/admin/menu/create', 'Admin\\MenuController@create');
$router->addRoute('POST', '/admin/menu/store', 'Admin\\MenuController@store');
$router->addRoute('GET', '/admin/menu/edit/{id}', 'Admin\\MenuController@edit');
$router->addRoute('POST', '/admin/menu/update/{id}', 'Admin\\MenuController@update');
$router->addRoute('POST', '/admin/menu/destroy/{id}', 'Admin\\MenuController@destroy');
$router->addRoute('POST', '/admin/menu/toggle_availability/{id}', 'Admin\\MenuController@toggleAvailability');
$router->addRoute('GET', '/admin/categories', 'Admin\\MenuController@categories');
$router->addRoute('POST', '/admin/categories/store', 'Admin\\MenuController@storeCategory');
$router->addRoute('POST', '/admin/categories/update/{id}', 'Admin\\MenuController@updateCategory');
$router->addRoute('POST', '/admin/categories/destroy/{id}', 'Admin\\MenuController@destroyCategory');

// ... Rute Manajemen Meja (Tables) ...
$router->addRoute('GET', '/admin/tables', 'Admin\\TableController@index');
$router->addRoute('GET', '/admin/tables/create', 'Admin\\TableController@create');
$router->addRoute('POST', '/admin/tables/store', 'Admin\\TableController@store');
$router->addRoute('GET', '/admin/tables/edit/{id}', 'Admin\\TableController@edit');
$router->addRoute('POST', '/admin/tables/update/{id}', 'Admin\\TableController@update');
$router->addRoute('POST', '/admin/tables/destroy/{id}', 'Admin\\TableController@destroy');
$router->addRoute('GET', '/admin/tables/qr/{id}', 'Admin\\TableController@generateQr');

// ... Rute Manajemen Pengguna (Users) ...
$router->addRoute('GET', '/admin/users', 'Admin\\UserController@index');
$router->addRoute('GET', '/admin/users/create', 'Admin\\UserController@create');
$router->addRoute('POST', '/admin/users/store', 'Admin\\UserController@store');
$router->addRoute('GET', '/admin/users/edit/{id}', 'Admin\\UserController@edit');
$router->addRoute('POST', '/admin/users/update/{id}', 'Admin\\UserController@update');
$router->addRoute('POST', '/admin/users/destroy/{id}', 'Admin\\UserController@destroy');

// Laporan (Reports)
$router->addRoute('GET', '/admin/reports', 'Admin\\ReportController@index');
$router->addRoute('GET', '/admin/reports/summary', 'Admin\\ReportController@summary');
$router->addRoute('GET', '/admin/reports/financials', 'Admin\\ReportController@financials');
$router->addRoute('GET', '/admin/reports/sales-detail', 'Admin\\ReportController@salesDetail');
$router->addRoute('GET', '/admin/reports/summary/export', 'Admin\\ReportController@exportSummary');
$router->addRoute('GET', '/admin/reports/product-sales', 'Admin\\ReportController@productSales');
$router->addRoute('GET', '/admin/reports/category-sales', 'Admin\\ReportController@productByCategory');
$router->addRoute('GET', '/admin/reports/cash-summary', 'Admin\\ReportController@cashSummary');
// **TAMBAHKAN RUTE BARU INI**
$router->addRoute('GET', '/admin/reports/profit-loss', 'Admin\\ReportController@profitAndLoss');

// ... Rute Pengaturan ...
$router->addRoute('GET', '/admin/settings', 'Admin\\SettingsController@index');
$router->addRoute('POST', '/admin/settings/update', 'Admin\\SettingsController@update');

// ... Rute Cashier ...
$router->addRoute('GET', '/admin/cashier', 'Admin\\CashierController@index');
$router->addRoute('POST', '/admin/cashier/open', 'Admin\\CashierController@open');
$router->addRoute('POST', '/admin/cashier/close', 'Admin\\CashierController@close');
$router->addRoute('POST', '/admin/cashier/cash_in', 'Admin\\CashierController@cashIn');
$router->addRoute('POST', '/admin/cashier/cash_out', 'Admin\\CashierController@cashOut');

// Rute Laporan Tutup Kasir
$router->addRoute('GET', '/admin/reports/closing', 'Admin\\ReportController@closingReportList');
$router->addRoute('GET', '/admin/reports/closing/{id}', 'Admin\\ReportController@closingReportDetail');