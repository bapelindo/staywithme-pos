<?php
namespace App\Core;

use SessionHandlerInterface;
use App\Core\Database;
use PDO;

class DatabaseSessionHandler implements SessionHandlerInterface
{
    private PDO $db;
    private string $sessionTable = 'sessions'; // Nama tabel sesi

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function open(string $path, string $name): bool
    {
        // Tidak ada yang perlu dilakukan di sini karena koneksi sudah dibuat di constructor
        return true;
    }

    public function close(): bool
    {
        // Tidak ada yang perlu dilakukan di sini karena koneksi dikelola oleh Singleton
        return true;
    }

    public function read(string $id): string
    {
        $stmt = $this->db->prepare("SELECT data FROM " . $this->sessionTable . " WHERE id = :id AND expiry > :expiry");
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->bindValue(':expiry', time(), PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (string)$result['data'] : '';
    }

    public function write(string $id, string $data): bool
    {
        $expiry = time() + (int)ini_get('session.gc_maxlifetime');
        $stmt = $this->db->prepare("REPLACE INTO " . $this->sessionTable . " (id, data, expiry) VALUES (:id, :data, :expiry)");
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->bindValue(':data', $data, PDO::PARAM_STR);
        $stmt->bindValue(':expiry', $expiry, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM " . $this->sessionTable . " WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->db->prepare("DELETE FROM " . $this->sessionTable . " WHERE expiry < :expiry");
        $stmt->bindValue(':expiry', time() - $max_lifetime, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
