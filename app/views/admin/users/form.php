<?php
// Lokasi File: app/views/admin/users/form.php
// Dimuat oleh Admin\UserController::create(), store(), edit(), update()

ob_start();
// Data: $title, $pageTitle, $errors, $old, $user (opsional, untuk edit)
$isEdit = isset($user) && $user->id;
$formAction = $isEdit ? url_for('/admin/users/update/' . $user->id) : url_for('/admin/users/store');
?>

<div class="max-w-xl mx-auto">
    <a href="<?= url_for('/admin/users') ?>" class="text-sm text-blue-600 hover:underline mb-4 inline-block">&larr; Kembali ke Daftar Pengguna</a>

    <form action="<?= $formAction ?>" method="POST">
        <?php // CSRF Token ?>
        <?php if ($isEdit) echo '<input type="hidden" name="_method" value="PUT">'; ?>

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" required maxlength="100"
                   value="<?= htmlspecialchars($old['name'] ?? ($user->name ?? '')) ?>"
                   class="w-full border <?= isset($errors['name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <?php if (isset($errors['name'])): ?><p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['name'][0]) ?></p><?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
            <input type="text" id="username" name="username" required maxlength="50"
                   value="<?= htmlspecialchars($old['username'] ?? ($user->username ?? '')) ?>"
                   class="w-full border <?= isset($errors['username']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                   <?= $isEdit ? 'readonly' : '' // Username mungkin tidak boleh diubah ?> >
            <?php if ($isEdit): ?><p class="text-xs text-gray-500 mt-1">Username tidak dapat diubah setelah dibuat.</p><?php endif; ?>
            <?php if (isset($errors['username'])): ?><p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['username'][0]) ?></p><?php endif; ?>
        </div>

         <div class="mb-4">
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Peran (Role) <span class="text-red-500">*</span></label>
            <select id="role" name="role" required
                    class="w-full border <?= isset($errors['role']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                <?php
                 $currentRole = $old['role'] ?? ($user->role ?? 'staff');
                 // Hanya izinkan role staff/kitchen, jangan admin dari form ini
                 $roles = ['staff' => 'Staff (Kasir/Waiter)', 'kitchen' => 'Dapur (KDS)'];
                 foreach ($roles as $value => $label):
                 ?>
                 <option value="<?= $value ?>" <?= ($currentRole == $value) ? 'selected' : '' ?>><?= $label ?></option>
                 <?php endforeach; ?>
            </select>
             <?php if (isset($errors['role'])): ?><p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['role'][0]) ?></p><?php endif; ?>
        </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
             <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <?= !$isEdit ? '<span class="text-red-500">*</span>' : '(Kosongkan jika tidak diubah)' ?></label>
                <input type="password" id="password" name="password" <?= !$isEdit ? 'required' : '' ?> minlength="6"
                       class="w-full border <?= isset($errors['password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <?php if (isset($errors['password'])): ?><p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['password'][0]) ?></p><?php endif; ?>
             </div>
              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password <?= !$isEdit ? '<span class="text-red-500">*</span>' : '' ?></label>
                <input type="password" id="password_confirmation" name="password_confirmation" <?= !$isEdit ? 'required' : '' ?>
                       class="w-full border <?= isset($errors['password_confirmation']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                 <?php if (isset($errors['password_confirmation'])): ?><p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['password_confirmation'][0]) ?></p><?php endif; ?>
             </div>
        </div>

         <div class="mb-6">
             <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Status Akun</label>
            <select id="is_active" name="is_active" class="w-full border rounded px-3 py-2 bg-white text-sm">
                <option value="1" <?= (($old['is_active'] ?? ($user->is_active ?? 1)) == 1) ? 'selected' : '' ?>>Aktif</option>
                <option value="0" <?= (($old['is_active'] ?? ($user->is_active ?? 1)) == 0) ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>


        <div class="flex justify-end">
            <a href="<?= url_for('/admin/users') ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-md text-sm mr-2">
                Batal
            </a>
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md text-sm shadow-sm">
                <?= $isEdit ? 'Update Pengguna' : 'Simpan Pengguna Baru' ?>
            </button>
        </div>

    </form>
</div>

<?php
$content = ob_get_clean();
require APPROOT . '/views/layouts/admin.php';
?>