<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;
use DateTime;

class Report extends Model
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    private function buildSalesQuery($columns, $startDate, $endDate, $filterBy, $searchTerm, $statusFilter)
    {
        $sql = "SELECT {$columns}
                FROM orders o
                LEFT JOIN payments p ON o.id = p.order_id
                LEFT JOIN tables t ON o.table_id = t.id";

        $whereClauses = [];
        $params = [];

        // Status Filter (e.g., 'paid', 'served', etc.)
        if (!empty($statusFilter) && $statusFilter !== 'all') {
            $whereClauses[] = "o.status = :status";
            $params[':status'] = $statusFilter;
        }

        // Date Filter
        $dateColumn = ($filterBy === 'payment_time') ? 'p.payment_time' : 'o.created_at';
        if ($filterBy === 'payment_time') {
            // Only include orders that have a payment time within the date range
            $whereClauses[] = "(DATE({$dateColumn}) BETWEEN :startDate AND :endDate)";
        } else {
            // Filter by order creation time
            $whereClauses[] = "DATE(o.created_at) BETWEEN :startDate AND :endDate";
        }
        $params[':startDate'] = $startDate;
        $params[':endDate'] = $endDate;

        // Search Term Filter with unique placeholders to prevent PDO error
        if (!empty($searchTerm)) {
            $searchCondition = "(
                o.order_number LIKE :searchTerm1 OR 
                p.payment_method LIKE :searchTerm2 OR 
                t.table_number LIKE :searchTerm3 OR
                (CASE WHEN o.table_id IS NOT NULL THEN 'Dine-in' ELSE 'Takeaway' END) LIKE :searchTerm4
            )";
            $whereClauses[] = $searchCondition;
            $searchTermValue = '%' . $searchTerm . '%';
            $params[':searchTerm1'] = $searchTermValue;
            $params[':searchTerm2'] = $searchTermValue;
            $params[':searchTerm3'] = $searchTermValue;
            $params[':searchTerm4'] = $searchTermValue;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        return ['sql' => $sql, 'params' => $params];
    }

    public function getSalesDetails($startDate, $endDate, $filterBy, $searchTerm, $statusFilter)
    {
        $columns = "o.id as order_id,
                    o.order_number as transaction_no,
                    o.created_at as order_time,
                    p.payment_time,
                    COALESCE(t.table_number, 'Takeaway') as outlet,
                    CASE WHEN o.table_id IS NOT NULL THEN 'Dine-in' ELSE 'Takeaway' END as order_type,
                    o.total_amount as total_sales,
                    p.payment_method,
                    p.amount_paid";

        $queryInfo = $this->buildSalesQuery($columns, $startDate, $endDate, $filterBy, $searchTerm, $statusFilter);
        
        $dateColumnSort = ($filterBy === 'payment_time') ? 'p.payment_time' : 'o.created_at';
        $sql = $queryInfo['sql'] . " ORDER BY {$dateColumnSort} DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($queryInfo['params']);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getSalesMetrics($startDate, $endDate, $filterBy, $searchTerm, $statusFilter)
    {
        $columns = "SUM(o.total_amount) as total_sales,
                    COUNT(DISTINCT o.id) as total_transactions,
                    SUM(o.total_amount - COALESCE(o.refunds, 0)) as net_sales,
                    SUM(p.amount_paid) as total_payments";

        $queryInfo = $this->buildSalesQuery($columns, $startDate, $endDate, $filterBy, $searchTerm, $statusFilter);

        $stmt = $this->pdo->prepare($queryInfo['sql']);
        $stmt->execute($queryInfo['params']);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getFullSalesReport($startDate, $endDate)
    {
        $report = [];
        $report['current_period'] = $this->getPeriodFinancials($startDate, $endDate);
        
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
        $interval = $startDateTime->diff($endDateTime);
        $daysDifference = $interval->days + 1;
        
        $prevEndDate = date('Y-m-d', strtotime($startDate . ' -1 day'));
        $prevStartDate = date('Y-m-d', strtotime($prevEndDate . ' -' . ($daysDifference - 1) . ' days'));
        
        $report['previous_period'] = $this->getPeriodFinancials($prevStartDate, $prevEndDate);
        $report['previous_period_range'] = ['start' => $prevStartDate, 'end' => $prevEndDate];
        $report['payment_methods'] = $this->getPaymentMethodSummary($startDate, $endDate);
        $report['today'] = $this->getTodaySummary();
        return $report;
    }

    private function getTodaySummary()
    {
        $summary = [];
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status IN ('paid', 'served')");
        $stmt->execute();
        $summary['revenue'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;
        
        $stmt = $this->pdo->prepare("SELECT COUNT(id) as total FROM orders WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $summary['orders'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;
        return $summary;
    }

    public function getPeriodFinancials($startDate, $endDate)
    {
        // Initialize all keys with default values to prevent "Undefined array key" errors
        $data = [
            'total_revenue'     => 0.0,
            'gross_sales'       => 0.0,
            'net_sales'         => 0.0,
            'refunds'           => 0.0,
            'service_charge'    => 0.0,
            'mdr_service_fee'   => 0.0,
            'tax'               => 0.0,
            'other_revenue'     => 0.0,
            'total_orders'      => 0,
            'cogs'              => 0.0, // Cost of Goods Sold
            'total_promo'       => 0.0,
            'admin_fee'         => 0.0,
            'mdr_fee'           => 0.0,
            'commission'        => 0.0,
            'gross_profit'      => 0.0, // Gross Profit
            'aov'               => 0.0, // Average Order Value
        ];

        $sql = "SELECT 
                    SUM(total_amount) as total_revenue,
                    COUNT(id) as total_orders
                FROM orders 
                WHERE status IN ('paid', 'served') 
                AND DATE(created_at) BETWEEN :startDate AND :endDate";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['total_orders'] > 0) {
            $data['total_revenue'] = (float)($result['total_revenue'] ?? 0);
            $data['total_orders'] = (int)($result['total_orders'] ?? 0);
            
            // --- Placeholder Calculations ---
            // These are simplified calculations. You should replace them with your actual business logic.
            $data['gross_sales'] = $data['total_revenue'] / 1.11; // Assuming 11% tax/service
            $data['tax'] = $data['gross_sales'] * 0.10; // 10% tax
            $data['service_charge'] = $data['gross_sales'] * 0.01; // 1% service charge
            $data['cogs'] = $data['total_revenue'] * 0.4; // Assuming COGS is 40% of revenue
            $data['net_sales'] = $data['gross_sales']; // Assuming no refunds for now
            // --- End Placeholder Calculations ---

            $data['gross_profit'] = $data['net_sales'] - $data['cogs'];
            $data['aov'] = $data['total_orders'] > 0 ? $data['total_revenue'] / $data['total_orders'] : 0;
        }

        return $data;
    }

    public function getPaymentMethodSummary($startDate, $endDate)
    {
        $sql = "SELECT p.payment_method, SUM(p.amount_paid) as total_amount, COUNT(p.id) as transaction_count
                FROM payments p
                JOIN orders o ON p.order_id = o.id
                WHERE o.status IN ('paid', 'served') AND DATE(o.created_at) BETWEEN :startDate AND :endDate
                GROUP BY p.payment_method";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getTopSellingItems($startDate, $endDate, $limit = 5)
    {
        $sql = "SELECT mi.name, SUM(oi.quantity) as total_quantity FROM order_items oi JOIN menu_items mi ON oi.menu_item_id = mi.id JOIN orders o ON oi.order_id = o.id WHERE o.status IN ('paid', 'served') AND DATE(o.created_at) BETWEEN :startDate AND :endDate GROUP BY mi.id, mi.name ORDER BY total_quantity DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getSalesDataForChart($startDate, $endDate)
    {
        $stmt = $this->pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as sale_date, SUM(total_amount) as daily_total FROM orders WHERE status IN ('paid', 'served') AND DATE(created_at) BETWEEN :startDate AND :endDate GROUP BY sale_date ORDER BY sale_date ASC");
        $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}