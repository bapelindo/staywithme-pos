<?php
// Lokasi File: app/views/admin/categories/index.php
// Dimuat oleh Admin\CategoryController::index() (Controller belum dibuat)

ob_start();
// Data dari controller: $title, $pageTitle, $categories
?>

<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Kelola kategori untuk item menu Anda.</p>
    <a href="<?= url_for('/admin/categories/create') ?>"
       class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out text-sm">
        + Tambah Kategori Baru
    </a>
</div>

<div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
    <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
        <thead>
            <tr class="text-left bg-gray-100 sticky top-0">
                <th class="py-2 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                <th class="py-2 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                <th class="py-2 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                <tr class="hover:bg-gray-50 border-b border-gray-200">
                    <td class="py-3 px-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($category->name) ?></td>
                    <td class="py-3 px-4 text-sm text-gray-600"><?= htmlspecialchars(substr($category->description ?? '', 0, 100)) . (strlen($category->description ?? '') > 100 ? '...' : '') ?></td>
                    <td class="py-3 px-4 text-sm text-center space-x-1 whitespace-nowrap">
                        <a href="<?= url_for('/admin/categories/edit/' . $category->id) ?>" title="Edit Kategori"
                           class="text-indigo-600 hover:text-indigo-900 p-1 inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                        <form action="<?= url_for('/admin/categories/delete/' . $category->id) ?>" method="POST" class="inline-block"
                              onsubmit="return confirm('Yakin hapus kategori \"<?= htmlspecialchars($category->name) ?>\"? Item menu dalam kategori ini mungkin tidak bisa dihapus.');">
                            <?php // CSRF ?>
                             <?php // Method spoofing DELETE jika diperlukan ?>
                             <?php /* <input type="hidden" name="_method" value="DELETE"> */ ?>
                            <button type="submit" title="Hapus Kategori" class="text-red-600 hover:text-red-900 p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center py-10 text-gray-500 italic">Belum ada data kategori menu.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php
$content = ob_get_clean();
require APPROOT . '/views/layouts/admin.php';
?>