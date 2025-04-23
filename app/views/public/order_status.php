<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;
use App\Helpers\DateHelper;

$order = $order ?? null;
$pageTitle = "Status Pesanan";
if ($order) {
    $pageTitle .= " #" . SanitizeHelper::html($order['order_number']);
}

// Fungsi helper lokal untuk kelas badge status
function getStatusInfo(string $status): array {
    return match ($status) {
        'pending' => ['text' => 'Menunggu Konfirmasi', 'class' => 'bg-gray-100 text-gray-600', 'icon' => '...'], // Ganti icon jika perlu
        'received' => ['text' => 'Pesanan Diterima', 'class' => 'bg-blue-100 text-blue-800', 'icon' => '✓'],
        'preparing' => ['text' => 'Sedang Disiapkan', 'class' => 'bg-yellow-100 text-yellow-800 animate-pulse', 'icon' => '🍳'],
        'ready' => ['text' => 'Siap Diambil/Diantar', 'class' => 'bg-teal-100 text-teal-800', 'icon' => '🔔'],
        'served' => ['text' => 'Telah Disajikan', 'class' => 'bg-green-100 text-green-800', 'icon' => '👍'],
        'paid' => ['text' => 'Lunas Dibayar', 'class' => 'bg-indigo-100 text-indigo-800', 'icon' => '💰'],
        'cancelled' => ['text' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-800', 'icon' => '❌'],
        default => ['text' => 'Tidak Diketahui', 'class' => 'bg-gray-100 text-gray-800', 'icon' => '?'],
    };
}

$statusInfo = $order ? getStatusInfo($order['status']) : getStatusInfo('unknown');
?>

<div class="max-w-3xl mx-auto px-4 py-6">
    <?php if ($order): ?>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100">
                <h1 class="text-2xl font-bold text-slate-800 mb-1">Status Pesanan Anda</h1>
                <p class="text-indigo-800 font-semibold text-lg">#<?= SanitizeHelper::html($order['order_number']) ?></p>
                <p class="text-sm text-slate-500 mt-1">
                    Meja: <?= SanitizeHelper::html($order['table_number']) ?> | Waktu: <?= DateHelper::formatIndonesian($order['order_time'], 'short') ?>
                </p>
            </div>

            <div class="p-6">
                <div class="mb-6 text-center">
                    <p class="text-sm text-slate-500 mb-2">Status Saat Ini:</p>
                    <div id="order-status-container" class="inline-block">
                        <span id="order-status" class="text-lg md:text-xl font-bold px-5 py-2 rounded-full shadow-sm transition-colors duration-300 <?= $statusInfo['class'] ?>">
                             <span class="mr-1"><?= $statusInfo['icon'] ?></span> <?= SanitizeHelper::html($statusInfo['text']) ?>
                        </span>
                    </div>
                     <p id="status-last-updated" class="text-xs text-slate-400 mt-2"></p> </div>

                <div class="mb-6 border-t border-slate-200 pt-5">
                    <h2 class="text-lg font-semibold text-slate-700 mb-3">Ringkasan Pesanan:</h2>
                    <div class="flow-root">
                        <ul class="-my-4 divide-y divide-slate-100">
                            <?php if (!empty($order['items'])): ?>
                                <?php foreach ($order['items'] as $item): ?>
                                    <li class="py-4 flex items-center space-x-4">
                                         <?php
                                            $itemImage = UrlHelper::asset(SanitizeHelper::html($item['image_path'] ?? 'images/default_menu.png'));
                                         ?>
                                        <img src="<?= $itemImage ?>" alt="" class="w-12 h-12 rounded object-cover flex-shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-900 truncate">
                                                <?= SanitizeHelper::html($item['quantity']) ?>x <?= SanitizeHelper::html($item['menu_item_name']) ?>
                                            </p>
                                             <p class="text-xs text-slate-500"><?= NumberHelper::formatCurrencyIDR($item['price_at_order']) ?></p>
                                             <?php if (!empty($item['notes'])): ?>
                                                <p class="text-xs text-amber-700 italic mt-0.5">Catatan: <?= SanitizeHelper::html($item['notes']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="inline-flex text-sm font-semibold text-slate-900">
                                             <?= NumberHelper::formatCurrencyIDR($item['subtotal']) ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-slate-500 text-sm py-4">Tidak ada item dalam pesanan ini.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-5 mt-5">
                     <dl class="space-y-2">
                         <div class="flex justify-between text-lg font-bold">
                             <dt>Total</dt>
                             <dd><?= NumberHelper::formatCurrencyIDR($order['total_amount']) ?></dd>
                         </div>
                     </dl>
                      <p class="text-center text-sm text-slate-500 mt-6">
                          <?php if($order['status'] == 'paid'): ?>
                              Pesanan ini sudah lunas. Terima kasih!
                          <?php elseif($order['status'] != 'cancelled'): ?>
                               Harap lakukan pembayaran di kasir kami. Terima kasih!
                          <?php else: ?>
                              Pesanan ini telah dibatalkan.
                          <?php endif; ?>
                      </p>
                 </div>
            </div>

        </div>

        <input type="hidden" id="order-id" value="<?= SanitizeHelper::html($order['id']) ?>">

    <?php else: ?>
         <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg relative text-center" role="alert">
            <strong class="font-bold block mb-2">Pesanan Tidak Ditemukan!</strong>
            <span class="block sm:inline"> Pastikan Anda memasukkan link yang benar atau ID pesanan valid.</span>
             <p class="mt-4"><a href="<?= UrlHelper::baseUrl('/') ?>" class="text-sm font-medium text-red-800 hover:underline">Kembali ke Beranda</a></p>
        </div>
    <?php endif; ?>
</div>

<script src="<?= App\Helpers\UrlHelper::asset('js/customer-status.js') ?>" defer></script>