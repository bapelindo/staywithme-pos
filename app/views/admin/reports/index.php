<?php
// File: app/Views/admin/reports/index.php (Lengkap - Dengan Laporan Kategori)

use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;
use App\Helpers\DateHelper;

// Data dari ReportController
$startDate = $startDate ?? date('Y-m-01');
$endDate = $endDate ?? date('Y-m-d');
$summary = $summary ?? ['total_orders' => 0, 'total_revenue' => 0, 'average_order_value' => 0];
$popularItems = $popularItems ?? [];
$salesDataForChart = $salesDataForChart ?? ['labels'=>[], 'revenue'=>[], 'orders'=>[]];
$popularItemsForChart = $popularItemsForChart ?? ['labels' => [], 'quantities' => []];
// Data baru untuk laporan kategori
$categoryRevenueForChart = $categoryRevenueForChart ?? ['labels'=>[], 'data'=>[], 'colors'=>[]];
$revenueByCategory = $revenueByCategory ?? []; // Untuk tabel detail

$todayDate = date('Y-m-d'); // Untuk batas max date input
// $pageTitle diatur layout admin
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
    <p class="text-xs text-slate-500 mt-2">Menampilkan laporan untuk periode:
    <?php
        $formattedStartDate = '[Invalid Date]';
        $formattedEndDate = '[Invalid Date]';
        if (class_exists('App\\Helpers\\DateHelper')) {
             $formattedStartDate = DateHelper::formatIndonesian($startDate, 'dateonly') ?? $formattedStartDate;
             $formattedEndDate = DateHelper::formatIndonesian($endDate, 'dateonly') ?? $formattedEndDate;
        }
     ?>
     <?= SanitizeHelper::html($formattedStartDate) ?> s/d <?= SanitizeHelper::html($formattedEndDate) ?></p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
    <div class="p-5 bg-white rounded-lg shadow border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500 uppercase mb-1">Total Pendapatan</h3>
        <p class="text-3xl font-bold text-green-600"><?= NumberHelper::formatCurrencyIDR($summary['total_revenue']) ?></p>
    </div>
    <div class="p-5 bg-white rounded-lg shadow border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500 uppercase mb-1">Total Pesanan Selesai</h3>
        <p class="text-3xl font-bold text-blue-600"><?= NumberHelper::formatNumber($summary['total_orders']) ?></p>
         <span class="text-xs text-slate-400">(Status: Served)</span> <?php // Sesuaikan jika perlu ?>
    </div>
    <div class="p-5 bg-white rounded-lg shadow border border-slate-200">
        <h3 class="text-sm font-medium text-slate-500 uppercase mb-1">Rata-Rata per Pesanan</h3>
        <p class="text-3xl font-bold text-indigo-600"><?= NumberHelper::formatCurrencyIDR($summary['average_order_value']) ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-5 rounded-lg shadow border border-slate-200">
        <h3 class="text-lg font-semibold text-slate-700 mb-4">Tren Pendapatan & Pesanan</h3>
        <div class="h-72"> <?php // Tinggi tetap ?>
            <?php if (!empty($salesDataForChart['labels'])): ?>
                <canvas id="salesChart"></canvas>
            <?php else: ?>
                <p class="text-center text-slate-500 pt-10">Data tidak cukup untuk menampilkan grafik tren.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white p-5 rounded-lg shadow border border-slate-200">
        <h3 class="text-lg font-semibold text-slate-700 mb-4">Top 5 Item Terpopuler</h3>
         <div class="h-72"> <?php // Tinggi tetap ?>
             <?php if (!empty($popularItemsForChart['labels'])): ?>
                 <canvas id="popularItemsChart"></canvas>
             <?php else: ?>
                 <p class="text-center text-slate-500 pt-10">Belum ada data item populer untuk periode ini.</p>
             <?php endif; ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-5 rounded-lg shadow border border-slate-200">
        <h3 class="text-lg font-semibold text-slate-700 mb-4">Pendapatan per Kategori</h3>
        <div class="h-72 relative"> <?php // Tinggi tetap, perlu relatif untuk tooltip/legend ?>
            <?php if (!empty($categoryRevenueForChart['labels'])): ?>
                <canvas id="categoryRevenueChart"></canvas>
            <?php else: ?>
                <p class="text-center text-slate-500 pt-10">Tidak ada data pendapatan per kategori untuk periode ini.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-slate-200 lg:max-h-[calc(18rem_+_2.5rem_+_2.5rem)] lg:overflow-y-auto"> <?php // Batasi tinggi & scroll jika perlu ?>
        <h3 class="text-lg font-semibold text-slate-700 p-5 border-b border-slate-200 sticky top-0 bg-white z-10">Detail Pendapatan per Kategori</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kategori</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    <?php if (empty($revenueByCategory)): ?>
                        <tr>
                            <td colspan="2" class="px-6 py-6 text-center text-slate-500 text-sm">Tidak ada data.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($revenueByCategory as $catData): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                    <?= SanitizeHelper::html($catData['category_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 text-right font-semibold">
                                    <?= NumberHelper::formatCurrencyIDR($catData['total_revenue']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="bg-white shadow-md rounded-lg overflow-hidden border border-slate-200">
    <h3 class="text-lg font-semibold text-slate-700 p-5 border-b border-slate-200">Detail Item Terlaris (Periode yang Sama)</h3>
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

<?php // Kirim semua data yang dibutuhkan oleh JS ?>
<script>
    window.salesReportData = <?= json_encode($salesDataForChart) ?>;
    window.popularItemsReportData = <?= json_encode($popularItemsForChart) ?>;
    window.categoryRevenueReportData = <?= json_encode($categoryRevenueForChart) ?>;
</script>

<?php // Load library Chart.js & script JS laporan ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
<script src="<?= UrlHelper::baseUrl('js/admin-reports.js') ?>" defer></script>