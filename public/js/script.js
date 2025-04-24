// File: public/js/script.js (Dengan Fix Tombol Nav & Efek Coverflow Testimoni)

document.addEventListener('DOMContentLoaded', () => {
    // --- Preloader Logic ---
    const preloader = document.getElementById('preloader');
    if (preloader) {
        window.addEventListener('load', () => {
            preloader.classList.add('loaded');
        });
        // Backup: hide preloader after timeout if load event fails
        setTimeout(() => {
            if (preloader) preloader.classList.add('loaded');
        }, 3000);
    }

    // --- Initialize AOS ---
    try {
        if (typeof AOS !== 'undefined') {
            AOS.init({ duration: 800, easing: 'ease-out-cubic', once: true, offset: 50 });
        } else { throw new Error('AOS library not loaded.'); }
    } catch(e) { /* Handle error silently or log minimal info */ }

    // --- Toggle Menu Mobile ---
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
        // Tutup menu saat link di klik (terutama untuk link internal #)
        mobileMenu.querySelectorAll('a').forEach(link => {
             link.addEventListener('click', (e) => {
                 if (link.getAttribute('href')?.startsWith('#')) {
                     mobileMenuButton.click(); // Tutup menu
                 }
             });
         });
    }

    // --- Sticky Header ---
    const header = document.getElementById('main-header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('header-scrolled', window.pageYOffset > 50);
        }, { passive: true });
    }

    // --- Variabel Global untuk Swiper & Slides ---
    let fullMenuSlider = null;
    let allMenuSlides = [];

    // --- Inisialisasi Swiper Sliders ---
     try {
         if (typeof Swiper === 'undefined') throw new Error('Swiper library not loaded.');

         // --- Slider Top 5 Item Terpopuler ---
         const featuredMenuSliderEl = document.querySelector('.featured-menu-slider');
         if (featuredMenuSliderEl) {
             const featuredSlides = featuredMenuSliderEl.querySelectorAll('.swiper-slide').length;
             if (featuredSlides > 0) {
                 const featuredMenuSlider = new Swiper('.featured-menu-slider', {
                     loop: false,
                     slidesPerView: 1.2,
                     spaceBetween: 15,
                     grabCursor: true,
                     // === PERBAIKAN TOMBOL NAVIGASI ===
                     navigation: {
                         nextEl: '.swiper-button-next-top', // Selector tombol Next
                         prevEl: '.swiper-button-prev-top'  // Selector tombol Prev
                     },
                     // === AKHIR PERBAIKAN ===
                     breakpoints: {
                         640: { slidesPerView: 2, spaceBetween: 15 },
                         768: { slidesPerView: 3, spaceBetween: 20 },
                         1024: { slidesPerView: 4, spaceBetween: 20 },
                         1280: { slidesPerView: 5, spaceBetween: 25 }
                     }
                 });
             }
         }

         // --- Slider Menu Lengkap ---
         const fullMenuSliderEl = document.querySelector('.full-menu-slider');
         if (fullMenuSliderEl) {
              const initialFullMenuSlides = fullMenuSliderEl.querySelectorAll('.swiper-slide');
              if (initialFullMenuSlides.length > 0) {
                 allMenuSlides = Array.from(initialFullMenuSlides);
                 fullMenuSlider = new Swiper('.full-menu-slider', {
                     loop: false,
                     slidesPerView: 2, // <-- Tinjau ini untuk mobile terkecil
                     spaceBetween: 15,
                     grabCursor: true,
                     navigation: { nextEl: '.swiper-button-next-full', prevEl: '.swiper-button-prev-full' },
                     breakpoints: {
                         640: { slidesPerView: 2, spaceBetween: 15 },
                         768: { slidesPerView: 3, spaceBetween: 20 },
                         1024: { slidesPerView: 4, spaceBetween: 20 },
                         1280: { slidesPerView: 5, spaceBetween: 25 }
                     }
                 });
              }
          }

         // --- Slider Testimonial ---
         const testimonialSliderEl = document.querySelector('.testimonial-slider-v2');
         if (testimonialSliderEl) {
              const testimonialSlidesNodes = testimonialSliderEl.querySelectorAll('.swiper-slide');
              const testimonialSlidesCount = testimonialSlidesNodes.length;
              if (testimonialSlidesCount > 0) {
                  const testimonialSlider = new Swiper('.testimonial-slider-v2', {
                      // === KONFIGURASI EFEK COVERFLOW ===
                      effect: 'coverflow',    // Aktifkan efek coverflow
                      grabCursor: true,
                      centeredSlides: true,    // Slide aktif selalu di tengah
                      slidesPerView: 'auto',  // Biarkan Swiper mengatur atau coba 1.5 / 2
                      loop: true,            
                      coverflowEffect: {
                          rotate: 40,        // Rotasi slide samping
                          stretch: 0,        // Jarak antar slide
                          depth: 100,       // Efek kedalaman (3D)
                          modifier: 1,       // Pengali efek
                          slideShadows: true, // Tampilkan bayangan di slide samping
                      },
                      // === AKHIR KONFIGURASI COVERFLOW ===

                      spaceBetween: 20, // Sesuaikan jarak jika perlu untuk coverflow
                      autoplay: {
                          delay: 6000,
                          disableOnInteraction: false,
                      },
                      pagination: {
                          el: '.swiper-pagination',
                          clickable: true,
                      },
                      // Breakpoints mungkin bisa disederhanakan karena slidesPerView: 'auto'
                      // atau disesuaikan untuk parameter coverflowEffect di ukuran berbeda
                      // breakpoints: {
                      //     768: { spaceBetween: 40 },
                      //     1024: { spaceBetween: 50 }
                      // }
                  });
              }
         }

     } catch(e) { console.warn("Swiper init failed:", e.message); }

    // --- Logika Filter untuk Carousel Menu Lengkap ---
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
                if (filteredSlides.length > 0) {
                    fullMenuSlider.appendSlide(filteredSlides);
                } else {
                    // Opsional: Tampilkan pesan jika tidak ada hasil
                    const wrapper = fullMenuSlider.wrapperEl;
                    if(wrapper) wrapper.innerHTML = '<div class="swiper-slide text-center text-text-dark-secondary p-8">Tidak ada menu dalam kategori ini.</div>';
                }
                fullMenuSlider.update();
                fullMenuSlider.slideTo(0, 0); // Kembali ke slide pertama
            });
        });
    }

    // --- Logika Quick View Modal Menu (Contoh Dasar) ---
    const quickViewModal = document.getElementById('quick-view-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const modalContent = document.getElementById('modal-content');
    const quickViewButtons = document.querySelectorAll('.quick-view-btn'); // Jika Anda punya tombol ini

    const openModal = (contentHtml) => {
        if (!quickViewModal || !modalContent) return;
        modalContent.innerHTML = contentHtml;
        // Tambahkan tombol close di modal jika belum ada
        if (!modalContent.querySelector('#close-modal-btn-inner')) {
            const closeBtn = document.createElement('button');
            closeBtn.id = 'close-modal-btn-inner';
            closeBtn.className = 'absolute top-4 right-4 text-text-dark-secondary hover:text-white text-2xl transition-colors z-10';
            closeBtn.innerHTML = '<i class="fas fa-times"></i>';
            closeBtn.onclick = closeModal;
            modalContent.prepend(closeBtn);
        }
        quickViewModal.classList.remove('opacity-0', 'pointer-events-none');
        quickViewModal.classList.add('opacity-100');
        modalContent.classList.remove('scale-95'); modalContent.classList.add('scale-100');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        if (!quickViewModal || !modalContent) return;
        modalContent.classList.remove('scale-100'); modalContent.classList.add('scale-95');
        quickViewModal.classList.remove('opacity-100'); quickViewModal.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
        setTimeout(() => { if(modalContent) modalContent.innerHTML = `<div class="p-8 text-center"><i class="fas fa-spinner fa-spin mr-2 text-accent-primary"></i> Memuat detail...</div>`; }, 300);
    };

    if (quickViewModal && closeModalBtn && modalContent) {
        closeModalBtn.addEventListener('click', closeModal);
        quickViewModal.addEventListener('click', (e) => { if (e.target === quickViewModal) closeModal(); });

        // Event listener untuk tombol quick view (jika ada)
        if (quickViewButtons.length > 0) {
            quickViewButtons.forEach(button => {
                button.addEventListener('click', async () => {
                    const itemId = button.dataset.itemId; // Ambil ID item dari tombol
                    if (!itemId) return;
                    try {
                        // Ganti URL ini dengan endpoint API Anda untuk mendapatkan detail item
                        const response = await fetch(`/api/menu/${itemId}`);
                        if (!response.ok) throw new Error('Gagal mengambil detail item');
                        const itemData = await response.json();

                        // Format HTML untuk ditampilkan di modal
                        const modalHtml = `
                            <button id="close-modal-btn-inner" class="absolute top-4 right-4 text-text-dark-secondary hover:text-white text-2xl transition-colors z-10"> <i class="fas fa-times"></i> </button>
                            <div class="grid md:grid-cols-2 gap-6 p-6 md:p-8">
                                <div>
                                    <img src="${itemData.image_path || '/images/menu-placeholder.jpg'}" alt="${itemData.name}" class="rounded-lg shadow-md w-full h-auto object-cover mb-4">
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold mb-2 text-white">${itemData.name}</h3>
                                    <p class="text-sm text-text-dark-secondary mb-4">${itemData.category_name || ''}</p>
                                    <p class="text-lg text-accent-primary font-semibold mb-4">Rp ${parseInt(itemData.price).toLocaleString('id-ID')}</p>
                                    <p class="text-text-dark-secondary mb-6">${itemData.description || 'Tidak ada deskripsi.'}</p>
                                    <button class="btn btn-accent w-full py-2">Tambah ke Keranjang (Contoh)</button>
                                </div>
                            </div>`;
                        openModal(modalHtml);
                    } catch (error) {
                        console.error('Error fetching item details:', error);
                        openModal('<div class="p-8 text-center text-red-400">Gagal memuat detail item.</div>');
                    }
                });
            });
        }
    }



}); // Akhir DOMContentLoaded

// --- Fungsi Modal (di luar DOMContentLoaded jika dipanggil dari tempat lain) ---
// Fungsi openModal dan closeModal tetap sama seperti sebelumnya
function openModal(contentHtml) {
    const qvm = document.getElementById('quick-view-modal');
    const mc = document.getElementById('modal-content');
    if (!qvm || !mc) return;
    mc.innerHTML = contentHtml; // Isi konten modal
     // Tambahkan tombol close di sini jika belum ada di contentHtml
     if (!mc.querySelector('#close-modal-btn-inner')) {
         const closeBtn = document.createElement('button');
         closeBtn.id = 'close-modal-btn-inner';
         closeBtn.className = 'absolute top-4 right-4 text-text-dark-secondary hover:text-white text-2xl transition-colors z-10';
         closeBtn.innerHTML = '<i class="fas fa-times"></i>';
         closeBtn.onclick = closeModal; // Panggil fungsi closeModal saat diklik
         mc.prepend(closeBtn); // Tambahkan di awal konten modal
     }
    qvm.classList.remove('opacity-0', 'pointer-events-none');
    qvm.classList.add('opacity-100');
    mc.classList.remove('scale-95'); mc.classList.add('scale-100');
    document.body.style.overflow = 'hidden'; // Cegah scroll body
}

function closeModal() {
    const qvm = document.getElementById('quick-view-modal');
    const mc = document.getElementById('modal-content');
    if (!qvm || !mc) return;
    mc.classList.remove('scale-100'); mc.classList.add('scale-95');
    qvm.classList.remove('opacity-100'); qvm.classList.add('opacity-0', 'pointer-events-none');
    document.body.style.overflow = ''; // Kembalikan scroll body
    // Reset konten modal setelah animasi selesai
    setTimeout(() => { if(mc) mc.innerHTML = `<div class="p-8 text-center"><i class="fas fa-spinner fa-spin mr-2 text-accent-primary"></i> Memuat detail...</div>`; }, 300);
}