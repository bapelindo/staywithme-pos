<?php
// File: app/Views/admin/cashier/index.php (Perbaikan Final Dropdown)

use App\Helpers\SessionHelper;
use App\Helpers\NumberHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SanitizeHelper;

$openDrawer = $openDrawer ?? null;
$todaysDrawers = $todaysDrawers ?? [];

// Kategori didefinisikan di PHP
$cashInCategories = [
    'Modal Tambahan',
    'Pendapatan Non-Penjualan',
    'Pengembalian Dana (Refund) dari Supplier',
    'Lainnya'
];
$cashOutCategories = [
    'Belanja Bahan Baku (Kopi, Susu, Sirup)',
    'Belanja Bahan Makanan (Daging, Sayur)',
    'Belanja Kebutuhan Dapur (Gas, Plastik)',
    'Belanja ATK & Fotokopi',
    'Gaji Karyawan',
    'Kasbon Karyawan',
    'Biaya Listrik',
    'Biaya Air',
    'Biaya Internet',
    'Biaya Sewa',
    'Biaya Kebersihan & Keamanan',
    'Biaya Perbaikan & Perawatan Alat',
    'Biaya Marketing & Promosi',
    'Transportasi & Bensin',
    'Pajak & Retribusi',
    'Pengeluaran Tak Terduga',
    'Lainnya'
];
?>

<div x-data="{ modalOpen: false, modalType: '' }">
    <div class="container mx-auto">
        <h1 class="text-2xl font-semibold text-slate-800 mb-6"><?= SanitizeHelper::html($pageTitle ?? 'Manajemen Kasir') ?></h1>

        <?php SessionHelper::displayAllFlashMessages(); ?>

        <?php if ($openDrawer): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-700 mb-4">Transaksi Kas</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <button @click="modalType = 'in'; modalOpen = true;" class="w-full text-center bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition">
                            <i class="fas fa-arrow-down mr-2"></i> Kas Masuk
                        </button>
                        <button @click="modalType = 'out'; modalOpen = true;" class="w-full text-center bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg transition">
                            <i class="fas fa-arrow-up mr-2"></i> Kas Keluar
                        </button>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-700 mb-2">Tutup Sesi Kasir</h2>
                    <p class="text-sm text-slate-500 mb-4">Sesi dibuka pada <?= SanitizeHelper::html($openDrawer['opened_at']) ?> dengan modal <?= NumberHelper::format_rupiah($openDrawer['opening_amount']) ?>.</p>
                    <form action="<?= UrlHelper::baseUrl('/admin/cashier/close') ?>" method="POST">
                        <div class="mb-4">
                            <label for="closing_amount" class="block text-sm font-medium text-slate-700 mb-1">Jumlah Uang Akhir (Rp) <span class="text-red-500">*</span></label>
                            <input type="text" id="closing_amount" name="closing_amount" class="form-input w-full" required placeholder="Contoh: 1500000" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                         <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                            <textarea id="notes" name="notes" rows="2" class="form-input w-full" placeholder="Catatan penutupan (opsional)"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">
                            Tutup Sesi
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white p-6 rounded-lg shadow-md mb-6 max-w-md mx-auto">
                <h2 class="text-xl font-bold mb-4 text-center text-slate-800">Buka Sesi Kasir</h2>
                <p class="text-sm text-center text-slate-500 mb-6">Anda harus membuka sesi kasir baru untuk dapat melakukan transaksi.</p>
                <form action="<?= UrlHelper::baseUrl('/admin/cashier/open') ?>" method="POST">
                    <div class="mb-4">
                        <label for="opening_amount" class="block text-sm font-medium text-slate-700 mb-1">Modal Awal (Rp) <span class="text-red-500">*</span></label>
                        <input type="text" id="opening_amount" name="opening_amount" class="form-input w-full text-center text-lg" required placeholder="Contoh: 500000" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition text-lg">
                        Buka Kasir
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4 text-slate-800">Riwayat Sesi Kasir Hari Ini</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-medium text-slate-500 uppercase">Pengguna</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-slate-500 uppercase">Waktu Buka</th>
                            <th class="py-3 px-4 text-right text-xs font-medium text-slate-500 uppercase">Modal Awal</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-slate-500 uppercase">Waktu Tutup</th>
                            <th class="py-3 px-4 text-right text-xs font-medium text-slate-500 uppercase">Jumlah Akhir</th>
                            <th class="py-3 px-4 text-center text-xs font-medium text-slate-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 divide-y divide-slate-200">
                        <?php if (empty($todaysDrawers)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-500">Belum ada aktivitas kasir hari ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($todaysDrawers as $drawer): ?>
                                <tr>
                                    <td class="py-3 px-4 text-sm"><?= SanitizeHelper::html($drawer['username']) ?></td>
                                    <td class="py-3 px-4 text-sm"><?= SanitizeHelper::html($drawer['opened_at']) ?></td>
                                    <td class="py-3 px-4 text-sm text-right"><?= NumberHelper::format_rupiah($drawer['opening_amount']) ?></td>
                                    <td class="py-3 px-4 text-sm"><?= $drawer['closed_at'] ? SanitizeHelper::html($drawer['closed_at']) : '-' ?></td>
                                    <td class="py-3 px-4 text-sm text-right"><?= $drawer['closing_amount'] ? NumberHelper::format_rupiah($drawer['closing_amount']) : '-' ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $drawer['status'] == 'open' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' ?>">
                                            <?= SanitizeHelper::html(ucfirst($drawer['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalOpen = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                
                <div x-show="modalType === 'in'">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Catat Kas Masuk</h3>
                    <form action="<?= UrlHelper::baseUrl('/admin/cashier/cash_in') ?>" method="POST" class="mt-4">
                        <div class="mb-4">
                            <label for="modal_amount_in" class="block text-sm font-medium text-slate-700 mb-1">Jumlah (Rp) <span class="text-red-500">*</span></label>
                            <input type="text" id="modal_amount_in" name="amount" class="form-input w-full" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="mb-4">
                            <label for="modal_category_in" class="block text-sm font-medium text-slate-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                            <select id="modal_category_in" name="category" class="form-select w-full" required>
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <?php foreach ($cashInCategories as $cat): ?>
                                    <option value="<?= SanitizeHelper::html($cat) ?>"><?= SanitizeHelper::html($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="modal_notes_in" class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                            <textarea id="modal_notes_in" name="notes" rows="3" class="form-input w-full" placeholder="Keterangan tambahan (opsional)"></textarea>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">Batal</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700">Simpan Transaksi</button>
                        </div>
                    </form>
                </div>

                <div x-show="modalType === 'out'">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Catat Kas Keluar</h3>
                    <form action="<?= UrlHelper::baseUrl('/admin/cashier/cash_out') ?>" method="POST" class="mt-4">
                        <div class="mb-4">
                            <label for="modal_amount_out" class="block text-sm font-medium text-slate-700 mb-1">Jumlah (Rp) <span class="text-red-500">*</span></label>
                            <input type="text" id="modal_amount_out" name="amount" class="form-input w-full" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="mb-4">
                            <label for="modal_category_out" class="block text-sm font-medium text-slate-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                            <select id="modal_category_out" name="category" class="form-select w-full" required>
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <?php foreach ($cashOutCategories as $cat): ?>
                                    <option value="<?= SanitizeHelper::html($cat) ?>"><?= SanitizeHelper::html($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="modal_notes_out" class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                            <textarea id="modal_notes_out" name="notes" rows="3" class="form-input w-full" placeholder="Keterangan tambahan (opsional)"></textarea>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">Batal</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700">Simpan Transaksi</button>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
</div>