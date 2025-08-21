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
        $reportModel = $this->model('Report');

        $data = [
            'title' => 'Ringkasan Penjualan',
            'summary' => $reportModel->getSalesSummary(),
            'top_items' => $reportModel->getTopSellingItems(5),
            'chart_data' => $reportModel->getSalesDataForChart()
        ];
        
        $this->view('admin.reports.summary', $data, 'admin_layout');
    }
}
?>