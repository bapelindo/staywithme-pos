<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\AuthHelper;
use App\Models\Dashboard;
use App\Models\Order;
use App\Models\MenuItem;

class DashboardController extends Controller
{
    public function index()
    {
        AuthHelper::requireRole(['admin', 'staff']);

        $orderModel = $this->model('Order');
        $menuItemModel = $this->model('MenuItem');
        
        $userName = AuthHelper::getUserName() ?? 'User';
        $newOrderCount = $orderModel->countByStatus('pending_payment');
        $processingOrderCount = $orderModel->countByStatus(['received', 'preparing']);
        $unavailableItemCount = $menuItemModel->countUnavailable();

        $period = $_GET['period'] ?? 'daily';
        if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
            $period = 'daily';
        }

        $dateInput = $_GET['date'] ?? date('Y-m-d');
        
        $d = \DateTime::createFromFormat('Y-m-d', $dateInput);
        if ($d && $d->format('Y-m-d') === $dateInput) {
            $date = $dateInput;
        } else {
            $date = date('Y-m-d');
        }
        
        $currentDate = new \DateTime($date);
        $dashboardModel = $this->model('Dashboard');

        switch ($period) {
            case 'weekly':
                $prevDate = (clone $currentDate)->modify('-1 week')->format('Y-m-d');
                $nextDate = (clone $currentDate)->modify('+1 week')->format('Y-m-d');
                break;
            case 'monthly':
                $prevDate = (clone $currentDate)->modify('first day of last month')->format('Y-m-d');
                $nextDate = (clone $currentDate)->modify('first day of next month')->format('Y-m-d');
                break;
            default: 
                $prevDate = (clone $currentDate)->modify('-1 day')->format('Y-m-d');
                $nextDate = (clone $currentDate)->modify('+1 day')->format('Y-m-d');
        }

        $chartData = $dashboardModel->getSalesChartData($period, $date);
        $salesForecast = $dashboardModel->getSalesForecastNext7Days();

        $data = [
            'pageTitle' => 'Dashboard',
            'userName' => $userName,
            'newOrderCount' => $newOrderCount,
            'processingOrderCount' => $processingOrderCount,
            'unavailableItemCount' => $unavailableItemCount,
            'period' => $period,
            'currentDate' => $currentDate->format('Y-m-d'),
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'metrics' => $dashboardModel->getSalesMetrics($period, $date),
            // PERBAIKAN: Kirim parameter $date ke method model
            'mtd_sales' => $dashboardModel->getMonthToDateSales($date),
            'monthly_projection' => $dashboardModel->getMonthlyProjection($date),
            // AKHIR PERBAIKAN
            'chart_data' => $chartData,
            'sales_forecast' => $salesForecast,
        ];

        $this->view('admin.dashboard', $data, 'admin_layout');
    }
}