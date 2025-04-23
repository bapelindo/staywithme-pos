<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;

// Asumsikan view ini dipanggil oleh HomeController dan menggunakan public_layout.php
$pageTitle = "Selamat Datang";
?>

<div class="text-center py-12 md:py-20 px-4">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-4xl md:text-5xl font-bold text-indigo-700 mb-5 leading-tight">
            Pesan Lebih Mudah di <br class="hidden sm:block">Stay With Me Cafe
        </h1>
        <p class="text-lg text-slate-600 mb-10">
            Tidak perlu menunggu pelayan, cukup pindai QR Code di meja Anda untuk melihat menu lengkap kami dan lakukan pemesanan langsung dari smartphone Anda. Cepat, mudah, dan nyaman!
        </p>
        <div class="flex justify-center">
             <img src="<?= UrlHelper::asset('images/qr-code-scan.svg') // Ganti dengan ilustrasi yang menarik ?>" alt="Scan QR Code" class="w-full max-w-xs h-auto object-contain">
        </div>
         <p class="mt-6 text-sm text-slate-500">
            Temukan QR Code di meja Anda dan mulai memesan sekarang!
         </p>
    </div>
</div>