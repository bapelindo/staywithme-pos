<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Report extends Model
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getSalesSummary()
    {
        $summary = [];
        // Pendapatan Hari Ini
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'paid'");
        $stmt->execute();
        $summary['daily_revenue'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

        // Pesanan Hari Ini
        $stmt = $this->pdo->prepare("SELECT COUNT(id) as total FROM orders WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $summary['daily_orders'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

        // Total Pendapatan
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE status = 'paid'");
        $stmt->execute();
        $summary['total_revenue'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;

        // Total Pesanan Selesai
        $stmt = $this->pdo->prepare("SELECT COUNT(id) as total FROM orders WHERE status = 'paid'");
        $stmt->execute();
        $summary['total_completed_orders'] = $stmt->fetch(PDO::FETCH_OBJ)->total ?? 0;
        
        return $summary;
    }

    public function getTopSellingItems($limit = 5)
    {
        $sql = "SELECT mi.name, SUM(oi.quantity) as total_quantity FROM order_items oi JOIN menu_items mi ON oi.menu_item_id = mi.id JOIN orders o ON oi.order_id = o.id WHERE o.status = 'paid' GROUP BY mi.id, mi.name ORDER BY total_quantity DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getSalesDataForChart()
    {
        $stmt = $this->pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as sale_date, SUM(total_amount) as daily_total FROM orders WHERE status = 'paid' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY sale_date ORDER BY sale_date ASC");
        $stmt->execute();
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