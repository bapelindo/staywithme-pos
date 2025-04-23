<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;

// Asumsikan menggunakan public_layout.php
$pageTitle = "404 - Tidak Ditemukan";
$errorMessage = $message ?? 'Maaf, halaman atau sumber daya yang Anda cari tidak dapat ditemukan di server kami.';
?>

<div class="text-center py-16 md:py-24 px-4">
    <div class="max-w-xl mx-auto">
         <svg class="mx-auto h-24 w-auto text-indigo-300 mb-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
         </svg>
        <h1 class="text-3xl font-bold text-slate-800 mb-3">Oops! Halaman Hilang</h1>
        <p class="text-slate-600 mb-8">
            <?= SanitizeHelper::html($errorMessage) ?>
             Mungkin link yang Anda ikuti salah atau halaman telah dipindahkan.
        </p>
        <a href="<?= UrlHelper::baseUrl('/') ?>" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-lg transition duration-150 ease-in-out shadow-sm">
             <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
             </svg>
            Kembali ke Beranda
        </a>
    </div>
</div>