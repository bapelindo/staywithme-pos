<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Dashboard extends Model
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    private function getDateCondition($period, $date = null)
    {
        $baseDate = $date ? "'$date'" : 'CURRENT_DATE';

        switch ($period) {
            case 'weekly':
                return "YEARWEEK(created_at, 1) = YEARWEEK($baseDate, 1)";
            case 'monthly':
                return "YEAR(created_at) = YEAR($baseDate) AND MONTH(created_at) = MONTH($baseDate)";
            default: // daily
                return "DATE(created_at) = DATE($baseDate)";
        }
    }

    public function getSalesMetrics($period, $date = null)
    {
        $dateCondition = $this->getDateCondition($period, $date);

        $sql = "SELECT
                    COALESCE(SUM(total_amount), 0) as total_sales,
                    COALESCE(SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END), 0) as paid_sales,
                    COALESCE(SUM(CASE WHEN status != 'paid' AND status != 'cancelled' THEN total_amount ELSE 0 END), 0) as unpaid_sales,
                    COUNT(id) as transactions
                FROM orders
                WHERE {$dateCondition}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $metrics = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT COALESCE(SUM(oi.quantity), 0) as total_products
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE {$dateCondition}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $metrics['total_products'] = $stmt->fetchColumn();

        $metrics['sales_per_transaction'] = $metrics['transactions'] > 0 ? $metrics['total_sales'] / $metrics['transactions'] : 0;
        $metrics['products_per_transaction'] = $metrics['transactions'] > 0 ? $metrics['total_products'] / $metrics['transactions'] : 0;

        return $metrics;
    }

    public function getMonthToDateSales()
    {
        $sql = "SELECT COALESCE(SUM(total_amount), 0) as mtd_sales
                FROM orders
                WHERE DATE(created_at) >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01') AND DATE(created_at) <= CURRENT_DATE";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getMonthlyProjection()
    {
        $sql = "SELECT 
            (AVG(daily_total) * DAY(LAST_DAY(CURRENT_DATE))) as projection
            FROM (
                SELECT DATE(created_at) as sale_date, 
                       SUM(total_amount) as daily_total
                FROM orders
                WHERE DATE(created_at) >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
                AND DATE(created_at) <= CURRENT_DATE
                GROUP BY DATE(created_at)
            ) as daily_totals";
        
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['projection'] ?? 0;
    }

    public function getSalesChartData($period, $date = null)
    {
        $dateCondition = $this->getDateCondition($period, $date);
        $groupBy = '';
        $labelSelect = '';

        switch ($period) {
            case 'weekly':
                $groupBy = "DATE(created_at)";
                $labelSelect = "DATE_FORMAT(created_at, '%a, %d')"; // Mon, 21
                break;
            case 'monthly':
                $groupBy = "DATE(created_at)";
                $labelSelect = "DATE_FORMAT(created_at, '%d %b')"; // 21 Aug
                break;
            default: // daily
                $groupBy = "HOUR(created_at)";
                $labelSelect = "CONCAT(HOUR(created_at), ':00')";
        }

        $sql = "SELECT {$labelSelect} as label, SUM(total_amount) as sales
                FROM orders
                WHERE {$dateCondition}
                GROUP BY {$groupBy}
                ORDER BY created_at ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
