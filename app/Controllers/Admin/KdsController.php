<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\OrderItem; // Untuk menampilkan detail item di KDS
use App\Helpers\AuthHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper;

class KdsController extends Controller {

    public function index() {
        AuthHelper::requireRole(['admin', 'kitchen']);

        $orderModel = new Order();
        $orders = $orderModel->findByStatus(['received', 'preparing'], 'order_time ASC');

        $orderItemModel = new OrderItem();
        foreach ($orders as &$order) {
            $order['items'] = $orderItemModel->findByOrderId($order['id']);
        }
        unset($order);

        $this->view('admin.kds.index', [
            'pageTitle' => 'Kitchen Display System',
            'orders' => $orders
        ], null);
    }

    public function getOrders() {
        AuthHelper::requireRole(['admin', 'kitchen']);

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
             return $this->jsonResponse(['success' => false, 'message' => 'Metode tidak valid.'], 405);
        }

        $orderModel = new Order();
        $orders = $orderModel->findByStatus(['received', 'preparing'], 'order_time ASC');

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

        // PERBAIKAN: Tambahkan 'cancelled' ke status yang diizinkan dari KDS
        $allowedStatuses = ['preparing', 'ready', 'cancelled'];

         if ($orderId <= 0 || !in_array($newStatus, $allowedStatuses)) {
             return $this->jsonResponse(['success' => false, 'message' => 'ID Pesanan atau status tidak valid untuk KDS.'], 400);
         }

         $orderModel = new Order();
         $currentOrder = $orderModel->findById($orderId);

         if(!$currentOrder) {
              return $this->jsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
         }

         // PERBAIKAN: Logika transisi status yang lebih jelas
         $validTransitions = [
             'received' => ['preparing'],
             'preparing' => ['ready', 'cancelled'] // Izinkan batal dari 'preparing'
         ];

         if (!isset($validTransitions[$currentOrder['status']]) || !in_array($newStatus, $validTransitions[$currentOrder['status']])) {
              return $this->jsonResponse([
                  'success' => false,
                  'message' => "Tidak bisa mengubah status dari '{$currentOrder['status']}' ke '{$newStatus}'."
              ], 400);
          }

         if ($orderModel->updateStatus($orderId, $newStatus)) {
              return $this->jsonResponse(['success' => true, 'message' => 'Status pesanan berhasil diperbarui.']);
         } else {
              return $this->jsonResponse(['success' => false, 'message' => 'Gagal memperbarui status pesanan di database.'], 500);
         }
    }
}