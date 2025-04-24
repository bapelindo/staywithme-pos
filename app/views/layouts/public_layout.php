<?php
// File: app/Views/layouts/public_layout.php (Layout Murni)
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper;

// Ambil konstanta dari config
$appName = SanitizeHelper::html(APP_NAME ?? 'Nama Kafe Default');
$cafeAddress = SanitizeHelper::html(CAFE_ADDRESS ?? 'Alamat Kafe Default');
$cafePhone = SanitizeHelper::html(CAFE_PHONE ?? 'Nomor Telepon Default');
$baseUrl = rtrim(UrlHelper::baseUrl(), '/');
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth dark"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= isset($pageTitle) ? SanitizeHelper::html($pageTitle) . ' - ' : '' ?><?= $appName ?></title>
    <meta name="description" content="Deskripsi singkat tentang kafe Anda, kopi, suasana, dan keunikannya.">
    <meta name="keywords" content="cafe modern, coffee shop, tempat nongkrong, workspace, kopi enak, nama kota">

    <link rel="icon" href="<?= $baseUrl ?>/favicon.ico" sizes="any">
    <link rel="icon" href="<?= UrlHelper::asset('images/icon.svg') ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= UrlHelper::asset('images/apple-touch-icon.png') ?>">
    <link rel="manifest" href="<?= $baseUrl ?>/manifest.webmanifest">

    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

    <link rel="stylesheet" href="<?= UrlHelper::baseUrl('css/style.css') ?>">

    <script>
        document.documentElement.classList.add('dark');
        var APP_BASE_URL = "<?= $baseUrl ?>";
    </script>
</head>
<body class="antialiased text-base bg-bg-dark-primary text-text-dark-primary overflow-x-hidden">
    <div id="preloader" class="fixed inset-0 bg-bg-dark-primary flex items-center justify-center z-[1000]">
         <div class="loader-container text-center">
             <i class="fas fa-mug-hot loader-icon text-accent-primary text-5xl animate-bounce"></i>
             <p class="loader-text text-lg mt-4 text-text-dark-secondary font-medium">Menyeduh Kesempurnaan...</p>
         </div>
    </div>

    <header class="fixed top-0 left-0 right-0 z-[100] bg-bg-dark-primary/80 backdrop-blur-md shadow-md transition-all duration-300" id="main-header">
         <nav class="container mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center">
             <a href="<?= $baseUrl ?>/" class="logo-link text-2xl font-bold text-white tracking-tight hover:opacity-90 transition-opacity inline-flex items-center">
                <i class="fas fa-mug-hot text-accent-primary mr-2 text-xl"></i> <?= $appName ?>
            </a>
             <div class="hidden lg:flex space-x-10 items-center">
                <a href="<?= $baseUrl ?>/#hero" class="nav-link relative text-sm font-semibold text-white hover:text-accent-primary transition-colors active">Beranda</a>
                <a href="<?= $baseUrl ?>/#story" class="nav-link relative text-sm font-semibold text-text-dark-secondary hover:text-accent-primary transition-colors">Cerita Kami</a>
                <a href="<?= $baseUrl ?>/#menu" class="nav-link relative text-sm font-semibold text-text-dark-secondary hover:text-accent-primary transition-colors">Menu</a>
                <a href="<?= $baseUrl ?>/#experience" class="nav-link relative text-sm font-semibold text-text-dark-secondary hover:text-accent-primary transition-colors">Suasana</a>
                <a href="<?= $baseUrl ?>/#gallery" class="nav-link relative text-sm font-semibold text-text-dark-secondary hover:text-accent-primary transition-colors">Galeri</a>
                <a href="<?= $baseUrl ?>/#connect" class="nav-link relative text-sm font-semibold text-text-dark-secondary hover:text-accent-primary transition-colors">Kontak</a>
             </div>
             <div class="hidden lg:flex items-center space-x-4">
                  <a href="https://instagram.com/username_kafe_anda" target="_blank" class="text-text-dark-secondary hover:text-white transition text-lg" title="Instagram" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                  <a href="https://wa.me/62xxxxxxxxxx" target="_blank" class="text-text-dark-secondary hover:text-white transition text-lg" title="WhatsApp" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
             </div>
             <button id="mobile-menu-button" aria-label="Toggle Menu" aria-expanded="false" class="lg:hidden p-2 text-white focus:outline-none focus:ring-2 focus:ring-accent-primary rounded">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </nav>
        <div id="mobile-menu" class="hidden lg:hidden absolute top-full left-0 right-0 w-full bg-bg-dark-secondary shadow-xl border-t border-border-dark transform origin-top transition-transform duration-300 ease-out scale-y-0">
             <div class="px-5 pt-5 pb-6 space-y-3">
                 <a href="<?= $baseUrl ?>/#hero" class="mobile-nav-link block text-white hover:text-accent-primary px-3 py-2 rounded-md text-base font-medium">Beranda</a>
                 <a href="<?= $baseUrl ?>/#story" class="mobile-nav-link block text-text-dark-secondary hover:text-accent-primary px-3 py-2 rounded-md text-base font-medium">Cerita Kami</a>
                 <a href="<?= $baseUrl ?>/#menu" class="mobile-nav-link block text-text-dark-secondary hover:text-accent-primary px-3 py-2 rounded-md text-base font-medium">Menu</a>
                 <a href="<?= $baseUrl ?>/#experience" class="mobile-nav-link block text-text-dark-secondary hover:text-accent-primary px-3 py-2 rounded-md text-base font-medium">Suasana</a>
                 <a href="<?= $baseUrl ?>/#gallery" class="mobile-nav-link block text-text-dark-secondary hover:text-accent-primary px-3 py-2 rounded-md text-base font-medium">Galeri</a>
                 <a href="<?= $baseUrl ?>/#connect" class="mobile-nav-link block text-text-dark-secondary hover:text-accent-primary px-3 py-2 rounded-md text-base font-medium">Kontak</a>
                 <hr class="border-border-dark my-4">
                 <div class="flex justify-center space-x-6 pt-5">
                      <a href="https://instagram.com/username_kafe_anda" target="_blank" class="social-link text-text-dark-secondary hover:text-white transition text-xl" title="Instagram"><i class="fab fa-instagram"></i></a>
                      <a href="https://wa.me/62xxxxxxxxxx" target="_blank" class="social-link text-text-dark-secondary hover:text-white transition text-xl" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                      <a href="#" class="social-link text-text-dark-secondary hover:text-white transition text-xl" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                  </div>
             </div>
        </div>
    </header>

    <main class="mt-[70px]"> <?php
        // Memuat file view spesifik yang diminta oleh Controller
        if (isset($viewPath) && file_exists($viewPath)) {
            require $viewPath;
        } else {
            // Tampilkan pesan error jika view spesifik tidak ada
            // Atau load view 404 standar
            $errorViewPath = '../app/Views/public/errors/404.php';
            if (file_exists($errorViewPath)) {
                 $message = "View file tidak ditemukan: " . SanitizeHelper::html($viewPath ?? 'Not Set');
                 require $errorViewPath;
            } else {
                 echo '<div class="container mx-auto px-4 py-10 bg-red-100 border border-red-400 text-red-700" role="alert">Error: View content could not be loaded.</div>';
            }
        }
        ?>
    </main>
    <footer class="pt-20 pb-10 bg-bg-dark-primary border-t-2 border-border-dark">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
             <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-10 mb-12 text-sm">
                 <div class="col-span-2 lg:col-span-2">
                     <a href="<?= $baseUrl ?>/" class="logo-link text-2xl font-bold text-white mb-4 inline-flex items-center">
                          <i class="fas fa-mug-hot text-accent-primary mr-2 text-xl"></i> <?= $appName ?>
                     </a>
                     <p class="text-xs text-text-dark-secondary leading-relaxed mb-5 pr-4">Meracik kopi, membina komunitas. Destinasi premium Anda.</p>
                     <div class="space-y-1 text-xs">
                        <p class="flex items-start"><i class="fas fa-map-marker-alt w-4 mt-1 mr-2 text-accent-primary flex-shrink-0"></i> <span class="text-text-dark-secondary"><?= $cafeAddress ?></span></p>
                        <p class="flex items-start"><i class="fas fa-phone-alt w-4 mt-1 mr-2 text-accent-primary flex-shrink-0"></i> <a href="tel:<?= SanitizeHelper::html(str_replace('-', '', $cafePhone)) ?>" class="hover:text-accent-primary transition text-text-dark-secondary"><?= $cafePhone ?></a></p>
                     </div>
                 </div>
                  <div>
                     <h5 class="font-semibold text-white mb-4 tracking-wide uppercase text-xs">Tautan Cepat</h5>
                     <ul class="space-y-2 text-xs">
                         <li><a href="<?= $baseUrl ?>/#story" class="text-text-dark-secondary hover:text-accent-primary transition">Cerita Kami</a></li>
                         <li><a href="<?= $baseUrl ?>/#menu" class="text-text-dark-secondary hover:text-accent-primary transition">Menu Lengkap</a></li>
                         <li><a href="<?= $baseUrl ?>/#experience" class="text-text-dark-secondary hover:text-accent-primary transition">Suasana</a></li>
                         <li><a href="<?= $baseUrl ?>/#gallery" class="text-text-dark-secondary hover:text-accent-primary transition">Galeri</a></li>
                     </ul>
                 </div>
                  <div>
                    <h5 class="font-semibold text-white mb-4 tracking-wide uppercase text-xs">Informasi</h5>
                     <ul class="space-y-2 text-xs">
                         <li><a href="<?= $baseUrl ?>/#location" class="text-text-dark-secondary hover:text-accent-primary transition">Lokasi & Jam</a></li>
                         <li><a href="#connect" class="text-text-dark-secondary hover:text-accent-primary transition">Kontak Kami</a></li>
                         <li><a href="<?= UrlHelper::baseUrl('/admin') ?>" class="text-text-dark-secondary hover:text-accent-primary transition">Login Staff</a></li>
                     </ul>
                 </div>
                 <div class="col-span-2 md:col-span-1">
                     <h5 class="font-semibold text-white mb-4 tracking-wide uppercase text-xs">Newsletter</h5>
                     <p class="text-xs text-text-dark-secondary mb-3">Dapatkan update & penawaran spesial.</p>
                     <form action="#" method="POST" class="flex" id="footer-newsletter-form">
                          <label for="footer-email" class="sr-only">Email</label>
                          <input type="email" name="email" id="footer-email" required placeholder="Email Anda" class="form-input !text-xs !py-1.5 !px-2.5 flex-grow !rounded-r-none">
                          <button type="submit" class="btn btn-accent !py-1.5 !px-3 !text-xs !rounded-l-none"><i class="fas fa-paper-plane"></i></button>
                      </form>
                  </div>
             </div>
             <div class="border-t border-border-dark pt-8 mt-8 flex flex-col md:flex-row justify-between items-center text-xs text-text-dark-muted">
                <p>© <?= date('Y') ?> <?= $appName ?>. Hak Cipta Dilindungi.</p>
                 <div class="flex space-x-5 mt-4 md:mt-0">
                         <a href="https://wa.me/6282229114960" target="_blank" title="WhatsApp" class="hover:text-white transition">made with <i class="fa-solid fa-wand-magic-sparkles"></i> by Bapel</a>
                 </div>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <script src="<?= UrlHelper::baseUrl('js/script.js') ?>" defer></script>

</body>
</html>