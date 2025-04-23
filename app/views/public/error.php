<?php
// Lokasi File: app/views/public/error.php
// Bisa dimuat oleh controller jika perlu menampilkan error spesifik

ob_start();
// Data dari controller: $title, $message
$errorCode = $errorCode ?? 'Oops'; // Default jika tidak ada kode spesifik
?>

<div class="text-center py-20">
     <h1 class="text-6xl font-extrabold text-red-500 mb-4"><?= htmlspecialchars($errorCode) ?></h1>
    <h2 class="text-3xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($title ?? 'Terjadi Kesalahan') ?></h2>
    <p class="text-lg text-gray-600 mb-8 max-w-lg mx-auto">
        <?= htmlspecialchars($message ?? 'Maaf, terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti atau kembali ke halaman utama.') ?>
    </p>
    <a href="<?= url_for('/') ?>"
       class="bg-primary hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full text-lg shadow-md transition duration-300 ease-in-out">
        Kembali ke Halaman Utama
    </a>
</div>

<?php
$content = ob_get_clean();
require APPROOT . '/views/layouts/public.php';
?>