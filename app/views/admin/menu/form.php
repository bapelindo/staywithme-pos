<?php ob_start();
// Data: $title, $pageTitle, $errors, $old, $menuItem (opsional, untuk edit), $categories
$isEdit = isset($menuItem) && $menuItem->id;
$formAction = $isEdit ? url_for('/admin/menu/update/' . $menuItem->id) : url_for('/admin/menu/store');
?>
<div class="max-w-2xl mx-auto">
    <a href="<?= url_for('/admin/menu') ?>" class="text-sm text-blue-600 hover:underline mb-4 inline-block">&larr; Kembali ke Daftar Menu</a>
    <form action="<?= $formAction ?>" method="POST" enctype="multipart/form-data"> <?php // Enctype untuk upload gambar ?>
        <?php // CSRF ?>
        <?php if ($isEdit) echo '<input type="hidden" name="_method" value="PUT">'; // Method spoofing for update ?>

        <div class="mb-4"> <label for="name">Nama Item *</label> <input type="text" id="name" name="name" required value="<?= htmlspecialchars($old['name'] ?? ($menuItem->name ?? '')) ?>" class="w-full border rounded px-3 py-2"> <?php /* Error handling */ ?> </div>
        <div class="mb-4"> <label for="category_id">Kategori *</label> <select id="category_id" name="category_id" required class="w-full border rounded px-3 py-2 bg-white"> <option value="">-- Pilih Kategori --</option> <?php /* Loop $categories */ ?> </select> <?php /* Error handling */ ?> </div>
        <div class="mb-4"> <label for="price">Harga *</label> <input type="number" id="price" name="price" required step="500" min="0" value="<?= htmlspecialchars($old['price'] ?? ($menuItem->price ?? '')) ?>" class="w-full border rounded px-3 py-2"> <?php /* Error handling */ ?> </div>
        <div class="mb-4"> <label for="description">Deskripsi</label> <textarea id="description" name="description" rows="4" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($old['description'] ?? ($menuItem->description ?? '')) ?></textarea> </div>
        <div class="mb-4"> <label for="image">Gambar</label> <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" class="w-full border rounded px-3 py-2"> <?php /* Tampilkan gambar saat ini jika edit */ ?> </div>
        <div class="mb-6"> <label for="is_available">Ketersediaan</label> <select id="is_available" name="is_available" class="w-full border rounded px-3 py-2 bg-white"> <option value="1" <?= (($old['is_available'] ?? ($menuItem->is_available ?? 1)) == 1) ? 'selected' : '' ?>>Tersedia</option> <option value="0" <?= (($old['is_available'] ?? ($menuItem->is_available ?? 1)) == 0) ? 'selected' : '' ?>>Habis</option> </select> </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-5 rounded-md"><?= $isEdit ? 'Update Item' : 'Simpan Item Baru' ?></button>
    </form>
</div>
<?php $content = ob_get_clean(); require APPROOT . '/views/layouts/admin.php'; ?>