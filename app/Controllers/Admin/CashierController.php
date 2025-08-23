<?php
// File: app/Controllers/Admin/CashierController.php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Cashier;
use App\Models\CashTransaction;
use App\Helpers\AuthHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper;
use App\Helpers\SanitizeHelper;

class CashierController extends Controller
{
    private $cashierModel;
    private $cashTransactionModel;

    public function __construct()
    {
        AuthHelper::requireRole(['admin', 'staff']);
        $this->cashierModel = $this->model('Cashier');
        $this->cashTransactionModel = $this->model('CashTransaction');
    }

    public function index()
    {
        $userId = AuthHelper::getUserId();
        $openDrawer = $this->cashierModel->getOpenDrawerByUserId($userId);
        $todaysDrawers = $this->cashierModel->getTodaysDrawers();

        $this->view('admin/cashier/index', [
            'pageTitle' => 'Manajemen Kasir',
            'openDrawer' => $openDrawer,
            'todaysDrawers' => $todaysDrawers
        ], 'admin_layout');
    }

    public function open()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            UrlHelper::redirect('/admin/cashier');
            return;
        }

        $userId = AuthHelper::getUserId();
        if ($this->cashierModel->getOpenDrawerByUserId($userId)) {
            SessionHelper::setFlash('error', 'Anda sudah memiliki sesi kasir yang aktif.');
            UrlHelper::redirect('/admin/cashier');
            return;
        }

        $openingAmount = SanitizeHelper::float($_POST['opening_amount'] ?? 0);
        if ($openingAmount < 0) {
            SessionHelper::setFlash('error', 'Modal awal tidak boleh negatif.');
            UrlHelper::redirect('/admin/cashier');
            return;
        }

        $drawerId = $this->cashierModel->create(['user_id' => $userId, 'opening_amount' => $openingAmount]);

        if ($drawerId) {
            // Catat modal awal sebagai transaksi 'cash in'
            $this->cashTransactionModel->create([
                'user_id' => $userId,
                'drawer_id' => $drawerId,
                'type' => 'in',
                'amount' => $openingAmount,
                'category' => 'Modal Awal',
                'notes' => 'Membuka sesi kasir baru'
            ]);
            SessionHelper::setFlash('success', 'Sesi kasir berhasil dibuka.');
        } else {
            SessionHelper::setFlash('error', 'Gagal membuka sesi kasir.');
        }
        UrlHelper::redirect('/admin/cashier');
    }

    public function close()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            UrlHelper::redirect('/admin/cashier');
            return;
        }

        $userId = AuthHelper::getUserId();
        $openDrawer = $this->cashierModel->getOpenDrawerByUserId($userId);

        if (!$openDrawer) {
            SessionHelper::setFlash('error', 'Tidak ada sesi kasir aktif untuk ditutup.');
            UrlHelper::redirect('/admin/cashier');
            return;
        }

        $closingAmount = SanitizeHelper::float($_POST['closing_amount'] ?? 0);
        $notes = SanitizeHelper::string($_POST['notes'] ?? '');
         if ($closingAmount < 0) {
            SessionHelper::setFlash('error', 'Jumlah penutupan tidak boleh negatif.');
            UrlHelper::redirect('/admin/cashier');
            return;
        }
        
        if ($this->cashierModel->close($openDrawer['id'], $closingAmount, $notes)) {
             // Catat penutupan sebagai transaksi 'cash out'
            $this->cashTransactionModel->create([
                'user_id' => $userId,
                'drawer_id' => $openDrawer['id'],
                'type' => 'out',
                'amount' => $closingAmount,
                'category' => 'Tutup Kasir',
                'notes' => 'Menutup sesi kasir. ' . $notes
            ]);
            SessionHelper::setFlash('success', 'Sesi kasir berhasil ditutup.');
        } else {
            SessionHelper::setFlash('error', 'Gagal menutup sesi kasir.');
        }
        UrlHelper::redirect('/admin/cashier');
    }
    
    public function cashIn() {
        $this->handleCashTransaction('in');
    }

    public function cashOut() {
        $this->handleCashTransaction('out');
    }
    
    private function handleCashTransaction(string $type)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            UrlHelper::redirect('/admin/cashier');
            return;
        }

        $userId = AuthHelper::getUserId();
        $openDrawer = $this->cashierModel->getOpenDrawerByUserId($userId);

        if (!$openDrawer) {
            SessionHelper::setFlash('error', 'Anda harus membuka sesi kasir terlebih dahulu.');
            UrlHelper::redirect('/admin/cashier');
            return;
        }
        
        $amount = SanitizeHelper::float($_POST['amount'] ?? 0);
        $category = SanitizeHelper::string($_POST['category'] ?? 'Lainnya');
        $notes = SanitizeHelper::string($_POST['notes'] ?? '');

        if ($amount <= 0) {
            SessionHelper::setFlash('error', 'Jumlah harus lebih besar dari nol.');
            UrlHelper::redirect('/admin/cashier');
            return;
        }

        $transactionData = [
            'user_id' => $userId,
            'drawer_id' => $openDrawer['id'],
            'type' => $type,
            'amount' => $amount,
            'category' => $category,
            'notes' => $notes,
        ];

        if ($this->cashTransactionModel->create($transactionData)) {
            $typeName = $type === 'in' ? 'masuk' : 'keluar';
            SessionHelper::setFlash('success', "Transaksi kas {$typeName} berhasil dicatat.");
        } else {
            SessionHelper::setFlash('error', 'Gagal mencatat transaksi kas.');
        }

        UrlHelper::redirect('/admin/cashier');
    }
}