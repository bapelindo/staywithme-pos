<?php
namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

/**
 * Class Payment
 * Model untuk tabel 'payments'.
 */
class Payment extends Model {
    protected $table = 'payments';

    /**
     * Mencari data pembayaran berdasarkan Order ID.
     *
     * @param int $orderId ID Order.
     * @return array|false Data pembayaran jika ada, false jika tidak atau error.
     */
    public function findByOrderId(int $orderId): array|false {
        try {
            $stmt = $this->db->prepare("SELECT p.*, u.name as processed_by_user_name
                                        FROM {$this->table} p
                                        LEFT JOIN users u ON p.processed_by_user_id = u.id
                                        WHERE p.order_id = :order_id LIMIT 1");
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding payment for order ID {$orderId}: " . $e->getMessage());
            return false;
        }
    }

     /**
     * Membuat catatan pembayaran baru.
     * Biasanya dipanggil saat kasir menerima pembayaran cash atau mencatat pembayaran non-tunai.
     * Sebaiknya dilakukan dalam transaksi bersamaan dengan update status order menjadi 'paid'.
     *
     * @param int $orderId
     * @param float|string $amountPaid Jumlah yang dibayar.
     * @param string $paymentMethod Metode pembayaran ('cash', 'qris', 'card', 'transfer').
     * @param int|null $processedByUserId ID user (kasir) yang memproses.
     * @return int|false ID payment baru atau false jika gagal.
     */
    public function createPayment(int $orderId, float|string $amountPaid, string $paymentMethod, ?int $processedByUserId = null): int|false {
        $allowedMethods = ['cash', 'qris', 'card', 'transfer'];
        if (!in_array($paymentMethod, $allowedMethods)) {
            error_log("Invalid payment method '{$paymentMethod}' for order ID {$orderId}.");
            return false;
        }

        // Mulai transaksi agar insert payment dan update status order atomik
        $this->db->beginTransaction();

        $sql = "INSERT INTO {$this->table} (order_id, payment_method, amount_paid, payment_time, processed_by_user_id, created_at, updated_at)
                VALUES (:order_id, :payment_method, :amount_paid, NOW(), :processed_by_user_id, NOW(), NOW())";
       try {
           $stmt = $this->db->prepare($sql);
           $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
           $stmt->bindParam(':payment_method', $paymentMethod, PDO::PARAM_STR);
           $amountStr = (string)$amountPaid;
           $stmt->bindParam(':amount_paid', $amountStr, PDO::PARAM_STR);
           $stmt->bindParam(':processed_by_user_id', $processedByUserId, PDO::PARAM_INT);

           if ($stmt->execute()) {
               $paymentId = (int)$this->db->lastInsertId();

               // === PERUBAHAN DI SINI ===
               // Ubah status order menjadi 'received' agar masuk ke KDS
               $orderModel = new Order(); // Pastikan Order model bisa diakses
               if ($orderModel->updateStatus($orderId, 'paid')) {
               // === AKHIR PERUBAHAN ===
                   $this->db->commit(); // Commit transaksi jika semua berhasil
                   return $paymentId;
               } else {
                    $this->db->rollBack(); // Rollback jika gagal update status order
                    error_log("Payment created for order ID {$orderId} but failed to update order status to received.");
                    return false;
               }
           }
           $this->db->rollBack(); // Rollback jika insert payment gagal
           return false;

       } catch (PDOException $e) {
            $this->db->rollBack(); // Rollback jika ada error PDO
            error_log("Error creating payment or updating order status for order ID {$orderId}: " . $e->getMessage());
             // Gagal karena duplikat order_id (jika UNIQUE constraint di payments)
            if ($e->getCode() == 23000) { return false; }
            return false;
       }
   }
}
?>