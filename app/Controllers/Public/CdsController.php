<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\Order;
use App\Helpers\SanitizeHelper;

class CdsController extends Controller {

    /**
     * Menampilkan halaman Customer Display System (CDS).
     * Biasanya ditampilkan di layar dekat kasir/area tunggu.
     */
    public function index() {
        // Ambil data awal order yang sedang disiapkan atau siap diambil
        $orderModel = new Order();
        $preparingOrders = $orderModel->findByStatus('preparing', 'order_time ASC');
        $readyOrders = $orderModel->findByStatus('ready', 'updated_at ASC'); // Urutkan yg ready terbaru di atas? Atau terlama?

        $this->view('public.cds', [
            'preparingOrders' => $preparingOrders,
            'readyOrders' => $readyOrders,
            'pageTitle' => 'Status Pesanan - Stay With Me Cafe'
            // Jangan pakai layout utama jika ini untuk layar penuh
        ], 'admin_layout');
    }

    /**
     * Endpoint AJAX untuk polling data order terbaru untuk CDS.
     * Mengembalikan daftar order 'preparing' dan 'ready'.
     */
    public function getOrders() {
         // Hanya izinkan GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $this->jsonResponse(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
        }

        $orderModel = new Order();
        // Ambil order dengan status 'preparing' atau 'ready'
        // Mungkin perlu join dengan table untuk nomor meja juga jika desain memerlukan
        $preparingOrders = $orderModel->findByStatus('preparing', 'order_time ASC', 10); // Batasi misal 10
        $readyOrders = $orderModel->findByStatus('ready', 'updated_at DESC', 10); // Batasi misal 10

        // Hanya kirim data yang relevan (misal: nomor order, status, mungkin nama singkat)
        $prepareList = array_map(fn($order) => ['number' => $order['order_number']], $preparingOrders);
        $readyList = array_map(fn($order) => ['number' => $order['order_number']], $readyOrders);


        return $this->jsonResponse([
            'success' => true,
            'preparing' => $prepareList,
            'ready' => $readyList
        ]);
    }
}
?>