<?php 
use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;

function render_change_indicator($change) {
    $change = (float)$change;
    if ($change > 0) {
        echo '<span class="text-xs font-semibold text-green-600 flex items-center"><i class="fas fa-arrow-up mr-1"></i>' . number_format($change, 2) . '%</span>';
    } elseif ($change < 0) {
        echo '<span class="text-xs font-semibold text-red-600 flex items-center"><i class="fas fa-arrow-down mr-1"></i>' . number_format(abs($change), 2) . '%</span>';
    } else {
        echo '<span class="text-xs font-semibold text-gray-500 flex items-center">—</span>';
    }
}
?>
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($pageTitle); ?></h1>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="" class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-lg shadow-sm">
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate); ?>" class="border-gray-300 rounded-md shadow-sm text-sm">
                <span class="text-gray-500">-</span>
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate); ?>" class="border-gray-300 rounded-md shadow-sm text-sm">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Filter
                </button>
            </form>
            <a href="<?= UrlHelper::baseUrl('admin/reports/summary/export?start_date=' . htmlspecialchars($startDate) . '&end_date=' . htmlspecialchars($endDate)) ?>" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 flex items-center gap-2">
                <i class="fas fa-file-csv"></i> Export
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php 
        $cards = [
            ['title' => 'Total Pendapatan', 'value' => NumberHelper::format_rupiah($summary['total_revenue']), 'change' => $summary['total_revenue_change'], 'icon' => 'fa-dollar-sign', 'color' => 'blue'],
            ['title' => 'Laba Kotor', 'value' => NumberHelper::format_rupiah($summary['gross_profit']), 'change' => $summary['gross_profit_change'], 'icon' => 'fa-piggy-bank', 'color' => 'green'],
            ['title' => 'Total Pesanan', 'value' => number_format($summary['total_orders']), 'change' => $summary['total_orders_change'], 'icon' => 'fa-receipt', 'color' => 'yellow'],
            ['title' => 'Rata-rata/Pesanan', 'value' => NumberHelper::format_rupiah($summary['aov']), 'change' => $summary['aov_change'], 'icon' => 'fa-chart-pie', 'color' => 'red'],
        ];
        foreach ($cards as $card): ?>
        <div class="bg-white p-5 rounded-lg shadow-md">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-<?= $card['color'] ?>-100 text-<?= $card['color'] ?>-600 mr-4">
                    <i class="fas <?= $card['icon'] ?> fa-lg"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium"><?= $card['title'] ?></p>
                    <p class="text-2xl font-bold text-gray-800"><?= $card['value'] ?></p>
                </div>
            </div>
            <div class="mt-2 text-right">
                <?php render_change_indicator($card['change']); ?>
                <p class="text-xs text-gray-400">vs. periode sebelumnya</p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Grafik Tren Penjualan</h2>
            <div class="relative" style="height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Metode Pembayaran</h2>
            <div class="relative mx-auto mb-4" style="height: 180px; max-width: 180px;">
                <canvas id="paymentChart"></canvas>
            </div>
            <div class="flex-grow">
                <table class="w-full text-sm">
                    <?php foreach($payment_methods as $method): ?>
                    <tr class="border-b last:border-b-0">
                        <td class="py-2 text-gray-600"><?= ucfirst(htmlspecialchars($method->payment_method)) ?></td>
                        <td class="py-2 text-right text-gray-800 font-semibold"><?= NumberHelper::format_rupiah($method->total_amount) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($payment_methods)): ?>
                    <tr><td colspan="2" class="text-center py-4 text-gray-500">Tidak ada data.</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Produk Terlaris</h2>
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr><th class="py-3 px-4">Produk</th><th class="py-3 px-4 text-right">Terjual</th></tr>
                </thead>
                <tbody>
                    <?php foreach($top_items as $item): ?>
                    <tr class="border-b hover:bg-gray-50"><td class="py-3 px-4 font-medium text-gray-900"><?= htmlspecialchars($item->name) ?></td><td class="py-3 px-4 text-right"><?= $item->total_quantity ?></td></tr>
                    <?php endforeach; ?>
                    <?php if(empty($top_items)): ?>
                    <tr><td colspan="2" class="text-center py-4">Data tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Rincian Finansial</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b"><span class="text-gray-600">Penjualan Kotor</span><span class="font-medium text-gray-800"><?= NumberHelper::format_rupiah($financials['gross_sales']) ?></span></div>
                <div class="flex justify-between py-2 border-b"><span class="text-gray-600">HPP (COGS)</span><span class="font-medium text-gray-800">( <?= NumberHelper::format_rupiah($financials['cogs']) ?> )</span></div>
                <div class="flex justify-between pt-3"><span class="font-bold text-gray-700">Laba Kotor</span><span class="font-bold text-lg text-gray-900"><?= NumberHelper::format_rupiah($financials['gross_profit']) ?></span></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formatRupiah = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);

    // Sales Chart (Line)
    const salesCtx = document.getElementById('salesChart')?.getContext('2d');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_data['labels']); ?>,
                datasets: [{
                    label: 'Pendapatan', data: <?= json_encode($chart_data['data']); ?>,
                    backgroundColor: 'rgba(79, 70, 229, 0.1)', borderColor: '#4f46e5',
                    borderWidth: 2, tension: 0.4, fill: true, pointBackgroundColor: '#4f46e5'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value) }}}, plugins: { tooltip: { callbacks: { label: c => formatRupiah(c.parsed.y) }}}}
        });
    }

    // Payment Chart (Pie)
    const paymentCtx = document.getElementById('paymentChart')?.getContext('2d');
    if (paymentCtx && <?= !empty($payment_chart_data['data']) ? 'true' : 'false' ?>) {
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($payment_chart_data['labels']); ?>,
                datasets: [{
                    data: <?= json_encode($payment_chart_data['data']); ?>,
                    backgroundColor: <?= json_encode($payment_chart_data['colors']); ?>,
                    hoverOffset: 4,
                    borderWidth: 0,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => `${c.label}: ${formatRupiah(c.raw)}` }}}}
        });
    }
});
</script>