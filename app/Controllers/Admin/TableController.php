<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Table;
use App\Helpers\AuthHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\QrCodeHelper;
use App\Helpers\StringHelper; // Untuk slug atau random string QR ID

class TableController extends Controller {

    private $tableModel;

    public function __construct() {
        $this->tableModel = new Table();
    }

    /**
     * Menampilkan daftar semua meja.
     */
    public function index() {
        AuthHelper::requireAdmin(); // Hanya Admin kelola meja

        $tables = $this->tableModel->getAllSorted(); // Ambil semua (aktif/non-aktif)

        $this->view('admin.tables.index', [
            'pageTitle' => 'Kelola Meja',
            'tables' => $tables
        ], 'admin_layout');
    }

    /**
     * Menampilkan form tambah meja baru.
     */
    public function create() {
        AuthHelper::requireAdmin();
        $oldInput = SessionHelper::getFlash('old_input') ?? [];
        $this->view('admin.tables.create', [
            'pageTitle' => 'Tambah Meja Baru',
            'oldInput' => $oldInput
        ], 'admin_layout');
    }

    /**
     * Menyimpan data meja baru.
     */
    public function store() {
         AuthHelper::requireAdmin();
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/tables/create'); return; }

         $tableNumber = SanitizeHelper::string($_POST['table_number'] ?? '');
         $description = SanitizeHelper::string($_POST['description'] ?? null);
         $isActive = isset($_POST['is_active']) ? true : false;

         SessionHelper::setFlash('old_input', $_POST);

         if (empty($tableNumber)) {
             SessionHelper::setFlash('error', 'Nomor Meja wajib diisi.');
             UrlHelper::redirect('/admin/tables/create');
             return;
         }

         // Generate QR Code Identifier Unik
         // Contoh: Gunakan kombinasi random string dan timestamp
         $qrIdentifier = StringHelper::slugify($tableNumber) . '-' . bin2hex(random_bytes(4));
         // Pastikan identifier unik (cek ke DB jika perlu, meski kemungkinannya kecil untuk collision)

         $tableId = $this->tableModel->createTable($tableNumber, $qrIdentifier, $description, $isActive);

         if ($tableId) {
             SessionHelper::setFlash('success', 'Meja berhasil ditambahkan.');
             SessionHelper::getFlash('old_input');
             UrlHelper::redirect('/admin/tables');
         } else {
             SessionHelper::setFlash('error', 'Gagal menambahkan meja (mungkin nomor meja sudah ada).');
             UrlHelper::redirect('/admin/tables/create');
         }
    }

     /**
     * Menampilkan form edit meja.
     *
     * @param int $id ID Meja.
     */
    public function edit(int $id) {
         AuthHelper::requireAdmin();
         $safeId = SanitizeHelper::integer($id);
         if ($safeId <= 0) { UrlHelper::redirect('/admin/tables'); return; }

         $table = $this->tableModel->findById($safeId);
         if (!$table) {
             SessionHelper::setFlash('error', 'Meja tidak ditemukan.');
             UrlHelper::redirect('/admin/tables');
             return;
         }

         $oldInput = SessionHelper::getFlash('old_input') ?? $table;

         $this->view('admin.tables.edit', [
             'pageTitle' => 'Edit Meja: ' . SanitizeHelper::html($table['table_number']),
             'table' => $oldInput
         ], 'admin_layout');
    }

     /**
     * Mengupdate data meja.
     *
     * @param int $id ID Meja.
     */
     public function update(int $id) {
         AuthHelper::requireAdmin();
         $safeId = SanitizeHelper::integer($id);
         if ($safeId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/tables'); return; }

         $tableNumber = SanitizeHelper::string($_POST['table_number'] ?? '');
         $description = SanitizeHelper::string($_POST['description'] ?? null);
         $isActive = isset($_POST['is_active']) ? true : false;

         SessionHelper::setFlash('old_input', $_POST + ['id' => $safeId]);

          if (empty($tableNumber)) {
             SessionHelper::setFlash('error', 'Nomor Meja wajib diisi.');
             UrlHelper::redirect('/admin/tables/edit/' . $safeId);
             return;
         }

         $success = $this->tableModel->updateTable($safeId, $tableNumber, $description, $isActive);

         if ($success) {
              SessionHelper::setFlash('success', 'Meja berhasil diperbarui.');
              SessionHelper::getFlash('old_input');
              UrlHelper::redirect('/admin/tables');
         } else {
              SessionHelper::setFlash('error', 'Gagal memperbarui meja (mungkin nomor meja sudah ada).');
              UrlHelper::redirect('/admin/tables/edit/' . $safeId);
         }
     }

    /**
     * Menghapus meja.
     *
     * @param int $id ID Meja.
     */
    public function destroy(int $id) {
        AuthHelper::requireAdmin();
        $safeId = SanitizeHelper::integer($id);
         if ($safeId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/tables'); return; }

        if ($this->tableModel->deleteTable($safeId)) {
            SessionHelper::setFlash('success', 'Meja berhasil dihapus.');
        } else {
            SessionHelper::setFlash('error', 'Gagal menghapus meja (pastikan tidak ada pesanan aktif terkait meja ini).');
        }
        UrlHelper::redirect('/admin/tables');
    }

     /**
     * Menghasilkan dan menampilkan QR Code untuk meja tertentu.
     *
     * @param int $id ID Meja.
     */
    public function generateQr(int $id) {
         AuthHelper::requireAdmin(); // Atau staff juga boleh cetak QR?
         $safeId = SanitizeHelper::integer($id);
         if ($safeId <= 0) { http_response_code(404); echo "Invalid ID"; return; }

         $table = $this->tableModel->findById($safeId);
         if (!$table || empty($table['qr_code_identifier'])) {
              http_response_code(404); echo "Table not found or QR Identifier missing."; return;
         }

         // URL yang akan di-encode di QR Code
         $qrData = UrlHelper::baseUrl('/menu/table/' . $table['qr_code_identifier']);

         // Tampilkan QR Code sebagai gambar PNG
         QrCodeHelper::display($qrData, 250, 'png'); // Ukuran 250px
         exit; // Helper sudah exit, tapi pastikan saja
    }
}
?>