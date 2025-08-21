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

    public function getFullSalesReport($startDate, $endDate)
    {
        $report = [];

        // Data periode saat ini
        $report['current_period'] = $this->getPeriodFinancials($startDate, $endDate);

        // Hitung rentang periode sebelumnya
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
        $interval = $startDateTime->diff($endDateTime);
        $daysDifference = $interval->days + 1;

        $prevEndDate = date('Y-m-d', strtotime($startDate . ' -1 day'));
        $prevStartDate = date('Y-m-d', strtotime($prevEndDate . ' -' . ($daysDifference - 1) . ' days'));

        // Data periode sebelumnya
        $report['previous_period'] = $this->getPeriodFinancials($prevStartDate, $prevEndDate);
        $report['previous_period_range'] = ['start' => $prevStartDate, 'end' => $prevEndDate];

        // Data tambahan untuk periode saat ini
        $report['payment_methods'] = $this->getPaymentMethodSummary($startDate, $endDate);
        $report['today'] = $this->getTodaySummary();

        return $report;
    }

    private function getTodaySummary()
    {
        $summary = [];
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'paid'");
        $stmt->execute();
        $summary['revenue'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

        $stmt = $this->pdo->prepare("SELECT COUNT(id) as total FROM orders WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $summary['orders'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;
        return $summary;
    }

    public function getPeriodFinancials($startDate, $endDate)
    {
        $data = [];

        // Query untuk mengambil semua data keuangan dari orders
        $sql = "SELECT 
            SUM(total_amount) as gross_sales,
            SUM(shipping_cost) as shipping_cost,
            SUM(service_charge) as service_charge,
            SUM(mdr_service_fee) as mdr_service_fee,
            SUM(rounding) as rounding,
            SUM(tax) as tax,
            SUM(other_revenue) as other_revenue,
            SUM(purchase_promo) as purchase_promo,
            SUM(product_promo) as product_promo,
            SUM(complimentary) as complimentary,
            SUM(admin_fee) as admin_fee,
            SUM(refunds) as refunds,
            SUM(mdr_fee) as mdr_fee,
            SUM(commission) as commission
            FROM orders 
            WHERE status = 'paid' 
            AND DATE(created_at) BETWEEN :startDate AND :endDate";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kalkulasi Total Pendapatan
        $data['gross_sales'] = (float)($result['gross_sales'] ?? 0);
        $data['shipping_cost'] = (float)($result['shipping_cost'] ?? 0);
        $data['service_charge'] = (float)($result['service_charge'] ?? 0);
        $data['mdr_service_fee'] = (float)($result['mdr_service_fee'] ?? 0);
        $data['rounding'] = (float)($result['rounding'] ?? 0);
        $data['tax'] = (float)($result['tax'] ?? 0);
        $data['other_revenue'] = (float)($result['other_revenue'] ?? 0);

        $data['total_revenue'] = $data['gross_sales'] + 
                                $data['shipping_cost'] + 
                                $data['service_charge'] + 
                                $data['mdr_service_fee'] + 
                                $data['rounding'] + 
                                $data['tax'] + 
                                $data['other_revenue'];

        // Kalkulasi Biaya Promosi
        $data['purchase_promo'] = (float)($result['purchase_promo'] ?? 0);
        $data['product_promo'] = (float)($result['product_promo'] ?? 0);
        $data['complimentary'] = (float)($result['complimentary'] ?? 0);
        $data['total_promo'] = $data['purchase_promo'] + $data['product_promo'] + $data['complimentary'];

        // Biaya Administrasi
        $data['admin_fee'] = (float)($result['admin_fee'] ?? 0);

        // Total Penjualan
        $data['total_sales'] = $data['total_revenue'] - $data['total_promo'] - $data['admin_fee'];

        // Pengembalian
        $data['refunds'] = (float)($result['refunds'] ?? 0);
        $data['net_sales'] = $data['total_sales'] - $data['refunds'];

        // HPP dan Biaya Lainnya
        $data['mdr_fee'] = (float)($result['mdr_fee'] ?? 0);
        $data['commission'] = (float)($result['commission'] ?? 0);

        // Get COGS
        $sql = "SELECT SUM(oi.quantity * mi.cost) as total_cogs 
                FROM order_items oi 
                JOIN menu_items mi ON oi.menu_item_id = mi.id 
                JOIN orders o ON oi.order_id = o.id 
                WHERE o.status = 'paid' 
                AND DATE(o.created_at) BETWEEN :startDate AND :endDate";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        $data['cogs'] = (float)($stmt->fetch(PDO::FETCH_OBJ)->total_cogs ?? 0);

        // Total Laba Kotor
        $data['gross_profit'] = $data['net_sales'] - $data['mdr_fee'] - $data['cogs'] - $data['commission'];

        // Additional data for reports
        $stmt = $this->pdo->prepare("SELECT COUNT(id) as total FROM orders WHERE status = 'paid' AND DATE(created_at) BETWEEN :startDate AND :endDate");
        $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        $data['total_orders'] = (int)($stmt->fetch(PDO::FETCH_OBJ)->total ?? 0);
        $data['aov'] = ($data['total_orders'] > 0) ? ($data['total_revenue'] / $data['total_orders']) : 0;

        return $data;
    }

    public function getPaymentMethodSummary($startDate, $endDate)
    {
        $sql = "SELECT p.payment_method, SUM(p.amount_paid) as total_amount, COUNT(p.id) as transaction_count
                FROM payments p
                JOIN orders o ON p.order_id = o.id
                WHERE o.status = 'paid' AND DATE(o.created_at) BETWEEN :startDate AND :endDate
                GROUP BY p.payment_method";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getTopSellingItems($startDate, $endDate, $limit = 5)
    {
        $sql = "SELECT mi.name, SUM(oi.quantity) as total_quantity FROM order_items oi JOIN menu_items mi ON oi.menu_item_id = mi.id JOIN orders o ON oi.order_id = o.id WHERE o.status = 'paid' AND DATE(o.created_at) BETWEEN :startDate AND :endDate GROUP BY mi.id, mi.name ORDER BY total_quantity DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getSalesDataForChart($startDate, $endDate)
    {
        $stmt = $this->pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as sale_date, SUM(total_amount) as daily_total FROM orders WHERE status = 'paid' AND DATE(created_at) BETWEEN :startDate AND :endDate GROUP BY sale_date ORDER BY sale_date ASC");
        $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        $labels = [];
        $data = [];
        foreach ($results as $row) {
            $labels[] = $row->sale_date;
            $data[] = $row->daily_total;
        }
        return ['labels' => $labels, 'data' => $data];
    }
}