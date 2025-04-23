<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper;
use App\Helpers\AuthHelper;

$roles = $roles ?? ['admin', 'staff', 'kitchen'];
// === PERBAIKAN: Ambil oldInput dari FlashData ===
$oldInput = SessionHelper::getFlashData('old_input') ?? [];
// ============================================
AuthHelper::requireAdmin(); // Pastikan hanya admin
?>

<div class="max-w-lg mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-slate-800">Tambah Pengguna Baru</h2>
        <a href="<?= UrlHelper::baseUrl('/admin/users') ?>" class="text-sm text-indigo-600 hover:text-indigo-800 inline-flex items-center">
             <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            Kembali ke Daftar Pengguna
        </a>
    </div>

    <?php SessionHelper::displayFlash('error'); // Tampilkan error umum ?>

    <div class="bg-white p-6 rounded-lg shadow-md border border-slate-200">
        <form action="<?= UrlHelper::baseUrl('/admin/users/store') ?>" method="POST" novalidate>
             <div class="mb-4">
                 <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                 <input type="text" id="name" name="name" required maxlength="100"
                        value="<?= SanitizeHelper::html($oldInput['name'] ?? '') ?>"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
             </div>

             <div class="mb-4">
                 <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username <span class="text-red-500">*</span></label>
                 <input type="text" id="username" name="username" required maxlength="50"
                        value="<?= SanitizeHelper::html($oldInput['username'] ?? '') ?>"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                        placeholder="Untuk login, tanpa spasi">
                  <p class="text-xs text-slate-500 mt-1">Harus unik.</p>
             </div>

             <div class="mb-4">
                 <label for="role" class="block text-sm font-medium text-slate-700 mb-1">Peran <span class="text-red-500">*</span></label>
                 <select id="role" name="role" required
                         class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm appearance-none">
                     <option value="" disabled <?= empty($oldInput['role'] ?? null) ? 'selected' : '' ?>>-- Pilih Peran --</option>
                     <?php foreach ($roles as $role): ?>
                         <option value="<?= SanitizeHelper::html($role) ?>"
                                 <?= (isset($oldInput['role']) && $oldInput['role'] === $role) ? 'selected' : '' ?>>
                             <?= SanitizeHelper::html(ucfirst($role)) ?>
                         </option>
                     <?php endforeach; ?>
                 </select>
             </div>

             <div class="mb-4">
                 <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password <span class="text-red-500">*</span></label>
                 <input type="password" id="password" name="password" required minlength="6"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                  <p class="text-xs text-slate-500 mt-1">Minimal 6 karakter.</p>
             </div>

              <div class="mb-4">
                 <label for="password_confirm" class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                 <input type="password" id="password_confirm" name="password_confirm" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
             </div>

             <div class="mb-6">
                  <label for="is_active" class="flex items-center">
                      <input type="checkbox" id="is_active" name="is_active" value="1"
                             class="h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500"
                             <?= (isset($oldInput['is_active']) || !isset($oldInput) || empty($oldInput)) ? 'checked' : '' ?>
                      >
                      <span class="ml-2 text-sm text-slate-700">Akun Aktif</span>
                  </label>
                  <p class="text-xs text-slate-500 mt-1">Pengguna bisa login jika aktif.</p>
             </div>

             <div class="flex justify-end space-x-3 border-t border-slate-200 pt-5 mt-5">
                 <a href="<?= UrlHelper::baseUrl('/admin/users') ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-4 rounded-lg transition border border-slate-300">Batal</a>
                 <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-lg transition shadow-md">Simpan Pengguna</button>
             </div>
        </form>
    </div>
</div>