<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;

// Data dari DashboardController
$userName = $userName ?? 'User';
$newOrderCount = $newOrderCount ?? 0;
$processingOrderCount = $processingOrderCount ?? 0;
$unavailableItemCount = $unavailableItemCount ?? 0;
// $pageTitle sudah diatur oleh layout
?>

<div class="mb-6">
    <h2 class="text-2xl font-semibold text-slate-800">Selamat Datang Kembali, <?= SanitizeHelper::html($userName) ?>!</h2>
    <p class="text-slate-500">Berikut ringkasan aktivitas hari ini.</p>
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

    <a href="<?= UrlHelper::baseUrl('/admin/orders?status=preparing') // Bisa juga link ke gabungan status ?>" class="block p-6 bg-gradient-to-br from-yellow-400 to-yellow-500 text-white rounded-xl shadow-lg hover:shadow-yellow-400/50 transition-all duration-300 transform hover:-translate-y-1">
         <div class="flex justify-between items-start mb-2">
            <h3 class="text-lg font-semibold">Pesanan Diproses</h3>
             <svg class="w-8 h-8 text-yellow-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <p class="text-4xl font-bold"><?= SanitizeHelper::html($processingOrderCount) ?></p>
        <p class="text-xs text-yellow-50 mt-1">Status: Preparing / Ready</p>
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

<div class="container mx-auto">
    <!-- Header and Period Selector -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
        <div class="flex items-center bg-white border rounded-lg p-1 shadow-sm">
            <a href="<?= UrlHelper::baseUrl('admin/dashboard?period=daily') ?>" 
               class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors <?= $period === 'daily' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' ?>">
               Hari Ini
            </a>
            <a href="<?= UrlHelper::baseUrl('admin/dashboard?period=weekly') ?>"
               class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors <?= $period === 'weekly' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' ?>">
               7 Hari
            </a>
            <a href="<?= UrlHelper::baseUrl('admin/dashboard?period=monthly') ?>"
               class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors <?= $period === 'monthly' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' ?>">
               Bulan Ini
            </a>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Penjualan -->
        <div class="bg-white border rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-500">Total Penjualan</h3>
            <p class="text-2xl font-semibold text-gray-900 mt-1"><?= NumberHelper::format_rupiah($metrics['total_sales']) ?></p>
        </div>
        <!-- Akumulasi dari Awal Bulan -->
        <div class="bg-white border rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-500">Akumulasi Bulan Ini</h3>
            <p class="text-2xl font-semibold text-gray-900 mt-1"><?= NumberHelper::format_rupiah($mtd_sales) ?></p>
        </div>
        <!-- Proyeksi Bulan Ini -->
        <div class="bg-white border rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-500">Proyeksi Bulan Ini</h3>
            <p class="text-2xl font-semibold text-gray-900 mt-1"><?= NumberHelper::format_rupiah($monthly_projection) ?></p>
        </div>
        <!-- Penjualan Belum Dibayar & Terbayar -->
        <div class="bg-white border rounded-lg shadow-sm p-4">
             <div class="grid grid-cols-2 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Belum Dibayar</h3>
                    <p class="text-lg font-semibold text-amber-600 mt-1"><?= NumberHelper::format_rupiah($metrics['unpaid_sales']) ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Terbayar</h3>
                    <p class="text-lg font-semibold text-green-600 mt-1"><?= NumberHelper::format_rupiah($metrics['paid_sales']) ?></p>
                </div>
            </div>
        </div>
        <!-- Transaksi -->
        <div class="bg-white border rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-500">Transaksi</h3>
            <p class="text-2xl font-semibold text-gray-900 mt-1"><?= number_format($metrics['transactions']) ?></p>
        </div>
        <!-- Penjualan per Transaksi -->
        <div class="bg-white border rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-500">Penjualan per Transaksi</h3>
            <p class="text-2xl font-semibold text-gray-900 mt-1"><?= NumberHelper::format_rupiah($metrics['sales_per_transaction']) ?></p>
        </div>
        <!-- Produk Terjual -->
        <div class="bg-white border rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-500">Produk Terjual</h3>
            <p class="text-2xl font-semibold text-gray-900 mt-1"><?= number_format($metrics['total_products']) ?></p>
        </div>
        <!-- Produk per Transaksi -->
        <div class="bg-white border rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-500">Produk per Transaksi</h3>
            <p class="text-2xl font-semibold text-gray-900 mt-1"><?= number_format($metrics['products_per_transaction'], 2) ?></p>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="bg-white border rounded-lg shadow-sm p-4">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Grafik Penjualan</h2>
        <div class="relative" style="height: 300px;">
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

    const salesCtx = document.getElementById('salesChart')?.getContext('2d');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($chart_data, 'label')); ?>,
                datasets: [{
                    label: 'Penjualan',
                    data: <?= json_encode(array_column($chart_data, 'sales')); ?>,
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
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => formatRupiah(value) }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: { label: c => formatRupiah(c.parsed.y) }
                    }
                }
            }
        });
    }
});
</script>