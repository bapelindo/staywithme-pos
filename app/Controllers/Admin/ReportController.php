<?php
// File: app/Controllers/Admin/ReportController.php (Update)
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\AuthHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper;
use App\Helpers\NumberHelper;
use App\Models\Order;
use App\Models\OrderItem; // <-- Pastikan OrderItem di-use
use DateTime;

class ReportController extends Controller {

    public function index() {
        AuthHelper::requireRole(['admin', 'staff']);

        // --- Tangani Filter Tanggal (Tetap Sama) ---
        $today = date('Y-m-d');
        $defaultStartDate = date('Y-m-01');
        $startDateInput = $_GET['start_date'] ?? $defaultStartDate;
        $endDateInput = $_GET['end_date'] ?? $today;
        $startDate = SanitizeHelper::string($startDateInput);
        $endDate = SanitizeHelper::string($endDateInput);
        // ... (validasi tanggal tetap sama) ...
        $dateFormatRegex = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($dateFormatRegex, $startDate)) { $startDate = $defaultStartDate; }
        if (!preg_match($dateFormatRegex, $endDate)) { $endDate = $today; }
        if ($endDate < $startDate) { $endDate = $startDate; }
        if ($endDate > $today) { $endDate = $today; }


        // --- Ambil Data dari Model ---
        $orderModel = $this->model('Order');
        $orderItemModel = $this->model('OrderItem'); // <-- Pastikan model OrderItem dimuat

        // 1. Data Ringkasan (Summary Cards) - Tetap Sama
        $summary = $orderModel->getSalesReportSummary($startDate, $endDate); // Menggunakan status 'served'/'paid'

        // 2. Data Item Terpopuler (Tabel & Grafik Batang) - Tetap Sama
        $limitPopular = 5;
        $popularItems = $orderItemModel->getPopularItems($startDate, $endDate, $limitPopular); // Menggunakan status 'served'/'paid'
        $popularItemsForChart = [
            'labels' => array_column($popularItems, 'menu_item_name'),
            'quantities' => array_column($popularItems, 'total_quantity')
        ];

        // 3. Data Tren Penjualan Harian (Grafik Garis) - Tetap Sama
        $salesDataForChart = $orderModel->getSalesTrendData($startDate, $endDate); // Menggunakan status 'served'/'paid'

        // === PERUBAHAN: 4. Ambil Data Penjualan per Kategori ===
        $revenueByCategory = $orderItemModel->getRevenueByCategory($startDate, $endDate);
        // Siapkan data khusus untuk grafik pie/doughnut
        $categoryRevenueForChart = [
            'labels' => [], // Nama kategori
            'data' => [],   // Pendapatan per kategori
            'colors' => [], // Warna untuk grafik (bisa digenerate otomatis atau manual)
        ];
        // Contoh warna dasar (bisa diperbanyak atau gunakan library JS untuk generate)
        $baseColors = [
            'rgba(79, 70, 229, 0.7)',  // Indigo
            'rgba(5, 150, 105, 0.7)',  // Emerald
            'rgba(217, 119, 6, 0.7)',  // Amber
            'rgba(219, 39, 119, 0.7)', // Fuchsia
            'rgba(107, 114, 128, 0.7)',// Gray
            'rgba(6, 182, 212, 0.7)',  // Cyan
            'rgba(139, 92, 246, 0.7)', // Violet
            'rgba(234, 88, 12, 0.7)',  // Orange
        ];
        $colorIndex = 0;
        foreach ($revenueByCategory as $catData) {
            $categoryRevenueForChart['labels'][] = $catData['category_name'];
            $categoryRevenueForChart['data'][] = (float)$catData['total_revenue'];
            $categoryRevenueForChart['colors'][] = $baseColors[$colorIndex % count($baseColors)];
            $colorIndex++;
        }
        // === AKHIR PERUBAHAN ===

        // --- Kirim Data ke View ---
        $data = [
            'pageTitle' => 'Laporan Penjualan',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'popularItems' => $popularItems,
            'salesDataForChart' => $salesDataForChart,
            'popularItemsForChart' => $popularItemsForChart,
            'categoryRevenueForChart' => $categoryRevenueForChart, // <-- Kirim data kategori
            'revenueByCategory' => $revenueByCategory, // <-- Kirim data detail untuk tabel (opsional)
        ];

        $this->view('admin.reports.index', $data, 'admin_layout');
    }
        public function summary()
    {
        AuthHelper::requireAdmin();

        $today = date('Y-m-d');
        $defaultStartDate = date('Y-m-01');
        
        $startDateInput = $_GET['start_date'] ?? $defaultStartDate;
        $endDateInput = $_GET['end_date'] ?? $today;

        $startDate = SanitizeHelper::string($startDateInput);
        $endDate = SanitizeHelper::string($endDateInput);
        $dateFormatRegex = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($dateFormatRegex, $startDate)) { $startDate = $defaultStartDate; }
        if (!preg_match($dateFormatRegex, $endDate)) { $endDate = $today; }
        if ($endDate < $startDate) { $endDate = $startDate; }

        $reportModel = $this->model('Report');
        $reportData = $reportModel->getFullSalesReport($startDate, $endDate);

        $calculateChange = function($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100.0 : 0.0;
            }
            return (($current - $previous) / abs($previous)) * 100;
        };

        $summary = [
            'total_revenue' => $reportData['current_period']['total_revenue'],
            'total_revenue_change' => $calculateChange($reportData['current_period']['total_revenue'], $reportData['previous_period']['total_revenue']),
            'gross_profit' => $reportData['current_period']['gross_profit'],
            'gross_profit_change' => $calculateChange($reportData['current_period']['gross_profit'], $reportData['previous_period']['gross_profit']),
            'total_orders' => $reportData['current_period']['total_orders'],
            'total_orders_change' => $calculateChange($reportData['current_period']['total_orders'], $reportData['previous_period']['total_orders']),
            'aov' => $reportData['current_period']['aov'],
            'aov_change' => $calculateChange($reportData['current_period']['aov'], $reportData['previous_period']['aov']),
        ];

        $paymentDataForChart = ['labels' => [], 'data' => [], 'colors' => []];
        $baseColors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#6b7280'];
        $i = 0;
        foreach ($reportData['payment_methods'] as $method) {
            $paymentDataForChart['labels'][] = ucfirst($method->payment_method);
            $paymentDataForChart['data'][] = $method->total_amount;
            $paymentDataForChart['colors'][] = $baseColors[$i % count($baseColors)];
            $i++;
        }

        $data = [
            'pageTitle' => 'Ringkasan Penjualan',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'today' => $reportData['today'],
            'summary' => $summary,
            'financials' => [
                'gross_sales' => $reportData['current_period']['total_revenue'],
                'cogs' => $reportData['current_period']['cogs'],
                'gross_profit' => $reportData['current_period']['gross_profit'],
            ],
            'payment_methods' => $reportData['payment_methods'],
            'payment_chart_data' => $paymentDataForChart,
            'top_items' => $reportModel->getTopSellingItems(5, $startDate, $endDate),
            'chart_data' => $reportModel->getSalesDataForChart($startDate, $endDate),
            'previous_period_range' => $reportData['previous_period_range']
        ];
        
        $this->view('admin.reports.summary', $data, 'admin_layout');
    }

    public function exportSummary()
    {
        AuthHelper::requireAdmin();

        $startDate = SanitizeHelper::string($_GET['start_date'] ?? date('Y-m-01'));
        $endDate = SanitizeHelper::string($_GET['end_date'] ?? date('Y-m-d'));

        $reportModel = $this->model('Report');
        $reportData = $reportModel->getFullSalesReport($startDate, $endDate);
        $topItems = $reportModel->getTopSellingItems(100, $startDate, $endDate); // Export more items

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sales_summary_'.$startDate.'_to_'.$endDate.'.csv"');

        $output = fopen('php://output', 'w');

        fputcsv($output, ['Laporan Ringkasan Penjualan']);
        fputcsv($output, ['Periode:', $startDate . ' - ' . $endDate]);
        fputcsv($output, []); // Empty line

        fputcsv($output, ['Metrik Utama', 'Nilai']);
        fputcsv($output, ['Total Pendapatan', $reportData['current_period']['total_revenue']]);
        fputcsv($output, ['Total Pesanan', $reportData['current_period']['total_orders']]);
        fputcsv($output, ['Rata-rata per Pesanan (AOV)', $reportData['current_period']['aov']]);
        fputcsv($output, ['HPP (COGS)', $reportData['current_period']['cogs']]);
        fputcsv($output, ['Laba Kotor', $reportData['current_period']['gross_profit']]);
        fputcsv($output, []);

        fputcsv($output, ['Metode Pembayaran', 'Total Transaksi', 'Total Pendapatan']);
        if (!empty($reportData['payment_methods'])) {
            foreach ($reportData['payment_methods'] as $method) {
                fputcsv($output, [ucfirst($method->payment_method), $method->transaction_count, $method->total_amount]);
            }
        } else {
            fputcsv($output, ['Tidak ada data pembayaran', '0', '0']);
        }
        fputcsv($output, []);

        fputcsv($output, ['Produk Terlaris', 'Jumlah Terjual']);
        if (!empty($topItems)) {
            foreach ($topItems as $item) {
                fputcsv($output, [$item->name, $item->total_quantity]);
            }
        } else {
            fputcsv($output, ['Tidak ada produk terjual', '0']);
        }

        fclose($output);
        exit;
    }
}
?>