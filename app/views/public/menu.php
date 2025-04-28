<?php
// File: app/Views/public/menu.php (Redesigned - Mobile First)
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;

// Data dari MenuController
$table = $table ?? ['id' => 0, 'table_number' => '???'];
$categories = $categories ?? [];
$menuItemsByCategory = $menuItemsByCategory ?? []; // Diasumsikan ini array [categoryId => [items...]]

$pageTitle = "Menu - Meja " . SanitizeHelper::html($table['table_number']);
$placeholderImage = UrlHelper::baseUrl('images/menu-placeholder.jpg');
?>

<?php // ---- Mulai Konten Halaman ---- ?>
<div class="bg-bg-dark-secondary min-h-screen pb-32"> <?php // Background utama & padding bawah u/ cart ?>

    <div class="sticky top-0 z-40 shadow-md"> <?php // Header sticky ?>
        <div class="bg-gradient-to-r from-gray-800 via-bg-dark-primary to-gray-800 p-4 border-b border-border-dark">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                 <h1 class="text-2xl font-bold text-white text-center">
                     <i class="fas fa-tablet-alt mr-2 text-accent-primary"></i> Meja <?= SanitizeHelper::html($table['table_number']) ?>
                 </h1>
                 <input type="hidden" id="table-id" value="<?= SanitizeHelper::html($table['id']) ?>">
            </div>
        </div>

        <?php if (!empty($categories)): ?>
        <nav id="category-tabs-container" class="bg-bg-dark-primary border-b border-border-dark overflow-x-auto whitespace-nowrap category-tabs-sticky">
             <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-2 flex space-x-4 justify-start md:justify-center">
                 <button class="category-tab active text-sm font-medium px-4 py-1.5 rounded-full flex-shrink-0" data-filter="all">
                    <i class="fas fa-utensils mr-1.5"></i> Semua Menu
                </button>
                 <?php foreach ($categories as $category): ?>
                     <?php $categorySlug = SanitizeHelper::html(strtolower(str_replace(' ', '-', $category['name']))); ?>
                     <button class="category-tab text-sm font-medium px-4 py-1.5 rounded-full flex-shrink-0" data-filter="<?= $categorySlug ?>">
                         <?= SanitizeHelper::html($category['name']) ?>
                     </button>
                 <?php endforeach; ?>
             </div>
        </nav>
        <?php endif; ?>
     </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <?php if (empty($categories) || empty($menuItemsByCategory)): ?>
            <div class="text-center py-16 bg-bg-dark-primary rounded-lg shadow mt-6">
                <i class="fas fa-exclamation-circle text-4xl text-slate-500 mb-4"></i>
                <p class="text-xl text-text-dark-secondary">Menu belum tersedia.</p>
                 <p class="text-sm text-slate-400 mt-2">Silakan hubungi staff kami.</p>
            </div>
        <?php else: ?>
            <?php // Container untuk item menu yang akan difilter oleh JS ?>
            <div id="menu-items-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4 lg:gap-5"> <?php // Diubah: Mulai dari grid-cols-2 ?>
                <?php foreach ($categories as $category): ?>
                    <?php $categoryId = $category['id']; ?>
                    <?php if (isset($menuItemsByCategory[$categoryId]) && !empty($menuItemsByCategory[$categoryId])): ?>
                        <?php foreach ($menuItemsByCategory[$categoryId] as $item): ?>
                            <?php
                                $itemId = $item['id'];
                                $imageUrl = !empty($item['image_path'])
                                            ? UrlHelper::baseUrl($item['image_path'])
                                            : $placeholderImage;
                                $categorySlug = SanitizeHelper::html(strtolower(str_replace(' ', '-', $category['name'])));
                             ?>
                            <div class="menu-item bg-bg-dark rounded-lg shadow-md overflow-hidden border border-border-dark flex flex-col group transition-all duration-300 ease-out opacity-100"
                                 data-category="<?= $categorySlug ?>" data-aos="fade-up" data-aos-delay="50">
                                 <div class="relative w-full aspect-w-4 aspect-h-3">
                                     <img src="<?= $imageUrl ?>" alt="<?= SanitizeHelper::html($item['name']) ?>"
                                          class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                          loading="lazy"
                                          onerror="this.onerror=null; this.src='<?= $placeholderImage ?>';">
                                 </div>
                                <div class="p-3 sm:p-4 flex flex-col flex-grow"> <?php // Padding sedikit dikurangi untuk mobile ?>
                                    <h3 class="text-sm sm:text-md font-semibold text-white mb-1 truncate" title="<?= SanitizeHelper::html($item['name']) ?>">
                                        <?= SanitizeHelper::html($item['name']) ?>
                                    </h3>
                                    <p class="text-xs text-text-dark-secondary mb-2 sm:mb-3 flex-grow min-h-[2.5em] line-clamp-2"> <?php // min-h sedikit dikurangi ?>
                                        <?= SanitizeHelper::html($item['description'] ?? '') ?>
                                    </p>
                                    <div class="flex justify-between items-center mt-auto pt-1 sm:pt-2">
                                        <span class="text-accent-primary font-bold text-base sm:text-lg">
                                            <?= NumberHelper::formatCurrencyIDR($item['price']) ?>
                                        </span>
                                        <button
                                            class="add-to-cart-btn bg-accent-secondary hover:bg-sky-500 text-white text-xs font-bold py-5 px-5 sm:py-2 sm:px-3 rounded-md transition duration-150 ease-in-out transform active:scale-95 flex items-center"
                                            data-id="<?= SanitizeHelper::html($itemId) ?>"
                                            data-name="<?= SanitizeHelper::html($item['name']) ?>"
                                            data-price="<?= SanitizeHelper::html((string)$item['price']) ?>"
                                        >
                                             <i class="fas fa-cart-plus text-xs sm:text-sm mr-0"></i>
                                             <span class="text-text-dark-primary hidden sm:inline ml-1">Tambah</span> <?php // Teks hanya di layar sm+ ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
             <div id="menu-no-results" class="text-center py-16 bg-bg-dark-primary rounded-lg shadow mt-6 hidden">
                <i class="fas fa-search text-4xl text-slate-500 mb-4"></i>
                <p class="text-xl text-text-dark-secondary">Menu tidak ditemukan</p>
                <p class="text-sm text-slate-400 mt-2">Coba pilih kategori lain.</p>
            </div>
        <?php endif; ?>
    </div>

</div> <?php // Akhir bg-bg-dark-secondary ?>

<div id="cart-section" class="fixed bottom-0 left-0 right-0 bg-bg-dark border-t-2 border-border-dark shadow-[-4px_0px_15px_rgba(0,0,0,0.2)] z-50 transform translate-y-full transition-transform duration-300 ease-in-out rounded-t-lg">
    <div id="cart-header" class="px-4 py-5 flex justify-between items-center cursor-pointer bg-bg-dark-secondary hover:bg-bg-dark-tertiary transition-colors rounded-t-lg">
         <h3 class="text-lg font-semibold text-white">Keranjang (<span id="cart-item-count" class="text-accent-primary">0</span>)</h3>
          <span id="cart-toggle-icon" class="text-accent-primary transform rotate-180 transition-transform">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
         </span>
     </div>
    <div id="cart-content" class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
         <div id="cart-items" class="p-4 max-h-60 overflow-y-auto border-b border-border-dark">
             <p class="text-text-dark-secondary text-sm text-center cart-empty-message">Keranjang masih kosong.</p>
             <?php /* Template Item Cart (untuk referensi customer-menu.js) */ ?>
        </div>
        <div class="p-4 bg-bg-dark-secondary">
            <div class="flex justify-between items-center mb-3 font-semibold">
                <span class="text-text-dark-secondary">Total:</span>
                <span id="cart-total" class="text-xl text-accent-primary">Rp 0</span>
            </div>
            <button id="place-order-btn" class="w-full btn btn-accent py-3 rounded-lg font-semibold text-base disabled:opacity-50 disabled:cursor-not-allowed shadow-md" disabled>
                 Pesan Sekarang
            </button>
            <div id="order-message" class="mt-3 text-sm text-center font-medium"></div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const categoryTabsContainer = document.getElementById('category-tabs-container');
    const categoryTabs = document.querySelectorAll('.category-tab');
    const menuItemsContainer = document.getElementById('menu-items-container');
    const allMenuItems = menuItemsContainer ? Array.from(menuItemsContainer.querySelectorAll('.menu-item')) : [];
    const noResultsDiv = document.getElementById('menu-no-results');

    // --- Logika Filter Kategori ---
    if (categoryTabs.length > 0 && menuItemsContainer && allMenuItems.length > 0 && noResultsDiv) {
        console.log("Initializing category tab filters...");
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const filterValue = tab.dataset.filter;

                // Update status aktif tab
                categoryTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                let hasVisibleItems = false;
                // Tampilkan/Sembunyikan item menu
                allMenuItems.forEach(item => {
                    const itemCategory = item.dataset.category;
                    const matchesFilter = filterValue === 'all' || itemCategory === filterValue;

                    // Gunakan transisi sederhana (opsional)
                    item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    item.style.opacity = matchesFilter ? '1' : '0';
                    item.style.transform = matchesFilter ? 'scale(1)' : 'scale(0.95)';

                    // Set display setelah transisi (atau langsung jika tidak pakai transisi)
                    setTimeout(() => {
                         item.style.display = matchesFilter ? 'flex' : 'none'; // 'flex' karena kartu pakai flex
                    }, 50); // Beri sedikit jeda

                    if(matchesFilter) hasVisibleItems = true;
                });

                 // Tampilkan/sembunyikan pesan "tidak ada hasil"
                 noResultsDiv.style.display = hasVisibleItems ? 'none' : 'block';

                // Refresh AOS jika item menu menggunakan AOS
                 if (typeof AOS !== 'undefined') {
                     setTimeout(() => AOS.refreshHard(), 100);
                 }
            });
        });
    } else {
        console.warn("Category tabs or menu items container not found, filtering disabled.");
    }

    // --- Logika Toggle Cart (Sama seperti sebelumnya) ---
    const cartHeader = document.getElementById('cart-header');
    const cartContent = document.getElementById('cart-content');
    const cartSection = document.getElementById('cart-section');
    const cartToggleIcon = document.getElementById('cart-toggle-icon');
    let isCartOpen = false;
    const cartHeaderHeight = cartHeader ? cartHeader.offsetHeight : 50; // Ambil tinggi dinamis jika bisa

    if (cartHeader && cartContent && cartSection && cartToggleIcon) {
        cartHeader.addEventListener('click', () => {
            isCartOpen = !isCartOpen;
            cartSection.style.transform = isCartOpen ? 'translateY(0)' : `translateY(calc(100% - ${cartHeaderHeight}px))`;
            cartContent.style.maxHeight = isCartOpen ? '400px' : '0px'; // Sesuaikan max-height
            cartToggleIcon.style.transform = isCartOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        });
        cartSection.style.transform = `translateY(calc(100% - ${cartHeaderHeight}px))`;
    }

     // Fungsi untuk membuka cart (dipanggil oleh customer-menu.js)
     window.showCartTemporarily = () => {
         if (!isCartOpen && cartHeader) {
             cartHeader.click();
         }
     };
});
</script>

<?php // Pastikan customer-menu.js dimuat setelah HTML ini ?>
<script src="<?= UrlHelper::baseUrl('js/customer-menu.js') ?>" defer></script>