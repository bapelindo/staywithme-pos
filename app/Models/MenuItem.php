<?php
namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class MenuItem extends Model {
    protected $table = 'menu_items';

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
            error_log("Error fetching available menu items: " . $e->getMessage());
            return [];
        }
    }

    public function getAllGroupedByCategory(): array {
         $sql = "SELECT mi.*, c.name as category_name
                FROM {$this->table} mi
                JOIN categories c ON mi.category_id = c.id
                ORDER BY c.sort_order ASC, c.name ASC, mi.name ASC";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all menu items: " . $e->getMessage());
            return [];
        }
    }

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

    public function createMenuItem(int $categoryId, string $name, float|string $price, float|string $cost, ?string $description = null, ?string $imagePath = null, bool $isAvailable = true): int|false {
        if ($categoryId <= 0 || empty($name) || !is_numeric($price) || $price < 0) {
             error_log("Invalid input for createMenuItem.");
             return false;
        }
        $sql = "INSERT INTO {$this->table} (category_id, name, description, price, cost, image_path, is_available, created_at, updated_at)
                VALUES (:category_id, :name, :description, :price, :cost, :image_path, :is_available, NOW(), NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $priceStr = (string)$price;
            $costStr = (string)$cost;
            $stmt->bindParam(':price', $priceStr, PDO::PARAM_STR);
            $stmt->bindParam(':cost', $costStr, PDO::PARAM_STR);
            $stmt->bindParam(':image_path', $imagePath, PDO::PARAM_STR);
            $stmt->bindParam(':is_available', $isAvailable, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
             return false;
        } catch (PDOException $e) {
            error_log("Error creating menu item '{$name}': " . $e->getMessage());
            if ($e->getCode() == 23000) { return false; }
            return false;
        }
    }

    public function updateMenuItem(int $id, int $categoryId, string $name, float|string $price, float|string $cost, ?string $description, ?string $imagePath, bool $isAvailable): bool {
         if ($id <= 0 || $categoryId <= 0 || empty($name) || !is_numeric($price) || $price < 0) {
             error_log("Invalid input for updateMenuItem ID {$id}.");
             return false;
         }
         $imageSqlPart = "";
         if ($imagePath !== null) {
              $imageSqlPart = ", image_path = :image_path";
         }

         $sql = "UPDATE {$this->table} SET
                 category_id = :category_id,
                 name = :name,
                 description = :description,
                 price = :price,
                 cost = :cost,
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
            $costStr = (string)$cost;
            $stmt->bindParam(':price', $priceStr, PDO::PARAM_STR);
            $stmt->bindParam(':cost', $costStr, PDO::PARAM_STR);
            $stmt->bindParam(':is_available', $isAvailable, PDO::PARAM_BOOL);
            if ($imagePath !== null) {
                 $stmt->bindParam(':image_path', $imagePath, PDO::PARAM_STR);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
             error_log("Error updating menu item ID {$id}: " . $e->getMessage());
              if ($e->getCode() == 23000) { return false; }
             return false;
         }
    }
    
    public function deleteMenuItem(int $id): bool {
        if ($id <= 0) return false;
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $success = $stmt->execute();
             return $success && ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
             error_log("Error deleting menu item ID {$id}: " . $e->getMessage());
              if ($e->getCode() == 23000) { return false; }
             return false;
        }
    }

    public function setAvailability(int $id, bool $isAvailable): bool {
        if ($id <= 0) return false;
        $sql = "UPDATE {$this->table} SET is_available = :is_available, updated_at = NOW() WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':is_available', $isAvailable, PDO::PARAM_BOOL);
            return $stmt->execute();
        } catch (PDOException $e) {
             error_log("Error setting availability for menu item ID {$id}: " . $e->getMessage());
             return false;
        }
    }

    public function countUnavailable(): int {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE is_available = 0");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting unavailable menu items: " . $e->getMessage());
            return 0;
        }
    }
}
