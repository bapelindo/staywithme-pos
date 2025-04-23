<?php
// Lokasi File: app/views/public/order_status.php
// Halaman ini akan dimuat oleh Public\OrderController::status()

// Tangkap output
ob_start();

// Data dari controller: $title, $order, $orderItems, $tableNumber, $initialStatus
?>

<div class="container mx-auto my-6 px-4">
    <div class="bg-white p-6 md:p-8 rounded-xl shadow-lg max-w-2xl mx-auto border border-gray-200">

        <div class="text-center mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800"><?= htmlspecialchars($title ?? 'Status Pesanan Anda') ?></h1>
            <p class="text-sm text-gray-500 mt-1">
                Meja: <?= htmlspecialchars($tableNumber ?? '') ?> | Kode: <?= htmlspecialchars($order->order_code ?? '') ?>
            </p>
        </div>

        <div id="order-status-container"
             data-order-id="<?= $order->id ?? 0 ?>"
             data-initial-status="<?= htmlspecialchars($initialStatus ?? 'pending') ?>"
             class="mb-6 border border-gray-200 rounded-lg p-4 bg-gray-50 transition-colors duration-500">

            <p class="text-md font-semibold text-gray-700 mb-2 text-center">Status Saat Ini:</p>
            <p id="order-status-text" class="text-2xl md:text-3xl font-bold text-blue-600 text-center break-words">
                 <?= htmlspecialchars(ucwords(str_replace('_', ' ', $initialStatus ?? 'pending'))) ?>
            </p>

            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4 overflow-hidden">
                <div id="order-progress-bar" class="h-full bg-blue-500 rounded-full transition-all duration-500 ease-in-out" style="width: 0%">
                    <?php /* Width diupdate oleh JS */ ?>
                </div>
            </div>
        </div>

        <?php if (!empty($orderItems)): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Detail Item:</h3>
                <ul class="space-y-2">
                    <?php foreach ($orderItems as $item): ?>
                        <li class="flex justify-between items-center text-sm">
                            <span class="text-gray-800">
                                <?= htmlspecialchars($item->quantity ?? 1) ?>x <?= htmlspecialchars($item->menu_item_name ?? 'Item tidak dikenal') ?>
                            </span>
                            <span class="text-gray-600 font-medium">
                                Rp <?= number_format($item->subtotal ?? 0, 0, ',', '.') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                     <li class="flex justify-between items-center text-sm font-bold border-t pt-2 mt-2">
                            <span class="text-gray-900">Total</span>
                            <span class="text-gray-900">
                                Rp <?= number_format($order->total_amount ?? 0, 0, ',', '.') ?>
                            </span>
                        </li>
                </ul>
            </div>
        <?php endif; ?>

        <div id="payment-info-container" class="<?= (($initialStatus ?? '') === 'ready' || ($initialStatus ?? '') === 'served') ? '' : 'hidden' ?>">
             <?php if (($initialStatus ?? '') === 'ready' || ($initialStatus ?? '') === 'served'): ?>
                  <div id="payment-message" class="mt-4 p-4 bg-gradient-to-r from-blue-100 to-cyan-100 border border-blue-300 text-blue-800 rounded text-center shadow">
                    <strong class="block text-lg">Pesanan Anda sudah siap!</strong>
                    <p class="mt-1">Silakan lakukan pembayaran di kasir.</p>
                    <p class="text-sm">Sebutkan Nomor Meja atau Kode Pesanan Anda.</p>
                  </div>
             <?php endif; ?>
            <?php /* Diisi/diupdate oleh JS saat status 'ready' atau 'served' */ ?>
        </div>

        <div class="mt-8 text-center border-t pt-4">
             <p class="text-xs text-gray-500 mb-3">Halaman ini akan memuat status terbaru secara otomatis.</p>
            <a href="<?= url_for('/menu') ?>" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-5 rounded-lg text-sm transition duration-150 ease-in-out">
                &larr; Kembali ke Menu
            </a>
        </div>

    </div> <?php // End card ?>
</div> <?php // End container ?>


<?php
$content = ob_get_clean();

// Sertakan script polling
ob_start(); // Tangkap script untuk $pageScript
?>
<script>
  // Definisikan URLROOT global untuk JS
  window.APP_URLROOT = '<?= URLROOT ?>';
</script>
<script src="<?= asset('js/public.js') // Pastikan ini file JS polling yg sudah diupdate ?>?v=<?= time() // Cache busting ?>"></script>
<?php
$pageScript = ob_get_clean();

// Sertakan layout utama
require APPROOT . '/views/layouts/public.php';
?>