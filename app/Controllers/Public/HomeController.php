<?php
// File: app/Controllers/Public/HomeController.php (Direvisi - Tanpa mengubah OrderItem Model)

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\OrderItem; // <-- Tetap diperlukan
use DateTime;

class HomeController extends Controller
{
    public function index()
    {
        // Instansiasi model
        $menuItemModel = new MenuItem();
        $categoryModel = new Category();
        $orderItemModel = new OrderItem();

        // 1. Tentukan rentang tanggal (Contoh: 30 hari terakhir)
        $endDate = date('Y-m-d');
        $startDate = (new DateTime($endDate))->modify('-30 days')->format('Y-m-d');

        // 2. Ambil data populer (hanya nama & jumlah) dari OrderItem Model
        $popularData = $orderItemModel->getPopularItems($startDate, $endDate, 5); //

        // 3. Ambil SEMUA item menu yang AKTIF dengan detail lengkap
        $menuItems = $menuItemModel->getAllAvailableGroupedByCategory(); //

        // 4. Proses untuk mendapatkan detail lengkap Top 5
        $topItems = [];
        if (!empty($popularData) && !empty($menuItems)) {
            // Buat map jumlah terjual berdasarkan nama item
            $popularQuantities = array_column($popularData, 'total_quantity', 'menu_item_name');

            // Cari detail lengkap item populer dari $menuItems
            foreach ($menuItems as $item) {
                if (isset($popularQuantities[$item['name']])) {
                    // Item ini termasuk populer, tambahkan detail & jumlah terjual
                    $item['total_quantity'] = $popularQuantities[$item['name']];
                    $topItems[] = $item;
                }
            }

            // Urutkan $topItems berdasarkan 'total_quantity' secara descending
            usort($topItems, function($a, $b) {
                return ($b['total_quantity'] ?? 0) <=> ($a['total_quantity'] ?? 0);
            });

            // Pastikan hanya ada 5 item teratas (jika ada duplikasi nama atau proses filtering menghasilkan lebih)
             $topItems = array_slice($topItems, 0, 5);
        }
        // Jika $popularData kosong atau $menuItems kosong, $topItems akan tetap kosong.

        // 5. Ambil semua kategori (meski tidak dipakai di view carousel)
        $categories = $categoryModel->getAllSorted(); //

        // Data untuk dikirim ke view
        $data = [
            'pageTitle' => 'Selamat Datang',
            'topItems' => $topItems,         // <-- Top 5 dengan detail & qty
            'menuItems' => $menuItems,      // <-- Semua menu dengan detail
            'categories' => $categories    // <-- Kategori (jika masih diperlukan di tempat lain)
        ];

        // Load view 'home' dengan layout 'public_layout' dan kirim data
        $this->view('public/home', $data, 'public_layout');
    }
}