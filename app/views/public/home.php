<?php
// File: app/Views/public/home.php (Dengan AOS di elemen internal - Solusi 2)
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;

// Data dari Controller
$topItems = $topItems ?? [];
$menuItems = $menuItems ?? [];
$categories = $categories ?? [];
$appName = SanitizeHelper::html(APP_NAME ?? 'Nama Kafe Default');
$cafeAddress = SanitizeHelper::html(CAFE_ADDRESS ?? 'Alamat Kafe Default');
$cafePhone = SanitizeHelper::html(CAFE_PHONE ?? 'Nomor Telepon Default');
$baseUrl = rtrim(UrlHelper::baseUrl(), '/');
$placeholderImage = UrlHelper::baseUrl('images/menu-placeholder.jpg');
?>

<section class="relative py-24 md:py-36 overflow-hidden min-h-[calc(100vh-70px)] flex items-center -mt-6 md:-mt-8" id="hero">
    <div aria-hidden="true" class="absolute inset-0 z-0 opacity-40">
         <img src="<?= UrlHelper::baseUrl('images/background.svg') ?>" alt="Ilustrasi Latar Belakang Kafe Gelap Abstrak" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-bg-dark-primary via-bg-dark-primary/70 to-bg-dark-secondary"></div>
    </div>
    <div aria-hidden="true" class="absolute top-1/4 left-10 w-16 h-16 rounded-full bg-accent-primary/10 blur-2xl animate-pulse"></div>
    <div aria-hidden="true" class="absolute bottom-1/4 right-10 w-20 h-20 rounded-full bg-accent-secondary/10 blur-2xl animate-pulse delay-500"></div>

     <div class="container mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-16 items-center relative z-10">
        <?php // Kolom Kiri Hero dengan AOS fade-right ?>
        <div class="text-center lg:text-left" data-aos="fade-right" data-aos-duration="800">
        <h1 class="hero-heading text-5xl md:text-6xl lg:text-7xl font-extrabold mb-6 leading-tight text-white">
                <?php // Hapus 'block' jika ada ?>
                <span>Secangkir Inspirasi,</span>
                <?php // Hapus 'block' jika ada ?>
                <span id="dynamic-tagline" class="text-4xl md:text-6xl lg:text-7xl inline-block w-[23rem] sm:w-auto text-center mx-auto lg:mx-0 text-accent-primary whitespace-nowrap overflow-hidden">Sejuta Cerita.</span>
             </h1>
             <p class="text-lg md:text-xl text-text-dark-secondary mb-10 max-w-xl mx-auto lg:mx-0">
               Dapatkan kopi artisan terbaik, hidangan lezat, dan suasana yang memicu kreativitas serta koneksi di <?= $appName ?>.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <a href="#menu" class="btn btn-accent px-8 py-3 rounded-full font-semibold transition shadow-lg text-center text-base transform hover:scale-105 hover:shadow-xl inline-flex items-center justify-center">
                    <i class="fas fa-book-open mr-2"></i> Menu Kami
                </a>
                <a href="#location" class="btn btn-secondary px-8 py-3 rounded-full font-semibold transition shadow-sm text-center text-base inline-flex items-center justify-center">
                    <i class="fas fa-map-marker-alt mr-2"></i> Kunjungi Kami
                </a>
            </div>
        </div>
        <?php // Kolom Kanan Hero dengan AOS zoom-in ?>
        <div class="hidden lg:flex justify-center items-center relative" data-aos="zoom-in" data-aos-duration="800" data-aos-delay="200">
             <img loading="lazy" src="<?= UrlHelper::baseUrl('images/coffee.svg') ?>" alt="Ilustrasi Cangkir Kopi Premium" class="relative z-10 w-2/3 max-w-sm drop-shadow-2xl">
             <img loading="lazy" src="<?= UrlHelper::baseUrl('images/coffee-cup.svg') ?>" alt="Ilustrasi Biji Kopi" class="absolute -bottom-10 -left-10 w-1/2 opacity-60 z-0">
             <img loading="lazy" src="<?= UrlHelper::baseUrl('images/coffee-bean.svg') ?>" alt="Ilustrasi Asap Kopi" class="absolute -top-10 -right-10 w-1/2 opacity-60 z-0">
        </div>
    </div>
</section>

<section id="story" class="section-padding bg-bg-dark-secondary relative overflow-hidden">
     <div aria-hidden="true" class="absolute top-0 left-0 w-1/3 h-full bg-gradient-to-r from-bg-dark-tertiary/30 to-transparent opacity-30 pointer-events-none"></div>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16 max-w-3xl mx-auto" data-aos="fade-up">
             <span class="text-sm font-semibold text-accent-primary uppercase tracking-wider">Gairah Kami</span>
            <h2 class="text-4xl md:text-5xl font-bold my-4 text-white">Seni & Jiwa dalam Kopi</h2>
            <p class="text-lg text-text-dark-secondary leading-relaxed">
               Dari kebun hingga cangkir, setiap langkah adalah dedikasi pada kualitas. Kami bekerja sama dengan petani lokal, menyangrai biji kami sendiri, dan memberdayakan barista kami untuk menciptakan minuman sempurna Anda.
            </p>
        </div>
        <div class="grid md:grid-cols-3 gap-10">
            <div class="text-center p-6 bg-bg-dark rounded-lg shadow-lg border border-border-dark" data-aos="fade-up" data-aos-delay="100">
                <div class="mb-5 text-5xl text-accent-secondary"><i class="fas fa-leaf"></i></div>
                <h3 class="text-2xl font-semibold mb-3 text-white">Sumber Etis</h3>
                <p class="text-sm text-text-dark-secondary leading-relaxed">
                    Memilih hanya biji kopi *single-origin* terbaik, memastikan perdagangan yang adil dan pertanian berkelanjutan.
                </p>
            </div>
            <div class="text-center p-6 bg-bg-dark rounded-lg shadow-lg border border-border-dark" data-aos="fade-up" data-aos-delay="200">
                 <div class="mb-5 text-5xl text-accent-secondary"><i class="fas fa-fire-alt"></i></div>
                <h3 class="text-2xl font-semibold mb-3 text-white">Sangrai Internal</h3>
                <p class="text-sm text-text-dark-secondary leading-relaxed">
                   Penyangraian *small-batch* setiap hari untuk membuka profil rasa unik setiap biji. Presisi dan hati-hati.
                </p>
            </div>
            <div class="text-center p-6 bg-bg-dark rounded-lg shadow-lg border border-border-dark" data-aos="fade-up" data-aos-delay="300">
                 <div class="mb-5 text-5xl text-accent-secondary"><i class="fas fa-mug-hot"></i></div>
                <h3 class="text-2xl font-semibold mb-3 text-white">Penyeduhan Ahli</h3>
                <p class="text-sm text-text-dark-secondary leading-relaxed">
                    Barista terampil menggunakan peralatan dan teknik mutakhir, dari espresso hingga *manual brew*.
                </p>
            </div>
        </div>
    </div>
</section>

<section id="experience" class="section-padding bg-bg-dark-primary">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 md:gap-16 items-center">
        
        <?php // Kolom Kiri Experience - Hapus AOS dari div ini ?>
        <div>
            <span class="text-sm font-semibold text-accent-primary uppercase tracking-wider" data-aos="fade-right" data-aos-duration="600">Ruang Anda</span>
            <h2 class="text-4xl md:text-5xl font-bold my-4 text-white" data-aos="fade-right" data-aos-duration="600" data-aos-delay="100">Lebih dari Kopi, Ini Pengalaman.</h2>
            <p class="text-lg text-text-dark-secondary mb-8 leading-relaxed" data-aos="fade-right" data-aos-duration="600" data-aos-delay="200">
               <?= SanitizeHelper::html(APP_NAME ?? 'Kafe Kami') ?> dirancang untuk menjadi tempat perlindungan Anda. Baik mencari fokus, koneksi, atau sekadar ketenangan, temukan sudut sempurna Anda.
            </p>
            <div class="space-y-6">
                <div class="flex items-start space-x-4 p-4 bg-bg-dark-secondary rounded-lg border border-border-dark" data-aos="fade-right" data-aos-duration="600" data-aos-delay="300">
                    <i class="fas fa-couch text-2xl text-accent-secondary mt-1 flex-shrink-0 w-6 text-center"></i>
                    <div>
                        <h4 class="font-semibold text-lg text-white">Suasana Nyaman</h4>
                        <p class="text-sm text-text-dark-secondary">Kursi empuk, pencahayaan hangat, dan daftar putar pilihan menciptakan atmosfer santai.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-4 p-4 bg-bg-dark-secondary rounded-lg border border-border-dark" data-aos="fade-right" data-aos-duration="600" data-aos-delay="400">
                    <i class="fas fa-laptop-code text-2xl text-accent-secondary mt-1 flex-shrink-0 w-6 text-center"></i>
                    <div>
                        <h4 class="font-semibold text-lg text-white">Ruang Kerja Produktif</h4>
                        <p class="text-sm text-text-dark-secondary">Banyak stopkontak, Wi-Fi andal, dan zona tenang khusus untuk kerja fokus.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-4 p-4 bg-bg-dark-secondary rounded-lg border border-border-dark" data-aos="fade-right" data-aos-duration="600" data-aos-delay="500">
                    <i class="fas fa-users text-2xl text-accent-secondary mt-1 flex-shrink-0 w-6 text-center"></i>
                    <div>
                        <h4 class="font-semibold text-lg text-white">Pusat Komunitas</h4>
                        <p class="text-sm text-text-dark-secondary">Acara rutin, lokakarya, dan ruang yang dirancang untuk kolaborasi dan koneksi.</p>
                    </div>
                </div>
            </div>
        </div>

        <?php // Kolom Kanan Experience - Hapus AOS dari div ini ?>
        <div div data-aos="zoom-in-down" data-aos-duration="800" data-aos-delay="200" class="overflow-x-auto hide-scrollbar">
            <div class="grid grid-cols-2 gap-4 lg:gap-6">
                <?php
                    $img1 = UrlHelper::baseUrl('images/experience-illustration-1.jpg');
                    $img2 = UrlHelper::baseUrl('images/experience-illustration-1.jpg');
                    $img3 = UrlHelper::baseUrl('images/experience-illustration-1.jpg');
                    $img4 = UrlHelper::baseUrl('images/experience-illustration-1.jpg');
                ?>
                <?php // Terapkan AOS ke masing-masing gambar ?>
                <img loading="lazy" src="<?= $img1 ?>" alt="Ilustrasi Sudut Nyaman Kafe" class="w-full rounded-lg shadow-lg aspect-square object-cover transform hover:scale-105 transition-transform duration-300 border-2 border-border-dark" data-aos="zoom-in-left" data-aos-duration="600" data-aos-delay="100" onerror="this.onerror=null; this.src='<?= $placeholderImage ?>';">
                <img loading="lazy" src="<?= $img2 ?>" alt="Ilustrasi Barista Meracik Kopi" class="w-full rounded-lg shadow-lg aspect-square object-cover mt-8 lg:mt-10 transform hover:scale-105 transition-transform duration-300 border-2 border-border-dark" data-aos="zoom-in-left" data-aos-duration="600" data-aos-delay="200" onerror="this.onerror=null; this.src='<?= $placeholderImage ?>';">
                <img loading="lazy" src="<?= $img3 ?>" alt="Ilustrasi Acara Komunitas di Kafe" class="w-full rounded-lg shadow-lg aspect-square object-cover transform hover:scale-105 transition-transform duration-300 border-2 border-border-dark" data-aos="zoom-in-left" data-aos-duration="600" data-aos-delay="300" onerror="this.onerror=null; this.src='<?= $placeholderImage ?>';">
                <img loading="lazy" src="<?= $img4 ?>" alt="Ilustrasi Orang Bekerja di Kafe" class="w-full rounded-lg shadow-lg aspect-square object-cover mt-8 lg:mt-10 transform hover:scale-105 transition-transform duration-300 border-2 border-border-dark" data-aos="zoom-in-left" data-aos-duration="600" data-aos-delay="400" onerror="this.onerror=null; this.src='<?= $placeholderImage ?>';">
            </div>
        </div>

    </div>
</section>

<?php // Pastikan section lain yang sebelumnya dikomentari, sekarang aktif kembali ?>
<section id="menu" class="section-padding bg-bg-dark-secondary overflow-hidden">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl md:text-5xl font-bold text-center mb-10 text-white" data-aos="fade-up">Kreasi Khas Kami</h2>

        <?php if (!empty($topItems)): ?>
        <div class="mb-12" data-aos="fade-up">
             <h3 class="text-2xl font-semibold text-center mb-8 text-accent-primary">Top 5 Item Terpopuler</h3>
             <div class="relative">
                <div class="swiper-container-wrapper relative"> <?php // Wrapper penting untuk overflow:hidden ?>
                    <div class="swiper-container featured-menu-slider">
                        <div class="swiper-wrapper pb-8">
                            <?php foreach ($topItems as $topItem): ?>
                                <?php
                                    $topItemImagePath = !empty($topItem['image_path'])
                                                        ? UrlHelper::baseUrl($topItem['image_path']) // Gunakan baseUrl
                                                        : $placeholderImage;
                                ?>
                                <div class="swiper-slide h-auto px-1.5 sm:px-2">
                                    <div class="menu-item menu-card relative bg-bg-dark rounded-lg shadow-lg overflow-hidden group border border-border-dark flex flex-col h-full">
                                        <div class="relative h-52 w-full">
                                            <img loading="lazy" src="<?= $topItemImagePath ?>" alt="<?= SanitizeHelper::html($topItem['name']) ?>"
                                                class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                onerror="this.onerror=null; this.src='<?= $placeholderImage ?>';">
                                        </div>
                                        <div class="p-4 sm:p-5 flex flex-col flex-grow">
                                            <h4 class="font-semibold text-lg mb-1 text-white truncate"><?= SanitizeHelper::html($topItem['name']) ?></h4>
                                            <p class="text-xs text-text-dark-secondary mb-3 flex-grow min-h-[3em] line-clamp-2">
                                                <?= SanitizeHelper::html($topItem['description'] ?? '...') ?>
                                            </p>
                                            <div class="flex justify-between items-center mt-auto pt-2">
                                                <span class="font-bold text-lg text-accent-primary">
                                                    <?= NumberHelper::formatCurrencyIDR($topItem['price']) ?>
                                                </span>
                                                <span class="text-xs text-text-dark-muted" title="Total Terjual (30 Hari Terakhir)">
                                                    <i class="fas fa-star text-yellow-400"></i> <?= $topItem['total_quantity'] ?? 0 ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div> <?php // Akhir swiper-container-wrapper relative ?>
                <div class="swiper-button-prev swiper-button-prev-top !absolute !top-1/2 !-translate-y-1/2 !-left-0 text-accent-primary opacity-70 hover:opacity-100 transition-opacity z-10 cursor-pointer p-2"><i class="fas fa-chevron-left text-xl"></i></div>
                 <div class="swiper-button-next swiper-button-next-top !absolute !top-1/2 !-translate-y-1/2 !-right-0 text-accent-primary opacity-70 hover:opacity-100 transition-opacity z-10 cursor-pointer p-2"><i class="fas fa-chevron-right text-xl"></i></div>
             </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($categories)): ?>
        <div class="flex flex-wrap justify-center gap-3 md:gap-4 my-10" id="menu-filter-buttons" data-aos="fade-up">
             <button class="menu-filter-btn active" data-filter="all">Semua</button>
             <?php foreach ($categories as $category): ?>
             <?php
                $categorySlug = SanitizeHelper::html(strtolower(str_replace(' ', '-', $category['name'])));
             ?>
             <button class="menu-filter-btn" data-filter="<?= $categorySlug ?>">
                 <?= SanitizeHelper::html($category['name']) ?>
             </button>
             <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($menuItems)): ?>
        <div class="mt-0 sm:mt-8" data-aos="fade-up" data-aos-delay="100">
             <div class="relative">
                 <div class="swiper-container-wrapper relative"> <?php // Wrapper penting untuk overflow:hidden ?>
                     <div class="swiper-container full-menu-slider">
                         <div class="swiper-wrapper pb-8">
                             <?php foreach ($menuItems as $item): ?>
                                 <?php
                                     $imagePath = !empty($item['image_path'])
                                                ? UrlHelper::baseUrl($item['image_path']) // Gunakan baseUrl
                                                : $placeholderImage;
                                     $categorySlug = SanitizeHelper::html(strtolower(str_replace(' ', '-', $item['category_name'] ?? 'uncategorized')));
                                 ?>
                                 <div class="swiper-slide h-auto px-1.5 sm:px-2" data-category="<?= $categorySlug ?>">
                                     <div class="menu-item menu-card relative bg-bg-dark rounded-lg shadow-lg overflow-hidden group border border-border-dark flex flex-col h-full">
                                        <div class="relative h-48 w-full">
                                             <img loading="lazy" src="<?= $imagePath ?>" alt="<?= SanitizeHelper::html($item['name']) ?>"
                                                class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                onerror="this.onerror=null; this.src='<?= $placeholderImage ?>';">
                                        </div>
                                        <div class="p-4 sm:p-5 flex flex-col flex-grow">
                                            <h3 class="font-semibold text-lg mb-1 text-white truncate"><?= SanitizeHelper::html($item['name']) ?></h3>
                                            <p class="text-xs text-text-dark-secondary mb-3 flex-grow min-h-[3em] line-clamp-2">
                                                <?= SanitizeHelper::html($item['description'] ?? '...') ?>
                                            </p>
                                            <div class="flex justify-between items-center mt-auto pt-2">
                                                <span class="font-bold text-base text-accent-primary">
                                                    <?= NumberHelper::formatCurrencyIDR($item['price']) ?>
                                                </span>
                                                <?php /* Tombol quick view/add to cart bisa di sini jika perlu */ ?>
                                            </div>
                                        </div>
                                     </div>
                                 </div>
                             <?php endforeach; ?>
                         </div>
                     </div>
                 </div> <?php // Akhir swiper-container-wrapper relative ?>
                 <div class="swiper-button-prev swiper-button-prev-full !absolute !top-1/2 !-translate-y-1/2 !-left-0 text-accent-primary opacity-70 hover:opacity-100 transition-opacity z-10 cursor-pointer p-2"><i class="fas fa-chevron-left text-xl"></i></div>
                 <div class="swiper-button-next swiper-button-next-full !absolute !top-1/2 !-translate-y-1/2 !-right-0 text-accent-primary opacity-70 hover:opacity-100 transition-opacity z-10 cursor-pointer p-2"><i class="fas fa-chevron-right text-xl"></i></div>
             </div>
        </div>
        <?php endif; ?>

        <div class="text-center mt-16" data-aos="fade-up">
             <p class="text-text-dark-secondary text-sm">Temukan QR Code di meja Anda untuk memulai pemesanan digital.</p>
        </div>

         <?php // Modal Quick View (Struktur saja, konten diisi JS jika ada) ?>
        <div id="quick-view-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[150] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-bg-dark-secondary rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto relative transform scale-95 transition-all duration-300 ease-out" id="modal-content">
                <button id="close-modal-btn" class="absolute top-4 right-4 text-text-dark-secondary hover:text-white text-2xl transition-colors z-10"> <i class="fas fa-times"></i> </button>
                 <?php // Konten modal akan dimuat di sini oleh JS ?>
            </div>
         </div>

    </div>
</section>

<section id="gallery" class="section-padding overflow-hidden bg-bg-dark-primary">
     <div class="container mx-auto px-4 sm:px-6 lg:px-8">
         <h2 class="text-4xl md:text-5xl font-bold text-center mb-16 text-white" data-aos="fade-up">Abadikan Momen</h2>
        <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4" id="image-gallery">
             <?php // Contoh item galeri dengan AOS ?>
             <div class="gallery-item break-inside-avoid relative overflow-hidden rounded-lg shadow-lg cursor-pointer group" data-aos="fade-up" data-aos-delay="50" data-src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>">
                <img loading="lazy" src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>" alt="Suasana Interior Kafe" class="w-full h-auto object-cover transform group-hover:scale-110 transition-transform duration-500 ease-in-out" onerror="this.onerror=null; this.src='<?= $placeholderImage ?>';">
                <div class="gallery-overlay"> <i class="fas fa-search-plus"></i> </div>
            </div>
             <div class="gallery-item break-inside-avoid relative overflow-hidden rounded-lg shadow-lg cursor-pointer group" data-aos="fade-up" data-aos-delay="100" data-src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>">
                <img loading="lazy" src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>" alt="Seni Latte Art Detail" class="w-full h-auto object-cover transform group-hover:scale-110 transition-transform duration-500 ease-in-out" onerror="this.onerror=null; this.src='<?= $placeholderImage ?>';">
                 <div class="gallery-overlay"> <i class="fas fa-search-plus"></i> </div>
            </div>
            <div class="gallery-item break-inside-avoid relative overflow-hidden rounded-lg shadow-lg cursor-pointer group" data-aos="fade-up" data-aos-delay="150" data-src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>">
                <img loading="lazy" src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>" alt="Area Duduk Outdoor Malam Hari" class="w-full h-auto object-cover transform group-hover:scale-110 transition-transform duration-500 ease-in-out">
                 <div class="gallery-overlay"> <i class="fas fa-search-plus"></i> </div>
            </div>
             <div class="gallery-item break-inside-avoid relative overflow-hidden rounded-lg shadow-lg cursor-pointer group" data-aos="fade-up" data-aos-delay="200" data-src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>">
                <img loading="lazy" src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>" alt="Aneka Pastry di Etalase" class="w-full h-auto object-cover transform group-hover:scale-110 transition-transform duration-500 ease-in-out">
                 <div class="gallery-overlay"> <i class="fas fa-search-plus"></i> </div>
            </div>
             <?php // Tambahkan item galeri lainnya dengan data-aos berbeda ?>
             </div>
    </div>
</section>

<section id="testimonials" class="section-padding bg-bg-dark-secondary">
     <div class="container mx-auto px-4 sm:px-6 lg:px-8">
         <h2 class="text-4xl md:text-5xl font-bold text-center mb-16 text-white" data-aos="fade-up">Apa Kata Pelanggan Kami</h2>
         <div class="swiper-container-wrapper relative max-w-6xl mx-auto"> <?php // Wrapper penting untuk overflow:hidden ?>
             <div class="swiper-container testimonial-slider-v2 relative" data-aos="fade-up" data-aos-delay="100">
                 <div class="swiper-wrapper pb-16">
                      <?php // Contoh slide testimonial ?>
                     <div class="swiper-slide">
                         <div class="bg-bg-dark rounded-lg shadow-xl p-8 text-center border border-border-dark relative">
                             <img loading="lazy" src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>"
                                  alt="Avatar Pelanggan 1"
                                  class="w-20 h-20 rounded-full mx-auto mb-5 border-4 border-bg-dark-secondary absolute -top-10 left-1/2 transform -translate-x-1/2 shadow-md"
                                  onerror="this.onerror=null; this.src='<?= $placeholderImage ?>';">
                                  <div class="pt-12">
                                    <i class="fas fa-quote-left text-accent-primary text-3xl mb-4 opacity-70"></i>
                                     <p class="italic mb-6 text-lg leading-relaxed">"Kedai kopi terbaik di Malang! Tempat sempurna untuk bersantai atau menyelesaikan pekerjaan. Stafnya juga super ramah."</p>
                                      <p class="font-semibold text-base text-white">Aisha N.</p>
                                      <p class="text-xs text-text-dark-secondary">Pengunjung Setia</p>
                                      <div class="flex justify-center mt-4 text-accent-primary text-sm"> <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i> </div>
                                 </div>
                         </div>
                     </div>
                     <div class="swiper-slide">
                         <div class="bg-bg-dark relative rounded-lg shadow-xl p-8 text-center border border-border-dark relative">
                             <img loading="lazy" src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>"
                                  alt="Avatar Pelanggan 2"
                                  class="w-20 h-20 rounded-full mx-auto mb-5 border-4 border-bg-dark-secondary
                                         absolute -top-10 left-1/2 transform -translate-x-1/2 shadow-md">
                              <div class="pt-12">
                                 <i class="fas fa-quote-left text-accent-primary text-3xl mb-4 opacity-70"></i>
                                 <p class="italic mb-6 text-lg leading-relaxed">"Pilihan manual brew-nya luar biasa. Baristanya sangat paham kopi dan senang merekomendasikan biji terbaik."</p>
                                  <p class="font-semibold text-base text-white">Budi S.</p>
                                  <p class="text-xs text-text-dark-secondary">Pecinta Kopi</p>
                                  <div class="flex justify-center mt-4 text-accent-primary text-sm"> <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> </div>
                              </div>
                         </div>
                     </div>
                     <div class="swiper-slide">
                         <div class="bg-bg-dark relative rounded-lg shadow-xl p-8 text-center border border-border-dark relative">
                             <img loading="lazy" src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>"
                                  alt="Avatar Pelanggan 2"
                                  class="w-20 h-20 rounded-full mx-auto mb-5 border-4 border-bg-dark-secondary
                                         absolute -top-10 left-1/2 transform -translate-x-1/2 shadow-md">
                              <div class="pt-12">
                                 <i class="fas fa-quote-left text-accent-primary text-3xl mb-4 opacity-70"></i>
                                 <p class="italic mb-6 text-lg leading-relaxed">"Pilihan manual brew-nya luar biasa. Baristanya sangat paham kopi dan senang merekomendasikan biji terbaik."</p>
                                  <p class="font-semibold text-base text-white">Budi S.</p>
                                  <p class="text-xs text-text-dark-secondary">Pecinta Kopi</p>
                                  <div class="flex justify-center mt-4 text-accent-primary text-sm"> <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> </div>
                              </div>
                         </div>
                     </div>
                     <div class="swiper-slide">
                         <div class="bg-bg-dark relative rounded-lg shadow-xl p-8 text-center border border-border-dark relative">
                             <img loading="lazy" src="<?= UrlHelper::baseUrl('images/experience-illustration-1.jpg') ?>"
                                  alt="Avatar Pelanggan 2"
                                  class="w-20 h-20 rounded-full mx-auto mb-5 border-4 border-bg-dark-secondary
                                         absolute -top-10 left-1/2 transform -translate-x-1/2 shadow-md">
                              <div class="pt-12">
                                 <i class="fas fa-quote-left text-accent-primary text-3xl mb-4 opacity-70"></i>
                                 <p class="italic mb-6 text-lg leading-relaxed">"Pilihan manual brew-nya luar biasa. Baristanya sangat paham kopi dan senang merekomendasikan biji terbaik."</p>
                                  <p class="font-semibold text-base text-white">Budi S.</p>
                                  <p class="text-xs text-text-dark-secondary">Pecinta Kopi</p>
                                  <div class="flex justify-center mt-4 text-accent-primary text-sm"> <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i> </div>
                              </div>
                         </div>
                     </div>
                     </div>
                 <div class="swiper-pagination !bottom-0 !relative !mt-8"></div>
             </div>
         </div> <?php // Akhir swiper-container-wrapper relative ?>
    </div>
</section>

<section id="location" class="section-padding bg-bg-dark-primary">
     <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <?php // Kolom Kiri Lokasi dengan AOS ?>
            <div data-aos="fade-right" data-aos-duration="800">
                <h2 class="text-4xl md:text-5xl font-bold mb-6 text-white">Temukan Kami Disini</h2>
                <p class="text-lg text-text-dark-secondary mb-8">Berlokasi strategis di kabupaten Malang, kami siap menyambut Anda.</p>
                <div class="space-y-5 mb-8 text-sm">
                    <p class="flex items-start"><i class="fas fa-map-marker-alt w-5 mt-1 mr-4 text-accent-primary flex-shrink-0"></i> <span class="flex-1 text-text-dark-secondary"><?= $cafeAddress ?></span></p>
                    <p class="flex items-start"><i class="fas fa-phone-alt w-5 mt-1 mr-4 text-accent-primary flex-shrink-0"></i> <a href="tel:<?= SanitizeHelper::html(str_replace('-', '', $cafePhone)) ?>" class="hover:text-accent-primary transition text-text-dark-secondary"><?= $cafePhone ?></a></p>
                    <p class="flex items-start"><i class="fas fa-envelope w-5 mt-1 mr-4 text-accent-primary flex-shrink-0"></i> <a href="mailto:info@namakafeanda.com" class="hover:text-accent-primary transition text-text-dark-secondary">info@namakafe.com</a></p>
                    <p class="flex items-start"><i class="fas fa-clock w-5 mt-1 mr-4 text-accent-primary flex-shrink-0"></i> <span class="flex-1 text-text-dark-secondary">Senin - Minggu : 10:00 - 23:00 WIB</span></p>
                </div>
                 <a href="https://maps.app.goo.gl/YxhZAfuerRJn7Hm57" target="_blank" rel="noopener noreferrer" class="btn btn-accent inline-flex items-center px-7 py-3 rounded-full font-semibold transition text-base transform hover:scale-105">
                    <i class="fas fa-directions mr-2"></i> Lihat Peta & Arah
                 </a>
            </div>
             <?php // Kolom Kanan Lokasi dengan AOS ?>
            <div class="rounded-lg overflow-hidden shadow-xl border-2 border-border-dark" data-aos="zoom-in-down" data-aos-duration="800" data-aos-delay="200">
                 <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3212.4867603811585!2d112.64182154115723!3d-8.171733991975637!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd61f6b0266c8c3%3A0xe868ece3ca3050d8!2sStay%20With%20Me%20Coffee!5e0!3m2!1sid!2sid!4v1745879756956!5m2!1sid!2sid" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta Lokasi Kafe"></iframe>
            </div>
        </div>
    </div>
</section>

<?php // Pastikan section yang sebelumnya dikomentari sudah aktif kembali ?>