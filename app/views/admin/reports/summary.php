<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($title); ?></h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
            <div class="bg-blue-500 text-white rounded-full p-3 mr-4"><i class="fas fa-dollar-sign fa-2x"></i></div>
            <div>
                <p class="text-gray-500 text-sm">Pendapatan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?= App\Helpers\NumberHelper::format_rupiah($summary['daily_revenue']); ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
            <div class="bg-green-500 text-white rounded-full p-3 mr-4"><i class="fas fa-shopping-cart fa-2x"></i></div>
            <div>
                <p class="text-gray-500 text-sm">Pesanan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?= $summary['daily_orders']; ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
            <div class="bg-yellow-500 text-white rounded-full p-3 mr-4"><i class="fas fa-coins fa-2x"></i></div>
            <div>
                <p class="text-gray-500 text-sm">Total Pendapatan</p>
                <p class="text-2xl font-bold text-gray-800"><?= App\Helpers\NumberHelper::format_rupiah($summary['total_revenue']); ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
            <div class="bg-red-500 text-white rounded-full p-3 mr-4"><i class="fas fa-check-circle fa-2x"></i></div>
            <div>
                <p class="text-gray-500 text-sm">Total Pesanan Selesai</p>
                <p class="text-2xl font-bold text-gray-800"><?= $summary['total_completed_orders']; ?></p>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Grafik Penjualan (30 Hari Terakhir)</h2>
            <canvas id="salesChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Produk Terlaris</h2>
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr><th scope="col" class="py-3 px-4">Produk</th><th scope="col" class="py-3 px-4 text-right">Terjual</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($top_items)) : ?>
                        <?php foreach ($top_items as $item) : ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-900"><?= htmlspecialchars($item->name); ?></td>
                                <td class="py-3 px-4 text-right"><?= $item->total_quantity; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="2" class="text-center py-4">Data tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_data['labels']); ?>,
            datasets: [{
                label: 'Pendapatan', data: <?= json_encode($chart_data['data']); ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.2)', borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2, tension: 0.3, fill: true
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value) }}},
            plugins: { tooltip: { callbacks: { label: c => (c.dataset.label || '') + ': ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(c.parsed.y) }}}
        }
    });
});
</script>