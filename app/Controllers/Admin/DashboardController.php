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

        $dashboardModel = $this->model('Dashboard');

        $data = [
            'pageTitle' => 'Dashboard Penjualan',
            'period' => $period,
            'metrics' => $dashboardModel->getSalesMetrics($period),
            'mtd_sales' => $dashboardModel->getMonthToDateSales(),
            'monthly_projection' => $dashboardModel->getMonthlyProjection(),
            'chart_data' => $dashboardModel->getSalesChartData($period),
        ];

        // 3. Load View Dashboard
        $this->view('admin.dashboard', $data, 'admin_layout');
    }
}
?>