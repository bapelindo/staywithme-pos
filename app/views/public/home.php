<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;

$categories = $categories ?? [];
$menuItems = $menuItems ?? [];

// Inject mock data if DB is empty
if (empty($categories)) {
    $categories = [
        ['id' => 1, 'name' => 'Signature Blend'],
        ['id' => 2, 'name' => 'Manual Brew'],
        ['id' => 3, 'name' => 'Artisan Pastry'],
    ];
}
if (empty($menuItems)) {
    $menuItems = [
        ['category_id' => 1, 'name' => 'Bacelor Cafe Latte', 'description' => 'Espresso house blend, secret syrup, steamed oat milk, bubuk emas eksklusif.', 'price' => 55000, 'image_path' => 'images/menu-v2.jpg'],
        ['category_id' => 2, 'name' => 'Ethiopia Yirgacheffe V60', 'description' => 'Kopi seduh manual dengan profil rasa floral, jasmine, bergamot, dan sentuhan black tea yang menyegarkan.', 'price' => 40000, 'image_path' => 'images/experience-illustration-1-v2.jpg'],
        ['category_id' => 3, 'name' => 'Almond Butter Croissant', 'description' => 'Croissant panggang dua kali yang renyah di luar, diisi penuh dengan frangipane dan taburan almond panggang.', 'price' => 35000, 'image_path' => 'images/menu-v2.jpg'],
    ];
}

$categoryMap = [];
foreach ($categories as $cat) {
    $categoryMap[$cat['id']] = $cat['name'];
}

// Get top 3 items for the display
$item1 = $menuItems[0] ?? null;
$item2 = $menuItems[1] ?? null;
$item3 = $menuItems[2] ?? null;

$placeholderImage = UrlHelper::baseUrl('images/menu-placeholder-v2.jpg');

function getImageUrl($item, $placeholder)
{
    if (!$item)
        return $placeholder;
    return !empty($item['image_path']) ? UrlHelper::baseUrl($item['image_path']) : $placeholder;
}
function getCatName($item, $map)
{
    if (!$item)
        return 'Menu';
    return $map[$item['category_id']] ?? 'Menu';
}
?>

<!-- Hero Section -->
<section class="relative min-h-[100svh] flex items-center justify-center pt-20 overflow-hidden"
    data-stitch-vh="min-h-[100svh]===min-h-screen">
    <!-- Background Image with Parallax feel -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-b from-[#050505]/80 via-[#050505]/40 to-[#050505] z-10"></div>
        <img alt="Interior Cafe" class="w-full h-full object-cover opacity-60 scale-105 animate-pulse-slow"
            src="<?= UrlHelper::baseUrl('images/experience-illustration-1-v2.jpg') ?>">
    </div>

    <!-- Huge Background Typography -->
    <div class="absolute inset-0 z-0 flex items-center justify-center pointer-events-none overflow-hidden select-none">
        <h1
            class="text-[5rem] sm:text-[8rem] md:text-[14rem] font-display font-black text-outline whitespace-nowrap opacity-30 transform -translate-y-12 mix-blend-overlay">
            ROASTERY
        </h1>
    </div>

    <div class="relative z-20 text-center px-6 max-w-5xl mx-auto flex flex-col items-center mt-12">
        <div class="reveal-up">
            <span
                class="font-sans text-xs text-primary uppercase tracking-[0.4em] mb-8 block flex items-center justify-center gap-4">
                <span class="w-12 h-[1px] bg-primary/50"></span>
                Artisan Roastery
                <span class="w-12 h-[1px] bg-primary/50"></span>
            </span>
        </div>
        <h1
            class="reveal-up reveal-delay-1 font-display text-5xl md:text-8xl lg:text-9xl font-bold text-white leading-[0.9] mb-8 tracking-tighter mix-blend-screen drop-shadow-2xl">
            <span class="text-primary italic font-light drop-shadow-[0_0_30px_rgba(233,193,118,0.4)]">
                <font color="#ffffff"><span style="font-style: normal;"><b>Karya&nbsp;</b></span></font>BacelorCafe.
            </span>
            <br>Cita Rasa <i class="text-primary font-light">Berkelas.</i>
        </h1>
        <p
            class="reveal-up reveal-delay-2 font-sans text-lg md:text-xl text-on-surface-variant max-w-2xl mx-auto mb-16 font-light leading-relaxed">
            Ruang tenang untuk menepi dari keramaian, ditemani secangkir kopi artisan yang dikurasi dengan presisi. Jeda
            yang sempurna di tengah rutinitas Anda.
        </p>
        <div class="reveal-up reveal-delay-3">
            <a class="relative inline-flex items-center justify-center px-10 py-5 glass-panel text-white font-sans text-sm uppercase tracking-[0.2em] hover:bg-white hover:text-black transition-all duration-700 ease-[cubic-bezier(0.4,0,0.2,1)] group overflow-hidden rounded-full"
                href="/menu">
                <span class="relative z-10 flex items-center gap-4">
                    Jelajahi Menu
                    <span
                        class="material-symbols-outlined text-xl group-hover:translate-x-2 transition-transform duration-500">trending_flat</span>
                </span>
                <div
                    class="absolute inset-0 bg-gold-gradient opacity-0 group-hover:opacity-100 transition-opacity duration-700 z-0">
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Signature Collections (Staggered Layout) -->
<section class="py-24 px-6 md:px-12 max-w-[1600px] mx-auto relative z-10" id="menu">
    <div class="mb-20 flex flex-col items-center text-center reveal-up">
        <span class="font-sans text-xs text-primary uppercase tracking-[0.3em] mb-4 block">Penawaran Terkurasi</span>
        <h2 class="font-display text-5xl md:text-7xl text-white mb-8 tracking-tight"><i
                class="text-primary font-light">Koleksi</i> Utama</h2>
        <div class="h-[1px] w-24 bg-gradient-to-r from-transparent via-primary to-transparent"></div>
    </div>

    <div class="flex flex-col gap-20">
        <?php if ($item1): ?>
            <!-- Item 1: Left Aligned -->
            <div class="flex flex-col md:flex-row items-center gap-12 md:gap-16 group reveal-up">
                <div class="w-full md:w-3/5 relative">
                    <div
                        class="absolute inset-0 bg-primary/20 blur-[100px] rounded-full opacity-0 group-hover:opacity-50 transition-opacity duration-1000">
                    </div>
                    <div class="relative h-[400px] md:h-[500px] w-full glass-panel overflow-hidden rounded-sm">
                        <img alt="<?= SanitizeHelper::html($item1['name']) ?>"
                            class="absolute inset-0 w-full h-full object-cover opacity-70 saturate-50 group-hover:saturate-100 group-hover:scale-105 transition-all duration-[1.5s] ease-[cubic-bezier(0.4,0,0.2,1)]"
                            src="<?= getImageUrl($item1, $placeholderImage) ?>">
                    </div>
                </div>
                <div class="w-full md:w-2/5 flex flex-col justify-center">
                    <span class="font-sans text-xs text-primary uppercase tracking-[0.2em] mb-4 block">01 /
                        <?= SanitizeHelper::html(getCatName($item1, $categoryMap)) ?></span>
                    <h3 class="font-display text-3xl md:text-4xl text-white mb-6"><?php
                    $words = explode(' ', $item1['name']);
                    $firstWord = array_shift($words);
                    echo "<i class='text-primary'>" . SanitizeHelper::html($firstWord) . "</i> " . SanitizeHelper::html(implode(' ', $words));
                    ?></h3>
                    <p class="font-sans text-on-surface-variant text-lg font-light leading-relaxed mb-8">
                        <?= SanitizeHelper::html($item1['description']) ?>
                    </p>
                    <div class="font-display text-xl text-white mb-8">
                        <?= NumberHelper::formatCurrencyIDR($item1['price']) ?></div>
                    <a class="inline-flex items-center text-xs font-sans uppercase tracking-[0.2em] text-white hover:text-primary transition-colors duration-300"
                        href="<?= UrlHelper::baseUrl('/menu') ?>">
                        Temukan Lebih Lanjut <span class="material-symbols-outlined ml-2 text-sm">arrow_forward</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($item2): ?>
            <!-- Item 2: Right Aligned -->
            <div class="flex flex-col md:flex-row-reverse items-center gap-12 md:gap-16 group reveal-up">
                <div class="w-full md:w-1/2 relative">
                    <div
                        class="absolute inset-0 bg-primary/20 blur-[100px] rounded-full opacity-0 group-hover:opacity-50 transition-opacity duration-1000">
                    </div>
                    <div class="relative h-[350px] md:h-[450px] w-full glass-panel overflow-hidden rounded-sm md:mt-16">
                        <img alt="<?= SanitizeHelper::html($item2['name']) ?>"
                            class="absolute inset-0 w-full h-full object-cover opacity-70 saturate-50 group-hover:saturate-100 group-hover:scale-105 transition-all duration-[1.5s] ease-[cubic-bezier(0.4,0,0.2,1)]"
                            src="<?= getImageUrl($item2, $placeholderImage) ?>">
                    </div>
                </div>
                <div class="w-full md:w-1/2 flex flex-col justify-center text-left md:text-right md:items-end">
                    <span class="font-sans text-xs text-primary uppercase tracking-[0.2em] mb-4 block">02 /
                        <?= SanitizeHelper::html(getCatName($item2, $categoryMap)) ?></span>
                    <h3 class="font-display text-3xl md:text-4xl text-white mb-6"><?php
                    $words = explode(' ', $item2['name']);
                    $firstWord = array_shift($words);
                    echo "<i class='text-primary'>" . SanitizeHelper::html($firstWord) . "</i> " . SanitizeHelper::html(implode(' ', $words));
                    ?></h3>
                    <p
                        class="font-sans text-on-surface-variant text-lg font-light leading-relaxed mb-8 md:text-right max-w-md">
                        <?= SanitizeHelper::html($item2['description']) ?>
                    </p>
                    <div class="font-display text-xl text-white mb-8">
                        <?= NumberHelper::formatCurrencyIDR($item2['price']) ?></div>
                    <a class="inline-flex items-center text-xs font-sans uppercase tracking-[0.2em] text-white hover:text-primary transition-colors duration-300 flex-row-reverse"
                        href="<?= UrlHelper::baseUrl('/menu') ?>">
                        <span class="material-symbols-outlined mr-2 text-sm transform rotate-180">arrow_forward</span>
                        Temukan Lebih Lanjut
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($item3): ?>
            <!-- Item 3: Center Highlight -->
            <div class="flex flex-col items-center gap-12 group reveal-up pt-16">
                <div class="w-full max-w-4xl relative">
                    <div
                        class="absolute -inset-10 bg-primary/10 blur-[100px] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-1000">
                    </div>
                    <div class="relative h-[450px] md:h-[600px] w-full glass-panel overflow-hidden rounded-sm">
                        <img alt="<?= SanitizeHelper::html($item3['name']) ?>"
                            class="absolute inset-0 w-full h-full object-cover opacity-70 saturate-50 group-hover:saturate-100 group-hover:scale-105 transition-all duration-[1.5s] ease-[cubic-bezier(0.4,0,0.2,1)]"
                            src="<?= getImageUrl($item3, $placeholderImage) ?>">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-transparent to-transparent"></div>
                        <div class="absolute bottom-0 left-0 w-full p-8 md:p-12 text-center">
                            <span class="font-sans text-xs text-primary uppercase tracking-[0.2em] mb-4 block">03 /
                                <?= SanitizeHelper::html(getCatName($item3, $categoryMap)) ?></span>
                            <h3 class="font-display text-4xl md:text-5xl text-white mb-6"><?php
                            $words = explode(' ', $item3['name']);
                            $firstWord = array_shift($words);
                            echo "<i class='text-primary'>" . SanitizeHelper::html($firstWord) . "</i> " . SanitizeHelper::html(implode(' ', $words));
                            ?></h3>
                            <p class="font-sans text-on-surface-variant text-lg font-light max-w-2xl mx-auto mb-6">
                                <?= SanitizeHelper::html($item3['description']) ?>
                            </p>
                            <div class="font-display text-2xl text-white mb-8">
                                <?= NumberHelper::formatCurrencyIDR($item3['price']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- 4. SPACES (Horizontal Scroll Gallery - Awwwards Grade) -->
<section id="gallery" class="relative bg-[#020202] overflow-hidden" style="height: 100vh;">
    <!-- Pinned Container -->
    <div id="gallery-pin-container" class="h-screen flex items-center">
        <!-- Horizontal Track -->
        <div id="gallery-track" class="flex gap-8 md:gap-16 px-[10vw] h-full items-center w-max">
            
            <!-- Title Panel -->
            <div class="flex-shrink-0 w-[80vw] md:w-[40vw] flex flex-col justify-center pr-12">
                <span class="font-sans text-xs text-primary uppercase tracking-[0.4em] mb-6 block flex items-center gap-4">
                    <span class="w-8 h-[1px] bg-primary/50"></span>
                    Momen
                </span>
                <h2 class="font-display text-5xl md:text-7xl lg:text-8xl text-white mb-8 leading-[0.9] tracking-tighter">
                    Visual <br><i class="text-primary font-light">Jurnal.</i>
                </h2>
                <p class="font-sans text-on-surface-variant text-lg font-light leading-relaxed max-w-md">
                    Eksplorasi sudut-sudut arsitektur brutalis kami. Sebuah suaka visual yang dirancang untuk merayakan keheningan dan setiap detail proses penyeduhan artisan.
                </p>
            </div>

            <!-- Image Panel 1 -->
            <div class="flex-shrink-0 w-[70vw] md:w-[45vw] h-[50vh] md:h-[65vh] relative glass-panel overflow-hidden rounded-sm group">
                <div class="absolute inset-0 bg-primary/10 opacity-0 group-hover:opacity-100 transition-opacity duration-700 z-10 pointer-events-none"></div>
                <div class="gallery-img-container w-full h-full relative overflow-hidden">
                    <img src="<?= UrlHelper::baseUrl('images/experience-illustration-1-v2.jpg') ?>" class="absolute inset-0 w-[130%] h-full object-cover saturate-50 group-hover:saturate-100 transition-all duration-700" alt="Main Sanctuary" data-speed="0.8">
                </div>
                <div class="absolute bottom-6 left-6 z-20 overflow-hidden">
                    <p class="font-sans text-xs uppercase tracking-widest text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-500">01 / Main Sanctuary</p>
                </div>
            </div>

            <!-- Image Panel 2 -->
            <div class="flex-shrink-0 w-[60vw] md:w-[35vw] h-[40vh] md:h-[55vh] relative glass-panel overflow-hidden rounded-sm group transform translate-y-12 md:translate-y-24">
                <div class="absolute inset-0 bg-primary/10 opacity-0 group-hover:opacity-100 transition-opacity duration-700 z-10 pointer-events-none"></div>
                <div class="gallery-img-container w-full h-full relative overflow-hidden">
                    <img src="<?= UrlHelper::baseUrl('images/menu-v2.jpg') ?>" class="absolute inset-0 w-[130%] h-full object-cover saturate-50 group-hover:saturate-100 transition-all duration-700" alt="Artisan Tools" data-speed="0.8">
                </div>
                <div class="absolute bottom-6 left-6 z-20 overflow-hidden">
                    <p class="font-sans text-xs uppercase tracking-widest text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-500">02 / Artisan Tools</p>
                </div>
            </div>

            <!-- Image Panel 3 -->
            <div class="flex-shrink-0 w-[75vw] md:w-[50vw] h-[55vh] md:h-[70vh] relative glass-panel overflow-hidden rounded-sm group transform -translate-y-8 md:-translate-y-12">
                <div class="absolute inset-0 bg-primary/10 opacity-0 group-hover:opacity-100 transition-opacity duration-700 z-10 pointer-events-none"></div>
                <div class="gallery-img-container w-full h-full relative overflow-hidden">
                    <img src="<?= UrlHelper::baseUrl('images/experience-illustration-1-v2.jpg') ?>" class="absolute inset-0 w-[130%] h-full object-cover saturate-50 group-hover:saturate-100 transition-all duration-700" alt="Brutalist Textures" data-speed="0.8">
                </div>
                <div class="absolute bottom-6 left-6 z-20 overflow-hidden">
                    <p class="font-sans text-xs uppercase tracking-widest text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-500">03 / Brutalist Textures</p>
                </div>
            </div>

            <!-- End Spacer -->
            <div class="flex-shrink-0 w-[10vw]"></div>
            
        </div>
    </div>
</section>

<script>
// We need to wait for the main layout's GSAP to load and initialize.
// The public_layout.php has a DOMContentLoaded listener that registers ScrollTrigger.
// We will add another listener here that runs slightly after to ensure ScrollTrigger is ready.
window.addEventListener('load', () => {
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        const gallerySection = document.getElementById('gallery');
        const track = document.getElementById('gallery-track');
        
        if (gallerySection && track) {
            // Calculate total scroll distance based on track width vs viewport width
            const scrollDistance = track.scrollWidth - window.innerWidth;
            
            // Create the horizontal scroll animation
            let scrollTween = gsap.to(track, {
                x: -scrollDistance,
                ease: "none",
                scrollTrigger: {
                    trigger: gallerySection,
                    pin: true,
                    scrub: 1, // Smooth scrubbing taking 1 second to catch up
                    start: "top top",
                    end: () => "+=" + scrollDistance, // Pin for the duration of the scroll width
                    invalidateOnRefresh: true
                }
            });

            // Add parallax effect to images inside the horizontal scroll
            // Since they are moving horizontally, we animate them in the opposite direction
            gsap.utils.toArray('.gallery-img-container img').forEach(img => {
                gsap.to(img, {
                    xPercent: -20, // Move image 20% to the left while container moves left
                    ease: "none",
                    scrollTrigger: {
                        trigger: img.closest('.flex-shrink-0'),
                        containerAnimation: scrollTween,
                        start: "left right", // When the left of the image container enters from the right
                        end: "right left",   // When the right of the image container leaves to the left
                        scrub: true
                    }
                });
            });
        }
    }
});
</script>