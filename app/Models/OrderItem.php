<?php
// File: app/Models/OrderItem.php
namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

/**
 * Class OrderItem
 * Model untuk tabel 'order_items'.
 */
class OrderItem extends Model {
    protected $table = 'order_items';

    /**
     * Mengambil semua item berdasarkan order ID.
     */
    public function findByOrderId(int $orderId): array {
        // ...(Kode findByOrderId lengkap seperti sebelumnya)...
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

     /**
     * Memasukkan beberapa item order sekaligus (dipanggil dalam transaksi).
     */
    public function createOrderItems(int $orderId, array $itemsData): bool {
        // ...(Kode createOrderItems lengkap seperti sebelumnya)...
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

    /**
     * Mendapatkan daftar item menu terpopuler (paling banyak dipesan kuantitasnya)
     * dalam periode tanggal tertentu, dari order yang statusnya relevan.
     */
    public function getPopularItems(string $startDate, string $endDate, int $limit = 5): array {
        // ...(Kode getPopularItems lengkap seperti sebelumnya)...
         if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) || $limit <= 0) {
              error_log("Invalid date format or limit for popular items report.");
              return [];
         }
        $endDateFull = $endDate . ' 23:59:59';
        $revenueStatuses = ['paid', 'served'];
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

} // Akhir kelas OrderItem
?>