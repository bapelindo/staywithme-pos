<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class Cashier extends Model
{
    protected $table = 'cash_drawers';

    public function create($data)
    {
        $sql = 'INSERT INTO cash_drawers (user_id, opening_amount, opened_at, status) VALUES (:user_id, :opening_amount, NOW(), :status)';
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':opening_amount', (string)$data['opening_amount'], PDO::PARAM_STR);
            $stmt->bindValue(':status', 'open', PDO::PARAM_STR);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating cash drawer: " . $e->getMessage());
            return false;
        }
    }

    public function getOpenDrawerByUserId($user_id)
    {
        $sql = 'SELECT * FROM cash_drawers WHERE user_id = :user_id AND status = :status';
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $user_id, ':status' => 'open']);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting open drawer: " . $e->getMessage());
            return false;
        }
    }

    public function close($id, $closing_amount, $notes)
    {
        $sql = 'UPDATE cash_drawers SET closing_amount = :closing_amount, notes = :notes, closed_at = NOW(), status = :status WHERE id = :id';
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':closing_amount' => $closing_amount,
                ':notes' => $notes,
                ':status' => 'closed'
            ]);
        } catch (PDOException $e) {
            error_log("Error closing drawer: " . $e->getMessage());
            return false;
        }
    }

    public function getTodaysDrawers()
    {
        $sql = "SELECT cd.*, u.username FROM cash_drawers cd JOIN users u ON cd.user_id = u.id WHERE DATE(cd.opened_at) = CURDATE() ORDER BY cd.opened_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting today's drawers: " . $e->getMessage());
            return [];
        }
    }
}