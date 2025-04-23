<?php
// Lokasi File: app/views/admin/orders/index.php
// Dimuat oleh Admin\OrderController::index() (Controller belum dibuat)

ob_start();
// Data: $title, $pageTitle, $orders, $filters (nilai filter saat ini)
?>

<div class="mb-6 bg-white p-4 rounded-lg shadow-sm border">
    <form action="<?= url_for('/admin/orders') ?>" method="GET" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4 items-end">
        <div>
            <label for="filter_date" class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
            <input type="date" id="filter_date" name="filter_date"
                   value="<?= htmlspecialchars($filters['date'] ?? '') ?>"
                   class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm bg-white">
        </div>
        <div>
            <label for="filter_table" class="block text-xs font-medium text-gray-600 mb-1">Nomor Meja</label>
            <input type="text" id="filter_table" name="filter_table"
                    value="<?= htmlspecialchars($filters['table'] ?? '') ?>"
                   class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm" placeholder="Misal: M01">
        </div>
         <div>
            <label for="filter_status" class="block text-xs font-medium text-gray-600 mb-1">Status Pesanan</label>
            <select id="filter_status" name="filter_status" class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm bg-white">
                <option value="">Semua Status</option>
                <?php
                $statuses = ['pending', 'received', 'preparing', 'ready', 'served', 'paid', 'cancelled'];
                foreach ($statuses as $status): ?>
                 <option value="<?= $status ?>" <?= (($filters['status'] ?? '') == $status) ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="filter_payment" class="block text-xs font-medium text-gray-600 mb-1">Status Bayar</label>
            <select id="filter_payment" name="filter_payment" class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm bg-white">
                 <option value="">Semua Status</option>
                 <option value="unpaid" <?= (($filters['payment'] ?? '') == 'unpaid') ? 'selected' : '' ?>>Belum Bayar</option>
                 <option value="paid" <?= (($filters['payment'] ?? '') == 'paid') ? 'selected' : '' ?>>Sudah Bayar</option>
                 <option value="failed" <?= (($filters['payment'] ?? '') == 'failed') ? 'selected' : '' ?>>Gagal</option>
            </select>
        </div>
        <div class="flex space-x-2">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1.5 px-4 rounded-md text-sm">Filter</button>
            <a href="<?= url_for('/admin/orders') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-1.5 px-4 rounded-md text-sm">Reset</a>
        </div>
    </form>
</div>

<div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
    <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
        <thead>
            <tr class="text-left bg-gray-100 sticky top-0">
                <?php // Tambahkan Link Sorting jika perlu ?>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Order</th>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Meja</th>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Pesan</th>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">Total</th>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Status Order</th>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Status Bayar</th>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
             <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order):
                    // Mapping status ke warna (contoh)
                    $statusColor = match($order->order_status) {
                        'pending', 'received' => 'bg-yellow-100 text-yellow-800',
                        'preparing' => 'bg-blue-100 text-blue-800',
                        'ready', 'served' => 'bg-teal-100 text-teal-800',
                        'paid' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800'
                    };
                     $paymentColor = match($order->payment_status) {
                        'unpaid' => 'bg-yellow-100 text-yellow-800',
                        'paid' => 'bg-green-100 text-green-800',
                        'failed' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800'
                    };
                ?>
                <tr class="hover:bg-gray-50 border-b border-gray-200">
                    <td class="py-3 px-3 text-sm font-medium text-indigo-600 hover:underline">
                         <a href="<?= url_for('/admin/orders/show/' . $order->id) ?>"><?= htmlspecialchars($order->order_code) ?></a>
                    </td>
                    <td class="py-3 px-3 text-sm text-gray-600"><?= htmlspecialchars($order->table_number ?? '?') // Asumsi ada join atau relasi ?></td>
                    <td class="py-3 px-3 text-sm text-gray-500"><?= format_datetime($order->ordered_at, 'd/m/y H:i') ?></td>
                    <td class="py-3 px-3 text-sm text-gray-900 font-semibold text-right"><?= format_rupiah($order->total_amount) ?></td>
                    <td class="py-3 px-3 text-center">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusColor ?>">
                            <?= ucfirst(htmlspecialchars($order->order_status)) ?>
                         </span>
                    </td>
                     <td class="py-3 px-3 text-center">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $paymentColor ?>">
                            <?= ucfirst(htmlspecialchars($order->payment_status)) ?>
                         </span>
                    </td>
                    <td class="py-3 px-3 text-sm text-center space-x-1 whitespace-nowrap">
                         <a href="<?= url_for('/admin/orders/show/' . $order->id) ?>" title="Lihat Detail"
                           class="text-blue-500 hover:text-blue-700 p-1 inline-block">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                         </a>
                         <?php // Tambahkan aksi lain: print invoice, update status manual?, cancel order? ?>
                          <a href="<?= url_for('/admin/invoice/print/' . $order->id) ?>" title="Cetak Invoice" target="_blank"
                           class="text-gray-500 hover:text-gray-700 p-1 inline-block">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                         </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-10 text-gray-500 italic">Tidak ada data pesanan yang cocok dengan filter.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4">
    <?php // Tampilkan link pagination di sini ?>
</div>


<td class="py-3 px-3 text-sm text-center space-x-1 whitespace-nowrap">
                         <a href="<?= url_for('/admin/orders/show/' . $order->id) ?>" title="Lihat Detail"
                           class="text-blue-500 hover:text-blue-700 p-1 inline-block">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                         </a>
                         <?php // Tambahkan tombol bayar jika belum lunas ?>
                         <?php if ($order->payment_status === 'unpaid'): ?>
                            <a href="<?= url_for('/admin/payments/process/' . $order->id) ?>" title="Proses Pembayaran Tunai"
                               class="text-green-600 hover:text-green-800 p-1 inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </a>
                         <?php endif; ?>
                         <?php // Tombol Print Invoice tetap ada ?>
                         <a href="<?= url_for('/admin/invoice/print/' . $order->id) ?>" title="Cetak Invoice" target="_blank"
                           class="text-gray-500 hover:text-gray-700 p-1 inline-block">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                         </a>
                         <?php // Mungkin ada tombol Cancel Order? ?>
                    </td>

<?php
$content = ob_get_clean();
require APPROOT . '/views/layouts/admin.php';
?>