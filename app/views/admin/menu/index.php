<?php ob_start(); ?>
<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Daftar semua item menu.</p>
    <a href="<?= url_for('/admin/menu/create') ?>" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-md text-sm">+ Tambah Item Menu</a>
</div>
<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full">
        <thead> <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Item</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Ketersediaan</th>
            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr> </thead>
        <tbody>
            <?php /* Loop data $menuItems dari controller */ ?>
            <tr> <td colspan="5" class="text-center py-10 text-gray-500 italic">Data item menu akan ditampilkan di sini...</td> </tr>
            <?php /* Contoh Row:
            <tr>
                <td class="px-4 py-2 border-b">Nama Menu Lezat</td>
                <td class="px-4 py-2 border-b">Makanan Berat</td>
                <td class="px-4 py-2 border-b text-right">Rp 55.000</td>
                <td class="px-4 py-2 border-b text-center"><span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Tersedia</span></td>
                <td class="px-4 py-2 border-b text-center space-x-1"> <a href="#" class="text-indigo-600">Edit</a> <button class="text-red-600">Hapus</button> </td>
            </tr>
            */ ?>
        </tbody>
    </table>
</div>
<?php $content = ob_get_clean(); require APPROOT . '/views/layouts/admin.php'; ?>