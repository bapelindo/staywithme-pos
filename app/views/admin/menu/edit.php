<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper;
use App\Helpers\NumberHelper; // Tidak perlu di sini

// Data dari MenuController
$categories = $categories ?? [];
$menuItem = $menuItem ?? null; // Data item asli dari DB
// Ambil old input dari flash data (jika ada, karena gagal update sebelumnya)
$oldInputFromSession = SessionHelper::getFlashData('old_input');

if (!$menuItem && !$oldInputFromSession) {
     // Jika tidak ada data asli DAN tidak ada old input, tampilkan error
     echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">Item menu tidak ditemukan atau data tidak valid.</div>';
     // Sebaiknya redirect dari Controller jika $menuItem benar-benar null
     return; // Hentikan render view
}

// Prioritaskan old input dari session jika ada, jika tidak gunakan data asli dari $menuItem
$formData = $oldInputFromSession ?? $menuItem;
$pageTitle = "Edit Item Menu: " . SanitizeHelper::html($menuItem['name'] ?? 'Error'); // Ambil nama asli untuk judul
$formActionId = $menuItem['id'] ?? ($formData['id'] ?? null); // Pastikan ID untuk action form ada

if (!$formActionId) {
     echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">Error: ID Item tidak ditemukan untuk form action.</div>';
     return;
}
?>

<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-slate-800"><?= SanitizeHelper::html($pageTitle) ?></h2>
        <a href="<?= UrlHelper::baseUrl('/admin/menu') ?>" class="text-sm text-indigo-600 hover:text-indigo-800 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            Kembali ke Daftar Menu
        </a>
    </div>

    <?php SessionHelper::displayFlash('error', 'mb-4 p-4 rounded-md text-sm border', ['error' => 'bg-red-100 border-red-300 text-red-800']); ?>

    <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200">
        <form action="<?= UrlHelper::baseUrl('/admin/menu/update/' . $formActionId) ?>" method="POST" enctype="multipart/form-data" novalidate>
            <div class="mb-4">
                <label for="category_id" class="block text-sm font-medium text-slate-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                <select id="category_id" name="category_id" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm appearance-none">
                    <option value="" disabled>-- Pilih Kategori --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= SanitizeHelper::html($category['id']) ?>"
                                <?= (isset($formData['category_id']) && $formData['category_id'] == $category['id']) ? 'selected' : '' ?>>
                            <?= SanitizeHelper::html($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nama Item <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" required maxlength="150"
                       value="<?= SanitizeHelper::html($formData['name'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi (Opsional)</label>
                <textarea id="description" name="description" rows="3" maxlength="500"
                          class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                ><?= SanitizeHelper::html($formData['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-4">
                <label for="price" class="block text-sm font-medium text-slate-700 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                <input type="text" id="price" name="price" required inputmode="numeric"
                       value="<?= SanitizeHelper::html($formData['price'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                       placeholder="Contoh: 18000">
            </div>

            <div class="mb-4 border-t pt-4 mt-4 border-slate-200">
                 <label class="block text-sm font-medium text-slate-700 mb-2">Gambar Item</label>
                <?php $currentImagePath = $menuItem['image_path'] ?? null; // Ambil dari data asli $menuItem ?>
                <?php if (!empty($currentImagePath)): ?>
                    <div class="mb-3 p-2 border border-dashed border-slate-300 rounded-md inline-block">
                         <p class="text-xs text-slate-500 mb-1">Gambar Saat Ini:</p>
                        <img src="<?= UrlHelper::asset(SanitizeHelper::html($currentImagePath)) ?>" alt="Gambar saat ini" class="h-20 w-auto rounded border border-slate-200">
                        <label class="flex items-center mt-1.5">
                             <input type="checkbox" name="remove_image" value="1" class="h-4 w-4 text-red-600 border-slate-300 rounded focus:ring-red-500">
                             <span class="ml-1.5 text-xs text-red-700">Hapus gambar saat ini</span>
                        </label>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-slate-500 mb-2">Belum ada gambar.</p>
                <?php endif; ?>
                 <label for="image" class="block text-sm font-medium text-slate-700 mb-1 mt-3">Ganti/Upload Gambar Baru (Opsional)</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/avif"
                       class="block w-full text-sm text-slate-500 border border-slate-300 rounded-lg cursor-pointer focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-slate-500 mt-1">Biarkan kosong jika tidak ingin mengganti gambar. Format: JPG, PNG, WEBP, AVIF. Maks 1MB.</p>
            </div>

            <div class="mb-6 border-t pt-4 mt-4 border-slate-200">
                 <label for="is_available" class="flex items-center">
                     <input type="checkbox" id="is_available" name="is_available" value="1"
                            class="h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500"
                            <?= (isset($formData['is_available']) && $formData['is_available'] == 1) ? 'checked' : '' ?>
                     >
                     <span class="ml-2 text-sm text-slate-700">Item Tersedia</span>
                 </label>
            </div>

            <div class="flex justify-end space-x-3 border-t border-slate-200 pt-5 mt-5">
                <a href="<?= UrlHelper::baseUrl('/admin/menu') ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-4 rounded-lg transition border border-slate-300">
                    Batal
                </a>
                 <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-lg transition shadow-md">
                    Update Item Menu
                </button>
            </div>

        </form>
    </div>
</div>