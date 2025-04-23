<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\Table;
use App\Models\Category;
use App\Models\MenuItem;
use App\Helpers\SanitizeHelper;

class MenuController extends Controller {

    /**
     * Menampilkan menu digital untuk meja tertentu berdasarkan QR Identifier.
     *
     * @param string $qr_identifier Identifier unik dari QR Code meja.
     */
    public function show(string $qr_identifier) {
        // 1. Sanitasi input dari URL
        $safe_qr_identifier = SanitizeHelper::string($qr_identifier);

        // 2. Load Model yang dibutuhkan
        $tableModel = new Table();
        $categoryModel = new Category();
        $menuItemModel = new MenuItem();

        // 3. Cari meja berdasarkan QR Identifier yang aktif
        $table = $tableModel->findByQrIdentifier($safe_qr_identifier);

        if (!$table) {
            // Meja tidak ditemukan atau tidak aktif
            http_response_code(404);
            // Load view error 404
            $this->view('public.errors.404', ['message' => 'Meja tidak valid atau tidak ditemukan. Silakan pindai ulang QR Code.']);
            return;
        }

        // 4. Ambil semua kategori yang diurutkan
        $categories = $categoryModel->getAllSorted();

        // 5. Ambil semua item menu yang tersedia, dikelompokkan per kategori
        $menuItemsByCategory = [];
        $availableItems = $menuItemModel->getAllAvailableGroupedByCategory();
        foreach ($availableItems as $item) {
            // Pastikan category_id ada sebelum digunakan sebagai key
            if (isset($item['category_id'])) {
                 $menuItemsByCategory[$item['category_id']][] = $item;
            }
        }

        // 6. Load View dan kirim data
        $this->view('public.menu', [
            'table' => $table, // Kirim data meja (ID dan nomor meja) ke view
            'categories' => $categories,
            'menuItemsByCategory' => $menuItemsByCategory,
            'pageTitle' => 'Pesan Menu - Meja ' . SanitizeHelper::html($table['table_number']) // Sanitasi output
        ]);
    }
}
?>