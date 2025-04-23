<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\AuthHelper; // Untuk cek ID user yg login

// Data dari UserController
$users = $users ?? [];
// $pageTitle sudah diatur layout

// Hanya admin yang boleh akses halaman ini (meski sudah dicek di Controller)
AuthHelper::requireAdmin();
$loggedInUserId = AuthHelper::getUserId(); // Dapatkan ID admin yg sedang login
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold text-slate-800">Kelola Pengguna Sistem</h2>
    <a href="<?= UrlHelper::baseUrl('/admin/users/create') ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-md transition shadow-sm">
        + Tambah Pengguna Baru
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden border border-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Lengkap</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Username</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Peran (Role)</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-500 text-sm">Belum ada pengguna yang terdaftar.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="<?= ($user['id'] === $loggedInUserId) ? 'bg-indigo-50' : '' // Highlight user yg login ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                <?= SanitizeHelper::html($user['name']) ?>
                                <?= ($user['id'] === $loggedInUserId) ? '<span class="text-xs text-indigo-600 ml-1">(Anda)</span>' : '' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                <?= SanitizeHelper::html($user['username']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?= SanitizeHelper::html(ucfirst($user['role'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-medium">
                                <?php if ($user['is_active']): ?>
                                    <span class="px-2.5 py-0.5 rounded-full bg-green-100 text-green-800">Aktif</span>
                                <?php else: ?>
                                     <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                <a href="<?= UrlHelper::baseUrl('/admin/users/edit/' . $user['id']) ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit">Edit</a>

                                <?php if ($user['id'] !== $loggedInUserId): // Jangan tampilkan tombol hapus untuk diri sendiri ?>
                                    <form action="<?= UrlHelper::baseUrl('/admin/users/destroy/' . $user['id']) ?>" method="POST" class="inline-block delete-confirm-form" data-confirm-message="Yakin ingin menghapus pengguna '<?= SanitizeHelper::html($user['username']) ?>'?">
                                         <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">Hapus</button>
                                    </form>
                                <?php else: ?>
                                     <span class="text-slate-400 cursor-not-allowed" title="Tidak dapat menghapus diri sendiri">Hapus</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>