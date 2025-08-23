<?php 
use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;

function render_change_indicator($change) {
    $change = (float)$change;
    if ($change > 0) {
        return '<span class="text-xs font-medium text-green-600 flex items-center gap-1"><i class="fas fa-arrow-up"></i>' . number_format($change, 2) . '%</span>';
    } elseif ($change < 0) {
        return '<span class="text-xs font-medium text-red-600 flex items-center gap-1"><i class="fas fa-arrow-down"></i>' . number_format(abs($change), 2) . '%</span>';
    }
    return '<span class="text-xs font-medium text-gray-500">—</span>';
}
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
            <a href="<?= UrlHelper::baseUrl('admin/reports/summary/export?start_date=' . htmlspecialchars($startDate) . '&end_date=' . htmlspecialchars($endDate)) ?>" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-file-export"></i>
                Export CSV
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <?php 
        $cards = [
            ['title' => 'Total Pendapatan', 'value' => NumberHelper::format_rupiah($summary['total_revenue'] ?? 0), 'change' => $summary['total_revenue_change'] ?? 0, 'icon' => 'fa-dollar-sign', 'color' => 'indigo'],
            ['title' => 'Laba Kotor', 'value' => NumberHelper::format_rupiah($summary['gross_profit'] ?? 0), 'change' => $summary['gross_profit_change'] ?? 0, 'icon' => 'fa-piggy-bank', 'color' => 'green'],
            ['title' => 'Total Pesanan', 'value' => number_format($summary['total_orders'] ?? 0), 'change' => $summary['total_orders_change'] ?? 0, 'icon' => 'fa-receipt', 'color' => 'amber'],
            ['title' => 'Rata-rata/Pesanan', 'value' => NumberHelper::format_rupiah($summary['aov'] ?? 0), 'change' => $summary['aov_change'] ?? 0, 'icon' => 'fa-chart-pie', 'color' => 'rose']
        ];
        
        foreach ($cards as $card): 
            $bgColor = "bg-{$card['color']}-50";
            $textColor = "text-{$card['color']}-600";
        ?>
        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500"><?= $card['title'] ?></span>
                    <div class="<?= $bgColor ?> <?= $textColor ?> p-2 rounded-lg">
                        <i class="fas <?= $card['icon'] ?>"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-semibold text-gray-900"><?= $card['value'] ?></h3>
                    <?= render_change_indicator($card['change']) ?>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-2 text-xs text-gray-500">
                vs periode sebelumnya
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white border rounded-lg shadow-sm p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Tren Penjualan</h2>
            <div class="relative" style="height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="bg-white border rounded-lg shadow-sm p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Metode Pembayaran</h2>
            <?php 
            $totalPayments = array_sum(array_map(function($method) {
                return $method->total_amount;
            }, $payment_methods ?? []));
            
            if (empty($payment_methods) || $totalPayments == 0): 
            ?>
                <div class="flex items-center justify-center h-[180px] text-gray-500">
                    <div class="text-center">
                        <i class="fas fa-receipt text-3xl mb-2"></i>
                        <p>Belum ada transaksi</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="relative mx-auto mb-4" style="height: 180px; max-width: 180px;">
                    <canvas id="paymentChart"></canvas>
                </div>
                <div class="mt-4">
                    <table class="w-full text-sm">
                        <?php 
                        foreach($payment_methods as $index => $method): 
                            $colorIndex = $index % count($payment_chart_data['colors']);
                        ?>
                            <tr class="border-b last:border-0">
                                <td class="py-2">
                                    <span class="inline-flex items-center">
                                        <span class="w-3 h-3 rounded-full mr-2" 
                                              style="background-color: <?= $payment_chart_data['colors'][$colorIndex] ?>">
                                        </span>
                                        <?= ucfirst(htmlspecialchars($method->payment_method)) ?>
                                        <span class="text-gray-500 ml-1">(<?= $method->transaction_count ?>)</span>
                                    </span>
                                </td>
                                <td class="py-2 text-right font-medium">
                                    <?= NumberHelper::format_rupiah($method->total_amount) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border rounded-lg shadow-sm p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Produk Terlaris</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Terjual</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($top_items as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($item->name) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium"><?= $item->total_quantity ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white border rounded-lg shadow-sm p-6" x-data="{ showPopover: null }">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Ringkasan Finansial</h2>
                <a href="<?= UrlHelper::baseUrl('admin/reports/financials?start_date=' . $startDate . '&end_date=' . $endDate) ?>" 
                   class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
                    Lihat Detail <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 p-4 bg-indigo-50 rounded-lg relative">
                    <div class="flex items-center gap-2">
                        <div class="text-sm text-gray-600">Total Pendapatan</div>
                        <button @click="showPopover = showPopover === 'revenue' ? null : 'revenue'" 
                                class="text-gray-400 hover:text-indigo-600 focus:outline-none">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                    <div class="text-xl font-bold text-indigo-600 mt-1">
                        <?= NumberHelper::format_rupiah($financials['total_revenue'] ?? 0) ?>
                    </div>
                    <div x-show="showPopover === 'revenue'" @click.away="showPopover = null" x-transition class="absolute left-0 mt-2 w-full max-w-sm bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">Komponen Total Pendapatan</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Penjualan Kotor</span><span class="font-medium"><?= NumberHelper::format_rupiah($financials['gross_sales'] ?? 0) ?></span></div>
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Biaya Layanan</span><span class="font-medium"><?= NumberHelper::format_rupiah($financials['service_charge'] ?? 0) ?></span></div>
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Pajak</span><span class="font-medium"><?= NumberHelper::format_rupiah($financials['tax'] ?? 0) ?></span></div>
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Lainnya</span><span class="font-medium"><?= NumberHelper::format_rupiah($financials['other_revenue'] ?? 0) ?></span></div>
                                <div class="pt-2 mt-2 border-t border-gray-200"><div class="flex justify-between font-medium"><span class="text-gray-900">Total</span><span class="text-indigo-600"><?= NumberHelper::format_rupiah($financials['total_revenue'] ?? 0) ?></span></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-red-50 rounded-lg relative">
                    <div class="flex items-center gap-2">
                        <div class="text-sm text-gray-600">Total Biaya & HPP</div>
                        <button @click="showPopover = showPopover === 'costs' ? null : 'costs'" class="text-gray-400 hover:text-red-600 focus:outline-none"><i class="fas fa-info-circle"></i></button>
                    </div>
                    <div class="text-lg font-bold text-red-600 mt-1">
                        <?= NumberHelper::format_rupiah(
                            ($financials['cogs'] ?? 0) + 
                            ($financials['total_promo_cost'] ?? 0) + 
                            ($financials['admin_fee'] ?? 0) + 
                            ($financials['mdr_fee'] ?? 0) + 
                            ($financials['commission'] ?? 0)
                        ) ?>
                    </div>
                    <div x-show="showPopover === 'costs'" @click.away="showPopover = null" x-transition class="absolute left-0 mt-2 w-full max-w-sm bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">Komponen Biaya</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm"><span class="text-gray-600">HPP</span><span class="font-medium"><?= NumberHelper::format_rupiah($financials['cogs'] ?? 0) ?></span></div>
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Biaya Promosi</span><span class="font-medium"><?= NumberHelper::format_rupiah($financials['total_promo_cost'] ?? 0) ?></span></div>
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Biaya Admin</span><span class="font-medium"><?= NumberHelper::format_rupiah($financials['admin_fee'] ?? 0) ?></span></div>
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Komisi</span><span class="font-medium"><?= NumberHelper::format_rupiah($financials['commission'] ?? 0) ?></span></div>
                                <div class="pt-2 mt-2 border-t border-gray-200"><div class="flex justify-between font-medium"><span class="text-gray-900">Total</span><span class="text-red-600"><?= NumberHelper::format_rupiah(($financials['cogs'] ?? 0) + ($financials['total_promo_cost'] ?? 0) + ($financials['admin_fee'] ?? 0) + ($financials['mdr_fee'] ?? 0) + ($financials['commission'] ?? 0)) ?></span></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-emerald-50 rounded-lg relative">
                    <div class="flex items-center gap-2">
                        <div class="text-sm text-gray-600">Penjualan Bersih</div>
                        <button @click="showPopover = showPopover === 'netSales' ? null : 'netSales'" class="text-gray-400 hover:text-emerald-600 focus:outline-none"><i class="fas fa-info-circle"></i></button>
                    </div>
                    <div class="text-lg font-bold text-emerald-600 mt-1">
                        <?= NumberHelper::format_rupiah($financials['net_sales'] ?? 0) ?>
                    </div>
                    <div x-show="showPopover === 'netSales'" @click.away="showPopover = null" x-transition class="absolute left-0 mt-2 w-full max-w-sm bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">Komponen Penjualan Bersih</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Total Penjualan</span><span class="font-medium"><?= NumberHelper::format_rupiah($financials['total_revenue'] ?? 0) ?></span></div>
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Pengembalian Dana</span><span class="font-medium text-red-600">- <?= NumberHelper::format_rupiah($financials['refunds'] ?? 0) ?></span></div>
                                <div class="pt-2 mt-2 border-t border-gray-200"><div class="flex justify-between font-medium"><span class="text-gray-900">Total</span><span class="text-emerald-600"><?= NumberHelper::format_rupiah($financials['net_sales'] ?? 0) ?></span></div></div>
                                </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-2 p-4 bg-green-50 rounded-lg border border-green-100 relative">
                    <div class="flex items-center gap-2">
                        <div class="text-sm text-gray-600">Laba Kotor</div>
                        <button @click="showPopover = showPopover === 'profit' ? null : 'profit'" class="text-gray-400 hover:text-green-600 focus:outline-none"><i class="fas fa-info-circle"></i></button>
                    </div>
                    <div class="text-2xl font-bold text-green-600 mt-1">
                        <?= NumberHelper::format_rupiah($financials['gross_profit'] ?? 0) ?>
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Margin: <?= number_format(((float)($financials['gross_profit'] ?? 0) / ((float)($financials['net_sales'] ?? 0) ?: 1)) * 100, 1) ?>%
                    </div>
                    <div x-show="showPopover === 'profit'" @click.away="showPopover = null" x-transition class="absolute left-0 mt-2 w-full max-w-sm bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">Komponen Laba Kotor</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Penjualan Bersih</span><span class="font-medium"><?= NumberHelper::format_rupiah($financials['net_sales'] ?? 0) ?></span></div>
                                <div class="flex justify-between text-sm"><span class="text-gray-600">HPP (COGS)</span><span class="font-medium text-red-600">- <?= NumberHelper::format_rupiah($financials['cogs'] ?? 0) ?></span></div>
                                <div class="pt-2 mt-2 border-t border-gray-200"><div class="flex justify-between font-medium"><span class="text-gray-900">Total</span><span class="text-green-600"><?= NumberHelper::format_rupiah($financials['gross_profit'] ?? 0) ?></span></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formatRupiah = (value) => new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR', 
        minimumFractionDigits: 0 
    }).format(value);

    // Sales Chart
    const salesCtx = document.getElementById('salesChart')?.getContext('2d');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_data['labels']); ?>,
                datasets: [{
                    label: 'Pendapatan',
                    data: <?= json_encode($chart_data['data']); ?>,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4f46e5'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { callback: value => formatRupiah(value) } } },
                plugins: { tooltip: { callbacks: { label: c => formatRupiah(c.parsed.y) } } }
            }
        });
    }

    // Payment Methods Chart
    const paymentCtx = document.getElementById('paymentChart')?.getContext('2d');
    const paymentMethods = <?= json_encode($payment_methods) ?>;
    
    if (paymentCtx && paymentMethods && paymentMethods.length > 0) {
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($payment_chart_data['labels']) ?>,
                datasets: [{
                    data: <?= json_encode($payment_chart_data['data']) ?>,
                    backgroundColor: <?= json_encode($payment_chart_data['colors']) ?>,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                return `${ctx.label}: ${formatRupiah(ctx.raw)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>