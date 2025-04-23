<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;

// Data dari ReportController
$startDate = $startDate ?? date('Y-m-01'); // Default awal bulan ini
$endDate = $endDate ?? date('Y-m-d');   // Default hari ini
$summary = $summary ?? ['total_orders' => 0, 'total_revenue' => 0, 'average_order_value' => 0];
$popularItems = $popularItems ?? [];
// $pageTitle diatur layout

// !! PENTING: Data untuk grafik harus disiapkan oleh ReportController !!
// Controller perlu query data (misal penjualan harian) dan menyiapkannya
// dalam format yang bisa dibaca Chart.js, lalu dikirim ke view ini.
// Contoh: $salesDataForChart = ['labels' => ['Tgl1', 'Tgl2', ...], 'revenue' => [100, 120, ...], 'orders' => [5, 7, ...]];
$salesDataForChart = $salesDataForChart ?? ['labels'=>[], 'revenue'=>[], 'orders'=>[]]; // Data untuk grafik penjualan
$popularItemsForChart = $popularItemsForChart ?? ['labels' => array_column($popularItems, 'menu_item_name'), 'quantities' => array_column($popularItems, 'total_quantity')]; // Data utk grafik item populer

$todayDate = date('Y-m-d'); // Untuk batas max date input
?>

<div class="mb-6">
    <h2 class="text-2xl font-semibold text-slate-800">Laporan Penjualan</h2>
    <p class="text-slate-500">Analisis performa penjualan kafe Anda.</p>
</div>

<div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-slate-200">
    <form action="<?= UrlHelper::baseUrl('/admin/reports') ?>" method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="start_date" class="block text-xs font-medium text-slate-600 mb-1">Tanggal Mulai:</label>
            <input type="date" id="start_date" name="start_date" value="<?= SanitizeHelper::html($startDate) ?>"
                   max="<?= SanitizeHelper::html($todayDate) ?>"
                   class="border-slate-300 rounded-md shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div>
            <label for="end_date" class="block text-xs font-medium text-slate-600 mb-1">Tanggal Akhir:</label>
            <input type="date" id="end_date" name="end_date" value="<?= SanitizeHelper::html($endDate) ?>"
                   max="<?= SanitizeHelper::html($todayDate) ?>"
                   class="border-slate-300 rounded-md shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 px-5 rounded-md transition shadow-sm">
                Tampilkan Laporan
            </button>
        </div>
    </form>
    <p class="text-xs text-slate-500 mt-2">Menampilkan laporan untuk periode: <?= SanitizeHelper::html(DateHelper::formatIndonesian($startDate, 'dateonly')) ?> s/d <?= SanitizeHelper::html(DateHelper::formatIndonesian($endDate, 'dateonly')) ?></p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
    <div class="p-5 bg-white rounded-lg shadow border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500 uppercase mb-1">Total Pendapatan</h3>
        <p class="text-3xl font-bold text-green-600"><?= NumberHelper::formatCurrencyIDR($summary['total_revenue']) ?></p>
    </div>
    <div class="p-5 bg-white rounded-lg shadow border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500 uppercase mb-1">Total Pesanan Selesai</h3>
        <p class="text-3xl font-bold text-blue-600"><?= NumberHelper::formatNumber($summary['total_orders']) ?></p>
         <span class="text-xs text-slate-400">(Status: Paid/Served)</span>
    </div>
    <div class="p-5 bg-white rounded-lg shadow border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500 uppercase mb-1">Rata-Rata per Pesanan</h3>
        <p class="text-3xl font-bold text-indigo-600"><?= NumberHelper::formatCurrencyIDR($summary['average_order_value']) ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-5 rounded-lg shadow border border-slate-200">
        <h3 class="text-lg font-semibold text-slate-700 mb-4">Tren Pendapatan & Pesanan</h3>
        <div class="h-72"> <?php if (!empty($salesDataForChart['labels'])): ?>
                <canvas id="salesChart"></canvas>
            <?php else: ?>
                <p class="text-center text-slate-500 pt-10">Data tidak cukup untuk menampilkan grafik tren.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white p-5 rounded-lg shadow border border-slate-200">
        <h3 class="text-lg font-semibold text-slate-700 mb-4">Top 5 Item Terpopuler</h3>
         <div class="h-72"> <?php if (!empty($popularItemsForChart['labels'])): ?>
                 <canvas id="popularItemsChart"></canvas>
             <?php else: ?>
                 <p class="text-center text-slate-500 pt-10">Belum ada data item populer untuk periode ini.</p>
             <?php endif; ?>
        </div>
    </div>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden border border-slate-200">
    <h3 class="text-lg font-semibold text-slate-700 p-5 border-b border-slate-200">Detail Item Terlaris (<?= SanitizeHelper::html(DateHelper::formatIndonesian($startDate, 'dateonly')) ?> - <?= SanitizeHelper::html(DateHelper::formatIndonesian($endDate, 'dateonly')) ?>)</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Item</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Jumlah Terjual</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                <?php if (empty($popularItems)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-6 text-center text-slate-500 text-sm">Tidak ada data penjualan item untuk periode ini.</td>
                    </tr>
                <?php else: ?>
                    <?php $rank = 1; ?>
                    <?php foreach ($popularItems as $item): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center"><?= $rank++ ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                <?= SanitizeHelper::html($item['menu_item_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 text-center font-semibold">
                                <?= SanitizeHelper::html($item['total_quantity']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Pastikan variabel ini ada sebelum JS report dimuat
    window.salesReportData = <?= json_encode($salesDataForChart) ?>;
    window.popularItemsReportData = <?= json_encode($popularItemsForChart) ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= UrlHelper::baseUrl('js/admin-reports.js') ?>" defer></script>