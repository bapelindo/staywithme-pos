// File: public/assets/js/customer-status.js

document.addEventListener('DOMContentLoaded', () => {
    const orderIdInput = document.getElementById('order-id');
    const statusElement = document.getElementById('order-status');
    const statusContainer = document.getElementById('order-status-container'); // Optional: container untuk styling
    const lastUpdatedElement = document.getElementById('status-last-updated'); // Optional

    if (!orderIdInput || !statusElement) {
        console.warn('Elemen status pesanan atau ID tidak ditemukan. Polling tidak aktif.');
        return;
    }

    const orderId = orderIdInput.value;
    let currentStatus = statusElement.textContent.trim().toLowerCase().replace(/ /g, '_'); // Ambil status awal dari HTML
    let pollingInterval;
    const POLLING_RATE = 8000; // Check setiap 8 detik

    const statusStyles = { // Mapping status ke kelas Tailwind (sesuaikan dengan View)
        'pending': 'bg-blue-100 text-blue-800',
        'received': 'bg-blue-100 text-blue-800',
        'preparing': 'bg-yellow-100 text-yellow-800',
        'ready': 'bg-teal-100 text-teal-800',
        'served': 'bg-green-100 text-green-800',
        'paid': 'bg-indigo-100 text-indigo-800',
        'cancelled': 'bg-red-100 text-red-800',
        'default': 'bg-gray-100 text-gray-800'
    };

    function formatStatusText(status) {
         if (!status) return 'Tidak Diketahui';
         return status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ');
    }

    async function fetchStatus() {
        const baseUrl = window.APP_BASE_URL || '';
        try {
            const response = await fetch(`${baseUrl}/order/get_status/${orderId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                // Jika order tidak ditemukan (404) atau error server, hentikan polling?
                if (response.status === 404) {
                     console.error('Pesanan tidak ditemukan. Menghentikan polling.');
                     stopPolling();
                } else {
                    console.error('Gagal mengambil status, status code:', response.status);
                    // Mungkin tidak perlu stop polling jika hanya error sementara
                }
                return; // Keluar jika error
            }

            const result = await response.json();

            if (result.success && result.status) {
                const newStatus = result.status;
                if (newStatus !== currentStatus) {
                    console.log(`Status berubah: ${currentStatus} -> ${newStatus}`);
                    currentStatus = newStatus;
                    statusElement.textContent = formatStatusText(newStatus);

                    // Update kelas badge styling
                    const badgeClass = statusStyles[newStatus] || statusStyles['default'];
                    statusElement.className = `text-xl font-bold px-4 py-1.5 rounded-full ${badgeClass}`;

                    // Tampilkan notifikasi visual atau suara jika perlu
                    flashStatusUpdate();

                     // Tampilkan waktu update terakhir (optional)
                    if(lastUpdatedElement) {
                        lastUpdatedElement.textContent = `Diperbarui: ${new Date().toLocaleTimeString('id-ID')}`;
                    }
                }

                // Hentikan polling jika status final
                if (['served', 'paid', 'cancelled'].includes(newStatus)) {
                    console.log('Status final tercapai. Menghentikan polling.');
                    stopPolling();
                }
            } else if (!result.success) {
                 console.error('API mengembalikan error:', result.message);
                  if (response.status === 404) stopPolling(); // Stop jika 404 dari API
            }

        } catch (error) {
            console.error('Error saat polling status:', error);
            // Mungkin perlu logic retry atau hentikan polling jika error berulang
        }
    }

    function flashStatusUpdate() {
        if (statusContainer) {
            statusContainer.classList.add('opacity-50', 'transition-opacity', 'duration-500');
            setTimeout(() => {
                statusContainer.classList.remove('opacity-50');
            }, 500);
        }
    }

    function startPolling() {
        console.log(`Memulai polling status untuk order ID: ${orderId}`);
        // Panggil sekali di awal
        fetchStatus();
        // Lalu set interval
        pollingInterval = setInterval(fetchStatus, POLLING_RATE);
    }

    function stopPolling() {
        console.log(`Menghentikan polling status untuk order ID: ${orderId}`);
        clearInterval(pollingInterval);
    }

    // --- Inisialisasi ---
    // Hanya mulai polling jika status awal belum final
    if (!['served', 'paid', 'cancelled'].includes(currentStatus)) {
        startPolling();
    } else {
        console.log('Status awal sudah final. Polling tidak dimulai.');
         if(lastUpdatedElement) { // Tampilkan waktu load halaman jika sudah final
            lastUpdatedElement.textContent = `Status Final`;
        }
    }

});