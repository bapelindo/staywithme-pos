<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\Table;
use App\Models\Category;
use App\Models\MenuItem;
use App\Helpers\SanitizeHelper;

class MenuController extends Controller {

    /**
     * Menampilkan menu digital umum tanpa meja (hanya lihat).
     */
    public function index() {
        $categoryModel = new Category();
        $menuItemModel = new MenuItem();

        $categories = $categoryModel->getAllSorted();

        $menuItemsByCategory = [];
        $availableItems = $menuItemModel->getAllAvailableGroupedByCategory();
        foreach ($availableItems as $item) {
            if (isset($item['category_id'])) {
                 $menuItemsByCategory[$item['category_id']][] = $item;
            }
        }

        $this->view('public.menu', [
            'table' => null, // Tidak ada meja
            'categories' => $categories,
            'menuItemsByCategory' => $menuItemsByCategory,
            'pageTitle' => 'Koleksi Menu - Yours Cafe'
        ]);
    }

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
            // Jika meja tidak valid, langsung tampilkan menu umum (tanpa fungsi order)
            // Redirect ke halaman menu umum untuk mencegah error atau tampilan 404
            UrlHelper::redirect('/menu');
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
        $existingOrderId = isset($_GET['order_id']) ? SanitizeHelper::integer($_GET['order_id']) : null;
        
        $this->view('public.menu', [
            'table' => $table, // Kirim data meja (ID dan nomor meja) ke view
            'categories' => $categories,
            'menuItemsByCategory' => $menuItemsByCategory,
            'existingOrderId' => $existingOrderId,
            'pageTitle' => 'Pesan Menu - Meja ' . SanitizeHelper::html($table['table_number']) // Sanitasi output
        ]);
    }
}
?>