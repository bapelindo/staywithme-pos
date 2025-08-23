<?php 
// File: app/Views/admin/reports/financials.php (REBUILT & FINAL V6)

use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;
?>

<div class="container mx-auto">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-sm text-gray-500 mt-1">Periode: <?= date('j F Y', strtotime($startDate)) ?> - <?= date('j F Y', strtotime($endDate)) ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="" class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2 bg-white border rounded-lg p-2">
                    <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="text-sm border-0 focus:ring-0">
                    <span class="text-gray-400">-</span>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="text-sm border-0 focus:ring-0">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
            </form>
            <a href="<?= UrlHelper::baseUrl('admin/reports/summary') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Ringkasan
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Keuangan</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-indigo-50 rounded-lg">
                    <div class="text-sm text-gray-600">Total Pendapatan</div>
                    <div class="text-xl font-bold text-indigo-600 mt-1"><?= NumberHelper::format_rupiah($financials['total_revenue'] ?? 0) ?></div>
                </div>
                <div class="p-4 bg-red-50 rounded-lg">
                    <div class="text-sm text-gray-600">Total Biaya</div>
                    <div class="text-xl font-bold text-red-600 mt-1"><?= NumberHelper::format_rupiah(
                        ($financials['total_promo_cost'] ?? 0) + 
                        ($financials['mdr_fee'] ?? 0) + 
                        ($financials['cogs'] ?? 0) + 
                        ($financials['commission'] ?? 0) + 
                        ($financials['admin_fee'] ?? 0) +
                        ($financials['service_charge'] ?? 0) + // Ditambahkan
                        ($financials['tax'] ?? 0) // Ditambahkan
                    ) ?></div>
                </div>
                <div class="p-4 bg-green-50 rounded-lg col-span-2">
                    <div class="text-sm text-gray-600">Laba Kotor</div>
                    <div class="text-2xl font-bold text-green-600 mt-1"><?= NumberHelper::format_rupiah($financials['gross_profit'] ?? 0) ?></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Biaya</h3>
            <div class="relative h-[250px]">
                <canvas id="costDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-8">

        <div>
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">PENDAPATAN</h3>
            <table class="w-full">
                <tbody class="divide-y divide-gray-200 text-sm">
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Penjualan Kotor</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['gross_sales'] ?? 0) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Biaya Pelayanan</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['service_charge'] ?? 0) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Biaya Pelayanan MDR</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['mdr_service_fee'] ?? 0) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Biaya Administrasi</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['admin_fee'] ?? 0) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Pembulatan</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['rounding'] ?? 0) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Pajak</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['tax'] ?? 0) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Lainnya</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['other_revenue'] ?? 0) ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-50">
                        <td class="py-3 pl-2 text-gray-800">TOTAL PENDAPATAN</td>
                        <td class="py-3 pr-2 text-right text-indigo-600 text-base"><?= NumberHelper::format_rupiah($financials['total_revenue'] ?? 0) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">BIAYA PROMOSI</h3>
            <table class="w-full">
                <tbody class="divide-y divide-gray-200 text-sm">
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Promo Pembelian</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['purchase_promo'] ?? 0) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Promo Produk</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['product_promo'] ?? 0) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Komplimen</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['complimentary'] ?? 0) ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-50">
                        <td class="py-3 pl-2 text-gray-800">TOTAL BIAYA PROMOSI</td>
                        <td class="py-3 pr-2 text-right text-red-600 text-base"><?= NumberHelper::format_rupiah($financials['total_promo_cost'] ?? 0) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">PENJUALAN BERSIH</h3>
            <table class="w-full">
                <tbody class="divide-y divide-gray-200 text-sm">
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Total Pendapatan</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['total_revenue'] ?? 0) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Pengembalian</td>
                        <td class="py-2 pr-2 text-right font-medium text-red-600"><?= NumberHelper::format_rupiah(-($financials['refunds'] ?? 0)) ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-50">
                        <td class="py-3 pl-2 text-gray-800">TOTAL PENJUALAN BERSIH</td>
                        <td class="py-3 pr-2 text-right text-emerald-600 text-base"><?= NumberHelper::format_rupiah($financials['net_sales'] ?? 0) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div>
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">LABA KOTOR</h3>
            <table class="w-full">
                <tbody class="divide-y divide-gray-200 text-sm">
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Penjualan Bersih</td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['net_sales'] ?? 0) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Biaya MDR</td>
                        <td class="py-2 pr-2 text-right font-medium text-red-600"><?= NumberHelper::format_rupiah(-($financials['mdr_fee'] ?? 0)) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">HPP</td>
                        <td class="py-2 pr-2 text-right font-medium text-red-600"><?= NumberHelper::format_rupiah(-($financials['cogs'] ?? 0)) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-2 text-gray-600">Komisi</td>
                        <td class="py-2 pr-2 text-right font-medium text-red-600"><?= NumberHelper::format_rupiah(-($financials['commission'] ?? 0)) ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-green-50">
                        <td class="py-3 pl-2 text-gray-800">TOTAL LABA KOTOR</td>
                        <td class="py-3 pr-2 text-right text-green-600 text-base"><?= NumberHelper::format_rupiah($financials['gross_profit'] ?? 0) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cost Distribution Chart
    const ctx = document.getElementById('costDistributionChart').getContext('2d');
    const financials = <?= json_encode($financials ?? []) ?>;
    const costData = [
        financials.cogs ?? 0,
        financials.total_promo_cost ?? 0,
        financials.admin_fee ?? 0,
        financials.commission ?? 0,
        financials.mdr_fee ?? 0,
        financials.service_charge ?? 0, // Ditambahkan
        financials.tax ?? 0 // Ditambahkan
    ];

    if (costData.some(v => v > 0)) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['HPP', 'Biaya Promosi', 'Biaya Admin', 'Komisi', 'Biaya MDR', 'Biaya Pelayanan', 'Pajak'], // Ditambahkan
                datasets: [{
                    data: costData,
                    backgroundColor: [
                        '#ef4444', '#f59e0b', '#10b981', '#6366f1', '#8b5cf6', '#3b82f6', '#f472b6' // Warna ditambahkan
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    return data.labels.map((label, i) => ({
                                        text: `${label} (${total > 0 ? ((data.datasets[0].data[i]/total)*100).toFixed(1) : 0}%)`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        index: i
                                    }));
                                }
                                return [];
                            }
                        }
                    }
                }
            }
        });
    } else {
        ctx.canvas.parentNode.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Tidak ada data biaya.</div>';
    }
});
</script>