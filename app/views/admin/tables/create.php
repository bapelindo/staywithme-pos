<?php
// Lokasi File: app/views/admin/tables/create.php
// Dimuat oleh Admin\TableController::create() & store() jika validasi gagal

ob_start();
// Data: $title, $pageTitle, $errors, $old
?>

<div class="max-w-lg mx-auto">
    <a href="<?= url_for('/admin/tables') ?>" class="text-sm text-blue-600 hover:underline mb-4 inline-block">&larr; Kembali ke Daftar Meja</a>

    <form action="<?= url_for('/admin/tables/store') ?>" method="POST">
        <?php // CSRF Token ?>

        <div class="mb-4">
            <label for="table_number" class="block text-sm font-medium text-gray-700 mb-1">Nomor/Nama Meja <span class="text-red-500">*</span></label>
            <input type="text" id="table_number" name="table_number" required
                   value="<?= htmlspecialchars($old['table_number'] ?? '') ?>"
                   class="w-full border <?= isset($errors['table_number']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Contoh: M01, V02, Meja Sudut">
             <?php if (isset($errors['table_number'])): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['table_number'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div class="mb-6">
            <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Kapasitas <span class="text-red-500">*</span></label>
            <input type="number" id="capacity" name="capacity" required min="1" max="50"
                   value="<?= htmlspecialchars($old['capacity'] ?? '2') ?>"
                   class="w-full border <?= isset($errors['capacity']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Jumlah orang">
            <?php if (isset($errors['capacity'])): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['capacity'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div class="flex justify-end">
            <a href="<?= url_for('/admin/tables') ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-md text-sm mr-2">
                Batal
            </a>
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md text-sm shadow-sm">
                Simpan Meja
            </button>
        </div>

    </form>
</div>

<?php
$content = ob_get_clean();
require APPROOT . '/views/layouts/admin.php';
?>