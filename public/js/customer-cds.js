// File: public/assets/js/customer-cds.js

document.addEventListener('DOMContentLoaded', () => {
    const preparingListElement = document.getElementById('preparing-list');
    const readyListElement = document.getElementById('ready-list');
    const preparingEmptyMessage = preparingListElement?.nextElementSibling; // Asumsi elemen <p> setelah list
    const readyEmptyMessage = readyListElement?.nextElementSibling;

    if (!preparingListElement || !readyListElement) {
        console.error('Elemen daftar CDS (preparing/ready) tidak ditemukan. Polling tidak aktif.');
        return;
    }

    let currentPreparing = [];
    let currentReady = [];
    const POLLING_RATE_CDS = 10000; // Check setiap 10 detik

    function updateList(listElement, emptyMessageElement, newOrderNumbers) {
        const currentOrderElements = listElement.querySelectorAll('.cds-list-item');
        const currentNumbers = Array.from(currentOrderElements).map(el => el.dataset.orderNumber);

        // 1. Hapus nomor yang sudah tidak ada di daftar baru
        currentNumbers.forEach(num => {
            if (!newOrderNumbers.includes(num)) {
                const elementToRemove = listElement.querySelector(`.cds-list-item[data-order-number="${num}"]`);
                if (elementToRemove) {
                    elementToRemove.remove();
                }
            }
        });

        // 2. Tambah nomor baru yang belum ada di daftar
        newOrderNumbers.forEach(num => {
            if (!currentNumbers.includes(num)) {
                const newItem = document.createElement('div');
                newItem.className = 'cds-list-item';
                newItem.dataset.orderNumber = num;
                newItem.textContent = num;
                listElement.appendChild(newItem);

                 // Efek highlight singkat untuk item baru
                 newItem.classList.add('opacity-0', 'scale-90');
                 setTimeout(() => {
                     newItem.classList.remove('opacity-0', 'scale-90');
                     newItem.classList.add('transition-all', 'duration-300', 'ease-out');
                 }, 50); // Small delay to trigger transition

                 // Suara notifikasi (opsional)
                 playNotificationSound();
            }
        });

        // 3. Tampilkan/sembunyikan pesan kosong
        const finalItemCount = listElement.querySelectorAll('.cds-list-item').length;
        if (emptyMessageElement) {
             emptyMessageElement.style.display = (finalItemCount === 0) ? 'block' : 'none';
             // Jika pakai grid, pastikan pesan kosong mengisi kolom
             if (finalItemCount === 0) emptyMessageElement.classList.add('col-span-full');
             else emptyMessageElement.classList.remove('col-span-full');
        }
    }

    // Fungsi sederhana untuk memainkan suara (perlu file audio)
    let notificationAudio;
    function playNotificationSound() {
        // Inisialisasi AudioContext setelah interaksi user pertama (best practice)
        // atau coba mainkan langsung (mungkin diblok browser)
        try {
            if (!notificationAudio) {
                // Ganti dengan path file audio Anda
                 notificationAudio = new Audio('/assets/audio/cds_notification.mp3');
            }
            // Hanya mainkan jika belum ada yg sedang dimainkan (cegah tumpang tindih)
            if (notificationAudio.paused) {
                notificationAudio.play().catch(e => console.warn("Gagal memainkan suara notifikasi:", e));
            }
        } catch (e) {
            console.warn("Tidak dapat memainkan suara notifikasi:", e);
        }
    }

    async function fetchCdsOrders() {
        const baseUrl = window.APP_BASE_URL || '';
        try {
            const response = await fetch(`${baseUrl}/cds/get_orders`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                console.error('Gagal mengambil data CDS, status code:', response.status);
                // Mungkin tampilkan indikator error di UI CDS?
                return;
            }

            const result = await response.json();

            if (result.success) {
                const newPreparingNumbers = result.preparing.map(order => order.number);
                const newReadyNumbers = result.ready.map(order => order.number);

                // Update list hanya jika ada perubahan untuk mengurangi DOM manipulation
                if (JSON.stringify(newPreparingNumbers) !== JSON.stringify(currentPreparing)) {
                    updateList(preparingListElement, preparingEmptyMessage, newPreparingNumbers);
                    currentPreparing = newPreparingNumbers;
                }
                if (JSON.stringify(newReadyNumbers) !== JSON.stringify(currentReady)) {
                     updateList(readyListElement, readyEmptyMessage, newReadyNumbers);
                    currentReady = newReadyNumbers;
                }

            } else {
                console.error('API CDS mengembalikan error:', result.message);
            }

        } catch (error) {
            console.error('Error saat polling data CDS:', error);
        }
    }

    // --- Inisialisasi ---
    console.log('Memulai polling CDS...');
    fetchCdsOrders(); // Panggil sekali di awal
    setInterval(fetchCdsOrders, POLLING_RATE_CDS);

});