<?php
// Lokasi File: app/views/public/cart.php
// Halaman ini akan dimuat oleh Public\CartController::index() (belum dibuat controllernya)
// Controller perlu memeriksa session $_SESSION['cart']

// Tangkap output
ob_start();

// Cek sesi meja
$tableNumber = $_SESSION['current_table_number'] ?? null;
if (!$tableNumber) {
    set_flash('error', 'Scan QR Code meja terlebih dahulu untuk melihat keranjang.');
    // Mungkin redirect? Atau tampilkan pesan saja?
    // header('Location: ' . url_for('/')); exit;
}

// Ambil data keranjang dari session
ensure_session_started();
$cartItems = $_SESSION['cart'] ?? [];
$cartTotal = 0;
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-8"><?= htmlspecialchars($title ?? 'Keranjang Belanja Anda') ?></h1>

    <?php if (!$tableNumber): ?>
         <p class="text-center text-lg text-red-600 bg-red-100 p-4 rounded-md mb-6">
             Anda harus <a href="<?= url_for('/') ?>" class="font-bold underline hover:text-red-700">memindai QR Code Meja</a> sebelum dapat memesan.
        </p>
    <?php elseif (empty($cartItems)): ?>
        <div class="text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            <p class="text-center text-gray-500 text-lg mt-4">Keranjang Anda masih kosong.</p>
            <a href="<?= url_for('/menu') ?>" class="mt-6 inline-block bg-primary hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-md transition">
                Lihat Menu &rarr;
            </a>
        </div>
    <?php else: ?>
        <p class="text-center text-md text-gray-600 mb-6 -mt-4">Memesan untuk Meja: <strong><?= htmlspecialchars($tableNumber) ?></strong></p>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Item
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jumlah
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Subtotal
                        </th>
                        <th scope="col" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($cartItems as $itemId => $item):
                        $subtotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                        $cartTotal += $subtotal;
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['name'] ?? 'Item tidak dikenal') ?></div>
                            <div class="text-sm text-gray-500"><?= format_rupiah($item['price'] ?? 0, false) ?> / item</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <form action="<?= url_for('/cart/update') ?>" method="POST" class="inline-flex items-center space-x-1">
                                <?php // CSRF ?>
                                <input type="hidden" name="menu_item_id" value="<?= $itemId ?>">
                                <button type="submit" name="quantity" value="<?= max(1, $item['quantity'] - 1) ?>" class="p-1 text-gray-500 hover:text-gray-700">-</button>
                                <input type="number" name="quantity_direct" value="<?= (int)$item['quantity'] ?>" min="1" max="99"
                                       class="w-12 text-center border border-gray-300 rounded-md text-sm py-1"
                                       onchange="this.form.quantity_direct_submit.click()"> <?php // Submit on change ?>
                                <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>" class="p-1 text-gray-500 hover:text-gray-700">+</button>
                                <button type="submit" name="quantity_direct_submit" class="hidden">Update</button> <?php // Hidden submit for number input change ?>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-semibold text-gray-900"><?= format_rupiah($subtotal) ?></div>
                        </td>
                        <td class="px-2 py-4 whitespace-nowrap text-center">
                            <form action="<?= url_for('/cart/remove') ?>" method="POST" class="inline-block">
                                <?php // CSRF ?>
                                <input type="hidden" name="menu_item_id" value="<?= $itemId ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus item">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-8 p-6 bg-gray-50 rounded-lg border">
            <h2 class="text-xl font-semibold mb-4">Ringkasan Pesanan</h2>
            <div class="flex justify-between mb-4">
                <span class="text-gray-600">Total Harga:</span>
                <span class="text-xl font-bold text-primary"><?= format_rupiah($cartTotal) ?></span>
            </div>

            <form action="<?= url_for('/order/store') ?>" method="POST">
                 <?php // CSRF ?>
                 <div class="mb-4">
                     <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Pemesan (Opsional):</label>
                     <input type="text" id="customer_name" name="customer_name"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-primary focus:border-primary"
                            placeholder="Nama Anda">
                 </div>
                 <div class="mb-4">
                     <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan (Opsional):</label>
                     <textarea id="notes" name="notes" rows="3"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-primary focus:border-primary"
                               placeholder="Misalnya: Tidak pedas, ekstra saus..."></textarea>
                 </div>

                 <button type="submit"
                         class="w-full bg-secondary hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-lg shadow-md transition duration-300 ease-in-out">
                     Buat Pesanan Sekarang
                 </button>
            </form>
             <p class="text-xs text-gray-500 mt-4 text-center">
                 Pembayaran dilakukan di kasir setelah pesanan Anda siap atau disajikan.
            </p>
        </div>
         <div class="mt-6 text-center">
            <a href="<?= url_for('/menu') ?>" class="text-blue-500 hover:text-blue-700">&larr; Tambah Menu Lain</a>
        </div>

    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
// JS untuk interaksi keranjang (update qty tanpa reload?) perlu ditambahkan
// $pageScript = '<script src="' . asset('js/cart_handler.js') . '"></script>';
require APPROOT . '/views/layouts/public.php';
?>