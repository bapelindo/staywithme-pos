<?php
// File: app/Views/public/order_status.php (REDESIGN - FINAL)
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;
use App\Helpers\DateHelper;

// Data dari controller sudah di-load ke $order
/** @var array|null $order */
$order = $order ?? null;
$pageTitle = "Status Pesanan";
if ($order) {
    $pageTitle .= " #" . SanitizeHelper::html($order['order_number']);
}

// Status Definition & Mapping
$statusMap = [
    'pending' => ['index' => 0, 'text' => 'Menunggu', 'icon' => 'fas fa-hourglass-half'],
    'received' => ['index' => 1, 'text' => 'Diterima', 'icon' => 'fas fa-receipt'],
    'preparing' => ['index' => 2, 'text' => 'Disiapkan', 'icon' => 'fas fa-utensils'],
    'ready' => ['index' => 3, 'text' => 'Siap', 'icon' => 'fas fa-bell'],
    // Final statuses - can be one of these
    'served' => ['index' => 4, 'text' => 'Disajikan', 'icon' => 'fas fa-check-circle'],
    'paid' => ['index' => 4, 'text' => 'Lunas', 'icon' => 'fas fa-check-double'],
    // Error/Cancelled status
    'cancelled' => ['index' => -1, 'text' => 'Dibatalkan', 'icon' => 'fas fa-times-circle'],
];

$currentStatusKey = $order['status'] ?? 'pending'; // Default if null
$currentStatusInfo = $statusMap[$currentStatusKey] ?? ['index' => -2, 'text' => 'Tidak Diketahui', 'icon' => 'fas fa-question-circle'];
$currentStatusIndex = $currentStatusInfo['index'];

// === Definisikan Langkah-langkah Stepper (FIXED LOGIC) ===
// Definisikan langkah-langkah dasar yang selalu ada dalam urutan
$orderSteps = [
    $statusMap['pending'],      // index 0
    $statusMap['received'],     // index 1
    $statusMap['preparing'],    // index 2
    $statusMap['ready'],        // index 3
    // Tetapkan tampilan default untuk langkah akhir (misalnya 'Disajikan')
    $statusMap['served']       // index 4
];

// Jika status aktual adalah 'paid', ganti tampilan langkah akhir menjadi 'Lunas'
// Ini hanya mempengaruhi teks/ikon default yang ditampilkan, BUKAN progres aktual
if ($currentStatusKey === 'paid') {
    $orderSteps[4] = $statusMap['paid'];
}
// === AKHIR DEFINISI STEPPER ===

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

            <div class="p-6 sm:p-8" id="status-stepper-container">
                <h2 class="sr-only">Progres Pesanan</h2>

                <?php if ($isCancelled): ?>
                     <div class="text-center p-4 bg-red-900/30 border border-red-700 rounded-lg">
                         <i class="<?= $statusMap['cancelled']['icon'] ?> text-red-400 text-4xl mb-3"></i>
                         <p class="text-xl font-semibold text-red-300"><?= $statusMap['cancelled']['text'] ?></p>
                         <p class="text-sm text-red-400 mt-1">Pesanan ini telah dibatalkan.</p>
                    </div>
                <?php else: ?>
                    <ol class="flex items-center w-full space-x-2 sm:space-x-4 text-xs sm:text-sm font-medium text-center text-text-dark-secondary relative">
                        <?php foreach ($orderSteps as $index => $step): ?>
                            <?php
                                // Tentukan state berdasarkan index status aktual ($currentStatusIndex)
                                $isCompleted = $index < $currentStatusIndex;
                                // Status 'paid' atau 'served' (keduanya index 4) dianggap aktif/selesai jika $currentStatusIndex >= 4
                                $isActive = ($index === $currentStatusIndex) && ($currentStatusIndex < 4); // Aktif hanya jika BUKAN final state
                                $isFinalReached = $currentStatusIndex >= 4; // Apakah sudah mencapai state akhir (served/paid)?
                                $isCurrentStepFinal = $index === 4; // Apakah ini langkah terakhir (index 4)?

                                // Jika sudah mencapai state akhir, semua langkah dianggap selesai
                                if ($isFinalReached) {
                                    $isCompleted = true;
                                    $isActive = false;
                                }

                                // Tentukan class CSS berdasarkan state
                                $stepClass = '';
                                $iconClass = 'text-gray-500'; // Default: upcoming
                                $textClass = 'text-gray-500'; // Default: upcoming
                                $bgClass = 'bg-gray-800/30';   // Default: upcoming
                                $lineAfterClass = 'bg-gray-700'; // Default line after: upcoming

                                if ($isActive) {
                                    $stepClass = 'active-step';
                                    $iconClass = 'text-accent-primary animate-pulse';
                                    $textClass = 'text-accent-primary';
                                    $bgClass = 'bg-accent-primary/10';
                                } elseif ($isCompleted) {
                                    $stepClass = 'completed-step';
                                    $iconClass = 'text-green-400';
                                    $textClass = 'text-green-400';
                                    $bgClass = 'bg-green-800/30';
                                    $lineAfterClass = 'bg-green-500'; // Garis setelah step selesai berwarna hijau
                                }
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
                <?php endif; ?>
                 <p id="status-last-updated" class="text-xs text-center text-text-dark-muted mt-6"></p>
            </div>

            <div class="px-6 pb-6 border-t border-border-dark">
                <h2 class="text-xl font-semibold my-4 text-white">Detail Pesanan</h2>
                <div class="space-y-3">
                    <?php if (!empty($order['items'])): ?>
                        <?php foreach ($order['items'] as $item): ?>
                            <?php
                                $itemImage = !empty($item['image_path'])
                                           ? UrlHelper::baseUrl(SanitizeHelper::html($item['image_path']))
                                           : UrlHelper::baseUrl('images/menu-placeholder.jpg'); // Fallback placeholder
                            ?>
                            <div class="flex items-start space-x-4 bg-bg-dark p-3 rounded-lg border border-border-dark/50">
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

            <div class="px-6 py-4 bg-bg-dark border-t border-border-dark">
                 <dl class="space-y-1">
                     <div class="flex justify-between text-lg font-bold text-white">
                         <dt>Total</dt>
                         <dd><?= NumberHelper::formatCurrencyIDR($order['total_amount']) ?></dd>
                     </div>
                 </dl>
            </div>

            <div class="px-6 py-5 bg-bg-dark-secondary border-t border-border-dark">
                 <p class="text-center text-sm text-text-dark-secondary mb-4">
                    <?php if($currentStatusKey == 'paid'): ?>
                        Pesanan ini sudah lunas. Terima kasih telah berkunjung!
                    <?php elseif($currentStatusKey == 'served' || $currentStatusKey == 'ready'): ?>
                        Pesanan Anda telah <?php echo ($currentStatusKey == 'served') ? 'disajikan' : 'siap'; ?>. Harap lakukan pembayaran di kasir.
                    <?php elseif(!$isCancelled): ?>
                        Status akan diperbarui secara otomatis. Anda juga bisa menyegarkan secara manual.
                    <?php else: // Cancelled ?>
                        Jika ada pertanyaan mengenai pembatalan, silakan hubungi staf kami.
                    <?php endif; ?>
                </p>
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

        </div> <input type="hidden" id="order-id" value="<?= SanitizeHelper::html($order['id']) ?>">
        <input type="hidden" id="current-status-key" value="<?= SanitizeHelper::html($currentStatusKey) ?>">


    <?php else: ?>
         <div class="bg-red-900/30 border border-red-700 text-red-300 px-6 py-8 rounded-lg relative text-center max-w-md mx-auto">
            <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
            <h1 class="text-xl font-bold block mb-2">Pesanan Tidak Ditemukan!</h1>
            <p class="block sm:inline text-sm text-red-300/90">Pastikan Anda memasukkan link yang benar atau ID pesanan valid.</p>
             <p class="mt-6">
                 <a href="<?= UrlHelper::baseUrl('/') ?>" class="btn btn-secondary inline-flex items-center">
                     <i class="fas fa-home mr-2"></i> Kembali ke Beranda
                 </a>
             </p>
        </div>
    <?php endif; ?>
</div>

<script>
    // Pass base URL for JS if needed (pastikan ini didefinisikan di layout atau config)
    window.APP_BASE_URL = '<?= rtrim(UrlHelper::baseUrl(), '/') ?>';
</script>
<script src="<?= UrlHelper::baseUrl('js/customer-status.js') ?>" defer></script>