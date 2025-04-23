<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;
use App\Helpers\AuthHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper;
use App\Helpers\SanitizeHelper;

class UserController extends Controller {

    private $userModel;

    public function __construct() {
        $this->userModel = new User();
         // Pastikan hanya admin yang bisa akses semua method di controller ini
         AuthHelper::requireAdmin();
    }

    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index() {
        $users = $this->userModel->all(); // Ambil semua user dari Base Model

        $this->view('admin.users.index', [
            'pageTitle' => 'Kelola Pengguna',
            'users' => $users
        ], 'admin_layout');
    }

     /**
     * Menampilkan form tambah pengguna baru.
     */
    public function create() {
        $oldInput = SessionHelper::getFlash('old_input') ?? [];
        $roles = ['admin', 'staff', 'kitchen']; // Daftar peran yang valid

        $this->view('admin.users.create', [
            'pageTitle' => 'Tambah Pengguna Baru',
            'roles' => $roles,
            'oldInput' => $oldInput
        ], 'admin_layout');
    }

    /**
     * Menyimpan pengguna baru.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/users/create'); return; }

        $username = SanitizeHelper::string($_POST['username'] ?? '');
        $name = SanitizeHelper::string($_POST['name'] ?? '');
        $role = SanitizeHelper::string($_POST['role'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $isActive = isset($_POST['is_active']) ? true : false;
        $roles = ['admin', 'staff', 'kitchen'];

        SessionHelper::setFlash('old_input', $_POST);

        // Validasi
        if (empty($username) || empty($name) || empty($role) || empty($password) || empty($passwordConfirm)) {
             SessionHelper::setFlash('error', 'Semua field wajib diisi.');
             UrlHelper::redirect('/admin/users/create'); return;
        }
        if (!in_array($role, $roles)) {
             SessionHelper::setFlash('error', 'Peran tidak valid.');
             UrlHelper::redirect('/admin/users/create'); return;
        }
        if (strlen($password) < 6) { // Contoh validasi panjang password
             SessionHelper::setFlash('error', 'Password minimal 6 karakter.');
             UrlHelper::redirect('/admin/users/create'); return;
        }
        if ($password !== $passwordConfirm) {
             SessionHelper::setFlash('error', 'Konfirmasi password tidak cocok.');
             UrlHelper::redirect('/admin/users/create'); return;
        }

        // Hash Password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if (!$hashedPassword) {
            SessionHelper::setFlash('error', 'Gagal memproses password.');
            UrlHelper::redirect('/admin/users/create'); return;
        }

        // Simpan ke DB
        $userId = $this->userModel->createUser($username, $hashedPassword, $name, $role, $isActive);

        if ($userId) {
             SessionHelper::setFlash('success', 'Pengguna berhasil ditambahkan.');
             SessionHelper::getFlash('old_input');
             UrlHelper::redirect('/admin/users');
        } else {
             SessionHelper::setFlash('error', 'Gagal menambahkan pengguna (mungkin username sudah ada).');
             UrlHelper::redirect('/admin/users/create');
        }
    }

     /**
     * Menampilkan form edit pengguna.
     */
     public function edit(int $id) {
         $safeId = SanitizeHelper::integer($id);
         if ($safeId <= 0) { UrlHelper::redirect('/admin/users'); return; }

         // Jangan biarkan admin mengedit dirinya sendiri di form ini? Atau batasi field?
         // if ($safeId === AuthHelper::getUserId()) { ... }

         $user = $this->userModel->findById($safeId);
         if (!$user) {
             SessionHelper::setFlash('error', 'Pengguna tidak ditemukan.');
             UrlHelper::redirect('/admin/users'); return;
         }

         $oldInput = SessionHelper::getFlash('old_input') ?? $user;
         $roles = ['admin', 'staff', 'kitchen'];

         $this->view('admin.users.edit', [
             'pageTitle' => 'Edit Pengguna: ' . SanitizeHelper::html($user['name']),
             'user' => $oldInput, // Kirim data user ke view
             'roles' => $roles
         ], 'admin_layout');
     }


    /**
     * Mengupdate data pengguna.
     */
     public function update(int $id) {
         $safeId = SanitizeHelper::integer($id);
         if ($safeId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') { UrlHelper::redirect('/admin/users'); return; }

         $username = SanitizeHelper::string($_POST['username'] ?? '');
         $name = SanitizeHelper::string($_POST['name'] ?? '');
         $role = SanitizeHelper::string($_POST['role'] ?? '');
         $isActive = isset($_POST['is_active']) ? true : false;
         $password = $_POST['password'] ?? ''; // Password baru (opsional)
         $passwordConfirm = $_POST['password_confirm'] ?? '';
         $roles = ['admin', 'staff', 'kitchen'];

         SessionHelper::setFlash('old_input', $_POST + ['id' => $safeId]);

         // Validasi dasar
         if (empty($username) || empty($name) || empty($role)) {
              SessionHelper::setFlash('error', 'Username, Nama, dan Peran wajib diisi.');
              UrlHelper::redirect('/admin/users/edit/' . $safeId); return;
         }
          if (!in_array($role, $roles)) {
              SessionHelper::setFlash('error', 'Peran tidak valid.');
              UrlHelper::redirect('/admin/users/edit/' . $safeId); return;
         }

          // Validasi & Update Password jika diisi
          if (!empty($password)) {
              if (strlen($password) < 6) {
                   SessionHelper::setFlash('error', 'Password baru minimal 6 karakter.');
                   UrlHelper::redirect('/admin/users/edit/' . $safeId); return;
              }
              if ($password !== $passwordConfirm) {
                   SessionHelper::setFlash('error', 'Konfirmasi password baru tidak cocok.');
                   UrlHelper::redirect('/admin/users/edit/' . $safeId); return;
              }
              $newHashedPassword = password_hash($password, PASSWORD_DEFAULT);
              if (!$newHashedPassword) {
                   SessionHelper::setFlash('error', 'Gagal memproses password baru.');
                   UrlHelper::redirect('/admin/users/edit/' . $safeId); return;
              }
              // Update password di DB
              if (!$this->userModel->updatePassword($safeId, $newHashedPassword)) {
                  SessionHelper::setFlash('error', 'Gagal memperbarui password.');
                  UrlHelper::redirect('/admin/users/edit/' . $safeId); return;
              }
          }

         // Update data user lainnya
         if ($this->userModel->updateUser($safeId, $username, $name, $role, $isActive)) {
              SessionHelper::setFlash('success', 'Data pengguna berhasil diperbarui.');
              SessionHelper::getFlash('old_input');
              UrlHelper::redirect('/admin/users');
         } else {
              SessionHelper::setFlash('error', 'Gagal memperbarui data pengguna (mungkin username duplikat).');
              UrlHelper::redirect('/admin/users/edit/' . $safeId);
         }
     }

     /**
      * Menghapus pengguna.
      */
     public function destroy(int $id) {
         $safeId = SanitizeHelper::integer($id);
          // Jangan biarkan admin menghapus dirinya sendiri!
         if ($safeId <= 0 || $safeId === AuthHelper::getUserId() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
             UrlHelper::redirect('/admin/users'); return;
         }

         if ($this->userModel->deleteUser($safeId)) {
              SessionHelper::setFlash('success', 'Pengguna berhasil dihapus.');
         } else {
              SessionHelper::setFlash('error', 'Gagal menghapus pengguna (mungkin terkait dengan data lain).');
         }
         UrlHelper::redirect('/admin/users');
     }

}
?>