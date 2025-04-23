<?php
// File: app/Controllers/Admin/TableController.php (Final Diperbaiki)
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Table;
use App\Helpers\AuthHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper; // Pakai yg sudah update
use App\Helpers\SanitizeHelper;
use App\Helpers\QrCodeHelper;
use App\Helpers\StringHelper;

class TableController extends Controller {
    private $tableModel;
    public function __construct() { $this->tableModel = $this->model('Table'); AuthHelper::requireAdmin(); }
    public function index() { $tables = $this->tableModel->getAllSorted(); $this->view('admin.tables.index', ['pageTitle' => 'Kelola Meja', 'tables' => $tables], 'admin_layout'); }
    public function create() { $this->view('admin.tables.create', ['pageTitle' => 'Tambah Meja Baru'], 'admin_layout'); } // oldInput diambil di view

    public function store() {
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/tables/create'); return; }
         $tableNumber = SanitizeHelper::string($_POST['table_number'] ?? ''); $description = SanitizeHelper::string($_POST['description'] ?? null); $isActive = isset($_POST['is_active']) ? true : false;
         if (empty($tableNumber)) {
             SessionHelper::setFlashData('old_input', $_POST); // <<< Gunakan setFlashData
             SessionHelper::setFlash('error', 'Nomor Meja wajib diisi.'); // <<< Kunci standar 'error'
             UrlHelper::redirect('/admin/tables/create'); return;
         }
         $qrIdentifier = StringHelper::slugify($tableNumber) . '-' . bin2hex(random_bytes(4));
         $tableId = $this->tableModel->createTable($tableNumber, $qrIdentifier, $description, $isActive);
         if ($tableId) { SessionHelper::setFlash('success', 'Meja ditambahkan.'); UrlHelper::redirect('/admin/tables'); } // <<< Kunci standar 'success'
         else { SessionHelper::setFlashData('old_input', $_POST); SessionHelper::setFlash('error', 'Gagal tambah meja (nomor duplikat?).'); UrlHelper::redirect('/admin/tables/create'); } // <<< Gunakan 'error'
    }

    public function edit(int $id) {
         $safeId = SanitizeHelper::integer($id); if ($safeId <= 0) { UrlHelper::redirect('/admin/tables'); return; } $table = $this->tableModel->findById($safeId); if (!$table) { SessionHelper::setFlash('error', 'Meja tidak ditemukan.'); UrlHelper::redirect('/admin/tables'); return; }
         $this->view('admin.tables.edit', ['pageTitle' => 'Edit Meja: ' . SanitizeHelper::html($table['table_number']), 'table' => $table ], 'admin_layout'); // oldInput diambil di view
    }

     public function update(int $id) {
         $safeId = SanitizeHelper::integer($id); if ($safeId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/tables'); return; }
         $tableNumber = SanitizeHelper::string($_POST['table_number'] ?? ''); $description = SanitizeHelper::string($_POST['description'] ?? null); $isActive = isset($_POST['is_active']) ? true : false;
         if (empty($tableNumber)) {
             SessionHelper::setFlashData('old_input', $_POST + ['id' => $safeId]); // <<< Gunakan setFlashData
             SessionHelper::setFlash('error', 'Nomor Meja wajib diisi.'); // <<< Kunci standar 'error'
             UrlHelper::redirect('/admin/tables/edit/' . $safeId); return;
         }
         $success = $this->tableModel->updateTable($safeId, $tableNumber, $description, $isActive);
         if ($success) { SessionHelper::setFlash('success', 'Meja diperbarui.'); UrlHelper::redirect('/admin/tables'); } // <<< Kunci standar 'success'
         else { SessionHelper::setFlashData('old_input', $_POST + ['id' => $safeId]); SessionHelper::setFlash('error', 'Gagal update meja (nomor duplikat?).'); UrlHelper::redirect('/admin/tables/edit/' . $safeId); } // <<< Gunakan 'error'
     }

    public function destroy(int $id) {
        $safeId = SanitizeHelper::integer($id); if ($safeId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/tables'); return; }
        $table = $this->tableModel->findById($safeId); $tableName = $table ? $table['table_number'] : 'ID ' . $safeId;
        if ($this->tableModel->deleteTable($safeId)) { SessionHelper::setFlash('success', 'Meja "' . SanitizeHelper::html($tableName) . '" dihapus.'); } // <<< 'success'
        else { SessionHelper::setFlash('error', 'Gagal hapus meja (mungkin terkait pesanan?).'); } // <<< 'error'
        UrlHelper::redirect('/admin/tables');
     }

    public function generateQr(int $id) {
         $safeId = SanitizeHelper::integer($id); if ($safeId <= 0) { http_response_code(404); echo "Invalid ID"; return; } $table = $this->tableModel->findById($safeId); if (!$table || empty($table['qr_code_identifier'])) { http_response_code(404); echo "Table not found or QR Identifier missing."; return; } $qrData = UrlHelper::baseUrl('/menu/table/' . $table['qr_code_identifier']); QrCodeHelper::display($qrData, 250, 'png'); exit;
    }
}
?>