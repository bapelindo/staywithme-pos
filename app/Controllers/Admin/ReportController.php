<?php
// File: app/Controllers/Admin/ReportController.php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\AuthHelper;
use App\Helpers\SanitizeHelper;
use App\Models\Report;
use App\Models\Category; // Ditambahkan untuk mengambil daftar kategori
use App\Models\Order;
use App\Models\OrderItem;
use DateTime;

class ReportController extends Controller
{
    /**
     * Helper function to validate date format.
     */
    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function index()
    {
        AuthHelper::requireRole(['admin', 'staff']);

        $today = date('Y-m-d');
        $defaultStartDate = date('Y-m-01');
        $startDateInput = $_GET['start_date'] ?? $defaultStartDate;
        $endDateInput = $_GET['end_date'] ?? $today;

        $startDate = SanitizeHelper::string($startDateInput);
        $endDate = SanitizeHelper::string($endDateInput);

        if (!$this->validateDate($startDate)) {
            $startDate = $defaultStartDate;
        }
        if (!$this->validateDate($endDate)) {
            $endDate = $today;
        }
        if (strtotime($endDate) < strtotime($startDate)) {
            $endDate = $startDate;
        }

        $orderModel = $this->model('Order');
        $orderItemModel = $this->model('OrderItem');

        $summary = $orderModel->getSalesReportSummary($startDate, $endDate);
        $popularItems = $orderItemModel->getPopularItems($startDate, $endDate, 5);
        $popularItemsForChart = [
            'labels' => array_column($popularItems, 'menu_item_name'),
            'quantities' => array_column($popularItems, 'total_quantity')
        ];
        $salesDataForChart = $orderModel->getSalesTrendData($startDate, $endDate);
        $revenueByCategory = $orderItemModel->getRevenueByCategory($startDate, $endDate);
        $categoryRevenueForChart = [
            'labels' => [], 'data' => [], 'colors' => [],
        ];
        $baseColors = [
            'rgba(79, 70, 229, 0.7)', 'rgba(5, 150, 105, 0.7)', 'rgba(217, 119, 6, 0.7)',
            'rgba(219, 39, 119, 0.7)', 'rgba(107, 114, 128, 0.7)', 'rgba(6, 182, 212, 0.7)',
        ];
        $colorIndex = 0;
        foreach ($revenueByCategory as $catData) {
            $categoryRevenueForChart['labels'][] = $catData['category_name'];
            $categoryRevenueForChart['data'][] = (float)$catData['total_revenue'];
            $categoryRevenueForChart['colors'][] = $baseColors[$colorIndex % count($baseColors)];
            $colorIndex++;
        }

        $data = [
            'pageTitle' => 'Laporan Penjualan',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'popularItems' => $popularItems,
            'salesDataForChart' => $salesDataForChart,
            'popularItemsForChart' => $popularItemsForChart,
            'categoryRevenueForChart' => $categoryRevenueForChart,
            'revenueByCategory' => $revenueByCategory,
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

        if (!$this->validateDate($startDate)) { $startDate = $defaultStartDate; }
        if (!$this->validateDate($endDate)) { $endDate = $today; }
        if (strtotime($endDate) < strtotime($startDate)) { $endDate = $startDate; }

        $reportModel = $this->model('Report');
        $reportData = $reportModel->getFullSalesReport($startDate, $endDate);

        $calculateChange = function($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100.0 : 0.0;
            }
            return (($current - $previous) / abs($previous)) * 100;
        };

        // Pastikan variabel ada sebelum diakses untuk menghindari error
        $currentPeriod = $reportData['current_period'] ?? [];
        $previousPeriod = $reportData['previous_period'] ?? [];

        $summary = [
            'total_revenue'       => $currentPeriod['total_revenue'] ?? 0,
            'total_revenue_change'=> $calculateChange($currentPeriod['total_revenue'] ?? 0, $previousPeriod['total_revenue'] ?? 0),
            'gross_profit'        => $currentPeriod['gross_profit'] ?? 0,
            'gross_profit_change' => $calculateChange($currentPeriod['gross_profit'] ?? 0, $previousPeriod['gross_profit'] ?? 0),
            'total_orders'        => $currentPeriod['total_orders'] ?? 0,
            'total_orders_change' => $calculateChange($currentPeriod['total_orders'] ?? 0, $previousPeriod['total_orders'] ?? 0),
            'aov'                 => $currentPeriod['aov'] ?? 0,
            'aov_change'          => $calculateChange($currentPeriod['aov'] ?? 0, $previousPeriod['aov'] ?? 0),
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

        $chart_data_raw = $reportModel->getSalesDataForChart($startDate, $endDate);
        $chart_labels = array_map(fn($d) => $d->sale_date, $chart_data_raw);
        $chart_values = array_map(fn($d) => (float)$d->daily_total, $chart_data_raw);

        $data = [
            'pageTitle'             => 'Ringkasan Penjualan',
            'startDate'             => $startDate,
            'endDate'               => $endDate,
            'today'                 => $reportData['today'],
            'summary'               => $summary,
            'financials'            => $reportData['current_period'],
            'payment_methods'       => $reportData['payment_methods'],
            'payment_chart_data'    => $paymentDataForChart,
            'top_items'             => $reportModel->getTopSellingItems($startDate, $endDate, 5),
            'chart_data' => [
                'labels' => $chart_labels,
                'data'   => $chart_values,
            ],
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
        $topItems = $reportModel->getTopSellingItems($startDate, $endDate, 100);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sales_summary_'.$startDate.'_to_'.$endDate.'.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Laporan Ringkasan Penjualan']);
        fputcsv($output, ['Periode:', $startDate . ' - ' . $endDate]);
        fputcsv($output, []);
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
        }
        fputcsv($output, []);
        fputcsv($output, ['Produk Terlaris', 'Jumlah Terjual']);
        if (!empty($topItems)) {
            foreach ($topItems as $item) {
                fputcsv($output, [$item->name, $item->total_quantity]);
            }
        }
        fclose($output);
        exit;
    }

    public function financials()
    {
        AuthHelper::requireAdmin();

        $today = date('Y-m-d');
        $defaultStartDate = date('Y-m-01');
        
        $startDateInput = $_GET['start_date'] ?? $defaultStartDate;
        $endDateInput = $_GET['end_date'] ?? $today;

        $startDate = SanitizeHelper::string($startDateInput);
        $endDate = SanitizeHelper::string($endDateInput);

        if (!$this->validateDate($startDate)) { $startDate = $defaultStartDate; }
        if (!$this->validateDate($endDate)) { $endDate = $today; }
        if (strtotime($endDate) < strtotime($startDate)) { $endDate = $startDate; }

        $reportModel = $this->model('Report');
        $reportData = $reportModel->getFullSalesReport($startDate, $endDate);

        $data = [
            'pageTitle' => 'Rincian Finansial',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'financials' => $reportData['current_period'],
            'previous_period_range' => $reportData['previous_period_range']
        ];
        
        $this->view('admin.reports.financials', $data, 'admin_layout');
    }

    public function salesDetail()
    {
        AuthHelper::requireAdmin();

        // --- 1. Set Default Values ---
        $today = date('Y-m-d');
        $defaultStartDate = date('Y-m-01'); // Default is the first day of the current month.

        // --- 2. Get Input from GET Request (or use defaults) ---
        $startDateInput = $_GET['start_date'] ?? $defaultStartDate;
        $endDateInput = $_GET['end_date'] ?? $today;
        $filterBy = $_GET['filter_by'] ?? 'order_time';
        $searchTerm = $_GET['search_term'] ?? '';
        $statusFilter = $_GET['status_filter'] ?? 'paid';

        // --- 3. Sanitize and Validate All Inputs ---
        $startDate = SanitizeHelper::string($startDateInput);
        $endDate = SanitizeHelper::string($endDateInput);
        $filterBy = in_array($filterBy, ['order_time', 'payment_time']) ? $filterBy : 'order_time';
        $searchTerm = SanitizeHelper::string(trim($searchTerm));
        $statusFilter = SanitizeHelper::string($statusFilter);

        // Strict date validation
        if (!$this->validateDate($startDate)) {
            $startDate = $defaultStartDate;
        }
        if (!$this->validateDate($endDate)) {
            $endDate = $today;
        }
        // Ensure end date is not before start date
        if (strtotime($endDate) < strtotime($startDate)) {
            $endDate = $startDate;
        }

        // --- 4. Call the Model to Get Data ---
        $reportModel = $this->model('Report');
        
        $salesDetails = $reportModel->getSalesDetails($startDate, $endDate, $filterBy, $searchTerm, $statusFilter);
        $salesMetrics = $reportModel->getSalesMetrics($startDate, $endDate, $filterBy, $searchTerm, $statusFilter);

        // --- 5. Prepare Data Array for the View ---
        $data = [
            'pageTitle' => 'Detail Penjualan',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterBy' => $filterBy,
            'searchTerm' => $searchTerm,
            'statusFilter' => $statusFilter,
            'salesDetails' => $salesDetails,
            'totalSales' => $salesMetrics->total_sales ?? 0,
            'totalTransactions' => $salesMetrics->total_transactions ?? 0,
            'netSales' => $salesMetrics->net_sales ?? 0,
            'totalPayments' => $salesMetrics->total_payments ?? 0,
        ];
        
        // --- 6. Load the View with the Data ---
        $this->view('admin.reports.sales_detail', $data, 'admin_layout');
    }
    
    public function productSales()
    {
        AuthHelper::requireAdmin();

        // 1. Handle filters, including new graph filters
        $today = date('Y-m-d');
        $defaultStartDate = date('Y-m-01');
        
        $startDate = SanitizeHelper::string($_GET['start_date'] ?? $defaultStartDate);
        $endDate = SanitizeHelper::string($_GET['end_date'] ?? $today);
        $selectedCategory = SanitizeHelper::string($_GET['category'] ?? 'all');
        $searchTerm = SanitizeHelper::string(trim($_GET['search'] ?? ''));
        $groupBy = SanitizeHelper::string($_GET['group_by'] ?? 'day');
        $chartMetric = SanitizeHelper::string($_GET['chart_metric'] ?? 'penjualan');
        
        if (!$this->validateDate($startDate)) { $startDate = $defaultStartDate; }
        if (!$this->validateDate($endDate)) { $endDate = $today; }
        if (strtotime($endDate) < strtotime($startDate)) { $endDate = $startDate; }

        // 2. Fetch data from model
        $reportModel = $this->model('Report');
        $categoryModel = $this->model('Category');

        $reportData = $reportModel->getProductSalesReportV2($startDate, $endDate, $selectedCategory, $searchTerm);
        $allCategories = $categoryModel->getAllSorted();
        
        // 3. Calculate metrics
        $totalRevenue = array_sum(array_column($reportData, 'total_sales'));
        $totalQuantity = array_sum(array_column($reportData, 'total_quantity_sold'));
        $totalGrossProfit = array_sum(array_column($reportData, 'gross_profit'));

        // 4. Prepare data for the chart
        $chartData = $reportModel->getProductSalesChartData($startDate, $endDate, $selectedCategory, $searchTerm, $groupBy, $chartMetric);

        // 5. Send data to the view
        $data = [
            'pageTitle' => 'Laporan Penjualan Produk',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'categories' => $allCategories,
            'selectedCategory' => $selectedCategory,
            'searchTerm' => $searchTerm,
            'reportData' => $reportData,
            'metrics' => [
                'total_revenue' => $totalRevenue,
                'total_quantity' => $totalQuantity,
                'total_gross_profit' => $totalGrossProfit,
            ],
            'chartData' => $chartData,
            'groupBy' => $groupBy,
            'chartMetric' => $chartMetric,
        ];
        
        $this->view('admin.reports.product_sales', $data, 'admin_layout');
    }
}