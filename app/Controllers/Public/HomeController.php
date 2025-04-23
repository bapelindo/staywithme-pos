<?php
// File: app/Controllers/Public/HomeController.php

namespace App\Controllers\Public;

use App\Core\Controller; // Menggunakan Base Controller
use App\Helpers\SanitizeHelper; // Untuk sanitasi data ke view (meski di sini hanya judul)
// use App\Helpers\UrlHelper; // Gunakan jika perlu redirect
// use App\Helpers\AuthHelper; // Gunakan jika konten homepage tergantung status login

/**
 * Class HomeController
 *
 * Controller untuk halaman utama (beranda) publik.
 */
class HomeController extends Controller {

    /**
     * Menampilkan halaman beranda publik.
     * Metode ini dipanggil ketika pengguna mengakses root URL ('/').
     */
    public function index() {
        // Contoh: Jika pengguna sudah login (misal ada akun customer),
        // mungkin redirect ke halaman lain? Tapi untuk kasus ini, tampilkan saja homepage.
        // if (AuthHelper::isLoggedIn()) {
        //     UrlHelper::redirect('/dashboard-customer'); // Contoh
        //     return;
        // }

        // Data yang akan dikirim ke view (misalnya judul halaman)
        $data = [
            // Gunakan SanitizeHelper untuk memastikan output aman
            'pageTitle' => SanitizeHelper::html('Selamat Datang di Stay With Me Cafe')
            // Tambahkan data lain jika view 'public.home' membutuhkannya
            // 'featuredItems' => $this->model('MenuItem')->getFeatured(3) // Contoh jika perlu data model
        ];

        // Memanggil method view dari Base Controller untuk memuat file view
        // 'public.home' akan diterjemahkan menjadi path 'app/Views/public/home.php'
        // Data dalam array $data akan diekstrak menjadi variabel di dalam view (e.g., $pageTitle)
        $this->view('public.home', $data);
    }
}
?>