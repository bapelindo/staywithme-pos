<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper; // Untuk getFlashData & displayFlash
use App\Helpers\AuthHelper;

$table = $table ?? null; // Data meja asli
// === PERBAIKAN: Ambil oldInput dari FlashData ===
$oldInputFromSession = SessionHelper::getFlashData('old_input');
// ============================================

if (!$table && !$oldInputFromSession) {
     echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">Meja tidak ditemukan atau data tidak valid.</div>';
     return;
}

$formData = $oldInputFromSession ?? $table;
$pageTitle = "Edit Meja: " . SanitizeHelper::html($table['table_number'] ?? 'Error');
$formActionId = $table['id'] ?? ($formData['id'] ?? null);

if (!$formActionId) {
     echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">Error: ID Meja tidak ditemukan.</div>';
     return;
}
AuthHelper::requireAdmin();
?>

<div class="max-w-lg mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-slate-800"><?= SanitizeHelper::html($pageTitle) ?></h2>
        <a href="<?= UrlHelper::baseUrl('/admin/tables') ?>" class="text-sm text-indigo-600 hover:text-indigo-800 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            Kembali ke Daftar Meja
        </a>
    </div>

    <?php SessionHelper::displayFlash('error'); ?>

    <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200">
        <form action="<?= UrlHelper::baseUrl('/admin/tables/update/' . $formActionId) ?>" method="POST" novalidate>
            <div class="mb-4">
                <label for="table_number" class="block text-sm font-medium text-slate-700 mb-1">Nomor Meja <span class="text-red-500">*</span></label>
                <input type="text" id="table_number" name="table_number" required maxlength="50"
                       value="<?= SanitizeHelper::html($formData['table_number'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi (Opsional)</label>
                <textarea id="description" name="description" rows="3" maxlength="255"
                          class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                ><?= SanitizeHelper::html($formData['description'] ?? '') ?></textarea>
            </div>

             <div class="mb-4 p-3 bg-slate-50 rounded-md border border-slate-200">
                <label class="block text-sm font-medium text-slate-700 mb-1">QR Code</label>
                <p class="text-xs text-slate-500 mb-2">Identifier unik untuk URL QR Code (tidak dapat diubah):</p>
                <code class="text-sm bg-slate-200 text-slate-700 px-2 py-1 rounded"><?= SanitizeHelper::html($table['qr_code_identifier'] ?? 'N/A') // Ambil dari data asli ?></code>
                <a href="<?= UrlHelper::baseUrl('/admin/tables/qr/' . $formActionId) ?>" target="_blank" class="ml-3 text-xs text-indigo-600 hover:underline">(Lihat QR Code)</a>
             </div>

            <div class="mb-6">
                 <label for="is_active" class="flex items-center">
                     <input type="checkbox" id="is_active" name="is_active" value="1"
                            class="h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500"
                            <?= (isset($formData['is_active']) && $formData['is_active'] == 1) ? 'checked' : '' ?>
                     >
                     <span class="ml-2 text-sm text-slate-700">Meja Aktif</span>
                 </label>
            </div>

            <div class="flex justify-end space-x-3 border-t border-slate-200 pt-5 mt-5">
                <a href="<?= UrlHelper::baseUrl('/admin/tables') ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-4 rounded-lg transition border border-slate-300">Batal</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-lg transition shadow-md">Update Meja</button>
            </div>
        </form>
    </div>
</div>