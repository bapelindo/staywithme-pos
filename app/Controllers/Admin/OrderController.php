<?php
// File: app/Controllers/Admin/OrderController.php (Revisi Notifikasi)
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Helpers\AuthHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\NumberHelper;
use App\Helpers\DateHelper;

class OrderController extends Controller {

    /**
     * Menampilkan daftar semua pesanan (atau filter berdasarkan status).
     */
    public function index() {
        AuthHelper::requireRole(['admin', 'staff']);

        $statusFilter = isset($_GET['status']) ? SanitizeHelper::string($_GET['status']) : 'all';
        $page = isset($_GET['page']) ? max(1, SanitizeHelper::integer($_GET['page'])) : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $orderModel = new Order();
        $orders = [];
        $totalOrders = 0;

        // === PERUBAHAN: Pastikan filter 'pending_payment' bisa jalan ===
        // Cek apakah model sudah bisa handle 'pending_payment' di method get/count by status
        // (Berdasarkan kode model sebelumnya, ini seharusnya sudah bisa)
        $allowedStatusesForFilter = ['pending_payment', 'received', 'preparing', 'ready', 'served', 'cancelled'];

        if ($statusFilter === 'all' || empty($statusFilter)) {
             $orders = $orderModel->getAllOrdersPaginated($limit, $offset);
             $totalOrders = $orderModel->countAllOrders();
        } elseif (in_array($statusFilter, $allowedStatusesForFilter)) { // Cek status valid
             $orders = $orderModel->getOrdersByStatusPaginated($statusFilter, $limit, $offset);
             $totalOrders = $orderModel->countByStatus($statusFilter);
        } else {
            // Handle jika status tidak valid, mungkin redirect atau tampilkan semua
            $statusFilter = 'all'; // Reset ke semua jika tidak valid
            $orders = $orderModel->getAllOrdersPaginated($limit, $offset);
            $totalOrders = $orderModel->countAllOrders();
             SessionHelper::setFlash('error', 'Filter status tidak valid.'); // Beri pesan error
        }
        // === AKHIR PERUBAHAN (minor check) ===


        $totalPages = ceil($totalOrders / $limit);

        $this->view('admin.orders.index', [
            'pageTitle' => 'Daftar Pesanan',
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'statusFilter' => $statusFilter,
            'totalOrders' => $totalOrders
        ], 'admin_layout');
    }

    /**
     * Menampilkan detail satu pesanan.
     */
    public function show(int $order_id) {
        // ... (Kode method show tetap sama) ...
        AuthHelper::requireRole(['admin', 'staff', 'kitchen']); // Kitchen juga boleh lihat detail?

        $safeOrderId = SanitizeHelper::integer($order_id);
        if ($safeOrderId <= 0) {
            SessionHelper::setFlash('error', 'ID Pesanan tidak valid.');
            UrlHelper::redirect('/admin/orders');
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->getOrderWithDetails($safeOrderId);

        if (!$order) {
            SessionHelper::setFlash('error', 'Pesanan tidak ditemukan.');
            UrlHelper::redirect('/admin/orders');
            return;
        }

         // Cek data pembayaran
        $paymentModel = new Payment();
        $payment = $paymentModel->findByOrderId($safeOrderId);


        $this->view('admin.orders.show', [
            'pageTitle' => 'Detail Pesanan ' . SanitizeHelper::html($order['order_number']),
            'order' => $order,
            'payment' => $payment
        ], 'admin_layout');
    }


    /**
     * Endpoint AJAX untuk Polling pesanan baru (status 'pending_payment').
     * Digunakan untuk notifikasi di dashboard admin atau OSS.
     */
    public function getNewOrders() {
        AuthHelper::requireRole(['admin', 'staff']); // Hanya admin & staff

        $lastSeenId = isset($_GET['lastSeenId']) ? SanitizeHelper::integer($_GET['lastSeenId']) : 0;

        $orderModel = new Order();

        // === PERUBAHAN DI SINI: Ubah status yang dicari ===
        // Ambil order baru dengan status 'pending_payment' yang ID nya lebih besar dari lastSeenId
        $newOrders = $orderModel->getNewOrdersSince($lastSeenId, 'pending_payment'); // <-- Ubah 'received' menjadi 'pending_payment'
        // === AKHIR PERUBAHAN ===

        // Hanya kirim data minimal
        $ordersData = array_map(fn($order) => [
            'id' => $order['id'],
            'order_number' => $order['order_number'],
            'table_number' => $order['table_number'] ?? 'N/A', // Handle jika join gagal/belum ada
            'order_time' => DateHelper::timeAgo($order['order_time']) // Format waktu
        ], $newOrders);

        return $this->jsonResponse([
            'success' => true,
            'new_orders' => $ordersData
        ]);
    }

    /**
     * Menerima update status order dari Admin Panel atau KDS (via AJAX).
     */
    public function updateStatus() {
        // ... (Kode method updateStatus tetap sama) ...
         AuthHelper::requireRole(['admin', 'staff', 'kitchen']); // Semua boleh update status? Atur sesuai kebutuhan

         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
        }

        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['order_id']) || !isset($data['new_status'])) {
             return $this->jsonResponse(['success' => false, 'message' => 'Data tidak valid.'], 400);
        }

        $orderId = SanitizeHelper::integer($data['order_id']);
        $newStatus = SanitizeHelper::string($data['new_status']);
        // Daftar status yang boleh di-set dari sini (sesuaikan)
        // 'pending_payment' tidak diubah dari sini, hanya dari kasir saat bayar
        $allowedStatuses = ['received', 'preparing', 'ready', 'served', 'cancelled']; // 'paid' dihandle oleh payment

        if ($orderId <= 0 || !in_array($newStatus, $allowedStatuses)) {
             return $this->jsonResponse(['success' => false, 'message' => 'ID Pesanan atau status tidak valid.'], 400);
        }

        $orderModel = new Order();
        $order = $orderModel->findById($orderId);
        if (!$order) {
             return $this->jsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }
        // Validasi transisi status jika perlu

        if ($orderModel->updateStatus($orderId, $newStatus)) {
             // TODO: Implement Push Notification / Update ke client jika menggunakan WebSocket
             return $this->jsonResponse(['success' => true, 'message' => 'Status pesanan berhasil diperbarui.']);
        } else {
             return $this->jsonResponse(['success' => false, 'message' => 'Gagal memperbarui status pesanan.'], 500);
        }
    }

    /**
     * Menampilkan halaman invoice (HTML view).
     */
    public function invoice(int $order_id) {
        // ... (Kode method invoice tetap sama) ...
        AuthHelper::requireRole(['admin', 'staff']); // Hanya Admin/Staff

        $safeOrderId = SanitizeHelper::integer($order_id);
         if ($safeOrderId <= 0) {
            SessionHelper::setFlash('error', 'ID Pesanan tidak valid.');
            UrlHelper::redirect('/admin/orders');
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->getOrderWithDetails($safeOrderId);

        if (!$order) {
            SessionHelper::setFlash('error', 'Pesanan tidak ditemukan.');
            UrlHelper::redirect('/admin/orders');
            return;
        }

        // Ambil data payment jika ada
        $paymentModel = new Payment();
        $payment = $paymentModel->findByOrderId($safeOrderId);

        $this->view('admin.orders.invoice', [
             'pageTitle' => 'Invoice ' . SanitizeHelper::html($order['order_number']),
             'order' => $order,
             'payment' => $payment,
             'use_layout' => false
        ], null);
    }

     /**
      * Memproses pembayaran cash (dipanggil dari tombol "Tandai Lunas").
      * Method ini sekarang akan mengubah status menjadi 'received' via Payment model.
      */
     public function processCashPayment(int $order_id) {
        // ... (Kode method processCashPayment tetap sama, karena perubahan utama ada di Model Payment) ...
         AuthHelper::requireRole(['admin', 'staff']);

         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             UrlHelper::redirect('/admin/orders/show/' . $order_id);
             return;
         }

         $safeOrderId = SanitizeHelper::integer($order_id);
         $orderModel = new Order();
         $order = $orderModel->findById($safeOrderId);

         // Hanya proses jika statusnya 'pending_payment'
         if (!$order || $order['status'] !== 'pending_payment') {
             SessionHelper::setFlash('error', 'Pesanan tidak valid atau sudah diproses/dibatalkan.');
             UrlHelper::redirect('/admin/orders/show/' . $safeOrderId);
             return;
         }

         $amountPaid = (float) $order['total_amount']; // Asumsi bayar penuh
         $paymentModel = new Payment();
         $userId = AuthHelper::getUserId();

         // Panggil createPayment (yang sudah diubah untuk set status ke 'received')
         if ($paymentModel->createPayment($safeOrderId, $amountPaid, 'cash', $userId)) {
             SessionHelper::setFlash('success', 'Pembayaran cash berhasil diproses. Pesanan diteruskan ke dapur.');
         } else {
             SessionHelper::setFlash('error', 'Gagal memproses pembayaran.');
         }
         UrlHelper::redirect('/admin/orders/show/' . $safeOrderId); // Redirect ke detail order
     }
}
?>