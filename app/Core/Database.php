<?php
// File: app/Core/Database.php
namespace App\Core;

use PDO; // Import kelas PDO bawaan PHP
use PDOException; // Import kelas Exception PDO

/**
 * Class Database
 *
 * Mengelola koneksi database menggunakan PDO dengan pola Singleton.
 * Memastikan hanya ada satu instance koneksi database di seluruh aplikasi.
 */
class Database {
    /** @var PDO|null Koneksi PDO instance */
    private $conn = null;
    /** @var Database|null Singleton instance */
    private static $instance = null;

    /** @var string Host database (dari config) */
    private $host = \DB_HOST;
    /** @var string User database (dari config) */
    private $user = \DB_USER;
    /** @var string Password database (dari config) */
    private $pass = \DB_PASS;
    /** @var string Nama database (dari config) */
    private $name = \DB_NAME;
    /** @var string Port database (dari config) */
    private $port = \DB_PORT;

    /**
     * Constructor dibuat private untuk mencegah pembuatan instance langsung.
     * Gunakan getInstance() untuk mendapatkan instance.
     */
    private function __construct() {
        // Data Source Name (DSN) untuk koneksi PDO MySQL
        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->name . ';charset=utf8mb4';

        // Opsi koneksi PDO
        $options = [
            // Mode error: Lemparkan Exception jika terjadi error SQL
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Mode fetch default: Kembalikan hasil sebagai array asosiatif
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Nonaktifkan emulasi prepared statements (gunakan native prepared statements dari DB)
            PDO::ATTR_EMULATE_PREPARES   => false,
            // Opsi koneksi persistent (opsional, bisa meningkatkan performa tapi perlu hati-hati)
            // PDO::ATTR_PERSISTENT => true,
        ];

        try {
            // Buat instance PDO baru
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Tangani error koneksi database
            // Log error ke file atau sistem logging
            error_log("Database Connection Error: " . $e->getMessage());
            // Hentikan aplikasi atau tampilkan pesan error yang user-friendly
            // Jangan tampilkan detail error $e->getMessage() di produksi
            die("Gagal terhubung ke database. Silakan cek konfigurasi atau hubungi administrator.");
        }
    }

    /**
     * Mendapatkan instance Singleton dari kelas Database.
     *
     * @return Database Instance Database.
     */
    public static function getInstance(): Database {
        // Jika instance belum dibuat, buat baru
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        // Kembalikan instance yang sudah ada atau yang baru dibuat
        return self::$instance;
    }

    /**
     * Mendapatkan objek koneksi PDO.
     *
     * @return PDO Objek koneksi PDO.
     */
    public function getConnection(): PDO {
        return $this->conn;
    }

    /**
     * Mencegah cloning instance (Singleton Pattern).
     */
    private function __clone() {}

    /**
     * Mencegah unserialization instance (Singleton Pattern).
     */
    public function __wakeup() {}
}
?>