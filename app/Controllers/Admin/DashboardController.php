<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\AuthHelper;
use App\Helpers\SanitizeHelper;
use App\Models\Order; // Contoh jika ingin menampilkan statistik order
use App\Models\MenuItem;

class DashboardController extends Controller {

    public function index() {
        // 1. Cek otentikasi & otorisasi (Admin, Staff, Kitchen boleh akses dashboard)
        AuthHelper::requireRole(['admin', 'staff', 'kitchen']);

        // 2. Ambil data untuk ditampilkan di dashboard
        $orderModel = new Order();
        $menuItemModel = new MenuItem(); // Jika perlu info menu

        // Contoh data: Jumlah order baru, jumlah order diproses, item habis
        $newOrderCount = $orderModel->countByStatus('received'); // Perlu method countByStatus di Order model
        $processingOrderCount = $orderModel->countByStatus(['preparing', 'ready']);
        $unavailableItemCount = $menuItemModel->countUnavailable(); // Perlu method countUnavailable di MenuItem model

        $userName = SanitizeHelper::html(AuthHelper::getUserName());

        // 3. Load View Dashboard
        $this->view('admin.dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'userName' => $userName,
            'newOrderCount' => $newOrderCount,
            'processingOrderCount' => $processingOrderCount,
            'unavailableItemCount' => $unavailableItemCount,
            
            // Kirim data lain yang relevan
        ], 'admin_layout');
    }
}
?>