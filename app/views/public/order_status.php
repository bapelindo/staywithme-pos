<?php
// File: app/Views/public/order_status.php (Lengkap & Final)
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;
use App\Helpers\DateHelper;

/** @var array|null $order */
$order = $order ?? null;
$pageTitle = "Status Pesanan";
if ($order) {
    $pageTitle .= " #" . SanitizeHelper::html($order['order_number']);
}

// Status Map Lengkap (tanpa 'paid' dalam alur utama)
$statusMap = [
    'pending_payment' => ['index' => 0, 'text' => 'Pembayaran', 'icon' => 'fas fa-cash-register'],
    'received' => ['index' => 1, 'text' => 'Diterima', 'icon' => 'fas fa-receipt'],
    'preparing' => ['index' => 2, 'text' => 'Disiapkan', 'icon' => 'fas fa-utensils'],
    'ready' => ['index' => 3, 'text' => 'Siap', 'icon' => 'fas fa-bell'],
    'served' => ['index' => 4, 'text' => 'Disajikan', 'icon' => 'fas fa-check-circle'], // Status akhir operasional
    'cancelled' => ['index' => -1, 'text' => 'Dibatalkan', 'icon' => 'fas fa-times-circle'],
];

$currentStatusKey = $order['status'] ?? 'pending_payment';
// Anggap 'paid' (jika masih ada di DB) sebagai 'served' untuk tampilan
if ($currentStatusKey === 'paid') {
    $currentStatusKey = 'served';
}

$currentStatusInfo = $statusMap[$currentStatusKey] ?? ['index' => -2, 'text' => 'Tidak Diketahui', 'icon' => 'fas fa-question-circle'];
$currentStatusIndex = $currentStatusInfo['index'];

// Definisikan langkah-langkah stepper
$orderSteps = [
    $statusMap['pending_payment'],
    $statusMap['received'],
    $statusMap['preparing'],
    $statusMap['ready'],
    $statusMap['served'] // Langkah akhir selalu 'served' di definisi dasar
];
// Tidak perlu lagi cek $currentStatusKey === 'paid' untuk ganti langkah akhir

$isCancelled = ($currentStatusKey === 'cancelled');

?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">

    <?php if ($order): ?>
        <h1 class="text-3xl md:text-4xl font-bold text-center mb-10 text-white">
            Status Pesanan <span class="text-accent-primary">#<?= SanitizeHelper::html($order['order_number']) ?></span>
        </h1>

        <div class="max-w-4xl mx-auto bg-bg-dark-secondary rounded-xl shadow-xl border border-border-dark overflow-hidden">

            <div class="p-5 sm:p-6 border-b border-border-dark flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <p class="text-sm text-text-dark-secondary mb-1">Nomor Meja</p>
                    <p class="text-xl font-semibold text-white"><?= SanitizeHelper::html($order['table_number'] ?? 'N/A') ?></p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-sm text-text-dark-secondary mb-1">Waktu Pesanan</p>
                    <p class="text-base font-medium text-text-dark-primary">
                        <?= DateHelper::formatIndonesian($order['order_time']) ?>
                    </p>
                </div>
            </div>

            <?php // Container Stepper selalu dirender kecuali batal ?>
            <div id="status-stepper-container" class="p-6 sm:p-8 border-b border-border-dark">
                <?php // --- PERUBAHAN DISINI --- ?>
                <?php // 1. Selalu render struktur OL Stepper (tapi bisa disembunyikan JS) ?>
                 <ol class="stepper-list flex items-center w-full space-x-2 sm:space-x-4 text-xs sm:text-sm font-medium text-center text-text-dark-secondary relative"
                     style="<?= $isCancelled ? 'display: none;' : 'display: flex;' // Sembunyikan OL jika awalnya Batal ?>">
                     <?php foreach ($orderSteps as $index => $step): ?>
                         <?php
                             // ... (Logika styling $isCompleted, $isActive, dll. tetap sama) ...
                                $isCompleted = $index < $currentStatusIndex;
                                $isActive = ($index === $currentStatusIndex) && ($currentStatusIndex < 4);
                                $isFinalReached = $currentStatusIndex >= 4;

                                if ($isFinalReached) { $isCompleted = $index <= 4; $isActive = false; }
                                if ($currentStatusKey === 'pending_payment' && $index === 0) { $isActive = true; $isCompleted = false; }
                                elseif ($currentStatusKey !== 'pending_payment' && $index === 0) { $isCompleted = true; $isActive = false; }

                                $iconClass = 'text-gray-500'; $textClass = 'text-gray-500'; $bgClass = 'bg-gray-800/30'; $lineAfterClass = 'bg-gray-700';
                                if ($isActive) { $iconClass = 'text-accent-primary animate-pulse'; $textClass = 'text-accent-primary'; $bgClass = 'bg-accent-primary/10'; }
                                elseif ($isCompleted) { $iconClass = 'text-green-400'; $textClass = 'text-green-400'; $bgClass = 'bg-green-800/30'; $lineAfterClass = 'bg-green-500'; }
                         ?>
                         <li class="step-item flex flex-col items-center flex-1 relative" data-step-index="<?= $index ?>">
                             <span class="step-icon flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full mb-2 transition-colors duration-500 <?= $bgClass ?>">
                                 <i class="<?= $step['icon'] ?> text-lg sm:text-xl <?= $iconClass ?>"></i>
                             </span>
                             <span class="step-text leading-tight <?= $textClass ?>"><?= SanitizeHelper::html($step['text']) ?></span>
                             <?php if ($index < count($orderSteps) - 1): ?>
                             <div aria-hidden="true" class="step-line-after absolute top-[18px] sm:top-[22px] left-1/2 w-full -translate-y-1/2 -z-10">
                                 <div class="w-[calc(100%+1rem)] sm:w-[calc(100%+1.5rem)] h-0.5 transition-colors duration-500 <?= $lineAfterClass ?> -translate-x-[calc(50%-0.5rem)] sm:-translate-x-[calc(50%-0.75rem)]"></div>
                             </div>
                             <?php endif; ?>
                         </li>
                     <?php endforeach; ?>
                 </ol>

                 <?php // 2. Selalu render div pesan batal (tapi sembunyikan jika awalnya tidak Batal) ?>
                 <div class="cancelled-message text-center p-4 bg-red-900/30 border border-red-700 rounded-lg"
                      style="<?= $isCancelled ? 'display: block;' : 'display: none;' // Tampilkan hanya jika awalnya Batal ?>">
                     <i class="<?= $statusMap['cancelled']['icon'] ?> text-red-400 text-4xl mb-3"></i>
                     <p class="text-xl font-semibold text-red-300"><?= $statusMap['cancelled']['text'] ?></p>
                     <p class="text-sm text-red-400 mt-1">Pesanan ini telah dibatalkan.</p>
                 </div>
                <p id="status-last-updated" class="text-xs text-center text-text-dark-muted mt-6"></p>
            </div>

            <?php // Detail Item (tidak berubah) ?>
            <div class="px-6 pb-6 border-t border-border-dark">
                 <h2 class="text-xl font-semibold my-4 text-white">Detail Pesanan</h2>
                 <div class="space-y-3">
                     <?php if (!empty($order['items'])): ?>
                         <?php foreach ($order['items'] as $item): ?>
                              <div class="flex items-start space-x-4 bg-bg-dark p-3 rounded-lg border border-border-dark/50">
                                 <?php
                                     $itemImage = !empty($item['image_path'])
                                                ? UrlHelper::baseUrl(SanitizeHelper::html($item['image_path']))
                                                : UrlHelper::baseUrl('images/menu-placeholder.jpg');
                                 ?>
                                 <img src="<?= $itemImage ?>" alt="<?= SanitizeHelper::html($item['menu_item_name']) ?>" class="w-14 h-14 sm:w-16 sm:h-16 object-cover rounded-md flex-shrink-0" onerror="this.onerror=null; this.src='<?= UrlHelper::baseUrl('images/menu-placeholder.jpg') ?>';">
                                 <div class="flex-1 min-w-0">
                                     <p class="text-sm sm:text-base font-medium text-white mb-0.5">
                                         <?= SanitizeHelper::html($item['menu_item_name']) ?>
                                     </p>
                                     <p class="text-sm text-text-dark-secondary mb-1">
                                         <?= SanitizeHelper::html($item['quantity']) ?> x <?= NumberHelper::formatCurrencyIDR($item['price_at_order']) ?>
                                     </p>
                                     <?php if (!empty($item['notes'])): ?>
                                         <p class="text-xs text-amber-400/80 italic bg-amber-900/30 px-2 py-0.5 rounded inline-block">
                                             <i class="fas fa-sticky-note mr-1 opacity-70"></i> <?= SanitizeHelper::html($item['notes']) ?>
                                         </p>
                                     <?php endif; ?>
                                 </div>
                                 <div class="text-sm sm:text-base font-semibold text-accent-primary text-right pl-2">
                                     <?= NumberHelper::formatCurrencyIDR($item['subtotal']) ?>
                                 </div>
                              </div>
                         <?php endforeach; ?>
                     <?php else: ?>
                         <p class="text-text-dark-secondary text-sm italic py-4 text-center">Tidak ada item dalam pesanan ini.</p>
                     <?php endif; ?>
                 </div>
            </div>

            <?php // Total (tidak berubah) ?>
            <div class="px-6 py-4 bg-bg-dark border-t border-border-dark">
                 <dl class="space-y-1">
                      <div class="flex justify-between text-lg font-bold text-white">
                          <dt>Total</dt>
                          <dd><?= NumberHelper::formatCurrencyIDR($order['total_amount']) ?></dd>
                      </div>
                 </dl>
            </div>

            <?php // Bagian Bawah: Teks Status & Tombol ?>
            <div class="px-6 py-5 bg-bg-dark-secondary border-t border-border-dark">
                 <p id="order-status-text" class="text-center text-sm text-text-dark-secondary mb-4">
                     <?php // Teks Awal diatur di sini, akan diupdate JS ?>
                     <?php if($currentStatusKey == 'pending_payment'): ?>
                         Silakan lakukan pembayaran tunai di kasir dengan menunjukkan nomor pesanan #<?= SanitizeHelper::html($order['order_number']) ?>.
                     <?php elseif($currentStatusKey == 'served'): ?>
                         Pesanan Anda telah disajikan. Terima kasih!
                     <?php elseif($currentStatusKey == 'ready'): ?>
                          Pesanan Anda sudah siap. Silakan konfirmasi ke kasir.
                     <?php elseif($currentStatusKey == 'cancelled'): ?>
                          Pesanan ini telah dibatalkan.
                     <?php else: // received, preparing ?>
                         Status akan diperbarui secara otomatis.
                     <?php endif; ?>
                 </p>
                 <?php // Tombol (tidak berubah) ?>
                <div class="flex flex-col sm:flex-row gap-3">
                     <button id="refresh-status-btn" type="button" class="btn btn-secondary flex-1 inline-flex items-center justify-center">
                        <i class="fas fa-sync-alt mr-2"></i> Segarkan Status
                    </button>
                    <a href="<?= UrlHelper::baseUrl('/menu/table/' . SanitizeHelper::html($order['qr_code_identifier'] ?? '')) ?>" class="btn btn-accent flex-1 inline-flex items-center justify-center">
                        <i class="fas fa-book-open mr-2"></i> Kembali ke Menu
                    </a>
                </div>
                 <p id="polling-indicator" class="text-xs text-center text-text-dark-muted mt-4 hidden"><i class="fas fa-spinner fa-spin mr-1"></i> Memeriksa status...</p>
            </div>

        </div>
        <?php // Input Hidden (termasuk nomor order lengkap) ?>
        <input type="hidden" id="order-id" value="<?= SanitizeHelper::html($order['id']) ?>">
        <input type="hidden" id="current-status-key" value="<?= SanitizeHelper::html($currentStatusKey) ?>">
        <input type="hidden" id="order-number-full" value="<?= SanitizeHelper::html($order['order_number'] ?? '') ?>">

    <?php else: ?>
         <?php // Pesan Error jika Order Tidak Ditemukan ?>
         <div class="bg-red-900/30 border border-red-700 text-red-300 px-6 py-8 rounded-lg relative text-center max-w-md mx-auto">
             <?php // ... pesan error ... ?>
         </div>
    <?php endif; ?>
</div>

<?php // Script Loader ?>
<script> window.APP_BASE_URL = '<?= rtrim(UrlHelper::baseUrl(), '/') ?>'; </script>
<script src="<?= UrlHelper::baseUrl('js/customer-status.js') ?>" defer></script>