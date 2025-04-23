<?php
// File: app/Controllers/Admin/UserController.php (Final Diperbaiki)
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;
use App\Helpers\AuthHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper; // Pakai yg sudah update
use App\Helpers\SanitizeHelper;

class UserController extends Controller {
    private $userModel;
    public function __construct() { $this->userModel = $this->model('User'); AuthHelper::requireAdmin(); }
    public function index() { $users = $this->userModel->all(); $this->view('admin.users.index', ['pageTitle' => 'Kelola Pengguna','users' => $users], 'admin_layout'); }
    public function create() { $roles = ['admin', 'staff', 'kitchen']; $this->view('admin.users.create', ['pageTitle' => 'Tambah Pengguna Baru','roles' => $roles], 'admin_layout'); } // oldInput diambil di view

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/users/create'); return; }
        $username = SanitizeHelper::string($_POST['username'] ?? ''); $name = SanitizeHelper::string($_POST['name'] ?? ''); $role = SanitizeHelper::string($_POST['role'] ?? ''); $password = $_POST['password'] ?? ''; $passwordConfirm = $_POST['password_confirm'] ?? ''; $isActive = isset($_POST['is_active']) ? true : false; $roles = ['admin', 'staff', 'kitchen'];

        // Validasi
        $errorMsg = null;
        if (empty($username) || empty($name) || empty($role) || empty($password) || empty($passwordConfirm)) { $errorMsg = 'Semua field wajib diisi.'; }
        elseif (!in_array($role, $roles)) { $errorMsg = 'Peran tidak valid.'; }
        elseif (strlen($password) < 6) { $errorMsg = 'Password minimal 6 karakter.'; }
        elseif ($password !== $passwordConfirm) { $errorMsg = 'Konfirmasi password tidak cocok.'; }

        if ($errorMsg) {
            SessionHelper::setFlashData('old_input', $_POST); // <<< Gunakan setFlashData
            SessionHelper::setFlash('error', $errorMsg);      // <<< Kunci standar 'error'
            UrlHelper::redirect('/admin/users/create'); return;
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if (!$hashedPassword) { SessionHelper::setFlashData('old_input', $_POST); SessionHelper::setFlash('error', 'Gagal proses password.'); UrlHelper::redirect('/admin/users/create'); return; }

        // Simpan
        $userId = $this->userModel->createUser($username, $hashedPassword, $name, $role, $isActive);
        if ($userId) { SessionHelper::setFlash('success', 'Pengguna ditambahkan.'); UrlHelper::redirect('/admin/users'); } // <<< Kunci standar 'success'
        else { SessionHelper::setFlashData('old_input', $_POST); SessionHelper::setFlash('error', 'Gagal tambah pengguna (username duplikat?).'); UrlHelper::redirect('/admin/users/create'); } // <<< Gunakan 'error'
    }

    public function edit(int $id) {
        $safeId = SanitizeHelper::integer($id); if ($safeId <= 0) { UrlHelper::redirect('/admin/users'); return; } $user = $this->userModel->findById($safeId); if (!$user) { SessionHelper::setFlash('error', 'Pengguna tidak ditemukan.'); UrlHelper::redirect('/admin/users'); return; } $roles = ['admin', 'staff', 'kitchen'];
        $this->view('admin.users.edit', ['pageTitle' => 'Edit Pengguna: ' . SanitizeHelper::html($user['name']), 'user' => $user, 'roles' => $roles ], 'admin_layout'); // oldInput diambil di view
    }

    public function update(int $id) {
         $safeId = SanitizeHelper::integer($id); if ($safeId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/users'); return; }
         $username = SanitizeHelper::string($_POST['username'] ?? ''); $name = SanitizeHelper::string($_POST['name'] ?? ''); $role = SanitizeHelper::string($_POST['role'] ?? ''); $isActive = isset($_POST['is_active']) ? true : false; $password = $_POST['password'] ?? ''; $passwordConfirm = $_POST['password_confirm'] ?? ''; $roles = ['admin', 'staff', 'kitchen'];
         $isEditingSelf = ($safeId === AuthHelper::getUserId());
         // Ambil role & status asli jika edit diri sendiri (tidak bisa diubah via form)
         if ($isEditingSelf) { $currentUser = $this->userModel->findById($safeId); if($currentUser){ $role = $currentUser['role']; $isActive = (bool)$currentUser['is_active'];} }

         // Validasi dasar
         if (empty($username) || empty($name) || empty($role) || !in_array($role, $roles)) { SessionHelper::setFlashData('old_input', $_POST + ['id' => $safeId]); SessionHelper::setFlash('error', 'Username, Nama, dan Peran valid wajib diisi.'); UrlHelper::redirect('/admin/users/edit/' . $safeId); return; }

         // Validasi & Update Password jika diisi
         if (!empty($password)) {
             if (strlen($password) < 6 || $password !== $passwordConfirm) { SessionHelper::setFlashData('old_input', $_POST + ['id' => $safeId]); SessionHelper::setFlash('error', 'Password baru min 6 karakter & konfirmasi harus cocok.'); UrlHelper::redirect('/admin/users/edit/' . $safeId); return; }
             $newHashedPassword = password_hash($password, PASSWORD_DEFAULT);
             if (!$newHashedPassword) { SessionHelper::setFlashData('old_input', $_POST + ['id' => $safeId]); SessionHelper::setFlash('error', 'Gagal proses password baru.'); UrlHelper::redirect('/admin/users/edit/' . $safeId); return; }
             if (!$this->userModel->updatePassword($safeId, $newHashedPassword)) { SessionHelper::setFlashData('old_input', $_POST + ['id' => $safeId]); SessionHelper::setFlash('error', 'Gagal update password.'); UrlHelper::redirect('/admin/users/edit/' . $safeId); return; }
         }

         // Update data lain
         if ($this->userModel->updateUser($safeId, $username, $name, $role, $isActive)) { SessionHelper::setFlash('success', 'Data pengguna diperbarui.'); UrlHelper::redirect('/admin/users'); } // <<< Kunci 'success'
         else { SessionHelper::setFlashData('old_input', $_POST + ['id' => $safeId]); SessionHelper::setFlash('error', 'Gagal update data (username duplikat?).'); UrlHelper::redirect('/admin/users/edit/' . $safeId); } // <<< Kunci 'error'
     }

    public function destroy(int $id) {
        $safeId = SanitizeHelper::integer($id); if ($safeId <= 0 || $safeId === AuthHelper::getUserId() || $_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/users'); return; }
        $user = $this->userModel->findById($safeId); $userName = $user ? $user['username'] : 'ID ' . $safeId;
        if ($this->userModel->deleteUser($safeId)) { SessionHelper::setFlash('success', 'Pengguna "' . SanitizeHelper::html($userName) . '" dihapus.'); } // <<< 'success'
        else { SessionHelper::setFlash('error', 'Gagal hapus pengguna.'); } // <<< 'error'
        UrlHelper::redirect('/admin/users');
     }
}
?>