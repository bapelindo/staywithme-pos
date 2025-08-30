<?php
// File: app/Views/admin/reports/profit_loss.php (REDESIGNED)

use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper;

// Data dari Controller
$pageTitle = $pageTitle ?? 'Laporan Laba Rugi';
$startDate = $startDate ?? date('Y-m-01');
$endDate = $endDate ?? date('Y-m-d');
$reportData = $reportData ?? [];

// Ekstrak data untuk kemudahan akses
$revenue = $reportData['revenue'] ?? 0;
$cogs = $reportData['cogs'] ?? 0;
$gross_profit = $reportData['gross_profit'] ?? 0;
$expenses = $reportData['expenses'] ?? [];
$total_expenses = $reportData['total_expenses'] ?? 0;
$net_profit = $reportData['net_profit'] ?? 0;

// Hitung metrik penting
$gross_profit_margin = $revenue > 0 ? ($gross_profit / $revenue) * 100 : 0;
$net_profit_margin = $revenue > 0 ? ($net_profit / $revenue) * 100 : 0;

// Siapkan data untuk Chart.js
$expense_labels = array_column($expenses, 'category');
$expense_values = array_column($expenses, 'total_amount');

$chart_data = [
    'labels' => $expense_labels,
    'values' => $expense_values
];

?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">

    <div class="mb-8">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <?= SanitizeHelper::html($pageTitle) ?>
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Periode: <span class="font-semibold"><?= DateHelper::formatIndonesian($startDate, 'dateonly') ?></span> - <span class="font-semibold"><?= DateHelper::formatIndonesian($endDate, 'dateonly') ?></span>
                </p>
            </div>
            <a href="<?= UrlHelper::baseUrl('admin/reports/summary') ?>" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Ringkasan
            </a>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
             <form action="<?= UrlHelper::baseUrl('admin/reports/profit-loss') ?>" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="start_date" class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="<?= SanitizeHelper::html($startDate) ?>" class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-medium text-gray-600 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="end_date" value="<?= SanitizeHelper::html($endDate) ?>" class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-filter mr-2"></i> Terapkan
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Rincian Laporan</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center text-lg">
                    <span class="font-medium text-gray-800">Pendapatan Penjualan</span>
                    <span class="font-semibold text-gray-900"><?= NumberHelper::format_rupiah($revenue) ?></span>
                </div>

                <div class="flex justify-between items-center text-md pl-4">
                    <span class="text-gray-600">Harga Pokok Penjualan (HPP)</span>
                    <span class="font-medium text-red-600">(-) <?= NumberHelper::format_rupiah($cogs) ?></span>
                </div>

                <div class="flex justify-between items-center bg-gray-50 p-3 rounded-md mt-2">
                    <span class="font-bold text-lg text-gray-800">LABA KOTOR</span>
                    <span class="font-extrabold text-xl text-indigo-600"><?= NumberHelper::format_rupiah($gross_profit) ?></span>
                </div>

                <div class="pt-4 mt-4 border-t">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Biaya Operasional</h3>
                    <div class="space-y-2">
                        <?php if (empty($expenses)): ?>
                            <p class="text-sm text-gray-500 text-center py-4">Tidak ada data biaya operasional.</p>
                        <?php else: ?>
                            <?php foreach ($expenses as $expense): ?>
                            <div class="flex justify-between items-center text-sm hover:bg-gray-50 p-2 rounded">
                                <span class="text-gray-600"><?= SanitizeHelper::html($expense['category']) ?></span>
                                <span class="font-medium text-red-600">(-) <?= NumberHelper::format_rupiah($expense['total_amount']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                     <div class="flex justify-between items-center bg-gray-50 p-3 rounded-md mt-4">
                        <span class="font-bold text-gray-800">TOTAL BIAYA OPERASIONAL</span>
                        <span class="font-bold text-lg text-red-600">(-) <?= NumberHelper::format_rupiah($total_expenses) ?></span>
                    </div>
                </div>

                <div class="flex justify-between items-center p-4 rounded-lg mt-6 <?= $net_profit >= 0 ? 'bg-green-100 border border-green-200' : 'bg-red-100 border border-red-200' ?>">
                    <span class="font-extrabold text-xl <?= $net_profit >= 0 ? 'text-green-800' : 'text-red-800' ?>">LABA BERSIH</span>
                    <span class="font-extrabold text-2xl <?= $net_profit >= 0 ? 'text-green-700' : 'text-red-700' ?>"><?= NumberHelper::format_rupiah($net_profit) ?></span>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Metrik Utama</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Margin Laba Kotor</span>
                        <span class="font-semibold text-indigo-600 text-lg"><?= number_format($gross_profit_margin, 2) ?>%</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Margin Laba Bersih</span>
                        <span class="font-semibold <?= $net_profit_margin >= 0 ? 'text-green-600' : 'text-red-600' ?> text-lg">
                            <?= number_format($net_profit_margin, 2) ?>%
                        </span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t">
                        <span class="text-sm text-gray-600">Total Biaya</span>
                        <span class="font-semibold text-red-600 text-lg"><?= NumberHelper::format_rupiah($total_expenses) ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Distribusi Biaya Operasional</h3>
                 <div class="relative h-64">
                    <?php if (!empty($expense_values)): ?>
                        <canvas id="expenseChart"></canvas>
                    <?php else: ?>
                        <div class="flex items-center justify-center h-full text-gray-500">
                            <p>Tidak ada data biaya.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const expenseData = <?= json_encode($chart_data); ?>;
    const ctx = document.getElementById('expenseChart')?.getContext('2d');

    if (ctx && expenseData.values.length > 0) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: expenseData.labels,
                datasets: [{
                    label: 'Total Biaya',
                    data: expenseData.values,
                    backgroundColor: [
                        '#6366F1', '#EC4899', '#F59E0B', '#10B981', '#3B82F6',
                        '#8B5CF6', '#D946EF', '#F43F5E', '#EAB308', '#22C55E'
                    ],
                    borderColor: '#FFFFFF',
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            boxWidth: 12,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.raw);
                                    label += ` (${percentage}%)`;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>