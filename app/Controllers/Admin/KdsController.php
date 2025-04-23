<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\OrderItem; // Untuk menampilkan detail item di KDS
use App\Helpers\AuthHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper;

class KdsController extends Controller {

    /**
     * Menampilkan halaman Kitchen Display System (KDS).
     */
    public function index() {
        // KDS diakses oleh role 'kitchen' atau 'admin'
        AuthHelper::requireRole(['admin', 'kitchen']);

        // Ambil data awal (order 'received' dan 'preparing')
        $orderModel = new Order();
        $orders = $orderModel->findByStatus(['received', 'preparing'], 'order_time ASC');

        // Ambil detail item untuk setiap order
        $orderItemModel = new OrderItem();
        foreach ($orders as &$order) { // Gunakan reference (&) untuk modifikasi array asli
            $order['items'] = $orderItemModel->findByOrderId($order['id']);
        }
        unset($order); // Hapus reference setelah loop

        $this->view('admin.kds.index', [
            'pageTitle' => 'Kitchen Display System',
            'orders' => $orders
            // Mungkin perlu layout khusus KDS
        ]);
    }

    /**
     * Endpoint AJAX untuk polling data order terbaru untuk KDS.
     * Mengembalikan daftar order 'received' dan 'preparing' beserta itemnya.
     */
    public function getOrders() {
        AuthHelper::requireRole(['admin', 'kitchen']);

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
             return $this->jsonResponse(['success' => false, 'message' => 'Metode tidak valid.'], 405);
        }

        $orderModel = new Order();
        $orders = $orderModel->findByStatus(['received', 'preparing'], 'order_time ASC');

        // Ambil detail item dan format data untuk JSON response
        $orderItemModel = new OrderItem();
        $formattedOrders = [];
        foreach ($orders as $order) {
             $items = $orderItemModel->findByOrderId($order['id']);
             $formattedItems = array_map(fn($item) => [
                 'name' => $item['menu_item_name'],
                 'quantity' => $item['quantity'],
                 'notes' => $item['notes']
             ], $items);

             $formattedOrders[] = [
                 'id' => $order['id'],
                 'order_number' => $order['order_number'],
                 'table_number' => $order['table_number'],
                 'status' => $order['status'],
                 'order_time_ago' => DateHelper::timeAgo($order['order_time']),
                 'items' => $formattedItems
             ];
        }

        return $this->jsonResponse([
            'success' => true,
            'orders' => $formattedOrders
        ]);
    }

     /**
     * Endpoint AJAX untuk update status order DARI KDS.
     * (Mirip dengan OrderController::updateStatus tapi mungkin lebih terbatas)
     */
    public function updateOrderStatus() {
        AuthHelper::requireRole(['admin', 'kitchen']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             return $this->jsonResponse(['success' => false, 'message' => 'Metode tidak valid.'], 405);
        }

        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['order_id']) || !isset($data['new_status'])) {
             return $this->jsonResponse(['success' => false, 'message' => 'Data tidak valid.'], 400);
        }

        $orderId = SanitizeHelper::integer($data['order_id']);
        $newStatus = SanitizeHelper::string($data['new_status']);

        // *** PERUBAHAN DI SINI: Tambahkan 'cancelled' ***
        // Status yang boleh di-set DARI KDS: preparing, ready, cancelled
        $allowedStatuses = ['preparing', 'ready', 'cancelled']; // <-- Tambahkan 'cancelled'

         if ($orderId <= 0 || !in_array($newStatus, $allowedStatuses)) {
             // Pesan error ini mungkin perlu disesuaikan jika Anda mengizinkan lebih banyak status
             return $this->jsonResponse(['success' => false, 'message' => 'ID Pesanan atau status tidak valid untuk KDS.'], 400);
         }

         // Ambil order saat ini untuk validasi transisi
         $orderModel = new Order();
         $currentOrder = $orderModel->findById($orderId);

         if(!$currentOrder) {
              return $this->jsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
         }

         // *** PERUBAHAN (OPSIONAL): Definisikan transisi ke 'cancelled' ***
         // Validasi transisi sederhana
         $validTransitions = [
             'received' => ['preparing'],
             'preparing' => ['ready', 'cancelled'] // <-- Izinkan batal dari 'preparing'
             // Tambahkan transisi lain jika perlu (misal: bisa batal dari 'received'?)
             // 'received' => ['preparing', 'cancelled'],
         ];

         // Hanya lakukan validasi transisi jika status baru BUKAN 'cancelled'
         // atau jika Anda ingin membatasi pembatalan hanya dari status tertentu
         if ($newStatus !== 'cancelled' && (!isset($validTransitions[$currentOrder['status']]) || !in_array($newStatus, $validTransitions[$currentOrder['status']]))) {
              return $this->jsonResponse([
                  'success' => false,
                  'message' => "Tidak bisa mengubah status dari '{$currentOrder['status']}' ke '{$newStatus}'."
              ], 400);
          }
          // Jika Anda INGIN memvalidasi status asal pembatalan:
         else if ($newStatus === 'cancelled' && (!isset($validTransitions[$currentOrder['status']]) || !in_array('cancelled', $validTransitions[$currentOrder['status']]))) {
               return $this->jsonResponse([
                   'success' => false,
                   'message' => "Tidak bisa membatalkan pesanan dari status '{$currentOrder['status']}'."
               ], 400);
           }


         // Update status menggunakan Order Model
         if ($orderModel->updateStatus($orderId, $newStatus)) {
             // TODO: Push notification jika pakai WebSocket
              return $this->jsonResponse(['success' => true, 'message' => 'Status pesanan berhasil diperbarui.']);
         } else {
              // Kemungkinan gagal karena rowCount() == 0 di model jika status sudah sama, atau error DB
              return $this->jsonResponse(['success' => false, 'message' => 'Gagal memperbarui status pesanan di database.'], 500);
         }
    }

}
?>