<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;

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

    <a href="<?= UrlHelper::baseUrl('/admin/orders?status=received') ?>" class="block p-6 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg hover:shadow-blue-400/50 transition-all duration-300 transform hover:-translate-y-1">
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
    </div>
</div>