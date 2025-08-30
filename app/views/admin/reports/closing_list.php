<?php
// File: app/Views/admin/reports/closing_list.php

use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\DateHelper;

?>

<div class="container mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">
            <?= SanitizeHelper::html($pageTitle) ?>
        </h1>
        <a href="<?= UrlHelper::baseUrl('admin/reports') ?>" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Laporan Utama
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Sesi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kasir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu Tutup</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah Aktual</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($drawers)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-12 text-gray-500">
                                <p>Belum ada data laporan tutup kasir.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($drawers as $drawer): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?= $drawer['id'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= SanitizeHelper::html($drawer['username']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= DateHelper::formatIndonesian($drawer['closed_at']) ?></td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-gray-800"><?= NumberHelper::format_rupiah($drawer['closing_amount']) ?></td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= UrlHelper::baseUrl('/admin/reports/closing/' . $drawer['id']) ?>" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </div>
</div>