<?php
// File: app/Views/admin/reports/sales_detail.php

use App\Helpers\NumberHelper;
use App\Helpers\DateHelper;
use App\Helpers\UrlHelper;
?>

<main class="h-full overflow-y-auto">
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            <?= htmlspecialchars($pageTitle ?? 'Detail Penjualan') ?>
        </h2>

        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <form action="<?= UrlHelper::baseUrl('admin/reports/sales-detail') ?>" method="GET">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    <div class="flex flex-col">
                        <label for="start_date" class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($startDate ?? '2025-08-01') ?>" class="block w-full text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-indigo-400 focus:outline-none focus:shadow-outline-indigo dark:text-gray-300 dark:focus:shadow-outline-gray form-input rounded-md" />
                    </div>
                    <div class="flex flex-col">
                        <label for="end_date" class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($endDate ?? '2025-08-31') ?>" class="block w-full text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-indigo-400 focus:outline-none focus:shadow-outline-indigo dark:text-gray-300 dark:focus:shadow-outline-gray form-input rounded-md" />
                    </div>
                    <div class="flex flex-col">
                        <label for="filter_by" class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Filter Berdasarkan</label>
                        <select name="filter_by" id="filter_by" class="block w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-indigo-400 focus:outline-none focus:shadow-outline-indigo dark:focus:shadow-outline-gray rounded-md">
                            <option value="order_time" <?= ($filterBy ?? '') === 'order_time' ? 'selected' : '' ?>>Waktu Order</option>
                            <option value="payment_time" <?= ($filterBy ?? '') === 'payment_time' ? 'selected' : '' ?>>Waktu Bayar</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="search_term" class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Cari</label>
                        <input type="text" name="search_term" id="search_term" placeholder="No. Transaksi, Meja, dll..." value="<?= htmlspecialchars($searchTerm ?? '') ?>" class="block w-full text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-indigo-400 focus:outline-none focus:shadow-outline-indigo dark:text-gray-300 dark:focus:shadow-outline-gray form-input rounded-md" />
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500"><i class="fas fa-cash-register"></i></div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Penjualan</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?= NumberHelper::format_rupiah($totalSales) ?></p>
                </div>
            </div>
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500"><i class="fas fa-receipt"></i></div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Transaksi</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?= htmlspecialchars($totalTransactions) ?></p>
                </div>
            </div>
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500"><i class="fas fa-wallet"></i></div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Penjualan Bersih</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?= NumberHelper::format_rupiah($netSales) ?></p>
                </div>
            </div>
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full dark:text-teal-100 dark:bg-teal-500"><i class="fas fa-money-bill-wave"></i></div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Pembayaran</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?= NumberHelper::format_rupiah($totalPayments) ?></p>
                </div>
            </div>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">No Transaksi</th>
                            <th class="px-4 py-3">Waktu Order</th>
                            <th class="px-4 py-3">Waktu Bayar</th>
                            <th class="px-4 py-3">Outlet</th>
                            <th class="px-4 py-3">Jenis Order</th>
                            <th class="px-4 py-3 text-right">Total Penjualan</th>
                            <th class="px-4 py-3">Metode Bayar</th>
                            <th class="px-4 py-3 text-right">Dibayar</th>
                            <th class="px-4 py-3 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        <?php if (empty($salesDetails)) : ?>
                            <tr>
                                <td colspan="9" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data untuk ditampilkan.
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($salesDetails as $sale) : ?>
                                <tr class="text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-900">
                                    <td class="px-4 py-3 text-sm font-semibold"><?= htmlspecialchars($sale->transaction_no) ?></td>
                                    <td class="px-4 py-3 text-sm"><?= DateHelper::formatIndonesian($sale->order_time, 'd M Y, H:i') ?></td>
                                    <td class="px-4 py-3 text-sm"><?= $sale->payment_time ? DateHelper::formatIndonesian($sale->payment_time, 'd M Y, H:i') : '-' ?></td>
                                    <td class="px-4 py-3 text-sm"><?= htmlspecialchars($sale->outlet) ?></td>
                                    <td class="px-4 py-3 text-sm"><?= htmlspecialchars($sale->order_type) ?></td>
                                    <td class="px-4 py-3 text-sm text-right"><?= NumberHelper::format_rupiah($sale->total_sales) ?></td>
                                    <td class="px-4 py-3 text-sm"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $sale->payment_method ?? '-'))) ?></td>
                                    <td class="px-4 py-3 text-sm text-right"><?= NumberHelper::format_rupiah($sale->amount_paid ?? 0) ?></td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <a href="<?= UrlHelper::baseUrl('admin/orders/show/' . $sale->order_id) ?>" class="px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>