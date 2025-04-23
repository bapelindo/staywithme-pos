<?php
// Lokasi File: app/views/admin/categories/form.php
// Dimuat oleh Admin\CategoryController::create(), store(), edit(), update()

ob_start();
// Data: $title, $pageTitle, $errors, $old, $category (opsional, untuk edit)
$isEdit = isset($category) && $category->id;
$formAction = $isEdit ? url_for('/admin/categories/update/' . $category->id) : url_for('/admin/categories/store');
?>

<div class="max-w-xl mx-auto">
    <a href="<?= url_for('/admin/categories') ?>" class="text-sm text-blue-600 hover:underline mb-4 inline-block">&larr; Kembali ke Daftar Kategori</a>

    <form action="<?= $formAction ?>" method="POST">
        <?php // CSRF Token ?>
        <?php if ($isEdit) echo '<input type="hidden" name="_method" value="PUT">'; // Method spoofing ?>

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" required maxlength="100"
                   value="<?= htmlspecialchars($old['name'] ?? ($category->name ?? '')) ?>"
                   class="w-full border <?= isset($errors['name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Contoh: Kopi, Makanan Berat">
             <?php if (isset($errors['name'])): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['name'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi (Opsional)</label>
            <textarea id="description" name="description" rows="4"
                      class="w-full border <?= isset($errors['description']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Penjelasan singkat tentang kategori ini..."
                      ><?= htmlspecialchars($old['description'] ?? ($category->description ?? '')) ?></textarea>
             <?php if (isset($errors['description'])): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['description'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div class="flex justify-end">
            <a href="<?= url_for('/admin/categories') ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-md text-sm mr-2">
                Batal
            </a>
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md text-sm shadow-sm">
                <?= $isEdit ? 'Update Kategori' : 'Simpan Kategori Baru' ?>
            </button>
        </div>

    </form>
</div>

<?php
$content = ob_get_clean();
require APPROOT . '/views/layouts/admin.php';
?>