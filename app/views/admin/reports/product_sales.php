<?php
// app/Views/admin/reports/product_sales.php (REBUILT)

use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper;

// Data dari Controller
$pageTitle = $pageTitle ?? 'Laporan Penjualan Produk';
$startDate = $startDate ?? date('Y-m-01');
$endDate = $endDate ?? date('Y-m-d');
$categories = $categories ?? [];
$selectedCategory = $selectedCategory ?? 'all';
$searchTerm = $searchTerm ?? '';
$reportData = $reportData ?? [];
$metrics = $metrics ?? [
    'total_revenue' => 0,
    'total_quantity' => 0,
    'total_gross_profit' => 0,
];
// Filter untuk grafik
$groupBy = $groupBy ?? 'day';
$chartMetric = $chartMetric ?? 'penjualan';
$chartData = $chartData ?? ['labels' => [], 'datasets' => []];

?>

<div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8">

    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                <?= SanitizeHelper::html($pageTitle) ?>
            </h1>
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200 mb-8">
        <form action="<?= UrlHelper::baseUrl('admin/reports/product-sales') ?>" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label for="start_date" class="filter-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="<?= SanitizeHelper::html($startDate) ?>" class="filter-input" />
                </div>
                <div>
                    <label for="end_date" class="filter-label">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="end_date" value="<?= SanitizeHelper::html($endDate) ?>" class="filter-input" />
                </div>
                <div>
                    <label for="category" class="filter-label">Kategori</label>
                    <select name="category" id="category" class="filter-input">
                        <option value="all">Semua Kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= SanitizeHelper::html($category['id']) ?>" <?= ($selectedCategory == $category['id']) ? 'selected' : '' ?>>
                                <?= SanitizeHelper::html($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-grow">
                    <label for="search" class="filter-label">Cari Produk (Nama)</label>
                    <input type="text" name="search" id="search" placeholder="Contoh: Kopi Susu..." value="<?= SanitizeHelper::html($searchTerm) ?>" class="filter-input" />
                </div>
                <button type="submit" class="btn-filter w-full" aria-label="Terapkan Filter">
                    <i class="fas fa-filter mr-2"></i> Tampilkan
                </button>
            </div>
            <input type="hidden" name="group_by" value="<?= SanitizeHelper::html($groupBy) ?>">
            <input type="hidden" name="chart_metric" value="<?= SanitizeHelper::html($chartMetric) ?>">
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="metric-card-clean">
            <h3 class="metric-label-clean">Total Penjualan per Produk</h3>
            <p class="metric-value-clean"><?= NumberHelper::format_rupiah($metrics['total_revenue']) ?></p>
        </div>
        <div class="metric-card-clean">
            <h3 class="metric-label-clean">Total Produk Terjual</h3>
            <p class="metric-value-clean"><?= number_format($metrics['total_quantity']) ?></p>
        </div>
        <div class="metric-card-clean">
            <h3 class="metric-label-clean">Total Laba Kotor per Produk</h3>
            <p class="metric-value-clean text-green-600"><?= NumberHelper::format_rupiah($metrics['total_gross_profit']) ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-8">
        <div class="p-6 border-b border-gray-200 flex flex-wrap justify-between items-center gap-4">
            <h3 class="text-xl font-semibold text-gray-800">Grafik Penjualan Produk</h3>
            <form action="<?= UrlHelper::baseUrl('admin/reports/product-sales') ?>" method="GET" id="chart-filter-form" class="flex items-center gap-2">
                 <input type="hidden" name="start_date" value="<?= SanitizeHelper::html($startDate) ?>">
                 <input type="hidden" name="end_date" value="<?= SanitizeHelper::html($endDate) ?>">
                 <input type="hidden" name="category" value="<?= SanitizeHelper::html($selectedCategory) ?>">
                 <input type="hidden" name="search" value="<?= SanitizeHelper::html($searchTerm) ?>">

                <select name="group_by" id="group_by" class="filter-input !w-auto !py-1.5">
                    <option value="day" <?= $groupBy === 'day' ? 'selected' : '' ?>>Harian</option>
                    <option value="month" <?= $groupBy === 'month' ? 'selected' : '' ?>>Bulanan</option>
                    <option value="year" <?= $groupBy === 'year' ? 'selected' : '' ?>>Tahunan</option>
                </select>
                <select name="chart_metric" id="chart_metric" class="filter-input !w-auto !py-1.5">
                    <option value="penjualan" <?= $chartMetric === 'penjualan' ? 'selected' : '' ?>>Penjualan (Rp)</option>
                    <option value="produk" <?= $chartMetric === 'produk' ? 'selected' : '' ?>>Jumlah Produk</option>
                </select>
            </form>
        </div>
        <div class="p-6">
            <div class="relative h-96">
                <canvas id="productSalesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xl font-semibold text-gray-800">Detail Penjualan per Produk</h3>
            <a href="#" class="btn-export">
                <i class="fas fa-file-export mr-2"></i>
                <span>Export CSV</span>
            </a>
        </div>
        <div class="w-full overflow-x-auto">
            <table class="w-full clean-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th rowspan="2">Produk</th>
                        <th rowspan="2">Kategori</th>
                        <th colspan="4" class="text-center border-b">Penjualan</th>
                        <th colspan="3" class="text-center border-b">Biaya & Laba</th>
                    </tr>
                    <tr>
                        <th class="text-center">Jumlah</th>
                        <th class="text-right">Penjualan (Rp)</th>
                        <th class="text-right">Refund (Rp)</th>
                        <th class="text-center">Jml Refund</th>
                        <th class="text-right">Hpp (Rp)</th>
                        <th class="text-right">Hpp Refund (Rp)</th>
                        <th class="text-right">Laba Kotor (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($reportData)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-12 text-gray-500">
                                <i class="fas fa-chart-pie text-4xl mb-3 text-gray-300"></i>
                                <p class="font-medium">Tidak ada data untuk ditampilkan.</p>
                                <p class="text-sm">Silakan sesuaikan filter Anda di atas.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $item): ?>
                            <tr>
                                <td><?= SanitizeHelper::html($item['product_name']) ?></td>
                                <td><?= SanitizeHelper::html($item['category_name']) ?></td>
                                <td class="text-center"><?= number_format($item['total_quantity_sold']) ?></td>
                                <td class="text-right"><?= NumberHelper::format_rupiah($item['total_sales']) ?></td>
                                <td class="text-right text-red-600"><?= NumberHelper::format_rupiah($item['total_refund_amount']) ?></td>
                                <td class="text-center text-red-600"><?= number_format($item['total_refund_quantity']) ?></td>
                                <td class="text-right"><?= NumberHelper::format_rupiah($item['total_cogs']) ?></td>
                                <td class="text-right"><?= NumberHelper::format_rupiah($item['total_cogs_refund']) ?></td>
                                <td class="text-right font-semibold text-green-600"><?= NumberHelper::format_rupiah($item['gross_profit']) ?></td>
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
    chartFilterForm.addEventListener('change', () => {
        chartFilterForm.submit();
    });

    // Chart.js Initialization
    const chartData = <?= json_encode($chartData); ?>;
    const chartMetric = '<?= SanitizeHelper::html($chartMetric) ?>';
    const ctx = document.getElementById('productSalesChart')?.getContext('2d');
    
    if (ctx && chartData.labels.length > 0) {
        new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
        // Display a message if no data for the chart
        const canvas = ctx.canvas;
        const parent = canvas.parentNode;
        parent.innerHTML = `<div class="flex items-center justify-center h-full text-gray-500 text-center"><p>Tidak ada data untuk ditampilkan di grafik.<br>Silakan sesuaikan filter Anda.</p></div>`;
    }
});
</script>

<style>
.filter-label {
    @apply block text-sm font-medium text-gray-600 mb-1;
}
.filter-input {
    @apply w-full bg-white border border-gray-300 rounded-lg shadow-sm text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all;
}
.btn-filter {
    @apply h-10 bg-indigo-600 text-white rounded-lg shadow-sm hover:bg-indigo-700 transition-all flex items-center justify-center;
}
.btn-export {
    @apply flex items-center bg-white border border-gray-300 text-gray-700 text-sm font-semibold py-2 px-4 rounded-lg shadow-sm hover:bg-gray-50 transition-all;
}
.metric-card-clean {
    @apply bg-white p-6 rounded-xl shadow-lg border border-gray-200 text-center;
}
.metric-label-clean {
    @apply text-sm font-semibold text-gray-500 mb-1;
}
.metric-value-clean {
    @apply text-3xl font-bold text-gray-800;
}
.clean-table {
    @apply w-full text-sm;
}
.clean-table th {
    @apply px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50;
}
.clean-table td {
    @apply px-4 py-4 text-gray-700;
}
</style>