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
        $currentDate = new \DateTime($date);

        // Helper function to get date offsets
        $getPrevDate = function ($p, $d) {
            $current = new \DateTime($d);
            switch ($p) {
                case 'weekly': return $current->modify('-1 week')->format('Y-m-d');
                case 'monthly': return $current->modify('first day of last month')->format('Y-m-d');
                default: return $current->modify('-1 day')->format('Y-m-d');
            }
        };

        $prevDate = $getPrevDate($period, $date);
        $dateCondition = $this->getDateCondition($period, $date);
        $prevDateCondition = $this->getDateCondition($period, $prevDate);

        $groupBy = '';
        $labelKey = '';
        $fullLabelSet = [];

        switch ($period) {
            case 'weekly':
                $groupBy = "DAYOFWEEK(created_at)";
                $labelKey = "DAYOFWEEK(created_at)";
                $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                for ($i = 1; $i <= 7; $i++) {
                    $fullLabelSet[$i] = ['label' => $days[$i-1], 'current_sales' => 0, 'previous_sales' => 0];
                }
                break;
            case 'monthly':
                $groupBy = "DAY(created_at)";
                $labelKey = "DAY(created_at)";
                $daysInMonth = (clone $currentDate)->format('t');
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $fullLabelSet[$i] = ['label' => sprintf('%02d', $i), 'current_sales' => 0, 'previous_sales' => 0];
                }
                break;
            default: // daily
                $groupBy = "HOUR(created_at)";
                $labelKey = "HOUR(created_at)";
                for ($i = 0; $i < 24; $i++) {
                    $fullLabelSet[$i] = ['label' => $i . ':00', 'current_sales' => 0, 'previous_sales' => 0];
                }
        }

        $sql = "
            SELECT
                'current' as period_type,
                {$labelKey} as label_key,
                SUM(total_amount) as sales
            FROM orders
            WHERE {$dateCondition}
            GROUP BY {$groupBy}
            
            UNION ALL
            
            SELECT
                'previous' as period_type,
                {$labelKey} as label_key,
                SUM(total_amount) as sales
            FROM orders
            WHERE {$prevDateCondition}
            GROUP BY {$groupBy}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Populate the full label set with data from the database
        foreach ($results as $row) {
            $key = $row['label_key'];
            if (isset($fullLabelSet[$key])) {
                if ($row['period_type'] === 'current') {
                    $fullLabelSet[$key]['current_sales'] = (float)$row['sales'];
                } else {
                    $fullLabelSet[$key]['previous_sales'] = (float)$row['sales'];
                }
            }
        }
        
        return array_values($fullLabelSet);
    }
}
