<?php
// File: app/Views/admin/reports/category_sales.php

use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper;

// Data dari Controller
$pageTitle = $pageTitle ?? 'Laporan Penjualan per Kategori';
$startDate = $startDate ?? date('Y-m-01');
$endDate = $endDate ?? date('Y-m-d');
$searchTerm = $searchTerm ?? '';
$reportData = $reportData ?? [];
$metrics = $metrics ?? [
    'total_categories' => 0,
    'total_sales' => 0,
];
$totalGlobalSales = $totalGlobalSales ?? 0;
$totalGlobalProducts = $totalGlobalProducts ?? 0;

// Filter untuk grafik
$groupBy = $groupBy ?? 'day';
$chartMetric = $chartMetric ?? 'penjualan';
$chartData = $chartData ?? ['labels' => [], 'datasets' => []];

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
        <form action="<?= UrlHelper::baseUrl('admin/reports/category-sales') ?>" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" value="<?= SanitizeHelper::html($startDate) ?>" class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date" value="<?= SanitizeHelper::html($endDate) ?>" class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div class="lg:col-span-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Kategori</label>
                <input type="text" name="search" id="search" placeholder="Nama Kategori..." value="<?= SanitizeHelper::html($searchTerm) ?>" class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div class="lg:col-span-1 flex items-end">
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-filter mr-2"></i> Tampilkan
                </button>
            </div>
            <input type="hidden" name="group_by" value="<?= SanitizeHelper::html($groupBy) ?>">
            <input type="hidden" name="chart_metric" value="<?= SanitizeHelper::html($chartMetric) ?>">
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Total Kategori</h3>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($metrics['total_categories'] ?? 0) ?></p>
            </div>
            <div class="p-3 bg-indigo-100 text-indigo-600 rounded-full">
                <i class="fas fa-sitemap fa-lg"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Total Penjualan Kategori</h3>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= NumberHelper::format_rupiah($metrics['total_sales'] ?? 0) ?></p>
            </div>
            <div class="p-3 bg-green-100 text-green-600 rounded-full">
                <i class="fas fa-dollar-sign fa-lg"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-8">
        <div class="p-6 border-b border-gray-200 flex flex-wrap justify-between items-center gap-4">
            <h3 class="text-xl font-semibold text-gray-800">Grafik Penjualan Kategori</h3>
            <form action="<?= UrlHelper::baseUrl('admin/reports/category-sales') ?>" method="GET" id="chart-filter-form" class="flex items-center gap-2">
                 <input type="hidden" name="start_date" value="<?= SanitizeHelper::html($startDate) ?>">
                 <input type="hidden" name="end_date" value="<?= SanitizeHelper::html($endDate) ?>">
                 <input type="hidden" name="search" value="<?= SanitizeHelper::html($searchTerm) ?>">

                <select name="group_by" id="group_by" class="form-select rounded-md border-gray-300 shadow-sm text-sm py-1.5 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="day" <?= $groupBy === 'day' ? 'selected' : '' ?>>Harian</option>
                    <option value="week" <?= $groupBy === 'week' ? 'selected' : '' ?>>Mingguan</option>
                    <option value="month" <?= $groupBy === 'month' ? 'selected' : '' ?>>Bulanan</option>
                    <option value="year" <?= $groupBy === 'year' ? 'selected' : '' ?>>Tahunan</option>
                </select>
                <select name="chart_metric" id="chart_metric" class="form-select rounded-md border-gray-300 shadow-sm text-sm py-1.5 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="penjualan" <?= $chartMetric === 'penjualan' ? 'selected' : '' ?>>Penjualan (Rp)</option>
                    <option value="produk" <?= $chartMetric === 'produk' ? 'selected' : '' ?>>Jumlah Produk</option>
                </select>
            </form>
        </div>
        <div class="p-6">
            <div class="relative h-96">
                <canvas id="categorySalesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md border border-gray-200">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xl font-semibold text-gray-800">Detail Penjualan per Kategori</h3>
            <a href="<?= UrlHelper::baseUrl('admin/reports/product/by-category/export?start_date=' . SanitizeHelper::html($startDate) . '&end_date=' . SanitizeHelper::html($endDate) . '&search=' . SanitizeHelper::html($searchTerm)) ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-file-export mr-2"></i>
                <span>Export CSV</span>
            </a>
        </div>
        <div class="w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Produk</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Produk (%)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Penjualan (Rp)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Penjualan (%)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">HPP (Rp)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($reportData)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-500">
                                <i class="fas fa-box-open text-4xl mb-3 text-gray-300"></i>
                                <p class="font-medium">Tidak ada data penjualan kategori untuk ditampilkan.</p>
                                <p class="text-sm">Silakan sesuaikan filter Anda di atas.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $item): ?>
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= SanitizeHelper::html($item['category_name']) ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-700"><?= number_format($item['total_products_sold'] ?? 0) ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-700"><?= $totalGlobalProducts > 0 ? number_format((($item['total_products_sold'] ?? 0) / $totalGlobalProducts) * 100, 2) : 0 ?>%</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-700"><?= NumberHelper::format_rupiah($item['total_sales'] ?? 0) ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-700"><?= $totalGlobalSales > 0 ? number_format((($item['total_sales'] ?? 0) / $totalGlobalSales) * 100, 2) : 0 ?>%</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-semibold text-green-600"><?= NumberHelper::format_rupiah($item['total_cogs'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(value);

    // Chart Filters Auto-submit
    const chartFilterForm = document.getElementById('chart-filter-form');
    if(chartFilterForm) {
        chartFilterForm.addEventListener('change', () => {
            chartFilterForm.submit();
        });
    }

    // Chart.js Initialization
    const chartData = <?= json_encode($chartData); ?>;
    const chartMetric = '<?= SanitizeHelper::html($chartMetric) ?>';
    const ctx = document.getElementById('categorySalesChart')?.getContext('2d');
    
    if (ctx && chartData.labels.length > 0) {
        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6B7280' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => {
                                if (chartMetric === 'penjualan') {
                                    return new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                }
                                return value;
                            },
                            color: '#6B7280'
                        },
                        grid: {
                            color: '#E5E7EB',
                            borderDash: [3, 3]
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20, color: '#4B5563' }
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        callbacks: {
                            label: (context) => {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (chartMetric === 'penjualan') {
                                    label += formatRupiah(context.parsed.y);
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    } else if (ctx) {
        const canvas = ctx.canvas;
        const parent = canvas.parentNode;
        parent.innerHTML = `<div class="flex items-center justify-center h-full text-gray-500 text-center"><p>Tidak ada data untuk ditampilkan di grafik.<br>Silakan sesuaikan filter Anda.</p></div>`;
    }
});

</script>