<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;

$appName = SanitizeHelper::html(APP_NAME ?? 'Yours Cafe');
$cafeAddress = SanitizeHelper::html(CAFE_ADDRESS ?? 'Jl. Raya Kopi No. 1, Malang');
$cafePhone = SanitizeHelper::html(CAFE_PHONE ?? '+62 822-2911-4960');
$baseUrl = rtrim(UrlHelper::baseUrl(), '/');
?>
<!DOCTYPE html>
<html class="dark" lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?= isset($pageTitle) ? SanitizeHelper::html($pageTitle) . ' - ' : '' ?><?= $appName ?> | Cinematic Canvas
    </title>

    <!-- We will use the CDN for this specific template to match exactly what the user provided -->
    <script src="https://cdn.tailwindcss.com?plugins=container-queries"></script>

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;0,900;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#e9c176",
                        "primary-glow": "rgba(233, 193, 118, 0.4)",
                        "surface": "#0a0a0a",
                        "surface-variant": "#141414",
                        "on-surface": "#ffffff",
                        "on-surface-variant": "#a3a3a3",
                        "outline-variant": "#333333",
                    },
                    "fontFamily": {
                        "display": ["Playfair Display", "serif"],
                        "sans": ["Inter", "sans-serif"],
                    },
                    "backgroundImage": {
                        'glass-gradient': 'linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.01) 100%)',
                        'gold-gradient': 'linear-gradient(135deg, #e9c176 0%, #c5a059 100%)',
                    },
                    "boxShadow": {
                        'glass': '0 8px 32px 0 rgba(0, 0, 0, 0.37)',
                        'gold-glow': '0 0 40px -10px rgba(233, 193, 118, 0.3)',
                    },
                    "animation": {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    "keyframes": {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        body {
            background-color: #050505;
            color: #ffffff;
        }

        /* Scroll Animations */
        .reveal-up {
            opacity: 0;
            transform: translateY(60px);
            transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal-up.active {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-scale {
            opacity: 0;
            transform: scale(0.9);
            transition: all 1.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal-scale.active {
            opacity: 1;
            transform: scale(1);
        }

        .reveal-delay-1 {
            transition-delay: 0.1s;
        }

        .reveal-delay-2 {
            transition-delay: 0.2s;
        }

        .reveal-delay-3 {
            transition-delay: 0.3s;
        }

        /* Glassmorphism utilities */
        .glass-panel {
            background: rgba(20, 20, 20, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Huge Typography Outline */
        .text-outline {
            color: transparent;
            -webkit-text-stroke: 1px rgba(233, 193, 118, 0.2);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #050505;
        }

        ::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #e9c176;
        }
    </style>
    <script>var APP_BASE_URL = "<?= $baseUrl ?>";</script>
</head>

<body class="antialiased selection:bg-primary/30 selection:text-primary" data-mode="connect">

    <!-- Cinematic Preloader -->
    <div id="preloader"
        class="fixed inset-0 bg-[#050505] z-[99999] flex flex-col justify-end p-8 md:p-12 overflow-hidden">
        <div
            class="flex flex-col md:flex-row md:items-end justify-between w-full max-w-[1600px] mx-auto overflow-hidden">
            <div class="font-sans text-xs uppercase tracking-[0.4em] text-primary/70 mb-4 md:mb-0 transform translate-y-full opacity-0"
                id="loader-text">Initializing Canvas</div>
            <div class="font-display text-8xl md:text-[12rem] text-white leading-[0.8] tracking-tighter"
                id="loader-counter">0</div>
        </div>
        <div class="w-full max-w-[1600px] mx-auto h-[1px] bg-white/10 mt-8 relative overflow-hidden" id="loader-bar-bg">
            <div class="absolute top-0 left-0 h-full bg-primary w-0" id="loader-bar"></div>
        </div>
    </div>

    <!-- TopNavBar -->
    <nav class="fixed top-0 w-full z-50 glass-panel border-b-0 transition-all duration-500 ease-in-out py-2"
        id="main-nav">
        <div class="flex justify-between items-center h-20 px-8 md:px-12 max-w-[1600px] mx-auto">
            <a class="font-display text-2xl tracking-widest text-on-surface uppercase group" href="<?= $baseUrl ?>/">
                <span class="text-primary group-hover:text-white transition-colors duration-500">Yours</span> Cafes
            </a>
            <div class="hidden md:flex space-x-12">
                <?php $currentUri = $_SERVER['REQUEST_URI'] ?? ''; ?>
                <a class="font-sans text-xs uppercase tracking-[0.2em] <?= ($currentUri === '/' || $currentUri === $baseUrl . '/') ? 'text-primary relative after:content-[\'\'] after:absolute after:-bottom-2 after:left-0 after:w-full after:h-[1px] after:bg-primary' : 'text-on-surface-variant hover:text-primary' ?> transition-all duration-500"
                    href="<?= $baseUrl ?>/">Beranda</a>
                <a class="font-sans text-xs uppercase tracking-[0.2em] <?= (strpos($currentUri, '/menu') !== false) ? 'text-primary relative after:content-[\'\'] after:absolute after:-bottom-2 after:left-0 after:w-full after:h-[1px] after:bg-primary' : 'text-on-surface-variant hover:text-primary' ?> transition-all duration-500"
                    href="<?= $baseUrl ?>/menu">Menu</a>
                <a class="font-sans text-xs uppercase tracking-[0.2em] text-on-surface-variant hover:text-primary transition-all duration-500"
                    href="<?= UrlHelper::baseUrl('/admin') ?>">Staff Access</a>
            </div>
            <a class="hidden md:inline-flex items-center justify-center px-8 py-4 bg-transparent border border-primary/50 text-primary font-sans text-xs uppercase tracking-[0.2em] hover:bg-gold-gradient hover:text-black hover:border-transparent hover:shadow-gold-glow transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] rounded-sm"
                href="https://wa.me/<?= SanitizeHelper::html(str_replace([' ', '-', '+'], '', $cafePhone)) ?>">
                Pesan Meja
            </a>
            <button id="mobile-menu-btn" class="md:hidden text-primary z-50 relative">
                <span class="material-symbols-outlined text-3xl transition-transform duration-300"
                    id="mobile-menu-icon">menu</span>
            </button>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay"
        class="fixed inset-0 bg-[#050505]/95 backdrop-blur-xl z-40 transform translate-x-full transition-transform duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] flex flex-col items-center justify-center">
        <div class="flex flex-col space-y-8 text-center">
            <a class="mobile-nav-link font-display text-3xl text-white hover:text-primary transition-colors duration-300"
                href="<?= $baseUrl ?>/">Beranda</a>
            <a class="mobile-nav-link font-display text-3xl text-white hover:text-primary transition-colors duration-300"
                href="<?= $baseUrl ?>/menu">Menu</a>
            <a class="mobile-nav-link font-display text-3xl text-white hover:text-primary transition-colors duration-300"
                href="<?= UrlHelper::baseUrl('/admin') ?>">Staff Access</a>
            <a class="mobile-nav-link mt-8 px-8 py-4 border border-primary text-primary font-sans text-xs uppercase tracking-[0.2em] hover:bg-primary hover:text-black transition-colors duration-300 rounded-sm"
                href="https://wa.me/<?= SanitizeHelper::html(str_replace([' ', '-', '+'], '', $cafePhone)) ?>">
                Pesan Meja
            </a>
        </div>
    </div>

    <main>
        <?php
        if (isset($viewPath) && file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo '<div class="min-h-screen flex items-center justify-center"><p class="text-slate-400">Halaman tidak ditemukan.</p></div>';
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="w-full py-32 bg-[#020202] border-t border-white/5 relative overflow-hidden">
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[1px] bg-gradient-to-r from-transparent via-primary/50 to-transparent">
        </div>
        <div
            class="grid grid-cols-1 md:grid-cols-4 gap-16 px-8 md:px-12 max-w-[1600px] mx-auto relative z-10 reveal-up">
            <div class="md:col-span-2">
                <h2 class="font-display text-4xl mb-6 text-white"><?= $appName ?>.</h2>
                <p class="font-sans text-on-surface-variant text-sm font-light leading-relaxed max-w-sm mb-8">
                    Kami merancang ruang, waktu, dan rasa untuk memberikan jeda yang sempurna di tengah rutinitas Anda.
                </p>
                <div class="flex gap-4 mt-2">
                    <a href="https://wa.me/<?= SanitizeHelper::html(str_replace([' ', '-', '+'], '', $cafePhone)) ?>"
                        target="_blank"
                        class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-white hover:border-primary hover:text-primary hover:shadow-gold-glow transition-all duration-300"><i
                            class="fab fa-whatsapp text-lg"></i></a>
                    <a href="#" target="_blank"
                        class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-white hover:border-primary hover:text-primary hover:shadow-gold-glow transition-all duration-300"><i
                            class="fab fa-instagram text-lg"></i></a>
                    <a href="#" target="_blank"
                        class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-white hover:border-primary hover:text-primary hover:shadow-gold-glow transition-all duration-300"><i
                            class="fab fa-tiktok text-lg"></i></a>
                    <a href="#" target="_blank"
                        class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-white hover:border-primary hover:text-primary hover:shadow-gold-glow transition-all duration-300"><i
                            class="fab fa-youtube text-lg"></i></a>
                </div>
            </div>
            <div>
                <h4 class="font-sans text-xs text-primary uppercase tracking-[0.2em] mb-6">Alamat</h4>
                <p class="font-sans text-on-surface-variant text-sm font-light leading-relaxed mb-4">
                    <?= str_replace(', ', '<br>', $cafeAddress) ?>
                </p>
            </div>
            <div>
                <h4 class="font-sans text-xs text-primary uppercase tracking-[0.2em] mb-6">Hubungi Kami</h4>
                <a href="tel:<?= SanitizeHelper::html(str_replace([' ', '-'], '', $cafePhone)) ?>"
                    class="font-display text-xl text-white block mb-6 hover:text-primary transition-colors"><?= $cafePhone ?></a>

                <h4 class="font-sans text-xs text-primary uppercase tracking-[0.2em] mb-4 mt-8">Jam Buka</h4>
                <ul class="font-sans text-on-surface-variant text-sm font-light space-y-2">
                    <li class="flex justify-between"><span>Senin - Kamis</span><span class="text-white">08:00 -
                            22:00</span></li>
                    <li class="flex justify-between"><span>Jumat - Minggu</span><span class="text-white">08:00 -
                            23:30</span></li>
                </ul>
            </div>
        </div>
        <div
            class="mt-20 pt-8 border-t border-white/5 px-8 md:px-12 max-w-[1600px] mx-auto flex flex-col md:flex-row justify-between items-center text-xs font-sans text-on-surface-variant uppercase tracking-widest reveal-up reveal-delay-1">
            <p>&copy; <?= date('Y') ?> <?= strtoupper($appName) ?>. ALL RIGHTS RESERVED.</p>
            <div class="flex gap-6 mt-4 md:mt-0">
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
            </div>
        </div>
    </footer>

    <!-- GSAP & Lenis Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://unpkg.com/lenis@1.1.9/dist/lenis.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // 1. Cinematic Preloader Animation
            const counter = document.getElementById('loader-counter');
            let progress = { val: 0 };

            gsap.to(progress, {
                val: 100,
                duration: 2.5, // 2.5s cinematic loading time
                ease: "power2.inOut",
                onUpdate: () => {
                    counter.innerHTML = Math.round(progress.val);
                    document.getElementById('loader-bar').style.width = progress.val + '%';
                },
                onStart: () => {
                    gsap.to('#loader-text', { y: 0, opacity: 1, duration: 1, ease: "expo.out" });
                },
                onComplete: () => {
                    const tl = gsap.timeline();
                    // Slide the preloader up smoothly
                    tl.to("#preloader", { yPercent: -100, duration: 1.2, ease: "expo.inOut" })
                        .set("#preloader", { display: "none" });

                    // Reveal the Hero section elements dynamically after load
                    setTimeout(() => {
                        const visibleElements = document.querySelectorAll('.reveal-up, .reveal-scale');
                        visibleElements.forEach(el => {
                            const rect = el.getBoundingClientRect();
                            if (rect.top < window.innerHeight) {
                                el.classList.add('active');
                            }
                        });
                    }, 400); // Slight delay after preloader lifts
                }
            });

            // 2. Lenis Smooth Scroll Setup
            const lenis = new Lenis({
                duration: 1.5,
                easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
                direction: 'vertical',
                gestureDirection: 'vertical',
                smooth: true,
                smoothTouch: false,
                touchMultiplier: 2,
            });

            function raf(time) {
                lenis.raf(time);
                requestAnimationFrame(raf);
            }
            requestAnimationFrame(raf);

            // 3. GSAP ScrollTrigger Integration
            gsap.registerPlugin(ScrollTrigger);

            // Sync Lenis scroll with GSAP ScrollTrigger
            lenis.on('scroll', ScrollTrigger.update);
            gsap.ticker.add((time) => { lenis.raf(time * 1000); });
            gsap.ticker.lagSmoothing(0);

            // Trigger reveal animations reliably using ScrollTrigger instead of IntersectionObserver
            const revealElements = document.querySelectorAll('.reveal-up, .reveal-scale');
            revealElements.forEach(el => {
                ScrollTrigger.create({
                    trigger: el,
                    start: "top 95%", // Trigger when the top of the element hits 95% of the viewport height
                    onEnter: () => el.classList.add('active'),
                    once: true // Only animate once
                });
            });

            // 4. Parallax Image Effects
            // Add a subtle parallax effect to any background image on the page
            gsap.utils.toArray('.animate-pulse-slow, .glass-panel img').forEach(img => {
                gsap.to(img, {
                    yPercent: 15,
                    ease: "none",
                    scrollTrigger: {
                        trigger: img.parentElement,
                        start: "top bottom",
                        end: "bottom top",
                        scrub: true
                    }
                });
            });

            // 5. Navbar Scroll Effect
            const navbar = document.getElementById('main-nav');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.style.background = 'rgba(10, 10, 10, 0.85)';
                    navbar.style.backdropFilter = 'blur(16px)';
                } else {
                    navbar.style.background = 'rgba(20, 20, 20, 0.4)';
                    navbar.style.backdropFilter = 'blur(16px)';
                }
            });

            // 6. Mobile Menu Toggle
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const mobileIcon = document.getElementById('mobile-menu-icon');
            const mobileOverlay = document.getElementById('mobile-menu-overlay');
            const mobileLinks = document.querySelectorAll('.mobile-nav-link');
            let isMobileMenuOpen = false;

            if (mobileBtn && mobileOverlay && mobileIcon) {
                const toggleMenu = () => {
                    isMobileMenuOpen = !isMobileMenuOpen;
                    if (isMobileMenuOpen) {
                        mobileOverlay.classList.remove('translate-x-full');
                        mobileIcon.textContent = 'close';
                        mobileIcon.style.transform = 'rotate(90deg)';
                        lenis.stop(); // Prevent scrolling when menu is open
                    } else {
                        mobileOverlay.classList.add('translate-x-full');
                        mobileIcon.textContent = 'menu';
                        mobileIcon.style.transform = 'rotate(0deg)';
                        lenis.start();
                    }
                };

                mobileBtn.addEventListener('click', toggleMenu);

                // Close menu when clicking a link
                mobileLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (isMobileMenuOpen) toggleMenu();
                    });
                });
            }
        });
    </script>
</body>

</html>