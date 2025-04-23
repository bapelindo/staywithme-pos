<?php
// Lokasi File: app/views/public/menu.php
// Halaman ini akan dimuat oleh Public\MenuController::index() (belum dibuat controllernya)
// Controller perlu mengirimkan data $categories dan $menuItems (atau $categories berisi $menuItems)

// Tangkap output
ob_start();

// Asumsi data dari controller:
// $title = 'Menu Kami';
// $categories = [...]; // Array of category objects/arrays
// $menuItemsByCategory = [...]; // Array [category_id => [menu_item1, menu_item2]]

// Cek apakah user sudah di sesi meja
$tableNumber = $_SESSION['current_table_number'] ?? null;
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-8"><?= htmlspecialchars($title ?? 'Menu Kami') ?></h1>
    <?php if ($tableNumber): ?>
        <p class="text-center text-md text-gray-600 mb-8 -mt-4">Memesan untuk Meja: <strong><?= htmlspecialchars($tableNumber) ?></strong></p>
    <?php else: ?>
         <p class="text-center text-md text-yellow-700 bg-yellow-100 p-3 rounded mb-8">
            <a href="<?= url_for('/') ?>" class="font-semibold underline hover:text-yellow-800">Scan QR Code Meja</a> terlebih dahulu untuk mulai memesan.
        </p>
    <?php endif; ?>

    <?php if (!empty($categories)): ?>
    <nav class="mb-8 text-center space-x-2 md:space-x-4">
        <a href="#menu-all" class="px-4 py-2 bg-primary text-white rounded-full text-sm font-medium shadow hover:bg-blue-700 transition">Semua</a>
        <?php foreach ($categories as $category): ?>
            <a href="#category-<?= $category->id ?? '' ?>"
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-300 transition">
               <?= htmlspecialchars($category->name ?? 'Kategori') ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>


    <div id="menu-all" class="space-y-10">
        <?php if (!empty($categories) && !empty($menuItemsByCategory)): ?>
            <?php foreach ($categories as $category):
                $categoryId = $category->id ?? null;
                $itemsInCategory = $menuItemsByCategory[$categoryId] ?? [];
                if (empty($itemsInCategory)) continue; // Skip kategori kosong
            ?>
                <section id="category-<?= $categoryId ?>" class="pt-6"> <?php // pt-6 untuk offset anchor link ?>
                    <h2 class="text-2xl font-semibold text-gray-700 mb-6 border-b pb-2">
                        <?= htmlspecialchars($category->name ?? 'Kategori') ?>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($itemsInCategory as $item):
                            $isAvailable = $item->is_available ?? true; // Asumsi default tersedia
                        ?>
                            <div class="menu-item bg-white rounded-lg shadow-md overflow-hidden flex flex-col justify-between border <?= !$isAvailable ? 'opacity-60 bg-gray-100' : '' ?>">
                                <?php if (!empty($item->image_path)): ?>
                                <img src="<?= asset($item->image_path) ?>" alt="<?= htmlspecialchars($item->name) ?>" class="w-full h-40 object-cover">
                                <?php else: ?>
                                 <div class="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-400 italic">Gambar tidak tersedia</div>
                                <?php endif; ?>

                                <div class="p-4 flex-grow">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1"><?= htmlspecialchars($item->name) ?></h3>
                                    <p class="text-sm text-gray-600 mb-3 min-h-[40px]">
                                        <?= htmlspecialchars($item->description ?? '') ?>
                                    </p>
                                </div>

                                <div class="p-4 bg-gray-50 border-t flex justify-between items-center">
                                    <span class="text-lg font-bold text-primary"><?= format_rupiah($item->price ?? 0) ?></span>
                                    <?php if ($tableNumber && $isAvailable): // Hanya tampilkan tombol jika sudah scan meja & item tersedia ?>
                                        <form action="<?= url_for('/cart/add') ?>" method="POST" class="inline-block">
                                            <?php // CSRF Token jika ada ?>
                                            <input type="hidden" name="menu_item_id" value="<?= $item->id ?>">
                                            <input type="hidden" name="quantity" value="1"> <?php // Default quantity 1 ?>
                                            <button type="submit"
                                                    class="bg-secondary hover:bg-green-700 text-white font-medium py-1.5 px-4 rounded-md text-sm transition duration-150 ease-in-out shadow-sm">
                                                + Keranjang
                                            </button>
                                        </form>
                                    <?php elseif (!$isAvailable): ?>
                                         <span class="text-sm font-medium text-red-500 px-3 py-1.5 bg-red-100 rounded-md">Habis</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; // End loop items in category ?>
                    </div>
                </section>
            <?php endforeach; // End loop categories ?>
        <?php else: ?>
            <p class="text-center text-gray-500 text-lg py-10">Yah, sepertinya menu belum tersedia saat ini. Coba cek lagi nanti!</p>
        <?php endif; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
// Script JS untuk menu (misal filter, smooth scroll) bisa ditaruh di $pageScript jika perlu
// $pageScript = '<script>...</script>';
require APPROOT . '/views/layouts/public.php';
?>