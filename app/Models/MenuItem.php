<?php
namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

/**
 * Class MenuItem
 * Model untuk tabel 'menu_items'.
 */
class MenuItem extends Model {
    protected $table = 'menu_items';

     /**
     * Mencari item menu berdasarkan ID.
     * Method findById dari BaseModel sudah cukup jika hanya perlu query by ID.
     * public function findById($id): array|false { ... }
     */

    /**
     * Mengambil semua item menu yang tersedia (is_available = 1),
     * dikelompokkan berdasarkan kategori ID.
     *
     * @return array List item menu, diurutkan berdasarkan kategori lalu nama item.
     */
    public function getAllAvailableGroupedByCategory(): array {
        $sql = "SELECT mi.*, c.name as category_name
                FROM {$this->table} mi
                JOIN categories c ON mi.category_id = c.id
                WHERE mi.is_available = 1
                ORDER BY c.sort_order ASC, c.name ASC, mi.name ASC";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching available menu items grouped by category: " . $e->getMessage());
            return [];
        }
    }

     /**
     * Mengambil semua item menu (tersedia maupun tidak),
     * diurutkan berdasarkan kategori lalu nama item.
     *
     * @return array List semua item menu.
     */
    public function getAllGroupedByCategory(): array {
         $sql = "SELECT mi.*, c.name as category_name
                FROM {$this->table} mi
                JOIN categories c ON mi.category_id = c.id
                ORDER BY c.sort_order ASC, c.name ASC, mi.name ASC";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all menu items grouped by category: " . $e->getMessage());
            return [];
        }
    }

     /**
     * Menghitung jumlah item menu dalam kategori tertentu.
     *
     * @param int $categoryId ID Kategori.
     * @return int Jumlah item.
     */
    public function countByCategoryId(int $categoryId): int {
        if ($categoryId <= 0) return 0;
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE category_id = :category_id");
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting menu items for category ID {$categoryId}: " . $e->getMessage());
            return 0;
        }
    }


    /**
     * Membuat item menu baru.
     *
     * @param int $categoryId
     * @param string $name
     * @param float|string $price
     * @param string|null $description
     * @param string|null $imagePath Path relatif dari folder public.
     * @param bool $isAvailable Status ketersediaan.
     * @return int|false ID item baru atau false jika gagal.
     */
    public function createMenuItem(int $categoryId, string $name, float|string $price, ?string $description = null, ?string $imagePath = null, bool $isAvailable = true): int|false {
        if ($categoryId <= 0 || empty($name) || !is_numeric($price) || $price < 0) {
             error_log("Invalid input for createMenuItem.");
             return false;
        }
        $sql = "INSERT INTO {$this->table} (category_id, name, description, price, image_path, is_available, created_at, updated_at)
                VALUES (:category_id, :name, :description, :price, :image_path, :is_available, NOW(), NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $priceStr = (string)$price;
            $stmt->bindParam(':price', $priceStr, PDO::PARAM_STR);
            $stmt->bindParam(':image_path', $imagePath, PDO::PARAM_STR);
            $stmt->bindParam(':is_available', $isAvailable, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
             return false;
        } catch (PDOException $e) {
            error_log("Error creating menu item '{$name}': " . $e->getMessage());
            if ($e->getCode() == 23000) { return false; } // FK error
            return false;
        }
    }

    /**
     * Mengupdate data item menu.
     *
     * @param int $id
     * @param int $categoryId
     * @param string $name
     * @param float|string $price
     * @param string|null $description
     * @param string|null $imagePath Jika null, tidak diubah. String kosong ('') untuk menghapus.
     * @param bool $isAvailable
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updateMenuItem(int $id, int $categoryId, string $name, float|string $price, ?string $description, ?string $imagePath, bool $isAvailable): bool {
         if ($id <= 0 || $categoryId <= 0 || empty($name) || !is_numeric($price) || $price < 0) {
             error_log("Invalid input for updateMenuItem ID {$id}.");
             return false;
         }
         $imageSqlPart = "";
         // Hanya update image path jika $imagePath tidak null
         if ($imagePath !== null) {
              $imageSqlPart = ", image_path = :image_path";
         }

         $sql = "UPDATE {$this->table} SET
                 category_id = :category_id,
                 name = :name,
                 description = :description,
                 price = :price,
                 is_available = :is_available
                 {$imageSqlPart}
                 , updated_at = NOW()
                 WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $priceStr = (string)$price;
            $stmt->bindParam(':price', $priceStr, PDO::PARAM_STR);
            $stmt->bindParam(':is_available', $isAvailable, PDO::PARAM_BOOL);
            if ($imagePath !== null) {
                 $stmt->bindParam(':image_path', $imagePath, PDO::PARAM_STR); // Bind path baru/kosong
            }

            $success = $stmt->execute();
            // execute bisa true tapi tidak ada row affected jika data tidak berubah
            return $success; // Cukup return status eksekusi
        } catch (PDOException $e) {
             error_log("Error updating menu item ID {$id}: " . $e->getMessage());
              if ($e->getCode() == 23000) { return false; } // FK category_id
             return false;
         }
    }

     /**
     * Menghapus item menu.
     *
     * @param int $id ID item menu.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function deleteMenuItem(int $id): bool {
        if ($id <= 0) return false;
        // Peringatan: Harusnya ada pengecekan relasi ke order_items
        // Jika ON DELETE RESTRICT di DB, query ini akan gagal jika ada relasi.

        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $success = $stmt->execute();
             return $success && ($stmt->rowCount() > 0); // Pastikan ada yg terhapus
        } catch (PDOException $e) {
             error_log("Error deleting menu item ID {$id}: " . $e->getMessage());
              if ($e->getCode() == 23000) { return false; } // Gagal karena FK constraint
             return false;
        }
    }

     /**
     * Mengatur status ketersediaan item menu.
     *
     * @param int $id ID item menu.
     * @param bool $isAvailable True untuk tersedia, False untuk habis.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function setAvailability(int $id, bool $isAvailable): bool {
        if ($id <= 0) return false;
        $sql = "UPDATE {$this->table} SET is_available = :is_available, updated_at = NOW() WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':is_available', $isAvailable, PDO::PARAM_BOOL);
            $success = $stmt->execute();
            return $success; // Tidak perlu cek rowCount, status bisa sama
        } catch (PDOException $e) {
             error_log("Error setting availability for menu item ID {$id}: " . $e->getMessage());
             return false;
        }
    }

    /**
     * Menghitung jumlah item menu yang statusnya tidak tersedia (is_available = 0).
     *
     * @return int Jumlah item yang tidak tersedia.
     */
    public function countUnavailable(): int {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE is_available = 0");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting unavailable menu items: " . $e->getMessage());
            return 0;
        }
    }

} // Akhir kelas MenuItem
?>