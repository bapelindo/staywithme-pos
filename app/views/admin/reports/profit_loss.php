<?php
// File: app/Views/admin/reports/profit_loss.php

use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper;

$pageTitle = $pageTitle ?? 'Laporan Laba Rugi';
$startDate = $startDate ?? date('Y-m-01');
$endDate = $endDate ?? date('Y-m-d');
$reportData = $reportData ?? [];

$revenue = $reportData['revenue'] ?? 0;
$cogs = $reportData['cogs'] ?? 0;
$gross_profit = $reportData['gross_profit'] ?? 0;
$expenses = $reportData['expenses'] ?? [];
$total_expenses = $reportData['total_expenses'] ?? 0;
$net_profit = $reportData['net_profit'] ?? 0;
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
        <form action="<?= UrlHelper::baseUrl('admin/reports/profit-loss') ?>" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" value="<?= SanitizeHelper::html($startDate) ?>" class="form-input block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date" value="<?= SanitizeHelper::html($endDate) ?>" class="form-input block w-full rounded-md border-gray-300 shadow-sm" />
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="fas fa-filter mr-2"></i> Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 space-y-8">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">Pendapatan</h3>
            <table class="w-full">
                <tbody class="text-sm">
                    <tr>
                        <td class="py-2 pl-2 text-gray-600">Total Pendapatan Penjualan</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($revenue) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">Harga Pokok Penjualan (HPP)</h3>
            <table class="w-full">
                <tbody class="text-sm">
                    <tr>
                        <td class="py-2 pl-2 text-gray-600">Total HPP</td>
                        <td class="py-2 pr-2 text-right font-medium text-red-600">(-) <?= NumberHelper::format_rupiah($cogs) ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-50">
                        <td class="py-3 pl-2 text-gray-800">LABA KOTOR</td>
                        <td class="py-3 pr-2 text-right text-indigo-600 text-base"><?= NumberHelper::format_rupiah($gross_profit) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">Biaya Operasional</h3>
            <table class="w-full">
                <tbody class="divide-y divide-gray-200 text-sm">
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="2" class="py-4 text-center text-gray-500">Tidak ada biaya operasional tercatat pada periode ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $expense): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 pl-2 text-gray-600"><?= SanitizeHelper::html($expense['category']) ?></td>
                            <td class="py-2 pr-2 text-right font-medium text-red-600">(-) <?= NumberHelper::format_rupiah($expense['total_amount']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-50">
                        <td class="py-3 pl-2 text-gray-800">TOTAL BIAYA OPERASIONAL</td>
                        <td class="py-3 pr-2 text-right text-red-600 text-base">(-) <?= NumberHelper::format_rupiah($total_expenses) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="pt-6 border-t-2 border-gray-300">
             <table class="w-full">
                <tfoot>
                    <tr class="font-extrabold text-lg <?= $net_profit >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' ?>">
                        <td class="py-4 pl-2">LABA BERSIH</td>
                        <td class="py-4 pr-2 text-right"><?= NumberHelper::format_rupiah($net_profit) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>