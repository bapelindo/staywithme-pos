<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Table;
use App\Helpers\SanitizeHelper;
use App\Helpers\SessionHelper; // Untuk pesan flash (meski AJAX lebih umum di sini)
use App\Helpers\UrlHelper;

class OrderController extends Controller {

    /**
     * Menerima data pesanan (dari AJAX di halaman menu) dan menyimpannya.
     * Mengembalikan response JSON.
     */
    public function placeOrder() {
        // Hanya izinkan metode POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
        }

        // Ambil data JSON dari body request (umumnya dari fetch JS)
        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        // Validasi data dasar
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['table_id']) || !isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Data pesanan tidak valid.'], 400);
        }

        // Sanitasi data
        $tableId = SanitizeHelper::integer($data['table_id']);
        $notes = isset($data['notes']) ? SanitizeHelper::string($data['notes']) : null;
        $items = [];
        foreach ($data['items'] as $item) {
            if (!isset($item['menu_item_id']) || !isset($item['quantity'])) continue;
            $sanitizedItem = [
                'menu_item_id' => SanitizeHelper::integer($item['menu_item_id']),
                'quantity' => SanitizeHelper::integer($item['quantity']),
                'notes' => isset($item['notes']) ? SanitizeHelper::string($item['notes']) : null,
            ];
            // Validasi kuantitas dan ID item minimal
            if ($sanitizedItem['menu_item_id'] > 0 && $sanitizedItem['quantity'] > 0) {
                $items[] = $sanitizedItem;
            }
        }

        if (empty($items) || $tableId <= 0) {
             return $this->jsonResponse(['success' => false, 'message' => 'Item pesanan atau ID meja tidak valid.'], 400);
        }

        // Cek apakah meja valid (optional tapi bagus)
        $tableModel = new Table();
        if (!$tableModel->findById($tableId)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Meja tidak ditemukan.'], 404);
        }

        // Proses pembuatan order menggunakan Model
        $orderModel = new Order();
        $orderId = $orderModel->createOrder($tableId, $items, $notes);

        if ($orderId) {
            // Sukses membuat order
            $newOrderData = $orderModel->findById($orderId); // Ambil data order baru untuk nomornya
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat!',
                'order_id' => $orderId,
                'order_number' => $newOrderData['order_number'] ?? null, // Sertakan nomor order jika perlu
                'redirect_url' => UrlHelper::baseUrl('/order/status/' . $orderId) // URL Halaman Status
            ]);
        } else {
            // Gagal membuat order
            return $this->jsonResponse(['success' => false, 'message' => 'Gagal membuat pesanan. Silakan coba lagi.'], 500);
        }
    }

    /**
     * Menampilkan halaman status pesanan untuk pelanggan.
     *
     * @param int $order_id ID Pesanan.
     */
    public function showStatus(int $order_id) {
        $safeOrderId = SanitizeHelper::integer($order_id);
        if ($safeOrderId <= 0) {
             // Redirect atau tampilkan error jika ID tidak valid
             UrlHelper::redirect('/'); // Ke home misalnya
             return;
        }

        $orderModel = new Order();
        $order = $orderModel->getOrderWithDetails($safeOrderId);

        if (!$order) {
            http_response_code(404);
            $this->view('public.errors.404', ['message' => 'Pesanan tidak ditemukan.']);
            return;
        }

        // Load view status pesanan
        $this->view('public.order_status', [
            'order' => $order,
            'pageTitle' => 'Status Pesanan ' . SanitizeHelper::html($order['order_number'])
        ]);
    }

    /**
     * Endpoint AJAX untuk Polling status pesanan dari halaman status pelanggan.
     * Mengembalikan status terbaru dalam format JSON.
     *
     * @param int $order_id ID Pesanan.
     */
    public function getStatusUpdate(int $order_id) {
        $safeOrderId = SanitizeHelper::integer($order_id);
        if ($safeOrderId <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'ID Pesanan tidak valid.'], 400);
        }

        $orderModel = new Order();
        $order = $orderModel->findById($safeOrderId); // Hanya perlu status

        if (!$order) {
            return $this->jsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        return $this->jsonResponse([
            'success' => true,
            'order_id' => $order['id'],
            'status' => $order['status'] // Kirim status terbaru
        ]);
    }
}
?>