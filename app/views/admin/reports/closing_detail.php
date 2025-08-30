<?php
// File: app/Views/admin/reports/closing_detail.php

use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper;

$drawerInfo = $reportData['drawer_info'] ?? [];
$variance = $reportData['variance'] ?? 0;
?>

<div class="container mx-auto max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800"><?= SanitizeHelper::html($pageTitle) ?></h1>
            <p class="text-sm text-gray-500">ID Sesi: #<?= $drawerInfo['id'] ?> | Kasir: <?= SanitizeHelper::html($drawerInfo['username']) ?></p>
        </div>
        <a href="<?= UrlHelper::baseUrl('admin/reports/closing') ?>" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Laporan
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="p-4 bg-white rounded-lg shadow border">
            <h3 class="text-sm font-medium text-gray-500">Kas Seharusnya</h3>
            <p class="text-xl font-bold text-gray-900 mt-1"><?= NumberHelper::format_rupiah($reportData['expected_cash'] ?? 0) ?></p>
        </div>
        <div class="p-4 bg-white rounded-lg shadow border">
            <h3 class="text-sm font-medium text-gray-500">Kas Aktual (Dihitung)</h3>
            <p class="text-xl font-bold text-gray-900 mt-1"><?= NumberHelper::format_rupiah($drawerInfo['closing_amount'] ?? 0) ?></p>
        </div>
        <div class="p-4 rounded-lg shadow border <?= $variance == 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' ?>">
            <h3 class="text-sm font-medium <?= $variance == 0 ? 'text-green-600' : 'text-red-600' ?>">Selisih</h3>
            <p class="text-xl font-bold mt-1 <?= $variance == 0 ? 'text-green-700' : 'text-red-700' ?>"><?= NumberHelper::format_rupiah($variance) ?></p>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Rincian Perhitungan Kas</h2>
        <table class="w-full text-sm">
            <tbody>
                <tr class="border-b">
                    <td class="py-2 text-gray-600">Modal Awal</td>
                    <td class="py-2 text-right font-medium"><?= NumberHelper::format_rupiah($drawerInfo['opening_amount'] ?? 0) ?></td>
                </tr>
                <tr class="border-b">
                    <td class="py-2 text-gray-600">Total Penjualan Tunai</td>
                    <td class="py-2 text-right font-medium text-green-600">(+) <?= NumberHelper::format_rupiah($reportData['total_cash_sales'] ?? 0) ?></td>
                </tr>
                <tr class="border-b">
                    <td class="py-2 text-gray-600">Total Kas Masuk (Lainnya)</td>
                    <td class="py-2 text-right font-medium text-green-600">(+) <?= NumberHelper::format_rupiah($reportData['total_cash_in_other'] ?? 0) ?></td>
                </tr>
                <tr class="border-b">
                    <td class="py-2 text-gray-600">Total Kas Keluar</td>
                    <td class="py-2 text-right font-medium text-red-600">(-) <?= NumberHelper::format_rupiah($reportData['total_cash_out'] ?? 0) ?></td>
                </tr>
                <tr class="font-bold bg-gray-50">
                    <td class="py-3">Total Kas Seharusnya</td>
                    <td class="py-3 text-right text-lg"><?= NumberHelper::format_rupiah($reportData['expected_cash'] ?? 0) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow-md border border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 p-6 border-b">Log Transaksi Selama Sesi</h2>
        <div class="w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    <?php foreach($reportData['transactions'] as $trans): ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap"><?= DateHelper::formatIndonesian($trans['transaction_time'], 'H:i:s') ?></td>
                            <td class="px-4 py-3 text-gray-800"><?= SanitizeHelper::html($trans['category']) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= SanitizeHelper::html($trans['notes']) ?></td>
                            <td class="px-4 py-3 text-right font-medium <?= $trans['type'] === 'in' ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $trans['type'] === 'in' ? '(+) ' : '(-) ' ?><?= NumberHelper::format_rupiah($trans['amount']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>