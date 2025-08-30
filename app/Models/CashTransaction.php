<?php
// File: app/Models/CashTransaction.php

namespace App\Models;

use App\Core\Model;
use PDO;

class CashTransaction extends Model
{
    protected $table = 'cash_transactions';

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (user_id, drawer_id, type, amount, category, notes, device_name, transaction_time)
                VALUES (:user_id, :drawer_id, :type, :amount, :category, :notes, :device_name, NOW())";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':drawer_id', $data['drawer_id'], PDO::PARAM_INT);
            $stmt->bindValue(':type', $data['type'], PDO::PARAM_STR);
            $stmt->bindValue(':amount', (string)$data['amount'], PDO::PARAM_STR);
            $stmt->bindValue(':category', $data['category'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':notes', $data['notes'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':device_name', $data['device_name'] ?? null, PDO::PARAM_STR); // Ambil dari data jika ada
            
            return $stmt->execute();

        } catch (\PDOException $e) {
            error_log("Error creating cash transaction: " . $e->getMessage());
            return false;
        }
    }
}