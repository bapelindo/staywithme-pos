<?php
// Lokasi File: app/views/kds/index.php
// Dimuat oleh Kds\OrderController::index()

ob_start();
// Data dari controller: $title, $orders (array of order objects with items), $auto_refresh_interval (ms)

$autoRefreshInterval = $auto_refresh_interval ?? 15000; // Default 15 detik
?>

<div id="kds-container" class="w-full h-full">
    <?php // Pesan jika tidak ada pesanan aktif ?>
    <div id="no-orders-message" class="text-center text-gray-400 text-2xl italic mt-20 <?= !empty($orders) ? 'hidden' : '' ?>">
        Tidak ada pesanan aktif saat ini...
    </div>

    <?php // Grid untuk menampilkan kartu pesanan ?>
    <div id="kds-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order):
                // Hitung waktu sejak pesanan masuk
                $timeSinceOrder = time() - strtotime($order->ordered_at);
                $timeAgo = floor($timeSinceOrder / 60); // Menit
                $timeUnit = 'menit';
                if ($timeAgo >= 60) {
                    $timeAgo = floor($timeAgo / 60);
                    $timeUnit = 'jam';
                }

                 // Tentukan warna kartu berdasarkan waktu atau status (contoh)
                 $cardBgColor = 'bg-gray-700'; // Default
                 if ($timeSinceOrder > 600) $cardBgColor = 'bg-yellow-800'; // Lebih dari 10 menit -> kuning
                 if ($timeSinceOrder > 1200) $cardBgColor = 'bg-red-800'; // Lebih dari 20 menit -> merah
                 if ($order->order_status === 'preparing') $cardBgColor = 'bg-blue-800'; // Sedang disiapkan

            ?>
                <div id="order-card-<?= $order->id ?>"
                     class="order-card border border-gray-600 rounded-md shadow-md <?= $cardBgColor ?> text-white flex flex-col"
                     data-order-id="<?= $order->id ?>"
                     data-ordered-at="<?= strtotime($order->ordered_at) ?>">

                    <?php // Header Kartu Pesanan ?>
                    <div class="p-2 border-b border-gray-600 flex justify-between items-center">
                        <div>
                            <span class="font-bold text-lg">#<?= htmlspecialchars($order->order_code) ?></span>
                            <span class="text-sm ml-2">(Meja: <?= htmlspecialchars($order->table_number ?? '?') ?>)</span>
                        </div>
                        <span class="text-xs font-mono bg-black bg-opacity-20 px-1.5 py-0.5 rounded" title="<?= format_datetime($order->ordered_at) ?>">
                            <?= $timeAgo . ' ' . $timeUnit ?> lalu
                        </span>
                    </div>

                    <?php // Daftar Item Pesanan ?>
                    <div class="p-2 space-y-1.5 flex-grow overflow-y-auto max-h-60"> <?php // Max height agar bisa scroll jika item banyak ?>
                        <?php if (!empty($order->items)): ?>
                            <?php foreach ($order->items as $item):
                                 $itemStatus = $item->item_status ?? 'pending';
                                 $itemTextClass = ($itemStatus === 'ready') ? 'line-through text-gray-400' : (($itemStatus === 'preparing') ? 'text-yellow-300' : '');
                            ?>
                                <div id="item-<?= $item->id ?>" class="item-card flex justify-between items-start border-b border-gray-600 border-opacity-50 pb-1" data-item-id="<?= $item->id ?>" data-item-status="<?= $itemStatus ?>">
                                    <div class="flex-grow mr-2 <?= $itemTextClass ?>">
                                        <span class="font-semibold"><?= htmlspecialchars($item->quantity) ?>x</span>
                                        <span class="ml-1"><?= htmlspecialchars($item->menu_item_name ?? 'Item tidak dikenal') ?></span>
                                        <?php if (!empty($item->notes)): ?>
                                            <p class="text-xs italic text-yellow-200 mt-0.5">Note: <?= htmlspecialchars($item->notes) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php // Tombol Aksi per Item (jika status belum ready) ?>
                                     <?php if ($itemStatus !== 'ready'): ?>
                                    <div class="flex-shrink-0 space-x-1">
                                         <?php if ($itemStatus === 'pending' || $itemStatus === 'received'): ?>
                                        <button class="kds-item-action-btn p-1 bg-blue-600 hover:bg-blue-700 rounded text-xs" data-action="preparing" title="Tandai Sedang Disiapkan">Siap</button>
                                         <?php endif; ?>
                                          <?php if ($itemStatus === 'pending' || $itemStatus === 'received' || $itemStatus === 'preparing'): ?>
                                        <button class="kds-item-action-btn p-1 bg-green-600 hover:bg-green-700 rounded text-xs" data-action="ready" title="Tandai Selesai">✓</button>
                                        <?php endif; ?>
                                    </div>
                                      <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-xs italic text-gray-400">Tidak ada detail item?</p>
                        <?php endif; ?>
                    </div>

                     <?php // Catatan Pesanan Utama ?>
                     <?php if (!empty($order->notes)): ?>
                        <div class="p-2 border-t border-gray-600 text-xs italic bg-black bg-opacity-10">
                            Note Pesanan: <?= htmlspecialchars($order->notes) ?>
                        </div>
                    <?php endif; ?>

                    <?php // Footer Kartu Pesanan (Tombol Aksi Order) ?>
                    <div class="p-2 border-t border-gray-600 text-center">
                        <?php if ($order->order_status === 'received'): ?>
                         <button class="kds-order-action-btn w-full bg-blue-600 hover:bg-blue-700 py-1 rounded text-sm font-semibold" data-action="preparing" data-order-id="<?= $order->id ?>">
                             Mulai Siapkan Pesanan Ini
                         </button>
                        <?php elseif ($order->order_status === 'preparing'): ?>
                         <button class="kds-order-action-btn w-full bg-green-600 hover:bg-green-700 py-1 rounded text-sm font-semibold" data-action="ready" data-order-id="<?= $order->id ?>">
                             Tandai Semua Selesai (Siap)
                         </button>
                        <?php endif; ?>
                         <?php // Tambah tombol lain misal 'Tunda', 'Panggil Staff'? ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; // End check empty $orders ?>
    </div> <?php // End kds-grid ?>
</div> <?php // End kds-container ?>

<?php
$content = ob_get_clean();

// Sertakan script khusus KDS
ob_start();
?>
<script>
    // Kirim interval refresh ke JS
    window.KDS_REFRESH_INTERVAL = <?= $autoRefreshInterval ?>;
    // Mungkin perlu URL endpoint juga
    window.KDS_FETCH_URL = '<?= url_for('/kds/orders/fetchUpdates') ?>';
    window.KDS_ITEM_UPDATE_URL = '<?= url_for('/kds/items/updateStatus') ?>'; // Base URL, ID ditambahkan di JS
    window.KDS_ORDER_READY_URL = '<?= url_for('/kds/orders/markReady') ?>'; // Base URL, ID ditambahkan di JS
    window.KDS_ORDER_PREPARING_URL = '<?= url_for('/kds/orders/markPreparing') ?>'; // Endpoint baru jika perlu
</script>
<?php
// File JS utama KDS harus di-load SETELAH definisi variabel window di atas
// (Bisa ditaruh di layout kds.php setelah placeholder $pageScript)
$pageScript = ob_get_clean();

// Sertakan layout KDS
require APPROOT . '/views/layouts/kds.php';
?>