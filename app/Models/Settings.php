<?php
// File: app/Models/Settings.php

namespace App\Models;

use App\Core\Model;
use PDO;

class Settings extends Model
{
    protected $table = 'settings';

    /**
     * Mengambil semua pengaturan dari database dan mengembalikannya sebagai array asosiatif.
     */
    public function getAllSettings(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table}");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ubah format dari [ ['setting_key' => key1, 'setting_value' => val1], ... ]
            // menjadi [ 'key1' => val1, ... ]
            $settings = [];
            foreach ($results as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            return $settings;

        } catch (\PDOException $e) {
            error_log("Error fetching all settings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Menyimpan beberapa pengaturan sekaligus (menggunakan INSERT ... ON DUPLICATE KEY UPDATE).
     */
    public function updateSettings(array $settings): bool
    {
        if (empty($settings)) {
            return false;
        }

        $sql = "INSERT INTO {$this->table} (setting_key, setting_value) VALUES ";
        $placeholders = [];
        $values = [];

        foreach ($settings as $key => $value) {
            $placeholders[] = '(?, ?)';
            $values[] = $key;
            $values[] = $value;
        }

        $sql .= implode(', ', $placeholders);
        $sql .= " ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (\PDOException $e) {
            error_log("Error updating settings: " . $e->getMessage());
            return false;
        }
    }
}