<?php
// File: app/Core/Model.php
namespace App\Core;

use PDO; // Import PDO
use PDOException; // Import PDOException

/**
 * Class Model
 *
 * Kelas dasar abstrak untuk semua model dalam aplikasi.
 * Menyediakan koneksi database dan beberapa metode dasar (opsional).
 */
abstract class Model {
    /** @var PDO Objek koneksi database PDO */
    protected $db;
    /** @var string Nama tabel database yang terkait dengan model ini (harus di-set di child class) */
    protected $table;

    /**
     * Constructor Model.
     * Mendapatkan instance koneksi database saat model diinisialisasi.
     */
    public function __construct() {
        // Dapatkan objek koneksi PDO dari Singleton Database
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Contoh method dasar: Mengambil semua data dari tabel.
     * PERHATIAN: Gunakan dengan hati-hati pada tabel besar.
     *
     * @return array|false List data atau false jika error.
     */
    public function all(): array|false {
        // Pastikan properti $table sudah di-set di model turunan
        if (empty($this->table)) {
            error_log("Model Error: Property \$table is not set in " . get_class($this));
            return false;
        }
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table}");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all from {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Contoh method dasar: Mencari data berdasarkan ID primary key.
     *
     * @param int $id ID yang dicari.
     * @return array|false Data jika ditemukan, false jika tidak atau error.
     */
    public function findById(int $id): array|false {
        if (empty($this->table)) {
             error_log("Model Error: Property \$table is not set in " . get_class($this));
             return false;
        }
         if ($id <= 0) return false; // ID tidak valid

        try {
            // Selalu gunakan prepared statement meskipun hanya ID untuk konsistensi dan keamanan
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            // fetch() mengembalikan false jika tidak ada baris ditemukan
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding by ID {$id} in {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    // Model turunan akan mengimplementasikan method spesifik lainnya
    // seperti create(), update(), delete(), findByXXX(), dll.
    // SANGAT PENTING: Model turunan HARUS selalu menggunakan
    // Prepared Statements ($this->db->prepare() dan bindParam()/bindValue())
    // untuk query yang melibatkan data dari luar (input user, parameter URL)
    // untuk mencegah SQL Injection.
}
?>