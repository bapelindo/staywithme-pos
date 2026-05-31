<?php
// File: app/Views/public/menu.php (Redesigned - Cinematic Canvas)
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;

// Data dari MenuController
$table = isset($table) ? $table : null;
$categories = $categories ?? [];
$menuItemsByCategory = $menuItemsByCategory ?? [];

$pageTitle = "Menu " . ($table ? "- Meja " . SanitizeHelper::html($table['table_number']) : "Utama");
$placeholderImage = UrlHelper::baseUrl('images/menu-placeholder-v2.jpg');
?>

<div class="bg-[#050505] min-h-[100svh] pb-40 font-sans text-white relative pt-20">

    <!-- Cinematic Header -->
    <div class="relative w-full py-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-[#050505] via-[#050505]/80 to-[#050505] z-10"></div>
        <img alt="Menu Cover" class="absolute inset-0 w-full h-full object-cover opacity-30 animate-pulse-slow"
            src="<?= UrlHelper::baseUrl('images/experience-illustration-1-v2.jpg') ?>">
        <div class="relative z-20 container mx-auto px-6 lg:px-12 flex flex-col items-center">
            <span
                class="text-[10px] text-primary uppercase tracking-[0.4em] mb-4 block flex items-center justify-center gap-4 reveal-up">
                <span class="w-8 h-[1px] bg-primary/50"></span>
                Artisan Roastery
                <span class="w-8 h-[1px] bg-primary/50"></span>
            </span>
            <?php if ($table): ?>
                <h1
                    class="text-4xl md:text-6xl font-display text-white text-center mb-6 tracking-wide reveal-up reveal-delay-1">
                    Koleksi <span class="italic text-primary">Meja
                        <?= SanitizeHelper::html($table['table_number']) ?></span>
                </h1>
                <p class="text-[#a3a3a3] font-light max-w-lg text-center reveal-up reveal-delay-2">Pilih sajian favorit Anda
                    dan nikmati pengalaman bersantai yang tak terlupakan.</p>
                <input type="hidden" id="table-id" value="<?= SanitizeHelper::html($table['id']) ?>">
                <input type="hidden" id="existing-order-id" value="<?= SanitizeHelper::html($existingOrderId ?? '') ?>">
            <?php else: ?>
                <h1
                    class="text-4xl md:text-6xl font-display text-white text-center mb-6 tracking-wide reveal-up reveal-delay-1">
                    Koleksi <span class="italic text-primary">Menu</span>
                </h1>
                <p class="text-[#a3a3a3] font-light max-w-lg text-center reveal-up reveal-delay-2">Jelajahi dan nikmati
                    karya artisan roastery kami yang dikurasi khusus untuk Anda.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($categories)): ?>
        <!-- Category Sticky Nav -->
        <div
            class="sticky top-20 z-40 glass-panel border-y border-white/5 py-4 transition-all duration-300 reveal-up reveal-delay-3">
            <nav id="category-tabs-container"
                class="container mx-auto px-6 lg:px-12 overflow-x-auto whitespace-nowrap hide-scrollbar flex justify-start md:justify-center gap-8">
                <button
                    class="category-tab active text-xs uppercase tracking-[0.2em] font-medium pb-2 border-b-2 border-primary text-primary transition-colors hover:text-primary flex-shrink-0"
                    data-filter="all">
                    Semua Koleksi
                </button>
                <?php foreach ($categories as $category): ?>
                    <?php $categorySlug = SanitizeHelper::html(strtolower(str_replace(' ', '-', $category['name']))); ?>
                    <button
                        class="category-tab text-xs uppercase tracking-[0.2em] font-medium pb-2 border-b-2 border-transparent text-[#a3a3a3] transition-colors hover:text-white flex-shrink-0"
                        data-filter="<?= $categorySlug ?>">
                        <?= SanitizeHelper::html($category['name']) ?>
                    </button>
                <?php endforeach; ?>
            </nav>
        </div>
    <?php endif; ?>

    <!-- Menu Grid -->
    <div class="container mx-auto px-6 lg:px-12 mt-16">
        <?php if (empty($categories) || empty($menuItemsByCategory)): ?>
            <div class="text-center py-24 glass-panel rounded-sm shadow reveal-up">
                <span class="material-symbols-outlined text-4xl text-[#a3a3a3] mb-4">inventory_2</span>
                <p class="text-xl font-display text-white">Koleksi belum tersedia.</p>
                <p class="text-sm text-[#a3a3a3] mt-2 font-light">Silakan hubungi concierge kami.</p>
            </div>
        <?php else: ?>
            <div id="menu-items-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach ($categories as $category): ?>
                    <?php $categoryId = $category['id']; ?>
                    <?php if (isset($menuItemsByCategory[$categoryId]) && !empty($menuItemsByCategory[$categoryId])): ?>
                        <?php foreach ($menuItemsByCategory[$categoryId] as $item): ?>
                            <?php
                            $itemId = $item['id'];
                            $imageUrl = !empty($item['image_path']) ? UrlHelper::baseUrl($item['image_path']) : $placeholderImage;
                            $categorySlug = SanitizeHelper::html(strtolower(str_replace(' ', '-', $category['name'])));
                            ?>
                            <div class="menu-item group flex flex-col glass-panel overflow-hidden transition-all duration-500 hover:border-primary/50 reveal-up"
                                data-category="<?= $categorySlug ?>">
                                <div class="relative w-full h-64 overflow-hidden">
                                    <img src="<?= $imageUrl ?>" alt="<?= SanitizeHelper::html($item['name']) ?>"
                                        class="absolute inset-0 w-full h-full object-cover opacity-70 saturate-50 group-hover:scale-110 group-hover:saturate-100 group-hover:opacity-100 transition-all duration-[1s] ease-[cubic-bezier(0.4,0,0.2,1)]"
                                        loading="lazy" onerror="this.onerror=null; this.src='<?= $placeholderImage ?>';">
                                    <div class="absolute inset-0 bg-gradient-to-t from-[#050505] to-transparent opacity-80"></div>
                                </div>
                                <div class="p-6 flex flex-col flex-grow relative z-10 -mt-16">
                                    <span
                                        class="text-[10px] text-primary uppercase tracking-[0.2em] mb-2 block"><?= SanitizeHelper::html($category['name']) ?></span>
                                    <h3
                                        class="text-2xl font-display text-white mb-3 group-hover:text-primary transition-colors leading-tight line-clamp-2">
                                        <?= SanitizeHelper::html($item['name']) ?>
                                    </h3>
                                    <p class="text-sm text-[#a3a3a3] font-light leading-relaxed mb-6 flex-grow line-clamp-3">
                                        <?= SanitizeHelper::html($item['description'] ?? '') ?>
                                    </p>
                                    <div class="flex justify-between items-end mt-auto pt-4 border-t border-white/10">
                                        <span class="font-display text-xl text-white">
                                            <?= NumberHelper::formatCurrencyIDR($item['price']) ?>
                                        </span>
                                        <?php if ($table): ?>
                                            <button
                                                class="add-to-cart-btn flex items-center justify-center w-12 h-12 rounded-full border border-primary text-primary hover:bg-gold-gradient hover:text-black hover:border-transparent transition-all duration-300 hover:shadow-gold-glow"
                                                data-id="<?= SanitizeHelper::html($itemId) ?>"
                                                data-name="<?= SanitizeHelper::html($item['name']) ?>"
                                                data-price="<?= SanitizeHelper::html((string) $item['price']) ?>"
                                                title="Tambah ke Keranjang">
                                                <span class="material-symbols-outlined text-xl">add</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div id="menu-no-results" class="text-center py-24 glass-panel rounded-sm shadow mt-6 hidden">
                <span class="material-symbols-outlined text-4xl text-[#a3a3a3] mb-4">search_off</span>
                <p class="text-xl font-display text-white">Koleksi tidak ditemukan.</p>
                <p class="text-sm text-[#a3a3a3] mt-2 font-light">Silakan pilih kategori lainnya.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Cinematic Cart Panel -->
<?php if ($table): ?>
    <div id="cart-section"
        class="fixed bottom-0 left-0 w-full z-50 transform translate-y-full transition-transform duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] px-4 sm:px-6 md:px-12 pb-4 pointer-events-none">
        <div
            class="glass-panel border border-primary/30 shadow-gold-glow max-w-4xl mx-auto rounded-xl overflow-hidden pointer-events-auto flex flex-col bg-[#0a0a0a]">

            <!-- Cart Header -->
            <div id="cart-header"
                class="px-6 py-5 flex justify-between items-center cursor-pointer border-b border-white/5 hover:bg-white/5 transition-colors">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">local_mall</span>
                    <h3 class="font-display text-xl tracking-wide text-white">Reservasi Pesanan</h3>
                </div>
                <div class="flex items-center gap-4">
                    <span
                        class="text-xs uppercase tracking-[0.2em] bg-primary/10 text-primary px-3 py-1 rounded-full border border-primary/30"
                        id="cart-item-count">0</span>
                    <span id="cart-toggle-icon"
                        class="material-symbols-outlined text-[#a3a3a3] transform transition-transform duration-500">expand_less</span>
                </div>
            </div>

            <!-- Cart Content -->
            <div id="cart-content"
                class="max-h-0 overflow-hidden transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] flex flex-col">
                <div id="cart-items" class="p-6 overflow-y-auto max-h-[50vh] flex-grow custom-scrollbar"
                    data-lenis-prevent="true">
                    <p class="text-[#a3a3a3] text-sm text-center font-light cart-empty-message">Belum ada koleksi yang
                        dipilih.</p>
                    <?php /* Template Item Cart (di-handle js/customer-menu.js) */ ?>
                </div>
                <div class="p-6 border-t border-white/5 bg-[#050505]">
                    <div class="flex justify-between items-end mb-6">
                        <span class="text-xs uppercase tracking-[0.2em] text-[#a3a3a3]">Total Estimasi</span>
                        <span id="cart-total" class="font-display text-3xl text-primary leading-none">Rp 0</span>
                    </div>
                    <button id="place-order-btn"
                        class="w-full flex items-center justify-center gap-2 px-8 py-4 bg-transparent border border-primary/50 text-primary font-sans text-xs uppercase tracking-[0.2em] hover:bg-gold-gradient hover:text-black hover:border-transparent transition-all duration-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-primary disabled:hover:border-primary/50 rounded-sm shadow-md"
                        disabled>
                        Konfirmasi Pesanan <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>
                    <div id="order-message" class="mt-4 text-xs text-center font-light tracking-wide text-primary"></div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    /* Hide scrollbar for category nav but allow scroll */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Custom scrollbar for cart items */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(233, 193, 118, 0.3);
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(233, 193, 118, 0.8);
    }

    /* Custom cart item overrides for JS injection */
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .cart-item-details h4 {
        font-family: 'Playfair Display', serif;
        font-size: 1.1rem;
        color: white;
        margin-bottom: 0.25rem;
    }

    .cart-item-price {
        color: #a3a3a3;
        font-size: 0.875rem;
    }

    .cart-item-controls {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .cart-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 9999px;
        border: 1px solid rgba(233, 193, 118, 0.5);
        color: #e9c176;
        background: transparent;
        transition: all 0.2s;
    }

    .cart-btn:hover {
        background: #e9c176;
        color: black;
    }

    .cart-qty {
        font-weight: 600;
        color: white;
        min-width: 1.5rem;
        text-align: center;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- Logika Filter Kategori ---
        const categoryTabsContainer = document.getElementById('category-tabs-container');
        const categoryTabs = document.querySelectorAll('.category-tab');
        const menuItemsContainer = document.getElementById('menu-items-container');
        const allMenuItems = menuItemsContainer ? Array.from(menuItemsContainer.querySelectorAll('.menu-item')) : [];
        const noResultsDiv = document.getElementById('menu-no-results');

        if (categoryTabs.length > 0 && menuItemsContainer && allMenuItems.length > 0 && noResultsDiv) {
            categoryTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const filterValue = tab.dataset.filter;

                    // Update styling tab aktif (Cinematic theme)
                    categoryTabs.forEach(t => {
                        t.classList.remove('active', 'border-primary', 'text-primary');
                        t.classList.add('border-transparent', 'text-[#a3a3a3]');
                    });
                    tab.classList.remove('border-transparent', 'text-[#a3a3a3]');
                    tab.classList.add('active', 'border-primary', 'text-primary');

                    let hasVisibleItems = false;
                    allMenuItems.forEach(item => {
                        const itemCategory = item.dataset.category;
                        const matchesFilter = filterValue === 'all' || itemCategory === filterValue;

                        item.style.opacity = matchesFilter ? '1' : '0';
                        item.style.transform = matchesFilter ? 'scale(1) translateY(0)' : 'scale(0.95) translateY(10px)';

                        setTimeout(() => {
                            item.style.display = matchesFilter ? 'flex' : 'none';
                        }, 300);

                        if (matchesFilter) hasVisibleItems = true;
                    });

                    setTimeout(() => {
                        noResultsDiv.style.display = hasVisibleItems ? 'none' : 'block';
                    }, 300);
                });
            });
        }

        // --- Logika Toggle Cart ---
        const cartHeader = document.getElementById('cart-header');
        const cartContent = document.getElementById('cart-content');
        const cartSection = document.getElementById('cart-section');
        const cartToggleIcon = document.getElementById('cart-toggle-icon');
        let isCartOpen = false;

        // In cinematic mode, the cart header is inside a floating panel
        const cartHeaderHeight = cartHeader ? cartHeader.offsetHeight : 72;
        const bottomOffset = 16; // pb-4 padding bottom

        if (cartHeader && cartContent && cartSection && cartToggleIcon) {
            cartHeader.addEventListener('click', () => {
                isCartOpen = !isCartOpen;
                if (isCartOpen) {
                    cartSection.style.transform = `translateY(0)`;
                    cartContent.style.maxHeight = '70vh';
                    cartToggleIcon.style.transform = 'rotate(180deg)';
                } else {
                    cartSection.style.transform = `translateY(calc(100% - ${cartHeaderHeight + bottomOffset}px))`;
                    cartContent.style.maxHeight = '0px';
                    cartToggleIcon.style.transform = 'rotate(0deg)';
                }
            });

            // Initial state: hide content, peek header
            cartSection.style.transform = `translateY(calc(100% - ${cartHeaderHeight + bottomOffset}px))`;
        }

        window.showCartTemporarily = () => {
            if (!isCartOpen && cartHeader) {
                cartHeader.click();
            }
        };
    });
</script>

<!-- Load JS Logic from Project -->
<script src="<?= UrlHelper::baseUrl('js/customer-menu-v2.js') ?>" defer></script>