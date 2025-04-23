// public/js/public.js

document.addEventListener('DOMContentLoaded', function() {
    const statusContainer = document.getElementById('order-status-container'); // Container utama
    const orderStatusTextElement = document.getElementById('order-status-text'); // Teks status
    const orderProgressBar = document.getElementById('order-progress-bar'); // Progress bar (opsional)
    const orderId = statusContainer?.dataset.orderId;
    const urlRoot = window.APP_URLROOT || ''; // Ambil URL Root

    if (!statusContainer || !orderStatusTextElement || !orderId) {
        // console.error('Elemen penting untuk polling status tidak ditemukan.');
        return; // Berhenti jika elemen tidak ada
    }

    let currentReportedStatus = statusContainer.dataset.initialStatus?.toLowerCase() || '';
    const finalStatuses = ['served', 'paid', 'cancelled'];
    let pollingInterval = null; // Deklarasi interval di scope yg lebih luas
    let consecutiveErrors = 0; // Hitung error berturut-turut
    const maxErrors = 5; // Maksimum error sebelum berhenti polling
    let pollDelay = 7000; // Delay awal (7 detik)

    // --- Fungsi Helper ---
    const formatOrderStatus = (status) => {
        const statusMap = {
            'pending': 'Menunggu Konfirmasi',
            'received': 'Pesanan Diterima Dapur',
            'preparing': 'Sedang Disiapkan',
            'ready': 'Siap Diantar/Diambil',
            'served': 'Telah Disajikan',
            'paid': 'Telah Dibayar',
            'cancelled': 'Dibatalkan'
        };
        return statusMap[status] || status.charAt(0).toUpperCase() + status.slice(1);
    };

    const updateProgressBar = (status) => {
        if (!orderProgressBar) return;
        const statusSteps = ['pending', 'received', 'preparing', 'ready', 'served'];
        let progress = 0;
        const stepIndex = statusSteps.indexOf(status);

        if (status === 'cancelled' || status === 'paid') {
             progress = 100;
        } else if (stepIndex !== -1) {
             // Beri progress berdasarkan langkah (misal: 0, 25, 50, 75, 100)
             progress = (stepIndex / (statusSteps.length - 1)) * 100;
        }
        orderProgressBar.style.width = `${progress}%`;
        // Update warna progress bar (contoh)
        if (status === 'cancelled') {
            orderProgressBar.className = 'h-full bg-red-500 rounded transition-all duration-500 ease-in-out';
        } else if (status === 'paid' || status === 'served') {
            orderProgressBar.className = 'h-full bg-green-500 rounded transition-all duration-500 ease-in-out';
        } else {
             orderProgressBar.className = 'h-full bg-blue-500 rounded transition-all duration-500 ease-in-out';
        }
    };

    const displayPaymentInfo = () => {
        let paymentContainer = document.getElementById('payment-info-container');
        if (paymentContainer && !paymentContainer.querySelector('#payment-message')) {
            paymentContainer.innerHTML = `
                <div id="payment-message" class="mt-4 p-4 bg-gradient-to-r from-blue-100 to-cyan-100 border border-blue-300 text-blue-800 rounded text-center shadow">
                    <strong class="block text-lg">Pesanan Anda sudah siap!</strong>
                    <p class="mt-1">Silakan lakukan pembayaran di kasir.</p>
                    <p class="text-sm">Sebutkan Nomor Meja atau Kode Pesanan Anda.</p>
                </div>
            `;
            paymentContainer.classList.remove('hidden'); // Tampilkan jika tadinya disembunyikan
        }
    };

    const stopPolling = (reason) => {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null; // Tandai sudah berhenti
            console.log(`Polling dihentikan. Alasan: ${reason}`);
        }
    };

    // --- Fungsi Polling Utama ---
    const checkOrderStatus = async () => {
        const endpoint = `${urlRoot}/order/checkstatus/${orderId}`;
        console.log(`Polling: ${endpoint}`);

        try {
            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: AbortSignal.timeout(5000) // Batasi waktu fetch 5 detik (opsional)
            });

            if (!response.ok) {
                console.error('Polling Error HTTP:', response.status, response.statusText);
                consecutiveErrors++;
                if (consecutiveErrors >= maxErrors) {
                    stopPolling(`Terlalu banyak error (${consecutiveErrors})`);
                    orderStatusTextElement.textContent = 'Gagal Memuat Status';
                    orderStatusTextElement.classList.add('text-red-500');
                }
                // Terapkan exponential backoff (tambah delay jika error)
                // pollDelay = Math.min(pollDelay * 2, 60000); // Max 1 menit
                // console.log(`Mencoba lagi dalam ${pollDelay / 1000} detik...`);
                // stopPolling('Error, akan set interval baru'); // Hentikan interval lama
                // setTimeout(startPolling, pollDelay); // Mulai lagi dengan delay baru
                return;
            }

            // Reset error count jika berhasil
            consecutiveErrors = 0;
            // pollDelay = 7000; // Reset delay ke default

            const data = await response.json();

            if (data && data.status) {
                const newStatus = data.status.toLowerCase();

                if (newStatus !== currentReportedStatus) {
                    console.log(`Status berubah: ${currentReportedStatus} -> ${newStatus}`);
                    currentReportedStatus = newStatus; // Update status terakhir
                    orderStatusTextElement.textContent = formatOrderStatus(newStatus);
                    updateProgressBar(newStatus); // Update progress bar

                    // Tambahkan efek visual saat update (contoh: flash)
                    statusContainer.classList.add('flash-update');
                    setTimeout(() => statusContainer.classList.remove('flash-update'), 1000);

                    if (newStatus === 'served' || newStatus === 'ready') {
                        displayPaymentInfo();
                    }
                } else {
                     console.log(`Status tetap: ${currentReportedStatus}`);
                }

                // Cek status final
                if (finalStatuses.includes(newStatus)) {
                    stopPolling(`Status final tercapai (${newStatus})`);
                    if (newStatus === 'cancelled') {
                         updateProgressBar(newStatus); // Pastikan progress bar merah
                         orderStatusTextElement.classList.remove('text-blue-600');
                         orderStatusTextElement.classList.add('text-red-600');
                    } else if (newStatus === 'paid') {
                         updateProgressBar(newStatus);
                         orderStatusTextElement.classList.remove('text-blue-600');
                         orderStatusTextElement.classList.add('text-green-600');
                         // Bisa tambahkan pesan "Terima kasih"
                    }
                }

            } else {
                 console.warn('Data status tidak valid diterima:', data);
                 consecutiveErrors++; // Anggap sebagai error jika data aneh
            }

        } catch (error) {
            console.error('Error saat proses polling:', error.name === 'TimeoutError' ? 'Request Timeout' : error);
            consecutiveErrors++;
             if (error.name === 'AbortError') { // Khusus jika pakai AbortSignal.timeout
                console.log("Fetch request timed out.");
            }
            if (consecutiveErrors >= maxErrors) {
                stopPolling(`Terlalu banyak error (${consecutiveErrors})`);
                 orderStatusTextElement.textContent = 'Koneksi Bermasalah';
                 orderStatusTextElement.classList.add('text-orange-500');
            }
            // Implementasi backoff seperti di atas jika diinginkan
        }
    };

     // --- Mulai Polling ---
    const startPolling = () => {
        if (pollingInterval) { // Pastikan hanya ada satu interval aktif
             console.warn("Polling sudah berjalan.");
             return;
        }
        if (finalStatuses.includes(currentReportedStatus)) {
            console.log('Polling tidak dimulai, status awal sudah final.');
            updateProgressBar(currentReportedStatus); // Update progress bar sesuai status awal
            if (currentReportedStatus === 'served' || currentReportedStatus === 'ready') displayPaymentInfo();
            return; // Jangan mulai jika sudah selesai
        }

        console.log(`Memulai polling setiap ${pollDelay / 1000} detik untuk Order ID: ${orderId}`);
        pollingInterval = setInterval(checkOrderStatus, pollDelay);
        checkOrderStatus(); // Panggil sekali di awal
    };

    // --- Inisialisasi ---
    updateProgressBar(currentReportedStatus); // Set progress bar awal
    startPolling(); // Mulai proses polling

});

// Jangan lupa tambahkan CSS untuk class 'flash-update' jika Anda mau
// Contoh:
// .flash-update {
//   animation: flash 1s ease-out;
// }
// @keyframes flash {
//   0%, 100% { background-color: transparent; }
//   50% { background-color: #a7f3d0; } /* Warna flash hijau muda */
// }