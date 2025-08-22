<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;
use DateTime;
use PDOException; // Ditambahkan untuk penanganan error

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
        // LEFT JOIN diubah menjadi INNER JOIN ke payments untuk memastikan hanya transaksi yang sudah dibayar yang masuk.
        $sql = "SELECT {$columns}
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.id
                LEFT JOIN tables t ON o.table_id = t.id";

        $whereClauses = [];
        $params = [];

        // Filter utama sekarang berdasarkan tanggal pembayaran di tabel 'payments'
        $whereClauses[] = "DATE(p.payment_time) BETWEEN :startDate AND :endDate";
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
        $columns = "o.id as order_id,
                    o.order_number as transaction_no,
                    o.created_at as order_time,
                    p.payment_time,
                    COALESCE(t.table_number, 'Takeaway') as outlet,
                    CASE WHEN o.table_id IS NOT NULL THEN 'Dine-in' ELSE 'Takeaway' END as order_type,
                    o.total_amount as total_sales,
                    p.payment_method,
                    p.amount_paid";

        $queryInfo = $this->buildSalesQuery($columns, $startDate, $endDate, $filterBy, $searchTerm, 'all');
        
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
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status IN ('paid', 'served')");
        $stmt->execute();
        $summary['revenue'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;
        
        $stmt = $this->pdo->prepare("SELECT COUNT(id) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status IN ('paid', 'served')");
        $stmt->execute();
        $summary['orders'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;
        return $summary;
    }

    public function getPeriodFinancials($startDate, $endDate)
    {
        $settings = $this->getSettings();

        $taxRate = (float)($settings['tax_percentage'] ?? 11) / 100;
        $serviceRate = (float)($settings['service_charge_percentage'] ?? 5) / 100;
        $cogsRate = (float)($settings['cogs_percentage'] ?? 40) / 100;
        $promoRate = (float)($settings['default_promo_percentage'] ?? 0) / 100;
        $adminFee = (float)($settings['default_admin_fee'] ?? 0);
        $mdrRate = (float)($settings['default_mdr_fee_percentage'] ?? 0) / 100;
        $commissionRate = (float)($settings['default_commission_percentage'] ?? 0) / 100;

        $data = [
            'total_revenue' => 0.0, 'gross_sales' => 0.0, 'net_sales' => 0.0,
            'refunds' => 0.0, 'service_charge' => 0.0, 'tax' => 0.0,
            'other_revenue' => 0.0, 'total_orders' => 0, 'cogs' => 0.0,
            'total_promo' => 0.0, 'admin_fee' => $adminFee, 'mdr_fee' => 0.0,
            'commission' => 0.0, 'gross_profit' => 0.0, 'aov' => 0.0,
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
            
            $totalRate = 1 + $taxRate + $serviceRate;
            $data['gross_sales'] = $data['total_revenue'] / $totalRate;
            $data['tax'] = $data['gross_sales'] * $taxRate;
            $data['service_charge'] = $data['gross_sales'] * $serviceRate;
            
            $data['cogs'] = $data['gross_sales'] * $cogsRate;
            $data['total_promo'] = $data['gross_sales'] * $promoRate;
            $data['mdr_fee'] = $data['total_revenue'] * $mdrRate;
            $data['commission'] = $data['gross_sales'] * $commissionRate;

            $data['net_sales'] = $data['gross_sales'] - $data['total_promo'];
            
            $totalCost = $data['cogs'] + $data['total_promo'] + $data['admin_fee'] + $data['mdr_fee'] + $data['commission'];
            $data['gross_profit'] = $data['total_revenue'] - $totalCost;
            
            $data['aov'] = $data['total_orders'] > 0 ? $data['total_revenue'] / $data['total_orders'] : 0;
        }

        return $data;
    }

    public function getPaymentMethodSummary($startDate, $endDate)
    {
        $sql = "SELECT p.payment_method, SUM(p.amount_paid) as total_amount, COUNT(p.id) as transaction_count
                FROM payments p
                WHERE DATE(p.payment_time) BETWEEN :startDate AND :endDate
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

    /**
     * getProductSalesReportV2
     * Mengambil data laporan penjualan produk yang detail untuk tabel.
     * Termasuk placeholder untuk data refund yang belum didukung skema.
     */
    public function getProductSalesReportV2($startDate, $endDate, $categoryId = 'all', $searchTerm = '')
    {
        $sql = "SELECT
                    mi.id as product_id,
                    mi.name as product_name,
                    mi.cost as product_cost,
                    c.name as category_name,
                    SUM(oi.quantity) as total_quantity_sold,
                    SUM(oi.subtotal) as total_sales,
                    -- Placeholders for refund data as schema doesn't support per-item refund tracking
                    0.00 as total_refund_amount,
                    0 as total_refund_quantity,
                    (SUM(oi.quantity) * mi.cost) as total_cogs,
                    0.00 as total_cogs_refund, -- Placeholder
                    (SUM(oi.subtotal) - (SUM(oi.quantity) * mi.cost)) as gross_profit
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                JOIN orders o ON oi.order_id = o.id
                JOIN categories c ON mi.category_id = c.id
                WHERE o.status IN ('paid', 'served')
                AND DATE(o.order_time) BETWEEN :startDate AND :endDate";

        $params = [
            ':startDate' => $startDate,
            ':endDate' => $endDate,
        ];

        if ($categoryId !== 'all' && is_numeric($categoryId)) {
            $sql .= " AND mi.category_id = :categoryId";
            $params[':categoryId'] = (int)$categoryId;
        }

        if (!empty($searchTerm)) {
            $sql .= " AND mi.name LIKE :searchTerm";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }

        $sql .= " GROUP BY mi.id, mi.name, c.name, mi.cost
                  ORDER BY total_sales DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting V2 product sales report: " . $e->getMessage());
            return [];
        }
    }

    /**
     * getProductSalesChartData
     * Mengambil data agregat untuk grafik penjualan produk.
     */
    public function getProductSalesChartData($startDate, $endDate, $categoryId, $searchTerm, $groupBy, $metric)
    {
        $dateFormats = [
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            'year' => '%Y'
        ];
        $dateFormat = $dateFormats[$groupBy] ?? '%Y-%m-%d';

        $metricSql = $metric === 'produk' ? 'SUM(oi.quantity)' : 'SUM(oi.subtotal)';
        $metricLabel = $metric === 'produk' ? 'Jumlah Produk' : 'Penjualan (Rp)';

        $sql = "SELECT
                    DATE_FORMAT(o.order_time, '{$dateFormat}') as period,
                    {$metricSql} as value
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                JOIN orders o ON oi.order_id = o.id
                JOIN categories c ON mi.category_id = c.id
                WHERE o.status IN ('paid', 'served')
                AND DATE(o.order_time) BETWEEN :startDate AND :endDate";

        $params = [
            ':startDate' => $startDate,
            ':endDate' => $endDate,
        ];

        if ($categoryId !== 'all' && is_numeric($categoryId)) {
            $sql .= " AND mi.category_id = :categoryId";
            $params[':categoryId'] = (int)$categoryId;
        }

        if (!empty($searchTerm)) {
            $sql .= " AND mi.name LIKE :searchTerm";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }

        $sql .= " GROUP BY period ORDER BY period ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'labels' => array_column($results, 'period'),
                'datasets' => [
                    [
                        'label' => $metricLabel,
                        'data' => array_column($results, 'value'),
                        'backgroundColor' => 'rgba(79, 70, 229, 0.7)',
                        'borderColor' => 'rgba(79, 70, 229, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
        } catch (PDOException $e) {
            error_log("Error getting product sales chart data: " . $e->getMessage());
            return ['labels' => [], 'datasets' => []];
        }
    }
}