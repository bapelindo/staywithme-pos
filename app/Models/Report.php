<?php
// File: app/Models/Report.php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;
use DateTime;
use PDOException;

class Report extends Model
{
    protected $pdo;
    private $settings = null;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    private function getSettings() {
        if ($this->settings === null) {
            $settingsModel = new Settings();
            $this->settings = $settingsModel->getAllSettings();
        }
        return $this->settings;
    }

    private function buildSalesQuery($columns, $startDate, $endDate, $filterBy, $searchTerm, $statusFilter)
    {
        $sql = "SELECT {$columns}
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.id
                LEFT JOIN tables t ON o.table_id = t.id";

        $whereClauses = [];
        $params = [];

        $dateColumn = ($filterBy === 'payment_time') ? 'p.payment_time' : 'o.order_time';
        $whereClauses[] = "DATE({$dateColumn}) BETWEEN :startDate AND :endDate";
        $params[':startDate'] = $startDate;
        $params[':endDate'] = $endDate;

        if (!empty($statusFilter) && $statusFilter !== 'all') {
            $whereClauses[] = "o.status = :status";
            $params[':status'] = $statusFilter;
        }

        if (!empty($searchTerm)) {
            $searchCondition = "(
                o.order_number LIKE :searchTerm1 OR
                p.payment_method LIKE :searchTerm2 OR
                t.table_number LIKE :searchTerm3
            )";
            $whereClauses[] = $searchCondition;
            $searchTermValue = '%' . $searchTerm . '%';
            $params[':searchTerm1'] = $searchTermValue;
            $params[':searchTerm2'] = $searchTermValue;
            $params[':searchTerm3'] = $searchTermValue;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        return ['sql' => $sql, 'params' => $params];
    }

    public function getSalesDetails($startDate, $endDate, $filterBy, $searchTerm, $statusFilter)
    {
        $columns = "o.id as order_id, o.order_number as transaction_no, o.order_time, p.payment_time,
                    COALESCE(t.table_number, 'Takeaway') as outlet,
                    CASE WHEN o.table_id IS NOT NULL THEN 'Dine-in' ELSE 'Takeaway' END as order_type,
                    o.total_amount as total_sales, p.payment_method, p.amount_paid";
        $queryInfo = $this->buildSalesQuery($columns, $startDate, $endDate, $filterBy, $searchTerm, 'all');
        $dateColumnSort = ($filterBy === 'payment_time') ? 'p.payment_time' : 'o.order_time';
        $sql = $queryInfo['sql'] . " ORDER BY {$dateColumnSort} DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($queryInfo['params']);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getSalesMetrics($startDate, $endDate, $filterBy, $searchTerm, $statusFilter)
    {
        $columns = "SUM(o.total_amount) as total_sales, COUNT(DISTINCT o.id) as total_transactions,
                    SUM(o.total_amount - COALESCE(o.refunds, 0)) as net_sales, SUM(p.amount_paid) as total_payments";
        $queryInfo = $this->buildSalesQuery($columns, $startDate, $endDate, $filterBy, $searchTerm, 'all');
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
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) as total FROM orders o JOIN payments p ON o.id = p.order_id WHERE DATE(o.order_time) = CURDATE() AND o.status IN ('served', 'paid')");
        $stmt->execute();
        $summary['revenue'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

        $stmt = $this->pdo->prepare("SELECT COUNT(o.id) as total FROM orders o JOIN payments p ON o.id = p.order_id WHERE DATE(o.order_time) = CURDATE() AND o.status IN ('served', 'paid')");
        $stmt->execute();
        $summary['orders'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;
        return $summary;
    }

    public function getPeriodFinancials($startDate, $endDate)
    {
        $settings = $this->getSettings();
        $cogsRate = (float)($settings['cogs_percentage'] ?? 40) / 100;

        $data = [
            'total_revenue' => 0.0, 'gross_sales' => 0.0, 'net_sales' => 0.0,
            'refunds' => 0.0, 'service_charge' => 0.0, 'tax' => 0.0, 'other_revenue' => 0.0,
            'total_orders' => 0, 'cogs' => 0.0,
            'purchase_promo' => 0.0, 'product_promo' => 0.0, 'complimentary' => 0.0,
            'admin_fee' => 0.0, 'mdr_fee' => 0.0, 'commission' => 0.0,
            'gross_profit' => 0.0, 'aov' => 0.0,
            'mdr_service_fee' => 0.0, 'rounding' => 0.0,
            'total_promo_cost' => 0.0
        ];

        $sql = "SELECT
                    SUM(o.total_amount) as total_revenue,
                    COUNT(o.id) as total_orders,
                    SUM(o.admin_fee) as total_admin_fee,
                    SUM(o.service_charge) as total_service_charge,
                    SUM(o.mdr_service_fee) as total_mdr_service_fee,
                    SUM(o.rounding) as total_rounding,
                    SUM(o.tax) as total_tax,
                    SUM(o.other_revenue) as total_other_revenue,
                    SUM(o.purchase_promo) as total_purchase_promo,
                    SUM(o.product_promo) as total_product_promo,
                    SUM(o.complimentary) as total_complimentary,
                    SUM(o.mdr_fee) as total_mdr_fee,
                    SUM(o.commission) as total_commission
                FROM orders o
                JOIN payments p ON o.id = p.order_id
                WHERE o.status IN ('served', 'paid')
                AND DATE(o.order_time) BETWEEN :startDate AND :endDate";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql_refunds = "SELECT SUM(o.total_amount) as total_refunds
                        FROM orders o
                        WHERE o.status = 'cancelled'
                          AND EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.id)
                          AND DATE(o.order_time) BETWEEN :startDate AND :endDate";
        $stmt_refunds = $this->pdo->prepare($sql_refunds);
        $stmt_refunds->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        $refundsResult = $stmt_refunds->fetch(PDO::FETCH_ASSOC);
        $data['refunds'] = (float)($refundsResult['total_refunds'] ?? 0.0);

        if ($result && $result['total_orders'] > 0) {
            $data['total_orders'] = (int)($result['total_orders'] ?? 0);
            $data['total_revenue'] = (float)($result['total_revenue'] ?? 0);
            $data['service_charge'] = (float)($result['total_service_charge'] ?? 0);
            $data['mdr_service_fee'] = (float)($result['total_mdr_service_fee'] ?? 0);
            $data['admin_fee'] = (float)($result['total_admin_fee'] ?? 0);
            $data['rounding'] = (float)($result['total_rounding'] ?? 0);
            $data['tax'] = (float)($result['total_tax'] ?? 0);
            $data['other_revenue'] = (float)($result['total_other_revenue'] ?? 0);
            $data['gross_sales'] = $data['total_revenue'] - ($data['service_charge'] + $data['mdr_service_fee'] + $data['admin_fee'] + $data['rounding'] + $data['tax'] + $data['other_revenue']);
            $data['purchase_promo'] = (float)($result['total_purchase_promo'] ?? 0);
            $data['product_promo'] = (float)($result['total_product_promo'] ?? 0);
            $data['complimentary'] = (float)($result['total_complimentary'] ?? 0);
            $data['mdr_fee'] = (float)($result['total_mdr_fee'] ?? 0);
            $data['commission'] = (float)($result['total_commission'] ?? 0);

            $sql_cogs = "SELECT SUM(CASE WHEN mi.cost > 0 THEN oi.quantity * mi.cost ELSE oi.subtotal * :cogsRate END) as total_cogs
                         FROM order_items oi
                         JOIN orders o ON oi.order_id = o.id
                         JOIN menu_items mi ON oi.menu_item_id = mi.id
                         JOIN payments p ON o.id = p.order_id
                         WHERE o.status IN ('served', 'paid') AND DATE(o.order_time) BETWEEN :startDate AND :endDate";
            $stmt_cogs = $this->pdo->prepare($sql_cogs);
            $stmt_cogs->execute(['cogsRate' => $cogsRate, 'startDate' => $startDate, 'endDate' => $endDate]);
            $cogsResult = $stmt_cogs->fetch(PDO::FETCH_ASSOC);
            $data['cogs'] = (float)($cogsResult['total_cogs'] ?? 0);

            $data['total_promo_cost'] = $data['purchase_promo'] + $data['product_promo'] + $data['complimentary'];
            $data['net_sales'] = $data['total_revenue'] - $data['refunds'];
            $data['gross_profit'] = $data['net_sales'] - $data['mdr_fee'] - $data['cogs'] - $data['commission'];
            $data['aov'] = $data['total_orders'] > 0 ? $data['total_revenue'] / $data['total_orders'] : 0;
        }

        return $data;
    }

    public function getProductSalesReportV2($startDate, $endDate, $categoryId = 'all', $searchTerm = '')
    {
        $settings = $this->getSettings();
        $cogsRate = (float)($settings['cogs_percentage'] ?? 40) / 100;

        $sql = "SELECT
                    mi.id as product_id,
                    mi.name as product_name,
                    MAX(mi.cost) as product_cost,
                    c.name as category_name,
                    SUM(oi.quantity) as total_quantity_sold,
                    SUM(oi.subtotal) as total_sales,
                    SUM(
                        CASE
                            WHEN mi.cost > 0 THEN oi.quantity * mi.cost
                            ELSE oi.subtotal * :cogsRate
                        END
                    ) as total_cogs
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                JOIN orders o ON oi.order_id = o.id
                JOIN categories c ON mi.category_id = c.id
                JOIN payments p ON o.id = p.order_id
                WHERE o.status IN ('served', 'paid')
                AND DATE(o.order_time) BETWEEN :startDate AND :endDate";

        $params = [
            ':startDate' => $startDate,
            ':endDate' => $endDate,
            ':cogsRate' => $cogsRate,
        ];

        if ($categoryId !== 'all' && is_numeric($categoryId)) {
            $sql .= " AND mi.category_id = :categoryId";
            $params[':categoryId'] = (int)$categoryId;
        }

        if (!empty($searchTerm)) {
            $sql .= " AND mi.name LIKE :searchTerm";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }

        $sql .= " GROUP BY mi.id, mi.name, c.name
                  ORDER BY total_sales DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as &$row) {
                $row['gross_profit'] = (float)$row['total_sales'] - (float)$row['total_cogs'];
            }
            return $results;
        } catch (PDOException $e) {
            error_log("Error getting V2 product sales report: " . $e->getMessage());
            return [];
        }
    }

    public function getPaymentMethodSummary($startDate, $endDate)
    {
        $sql = "SELECT p.payment_method, SUM(p.amount_paid) as total_amount, COUNT(p.id) as transaction_count
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.id
                WHERE DATE(o.order_time) BETWEEN :startDate AND :endDate
                AND o.status IN ('served', 'paid')
                GROUP BY p.payment_method";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getTopSellingItems($startDate, $endDate, $limit = 5)
    {
        $sql = "SELECT mi.name, SUM(oi.quantity) as total_quantity
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                JOIN orders o ON oi.order_id = o.id
                JOIN payments p ON o.id = p.order_id
                WHERE o.status IN ('served', 'paid')
                AND DATE(o.order_time) BETWEEN :startDate AND :endDate
                GROUP BY mi.id, mi.name
                ORDER BY total_quantity DESC
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getSalesDataForChart($startDate, $endDate)
    {
        $stmt = $this->pdo->prepare("SELECT DATE_FORMAT(o.order_time, '%Y-%m-%d') as sale_date, SUM(o.total_amount) as daily_total
                FROM orders o
                JOIN payments p ON o.id = p.order_id
                WHERE o.status IN ('served', 'paid')
                AND DATE(o.order_time) BETWEEN :startDate AND :endDate
                GROUP BY sale_date
                ORDER BY sale_date ASC");
        $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getProductSalesTrendByProduct($startDate, $endDate, $categoryId, $searchTerm, $groupBy, $metric)
    {
        $dateFormats = ['day' => '%Y-%m-%d', 'month' => '%Y-%m', 'year' => '%Y'];
        $dateFormat = $dateFormats[$groupBy] ?? '%Y-%m-%d';
        $metricSql = $metric === 'produk' ? 'SUM(oi.quantity)' : 'SUM(oi.subtotal)';

        $sql = "SELECT DATE_FORMAT(o.order_time, '{$dateFormat}') as period, mi.name as product_name, {$metricSql} as value
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                JOIN orders o ON oi.order_id = o.id
                JOIN categories c ON mi.category_id = c.id
                JOIN payments p ON o.id = p.order_id
                WHERE o.status IN ('served', 'paid')
                AND DATE(o.order_time) BETWEEN :startDate AND :endDate";
        $params = [':startDate' => $startDate, ':endDate' => $endDate];
        if ($categoryId !== 'all' && is_numeric($categoryId)) {
            $sql .= " AND mi.category_id = :categoryId";
            $params[':categoryId'] = (int)$categoryId;
        }
        if (!empty($searchTerm)) {
            $sql .= " AND mi.name LIKE :searchTerm";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }
        $sql .= " GROUP BY period, mi.name ORDER BY period ASC, mi.name ASC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting product sales trend data: " . $e->getMessage());
            return [];
        }
    }
    
    public function getProductSalesByCategory($startDate, $endDate, $searchTerm = '')
    {
        $settings = $this->getSettings();
        $cogsRate = (float)($settings['cogs_percentage'] ?? 40) / 100;

        $sql = "SELECT
                    c.id as category_id,
                    c.name as category_name,
                    SUM(oi.quantity) as total_products_sold,
                    SUM(oi.subtotal) as total_sales,
                    SUM(
                        CASE
                            WHEN mi.cost > 0 THEN oi.quantity * mi.cost
                            ELSE oi.subtotal * :cogsRate
                        END
                    ) as total_cogs
                FROM categories c
                JOIN menu_items mi ON c.id = mi.category_id
                LEFT JOIN order_items oi ON mi.id = oi.menu_item_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status IN ('served', 'paid') AND DATE(o.order_time) BETWEEN :startDate AND :endDate
                WHERE 1";

        $params = [
            ':startDate' => $startDate,
            ':endDate' => $endDate,
            ':cogsRate' => $cogsRate,
        ];
        
        if (!empty($searchTerm)) {
            $sql .= " AND c.name LIKE :searchTerm";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }

        $sql .= " GROUP BY c.id, c.name
                  ORDER BY c.name ASC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting product sales by category report: " . $e->getMessage());
            return [];
        }
    }

    public function getCategorySalesTrend($startDate, $endDate, $searchTerm, $groupBy, $metric)
    {
        $dateFormats = ['day' => '%Y-%m-%d', 'month' => '%Y-%m', 'year' => '%Y', 'week' => '%Y-%v'];
        $dateFormat = $dateFormats[$groupBy] ?? '%Y-%m-%d';
        $metricSql = $metric === 'produk' ? 'SUM(oi.quantity)' : 'SUM(oi.subtotal)';
        
        $sql = "SELECT
                    DATE_FORMAT(o.order_time, '{$dateFormat}') as period,
                    c.name as category_name,
                    {$metricSql} as value
                FROM categories c
                JOIN menu_items mi ON c.id = mi.category_id
                JOIN order_items oi ON mi.id = oi.menu_item_id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.status IN ('served', 'paid')
                  AND DATE(o.order_time) BETWEEN :startDate AND :endDate";
        $params = [':startDate' => $startDate, ':endDate' => $endDate];
        
        if (!empty($searchTerm)) {
            $sql .= " AND c.name LIKE :searchTerm";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }

        $sql .= " GROUP BY period, c.name
                  ORDER BY period ASC, c.name ASC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting category sales trend data: " . $e->getMessage());
            return [];
        }
    }

    public function getCashTransactionsSummary($startDate, $endDate, $searchTerm = '')
    {
        $sql = "SELECT
                    ct.*,
                    u.username,
                    CASE ct.type WHEN 'in' THEN ct.amount ELSE 0 END as amount_in,
                    CASE ct.type WHEN 'out' THEN ct.amount ELSE 0 END as amount_out
                FROM cash_transactions ct
                JOIN users u ON ct.user_id = u.id
                WHERE DATE(ct.transaction_time) BETWEEN :startDate AND :endDate";

        $params = [
            ':startDate' => $startDate,
            ':endDate' => $endDate,
        ];
        
        if (!empty($searchTerm)) {
            $sql .= " AND (ct.category LIKE :searchTerm OR ct.notes LIKE :searchTerm OR u.username LIKE :searchTerm)";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }

        $sql .= " ORDER BY ct.transaction_time DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting cash transactions summary: " . $e->getMessage());
            return [];
        }
    }
}