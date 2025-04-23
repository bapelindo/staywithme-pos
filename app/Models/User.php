<?php
namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

/**
 * Class User
 * Model untuk tabel 'users'.
 */
class User extends Model {
    protected $table = 'users';

    /**
     * Mencari user berdasarkan username.
     *
     * @param string $username Username yang dicari.
     * @return array|false Data user jika ditemukan, false jika tidak atau error.
     */
    public function findByUsername(string $username): array|false {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding user by username '{$username}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mencari user berdasarkan ID.
     * Method findById dari BaseModel sudah cukup jika hanya perlu query by ID.
     * Anda bisa override jika perlu logic tambahan.
     * public function findById($id): array|false { ... }
     */

    /**
     * Membuat user baru.
     * Password HARUS sudah di-hash sebelum memanggil method ini.
     *
     * @param string $username
     * @param string $hashedPassword Password yang sudah di-hash.
     * @param string $name
     * @param string $role ('admin', 'staff', 'kitchen')
     * @param bool $isActive
     * @return int|false ID user yang baru dibuat atau false jika gagal.
     */
    public function createUser(string $username, string $hashedPassword, string $name, string $role, bool $isActive = true): int|false {
        $sql = "INSERT INTO {$this->table} (username, password, name, role, is_active, created_at, updated_at)
                VALUES (:username, :password, :name, :role, :is_active, NOW(), NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating user '{$username}': " . $e->getMessage());
            // Periksa apakah error karena duplikat username
            if ($e->getCode() == 23000) { // Kode SQL state untuk integrity constraint violation
               // Bisa return nilai spesifik atau throw exception khusus
               return false; // Gagal karena duplikat
            }
            return false;
        }
    }

     /**
     * Mengupdate data user (kecuali password).
     *
     * @param int $id ID user yang akan diupdate.
     * @param string $username
     * @param string $name
     * @param string $role
     * @param bool $isActive
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updateUser(int $id, string $username, string $name, string $role, bool $isActive): bool {
        $sql = "UPDATE {$this->table} SET
                username = :username,
                name = :name,
                role = :role,
                is_active = :is_active,
                updated_at = NOW()
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating user ID {$id}: " . $e->getMessage());
             if ($e->getCode() == 23000) {
               return false; // Gagal karena duplikat username mungkin
            }
            return false;
        }
    }

    /**
     * Mengupdate password user.
     *
     * @param int $id ID user.
     * @param string $newHashedPassword Password baru yang sudah di-hash.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updatePassword(int $id, string $newHashedPassword): bool {
         $sql = "UPDATE {$this->table} SET password = :password, updated_at = NOW() WHERE id = :id";
         try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':password', $newHashedPassword, PDO::PARAM_STR);
            return $stmt->execute();
         } catch (PDOException $e) {
            error_log("Error updating password for user ID {$id}: " . $e->getMessage());
            return false;
         }
    }

     /**
     * Menghapus user (Hard delete - hati-hati!).
     * Pertimbangkan soft delete (menambah kolom is_deleted).
     *
     * @param int $id ID user yang akan dihapus.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function deleteUser(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting user ID {$id}: " . $e->getMessage());
             // Mungkin gagal karena foreign key constraint jika user terkait dengan data lain
             if ($e->getCode() == 23000) {
                return false; // Gagal karena relasi
             }
            return false;
        }
    }

     /**
      * Mengambil semua user.
      * Method all() dari BaseModel sudah cukup jika tidak perlu filter/order.
      * Bisa di-override jika perlu sorting atau filter.
      * public function getAll($orderBy = 'name ASC') { ... }
      */
}
?>