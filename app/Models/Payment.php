<?php
// File: app/Models/Payment.php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class Payment extends Model {
    protected $table = 'payments';

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
     * Mengubah status order menjadi 'received' dan mencatat transaksi kas jika tunai.
     * Ini adalah jantung dari integrasi alur pembayaran dan kasir.
     */
    public function createPayment(int $orderId, float|string $amountPaid, string $paymentMethod, ?int $processedByUserId = null): int|false {
        $allowedMethods = ['cash', 'qris', 'card', 'transfer'];
        if (!in_array($paymentMethod, $allowedMethods)) {
            error_log("Invalid payment method '{$paymentMethod}' for order ID {$orderId}.");
            return false;
        }

        // Gunakan transaksi untuk memastikan semua proses berhasil atau semua gagal.
        $this->db->beginTransaction();

        try {
            // Langkah 1: Simpan data pembayaran ke tabel 'payments'
            $sql = "INSERT INTO {$this->table} (order_id, payment_method, amount_paid, payment_time, processed_by_user_id, created_at, updated_at)
                    VALUES (:order_id, :payment_method, :amount_paid, NOW(), :processed_by_user_id, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->bindParam(':payment_method', $paymentMethod, PDO::PARAM_STR);
            $amountStr = (string)$amountPaid;
            $stmt->bindParam(':amount_paid', $amountStr, PDO::PARAM_STR);
            $stmt->bindParam(':processed_by_user_id', $processedByUserId, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new PDOException("Gagal menyimpan data pembayaran.");
            }
            $paymentId = (int)$this->db->lastInsertId();

            // Langkah 2: Ubah status pesanan menjadi 'received' agar masuk ke antrean dapur (KDS)
            $orderModel = new Order();
            if (!$orderModel->updateStatus($orderId, 'received')) {
                throw new PDOException("Gagal mengubah status pesanan menjadi 'received'.");
            }

            // Langkah 3 (Integrasi): Jika metode pembayaran adalah tunai ('cash')
            if ($paymentMethod === 'cash') {
                // Cek apakah kasir memiliki sesi yang aktif
                $cashierModel = new Cashier();
                $openDrawer = $cashierModel->getOpenDrawerByUserId($processedByUserId);

                if (!$openDrawer) {
                    throw new PDOException("Tidak ada sesi kasir yang aktif untuk pengguna ID: {$processedByUserId}.");
                }

                // Jika ada sesi aktif, catat sebagai transaksi kas masuk
                $cashTransactionModel = new CashTransaction();
                $orderData = $orderModel->findById($orderId);
                
                $transactionSuccess = $cashTransactionModel->create([
                    'user_id' => $processedByUserId,
                    'drawer_id' => $openDrawer['id'],
                    'type' => 'in',
                    'amount' => $amountPaid,
                    'category' => 'Penjualan', // Kategori otomatis untuk pembayaran
                    'notes' => 'Pembayaran untuk Pesanan #' . ($orderData['order_number'] ?? $orderId)
                ]);

                if (!$transactionSuccess) {
                    throw new PDOException("Gagal mencatat transaksi kas masuk untuk pembayaran.");
                }
            }
            
            // Jika semua langkah berhasil, konfirmasi perubahan ke database
            $this->db->commit();
            return $paymentId;

        } catch (PDOException $e) {
            // Jika terjadi kesalahan di salah satu langkah, batalkan semua perubahan
            $this->db->rollBack();
            error_log("Error dalam proses createPayment untuk order ID {$orderId}: " . $e->getMessage());
            return false;
        }
    }
}