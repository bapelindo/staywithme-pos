<?php
// app/Views/admin/reports/product_sales.php (CORRECTED & REDESIGNED)

use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper; // <-- FIX: Added this line

// Data from Controller (remains the same)
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
$chartData = $chartData ?? ['labels' => [], 'datasets' => []];
?>

<div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8">

    <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                <?= SanitizeHelper::html($pageTitle) ?>
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Periode: <?= SanitizeHelper::html(DateHelper::formatIndonesian($startDate, 'dateonly')) ?> - <?= SanitizeHelper::html(DateHelper::formatIndonesian($endDate, 'dateonly')) ?>
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="#" class="btn-export">
                <i class="fas fa-file-export mr-2"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <div class="mb-10">
        <form action="<?= UrlHelper::baseUrl('admin/reports/product-sales') ?>" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-4 items-end">
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
                        <option value="<?= SanitizeHelper::html($category['id']) ?>" <?= (int)$selectedCategory === $category['id'] ? 'selected' : '' ?>>
                            <?= SanitizeHelper::html($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex space-x-2">
                <div class="flex-grow">
                    <label for="search" class="filter-label">Cari Produk</label>
                    <input type="text" name="search" id="search" placeholder="Nama produk..." value="<?= SanitizeHelper::html($searchTerm) ?>" class="filter-input" />
                </div>
                <button type="submit" class="btn-filter self-end" aria-label="Terapkan Filter">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="metric-card-clean">
            <h3 class="metric-label-clean">Total Penjualan</h3>
            <p class="metric-value-clean"><?= NumberHelper::format_rupiah($metrics['total_revenue']) ?></p>
        </div>
        <div class="metric-card-clean">
            <h3 class="metric-label-clean">Produk Terjual</h3>
            <p class="metric-value-clean"><?= number_format($metrics['total_quantity']) ?></p>
        </div>
        <div class="metric-card-clean">
            <h3 class="metric-label-clean">Total Laba Kotor</h3>
            <p class="metric-value-clean text-green-600"><?= NumberHelper::format_rupiah($metrics['total_gross_profit']) ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Visualisasi & Detail Data</h3>
        </div>

        <div class="p-6">
            <div class="relative h-96">
                <canvas id="productSalesChart"></canvas>
            </div>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full clean-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-right">Penjualan</th>
                        <th class="text-right">Laba Kotor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($reportData)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-12 text-gray-500">
                                <i class="fas fa-chart-pie text-4xl mb-3 text-gray-300"></i>
                                <p class="font-medium">Tidak ada data untuk ditampilkan</p>
                                <p class="text-sm">Silakan sesuaikan filter Anda.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $item): ?>
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-800"><?= SanitizeHelper::html($item['product_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= SanitizeHelper::html($item['category_name']) ?></div>
                                </td>
                                <td class="text-center"><?= number_format($item['total_quantity_sold']) ?></td>
                                <td class="text-right"><?= NumberHelper::format_rupiah($item['total_sales']) ?></td>
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

    const chartData = <?= json_encode($chartData); ?>;
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
                            callback: value => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value),
                            color: '#6B7280'
                        },
                        grid: {
                            color: '#E5E7EB',
                            borderDash: [3, 3]
                        },
                        title: { display: true, text: 'Total Penjualan (Rp)' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { color: '#6B7280' },
                        title: { display: true, text: 'Jumlah Terjual' }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20, color: '#4B5563' }
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        titleColor: '#F9FAFB',
                        bodyColor: '#F3F4F6',
                        borderColor: '#374151',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: { 
                            label: (context) => {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.dataset.yAxisID === 'y1') {
                                    label += context.parsed.y;
                                } else {
                                    label += formatRupiah(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                interaction: { intersect: false, mode: 'index' }
            }
        });
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
    @apply w-12 h-10 bg-indigo-600 text-white rounded-lg shadow-sm hover:bg-indigo-700 transition-all flex items-center justify-center;
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
    @apply w-full;
}
.clean-table th {
    @apply px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50;
}
.clean-table td {
    @apply px-6 py-4 text-sm text-gray-700;
}
</style>