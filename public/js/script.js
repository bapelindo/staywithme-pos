// File: public/js/script.js (Dengan perubahan interval tagline)

document.addEventListener('DOMContentLoaded', () => {
    // === TAMBAHKAN LOGIKA LIGHTBOX GALERI ===
    const galleryItems = document.querySelectorAll('#image-gallery .gallery-item');
    const lightboxOverlay = document.getElementById('lightbox-overlay');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxCloseBtn = document.getElementById('lightbox-close');

    if (galleryItems.length > 0 && lightboxOverlay && lightboxImage && lightboxCloseBtn) {
        galleryItems.forEach(item => {
            item.addEventListener('click', () => {
                const imageUrl = item.dataset.src; // Ambil URL gambar dari data-src
                if (imageUrl) {
                    openLightbox(imageUrl);
                }
            });
        });

        lightboxCloseBtn.addEventListener('click', closeLightbox);

        // Tutup lightbox jika klik di area overlay (background)
        lightboxOverlay.addEventListener('click', (event) => {
            if (event.target === lightboxOverlay) { // Pastikan klik pada overlay, bukan gambar
                closeLightbox();
            }
        });

        // Tutup lightbox dengan tombol Escape
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !lightboxOverlay.classList.contains('hidden')) {
                closeLightbox();
            }
        });

    } else {
        console.warn('Elemen galeri atau lightbox tidak ditemukan.');
    }

    function openLightbox(imageUrl) {
        if (!lightboxOverlay || !lightboxImage) return;
        lightboxImage.setAttribute('src', imageUrl);
        lightboxOverlay.classList.remove('hidden');
        // Trigger reflow untuk memastikan transisi opacity berjalan
        void lightboxOverlay.offsetWidth;
        lightboxOverlay.classList.remove('opacity-0', 'pointer-events-none');
        lightboxOverlay.classList.add('opacity-100');
        lightboxOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden'; // Cegah scroll body di belakang lightbox
    }

    function closeLightbox() {
        if (!lightboxOverlay) return;
        lightboxOverlay.classList.remove('opacity-100');
        lightboxOverlay.classList.add('opacity-0', 'pointer-events-none');
        lightboxOverlay.setAttribute('aria-hidden', 'true');
        // Tambahkan class hidden lagi setelah transisi selesai
        setTimeout(() => {
            lightboxOverlay.classList.add('hidden');
            lightboxImage.setAttribute('src', '#'); // Kosongkan src untuk cegah load aneh
        }, 300); // Sesuaikan dengan durasi transisi (duration-300)
        document.body.style.overflow = ''; // Kembalikan scroll body
    }
    // === AKHIR LOGIKA LIGHTBOX GALERI ===

    // --- Preloader Logic ---
    // ... (kode preloader tetap sama) ...
    const preloader = document.getElementById('preloader');
    if (preloader) {
        window.addEventListener('load', () => {
            preloader.classList.add('loaded');
        });
        setTimeout(() => {
            if (preloader) preloader.classList.add('loaded');
        }, 3000);
    }

    // --- Initialize AOS (Dengan Delay) ---
    // ... (kode AOS init tetap sama) ...
    try {
        if (typeof AOS !== 'undefined') {
            setTimeout(() => {
                console.log('Initializing AOS...');
                AOS.init({ duration: 800, easing: 'ease-out-cubic', once: true, offset: 50 });
            }, 100);
        } else { throw new Error('AOS library not loaded.'); }
    } catch(e) { console.warn("AOS init failed:", e.message); }


    // --- Toggle Menu Mobile ---
    // ... (kode toggle menu tetap sama) ...
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileIcon = mobileMenuButton?.querySelector('i');
    if (mobileMenuButton && mobileMenu && mobileIcon) {
        mobileMenuButton.addEventListener('click', () => {
            const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            mobileMenuButton.setAttribute('aria-expanded', String(!isExpanded));
            mobileMenu.classList.toggle('hidden');
            mobileMenu.classList.toggle('scale-y-0'); mobileMenu.classList.toggle('scale-y-100');
            if (!isExpanded) { mobileIcon.classList.replace('fa-bars', 'fa-times'); }
            else { mobileIcon.classList.replace('fa-times', 'fa-bars'); }
        });
        mobileMenu.querySelectorAll('a').forEach(link => {
             link.addEventListener('click', (e) => {
                 if (link.getAttribute('href')?.startsWith('#')) {
                     mobileMenuButton.click();
                 }
             });
         });
    }

    // --- Sticky Header ---
    // ... (kode sticky header tetap sama) ...
    const header = document.getElementById('main-header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('header-scrolled', window.pageYOffset > 50);
        }, { passive: true });
    }

    // --- Variabel Global untuk Swiper & Slides ---
    // ... (kode variabel swiper tetap sama) ...
    let fullMenuSlider = null;
    let allMenuSlides = [];

    // --- Inisialisasi Swiper Sliders (Dengan Delay) ---
    // ... (kode inisialisasi swiper tetap sama, termasuk setTimeout) ...
    setTimeout(() => {
        console.log('Initializing Swiper sliders...');
        try {
            // ... (kode new Swiper untuk featured, full, testimonial) ...
             if (typeof Swiper === 'undefined') throw new Error('Swiper library not loaded.');

             const featuredMenuSliderEl = document.querySelector('.featured-menu-slider');
             if (featuredMenuSliderEl) {
                 const featuredSlides = featuredMenuSliderEl.querySelectorAll('.swiper-slide').length;
                 if (featuredSlides > 0) {
                     const featuredMenuSlider = new Swiper('.featured-menu-slider', {
                        loop: false, slidesPerView: 1.2, spaceBetween: 15, grabCursor: true,
                        navigation: { nextEl: '.swiper-button-next-top', prevEl: '.swiper-button-prev-top' },
                        breakpoints: { 640: { slidesPerView: 2, spaceBetween: 15 }, 768: { slidesPerView: 3, spaceBetween: 20 }, 1024: { slidesPerView: 4, spaceBetween: 20 }, 1280: { slidesPerView: 5, spaceBetween: 25 } }
                     });
                 }
             }
             const fullMenuSliderEl = document.querySelector('.full-menu-slider');
             if (fullMenuSliderEl) {
                  const initialFullMenuSlides = fullMenuSliderEl.querySelectorAll('.swiper-slide');
                  if (initialFullMenuSlides.length > 0) {
                     allMenuSlides = Array.from(initialFullMenuSlides);
                     fullMenuSlider = new Swiper('.full-menu-slider', {
                        loop: false, slidesPerView: 2, spaceBetween: 15, grabCursor: true,
                        navigation: { nextEl: '.swiper-button-next-full', prevEl: '.swiper-button-prev-full' },
                        breakpoints: { 640: { slidesPerView: 2, spaceBetween: 15 }, 768: { slidesPerView: 3, spaceBetween: 20 }, 1024: { slidesPerView: 4, spaceBetween: 20 }, 1280: { slidesPerView: 5, spaceBetween: 25 } }
                     });
                  }
              }
             const testimonialSliderEl = document.querySelector('.testimonial-slider-v2');
             if (testimonialSliderEl) {
                  const testimonialSlidesNodes = testimonialSliderEl.querySelectorAll('.swiper-slide');
                  const testimonialSlidesCount = testimonialSlidesNodes.length;
                  if (testimonialSlidesCount > 0) {
                      const testimonialSlider = new Swiper('.testimonial-slider-v2', {
                          effect: 'coverflow', grabCursor: true, centeredSlides: true, slidesPerView: 'auto', loop: true,
                          coverflowEffect: { rotate: 40, stretch: 0, depth: 100, modifier: 1, slideShadows: false, },
                          spaceBetween: 15, autoplay: { delay: 3000, disableOnInteraction: false, },
                          pagination: { el: '.swiper-pagination', clickable: true, },
                      });
                  }
             }

        } catch(e) { console.warn("Swiper init failed:", e.message); }

        // --- Logika Filter untuk Carousel Menu Lengkap (Setelah Swiper Init) ---
        // ... (kode filter menu tetap sama) ...
        const filterButtons = document.querySelectorAll('#menu-filter-buttons .menu-filter-btn');
        if (filterButtons.length > 0 && fullMenuSlider && allMenuSlides.length > 0) {
            filterButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const filterValue = button.dataset.filter;
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    const filteredSlides = allMenuSlides.filter(slideNode => {
                         const category = slideNode.dataset?.category;
                        if (!category && filterValue !== 'all') return false;
                        return filterValue === 'all' || category === filterValue;
                    });
                    fullMenuSlider.removeAllSlides();
                    if (filteredSlides.length > 0) { fullMenuSlider.appendSlide(filteredSlides); }
                    else { const wrapper = fullMenuSlider.wrapperEl; if(wrapper) wrapper.innerHTML = '<div class="swiper-slide text-center text-text-dark-secondary p-8">Tidak ada menu dalam kategori ini.</div>'; }
                    fullMenuSlider.update();
                    fullMenuSlider.slideTo(0, 0);
                });
            });
        }

    }, 150);


    // --- Logika Quick View Modal Menu ---
    // ... (kode quick view modal tetap sama) ...
     const quickViewModal = document.getElementById('quick-view-modal');
     const closeModalBtn = document.getElementById('close-modal-btn');
     const modalContent = document.getElementById('modal-content');
     const quickViewButtons = document.querySelectorAll('.quick-view-btn');
     if (quickViewModal && closeModalBtn && modalContent) {
         closeModalBtn.addEventListener('click', closeModal);
         quickViewModal.addEventListener('click', (e) => { if (e.target === quickViewModal) closeModal(); });
         // Event listener untuk tombol quick view (jika ada)
         // ... (kode event listener quick view) ...
     }


    // --- Dynamic Tagline (Interval Diubah) ---
    const dynamicTagline = document.getElementById('dynamic-tagline');
    if (dynamicTagline) {
        const taglines = [
            "Sejuta Cerita.", "Sumber Energi.", "Momen Terbaik.",
            "Rasa Istimewa.", "Kopi Pilihan.",
            "Untuk Kreatif.", "Hangatnya Pagi.", "Indahnya Senja.",
            "Ruang Kerjamu.", "Waktu Santai.", "Selera Nikmat.", "Semangat Baru.",
            "Kreasi Unik.", "Percikan Ide.", "Obrolan Seru."];
        let currentTaglineIndex = 0;
        dynamicTagline.style.transition = 'opacity 0.4s ease-in-out'; // Percepat sedikit transisi fade

        setInterval(() => {
            dynamicTagline.style.opacity = '0';
            setTimeout(() => {
                currentTaglineIndex = (currentTaglineIndex + 1) % taglines.length;
                dynamicTagline.textContent = taglines[currentTaglineIndex];
                dynamicTagline.style.opacity = '1';
            }, 400); // Sesuaikan delay fade out (sedikit < setengah interval)
        // === PERUBAHAN INTERVAL DI SINI ===
        }, 1200); // Ganti setiap 1.2 detik (1200ms) -> 400ms fade out, ~400ms ganti teks, 400ms fade in
        // Anda bisa coba 1000ms (1 detik), tapi mungkin terasa terlalu cepat/terpatah
        // =================================
    }

}); // Akhir DOMContentLoaded

// Fungsi Modal (Tetap Sama)
// ... (kode fungsi openModal dan closeModal) ...
function openModal(contentHtml) {
    const qvm = document.getElementById('quick-view-modal');
    const mc = document.getElementById('modal-content');
    if (!qvm || !mc) return;
    mc.innerHTML = contentHtml;
     if (!mc.querySelector('#close-modal-btn-inner')) {
         const closeBtn = document.createElement('button');
         closeBtn.id = 'close-modal-btn-inner';
         closeBtn.className = 'absolute top-4 right-4 text-text-dark-secondary hover:text-white text-2xl transition-colors z-10';
         closeBtn.innerHTML = '<i class="fas fa-times"></i>';
         closeBtn.onclick = closeModal;
         mc.prepend(closeBtn);
     }
    qvm.classList.remove('opacity-0', 'pointer-events-none');
    qvm.classList.add('opacity-100');
    mc.classList.remove('scale-95'); mc.classList.add('scale-100');
    document.body.style.overflow = 'hidden';
}
function closeModal() {
    const qvm = document.getElementById('quick-view-modal');
    const mc = document.getElementById('modal-content');
    if (!qvm || !mc) return;
    mc.classList.remove('scale-100'); mc.classList.add('scale-95');
    qvm.classList.remove('opacity-100'); qvm.classList.add('opacity-0', 'pointer-events-none');
    document.body.style.overflow = '';
    setTimeout(() => { if(mc) mc.innerHTML = `<div class="p-8 text-center"><i class="fas fa-spinner fa-spin mr-2 text-accent-primary"></i> Memuat detail...</div>`; }, 300);
}