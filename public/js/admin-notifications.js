// File: public/assets/js/admin-notifications.js

document.addEventListener('DOMContentLoaded', () => {
    const newOrderBadge = document.getElementById('new-order-count-badge'); // Elemen badge di header/sidebar
    // const newOrderToastContainer = document.getElementById('new-order-toast'); // Optional: container toast

    if (!newOrderBadge) {
        // console.warn('Elemen badge notifikasi pesanan baru tidak ditemukan.');
        // Tidak perlu polling jika tidak ada tempat menampilkannya
        // return;
    }

    const POLLING_RATE_NOTIF = 20000; // Check setiap 20 detik
    let lastSeenId = 0; // ID order terakhir yang sudah dinotifikasi
    let notificationAudio;

    // Ambil lastSeenId awal jika disimpan (misal dari data attribute badge)
     if(newOrderBadge && newOrderBadge.dataset.lastSeenId) {
         lastSeenId = parseInt(newOrderBadge.dataset.lastSeenId) || 0;
     }

    async function fetchNewOrders() {
        const baseUrl = window.APP_BASE_URL || '';
        try {
            const response = await fetch(`${baseUrl}/admin/orders/new?lastSeenId=${lastSeenId}`, {
                 method: 'GET',
                 headers: { 'Accept': 'application/json' }
            });

             if (!response.ok) {
                 console.error('Gagal fetch notifikasi, status:', response.status);
                 return;
             }

             const result = await response.json();

             if (result.success && Array.isArray(result.new_orders) && result.new_orders.length > 0) {
                 const newOrders = result.new_orders;
                 const count = newOrders.length;
                 console.log(`Ditemukan ${count} pesanan baru.`);

                 // Update badge count
                 if (newOrderBadge) {
                     let currentCount = parseInt(newOrderBadge.textContent) || 0;
                     // Hanya tambahkan count baru jika tidak sedang menampilkan '9+'
                     if (!newOrderBadge.textContent.includes('+')) {
                         currentCount += count;
                     }
                     newOrderBadge.textContent = currentCount > 9 ? '9+' : currentCount.toString();
                     newOrderBadge.classList.remove('hidden'); // Tampilkan badge jika tadinya hidden
                      // Animasikan badge (contoh: pulse)
                      newOrderBadge.classList.add('animate-pulse');
                      setTimeout(() => newOrderBadge.classList.remove('animate-pulse'), 2000);
                 }

                 // Tampilkan toast (implementasi toast sederhana)
                 newOrders.forEach((order, index) => {
                     // Beri jeda antar toast jika banyak
                     setTimeout(() => showToast(`Pesanan Baru #${order.order_number.replace('STW-','')} dari Meja ${order.table_number}`), index * 500);
                 });

                 // Mainkan suara
                 playNotificationSound();

                 // Update lastSeenId dengan ID tertinggi dari hasil fetch
                 lastSeenId = Math.max(...newOrders.map(o => o.id), lastSeenId);
                  // Simpan lastSeenId ke data attribute jika perlu (misal: untuk load berikutnya)
                  if(newOrderBadge) newOrderBadge.dataset.lastSeenId = lastSeenId;

             } else if (!result.success) {
                 console.error('API notifikasi mengembalikan error:', result.message);
             }
             // Jika tidak ada order baru, tidak lakukan apa-apa

        } catch (error) {
            console.error('Error saat polling notifikasi:', error);
        }
    }

    // --- Fungsi Toast Sederhana ---
    function showToast(message) {
         // Implementasi toast sederhana: buat div, tampilkan, lalu hilangkan
         const toast = document.createElement('div');
         toast.className = 'fixed top-5 right-5 bg-indigo-600 text-white text-sm py-2 px-4 rounded-lg shadow-lg z-50 transition-all duration-300 ease-in-out transform translate-x-full opacity-0';
         toast.textContent = message;
         document.body.appendChild(toast);

         // Animasikan masuk
         setTimeout(() => {
             toast.classList.remove('translate-x-full', 'opacity-0');
             toast.classList.add('translate-x-0', 'opacity-100');
         }, 100);

         // Hilangkan setelah beberapa detik
         setTimeout(() => {
             toast.classList.add('translate-x-full', 'opacity-0');
             setTimeout(() => {
                 toast.remove();
             }, 300); // Tunggu transisi selesai sebelum remove
         }, 4000); // Tampilkan selama 4 detik
     }

    // --- Fungsi Suara Notifikasi ---
     function playNotificationSound() {
        try {
             if (!notificationAudio) {
                  // Ganti dengan path file audio Anda
                  notificationAudio = new Audio('/assets/audio/admin_notification.mp3');
             }
             if (notificationAudio.paused) {
                  notificationAudio.play().catch(e => console.warn("Gagal memainkan suara notifikasi:", e));
             }
         } catch (e) {
             console.warn("Tidak dapat memainkan suara notifikasi:", e);
         }
     }

    // --- Inisialisasi ---
     // Hanya mulai polling jika elemen badge ditemukan (opsional)
     if (newOrderBadge) {
        console.log('Memulai polling notifikasi pesanan baru...');
        // Panggil sekali saat load untuk cek awal? Bisa jadi tidak perlu jika lastSeenId sudah benar.
        // fetchNewOrders();
        setInterval(fetchNewOrders, POLLING_RATE_NOTIF);
     }

});