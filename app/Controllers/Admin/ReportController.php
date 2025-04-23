<?php
// File: app/Controllers/Admin/ReportController.php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\AuthHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper;     // Digunakan di view, tapi bisa juga di sini jika perlu
use App\Helpers\NumberHelper;   // Digunakan di view
use App\Models\Order;
use App\Models\OrderItem;
use DateTime; // Untuk validasi tanggal

class ReportController extends Controller {

    /**
     * Menampilkan halaman utama laporan dengan filter tanggal.
     */
    public function index() {
        AuthHelper::requireRole(['admin', 'staff']); // Tentukan siapa yg boleh akses

        // --- Tangani Filter Tanggal ---
        $today = date('Y-m-d');
        $defaultStartDate = date('Y-m-01'); // Default awal bulan ini

        // Ambil dari GET & sanitasi & validasi sederhana
        $startDateInput = $_GET['start_date'] ?? $defaultStartDate;
        $endDateInput = $_GET['end_date'] ?? $today;

        $startDate = SanitizeHelper::string($startDateInput);
        $endDate = SanitizeHelper::string($endDateInput);

        // Validasi format YYYY-MM-DD (dasar)
        $dateFormatRegex = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($dateFormatRegex, $startDate)) {
            $startDate = $defaultStartDate; // Kembali ke default jika format salah
        }
         if (!preg_match($dateFormatRegex, $endDate)) {
            $endDate = $today; // Kembali ke default jika format salah
        }

        // Pastikan end date tidak sebelum start date
        if ($endDate < $startDate) {
            $endDate = $startDate; // Samakan jika end date lebih awal
        }

        // Batasi end date maksimal hari ini
         if ($endDate > $today) {
            $endDate = $today;
        }

        // --- Ambil Data dari Model ---
        $orderModel = $this->model('Order'); // Gunakan model loader
        $orderItemModel = $this->model('OrderItem');

        // 1. Data Ringkasan (Summary Cards)
        $summary = $orderModel->getSalesReportSummary($startDate, $endDate);

        // 2. Data Item Terpopuler (Tabel & Grafik Batang)
        $limitPopular = 5; // Ambil top 5
        $popularItems = $orderItemModel->getPopularItems($startDate, $endDate, $limitPopular);
        // Siapkan data khusus untuk grafik item populer
        $popularItemsForChart = [
            'labels' => array_column($popularItems, 'menu_item_name'),
            'quantities' => array_column($popularItems, 'total_quantity')
        ];

        // 3. Data Tren Penjualan Harian (Grafik Garis)
        $salesDataForChart = $orderModel->getSalesTrendData($startDate, $endDate);

        // --- Kirim Data ke View ---
        $data = [
            'pageTitle' => 'Laporan Penjualan',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'popularItems' => $popularItems, // Untuk tabel detail
            'salesDataForChart' => $salesDataForChart, // Untuk grafik garis
            'popularItemsForChart' => $popularItemsForChart // Untuk grafik batang
        ];

        // Muat view laporan dengan layout admin
        $this->view('admin.reports.index', $data, 'admin_layout');
    }

    // Method lain untuk jenis laporan berbeda bisa ditambahkan di sini
    // public function inventoryReport() { ... }

} // Akhir kelas ReportController
?>