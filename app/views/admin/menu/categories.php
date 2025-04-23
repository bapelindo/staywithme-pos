<?php
// Menggunakan Helper yang relevan
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper; // Untuk menampilkan pesan flash

// Data dari MenuController->categories()
$categories = $categories ?? [];
// $pageTitle sudah diatur oleh layout admin
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold text-slate-800">Kelola Kategori Menu</h2>
    <a href="<?= UrlHelper::baseUrl('/admin/menu') ?>" class="text-sm text-indigo-600 hover:text-indigo-800 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        Kembali ke Daftar Menu
    </a>
</div>

<?php SessionHelper::displayFlash('success_category', 'p-4 mb-4 rounded-md text-sm border', ['success' => 'bg-green-100 border-green-300 text-green-800']); ?>
<?php SessionHelper::displayFlash('error_category', 'p-4 mb-4 rounded-md text-sm border', ['error' => 'bg-red-100 border-red-300 text-red-800']); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200 sticky top-20">
             <h3 class="text-lg font-semibold text-slate-700 mb-4">Tambah Kategori Baru</h3>
            <form action="<?= UrlHelper::baseUrl('/admin/categories/store') ?>" method="POST" novalidate>
                 <div class="mb-3">
                    <label for="new_name" class="block text-sm font-medium text-slate-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" id="new_name" name="name" required maxlength="100"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm"
                           placeholder="Makanan Utama">
                 </div>
                 <div class="mb-3">
                    <label for="new_description" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                    <textarea id="new_description" name="description" rows="2" maxlength="255"
                              class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm"
                              placeholder="Penjelasan singkat..."></textarea>
                 </div>
                 <div class="mb-4">
                    <label for="new_sort_order" class="block text-sm font-medium text-slate-700 mb-1">Urutan Tampil</label>
                    <input type="number" id="new_sort_order" name="sort_order" value="0" min="0"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm">
                     <p class="text-xs text-slate-500 mt-1">Angka kecil tampil dulu.</p>
                 </div>
                 <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-md transition shadow-md">
                    + Tambah Kategori
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-slate-200">
             <h3 class="text-lg font-semibold text-slate-700 p-4 border-b border-slate-200">Daftar Kategori</h3>
            <div class="overflow-x-auto">
                <?php if (empty($categories)): ?>
                     <p class="px-6 py-10 text-center text-slate-500 text-sm">Belum ada kategori menu.</p>
                <?php else: ?>
                    <table class="min-w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Detail Kategori (Nama, Deskripsi, Urutan)</th>
                                <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td class="px-4 py-3 align-top">
                                            <form action="<?= UrlHelper::baseUrl('/admin/categories/update/' . $category['id']) ?>" method="POST" class="category-edit-form space-y-2" id="edit-form-<?= $category['id'] ?>">
                                                <div>
                                                      <label for="name_<?= $category['id'] ?>" class="sr-only">Nama</label>
                                                      <input type="text" id="name_<?= $category['id'] ?>" name="name" value="<?= SanitizeHelper::html($category['name']) ?>" required maxlength="100" placeholder="Nama Kategori" class="category-input w-full text-sm font-medium text-slate-900 border border-slate-300 rounded px-2 py-1 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white">
                                                 </div>
                                                 <div>
                                                      <label for="desc_<?= $category['id'] ?>" class="sr-only">Deskripsi</label>
                                                      <textarea id="desc_<?= $category['id'] ?>" name="description" rows="2" maxlength="255" class="category-input w-full text-xs text-slate-600 border border-slate-300 rounded px-2 py-1 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white" placeholder="Deskripsi..."><?= SanitizeHelper::html($category['description'] ?? '') ?></textarea>
                                                 </div>
                                                 <div>
                                                    <label for="sort_<?= $category['id'] ?>" class="text-xs text-slate-500 mr-1">Urutan:</label>
                                                    <input type="number" id="sort_<?= $category['id'] ?>" name="sort_order" value="<?= SanitizeHelper::html($category['sort_order']) ?>" min="0" class="category-input w-20 text-xs text-slate-600 border border-slate-300 rounded px-2 py-1 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white">
                                                 </div>
                                            </form>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center align-middle text-sm font-medium space-y-2">
                                             <button type="submit" form="edit-form-<?= $category['id'] ?>" class="inline-flex items-center justify-center bg-green-100 hover:bg-green-200 text-green-700 px-2 py-1 text-xs rounded" title="Simpan Perubahan">
                                                  <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                  <span class="ml-1 hidden sm:inline">Simpan</span>
                                             </button>
                                             <form action="<?= UrlHelper::baseUrl('/admin/categories/destroy/' . $category['id']) ?>" method="POST" class="inline-block delete-confirm-form" data-confirm-message="Yakin ingin menghapus kategori '<?= SanitizeHelper::html($category['name']) ?>'?">
                                                 <button type="submit" class="inline-flex items-center justify-center bg-red-100 hover:bg-red-200 text-red-700 px-2 py-1 text-xs rounded" title="Hapus">
                                                      <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                                      <span class="ml-1 hidden sm:inline">Hapus</span>
                                                 </button>
                                             </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                        </tbody>
                    </table>
                 <?php endif; ?>
            </div>
        </div>
    </div>

</div>