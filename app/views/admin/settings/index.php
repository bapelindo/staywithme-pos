<?php
// File: app/Views/admin/settings/index.php

use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper;

$settings = $settings ?? [];
?>

<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-slate-800"><?= SanitizeHelper::html($pageTitle) ?></h2>
    </div>

    <?php SessionHelper::displayAllFlashMessages(); ?>

    <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200">
        <form action="<?= UrlHelper::baseUrl('/admin/settings/update') ?>" method="POST">
            <p class="text-sm text-slate-600 mb-6">
                Atur persentase dan nilai default yang akan digunakan dalam perhitungan laporan keuangan.
                Gunakan titik (.) sebagai pemisah desimal.
            </p>

            <fieldset class="mb-6">
                <legend class="text-lg font-semibold text-slate-700 mb-4 border-b pb-2">Komponen Biaya Pokok & Pendapatan</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="cogs_percentage" class="block text-sm font-medium text-slate-700 mb-1">Persentase HPP (%)</label>
                        <input type="number" step="0.01" id="cogs_percentage" name="cogs_percentage" value="<?= SanitizeHelper::html($settings['cogs_percentage'] ?? '40') ?>" class="w-full form-input">
                        <p class="text-xs text-slate-500 mt-1">Estimasi Harga Pokok Penjualan dari total pendapatan.</p>
                    </div>
                     <div>
                        <label for="tax_percentage" class="block text-sm font-medium text-slate-700 mb-1">Persentase Pajak (%)</label>
                        <input type="number" step="0.01" id="tax_percentage" name="tax_percentage" value="<?= SanitizeHelper::html($settings['tax_percentage'] ?? '11') ?>" class="w-full form-input">
                        <p class="text-xs text-slate-500 mt-1">Pajak yang ditambahkan ke penjualan kotor.</p>
                    </div>
                     <div>
                        <label for="service_charge_percentage" class="block text-sm font-medium text-slate-700 mb-1">Persentase Biaya Layanan (%)</label>
                        <input type="number" step="0.01" id="service_charge_percentage" name="service_charge_percentage" value="<?= SanitizeHelper::html($settings['service_charge_percentage'] ?? '5') ?>" class="w-full form-input">
                        <p class="text-xs text-slate-500 mt-1">Biaya layanan yang ditambahkan ke penjualan kotor.</p>
                    </div>
                </div>
            </fieldset>
            
            <fieldset>
                <legend class="text-lg font-semibold text-slate-700 mb-4 border-b pb-2">Biaya Operasional Lainnya (Default)</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="default_promo_percentage" class="block text-sm font-medium text-slate-700 mb-1">Persentase Promo (%)</label>
                        <input type="number" step="0.01" id="default_promo_percentage" name="default_promo_percentage" value="<?= SanitizeHelper::html($settings['default_promo_percentage'] ?? '0') ?>" class="w-full form-input">
                         <p class="text-xs text-slate-500 mt-1">Estimasi biaya promosi dari total pendapatan.</p>
                    </div>
                     <div>
                        <label for="default_admin_fee" class="block text-sm font-medium text-slate-700 mb-1">Biaya Admin (Rp)</label>
                        <input type="number" step="0.01" id="default_admin_fee" name="default_admin_fee" value="<?= SanitizeHelper::html($settings['default_admin_fee'] ?? '0') ?>" class="w-full form-input">
                        <p class="text-xs text-slate-500 mt-1">Biaya admin tetap per periode laporan.</p>
                    </div>
                      <div>
                        <label for="default_mdr_fee_percentage" class="block text-sm font-medium text-slate-700 mb-1">Persentase Biaya MDR (%)</label>
                        <input type="number" step="0.01" id="default_mdr_fee_percentage" name="default_mdr_fee_percentage" value="<?= SanitizeHelper::html($settings['default_mdr_fee_percentage'] ?? '0') ?>" class="w-full form-input">
                        <p class="text-xs text-slate-500 mt-1">Estimasi MDR dari total pendapatan.</p>
                    </div>
                     <div>
                        <label for="default_commission_percentage" class="block text-sm font-medium text-slate-700 mb-1">Persentase Komisi (%)</label>
                        <input type="number" step="0.01" id="default_commission_percentage" name="default_commission_percentage" value="<?= SanitizeHelper::html($settings['default_commission_percentage'] ?? '0') ?>" class="w-full form-input">
                        <p class="text-xs text-slate-500 mt-1">Estimasi komisi dari total pendapatan.</p>
                    </div>
                </div>
            </fieldset>

            <div class="flex justify-end border-t border-slate-200 pt-5 mt-6">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-6 rounded-lg transition shadow-md">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>