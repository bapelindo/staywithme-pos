<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;

// Data dari TableController
$tables = $tables ?? [];
// $pageTitle sudah diatur layout
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold text-slate-800">Kelola Meja</h2>
    <a href="<?= UrlHelper::baseUrl('/admin/tables/create') ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-md transition shadow-sm">
        + Tambah Meja Baru
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden border border-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nomor Meja</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Deskripsi</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">QR Code</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                <?php if (empty($tables)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-500 text-sm">Belum ada meja yang ditambahkan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tables as $table): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                <?= SanitizeHelper::html($table['table_number']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 max-w-xs truncate" title="<?= SanitizeHelper::html($table['description'] ?? '') ?>">
                                <?= SanitizeHelper::html($table['description'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-medium">
                                <?php if ($table['is_active']): ?>
                                    <span class="px-2.5 py-0.5 rounded-full bg-green-100 text-green-800">Aktif</span>
                                <?php else: ?>
                                     <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                <a href="<?= UrlHelper::baseUrl('/admin/tables/qr/' . $table['id']) ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800 inline-flex items-center text-xs font-medium py-1 px-2 border border-indigo-200 rounded hover:bg-indigo-50" title="Lihat/Cetak QR Code">
                                     <svg class="w-3 h-3 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z" /></svg>
                                     Lihat QR
                                </a>
                                 <span class="text-xs text-slate-400 block mt-0.5" title="Identifier untuk URL"><?= SanitizeHelper::html($table['qr_code_identifier']) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                <a href="<?= UrlHelper::baseUrl('/admin/tables/edit/' . $table['id']) ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit">Edit</a>
                                <form action="<?= UrlHelper::baseUrl('/admin/tables/destroy/' . $table['id']) ?>" method="POST" class="inline-block delete-confirm-form" data-confirm-message="Yakin ingin menghapus meja '<?= SanitizeHelper::html($table['table_number']) ?>'? Pesanan yang terkait mungkin akan bermasalah.">
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