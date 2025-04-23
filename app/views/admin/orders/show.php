<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;
use App\Helpers\DateHelper;
use App\Helpers\AuthHelper; // Untuk cek role jika perlu

// Data dari OrderController
$order = $order ?? null;
$payment = $payment ?? null;
// $pageTitle sudah diatur oleh layout

// Fungsi helper lokal untuk kelas badge status (sama seperti di index)
$statusClasses = [ /* ... (salin dari orders/index.php) ... */
    'pending' => 'bg-gray-100 text-gray-600', 'received' => 'bg-blue-100 text-blue-700',
    'preparing' => 'bg-yellow-100 text-yellow-700', 'ready' => 'bg-teal-100 text-teal-700',
    'served' => 'bg-green-100 text-green-700', 'paid' => 'bg-indigo-100 text-indigo-700',
    'cancelled' => 'bg-red-100 text-red-700',
];
$statusTexts = [ /* ... (salin dari orders/index.php) ... */
    'pending' => 'Pending', 'received' => 'Diterima', 'preparing' => 'Disiapkan',
    'ready' => 'Siap', 'served' => 'Disajikan', 'paid' => 'Lunas', 'cancelled' => 'Batal'
];

$isPaidOrCancelled = in_array($order['status'] ?? '', ['paid', 'cancelled']);
?>

<?php if ($order): ?>
<div class="max-w-4xl mx-auto">
    <div class="mb-4 flex justify-between items-center">
         <a href="<?= UrlHelper::baseUrl('/admin/orders') ?>" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
             <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
             Kembali ke Daftar Pesanan
         </a>
         <div>
            <a href="<?= UrlHelper::baseUrl('/admin/orders/invoice/' . $order['id']) ?>" target="_blank" class="inline-flex items-center bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-sm font-medium py-1.5 px-3 rounded-md transition shadow-sm mr-2">
                 <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.23a1.125 1.125 0 01-1.12-1.227L6.34 18m11.318 0h1.061a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v8.5a2.25 2.25 0 002.25 2.25h1.06" /></svg>
                Cetak Invoice
            </a>
            </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-slate-200">
        <div class="p-5 border-b border-slate-200 bg-slate-50">
             <div class="flex flex-col sm:flex-row justify-between sm:items-center">
                 <div>
                     <h2 class="text-xl font-semibold text-slate-800">Pesanan #<?= SanitizeHelper::html($order['order_number']) ?></h2>
                     <p class="text-sm text-slate-500 mt-1">
                         Meja: <span class="font-medium"><?= SanitizeHelper::html($order['table_number']) ?></span> |
                         Waktu: <span class="font-medium"><?= DateHelper::formatIndonesian($order['order_time'], 'full') ?></span>
                     </p>
                 </div>
                 <div class="mt-3 sm:mt-0 text-center sm:text-right">
                     <span class="inline-block text-sm font-semibold px-3 py-1 rounded-full <?= $statusClasses[$order['status']] ?? $statusClasses['pending'] ?>">
                         <?= SanitizeHelper::html($statusTexts[$order['status']] ?? ucfirst($order['status'])) ?>
                     </span>
                 </div>
             </div>
             <?php if (!empty($order['notes'])): ?>
                 <div class="mt-3 text-sm bg-yellow-50 border border-yellow-200 rounded-md p-3">
                     <p><strong class="font-medium text-yellow-800">Catatan Pelanggan:</strong> <?= SanitizeHelper::html($order['notes']) ?></p>
                 </div>
             <?php endif; ?>
        </div>

        <div class="p-5">
            <h3 class="text-lg font-semibold text-slate-700 mb-3">Item Dipesan</h3>
             <div class="flow-root">
                 <table class="min-w-full divide-y divide-slate-200 border border-slate-100 rounded-md">
                    <thead class="bg-slate-50">
                        <tr>
                             <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Qty</th>
                             <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Item</th>
                             <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Harga/Unit</th>
                             <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                     <tbody class="bg-white divide-y divide-slate-100">
                        <?php if (!empty($order['items'])): ?>
                            <?php foreach ($order['items'] as $item): ?>
                                 <tr>
                                     <td class="px-4 py-2 whitespace-nowrap text-sm text-slate-700"><?= SanitizeHelper::html($item['quantity']) ?>x</td>
                                     <td class="px-4 py-2 text-sm text-slate-800 font-medium">
                                         <?= SanitizeHelper::html($item['menu_item_name']) ?>
                                          <?php if (!empty($item['notes'])): ?>
                                             <p class="text-xs text-amber-700 italic mt-0.5">Catatan: <?= SanitizeHelper::html($item['notes']) ?></p>
                                         <?php endif; ?>
                                     </td>
                                     <td class="px-4 py-2 whitespace-nowrap text-sm text-slate-600 text-right"><?= NumberHelper::formatCurrencyIDR($item['price_at_order']) ?></td>
                                     <td class="px-4 py-2 whitespace-nowrap text-sm text-slate-800 text-right font-medium"><?= NumberHelper::formatCurrencyIDR($item['subtotal']) ?></td>
                                 </tr>
                             <?php endforeach; ?>
                         <?php else: ?>
                             <tr><td colspan="4" class="text-center py-4 text-sm text-slate-500">Tidak ada item.</td></tr>
                         <?php endif; ?>
                    </tbody>
                     <tfoot class="bg-slate-50">
                        <tr>
                             <td colspan="3" class="px-4 py-2 text-right text-sm font-semibold text-slate-700">Total Pesanan:</td>
                             <td class="px-4 py-2 text-right text-sm font-bold text-indigo-700"><?= NumberHelper::formatCurrencyIDR($order['total_amount']) ?></td>
                        </tr>
                     </tfoot>
                 </table>
             </div>
        </div>

         <div class="p-5 border-t border-slate-200">
             <h3 class="text-lg font-semibold text-slate-700 mb-3">Informasi Pembayaran</h3>
             <?php if ($payment): ?>
                 <div class="text-sm space-y-1">
                     <p><strong class="font-medium w-28 inline-block">Status:</strong> <span class="text-green-600 font-semibold">LUNAS</span></p>
                     <p><strong class="font-medium w-28 inline-block">Metode:</strong> <?= SanitizeHelper::html(ucfirst($payment['payment_method'])) ?></p>
                     <p><strong class="font-medium w-28 inline-block">Jumlah:</strong> <?= NumberHelper::formatCurrencyIDR($payment['amount_paid']) ?></p>
                     <p><strong class="font-medium w-28 inline-block">Waktu Bayar:</strong> <?= DateHelper::formatIndonesian($payment['payment_time'], 'full') ?></p>
                     <p><strong class="font-medium w-28 inline-block">Diproses Oleh:</strong> <?= SanitizeHelper::html($payment['processed_by_user_name'] ?? 'N/A') ?></p>
                 </div>
             <?php elseif($order['status'] === 'cancelled'): ?>
                  <p class="text-sm text-red-600">Pesanan ini dibatalkan.</p>
             <?php else: ?>
                 <p class="text-sm text-orange-600 mb-3">Pesanan ini belum dibayar.</p>
                 <?php if (AuthHelper::getUserRole() !== 'kitchen'): // Dapur tidak bisa proses bayar ?>
                 <form action="<?= UrlHelper::baseUrl('/admin/orders/pay_cash/' . $order['id']) ?>" method="POST" class="inline-block delete-confirm-form" data-confirm-message="Tandai pesanan #<?= SanitizeHelper::html($order['order_number']) ?> sebagai LUNAS (Cash)?">
                      <button type="submit" class="bg-green-500 hover:bg-green-600 text-white text-sm font-medium py-1.5 px-3 rounded-md transition shadow-sm">
                         Tandai Lunas (Cash)
                     </button>
                 </form>
                 <?php endif; ?>
             <?php endif; ?>
        </div>

         <?php if (!$isPaidOrCancelled && AuthHelper::getUserRole() !== 'kitchen'): ?>
         <div class="p-5 border-t border-slate-200 bg-slate-50">
              <h3 class="text-md font-semibold text-slate-700 mb-2">Ubah Status Pesanan:</h3>
              <div class="flex flex-wrap gap-2">
                  <?php
                  // Status yang bisa di set dari sini (sesuaikan kebutuhan)
                  $possibleNextStatuses = ['preparing', 'ready', 'served', 'cancelled'];
                  ?>
                  <?php foreach($possibleNextStatuses as $nextStatus): ?>
                      <?php if ($order['status'] !== $nextStatus): // Jangan tampilkan tombol untuk status saat ini ?>
                            <button class="change-status-btn bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 text-xs font-medium py-1 px-2.5 rounded-md transition shadow-sm"
                                    data-order-id="<?= $order['id'] ?>" data-new-status="<?= $nextStatus ?>">
                                Set ke <?= SanitizeHelper::html($statusTexts[$nextStatus] ?? ucfirst($nextStatus)) ?>
                            </button>
                      <?php endif; ?>
                  <?php endforeach; ?>
              </div>
              <div id="update-status-message" class="mt-2 text-xs"></div> </div>
         <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.change-status-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
            const btn = e.currentTarget;
            const orderId = btn.dataset.orderId;
            const newStatus = btn.dataset.newStatus;
            const messageEl = document.getElementById('update-status-message');

            if (!confirm(`Ubah status pesanan ke "${newStatus}"?`)) return;

            btn.disabled = true;
            btn.textContent = '...';
            if(messageEl) messageEl.textContent = '';

            const baseUrl = window.APP_BASE_URL || '';
            try {
                const response = await fetch(`${baseUrl}/admin/orders/update_status`, {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                     body: JSON.stringify({ order_id: parseInt(orderId), new_status: newStatus })
                 });
                const result = await response.json();

                if (response.ok && result.success) {
                     // Sukses: Reload halaman untuk lihat status terbaru (paling mudah)
                     if(messageEl) messageEl.textContent = 'Status berhasil diubah. Memuat ulang...';
                     messageEl.className = 'mt-2 text-xs text-green-600';
                     setTimeout(() => window.location.reload(), 1000);
                 } else {
                     throw new Error(result.message || 'Gagal update status.');
                 }
            } catch (error) {
                 console.error('Error updating status:', error);
                 if(messageEl) messageEl.textContent = `Error: ${error.message}`;
                 messageEl.className = 'mt-2 text-xs text-red-600';
                 btn.disabled = false;
                 // Kembalikan teks tombol? Perlu simpan teks asli
                 btn.textContent = `Set ke ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}`;
            }
        });
    });
});
</script>


<?php else: ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
        Pesanan tidak ditemukan.
        <a href="<?= UrlHelper::baseUrl('/admin/orders') ?>" class="ml-2 font-medium underline">Kembali ke daftar</a>
    </div>
<?php endif; ?>