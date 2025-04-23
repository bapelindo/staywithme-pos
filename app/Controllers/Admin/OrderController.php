<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Helpers\AuthHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\NumberHelper; // Untuk format harga di invoice
use App\Helpers\DateHelper;   // Untuk format tanggal di invoice

class OrderController extends Controller {

    /**
     * Menampilkan daftar semua pesanan (atau filter berdasarkan status).
     */
    public function index() {
        AuthHelper::requireRole(['admin', 'staff']); // Hanya admin & staff

        $statusFilter = isset($_GET['status']) ? SanitizeHelper::string($_GET['status']) : 'all';
        $page = isset($_GET['page']) ? max(1, SanitizeHelper::integer($_GET['page'])) : 1;
        $limit = 15; // Jumlah order per halaman
        $offset = ($page - 1) * $limit;

        $orderModel = new Order();
        $orders = [];
        $totalOrders = 0;

        // Ambil data order berdasarkan filter status
        // Anda perlu membuat method di Order Model yang lebih fleksibel, misal:
        // $orders = $orderModel->getOrdersFiltered($statusFilter, $limit, $offset);
        // $totalOrders = $orderModel->countOrdersFiltered($statusFilter);

        // Contoh sederhana: Ambil semua order terbaru
        if ($statusFilter === 'all' || empty($statusFilter)) {
             $orders = $orderModel->getAllOrdersPaginated($limit, $offset); // Perlu method getAllOrdersPaginated
             $totalOrders = $orderModel->countAllOrders(); // Perlu method countAllOrders
        } else {
             $orders = $orderModel->getOrdersByStatusPaginated($statusFilter, $limit, $offset); // Perlu method getOrdersByStatusPaginated
             $totalOrders = $orderModel->countByStatus($statusFilter); // Perlu method countByStatus
        }


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
     *
     * @param int $order_id ID Pesanan.
     */
    public function show(int $order_id) {
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
     * Endpoint AJAX untuk Polling pesanan baru (status 'received').
     * Digunakan untuk notifikasi di dashboard admin atau OSS.
     * **Modifikasi: Kembalikan juga total_amount dan format data**
     */
    public function getNewOrders() {
        AuthHelper::requireRole(['admin', 'staff']); // Hanya admin & staff

        $lastSeenId = isset($_GET['lastSeenId']) ? SanitizeHelper::integer($_GET['lastSeenId']) : 0;

        $orderModel = new Order();
        // Ambil order baru dengan status 'received' (atau sesuaikan jika perlu status lain)
        $newOrders = $orderModel->getNewOrdersSince($lastSeenId, 'received');

        // Hanya kirim data yang relevan dan format jika perlu
        $ordersData = array_map(function($order) {
            return [
                'id' => $order['id'],
                'order_number' => $order['order_number'],
                'table_number' => $order['table_number'],
                'order_time_full' => DateHelper::formatIndonesian($order['order_time'], 'full'), // Format lengkap
                'order_time_ago' => DateHelper::timeAgo($order['order_time']), // Format relatif
                'total_amount' => NumberHelper::formatCurrencyIDR($order['total_amount']), // Format mata uang
                'status' => $order['status'],
                'status_text' => SanitizeHelper::html($this->getStatusText($order['status'])), // Helper text (opsional)
                'status_class' => $this->getStatusClass($order['status']) // Helper class (opsional)
            ];
        }, $newOrders);

        return $this->jsonResponse([
            'success' => true,
            'new_orders' => $ordersData
        ]);
    }

    // Helper kecil untuk status (bisa juga ditaruh di helper terpisah)
    private function getStatusText(string $status): string {
        $map = [
            'pending' => 'Pending', 'received' => 'Diterima', 'preparing' => 'Disiapkan',
            'ready' => 'Siap', 'served' => 'Disajikan', 'paid' => 'Lunas', 'cancelled' => 'Batal'
        ];
        return $map[$status] ?? ucfirst($status);
    }

    private function getStatusClass(string $status): string {
        $map = [
            'pending' => 'bg-gray-100 text-gray-600', 'received' => 'bg-blue-100 text-blue-700',
            'preparing' => 'bg-yellow-100 text-yellow-700 animate-pulse', 'ready' => 'bg-teal-100 text-teal-700',
            'served' => 'bg-green-100 text-green-700', 'paid' => 'bg-indigo-100 text-indigo-700',
            'cancelled' => 'bg-red-100 text-red-700',
        ];
        return $map[$status] ?? 'bg-gray-100 text-gray-600';
    }

    /**
     * Menerima update status order dari Admin Panel atau KDS (via AJAX).
     */
    public function updateStatus() {
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
        // Daftar status yang boleh di-set dari sini
        $allowedStatuses = ['preparing', 'ready', 'served', 'cancelled']; // 'paid' dihandle oleh payment

        if ($orderId <= 0 || !in_array($newStatus, $allowedStatuses)) {
             return $this->jsonResponse(['success' => false, 'message' => 'ID Pesanan atau status tidak valid.'], 400);
        }

        $orderModel = new Order();
        // Cek order ada atau tidak (optional)
        $order = $orderModel->findById($orderId);
        if (!$order) {
             return $this->jsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }
        // Anda bisa tambahkan validasi transisi status jika perlu (misal: tidak bisa dari 'served' ke 'preparing')

        if ($orderModel->updateStatus($orderId, $newStatus)) {
             // TODO: Implement Push Notification / Update ke client jika menggunakan WebSocket
             return $this->jsonResponse(['success' => true, 'message' => 'Status pesanan berhasil diperbarui.']);
        } else {
             return $this->jsonResponse(['success' => false, 'message' => 'Gagal memperbarui status pesanan.'], 500);
        }
    }

    /**
     * Menampilkan halaman invoice (HTML view).
     *
     * @param int $order_id ID Pesanan.
     */
    public function invoice(int $order_id) {
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

        // Ambil detail cafe dari setting (jika ada)
        // $settings = SettingsModel::getAll();

        // Load view invoice (buat file view khusus invoice)
        $this->view('admin.orders.invoice', [
             'pageTitle' => 'Invoice ' . SanitizeHelper::html($order['order_number']),
             'order' => $order,
             'payment' => $payment,
             // 'cafe_details' => $settings,
             // Jangan gunakan layout admin utama untuk invoice agar mudah dicetak
             'use_layout' => false // Variabel untuk mengontrol layout di Base Controller::view()
        ], null);
    }

     /**
      * Memproses pembayaran cash (jika ada tombol bayar di detail order).
      *
      * @param int $order_id
      */
     public function processCashPayment(int $order_id) {
         AuthHelper::requireRole(['admin', 'staff']);

         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             UrlHelper::redirect('/admin/orders/show/' . $order_id);
             return;
         }

         $safeOrderId = SanitizeHelper::integer($order_id);
         $orderModel = new Order();
         $order = $orderModel->findById($safeOrderId);

         if (!$order || $order['status'] === 'paid' || $order['status'] === 'cancelled') {
             SessionHelper::setFlash('error', 'Pesanan tidak valid atau sudah dibayar/dibatalkan.');
             UrlHelper::redirect('/admin/orders/show/' . $safeOrderId);
             return;
         }

         $amountPaid = (float) $order['total_amount']; // Asumsi bayar penuh
         $paymentModel = new Payment();
         $userId = AuthHelper::getUserId();

         if ($paymentModel->createPayment($safeOrderId, $amountPaid, 'cash', $userId)) {
             SessionHelper::setFlash('success', 'Pembayaran cash berhasil diproses.');
         } else {
             SessionHelper::setFlash('error', 'Gagal memproses pembayaran.');
         }
         UrlHelper::redirect('/admin/orders/show/' . $safeOrderId);
     }
}
?>