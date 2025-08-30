<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;

// Data dari DashboardController (sudah digabung)
$userName = $userName ?? 'User';
$newOrderCount = $newOrderCount ?? 0;
$processingOrderCount = $processingOrderCount ?? 0;
$unavailableItemCount = $unavailableItemCount ?? 0;

$metrics = $metrics ?? [];
$mtd_sales = $mtd_sales ?? 0;
$monthly_projection = $monthly_projection ?? 0;
$currentDate = $currentDate ?? date('Y-m-d');
$period = $period ?? 'daily';
$prevDate = $prevDate ?? '';
$nextDate = $nextDate ?? '';
$chart_data = $chart_data ?? [];
$sales_forecast = $sales_forecast ?? [];
$pageTitleForSales = 'Dashboard Penjualan'; // Judul spesifik untuk bagian penjualan
?>

<div class="mb-6">
    <h2 class="text-2xl font-semibold text-slate-800">Selamat Datang Kembali, <?= SanitizeHelper::html($userName) ?>!</h2>
    <p class="text-slate-500">Berikut ringkasan aktivitas operasional hari ini.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <a href="<?= UrlHelper::baseUrl('/admin/orders?status=pending_payment') ?>" class="block p-6 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg hover:shadow-blue-400/50 transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex justify-between items-start mb-2">
            <h3 class="text-lg font-semibold">Pesanan Baru Diterima</h3>
            <svg class="w-8 h-8 text-blue-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
        </div>
        <p class="text-4xl font-bold"><?= SanitizeHelper::html($newOrderCount) ?></p>
        <p class="text-xs text-blue-100 mt-1">Klik untuk melihat detail</p>
    </a>

    <a href="<?= UrlHelper::baseUrl('/admin/orders?status=preparing') ?>" class="block p-6 bg-gradient-to-br from-yellow-400 to-yellow-500 text-white rounded-xl shadow-lg hover:shadow-yellow-400/50 transition-all duration-300 transform hover:-translate-y-1">
         <div class="flex justify-between items-start mb-2">
            <h3 class="text-lg font-semibold">Pesanan Diproses</h3>
             <svg class="w-8 h-8 text-yellow-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <p class="text-4xl font-bold"><?= SanitizeHelper::html($processingOrderCount) ?></p>
        <p class="text-xs text-yellow-50 mt-1">Status: Received / Preparing</p>
    </a>

    <a href="<?= UrlHelper::baseUrl('/admin/menu') ?>" class="block p-6 bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl shadow-lg hover:shadow-red-400/50 transition-all duration-300 transform hover:-translate-y-1">
         <div class="flex justify-between items-start mb-2">
            <h3 class="text-lg font-semibold">Item Menu Habis</h3>
            <svg class="w-8 h-8 text-red-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
        </div>
        <p class="text-4xl font-bold"><?= SanitizeHelper::html($unavailableItemCount) ?></p>
        <p class="text-xs text-red-100 mt-1">Periksa & update ketersediaan</p>
    </a>
</div>

<div class="mt-10 bg-white p-6 rounded-lg shadow border border-slate-200">
    <h3 class="text-lg font-semibold text-slate-700 mb-4">Akses Cepat</h3>
    <div class="flex flex-wrap gap-4">
        <a href="<?= UrlHelper::baseUrl('/admin/orders') ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-4 rounded-lg transition">Lihat Semua Pesanan</a>
        <a href="<?= UrlHelper::baseUrl('/admin/menu/create') ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-4 rounded-lg transition">Tambah Item Menu</a>
        <a href="<?= UrlHelper::baseUrl('/admin/kds') ?>" target="_blank" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-4 rounded-lg transition">Buka KDS</a>
        <a href="<?= UrlHelper::baseUrl('/cds') ?>" target="_blank" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-4 rounded-lg transition">Buka CDS</a>
    </div>
</div>

<div class="mt-8 bg-white rounded-lg shadow border border-slate-200">
    <h3 class="text-lg font-semibold text-slate-700 p-5 border-b border-slate-200">
        <i class="fa-solid fa-wand-magic-sparkles text-indigo-500 mr-2"></i>Prediksi Penjualan (7 Hari ke Depan)
    </h3>
    <div class="overflow-x-auto">
        <?php if (empty($sales_forecast)): ?>
            <p class="text-center text-slate-500 p-10 text-sm">Data historis tidak cukup untuk membuat prediksi.</p>
        <?php else: ?>
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Hari</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Estimasi Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php foreach ($sales_forecast as $forecast): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?= \App\Helpers\DateHelper::formatIndonesian($forecast['date'], 'dateonly') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                                <?= SanitizeHelper::html($forecast['day_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 font-semibold text-right">
                                <?= \App\Helpers\NumberHelper::formatCurrencyIDR($forecast['projected_sales']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <div class="p-4 bg-slate-50 text-xs text-slate-500 border-t">
        *Prediksi dihitung berdasarkan rata-rata penjualan per hari dari 90 hari terakhir.
    </div>
</div>

<hr class="my-12 border-t border-slate-200">

<div class="container mx-auto">
    <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($pageTitleForSales) ?></h1>
                <p class="text-sm text-slate-500 mt-1">
                    <?php
                    $date = new DateTime($currentDate);
                    if ($period === 'daily') {
                        echo $date->format('l, j F Y');
                    } elseif ($period === 'weekly') {
                        $startOfWeek = (clone $date)->modify('monday this week')->format('j M');
                        $endOfWeek = (clone $date)->modify('sunday this week')->format('j M Y');
                        echo "Minggu: $startOfWeek - $endOfWeek";
                    } elseif ($period === 'monthly') {
                        echo "Bulan: " . $date->format('F Y');
                    }
                    ?>
                </p>
            </div>
            <div class="flex items-center bg-slate-100 rounded-full p-1">
                <a href="<?= UrlHelper::baseUrl('admin/dashboard?period=' . $period . '&date=' . $prevDate) ?>" class="p-2 text-slate-500 hover:text-indigo-600 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <a href="<?= UrlHelper::baseUrl('admin/dashboard?period=daily&date=' . date('Y-m-d')) ?>" class="px-4 py-1.5 text-sm font-semibold rounded-full transition-all duration-200 <?= $period === 'daily' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-indigo-600' ?>">
                   Hari Ini
                </a>
                <a href="<?= UrlHelper::baseUrl('admin/dashboard?period=weekly&date=' . date('Y-m-d')) ?>" class="px-4 py-1.5 text-sm font-semibold rounded-full transition-all duration-200 <?= $period === 'weekly' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-indigo-600' ?>">
                   Minggu Ini
                </a>
                <a href="<?= UrlHelper::baseUrl('admin/dashboard?period=monthly&date=' . date('Y-m-d')) ?>" class="px-4 py-1.5 text-sm font-semibold rounded-full transition-all duration-200 <?= $period === 'monthly' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-indigo-600' ?>">
                   Bulan Ini
                </a>
                <a href="<?= UrlHelper::baseUrl('admin/dashboard?period=' . $period . '&date=' . $nextDate) ?>" class="p-2 text-slate-500 hover:text-indigo-600 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        
        <div class="bg-white rounded-xl shadow-md p-6 flex flex-col">
            <h3 class="text-sm font-medium text-slate-500 flex-shrink-0">Total Penjualan</h3>
            <div class="flex-grow flex items-end">
                <p class="text-2xl font-semibold text-slate-800 mt-5"><?= NumberHelper::format_rupiah($metrics['total_sales'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 flex flex-col">
            <h3 class="text-sm font-medium text-slate-500 flex-shrink-0">Akumulasi Bulan Ini</h3>
            <div class="flex-grow flex items-end">
                <p class="text-2xl font-semibold text-slate-800 mt-5"><?= NumberHelper::format_rupiah($mtd_sales) ?></p>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 flex flex-col">
            <h3 class="text-sm font-medium text-slate-500 flex-shrink-0">Proyeksi Bulan Ini</h3>
            <div class="flex-grow flex items-end">
                <p class="text-2xl font-semibold text-slate-800 mt-5"><?= NumberHelper::format_rupiah($monthly_projection) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 flex flex-col">
            <h3 class="text-sm font-medium text-slate-500 flex-shrink-0">Status Pembayaran</h3>
            <div class="flex-grow flex items-end">
                <div class="flex items-center space-x-4 w-full">
                    <div>
                        <p class="text-lg font-semibold text-green-600"><?= NumberHelper::format_rupiah($metrics['paid_sales'] ?? 0) ?></p>
                        <span class="text-xs text-slate-400 mt-1">Terbayar</span>
                    </div>
                    <div class="border-l h-6 border-slate-200"></div>
                    <div>
                        <p class="text-lg font-semibold text-amber-600"><?= NumberHelper::format_rupiah($metrics['unpaid_sales'] ?? 0) ?></p>
                        <span class="text-xs text-slate-400 mt-1">Belum Dibayar</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2 flex flex-col">
             <h3 class="text-sm font-medium text-slate-500 flex-shrink-0">Detail Transaksi</h3>
             <div class="flex-grow flex items-end">
                 <div class="flex items-center space-x-4">
                    <div>
                        <p class="text-lg font-semibold text-slate-800"><?= number_format($metrics['transactions'] ?? 0) ?></p>
                        <span class="text-xs text-slate-400 mt-1">Total Transaksi</span>
                    </div>
                     <div class="border-l h-6 border-slate-200"></div>
                    <div>
                        <p class="text-lg font-semibold text-slate-800"><?= number_format($metrics['total_products'] ?? 0) ?></p>
                        <span class="text-xs text-slate-400 mt-1">Produk Terjual</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2 flex flex-col">
            <h3 class="text-sm font-medium text-slate-500 flex-shrink-0">Rata-rata per Transaksi</h3>
            <div class="flex-grow flex items-end">
                <div class="flex items-center space-x-4">
                    <div>
                        <p class="text-lg font-semibold text-slate-800"><?= NumberHelper::format_rupiah($metrics['sales_per_transaction'] ?? 0) ?></p>
                        <span class="text-xs text-slate-400 mt-1">Penjualan</span>
                    </div>
                    <div class="border-l h-6 border-slate-200"></div>
                    <div>
                        <p class="text-lg font-semibold text-slate-800"><?= number_format($metrics['products_per_transaction'] ?? 0, 2) ?></p>
                        <span class="text-xs text-slate-400 mt-1">Produk</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border rounded-lg shadow-sm p-6 mt-8">
        <div class="flex flex-wrap justify-between items-start mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Grafik Penjualan</h2>
                <p class="text-sm text-gray-500">Perbandingan dengan periode sebelumnya.</p>
            </div>
            <div class="flex space-x-4 text-sm">
                <div class="text-right">
                    <span class="font-medium text-gray-700">Total Periode Ini</span>
                    <p id="totalCurrentSales" class="font-bold text-indigo-600 text-lg"></p>
                </div>
                <div class="text-right">
                    <span class="font-medium text-gray-500">Total Periode Lalu</span>
                    <p id="totalPreviousSales" class="font-bold text-gray-500 text-lg"></p>
                </div>
            </div>
        </div>
        <div class="relative" style="height: 320px;">
            <canvas id="salesChart"></canvas>
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

    const chartData = <?= json_encode($chart_data); ?>;
    const labels = chartData.map(d => d.label);
    const currentSales = chartData.map(d => d.current_sales);
    const previousSales = chartData.map(d => d.previous_sales);

    const totalCurrentSales = currentSales.reduce((a, b) => a + b, 0);
    const totalPreviousSales = previousSales.reduce((a, b) => a + b, 0);

    document.getElementById('totalCurrentSales').textContent = formatRupiah(totalCurrentSales);
    document.getElementById('totalPreviousSales').textContent = formatRupiah(totalPreviousSales);

    const salesCtx = document.getElementById('salesChart')?.getContext('2d');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Periode Ini',
                    data: currentSales,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#4f46e5',
                }, {
                    label: 'Periode Sebelumnya',
                    data: previousSales,
                    borderColor: '#9ca3af',
                    backgroundColor: 'rgba(156, 163, 175, 0.05)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#9ca3af',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#9ca3af',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => formatRupiah(value),
                            color: '#6b7280'
                        },
                        grid: {
                            borderColor: '#e5e7eb',
                            drawBorder: false,
                        }
                    },
                    x: {
                        ticks: { color: '#6b7280' },
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 20,
                            color: '#374151'
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#fff',
                        titleColor: '#374151',
                        bodyColor: '#374151',
                        borderColor: '#e5e7eb',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += formatRupiah(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
            }
        });
    }
});
</script>