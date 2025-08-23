<?php
// File: app/Views/admin/reports/cash_summary.php

use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper;

$pageTitle = $pageTitle ?? 'Laporan Kas Kasir';
$startDate = $startDate ?? date('Y-m-01');
$endDate = $endDate ?? date('Y-m-d');
$searchTerm = $searchTerm ?? '';
$cashTransactions = $cashTransactions ?? [];
$metrics = $metrics ?? [
    'total_in' => 0,
    'total_out' => 0,
    'net_cash' => 0,
];

?>

<div class="container mx-auto p-4 lg:p-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">
            <?= SanitizeHelper::html($pageTitle) ?>
        </h1>
        <a href="<?= UrlHelper::baseUrl('admin/reports') ?>" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Laporan Utama
        </a>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 mb-8">
        <form action="<?= UrlHelper::baseUrl('admin/reports/cash-summary') ?>" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" value="<?= SanitizeHelper::html($startDate) ?>" class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date" value="<?= SanitizeHelper::html($endDate) ?>" class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div class="lg:col-span-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" id="search" placeholder="Kategori, catatan, atau username..." value="<?= SanitizeHelper::html($searchTerm) ?>" class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div class="lg:col-span-1 flex items-end">
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-filter mr-2"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Total Kas Masuk</h3>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= NumberHelper::format_rupiah($metrics['total_in']) ?></p>
            </div>
            <div class="p-3 bg-green-100 text-green-600 rounded-full">
                <i class="fas fa-arrow-down fa-lg"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Total Kas Keluar</h3>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= NumberHelper::format_rupiah($metrics['total_out']) ?></p>
            </div>
            <div class="p-3 bg-red-100 text-red-600 rounded-full">
                <i class="fas fa-arrow-up fa-lg"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Total Kas Bersih</h3>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= NumberHelper::format_rupiah($metrics['net_cash']) ?></p>
            </div>
            <div class="p-3 bg-indigo-100 text-indigo-600 rounded-full">
                <i class="fas fa-wallet fa-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md border border-gray-200">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xl font-semibold text-gray-800">Detail Transaksi Kas</h3>
        </div>
        <div class="w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Transaksi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Kas Masuk (Rp)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Kas Keluar (Rp)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($cashTransactions)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-500">
                                <p class="font-medium">Tidak ada data transaksi kas untuk ditampilkan.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cashTransactions as $transaction): ?>
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"><?= DateHelper::formatIndonesian($transaction['transaction_time']) ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700"><?= SanitizeHelper::html($transaction['username'] ?? '-') ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700"><?= SanitizeHelper::html($transaction['category'] ?? '-') ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= SanitizeHelper::html($transaction['notes'] ?? '-') ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-green-600"><?= $transaction['amount_in'] > 0 ? NumberHelper::format_rupiah($transaction['amount_in']) : '-' ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-red-600"><?= $transaction['amount_out'] > 0 ? NumberHelper::format_rupiah($transaction['amount_out']) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>