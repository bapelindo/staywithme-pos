<?php
// File: app/Views/admin/orders/index.php (Revisi Hapus Paid)
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;
use App\Helpers\DateHelper;

// Data dari OrderController
$orders = $orders ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$statusFilter = $statusFilter ?? 'all';
$totalOrders = $totalOrders ?? 0;

// === PERUBAHAN: Hapus 'paid' ===
$statusClasses = [
    'pending_payment' => 'bg-orange-100 text-orange-700 animate-pulse',
    'received' => 'bg-blue-100 text-blue-700',
    'preparing' => 'bg-yellow-100 text-yellow-700',
    'ready' => 'bg-teal-100 text-teal-700',
    'served' => 'bg-green-100 text-green-700',
    // 'paid' dihapus
    'cancelled' => 'bg-red-100 text-red-700',
];
$statusTexts = [
    'pending_payment' => 'Pembayaran',
    'received' => 'Diterima',
    'preparing' => 'Disiapkan',
    'ready' => 'Siap',
    'served' => 'Disajikan',
    // 'paid' dihapus
    'cancelled' => 'Batal'
];
// === AKHIR PERUBAHAN ===
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold text-slate-800">Daftar Pesanan (<?= SanitizeHelper::html($totalOrders) ?>)</h2>
    <div class="flex flex-wrap gap-2">
         <?php
         // === PERUBAHAN: Hapus filter 'paid' ===
         $filterStatuses = [
             'all' => 'Semua',
             'pending_payment' => 'Pembayaran',
             'received' => 'Diterima',
             'preparing' => 'Disiapkan',
             'ready' => 'Siap',
             'served' => 'Disajikan',
             // 'paid' => 'Lunas', // <- Hapus baris ini
             'cancelled' => 'Batal'
            ];
         // === AKHIR PERUBAHAN ===
         ?>
         <?php foreach ($filterStatuses as $key => $text): ?>
            <a href="<?= UrlHelper::baseUrl('/admin/orders?status=' . $key) ?>"
               class="px-3 py-1.5 text-xs font-medium rounded-md transition whitespace-nowrap
                      <?= ($statusFilter === $key)
                         ? 'bg-indigo-600 text-white shadow-sm'
                         : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-50' ?>">
               <?= SanitizeHelper::html($text) ?>
            </a>
         <?php endforeach; ?>
    </div>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden border border-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <?php // Thead tabel tetap sama ?>
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Order #</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Meja</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Waktu</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody id="admin-orders-table-body" class="bg-white divide-y divide-slate-200">
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500 text-sm">
                            Tidak ada pesanan ditemukan <?= ($statusFilter !== 'all' && isset($statusTexts[$statusFilter])) ? 'untuk status "' . SanitizeHelper::html($statusTexts[$statusFilter]) . '"' : '' ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <?php // Render data kolom seperti sebelumnya ?>
                             <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                <?= SanitizeHelper::html($order['order_number']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                <?= SanitizeHelper::html($order['table_number']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <span title="<?= DateHelper::formatIndonesian($order['order_time'], 'full') ?>">
                                    <?= DateHelper::timeAgo($order['order_time']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 text-right font-medium">
                                <?= NumberHelper::formatCurrencyIDR($order['total_amount']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-medium">
                                <span class="px-2.5 py-0.5 rounded-full <?= $statusClasses[$order['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                                    <?= SanitizeHelper::html($statusTexts[$order['status']] ?? ucfirst($order['status'])) ?>
                                </span>
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="<?= UrlHelper::baseUrl('/admin/orders/show/' . $order['id']) ?>" class="text-indigo-600 hover:text-indigo-800" title="Lihat Detail">Detail</a>
                                <a href="<?= UrlHelper::baseUrl('/admin/orders/invoice/' . $order['id']) ?>" class="text-green-600 hover:text-green-800" title="Lihat Invoice" target="_blank">Invoice</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php // Paginasi (Tetap Sama) ?>
    <?php if ($totalPages > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-slate-200 sm:px-6">
            <?php // ... Kode paginasi ... ?>
        </div>
    <?php endif; ?>
</div>