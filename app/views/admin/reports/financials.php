<?php 
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
                    <div class="text-xl font-bold text-red-600 mt-1"><?= NumberHelper::format_rupiah(($financials['total_promo'] ?? 0) + ($financials['admin_fee'] ?? 0) + ($financials['mdr_fee'] ?? 0) + ($financials['cogs'] ?? 0) + ($financials['commission'] ?? 0)) ?></div>
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

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button class="tab-btn active px-6 py-4 border-b-2 border-indigo-500 text-sm font-medium text-indigo-600" data-target="revenue">Pendapatan</button>
                <button class="tab-btn px-6 py-4 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-target="costs">Biaya</button>
                <button class="tab-btn px-6 py-4 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-target="profit">Laba</button>
            </nav>
        </div>

        <div id="revenue" class="tab-content p-6 space-y-4">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider pb-4">Komponen</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider pb-4">Jumlah</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider pb-4">% dari Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php
                    $revenueComponents = [
                        ['label' => 'Penjualan Kotor', 'value' => $financials['gross_sales'] ?? 0],
                        ['label' => 'Biaya Layanan', 'value' => $financials['service_charge'] ?? 0],
                        ['label' => 'Biaya Layanan MDR', 'value' => $financials['mdr_service_fee'] ?? 0], // <-- BARIS PENYEBAB ERROR
                        ['label' => 'Pajak', 'value' => $financials['tax'] ?? 0],
                    ];
                    foreach ($revenueComponents as $item): 
                        $percentage = ($financials['total_revenue'] ?? 0) > 0 ? 
                            ($item['value'] / $financials['total_revenue'] * 100) : 0;
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 text-sm text-gray-900"><?= $item['label'] ?></td>
                        <td class="py-3 text-sm text-right font-medium text-gray-900"><?= NumberHelper::format_rupiah($item['value']) ?></td>
                        <td class="py-3 text-sm text-right text-gray-500"><?= number_format($percentage, 1) ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bg-indigo-50">
                        <td class="py-3 text-sm font-medium text-gray-900">TOTAL PENDAPATAN</td>
                        <td class="py-3 text-sm text-right font-bold text-indigo-600"><?= NumberHelper::format_rupiah($financials['total_revenue'] ?? 0) ?></td>
                        <td class="py-3 text-sm text-right font-medium text-indigo-600">100%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="costs" class="tab-content hidden p-6 space-y-4">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider pb-4">Komponen</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider pb-4">Jumlah</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider pb-4">% dari Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php
                    $costComponents = [
                        ['label' => 'HPP', 'value' => $financials['cogs'] ?? 0],
                        ['label' => 'Biaya Promosi', 'value' => $financials['total_promo'] ?? 0],
                        ['label' => 'Biaya Admin', 'value' => $financials['admin_fee'] ?? 0],
                        ['label' => 'Komisi', 'value' => $financials['commission'] ?? 0],
                        ['label' => 'Biaya MDR', 'value' => $financials['mdr_fee'] ?? 0],
                    ];
                    foreach ($costComponents as $item): 
                        $percentage = ($financials['total_revenue'] ?? 0) > 0 ? 
                            ($item['value'] / $financials['total_revenue'] * 100) : 0;
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 text-sm text-gray-900"><?= $item['label'] ?></td>
                        <td class="py-3 text-sm text-right font-medium text-gray-900"><?= NumberHelper::format_rupiah($item['value']) ?></td>
                        <td class="py-3 text-sm text-right text-gray-500"><?= number_format($percentage, 1) ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bg-red-50">
                        <td class="py-3 text-sm font-medium text-gray-900">TOTAL BIAYA</td>
                        <td class="py-3 text-sm text-right font-bold text-red-600"><?= NumberHelper::format_rupiah(($financials['total_promo'] ?? 0) + ($financials['admin_fee'] ?? 0) + ($financials['mdr_fee'] ?? 0) + ($financials['cogs'] ?? 0) + ($financials['commission'] ?? 0)) ?></td>
                        <td class="py-3 text-sm text-right font-medium text-red-600"><?= number_format((($financials['total_promo'] ?? 0) + ($financials['admin_fee'] ?? 0) + ($financials['mdr_fee'] ?? 0) + ($financials['cogs'] ?? 0) + ($financials['commission'] ?? 0)) / (($financials['total_revenue'] ?? 0) ?: 1) * 100, 1) ?>%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="profit" class="tab-content hidden p-6 space-y-4">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider pb-4">Komponen</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider pb-4">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 text-sm text-gray-900">Total Pendapatan</td>
                        <td class="py-3 text-sm text-right font-medium text-gray-900"><?= NumberHelper::format_rupiah($financials['total_revenue'] ?? 0) ?></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 text-sm text-gray-900">(Total Biaya)</td>
                        <td class="py-3 text-sm text-right font-medium text-red-600"><?= NumberHelper::format_rupiah(-(($financials['total_promo'] ?? 0) + ($financials['admin_fee'] ?? 0) + ($financials['mdr_fee'] ?? 0) + ($financials['cogs'] ?? 0) + ($financials['commission'] ?? 0))) ?></td>
                    </tr>
                    <tr class="bg-green-50">
                        <td class="py-3 text-sm font-medium text-gray-900">LABA KOTOR</td>
                        <td class="py-3 text-sm text-right font-bold text-green-600"><?= NumberHelper::format_rupiah($financials['gross_profit'] ?? 0) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab Switching Logic
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            button.classList.add('active', 'border-indigo-500', 'text-indigo-600');
            button.classList.remove('border-transparent', 'text-gray-500');
            document.getElementById(button.dataset.target).classList.remove('hidden');
        });
    });

    // Cost Distribution Chart
    const ctx = document.getElementById('costDistributionChart').getContext('2d');
    const financials = <?= json_encode($financials ?? []) ?>;
    const costData = [
        financials.cogs ?? 0,
        financials.total_promo ?? 0,
        financials.admin_fee ?? 0,
        financials.commission ?? 0,
        financials.mdr_fee ?? 0
    ];

    if (costData.some(v => v > 0)) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['HPP', 'Biaya Promosi', 'Biaya Admin', 'Komisi', 'Biaya MDR'],
                datasets: [{
                    data: costData,
                    backgroundColor: [
                        '#ef4444', '#f59e0b', '#10b981', '#6366f1', '#8b5cf6'
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