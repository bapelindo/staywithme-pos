<?php
namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

/**
 * Class Table
 * Model untuk tabel 'tables'.
 */
class Table extends Model {
    protected $table = 'tables';

    /**
     * Mencari meja berdasarkan QR Code Identifier uniknya.
     * Hanya mengembalikan meja yang aktif.
     *
     * @param string $identifier QR Code Identifier.
     * @return array|false Data meja jika ditemukan dan aktif, false jika tidak atau error.
     */
    public function findByQrIdentifier(string $identifier): array|false {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE qr_code_identifier = :identifier AND is_active = 1 LIMIT 1");
            $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding table by QR identifier '{$identifier}': " . $e->getMessage());
            return false;
        }
    }

     /**
     * Mencari meja berdasarkan nomor meja.
     *
     * @param string $tableNumber Nomor meja.
     * @return array|false Data meja jika ditemukan, false jika tidak atau error.
     */
    public function findByTableNumber(string $tableNumber): array|false {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE table_number = :table_number LIMIT 1");
            $stmt->bindParam(':table_number', $tableNumber, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding table by number '{$tableNumber}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil semua meja yang aktif, diurutkan berdasarkan nomor meja.
     *
     * @return array List meja aktif.
     */
    public function getAllActiveSorted(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY table_number ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching active tables: " . $e->getMessage());
            return []; // Return array kosong jika error
        }
    }

     /**
     * Mengambil semua meja (aktif dan non-aktif), diurutkan.
     *
     * @return array List semua meja.
     */
    public function getAllSorted(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY table_number ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all tables: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Membuat meja baru.
     * QR Code Identifier harus unik dan sebaiknya di-generate di Controller.
     *
     * @param string $tableNumber Nomor meja (harus unik).
     * @param string $qrIdentifier Identifier unik untuk QR code (harus unik).
     * @param string|null $description Deskripsi opsional.
     * @param bool $isActive Status aktif.
     * @return int|false ID meja baru atau false jika gagal.
     */
    public function createTable(string $tableNumber, string $qrIdentifier, ?string $description = null, bool $isActive = true): int|false {
        $sql = "INSERT INTO {$this->table} (table_number, qr_code_identifier, description, is_active, created_at, updated_at)
                VALUES (:table_number, :qr_identifier, :description, :is_active, NOW(), NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':table_number', $tableNumber, PDO::PARAM_STR);
            $stmt->bindParam(':qr_identifier', $qrIdentifier, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
             return false;
        } catch (PDOException $e) {
            error_log("Error creating table '{$tableNumber}': " . $e->getMessage());
             if ($e->getCode() == 23000) { return false; } // Gagal karena duplikat
            return false;
        }
    }

     /**
     * Mengupdate data meja.
     * QR code identifier sebaiknya tidak diubah setelah dibuat.
     *
     * @param int $id ID meja.
     * @param string $tableNumber
     * @param string|null $description
     * @param bool $isActive
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updateTable(int $id, string $tableNumber, ?string $description, bool $isActive): bool {
         $sql = "UPDATE {$this->table} SET
                table_number = :table_number,
                description = :description,
                is_active = :is_active,
                updated_at = NOW()
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':table_number', $tableNumber, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);
            return $stmt->execute();
        } catch (PDOException $e) {
             error_log("Error updating table ID {$id}: " . $e->getMessage());
             if ($e->getCode() == 23000) { return false; } // Gagal karena duplikat nomor meja
             return false;
         }
    }

     /**
     * Menghapus meja (Hard delete).
     * Perhatikan relasi dengan tabel 'orders'. Sebaiknya non-aktifkan saja (soft delete).
     * Jika hard delete, pastikan tidak ada order terkait atau handle relasinya.
     *
     * @param int $id ID meja.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function deleteTable(int $id): bool {
        // PERINGATAN: Hard delete bisa menyebabkan masalah jika ada foreign key
        // Cek dulu apakah ada order terkait meja ini
        $orderModel = new Order(); // Asumsi Order model sudah ada
        $relatedOrders = $orderModel->countByTableId($id); // Perlu buat method ini di Order Model

        if ($relatedOrders > 0) {
             error_log("Cannot delete table ID {$id}: It has related orders.");
             // Atau set is_active = 0 saja (soft delete)
             // return $this->updateTable($id, $tableData['table_number'], $tableData['description'], false);
             return false; // Mencegah delete
        }

        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
             error_log("Error deleting table ID {$id}: " . $e->getMessage());
             return false;
        }
    }
}
?>