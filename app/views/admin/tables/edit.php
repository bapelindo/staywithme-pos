<?php
// Lokasi File: app/views/admin/tables/edit.php
// Dimuat oleh Admin\TableController::edit() & update() jika validasi gagal

ob_start();
// Data: $title, $pageTitle, $table, $errors, $old
$currentTable = $table ?? null; // Data meja saat ini
?>

<div class="max-w-lg mx-auto">
     <a href="<?= url_for('/admin/tables') ?>" class="text-sm text-blue-600 hover:underline mb-4 inline-block">&larr; Kembali ke Daftar Meja</a>

    <?php if ($currentTable): ?>
    <form action="<?= url_for('/admin/tables/update/' . $currentTable->id) ?>" method="POST">
        <?php // CSRF Token ?>
         <?php // Method Spoofing jika ingin pakai PUT ?>
         <?php /* <input type="hidden" name="_method" value="PUT"> */ ?>

        <div class="mb-4">
            <label for="table_number" class="block text-sm font-medium text-gray-700 mb-1">Nomor/Nama Meja <span class="text-red-500">*</span></label>
            <input type="text" id="table_number" name="table_number" required
                   value="<?= htmlspecialchars($old['table_number'] ?? $currentTable->table_number) ?>"
                   class="w-full border <?= isset($errors['table_number']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <?php if (isset($errors['table_number'])): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['table_number'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Kapasitas <span class="text-red-500">*</span></label>
            <input type="number" id="capacity" name="capacity" required min="1" max="50"
                   value="<?= htmlspecialchars($old['capacity'] ?? $currentTable->capacity) ?>"
                   class="w-full border <?= isset($errors['capacity']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
             <?php if (isset($errors['capacity'])): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['capacity'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div class="mb-6">
             <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
             <select id="status" name="status" required
                     class="w-full border <?= isset($errors['status']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                 <?php
                 $currentStatus = $old['status'] ?? $currentTable->status;
                 $statuses = ['available' => 'Tersedia', 'occupied' => 'Terisi', 'reserved' => 'Direservasi'];
                 foreach ($statuses as $value => $label):
                 ?>
                 <option value="<?= $value ?>" <?= ($currentStatus == $value) ? 'selected' : '' ?>><?= $label ?></option>
                 <?php endforeach; ?>
             </select>
              <?php if (isset($errors['status'])): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['status'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">QR Code Identifier</label>
            <input type="text" readonly
                   value="<?= htmlspecialchars($currentTable->qr_code_identifier ?? 'Belum ada') ?>"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-gray-100 text-gray-500">
            <p class="text-xs text-gray-500 mt-1">Identifier dibuat otomatis dan sebaiknya tidak diubah manual.</p>
             <?php if (!empty($currentTable->qr_code_identifier)): ?>
             <form action="<?= url_for('/admin/tables/regenerateIdentifier/' . $currentTable->id) ?>" method="POST" class="inline-block mt-1"
                  onsubmit="return confirm('Buat ulang identifier? URL QR lama tidak akan berfungsi.');">
                <?php // CSRF ?>
                <button type="submit" class="text-xs text-yellow-600 hover:underline">(Generate Ulang)</button>
            </form>
            <?php else: ?>
             <form action="<?= url_for('/admin/tables/regenerateIdentifier/' . $currentTable->id) ?>" method="POST" class="inline-block mt-1">
                 <?php // CSRF ?>
                 <button type="submit" class="text-xs text-blue-600 hover:underline">(Generate Sekarang)</button>
             </form>
            <?php endif; ?>
        </div>


        <div class="flex justify-between items-center">
            <div>
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md text-sm shadow-sm">
                    Update Meja
                </button>
                 <a href="<?= url_for('/admin/tables') ?>" class="text-gray-600 text-sm ml-3 hover:underline">
                    Batal
                </a>
            </div>
             <form action="<?= url_for('/admin/tables/delete/' . $currentTable->id) ?>" method="POST"
                  onsubmit="return confirm('Yakin hapus meja <?= htmlspecialchars($currentTable->table_number) ?>?');">
                 <?php // CSRF ?>
                 <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-sm">
                     Hapus Meja Ini
                 </button>
             </form>
        </div>

    </form>
    <?php else: ?>
        <p class="text-center text-red-500">Data meja tidak ditemukan.</p>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require APPROOT . '/views/layouts/admin.php';
?>