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
                    COALESCE(SUM(CASE WHEN status IN ('paid', 'served') THEN total_amount ELSE 0 END), 0) as total_sales,
                    COALESCE(SUM(CASE WHEN status IN ('paid', 'served') THEN total_amount ELSE 0 END), 0) as paid_sales,
                    COALESCE(SUM(CASE WHEN status = 'pending_payment' THEN total_amount ELSE 0 END), 0) as unpaid_sales,
                    COUNT(CASE WHEN status IN ('paid', 'served') THEN id ELSE NULL END) as transactions
                FROM orders
                WHERE {$dateCondition}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $metrics = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql_products = "SELECT COALESCE(SUM(oi.quantity), 0) as total_products
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE {$dateCondition} AND o.status IN ('paid', 'served')";
        
        $stmt_products = $this->pdo->prepare($sql_products);
        $stmt_products->execute();
        $metrics['total_products'] = $stmt_products->fetchColumn();

        $metrics['sales_per_transaction'] = ($metrics['transactions'] ?? 0) > 0 ? ($metrics['total_sales'] ?? 0) / $metrics['transactions'] : 0;
        $metrics['products_per_transaction'] = ($metrics['transactions'] ?? 0) > 0 ? ($metrics['total_products'] ?? 0) / $metrics['transactions'] : 0;

        return $metrics;
    }

    /**
     * === PERBAIKAN DI SINI ===
     * Method ini sekarang menerima parameter tanggal.
     */
    public function getMonthToDateSales($date = null)
    {
        $baseDate = $date ? $this->pdo->quote($date) : 'CURRENT_DATE';
        
        $sql = "SELECT COALESCE(SUM(total_amount), 0) as mtd_sales
                FROM orders
                WHERE status IN ('paid', 'served') 
                AND DATE(created_at) >= DATE_FORMAT($baseDate, '%Y-%m-01') 
                AND DATE(created_at) <= DATE($baseDate)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * === PERBAIKAN DI SINI ===
     * Method ini sekarang menerima parameter tanggal.
     */
    public function getMonthlyProjection($date = null)
    {
        $baseDate = $date ? $this->pdo->quote($date) : 'CURRENT_DATE';

        $sql = "SELECT 
            (AVG(daily_total) * DAY(LAST_DAY($baseDate))) as projection
            FROM (
                SELECT DATE(created_at) as sale_date, 
                       SUM(total_amount) as daily_total
                FROM orders
                WHERE status IN ('paid', 'served')
                AND DATE(created_at) >= DATE_FORMAT($baseDate, '%Y-%m-01')
                AND DATE(created_at) <= DATE($baseDate)
                GROUP BY DATE(created_at)
            ) as daily_totals";
        
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['projection'] ?? 0;
    }

    public function getSalesChartData($period, $date = null)
    {
        $currentDate = new \DateTime($date);

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
            WHERE {$dateCondition} AND status IN ('paid', 'served')
            GROUP BY {$groupBy}
            
            UNION ALL
            
            SELECT
                'previous' as period_type,
                {$labelKey} as label_key,
                SUM(total_amount) as sales
            FROM orders
            WHERE {$prevDateCondition} AND status IN ('paid', 'served')
            GROUP BY {$groupBy}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    
    public function getSalesForecastNext7Days(): array
    {
        $sql = "
            SELECT 
                DAYOFWEEK(sale_date) as day_of_week,
                AVG(daily_total) as average_sales
            FROM (
                SELECT 
                    DATE(created_at) as sale_date, 
                    SUM(total_amount) as daily_total
                FROM orders
                WHERE 
                    status IN ('paid', 'served') AND
                    created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 90 DAY)
                GROUP BY DATE(created_at)
            ) as daily_sales
            GROUP BY DAYOFWEEK(sale_date)
        ";

        try {
            $stmt = $this->pdo->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $forecast = [];
            $days_in_indonesian = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

            for ($i = 1; $i <= 7; $i++) {
                $date = new \DateTime('+' . $i . ' day');
                $dayOfWeek = $date->format('w') + 1;
                
                $forecast[] = [
                    'date' => $date->format('Y-m-d'),
                    'day_name' => $days_in_indonesian[$date->format('w')],
                    'projected_sales' => (float)($results[$dayOfWeek] ?? 0)
                ];
            }
            return $forecast;

        } catch (\PDOException $e) {
            error_log("Error getting sales forecast: " . $e->getMessage());
            return [];
        }
    }
}