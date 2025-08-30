<?php
// Menggunakan Helper yang relevan
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper; // Untuk getFlashData & displayFlash

// Data dari MenuController
$categories = $categories ?? [];
// PERBAIKAN: Ambil oldInput dari FlashData
$oldInput = SessionHelper::getFlashData('old_input') ?? [];
?>

<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-slate-800">Tambah Item Menu Baru</h2>
        <a href="<?= UrlHelper::baseUrl('/admin/menu') ?>" class="text-sm text-indigo-600 hover:text-indigo-800 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            Kembali ke Daftar Menu
        </a>
    </div>

    <?php SessionHelper::displayFlash('error'); ?>

    <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200">
        <form action="<?= UrlHelper::baseUrl('/admin/menu/store') ?>" method="POST" enctype="multipart/form-data" novalidate>
            <div class="mb-4">
                <label for="category_id" class="block text-sm font-medium text-slate-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                <select id="category_id" name="category_id" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm appearance-none">
                    <option value="" disabled <?= empty($oldInput['category_id'] ?? null) ? 'selected' : '' ?>>-- Pilih Kategori --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= SanitizeHelper::html($category['id']) ?>"
                                <?= (isset($oldInput['category_id']) && $oldInput['category_id'] == $category['id']) ? 'selected' : '' ?>>
                            <?= SanitizeHelper::html($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nama Item <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" required maxlength="150"
                       value="<?= SanitizeHelper::html($oldInput['name'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                       placeholder="Contoh: Kopi Susu Gula Aren">
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi (Opsional)</label>
                <textarea id="description" name="description" rows="3" maxlength="500"
                          class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                          placeholder="Penjelasan singkat tentang item menu..."
                ><?= SanitizeHelper::html($oldInput['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="price" class="block text-sm font-medium text-slate-700 mb-1">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                    <input type="text" id="price" name="price" required inputmode="numeric"
                           value="<?= SanitizeHelper::html($oldInput['price'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                           placeholder="Contoh: 18000">
                    <p class="text-xs text-slate-500 mt-1">Harga yang ditampilkan ke pelanggan.</p>
                </div>
                <div>
                    <label for="cost" class="block text-sm font-medium text-slate-700 mb-1">HPP / Modal (Rp)</label>
                    <input type="text" id="cost" name="cost" inputmode="numeric"
                           value="<?= SanitizeHelper::html($oldInput['cost'] ?? '0') ?>"
                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                           placeholder="Contoh: 8000">
                    <p class="text-xs text-slate-500 mt-1">Modal untuk perhitungan laba. Opsional.</p>
                </div>
            </div>

            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-slate-700 mb-1">Gambar Item (Opsional)</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/avif"
                       class="block w-full text-sm text-slate-500 border border-slate-300 rounded-lg cursor-pointer focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG, WEBP, AVIF. Maksimal 1MB.</p>
            </div>

            <div class="mb-6">
                 <label for="is_available" class="flex items-center">
                     <input type="checkbox" id="is_available" name="is_available" value="1"
                            class="h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500"
                            <?= (isset($oldInput['is_available']) || !isset($oldInput) || empty($oldInput)) ? 'checked' : '' ?>
                     >
                     <span class="ml-2 text-sm text-slate-700">Item Tersedia</span>
                 </label>
                 <p class="text-xs text-slate-500 mt-1">Hilangkan centang jika item sedang habis.</p>
            </div>

            <div class="flex justify-end space-x-3 border-t border-slate-200 pt-5 mt-5">
                <a href="<?= UrlHelper::baseUrl('/admin/menu') ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-4 rounded-lg transition border border-slate-300">
                    Batal
                </a>
                 <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-lg transition shadow-md">
                    Simpan Item Menu
                </button>
            </div>

        </form>
    </div>
</div>