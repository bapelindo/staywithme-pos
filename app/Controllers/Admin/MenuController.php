<?php
// File: app/Controllers/Admin/MenuController.php (Final Diperbaiki - Kunci Flash & Old Input)

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\OrderItem;
use App\Helpers\AuthHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper; // Gunakan SessionHelper yang sudah diupdate
use App\Helpers\SanitizeHelper;

/**
 * Class MenuController
 * Mengelola item menu dan kategori menu di admin panel.
 */
class MenuController extends Controller {

    private $menuItemModel;
    private $categoryModel;
    private $orderItemModel;

    public function __construct() {
        $this->menuItemModel = $this->model('MenuItem');
        $this->categoryModel = $this->model('Category');
        $this->orderItemModel = $this->model('OrderItem');
    }

    // === METHOD ITEM MENU ===

    public function index() {
        AuthHelper::requireRole(['admin', 'staff']);
        $menuItems = $this->menuItemModel->getAllGroupedByCategory();
        $data = ['pageTitle' => 'Kelola Menu', 'menuItems' => $menuItems];
        $this->view('admin.menu.index', $data, 'admin_layout');
    }

    public function create() {
        AuthHelper::requireAdmin();
        $categories = $this->categoryModel->getAllSorted();
        $data = ['pageTitle' => 'Tambah Item Menu Baru', 'categories' => $categories];
        // oldInput diambil di view via getFlashData
        $this->view('admin.menu.create', $data, 'admin_layout');
    }

    public function store() {
        AuthHelper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/menu/create'); return; }

        // Sanitasi & Harga (Robust)
        $categoryId = SanitizeHelper::integer($_POST['category_id'] ?? 0); $name = SanitizeHelper::string($_POST['name'] ?? ''); $description = SanitizeHelper::string($_POST['description'] ?? null); $isAvailable = isset($_POST['is_available']) ? true : false;
        $priceInput = $_POST['price'] ?? '0'; $price = 0.0;
        if (extension_loaded('intl')) { $formatter = new \NumberFormatter('id_ID', \NumberFormatter::DECIMAL); $parsedPrice = $formatter->parse(trim($priceInput)); if ($parsedPrice !== false) { $price = (float)$parsedPrice; }}
        if (($price == 0.0 && $priceInput !== '0') || !extension_loaded('intl')) { if (!extension_loaded('intl')) error_log("PHP Intl extension not loaded."); $cleanedPrice = preg_replace('/[^\d,\.]/', '', $priceInput); $cleanedPrice = str_replace(',', '.', $cleanedPrice); if (substr_count($cleanedPrice, '.') > 1) { $parts = explode('.', $cleanedPrice); $last = array_pop($parts); $cleanedPrice = implode('', $parts) . '.' . $last; } if (is_numeric($cleanedPrice)) { $price = (float)$cleanedPrice; } else { error_log("Failed parse price (store): " . $priceInput); }}
        $price = max(0.0, $price);

        // Validasi
        if ($categoryId <= 0 || empty($name)) {
            SessionHelper::setFlashData('old_input', $_POST); // <<< Gunakan setFlashData
            SessionHelper::setFlash('error', 'Kategori dan Nama wajib diisi.'); // <<< Kunci standar 'error'
            UrlHelper::redirect('/admin/menu/create'); return;
        }

        // Upload Gambar
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // ... (Logika upload lengkap seperti sebelumnya, set $imagePath jika berhasil) ...
             $uploadDir = '../public/assets/uploads/menu/'; if (!is_dir($uploadDir)) { if (!@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) { SessionHelper::setFlash('error', 'Gagal buat dir upload.'); SessionHelper::setFlashData('old_input', $_POST); UrlHelper::redirect('/admin/menu/create'); return; }} $extension = strtolower(pathinfo(basename($_FILES['image']['name']), PATHINFO_EXTENSION)); if(empty($extension)) $extension = 'jpg'; $fileName = uniqid('menu_', true) . '.' . $extension; $targetFile = $uploadDir . $fileName; $allowedTypes = ['jpg', 'jpeg', 'png', 'webp', 'avif']; if (!in_array($extension, $allowedTypes)) { SessionHelper::setFlash('error', 'Format gambar tidak diizinkan.'); SessionHelper::setFlashData('old_input', $_POST); UrlHelper::redirect('/admin/menu/create'); return; } if ($_FILES['image']['size'] > 1 * 1024 * 1024) { SessionHelper::setFlash('error', 'Ukuran gambar maks 1MB.'); SessionHelper::setFlashData('old_input', $_POST); UrlHelper::redirect('/admin/menu/create'); return; } if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) { $imagePath = 'assets/uploads/menu/' . $fileName; } else { SessionHelper::setFlash('error', 'Gagal upload gambar.'); SessionHelper::setFlashData('old_input', $_POST); UrlHelper::redirect('/admin/menu/create'); return; }
        }

        // Simpan DB
        $itemId = $this->menuItemModel->createMenuItem($categoryId, $name, $price, $description, $imagePath, $isAvailable);
        if ($itemId) {
            SessionHelper::setFlash('success', 'Item menu "' . SanitizeHelper::html($name) . '" ditambahkan.'); // <<< Kunci standar 'success'
            UrlHelper::redirect('/admin/menu');
        } else {
            SessionHelper::setFlashData('old_input', $_POST); // Simpan input jika gagal
            SessionHelper::setFlash('error', 'Gagal menambahkan item menu ke database.'); // <<< Kunci standar 'error'
            UrlHelper::redirect('/admin/menu/create');
        }
    }

    public function edit(int $id) {
        AuthHelper::requireAdmin(); $safeId = SanitizeHelper::integer($id); if ($safeId <= 0) { UrlHelper::redirect('/admin/menu'); return; } $menuItem = $this->menuItemModel->findById($safeId); if (!$menuItem) { SessionHelper::setFlash('error', 'Item tidak ditemukan.'); UrlHelper::redirect('/admin/menu'); return; } $categories = $this->categoryModel->getAllSorted();
        $this->view('admin.menu.edit', ['pageTitle' => 'Edit Item: '.SanitizeHelper::html($menuItem['name']),'menuItem' => $menuItem,'categories' => $categories], 'admin_layout');
    }

    public function update(int $id) {
         AuthHelper::requireAdmin(); $safeId = SanitizeHelper::integer($id); if ($safeId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/menu'); return; } $oldMenuItem = $this->menuItemModel->findById($safeId); if (!$oldMenuItem) { SessionHelper::setFlash('error', 'Item tidak ditemukan.'); UrlHelper::redirect('/admin/menu'); return; }

         // Sanitasi & Harga (Robust)
         $categoryId = SanitizeHelper::integer($_POST['category_id'] ?? 0); $name = SanitizeHelper::string($_POST['name'] ?? ''); $description = SanitizeHelper::string($_POST['description'] ?? null); $isAvailable = isset($_POST['is_available']) ? true : false; $removeImage = isset($_POST['remove_image']) ? true : false;
         $priceInput = $_POST['price'] ?? '0'; $price = 0.0; if (extension_loaded('intl')) { $formatter = new \NumberFormatter('id_ID', \NumberFormatter::DECIMAL); $parsedPrice = $formatter->parse(trim($priceInput)); if ($parsedPrice !== false) { $price = (float)$parsedPrice; }} if (($price == 0.0 && $priceInput !== '0') || !extension_loaded('intl')) { if (!extension_loaded('intl')) error_log("PHP Intl extension not loaded."); $cleanedPrice = preg_replace('/[^\d,\.]/', '', $priceInput); $cleanedPrice = str_replace(',', '.', $cleanedPrice); if (substr_count($cleanedPrice, '.') > 1) { $parts = explode('.', $cleanedPrice); $last = array_pop($parts); $cleanedPrice = implode('', $parts) . '.' . $last; } if (is_numeric($cleanedPrice)) { $price = (float)$cleanedPrice; } else { error_log("Failed parse price (update): " . $priceInput); }} $price = max(0.0, $price);

         // Validasi
         if ($categoryId <= 0 || empty($name)) {
             SessionHelper::setFlashData('old_input', $_POST); // <<< Gunakan setFlashData
             SessionHelper::setFlash('error', 'Kategori dan Nama wajib diisi.'); // <<< Kunci standar 'error'
             UrlHelper::redirect('/admin/menu/edit/' . $safeId); return;
         }

         // Handle Gambar Update
         $currentImagePath = $oldMenuItem['image_path']; $imagePathForUpdate = null; $pathChanged = false; $oldImagePathFull = !empty($currentImagePath) ? '../public/' . $currentImagePath : null;
         if ($removeImage) { $imagePathForUpdate = ''; $pathChanged = true; if ($oldImagePathFull && file_exists($oldImagePathFull)) { @unlink($oldImagePathFull); }}
         elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // ... (Logika upload lengkap seperti di store, termasuk validasi) ...
             $uploadDir = '../public/assets/uploads/menu/'; if (!is_dir($uploadDir)) { if (!@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) { SessionHelper::setFlash('error', 'Gagal buat dir upload.'); SessionHelper::setFlashData('old_input', $_POST); UrlHelper::redirect('/admin/menu/edit/' . $safeId); return; }} $extension = strtolower(pathinfo(basename($_FILES['image']['name']), PATHINFO_EXTENSION)); if(empty($extension)) $extension = 'jpg'; $fileName = uniqid('menu_', true) . '.' . $extension; $targetFile = $uploadDir . $fileName; $allowedTypes = ['jpg', 'jpeg', 'png', 'webp', 'avif']; if (!in_array($extension, $allowedTypes)) { SessionHelper::setFlash('error', 'Format gambar tidak diizinkan.'); SessionHelper::setFlashData('old_input', $_POST); UrlHelper::redirect('/admin/menu/edit/' . $safeId); return; } if ($_FILES['image']['size'] > 1 * 1024 * 1024) { SessionHelper::setFlash('error', 'Ukuran gambar maks 1MB.'); SessionHelper::setFlashData('old_input', $_POST); UrlHelper::redirect('/admin/menu/edit/' . $safeId); return; } if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) { if ($oldImagePathFull && file_exists($oldImagePathFull)) { @unlink($oldImagePathFull); } $imagePathForUpdate = 'assets/uploads/menu/' . $fileName; $pathChanged = true; } else { SessionHelper::setFlash('error', 'Gagal upload gambar baru.'); SessionHelper::setFlashData('old_input', $_POST); UrlHelper::redirect('/admin/menu/edit/' . $safeId); return; }
         }

         // Update DB
         $success = $this->menuItemModel->updateMenuItem($safeId, $categoryId, $name, $price, $description, ($pathChanged ? $imagePathForUpdate : null), $isAvailable);
         if ($success !== false) {
             SessionHelper::setFlash('success', 'Item menu diperbarui.'); // <<< Kunci standar 'success'
             UrlHelper::redirect('/admin/menu');
         } else {
             SessionHelper::setFlashData('old_input', $_POST); // Simpan input jika gagal
             SessionHelper::setFlash('error', 'Gagal update item.'); // <<< Kunci standar 'error'
             UrlHelper::redirect('/admin/menu/edit/' . $safeId);
         }
     }

     public function destroy(int $id) {
        AuthHelper::requireAdmin();
        $safeId = SanitizeHelper::integer($id);
        if ($safeId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            UrlHelper::redirect('/admin/menu');
            return;
        }

        $menuItem = $this->menuItemModel->findById($safeId);

        if ($menuItem) {
            // === CEK SEBELUM HAPUS ===
            $orderItemCount = $this->orderItemModel->countByMenuItemId($safeId);

            if ($orderItemCount > 0) {
                // Jika ada item pesanan terkait, JANGAN HAPUS
                SessionHelper::setFlash('error', 'Item menu "' . SanitizeHelper::html($menuItem['name']) . '" tidak dapat dihapus karena sudah pernah dipesan.');
            } else {
                // Jika tidak ada pesanan terkait, lanjutkan penghapusan
                if ($this->menuItemModel->deleteMenuItem($safeId)) {
                    // Hapus gambar jika ada
                    if (!empty($menuItem['image_path'])) {
                        $imagePathFull = '../public/' . $menuItem['image_path'];
                        if (file_exists($imagePathFull)) {
                            @unlink($imagePathFull);
                        }
                    }
                    SessionHelper::setFlash('success', 'Item menu "' . SanitizeHelper::html($menuItem['name']) . '" berhasil dihapus.');
                } else {
                    // Tangani jika deleteMenuItem gagal karena alasan lain (meskipun jarang jika count 0)
                    SessionHelper::setFlash('error', 'Gagal menghapus item menu. Terjadi kesalahan tak terduga.');
                }
            }
            // === AKHIR CEK ===
        } else {
            SessionHelper::setFlash('error', 'Item menu tidak ditemukan.');
        }
        UrlHelper::redirect('/admin/menu');
    }


     public function toggleAvailability(int $id) { /* ... (Kode AJAX tetap sama) ... */ AuthHelper::requireRole(['admin', 'staff']); /* ... */ }

    // === METHOD KATEGORI (Gunakan kunci flash standar) ===
    public function categories() { AuthHelper::requireAdmin(); $categories = $this->categoryModel->getAllSorted(); $this->view('admin.menu.categories', ['pageTitle' => 'Kelola Kategori Menu','categories' => $categories], 'admin_layout'); }
    public function storeCategory() { AuthHelper::requireAdmin(); if ($_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/categories'); return; } $name=SanitizeHelper::string($_POST['name']??''); $desc=SanitizeHelper::string($_POST['description']??null); $sort=(int)SanitizeHelper::integer($_POST['sort_order']??0); if(empty($name)){SessionHelper::setFlash('error','Nama wajib diisi.');}else{$catId=$this->categoryModel->createCategory($name,$desc,$sort);if($catId){SessionHelper::setFlash('success','Kategori ditambahkan.');}else{SessionHelper::setFlash('error','Gagal tambah kategori.');}} UrlHelper::redirect('/admin/categories');}
    public function updateCategory(int $id) { AuthHelper::requireAdmin(); $safeId=SanitizeHelper::integer($id); if($safeId<=0||$_SERVER['REQUEST_METHOD']!=='POST'){UrlHelper::redirect('/admin/categories');return;} $name=SanitizeHelper::string($_POST['name']??''); $desc=SanitizeHelper::string($_POST['description']??null); $sort=(int)SanitizeHelper::integer($_POST['sort_order']??0); if(empty($name)){SessionHelper::setFlash('error','Nama wajib diisi.');}else{$success=$this->categoryModel->updateCategory($safeId,$name,$desc,$sort);if($success!==false){SessionHelper::setFlash('success','Kategori diperbarui.');}else{SessionHelper::setFlash('error','Gagal update kategori.');}} UrlHelper::redirect('/admin/categories');}
    public function destroyCategory(int $id) { AuthHelper::requireAdmin(); $safeId=SanitizeHelper::integer($id); if($safeId<=0||$_SERVER['REQUEST_METHOD']!=='POST'){UrlHelper::redirect('/admin/categories');return;} $cat=$this->categoryModel->findById($safeId);$catName=$cat?$cat['name']:'ID '.$safeId;$success=$this->categoryModel->deleteCategory($safeId);if($success){SessionHelper::setFlash('success','Kategori dihapus.');}else{SessionHelper::setFlash('error','Gagal hapus kategori.');} UrlHelper::redirect('/admin/categories');}

} // Akhir kelas
?>