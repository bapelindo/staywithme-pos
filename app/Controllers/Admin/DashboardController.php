<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\AuthHelper;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Cek otentikasi & otorisasi (Admin, Staff, Kitchen boleh akses dashboard)
        AuthHelper::requireRole(['admin', 'staff']);

        // 2. Ambil data untuk ditampilkan di dashboard
        $period = $_GET['period'] ?? 'daily';
        if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
            $period = 'daily';
        }

        // Ambil dan validasi tanggal dari query string
        $date = $_GET['date'] ?? date('Y-m-d');
        $currentDate = new \DateTime($date);

        $dashboardModel = $this->model('Dashboard');

        // Siapkan tanggal untuk navigasi
        switch ($period) {
            case 'weekly':
                $prevDate = (clone $currentDate)->modify('-1 week')->format('Y-m-d');
                $nextDate = (clone $currentDate)->modify('+1 week')->format('Y-m-d');
                break;
            case 'monthly':
                $prevDate = (clone $currentDate)->modify('first day of last month')->format('Y-m-d');
                $nextDate = (clone $currentDate)->modify('first day of next month')->format('Y-m-d');
                break;
            default: // daily
                $prevDate = (clone $currentDate)->modify('-1 day')->format('Y-m-d');
                $nextDate = (clone $currentDate)->modify('+1 day')->format('Y-m-d');
        }

        $chartData = $dashboardModel->getSalesChartData($period, $date);

        $data = [
            'pageTitle' => 'Dashboard Penjualan',
            'period' => $period,
            'currentDate' => $currentDate->format('Y-m-d'),
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'metrics' => $dashboardModel->getSalesMetrics($period, $date),
            'mtd_sales' => $dashboardModel->getMonthToDateSales(),
            'monthly_projection' => $dashboardModel->getMonthlyProjection(),
            'chart_data' => $chartData,
        ];

        // 3. Load View Dashboard
        $this->view('admin.dashboard', $data, 'admin_layout');
    }
}
?>