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

    // --- METHOD findById, createOrder, generateOrderNumber, updateStatus, dll DARI SEBELUMNYA ---
    // ...(Kode method-method yang sudah ada sebelumnya tetap di sini)...

     /**
     * Mencari order berdasarkan ID.
     */
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

    /**
     * Membuat order baru beserta item-itemnya dalam satu transaksi.
     */
    public function createOrder(int $tableId, array $items, ?string $notes = null): int|false {
        // ...(Kode createOrder lengkap seperti sebelumnya)...
        if (empty($items) || $tableId <= 0) {
            error_log("createOrder failed: Invalid input provided.");
            return false;
        }
        $orderNumber = $this->generateOrderNumber();
        if (!$orderNumber) {
             error_log("createOrder failed: Could not generate order number.");
             return false;
        }
        $menuItemModel = new MenuItem();
        $itemIds = array_column($items, 'menu_item_id');
        if (empty($itemIds)) {
             error_log("createOrder failed: No valid menu item IDs provided.");
             return false;
        }
        $itemPrices = [];
        $totalAmount = 0.00;
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
                 error_log("createOrder failed: Some menu items not found or are unavailable.");
                 return false;
            }
            $orderItemsData = [];
            foreach ($items as $item) {
                $menuItemId = (int)$item['menu_item_id'];
                $quantity = (int)$item['quantity'];
                if ($quantity <= 0 || !isset($dbItems[$menuItemId])) {
                    error_log("createOrder failed: Invalid quantity or item ID {$menuItemId} not found in fetched prices.");
                    return false;
                }
                $priceAtOrder = (float)$dbItems[$menuItemId];
                $subtotal = $priceAtOrder * $quantity;
                $totalAmount += $subtotal;
                $orderItemsData[] = [
                    'menu_item_id' => $menuItemId, 'quantity' => $quantity,
                    'price_at_order' => $priceAtOrder, 'subtotal' => $subtotal,
                    'notes' => isset($item['notes']) ? SanitizeHelper::string($item['notes']) : null
                ];
            }
        } catch (PDOException $e) {
            error_log("createOrder failed during price check: " . $e->getMessage());
            return false;
        }
         if (empty($orderItemsData)) {
             error_log("createOrder failed: No valid items to process after validation.");
             return false;
         }
        $this->db->beginTransaction();
        try {
            $sqlOrder = "INSERT INTO {$this->table} (table_id, order_number, total_amount, status, notes, order_time, created_at, updated_at)
                         VALUES (:table_id, :order_number, :total_amount, 'received', :notes, NOW(), NOW(), NOW())";
            $stmtOrder = $this->db->prepare($sqlOrder);
            $stmtOrder->bindParam(':table_id', $tableId, PDO::PARAM_INT);
            $stmtOrder->bindParam(':order_number', $orderNumber, PDO::PARAM_STR);
            $totalAmountStr = (string)$totalAmount;
            $stmtOrder->bindParam(':total_amount', $totalAmountStr, PDO::PARAM_STR);
            $safeNotes = $notes ? SanitizeHelper::string($notes) : null;
            $stmtOrder->bindParam(':notes', $safeNotes, PDO::PARAM_STR);
            if (!$stmtOrder->execute()) throw new PDOException("Failed to insert order header.");
            $orderId = (int)$this->db->lastInsertId();
             if ($orderId == 0) throw new PDOException("Failed to get last insert ID for order.");
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

    /**
     * Generate nomor order unik (Contoh: STW-YYYYMMDD-NNNN)
     */
    private function generateOrderNumber(): string|false {
        // ...(Kode generateOrderNumber lengkap seperti sebelumnya)...
        $prefix = "STW-";
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

    /**
     * Mengupdate status order.
     */
    public function updateStatus(int $orderId, string $newStatus): bool {
         // ...(Kode updateStatus lengkap seperti sebelumnya)...
        $allowedStatuses = ['pending', 'received', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
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

    /**
     * Mengambil order berdasarkan status tertentu, diurutkan.
     */
    public function findByStatus(string|array $status, string $orderBy = 'o.order_time ASC', ?int $limit = null): array {
         // ...(Kode findByStatus lengkap seperti sebelumnya)...
        $statuses = is_array($status) ? $status : [$status];
        $allowedStatuses = ['pending', 'received', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
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

     /**
      * Mengambil order aktif (received, preparing) untuk display KDS/polling.
      */
     public function getActiveOrdersForDisplay(array $statuses = ['received', 'preparing'], ?int $lastOrderId = null): array {
         // ...(Kode getActiveOrdersForDisplay lengkap seperti sebelumnya)...
         $allowedStatuses = ['pending', 'received', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
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


    /**
     * Mengambil detail lengkap order beserta item-itemnya.
     */
    public function getOrderWithDetails(int $orderId): array|false {
        // ...(Kode getOrderWithDetails lengkap seperti sebelumnya)...
         if ($orderId <= 0) return false;
         $sqlOrder = "SELECT o.*, t.table_number FROM {$this->table} o JOIN tables t ON o.table_id = t.id WHERE o.id = :id LIMIT 1";
         try {
            $stmtOrder = $this->db->prepare($sqlOrder);
            $stmtOrder->bindParam(':id', $orderId, PDO::PARAM_INT);
            $stmtOrder->execute();
            $orderData = $stmtOrder->fetch(PDO::FETCH_ASSOC);
            if (!$orderData) return false;
            $orderItemModel = new OrderItem();
            $itemsData = $orderItemModel->findByOrderId($orderId);
            $orderData['items'] = $itemsData;
            return $orderData;
         } catch (PDOException $e) {
             error_log("Error fetching order details for ID {$orderId}: " . $e->getMessage()); return false;
         }
    }

    /**
      * Menghitung jumlah order berdasarkan table_id.
      */
     public function countByTableId(int $tableId): int {
         // ...(Kode countByTableId lengkap seperti sebelumnya)...
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

    /**
     * Menghitung jumlah order berdasarkan satu atau beberapa status.
     */
    public function countByStatus(string|array $status): int {
         // ...(Kode countByStatus lengkap seperti sebelumnya)...
        $statuses = is_array($status) ? $status : [$status];
        $allowedStatuses = ['pending', 'received', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
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

    /**
     * Mengambil semua order dengan paginasi, diurutkan berdasarkan waktu order terbaru.
     */
    public function getAllOrdersPaginated(int $limit, int $offset): array {
        // ...(Kode getAllOrdersPaginated lengkap seperti sebelumnya)...
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

    /**
     * Menghitung total semua order dalam database.
     */
    public function countAllOrders(): int {
        // ...(Kode countAllOrders lengkap seperti sebelumnya)...
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting all orders: " . $e->getMessage()); return 0;
        }
    }

     /**
     * Mengambil order berdasarkan status dengan paginasi, urut terbaru.
     */
    public function getOrdersByStatusPaginated(string $status, int $limit, int $offset): array {
        // ...(Kode getOrdersByStatusPaginated lengkap seperti sebelumnya)...
        $allowedStatuses = ['pending', 'received', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) return [];
        $sql = "SELECT o.*, t.table_number FROM {$this->table} o JOIN tables t ON o.table_id = t.id WHERE o.status = :status ORDER BY o.order_time DESC LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching orders by status '{$status}' paginated: " . $e->getMessage()); return [];
        }
    }

     /**
      * Mengambil order baru dengan status tertentu sejak ID terakhir yang dilihat.
      */
     public function getNewOrdersSince(int $lastSeenId, string $status): array {
         // ...(Kode getNewOrdersSince lengkap seperti sebelumnya)...
         $allowedStatuses = ['pending', 'received', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
         if (!in_array($status, $allowedStatuses)) return [];
         $sql = "SELECT o.id, o.order_number, o.status, o.order_time, t.table_number FROM {$this->table} o JOIN tables t ON o.table_id = t.id WHERE o.status = :status AND o.id > :last_seen_id ORDER BY o.id ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':last_seen_id', $lastSeenId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching new orders with status '{$status}' since ID {$lastSeenId}: " . $e->getMessage()); return [];
        }
     }

    /**
     * Mengambil ringkasan data penjualan untuk periode tanggal tertentu.
     */
    public function getSalesReportSummary(string $startDate, string $endDate): array {
        // ...(Kode getSalesReportSummary lengkap seperti sebelumnya)...
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

    /**
     * >> BARU <<
     * Mengambil data tren penjualan harian (jumlah order & pendapatan)
     * dalam rentang tanggal tertentu untuk grafik.
     *
     * @param string $startDate Format YYYY-MM-DD.
     * @param string $endDate Format YYYY-MM-DD.
     * @return array Data untuk Chart.js ['labels' => [], 'revenue' => [], 'orders' => []].
     */
    public function getSalesTrendData(string $startDate, string $endDate): array {
         if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
              error_log("Invalid date format for sales trend data.");
              return ['labels'=>[], 'revenue'=>[], 'orders'=>[]];
         }
        $endDateFull = $endDate . ' 23:59:59';
        $revenueStatuses = ['paid', 'served']; // Status yg dihitung
        $placeholders = implode(',', array_fill(0, count($revenueStatuses), '?'));

        // Query untuk agregasi per hari
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
             // Bind parameter secara manual
            $paramIndex = 1;
            foreach($revenueStatuses as $st) $stmt->bindValue($paramIndex++, $st, PDO::PARAM_STR);
            $stmt->bindValue($paramIndex++, $startDate, PDO::PARAM_STR);
            $stmt->bindValue($paramIndex++, $endDateFull, PDO::PARAM_STR);

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Proses hasil query ke format Chart.js
            // Opsional: Isi gap tanggal jika ada hari tanpa penjualan
            $currentDate = new DateTime($startDate);
            $endDateObj = new DateTime($endDate);
            $dataMap = [];
            foreach ($results as $row) {
                $dataMap[$row['order_date']] = $row;
            }

            while ($currentDate <= $endDateObj) {
                $dateString = $currentDate->format('Y-m-d');
                $chartData['labels'][] = $dateString; // Atau format tanggal lain yg diinginkan
                if (isset($dataMap[$dateString])) {
                    $chartData['revenue'][] = (float)$dataMap[$dateString]['daily_revenue'];
                    $chartData['orders'][] = (int)$dataMap[$dateString]['daily_orders'];
                } else {
                    // Jika tidak ada data di hari itu, masukkan 0
                    $chartData['revenue'][] = 0;
                    $chartData['orders'][] = 0;
                }
                $currentDate->modify('+1 day'); // Maju ke hari berikutnya
            }

        } catch (PDOException $e) {
            error_log("Error getting sales trend data ({$startDate} - {$endDate}): " . $e->getMessage());
            // Kembalikan array kosong jika error
            return ['labels' => [], 'revenue' => [], 'orders' => []];
        }

        return $chartData;
    }

} // Akhir kelas Order
?>