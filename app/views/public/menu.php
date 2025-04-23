<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;

// Ambil data dari MenuController
$table = $table ?? ['id' => 0, 'table_number' => 'Tidak Diketahui'];
$categories = $categories ?? [];
$menuItemsByCategory = $menuItemsByCategory ?? [];

$pageTitle = "Pesan Menu - Meja " . SanitizeHelper::html($table['table_number']);
?>

<div class="relative pb-24"> <div class="mb-8 p-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold">Meja <?= SanitizeHelper::html($table['table_number']) ?></h1>
        <p class="text-indigo-100">Silakan pilih menu yang Anda inginkan.</p>
        <input type="hidden" id="table-id" value="<?= SanitizeHelper::html($table['id']) ?>">
    </div>

    <?php if (empty($categories) || empty($menuItemsByCategory)): ?>
        <div class="text-center py-16">
            <p class="text-xl text-slate-500">Mohon maaf, menu belum tersedia.</p>
             <p class="text-sm text-slate-400 mt-2">Silakan hubungi staff kami.</p>
        </div>
    <?php else: ?>
        <?php foreach ($categories as $category): ?>
            <?php $categoryId = $category['id']; ?>
            <?php if (isset($menuItemsByCategory[$categoryId]) && !empty($menuItemsByCategory[$categoryId])): ?>
                <section id="category-<?= SanitizeHelper::html($categoryId) ?>" class="mb-10">
                    <h2 class="text-2xl font-semibold text-slate-700 mb-5 pb-2 border-b-2 border-indigo-200"><?= SanitizeHelper::html($category['name']) ?></h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        <?php foreach ($menuItemsByCategory[$categoryId] as $item): ?>
                            <?php $itemId = $item['id']; ?>
                            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-slate-200 hover:shadow-lg transition-shadow duration-300 flex flex-col group">
                                <?php
                                    $imagePath = $item['image_path'] ?? 'images/default_menu.png'; // Gambar default
                                    $imageUrl = UrlHelper::asset(SanitizeHelper::html($imagePath));
                                ?>
                                <div class="h-48 overflow-hidden">
                                    <img src="<?= $imageUrl ?>" alt="<?= SanitizeHelper::html($item['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ease-in-out">
                                </div>
                                <div class="p-4 flex flex-col flex-grow">
                                    <h3 class="text-lg font-semibold text-slate-800 mb-1 truncate" title="<?= SanitizeHelper::html($item['name']) ?>"><?= SanitizeHelper::html($item['name']) ?></h3>
                                    <?php if (!empty($item['description'])): ?>
                                        <p class="text-xs text-slate-500 mb-3 flex-grow line-clamp-2"><?= SanitizeHelper::html($item['description']) ?></p>
                                    <?php else: ?>
                                         <div class="flex-grow mb-3"></div> <?php endif; ?>
                                    <div class="flex justify-between items-center mt-auto pt-2">
                                        <span class="text-indigo-600 font-bold text-lg">
                                            <?= NumberHelper::formatCurrencyIDR($item['price']) ?>
                                        </span>
                                        <button
                                            class="add-to-cart-btn bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out transform active:scale-95"
                                            data-id="<?= SanitizeHelper::html($itemId) ?>"
                                            data-name="<?= SanitizeHelper::html($item['name']) ?>"
                                            data-price="<?= SanitizeHelper::html((string)$item['price']) ?>"
                                        >
                                            Tambah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <div id="cart-section" class="fixed bottom-0 left-0 right-0 bg-white border-t-2 border-indigo-200 shadow-[-4px_0px_15px_rgba(0,0,0,0.1)] z-50 transform translate-y-full transition-transform duration-300 ease-in-out">
        <div id="cart-header" class="px-4 py-2 flex justify-between items-center cursor-pointer bg-indigo-50 hover:bg-indigo-100">
             <h3 class="text-lg font-semibold text-indigo-800">Keranjang Anda (<span id="cart-item-count">0</span> Item)</h3>
              <span id="cart-toggle-icon" class="text-indigo-600 transform rotate-180">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
             </span>
         </div>
        <div id="cart-content" class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
             <div id="cart-items" class="p-4 max-h-64 overflow-y-auto border-b">
                <p class="text-slate-500 text-sm text-center cart-empty-message">Keranjang masih kosong.</p>
            </div>
            <div class="p-4 bg-slate-50">
                <div class="flex justify-between items-center mb-3 font-semibold">
                    <span class="text-slate-700">Total:</span>
                    <span id="cart-total" class="text-xl text-indigo-700">Rp 0</span>
                </div>
                <button id="place-order-btn" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed shadow-md" disabled>
                    Pesan Sekarang
                </button>
                <div id="order-message" class="mt-3 text-sm text-center font-medium"></div> </div>
        </div>
    </div>

</div><script src="<?= UrlHelper::asset('js/customer-menu.js') ?>" defer></script>
<script>
    // JS sederhana untuk toggle cart view
    document.addEventListener('DOMContentLoaded', () => {
        const cartHeader = document.getElementById('cart-header');
        const cartContent = document.getElementById('cart-content');
        const cartSection = document.getElementById('cart-section');
        const cartToggleIcon = document.getElementById('cart-toggle-icon');
        let isCartOpen = false;

        if (cartHeader && cartContent && cartSection && cartToggleIcon) {
            cartHeader.addEventListener('click', () => {
                isCartOpen = !isCartOpen;
                if (isCartOpen) {
                    cartSection.style.transform = 'translateY(0)';
                    cartContent.style.maxHeight = '400px'; // Sesuaikan max-height
                     cartToggleIcon.style.transform = 'rotate(0deg)';
                } else {
                    // cartSection.style.transform = 'translateY(100%)'; // Sembunyikan lagi
                    cartContent.style.maxHeight = '0px';
                     cartToggleIcon.style.transform = 'rotate(180deg)';
                     // Beri jeda sebelum translate agar transisi max-height selesai
                     setTimeout(() => {
                         if (!isCartOpen) cartSection.style.transform = 'translateY(calc(100% - 50px))'; // Sesuaikan tinggi header
                     }, 300);
                }
            });

            // Inisialisasi posisi cart (sedikit terlihat)
             cartSection.style.transform = 'translateY(calc(100% - 50px))'; // Sesuaikan tinggi header
        }

         // Fungsi untuk menampilkan cart saat item ditambah (dipanggil dari customer-menu.js)
         window.showCartTemporarily = () => {
             if (!isCartOpen) {
                 cartHeader.click(); // Buka cart jika belum terbuka
             }
         };
    });
</script>