<?php
// Lokasi File: app/views/admin/users/index.php
// Dimuat oleh Admin\UserController::index() (Controller belum dibuat)

ob_start();
// Data: $title, $pageTitle, $users (hanya staff/kitchen)
?>

<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Kelola akun untuk staff dan bagian dapur.</p>
    <a href="<?= url_for('/admin/users/create') ?>"
       class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-md shadow-sm transition duration-150 ease-in-out text-sm">
        + Tambah Pengguna Baru
    </a>
</div>

<div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
    <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
        <thead>
            <tr class="text-left bg-gray-100 sticky top-0">
                <th class="py-2 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                <th class="py-2 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                <th class="py-2 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Peran (Role)</th>
                <th class="py-2 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Status</th>
                <th class="py-2 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user):
                    // Jangan tampilkan admin utama atau handle berbeda
                    if ($user->role === 'admin') continue;
                ?>
                <tr class="hover:bg-gray-50 border-b border-gray-200">
                    <td class="py-3 px-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($user->name) ?></td>
                    <td class="py-3 px-4 text-sm text-gray-600"><?= htmlspecialchars($user->username) ?></td>
                    <td class="py-3 px-4 text-sm text-gray-500"><?= ucfirst(htmlspecialchars($user->role)) ?></td>
                    <td class="py-3 px-4 text-center">
                         <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                            <?= ($user->is_active) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= ($user->is_active) ? 'Aktif' : 'Nonaktif' ?>
                         </span>
                    </td>
                    <td class="py-3 px-4 text-sm text-center space-x-1 whitespace-nowrap">
                        <a href="<?= url_for('/admin/users/edit/' . $user->id) ?>" title="Edit Pengguna"
                           class="text-indigo-600 hover:text-indigo-900 p-1 inline-block">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </a>
                        <form action="<?= url_for('/admin/users/delete/' . $user->id) ?>" method="POST" class="inline-block"
                              onsubmit="return confirm('Yakin hapus pengguna \"<?= htmlspecialchars($user->username) ?>\"?');">
                            <?php // CSRF ?>
                            <?php // Method spoofing DELETE jika diperlukan ?>
                            <button type="submit" title="Hapus Pengguna" class="text-red-600 hover:text-red-900 p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center py-10 text-gray-500 italic">Belum ada data pengguna staff/kitchen.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php
$content = ob_get_clean();
require APPROOT . '/views/layouts/admin.php';
?>