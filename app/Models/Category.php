<?php
// File: app/Models/Category.php
namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

/**
 * Class Category
 * Model untuk tabel 'categories'.
 */
class Category extends Model {
    protected $table = 'categories';

    /**
     * Mengambil semua kategori diurutkan berdasarkan sort_order, lalu nama.
     *
     * @return array List kategori.
     */
    public function getAllSorted(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY sort_order ASC, name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching sorted categories: " . $e->getMessage());
            return [];
        }
    }

     /**
      * Mencari kategori berdasarkan ID. (Warisan dari Base Model sudah cukup)
      * public function findById(int $id): array|false { ... }
      */

     /**
     * Membuat kategori baru.
     *
     * @param string $name Nama kategori (harus unik).
     * @param string|null $description Deskripsi.
     * @param int $sortOrder Urutan tampil.
     * @return int|false ID kategori baru atau false jika gagal.
     */
    public function createCategory(string $name, ?string $description = null, int $sortOrder = 0): int|false {
         $sql = "INSERT INTO {$this->table} (name, description, sort_order, created_at, updated_at)
                 VALUES (:name, :description, :sort_order, NOW(), NOW())";
         try {
             $stmt = $this->db->prepare($sql);
             $stmt->bindParam(':name', $name, PDO::PARAM_STR);
             $stmt->bindParam(':description', $description, PDO::PARAM_STR);
             $stmt->bindParam(':sort_order', $sortOrder, PDO::PARAM_INT);

             if ($stmt->execute()) {
                 return (int)$this->db->lastInsertId();
             }
             return false;
         } catch (PDOException $e) {
             // Cek kode error untuk duplikat entry (kode 23000 biasanya untuk integrity constraint)
             if ($e->getCode() == '23000') {
                error_log("Error creating category '{$name}': Duplicate name likely.");
             } else {
                error_log("Error creating category '{$name}': " . $e->getMessage());
             }
             return false; // Gagal
         }
    }

    /**
     * Mengupdate data kategori.
     *
     * @param int $id ID kategori.
     * @param string $name
     * @param string|null $description
     * @param int $sortOrder
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updateCategory(int $id, string $name, ?string $description, int $sortOrder): bool {
         if ($id <= 0) return false; // ID tidak valid
         $sql = "UPDATE {$this->table} SET
                 name = :name,
                 description = :description,
                 sort_order = :sort_order,
                 updated_at = NOW()
                 WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':sort_order', $sortOrder, PDO::PARAM_INT);
            return $stmt->execute(); // Mengembalikan true jika query berhasil dieksekusi
                                      // Mungkin 0 rows affected jika data tidak berubah, tapi tetap true
        } catch (PDOException $e) {
             if ($e->getCode() == '23000') {
                error_log("Error updating category ID {$id}: Duplicate name likely.");
             } else {
                error_log("Error updating category ID {$id}: " . $e->getMessage());
             }
             return false; // Gagal
         }
    }

     /**
     * Menghapus kategori.
     * Akan gagal jika ada item menu terkait (karena ON DELETE RESTRICT di DB).
     *
     * @param int $id ID kategori.
     * @return bool True jika berhasil dihapus, false jika gagal.
     */
    public function deleteCategory(int $id): bool {
        if ($id <= 0) return false;

        // Opsional tapi direkomendasikan: Cek item menu terkait di sini juga
        // agar bisa memberi pesan error yang lebih spesifik di controller.
        $menuItemModel = new MenuItem(); // Perlu di-instantiate atau di-inject
        $itemCount = $menuItemModel->countByCategoryId($id);
        if ($itemCount > 0) {
             error_log("Attempted to delete category ID {$id} which has {$itemCount} related menu items.");
             return false; // Mencegah delete
        }

        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $success = $stmt->execute();
            // Pastikan ada baris yang benar-benar terhapus
            return $success && ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
             // Error 23000 (Integrity constraint violation) akan terjadi jika FK restrict aktif
             // dan masih ada item terkait (meskipun sudah dicek di atas, sebagai fallback).
              error_log("Error deleting category ID {$id}: " . $e->getMessage());
             return false;
        }
    }
}
?>