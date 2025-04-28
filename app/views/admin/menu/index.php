<?php
// Menggunakan helper untuk URL, sanitasi, dan format angka
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;
use App\Helpers\SessionHelper; // Untuk menampilkan pesan sukses/error

// Data dari MenuController->index()
$menuItems = $menuItems ?? [];
// $pageTitle sudah diatur oleh layout admin
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold text-slate-800">Kelola Item Menu</h2>
    <div>
        <a href="<?= UrlHelper::baseUrl('/admin/categories') ?>" class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-sm font-medium py-2 px-4 rounded-md transition shadow-sm mr-2">
            Kelola Kategori
        </a>
        <a href="<?= UrlHelper::baseUrl('/admin/menu/create') ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-md transition shadow-sm">
            Tambah Item Baru
        </a>
    </div>
</div>

<?php SessionHelper::displayAllFlashMessages(); ?>

<div class="bg-white shadow-md rounded-lg overflow-hidden border border-slate-200">
    <div class="overflow-x-auto menu-items-table"> <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider w-20">Gambar</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Item</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kategori</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Harga</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                <?php if (empty($menuItems)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500 text-sm">Belum ada item menu. Silakan tambahkan item baru.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($menuItems as $item): ?>
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-center align-middle">
                                <?php $imgUrl = UrlHelper::baseUrl(SanitizeHelper::html($item['image_path'] ?? 'images/default_menu_thumb.png')); ?>
                                <img src="<?= $imgUrl ?>" alt="<?= SanitizeHelper::html($item['name']) ?>" class="h-12 w-16 object-cover rounded-md inline-block border border-slate-200">
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm font-medium text-slate-900">
                                <?= SanitizeHelper::html($item['name']) ?>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-slate-600">
                                <?= SanitizeHelper::html($item['category_name'] ?? 'N/A') ?>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-sm text-slate-800 text-right">
                                <?= NumberHelper::formatCurrencyIDR($item['price']) ?>
                            </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-center text-xs font-medium">
                                <?php
                                    $isAvailable = (bool) $item['is_available'];
                                    $buttonClass = $isAvailable ? 'border-green-400 bg-green-50 text-green-700 hover:bg-green-100' : 'border-red-400 bg-red-50 text-red-700 hover:bg-red-100';
                                    $indicatorClass = $isAvailable ? 'bg-green-500' : 'bg-red-500';
                                    $text = $isAvailable ? 'Tersedia' : 'Habis';
                                ?>
                                <button
                                    class="toggle-availability-btn inline-flex items-center border <?= $buttonClass ?> text-xs font-semibold px-2.5 py-1 rounded-full transition duration-150 cursor-pointer"
                                    data-item-id="<?= $item['id'] ?>"
                                    title="Klik untuk mengubah status ketersediaan">
                                    <span class="availability-indicator w-2 h-2 mr-1.5 rounded-full <?= $indicatorClass ?>"></span>
                                    <span class="availability-text"><?= $text ?></span>
                                </button>
                                <span class="temp-message text-xs ml-2"></span> </td>
                            <td class="px-6 py-4 align-middle whitespace-nowrap text-right text-sm font-medium space-x-3">
                                <a href="<?= UrlHelper::baseUrl('/admin/menu/edit/' . $item['id']) ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit">Edit</a>
                                <form action="<?= UrlHelper::baseUrl('/admin/menu/destroy/' . $item['id']) ?>" method="POST" class="inline-block delete-confirm-form" data-confirm-message="Yakin ingin menghapus item menu '<?= SanitizeHelper::html($item['name']) ?>'?">
                                     <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="<?= UrlHelper::baseUrl('js/admin-menu.js') ?>" defer></script>