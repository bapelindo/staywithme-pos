<?php
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

// Status Map Lengkap
$statusMap = [
    'pending_payment' => ['index' => 0, 'text' => 'Pembayaran', 'icon' => 'fas fa-cash-register'],
    'received' => ['index' => 1, 'text' => 'Diterima', 'icon' => 'fas fa-receipt'],
    'preparing' => ['index' => 2, 'text' => 'Disiapkan', 'icon' => 'fas fa-utensils'],
    'ready' => ['index' => 3, 'text' => 'Siap', 'icon' => 'fas fa-bell'],
    'served' => ['index' => 4, 'text' => 'Disajikan', 'icon' => 'fas fa-check-circle'],
    'cancelled' => ['index' => -1, 'text' => 'Dibatalkan', 'icon' => 'fas fa-times-circle'],
];

$currentStatusKey = $order['status'] ?? 'pending_payment';
if ($currentStatusKey === 'paid') {
    $currentStatusKey = 'served';
}

$currentStatusInfo = $statusMap[$currentStatusKey] ?? ['index' => -2, 'text' => 'Tidak Diketahui', 'icon' => 'fas fa-question-circle'];
$currentStatusIndex = $currentStatusInfo['index'];

$orderSteps = [
    $statusMap['pending_payment'],
    $statusMap['received'],
    $statusMap['preparing'],
    $statusMap['ready'],
    $statusMap['served']
];

$isCancelled = ($currentStatusKey === 'cancelled');

?>

<!-- Hero Section / Status Wrapper -->
<section class="relative min-h-[100svh] flex flex-col items-center justify-center pt-24 pb-12 px-6 overflow-hidden">
    <!-- Background Image with Parallax feel -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-b from-[#050505]/95 via-[#050505]/90 to-[#050505] z-10"></div>
        <img alt="Interior Cafe" class="w-full h-full object-cover opacity-30 scale-105 animate-pulse-slow"
            src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>">
    </div>

    <!-- Huge Background Typography -->
    <div class="fixed inset-0 z-0 flex items-center justify-center pointer-events-none overflow-hidden select-none">
        <h1
            class="text-[5rem] sm:text-[8rem] md:text-[14rem] font-display font-black text-outline whitespace-nowrap opacity-20 transform -translate-y-12 mix-blend-overlay">
            STATUS
        </h1>
    </div>

    <div class="relative z-20 w-full max-w-4xl mx-auto flex flex-col mt-4">

        <?php if ($order): ?>
            <div class="text-center mb-12 reveal-up">
                <span
                    class="font-sans text-xs text-primary uppercase tracking-[0.4em] mb-6 flex items-center justify-center gap-4">
                    <span class="w-8 h-[1px] bg-primary/50"></span>
                    Pesanan Anda
                    <span class="w-8 h-[1px] bg-primary/50"></span>
                </span>
                <h1
                    class="font-display text-4xl md:text-6xl lg:text-7xl font-bold text-white leading-[0.9] tracking-tighter mb-4 mix-blend-screen drop-shadow-2xl">
                    <i class="text-primary font-light">Status</i> Pesanan.
                </h1>
                <p class="font-sans text-xl md:text-2xl text-on-surface-variant font-light tracking-widest">
                    #<?= SanitizeHelper::html($order['order_number']) ?>
                </p>
            </div>

            <!-- Main Card -->
            <div
                class="glass-panel rounded-sm shadow-glass border border-white/10 overflow-hidden reveal-up reveal-delay-1 backdrop-blur-2xl">

                <!-- Header Info -->
                <div
                    class="p-6 md:p-8 border-b border-white/10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 bg-white/[0.02]">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full border border-primary/30 flex items-center justify-center bg-primary/10 text-primary">
                            <span class="material-symbols-outlined">table_restaurant</span>
                        </div>
                        <div>
                            <p class="font-sans text-[10px] text-primary uppercase tracking-[0.2em] mb-1">Nomor Meja</p>
                            <p class="font-display text-2xl sm:text-3xl text-white">
                                <?= SanitizeHelper::html($order['table_number'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="font-sans text-[10px] uppercase tracking-[0.2em] text-on-surface-variant mb-1">Waktu
                            Pesanan</p>
                        <p class="font-sans text-sm font-light text-white">
                            <?= DateHelper::formatIndonesian($order['order_time']) ?>
                        </p>
                    </div>
                </div>

                <!-- Stepper Container -->
                <div id="status-stepper-container"
                    class="py-8 px-2 sm:px-8 md:p-12 border-b border-white/10 relative overflow-hidden">
                    <!-- Subtle Glow -->
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3/4 h-3/4 bg-primary/10 blur-[100px] rounded-full pointer-events-none">
                    </div>

                    <ol class="stepper-list flex justify-between items-start w-full relative z-10"
                        style="<?= $isCancelled ? 'display: none;' : 'display: flex;' ?>">
                        <?php foreach ($orderSteps as $index => $step): ?>
                            <?php
                            $isCompleted = $index < $currentStatusIndex;
                            $isActive = ($index === $currentStatusIndex) && ($currentStatusIndex < 4);
                            $isFinalReached = $currentStatusIndex >= 4;

                            if ($isFinalReached) {
                                $isCompleted = $index <= 4;
                                $isActive = false;
                            }
                            if ($currentStatusKey === 'pending_payment' && $index === 0) {
                                $isActive = true;
                                $isCompleted = false;
                            } elseif ($currentStatusKey !== 'pending_payment' && $index === 0) {
                                $isCompleted = true;
                                $isActive = false;
                            }

                            // New Styling
                            $iconClass = 'text-white/30';
                            $textClass = 'text-white/30';
                            $bgClass = 'bg-white/5 border border-white/10';
                            $lineAfterClass = 'bg-white/10';
                            if ($isActive) {
                                $iconClass = 'text-primary animate-pulse';
                                $textClass = 'text-primary drop-shadow-gold-glow';
                                $bgClass = 'bg-primary/20 border border-primary/50 shadow-gold-glow';
                            } elseif ($isCompleted) {
                                $iconClass = 'text-white';
                                $textClass = 'text-white';
                                $bgClass = 'bg-white/20 border border-white/30';
                                $lineAfterClass = 'bg-primary/70';
                            }
                            ?>
                            <li class="step-item flex flex-col items-center relative flex-1" data-step-index="<?= $index ?>">
                                <span
                                    class="step-icon flex items-center justify-center w-10 h-10 sm:w-14 sm:h-14 rounded-full mb-3 sm:mb-4 transition-all duration-700 ease-[cubic-bezier(0.4,0,0.2,1)] <?= $bgClass ?> shrink-0">
                                    <i
                                        class="<?= $step['icon'] ?> text-lg sm:text-2xl transition-colors duration-500 <?= $iconClass ?>"></i>
                                </span>
                                <span
                                    class="step-text font-sans text-[8px] sm:text-xs uppercase tracking-tight sm:tracking-[0.2em] text-center transition-colors duration-500 <?= $textClass ?> px-1 max-w-[60px] sm:max-w-none break-words">
                                    <?= SanitizeHelper::html($step['text']) ?>
                                </span>
                                <?php if ($index < count($orderSteps) - 1): ?>
                                    <div aria-hidden="true"
                                        class="step-line-after absolute top-[20px] sm:top-[28px] left-[50%] w-full h-[1px] sm:h-[2px] -z-10">
                                        <div class="w-full h-full transition-colors duration-700 <?= $lineAfterClass ?>"></div>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>

                    <div class="cancelled-message text-center p-8 bg-red-950/20 border border-red-900/50 rounded-sm backdrop-blur-sm"
                        style="<?= $isCancelled ? 'display: block;' : 'display: none;' ?>">
                        <i
                            class="<?= $statusMap['cancelled']['icon'] ?> text-red-500 text-5xl mb-4 drop-shadow-[0_0_15px_rgba(239,68,68,0.5)]"></i>
                        <p class="font-display text-3xl text-white mb-2"><?= $statusMap['cancelled']['text'] ?></p>
                        <p class="font-sans text-sm text-red-400/80 font-light">Pesanan ini telah dibatalkan.</p>
                    </div>
                    <p id="status-last-updated"
                        class="text-[10px] font-sans uppercase tracking-[0.2em] text-center text-on-surface-variant mt-6 sm:mt-8">
                    </p>
                </div>

                <!-- Order Items -->
                <div class="p-6 md:p-8 border-b border-white/10 bg-white/[0.01]">
                    <h2 class="font-sans text-[10px] text-primary uppercase tracking-[0.3em] mb-6">Detail Pesanan</h2>
                    <div class="space-y-4">
                        <?php if (!empty($order['items'])): ?>
                            <?php foreach ($order['items'] as $item): ?>
                                <div
                                    class="flex items-center space-x-4 sm:space-x-6 p-4 rounded-sm hover:bg-white/5 transition-colors duration-300 group">
                                    <?php
                                    $itemImage = !empty($item['image_path'])
                                        ? UrlHelper::baseUrl(SanitizeHelper::html($item['image_path']))
                                        : UrlHelper::baseUrl('images/menu-placeholder.jpg');
                                    ?>
                                    <div
                                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-sm overflow-hidden relative border border-white/10 flex-shrink-0">
                                        <div
                                            class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 z-10 mix-blend-overlay">
                                        </div>
                                        <img src="<?= $itemImage ?>" alt="<?= SanitizeHelper::html($item['menu_item_name']) ?>"
                                            class="w-full h-full object-cover saturate-50 group-hover:saturate-100 transition-all duration-500 group-hover:scale-110"
                                            onerror="this.onerror=null; this.src='<?= UrlHelper::baseUrl('images/menu-placeholder.jpg') ?>';">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="font-display text-lg sm:text-xl text-white mb-1 group-hover:text-primary transition-colors truncate">
                                            <?= SanitizeHelper::html($item['menu_item_name']) ?>
                                        </p>
                                        <p class="font-sans text-sm text-on-surface-variant font-light mb-2">
                                            <?= SanitizeHelper::html($item['quantity']) ?> x
                                            <?= NumberHelper::formatCurrencyIDR($item['price_at_order']) ?>
                                        </p>
                                        <?php if (!empty($item['notes'])): ?>
                                            <p class="text-xs font-sans font-light text-primary/80 italic flex items-center gap-2">
                                                <span class="material-symbols-outlined text-[14px]">edit_note</span>
                                                <span class="truncate"><?= SanitizeHelper::html($item['notes']) ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-right pl-2">
                                        <p class="font-display text-lg sm:text-xl text-white">
                                            <?= NumberHelper::formatCurrencyIDR($item['subtotal']) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-on-surface-variant text-sm font-light italic text-center py-8">Tidak ada item dalam
                                pesanan ini.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Total & Actions -->
                <div class="p-6 md:p-8 bg-black/40">
                    <div class="flex justify-between items-end mb-8">
                        <p id="total-amount-label" class="font-sans text-[10px] uppercase tracking-[0.3em] text-on-surface-variant">
                            <?= ($order['status'] === 'pending_payment') ? 'Total Penagihan' : 'Total Dibayar' ?>
                        </p>
                        <p class="font-display text-1xl sm:text-1xl text-primary drop-shadow-gold-glow">
                            <?= NumberHelper::formatCurrencyIDR($order['total_amount']) ?></p>
                    </div>

                    <div class="text-center mb-8 relative">
                        <div class="absolute top-1/2 left-0 w-full h-[1px] bg-white/5 -translate-y-1/2 z-0"></div>
                        <p id="order-status-text"
                            class="relative z-10 inline-block px-6 bg-[#0c0c0c] font-sans text-xs sm:text-sm text-on-surface-variant font-light">
                            <?php if ($currentStatusKey == 'pending_payment'): ?>
                                Silakan lakukan pembayaran tunai di kasir.
                            <?php elseif ($currentStatusKey == 'served'): ?>
                                Pesanan Anda telah disajikan.
                            <?php elseif ($currentStatusKey == 'ready'): ?>
                                Pesanan Anda sudah siap diambil.
                            <?php elseif ($currentStatusKey == 'cancelled'): ?>
                                Pesanan ini telah dibatalkan.
                            <?php else: ?>
                                Status akan diperbarui secara otomatis.
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button id="refresh-status-btn" type="button"
                            class="flex-1 px-8 py-4 border border-white/20 text-white font-sans text-[10px] sm:text-xs uppercase tracking-[0.2em] hover:bg-white hover:text-black hover:border-transparent transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] rounded-sm flex items-center justify-center gap-3 group">
                            <span
                                class="material-symbols-outlined text-sm group-hover:rotate-180 transition-transform duration-700">sync</span>
                            Segarkan
                        </button>
                        <a href="<?= UrlHelper::baseUrl('/menu/table/' . SanitizeHelper::html($order['qr_code_identifier'] ?? '')) ?>" class="flex-1 px-8 py-4 border border-primary text-primary font-sans text-[10px] sm:text-xs uppercase tracking-[0.2em] hover:bg-primary hover:text-black hover:shadow-gold-glow transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] rounded-sm flex items-center justify-center gap-3">
                            <span class="material-symbols-outlined text-sm">restaurant_menu</span> 
                            Menu
                        </a>
                    </div>
                    <p id="polling-indicator"
                        class="text-[10px] font-sans uppercase tracking-[0.2em] text-center text-primary mt-6 hidden flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[12px] animate-spin">refresh</span>
                        Memeriksa status...
                    </p>
                </div>
            </div>

            <!-- Hidden Inputs -->
            <input type="hidden" id="order-id" value="<?= SanitizeHelper::html($order['id']) ?>">
            <input type="hidden" id="current-status-key" value="<?= SanitizeHelper::html($currentStatusKey) ?>">
            <input type="hidden" id="order-number-full" value="<?= SanitizeHelper::html($order['order_number'] ?? '') ?>">

        <?php else: ?>
            <!-- Error State -->
            <div class="reveal-up glass-panel border border-red-900/50 p-12 rounded-sm text-center max-w-lg mx-auto mt-24">
                <span
                    class="material-symbols-outlined text-6xl text-red-500 mb-6 drop-shadow-[0_0_15px_rgba(239,68,68,0.5)]">error</span>
                <h2 class="font-display text-3xl text-white mb-4">Pesanan Tidak Ditemukan</h2>
                <p class="font-sans text-sm text-on-surface-variant font-light mb-8">Maaf, kami tidak dapat menemukan detail
                    pesanan yang Anda cari. Silakan periksa kembali nomor pesanan Anda.</p>
                <a href="<?= UrlHelper::baseUrl('/') ?>"
                    class="inline-flex items-center px-8 py-4 border border-white/20 text-white font-sans text-xs uppercase tracking-[0.2em] hover:bg-white hover:text-black transition-all duration-500 rounded-sm">
                    Kembali ke Beranda
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Script Loader -->
<script> window.APP_BASE_URL = '<?= rtrim(UrlHelper::baseUrl(), '/') ?>'; </script>
<script src="<?= UrlHelper::baseUrl('js/customer-status.js?v=2.0') ?>" defer></script>