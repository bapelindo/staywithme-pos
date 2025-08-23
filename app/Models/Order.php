<?php
// File: app/Models/Order.php
namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;
use DateTime;
use DateTimeZone; // Impor DateTimeZone
use App\Helpers\SanitizeHelper;

/**
 * Class Order
 * Model untuk tabel 'orders'.
 */
class Order extends Model {
    protected $table = 'orders';

    /**
     * Membuat order baru beserta item-itemnya dalam satu transaksi.
     */
    
    public function createOrder(int $tableId, array $items, ?string $notes = null): int|false {
        if (empty($items) || $tableId <= 0) {
            return false;
        }
        $orderNumber = $this->generateOrderNumber();
        if (!$orderNumber) {
             return false;
        }

        // 1. Ambil pengaturan pajak dan layanan
        $settingsModel = new \App\Models\Settings();
        $settings = $settingsModel->getAllSettings();
        $adminFee = (float)($settings['default_admin_fee'] ?? 0);
        $taxRate = (float)($settings['tax_percentage'] ?? 0) / 100;
        $serviceRate = (float)($settings['service_charge_percentage'] ?? 0) / 100;

        // Faktor pembagi untuk menghitung harga dasar dari harga inklusif
        $inclusiveFactor = 1 + $taxRate + $serviceRate;
        if ($inclusiveFactor == 0) $inclusiveFactor = 1; // Hindari pembagian dengan nol

        $itemIds = array_column($items, 'menu_item_id');
        if (empty($itemIds)) {
             return false;
        }
        
        // Inisialisasi total
        $totalAmount = 0.00; // Ini akan menjadi jumlah dari harga menu
        $totalTax = 0.00;
        $totalServiceCharge = 0.00;

        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $sqlGetPrices = "SELECT id, price FROM menu_items WHERE id IN ($placeholders) AND is_available = 1";
        
        try {
            $stmtPrices = $this->db->prepare($sqlGetPrices);
            $paramIndex = 1;
            foreach ($itemIds as $itemId) {
                 $stmtPrices->bindValue($paramIndex++, $itemId, PDO::PARAM_INT);
            }
            $stmtPrices->execute();
            $dbItems = $stmtPrices->fetchAll(PDO::FETCH_KEY_PAIR);
            
            if (count($dbItems) !== count($itemIds)) {
                 error_log("createOrder failed: one or more menu items not found or unavailable.");
                 return false;
            }

            $orderItemsData = [];
            foreach ($items as $item) {
                $menuItemId = (int)$item['menu_item_id'];
                $quantity = (int)$item['quantity'];
                if ($quantity <= 0 || !isset($dbItems[$menuItemId])) {
                    return false;
                }
                
                $priceAtOrder = (float)$dbItems[$menuItemId]; // Harga inklusif dari DB
                $subtotal = $priceAtOrder * $quantity;
                
                // Tambahkan subtotal ke total akhir
                $totalAmount += $subtotal;

                // Hitung mundur untuk menemukan harga dasar, pajak, dan layanan untuk subtotal ini
                $baseSubtotal = $subtotal / $inclusiveFactor;
                $taxForSubtotal = $baseSubtotal * $taxRate;
                $serviceForSubtotal = $baseSubtotal * $serviceRate;

                // Akumulasi total
                $totalTax += $taxForSubtotal;
                $totalServiceCharge += $serviceForSubtotal;

                $orderItemsData[] = [
                    'menu_item_id' => $menuItemId, 'quantity' => $quantity,
                    'price_at_order' => $priceAtOrder, 'subtotal' => $subtotal,
                    'notes' => isset($item['notes']) ? SanitizeHelper::string($item['notes']) : null
                ];
            }
            
            // Tambahkan admin fee jika ada
            $finalTotalAmount = $totalAmount + $adminFee;

        } catch (PDOException $e) {
            error_log("createOrder failed during price check/calculation: " . $e->getMessage());
            return false;
        }
        
        $this->db->beginTransaction();
        try {
            // **FIXED**: Menghapus kolom 'gross_sales' dari query INSERT
            $sqlOrder = "INSERT INTO {$this->table} (table_id, order_number, service_charge, tax, admin_fee, total_amount, status, notes, order_time)
                         VALUES (:table_id, :order_number, :service_charge, :tax, :admin_fee, :total_amount, 'pending_payment', :notes, NOW())";
            $stmtOrder = $this->db->prepare($sqlOrder);
            
            $stmtOrder->bindValue(':table_id', $tableId, PDO::PARAM_INT);
            $stmtOrder->bindValue(':order_number', $orderNumber, PDO::PARAM_STR);
            $stmtOrder->bindValue(':service_charge', (string)round($totalServiceCharge, 2), PDO::PARAM_STR);
            $stmtOrder->bindValue(':tax', (string)round($totalTax, 2), PDO::PARAM_STR);
            $stmtOrder->bindValue(':admin_fee', (string)$adminFee, PDO::PARAM_STR);
            $stmtOrder->bindValue(':total_amount', (string)round($finalTotalAmount, 2), PDO::PARAM_STR);
            $stmtOrder->bindValue(':notes', $notes ? SanitizeHelper::string($notes) : null, PDO::PARAM_STR);

            if (!$stmtOrder->execute()) throw new PDOException("Failed to insert order header.");
            $orderId = (int)$this->db->lastInsertId();
            
            $orderItemModel = new OrderItem();
            if (!$orderItemModel->createOrderItems($orderId, $orderItemsData)) throw new PDOException("Failed to insert order items.");
            
            $this->db->commit();
            return $orderId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("createOrder failed during transaction: " . $e->getMessage());
            return false;
        }
    }

     // ... (metode-metode lain seperti findById, generateOrderNumber tetap sama) ...
     public function findById($id): array|false {
         if ($id <= 0) return false;
         try {
             $sql = "SELECT o.*, t.table_number
                     FROM {$this->table} o
                     JOIN tables t ON o.table_id = t.id
                     WHERE o.id = :id LIMIT 1";
             $stmt = $this->db->prepare($sql);
             $stmt->bindParam(':id', $id, PDO::PARAM_INT);
             $stmt->execute();
             return $stmt->fetch(PDO::FETCH_ASSOC);
         } catch (PDOException $e) {
             error_log("Error finding order by ID {$id}: " . $e->getMessage());
             return false;
         }
     }
    private function generateOrderNumber(): string|false {
        $prefix = "SWM-";
        $timezone = new DateTimeZone('Asia/Jakarta');
        $datePart = (new DateTime('now', $timezone))->format('Ymd');
        $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(order_number, '-', -1) AS UNSIGNED)) as last_num
                FROM {$this->table} WHERE order_number LIKE :prefix_date";
        try {
            $stmt = $this->db->prepare($sql);
            $prefixDate = $prefix . $datePart . "-%";
            $stmt->bindParam(':prefix_date', $prefixDate, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $nextNum = ($result && $result['last_num'] !== null) ? (int)$result['last_num'] + 1 : 1;
            $sequencePart = str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
            return $prefix . $datePart . "-" . $sequencePart;
        } catch (PDOException $e) {
             error_log("Error generating order number sequence: " . $e->getMessage());
             return false;
        }
    }
    public function updateStatus(int $orderId, string $newStatus): bool {
        $allowedStatuses = ['pending_payment', 'received', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
        if ($orderId <= 0 || !in_array($newStatus, $allowedStatuses)) {
             error_log("Invalid status '{$newStatus}' or Order ID {$orderId}.");
             return false;
        }
        $sql = "UPDATE {$this->table} SET status = :status, updated_at = NOW() WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $orderId, PDO::PARAM_INT);
            $stmt->bindParam(':status', $newStatus, PDO::PARAM_STR);
            $success = $stmt->execute();
            return $success && ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            error_log("Error updating status for order ID {$orderId}: " . $e->getMessage());
            return false;
        }
    }
    public function findByStatus(string|array $status, string $orderBy = 'o.order_time ASC', ?int $limit = null): array {
        $statuses = is_array($status) ? $status : [$status];
        $allowedStatuses = ['pending_payment', 'received', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
        $validStatuses = array_intersect($statuses, $allowedStatuses);
        if (empty($validStatuses)) return [];
        $allowedOrderBy = ['o.order_time ASC', 'o.order_time DESC', 'o.updated_at ASC', 'o.updated_at DESC', 'o.id ASC', 'o.id DESC'];
        if (!in_array($orderBy, $allowedOrderBy)) $orderBy = 'o.order_time ASC';
        $placeholders = implode(',', array_fill(0, count($validStatuses), '?'));
        $sql = "SELECT o.*, t.table_number FROM {$this->table} o JOIN tables t ON o.table_id = t.id WHERE o.status IN ($placeholders) ORDER BY {$orderBy}";
        if ($limit !== null && $limit > 0) $sql .= " LIMIT ?";
        try {
            $stmt = $this->db->prepare($sql);
            $paramIndex = 1;
            foreach ($validStatuses as $st) $stmt->bindValue($paramIndex++, $st, PDO::PARAM_STR);
            if ($limit !== null && $limit > 0) $stmt->bindValue($paramIndex++, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching orders by status: " . $e->getMessage()); return [];
        }
    }
     public function getActiveOrdersForDisplay(array $statuses = ['received', 'preparing'], ?int $lastOrderId = null): array {
         $allowedStatuses = ['pending_payment', 'received', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
         $validStatuses = array_intersect($statuses, $allowedStatuses);
         if (empty($validStatuses)) return [];
         $placeholders = implode(',', array_fill(0, count($validStatuses), '?'));
         $sql = "SELECT o.id, o.order_number, o.status, o.order_time, t.table_number FROM {$this->table} o JOIN tables t ON o.table_id = t.id WHERE o.status IN ($placeholders)";
         $params = $validStatuses;
         if ($lastOrderId !== null && $lastOrderId > 0) { $sql .= " AND o.id > ?"; $params[] = $lastOrderId; }
         $sql .= " ORDER BY o.order_time ASC";
         try {
             $stmt = $this->db->prepare($sql);
             $paramIndex = 1;
             foreach ($validStatuses as $st) $stmt->bindValue($paramIndex++, $st, PDO::PARAM_STR);
             if ($lastOrderId !== null && $lastOrderId > 0) $stmt->bindValue($paramIndex++, $lastOrderId, PDO::PARAM_INT);
             $stmt->execute();
             return $stmt->fetchAll(PDO::FETCH_ASSOC);
         } catch (PDOException $e) {
             error_log("Error fetching active orders for display: " . $e->getMessage()); return [];
         }
     }
    public function getOrderWithDetails(int $orderId): ?array {
        $sql = "SELECT
                    o.id, o.order_number, o.table_id, o.status, o.total_amount,
                    o.notes AS order_notes, o.order_time, o.created_at, o.updated_at,
                    t.table_number,
                    t.qr_code_identifier
                FROM orders o
                JOIN tables t ON o.table_id = t.id
                WHERE o.id = :order_id";
        
        try { 
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                 error_log("PDO prepare failed for getOrderWithDetails: " . implode(":", $this->db->errorInfo()));
                 return null;
            }
            $stmt->bindParam(':order_id', $orderId, \PDO::PARAM_INT);
            $stmt->execute();
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                return null;
            }

            $itemSql = "SELECT
                            oi.quantity, oi.price_at_order, oi.subtotal, oi.notes,
                            mi.name as menu_item_name, mi.image_path
                        FROM order_items oi
                        JOIN menu_items mi ON oi.menu_item_id = mi.id
                        WHERE oi.order_id = :order_id";
            $itemStmt = $this->db->prepare($itemSql);
             if (!$itemStmt) {
                 error_log("PDO prepare failed for order items: " . implode(":", $this->db->errorInfo()));
                 $order['items'] = [];
             } else {
                $itemStmt->bindParam(':order_id', $orderId, \PDO::PARAM_INT);
                $itemStmt->execute();
                $order['items'] = $itemStmt->fetchAll(\PDO::FETCH_ASSOC);
             }

            return $order;

        } catch (\PDOException $e) {
             error_log("PDO Exception in getOrderWithDetails for order ID {$orderId}: " . $e->getMessage());
             return null;
        }
    }
     public function countByTableId(int $tableId): int {
          if ($tableId <= 0) return 0;
         try {
             $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE table_id = :table_id");
             $stmt->bindParam(':table_id', $tableId, PDO::PARAM_INT);
             $stmt->execute();
             return (int)$stmt->fetchColumn();
         } catch (PDOException $e) {
             error_log("Error counting orders for table ID {$tableId}: " . $e->getMessage()); return 0;
         }
     }
    public function countByStatus(string|array $status): int {
        $statuses = is_array($status) ? $status : [$status];
        $allowedStatuses = ['pending_payment', 'received', 'preparing', 'ready', 'served', 'cancelled'];
        $validStatuses = array_intersect($statuses, $allowedStatuses);
        if (empty($validStatuses)) return 0;

        $placeholders = implode(',', array_fill(0, count($validStatuses), '?'));
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE status IN ($placeholders)";
        try {
            $stmt = $this->db->prepare($sql);
            $paramIndex = 1;
            foreach ($validStatuses as $st) $stmt->bindValue($paramIndex++, $st, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting orders by status: " . $e->getMessage()); return 0;
        }
    }
    public function getAllOrdersPaginated(int $limit, int $offset): array {
         $sql = "SELECT o.*, t.table_number FROM {$this->table} o JOIN tables t ON o.table_id = t.id ORDER BY o.order_time DESC LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all orders paginated: " . $e->getMessage()); return [];
        }
    }
    public function countAllOrders(): int {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting all orders: " . $e->getMessage()); return 0;
        }
    }
     public function getOrdersByStatusPaginated(string $status, int $limit, int $offset): array {
         $allowedStatuses = ['pending_payment', 'received', 'preparing', 'ready', 'served', 'cancelled'];

        if (!in_array($status, $allowedStatuses)) {
             error_log("Attempted to filter orders by invalid status: {$status}");
             return [];
        }

        $sql = "SELECT o.*, t.table_number
                FROM {$this->table} o
                JOIN tables t ON o.table_id = t.id
                WHERE o.status = :status
                ORDER BY o.order_time DESC
                LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching orders by status '{$status}' paginated: " . $e->getMessage());
            return [];
        }
    }
      public function getNewOrdersSince(int $lastSeenId, string $status): array {
        $allowedStatuses = ['pending_payment', 'received', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) {
            error_log("Attempted to get new orders with invalid status: {$status}");
            return [];
        }

        $sql = "SELECT o.id, o.order_number, o.status, o.order_time, t.table_number
                FROM {$this->table} o
                JOIN tables t ON o.table_id = t.id
                WHERE o.status = :status AND o.id > :last_seen_id
                ORDER BY o.id ASC";

       try {
           $stmt = $this->db->prepare($sql);
           $stmt->bindParam(':status', $status, PDO::PARAM_STR);
           $stmt->bindParam(':last_seen_id', $lastSeenId, PDO::PARAM_INT);
           $stmt->execute();
           return $stmt->fetchAll(PDO::FETCH_ASSOC);
       } catch (PDOException $e) {
           error_log("Error fetching new orders with status '{$status}' since ID {$lastSeenId}: " . $e->getMessage());
           return [];
       }
    }
    public function getSalesReportSummary(string $startDate, string $endDate): array {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
             error_log("Invalid date format for sales report summary.");
             return ['total_orders' => 0, 'total_revenue' => 0.00, 'average_order_value' => 0.00];
        }
        $endDateFull = $endDate . ' 23:59:59';
        $revenueStatuses = ['paid', 'served'];
        $placeholders = implode(',', array_fill(0, count($revenueStatuses), '?'));
        $sql = "SELECT COUNT(*) as total_orders, SUM(total_amount) as total_revenue FROM {$this->table} WHERE status IN ($placeholders) AND order_time BETWEEN ? AND ?";
        try {
            $params = array_merge($revenueStatuses, [$startDate, $endDateFull]);
            $stmt = $this->db->prepare($sql);
            $paramIndex = 1;
            foreach($revenueStatuses as $st) $stmt->bindValue($paramIndex++, $st, PDO::PARAM_STR);
            $stmt->bindValue($paramIndex++, $startDate, PDO::PARAM_STR);
            $stmt->bindValue($paramIndex++, $endDateFull, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $summary = [
                'total_orders' => $result['total_orders'] ? (int)$result['total_orders'] : 0,
                'total_revenue' => $result['total_revenue'] ? (float)$result['total_revenue'] : 0.00,
                'average_order_value' => 0.00
            ];
            if ($summary['total_orders'] > 0) $summary['average_order_value'] = $summary['total_revenue'] / $summary['total_orders'];
            return $summary;
        } catch (PDOException $e) {
            error_log("Error getting sales report summary ({$startDate} - {$endDate}): " . $e->getMessage());
            return ['total_orders' => 0, 'total_revenue' => 0.00, 'average_order_value' => 0.00];
        }
    }
    public function getSalesTrendData(string $startDate, string $endDate): array {
         if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
              error_log("Invalid date format for sales trend data.");
              return ['labels'=>[], 'revenue'=>[], 'orders'=>[]];
         }
        $endDateFull = $endDate . ' 23:59:59';
        $revenueStatuses = ['paid', 'served'];
        $placeholders = implode(',', array_fill(0, count($revenueStatuses), '?'));

        $sql = "SELECT
                    DATE(order_time) as order_date,
                    COUNT(*) as daily_orders,
                    SUM(total_amount) as daily_revenue
                FROM {$this->table}
                WHERE status IN ($placeholders)
                AND order_time BETWEEN ? AND ?
                GROUP BY order_date
                ORDER BY order_date ASC";

        $chartData = ['labels' => [], 'revenue' => [], 'orders' => []];

        try {
            $params = array_merge($revenueStatuses, [$startDate, $endDateFull]);
            $stmt = $this->db->prepare($sql);
            $paramIndex = 1;
            foreach($revenueStatuses as $st) $stmt->bindValue($paramIndex++, $st, PDO::PARAM_STR);
            $stmt->bindValue($paramIndex++, $startDate, PDO::PARAM_STR);
            $stmt->bindValue($paramIndex++, $endDateFull, PDO::PARAM_STR);

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $currentDate = new DateTime($startDate);
            $endDateObj = new DateTime($endDate);
            $dataMap = [];
            foreach ($results as $row) {
                $dataMap[$row['order_date']] = $row;
            }

            while ($currentDate <= $endDateObj) {
                $dateString = $currentDate->format('Y-m-d');
                $chartData['labels'][] = $dateString;
                if (isset($dataMap[$dateString])) {
                    $chartData['revenue'][] = (float)$dataMap[$dateString]['daily_revenue'];
                    $chartData['orders'][] = (int)$dataMap[$dateString]['daily_orders'];
                } else {
                    $chartData['revenue'][] = 0;
                    $chartData['orders'][] = 0;
                }
                $currentDate->modify('+1 day');
            }

        } catch (PDOException $e) {
            error_log("Error getting sales trend data ({$startDate} - {$endDate}): " . $e->getMessage());
            return ['labels' => [], 'revenue' => [], 'orders' => []];
        }

        return $chartData;
    }

}
?>