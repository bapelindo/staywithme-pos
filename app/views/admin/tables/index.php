<?php
// Lokasi File: app/views/admin/tables/index.php
// Dimuat oleh Admin\TableController::index()

ob_start();
// Data: $title, $pageTitle, $tables, $qrCodes
?>

<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Daftar semua meja yang terdaftar di sistem.</p>
    <a href="<?= url_for('/admin/tables/create') ?>"
       class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out text-sm">
        + Tambah Meja Baru
    </a>
</div>

<div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
    <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
        <thead>
            <tr class="text-left bg-gray-100 sticky top-0">
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider">No Meja</th>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Kapasitas</th>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider">QR Code Identifier</th>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Tampilan QR</th>
                <th class="py-2 px-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tables)): ?>
                <?php foreach ($tables as $table):
                    $qrCodeResult = $qrCodes[$table->id] ?? null;
                ?>
                <tr class="hover:bg-gray-50 border-b border-gray-200">
                    <td class="py-3 px-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($table->table_number) ?></td>
                    <td class="py-3 px-3 text-sm text-gray-600"><?= htmlspecialchars($table->capacity) ?> orang</td>
                    <td class="py-3 px-3 text-sm">
                         <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                            <?= ($table->status == 'available') ? 'bg-green-100 text-green-800' : (($table->status == 'occupied') ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                            <?= ucfirst(htmlspecialchars($table->status)) ?>
                         </span>
                    </td>
                    <td class="py-3 px-3 text-xs text-gray-500 font-mono">
                        <?= htmlspecialchars($table->qr_code_identifier ?? 'Belum ada') ?>
                        <?php if(empty($table->qr_code_identifier)): ?>
                             <form action="<?= url_for('/admin/tables/regenerateIdentifier/' . $table->id) ?>" method="POST" class="inline">
                                 <?php // CSRF ?>
                                 <button type="submit" class="text-blue-500 hover:underline text-xs ml-1">(Generate)</button>
                             </form>
                        <?php endif; ?>
                    </td>
                    <td class="py-1 px-3 text-center">
                        <?php if ($qrCodeResult): ?>
                            <img src="<?= $qrCodeResult->getDataUri() ?>" alt="QR Code Meja <?= htmlspecialchars($table->table_number) ?>" class="w-16 h-16 inline-block">
                        <?php elseif(!empty($table->qr_code_identifier)): ?>
                            <span class="text-red-500 text-xs italic">(Gagal generate)</span>
                        <?php else: ?>
                             <span class="text-gray-400 text-xs italic">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-3 text-sm text-center space-x-1 whitespace-nowrap">
                         <?php if ($qrCodeResult): ?>
                            <a href="<?= url_for('/admin/tables/downloadQr/' . $table->id) ?>" title="Download QR"
                               class="text-blue-500 hover:text-blue-700 p-1 inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            </a>
                        <?php endif; ?>
                        <a href="<?= url_for('/admin/tables/edit/' . $table->id) ?>" title="Edit Meja"
                           class="text-indigo-600 hover:text-indigo-900 p-1 inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                        <form action="<?= url_for('/admin/tables/delete/' . $table->id) ?>" method="POST" class="inline-block"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus meja <?= htmlspecialchars($table->table_number) ?>? Aksi ini tidak bisa dibatalkan.');">
                            <?php // CSRF ?>
                            <button type="submit" title="Hapus Meja" class="text-red-600 hover:text-red-900 p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                         <?php if (!empty($table->qr_code_identifier)): // Tombol regenerate jika sudah ada ?>
                             <form action="<?= url_for('/admin/tables/regenerateIdentifier/' . $table->id) ?>" method="POST" class="inline-block"
                                  onsubmit="return confirm('Apakah Anda yakin ingin membuat ulang QR Code Identifier untuk meja <?= htmlspecialchars($table->table_number) ?>? URL QR lama tidak akan berfungsi lagi.');">
                                <?php // CSRF ?>
                                <button type="submit" title="Generate Ulang Identifier" class="text-yellow-600 hover:text-yellow-800 p-1">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m-15.357-2a8.001 8.001 0 0015.357 2m0 0H15" /></svg>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-10 text-gray-500 italic">Belum ada data meja.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
// $pageScript = "<script> // JS untuk konfirmasi delete </script>";
require APPROOT . '/views/layouts/admin.php';
?>