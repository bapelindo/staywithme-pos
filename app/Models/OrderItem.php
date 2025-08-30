<?php
// File: app/Models/OrderItem.php
namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class OrderItem extends Model {
    protected $table = 'order_items';

    public function countByMenuItemId(int $menuItemId): int {
        if ($menuItemId <= 0) return 0;
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE menu_item_id = :menu_item_id");
            $stmt->bindParam(':menu_item_id', $menuItemId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error counting order items for menu item ID {$menuItemId}: " . $e->getMessage());
            return 0;
        }
    }
    
    public function findByOrderId(int $orderId): array {
         if ($orderId <= 0) return [];
         $sql = "SELECT oi.*, mi.name as menu_item_name, mi.image_path
                FROM {$this->table} oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE oi.order_id = :order_id
                ORDER BY oi.id ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
             error_log("Error fetching order items for order ID {$orderId}: " . $e->getMessage());
             return [];
        }
    }

    public function createOrderItems(int $orderId, array $itemsData): bool {
         if (empty($itemsData) || $orderId <= 0) return false;
         $sql = "INSERT INTO {$this->table} (order_id, menu_item_id, quantity, price_at_order, subtotal, notes)
                 VALUES (:order_id, :menu_item_id, :quantity, :price_at_order, :subtotal, :notes)";
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($itemsData as $item) {
                 if (!isset($item['menu_item_id'], $item['quantity'], $item['price_at_order'], $item['subtotal']) ||
                     !is_numeric($item['menu_item_id']) || $item['menu_item_id'] <= 0 ||
                     !is_numeric($item['quantity']) || $item['quantity'] <= 0 ||
                     !is_numeric($item['price_at_order']) || !is_numeric($item['subtotal']))
                 { error_log("Invalid item data structure provided for order ID {$orderId}"); return false; }
                $menuItemId = (int)$item['menu_item_id'];
                $quantity = (int)$item['quantity'];
                $priceStr = (string)$item['price_at_order'];
                $subtotalStr = (string)$item['subtotal'];
                $notes = $item['notes'] ?? null;
                $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
                $stmt->bindParam(':menu_item_id', $menuItemId, PDO::PARAM_INT);
                $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stmt->bindParam(':price_at_order', $priceStr, PDO::PARAM_STR);
                $stmt->bindParam(':subtotal', $subtotalStr, PDO::PARAM_STR);
                $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
                if (!$stmt->execute()) { error_log("Failed insert item for order ID {$orderId}, item ID {$menuItemId}"); return false; }
            }
            return true;
        } catch (PDOException $e) {
             error_log("Error creating order items for order ID {$orderId}: " . $e->getMessage());
             if ($e->getCode() == 23000) { return false; }
             return false;
        }
    }

    public function getPopularItems(string $startDate, string $endDate, int $limit = 5): array {
         if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) || $limit <= 0) {
              error_log("Invalid date format or limit for popular items report.");
              return [];
         }
        $endDateFull = $endDate . ' 23:59:59';
        // PERBAIKAN: Hanya gunakan status 'served' untuk laporan
        $revenueStatuses = ['served']; 
        $placeholders = implode(',', array_fill(0, count($revenueStatuses), '?'));
        $sql = "SELECT mi.name as menu_item_name, SUM(oi.quantity) as total_quantity
                FROM {$this->table} oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.status IN ($placeholders) AND o.order_time BETWEEN ? AND ?
                GROUP BY oi.menu_item_id, mi.name
                ORDER BY total_quantity DESC LIMIT ?";
        try {
            $stmt = $this->db->prepare($sql);
            $paramIndex = 1;
            foreach ($revenueStatuses as $status) $stmt->bindValue($paramIndex++, $status, PDO::PARAM_STR);
            $stmt->bindValue($paramIndex++, $startDate, PDO::PARAM_STR);
            $stmt->bindValue($paramIndex++, $endDateFull, PDO::PARAM_STR);
            $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting popular items ({$startDate} - {$endDate}): " . $e->getMessage());
            return [];
        }
    }

    public function getRevenueByCategory(string $startDate, string $endDate): array {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
             error_log("Invalid date format for revenue by category report.");
             return [];
        }
       $endDateFull = $endDate . ' 23:59:59';
       // PERBAIKAN: Hanya gunakan status 'served' untuk laporan
       $revenueStatuses = ['served'];
       $placeholders = implode(',', array_fill(0, count($revenueStatuses), '?'));

       $sql = "SELECT
                   c.id as category_id,
                   c.name as category_name,
                   SUM(oi.subtotal) as total_revenue
               FROM {$this->table} oi
               JOIN menu_items mi ON oi.menu_item_id = mi.id
               JOIN categories c ON mi.category_id = c.id
               JOIN orders o ON oi.order_id = o.id
               WHERE o.status IN ($placeholders)
               AND o.order_time BETWEEN ? AND ?
               GROUP BY c.id, c.name
               ORDER BY total_revenue DESC";

       try {
           $stmt = $this->db->prepare($sql);
           $paramIndex = 1;
           foreach ($revenueStatuses as $status) {
                $stmt->bindValue($paramIndex++, $status, PDO::PARAM_STR);
           }
           $stmt->bindValue($paramIndex++, $startDate, PDO::PARAM_STR);
           $stmt->bindValue($paramIndex++, $endDateFull, PDO::PARAM_STR);

           $stmt->execute();
           return $stmt->fetchAll(PDO::FETCH_ASSOC);
       } catch (PDOException $e) {
           error_log("Error getting revenue by category ({$startDate} - {$endDate}): " . $e->getMessage());
           return [];
       }
   }

}