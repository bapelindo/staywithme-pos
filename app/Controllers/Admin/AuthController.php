<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;
use App\Helpers\AuthHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SanitizeHelper;
use App\Helpers\SessionHelper;

class AuthController extends Controller {
    public function redirectToLogin(): void {
        UrlHelper::redirect('/admin/login');
    }

    public function showLoginForm() {
        if (AuthHelper::isLoggedIn()) {
            UrlHelper::redirect('/admin/dashboard');
        }
        $error = SessionHelper::getFlash('login_error'); // Ambil error dari session flash
        $this->view('admin.auth.login', ['pageTitle' => 'Admin Login', 'error' => $error], null);
    }

    public function login() {
         if (AuthHelper::isLoggedIn()) {
            UrlHelper::redirect('/admin/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            UrlHelper::redirect('/admin/login');
        }

        $username = SanitizeHelper::string($_POST['username'] ?? '');
        $password = $_POST['password'] ?? ''; // Jangan sanitasi password

        if (empty($username) || empty($password)) {
             SessionHelper::setFlash('login_error', 'Username dan password wajib diisi.');
             UrlHelper::redirect('/admin/login');
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        // Verifikasi User, Password HASH, dan Status Aktif
        if ($user && password_verify($password, $user['password']) && $user['is_active']) {
            // Peran yang diizinkan login ke admin panel
            $allowedRoles = ['admin', 'staff', 'kitchen']; // Sesuaikan jika perlu
            if (in_array($user['role'], $allowedRoles)) {
                AuthHelper::loginUser($user);
                // Redirect berdasarkan peran? Atau selalu ke dashboard?
                UrlHelper::redirect('/admin/dashboard');
            } else {
                 SessionHelper::setFlash('login_error', 'Anda tidak memiliki hak akses.');
                 UrlHelper::redirect('/admin/login');
            }
        } else {
             SessionHelper::setFlash('login_error', 'Username atau password salah, atau akun tidak aktif.');
             UrlHelper::redirect('/admin/login');
        }
    }

    public function logout() {
        AuthHelper::logoutUser();
        UrlHelper::redirect('/admin/login');
    }
}
?>