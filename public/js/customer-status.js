// File: public/js/customer-status.js (Modifikasi untuk Stepper Visual)

document.addEventListener('DOMContentLoaded', () => {
    // --- Get DOM Elements ---
    const orderIdInput = document.getElementById('order-id');
    const initialStatusKeyInput = document.getElementById('current-status-key'); // Status awal dari PHP
    const stepperContainer = document.getElementById('status-stepper-container');
    const stepItems = stepperContainer ? stepperContainer.querySelectorAll('.step-item') : []; // Semua item langkah
    const lastUpdatedElement = document.getElementById('status-last-updated');
    const refreshButton = document.getElementById('refresh-status-btn');
    const pollingIndicator = document.getElementById('polling-indicator');

    // --- Check if essential elements exist ---
    if (!orderIdInput || !initialStatusKeyInput || !stepperContainer || stepItems.length === 0) {
        console.warn('Elemen status pesanan, ID, atau stepper tidak ditemukan. Polling tidak aktif.');
        return;
    }

    // --- Config & State ---
    const orderId = orderIdInput.value;
    let currentStatusKey = initialStatusKeyInput.value; // Status saat ini (e.g., 'preparing')
    let pollingInterval;
    const POLLING_RATE = 8000; // Check setiap 8 detik (sesuaikan jika perlu)
    const FINAL_STATUSES = ['served', 'paid', 'cancelled'];

    // --- Mappings & Styles (Harus sinkron dengan PHP/Tailwind) ---
    const statusMap = {
        'pending': { index: 0, text: 'Menunggu', icon: 'fas fa-hourglass-half' },
        'received': { index: 1, text: 'Diterima', icon: 'fas fa-receipt' },
        'preparing': { index: 2, text: 'Disiapkan', icon: 'fas fa-utensils' },
        'ready': { index: 3, text: 'Siap', icon: 'fas fa-bell' },
        'served': { index: 4, text: 'Disajikan', icon: 'fas fa-check-circle' },
        'paid': { index: 4, text: 'Lunas', icon: 'fas fa-check-double' }, // index sama dgn served
        'cancelled': { index: -1, text: 'Dibatalkan', icon: 'fas fa-times-circle' }
    };

    // Definisikan kelas CSS untuk setiap state agar mudah diubah
    const stepClasses = {
        icon: {
            active: 'text-accent-primary animate-pulse',
            completed: 'text-green-400',
            upcoming: 'text-gray-500'
        },
        text: {
            active: 'text-accent-primary',
            completed: 'text-green-400',
            upcoming: 'text-gray-500'
        },
        bg: { // Background ikon
            active: 'bg-accent-primary/10',
            completed: 'bg-green-800/30',
            upcoming: 'bg-gray-800/30'
        },
        line: { // Garis konektor *setelah* step selesai / *sebelum* step aktif
            completed: 'bg-green-500',
            pending: 'bg-gray-700'
        }
    };

// --- UI Update Function ---
function updateStepperUI(newStatusKey) {
    if (!statusMap[newStatusKey]) {
        console.warn(`Status tidak dikenal diterima: ${newStatusKey}`);
        return;
    }

    const newStatusInfo = statusMap[newStatusKey];
    const newStatusIndex = newStatusInfo.index; // Akan jadi -1 untuk cancelled
    const finalStepIndex = stepItems.length - 1;

    // === PERUBAHAN UNTUK STATUS CANCELLED ===
    if (newStatusKey === 'cancelled') {
        console.log("Order berubah menjadi dibatalkan.");
        // 1. Sembunyikan Stepper
        if (stepperContainer) {
            stepperContainer.innerHTML = `
                <div class="text-center p-4 bg-red-900/30 border border-red-700 rounded-lg">
                     <i class="${newStatusInfo.icon} text-red-400 text-4xl mb-3"></i>
                     <p class="text-xl font-semibold text-red-300">${newStatusInfo.text}</p>
                     <p class="text-sm text-red-400 mt-1">Pesanan ini telah dibatalkan.</p>
                </div>`;
        }
        // 2. Update teks waktu terakhir
        if (lastUpdatedElement) {
            lastUpdatedElement.textContent = `Status Final: Dibatalkan (${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })})`;
        }
        // 3. Pastikan polling berhenti (meskipun sudah dipanggil di fetch)
        stopPolling();
        // 4. Tidak perlu return lagi, biarkan fungsi selesai normal jika ada proses lain di bawah
        // return; // Hapus atau komentari baris return ini
    } else {
         // === LOGIKA UPDATE STEPPER UNTUK STATUS LAIN (Tetap sama) ===

         // Update teks & ikon langkah terakhir jika status final berubah (Served -> Paid)
         if (newStatusIndex === finalStepIndex) {
              const lastStepItem = stepItems[finalStepIndex];
              const lastStepIcon = lastStepItem.querySelector('.step-icon i');
              const lastStepText = lastStepItem.querySelector('.step-text');
              // Pastikan ikon dan teks sesuai dengan status final yang baru (Served/Paid)
              if (lastStepIcon) lastStepIcon.className = `${newStatusInfo.icon} text-lg sm:text-xl ${stepClasses.icon.completed}`;
              if (lastStepText) lastStepText.textContent = newStatusInfo.text;
         }

        stepItems.forEach((step, index) => {
            const iconElement = step.querySelector('.step-icon i');
            const textElement = step.querySelector('.step-text');
            const iconBgElement = step.querySelector('.step-icon');
            const prevLineElement = step.querySelector('.step-line:not(.ml-2)'); // Perbaiki selector jika perlu
            const nextLineElement = step.querySelector('.step-line.ml-2');

            let currentState = 'upcoming';
            if (index === newStatusIndex) {
                currentState = 'active';
            } else if (index < newStatusIndex) {
                currentState = 'completed';
            }

            // Hapus class lama
            Object.values(stepClasses.icon).forEach(cls => iconElement?.classList.remove(...cls.split(' ')));
            Object.values(stepClasses.text).forEach(cls => textElement?.classList.remove(...cls.split(' ')));
            Object.values(stepClasses.bg).forEach(cls => iconBgElement?.classList.remove(...cls.split(' ')));

            // Terapkan class baru
            iconElement?.classList.add(...stepClasses.icon[currentState].split(' '));
            textElement?.classList.add(...stepClasses.text[currentState].split(' '));
            iconBgElement?.classList.add(...stepClasses.bg[currentState].split(' '));

             // Update garis konektor
             if (nextLineElement) {
                 nextLineElement.classList.remove(stepClasses.line.completed, stepClasses.line.pending);
                 nextLineElement.classList.add(index < newStatusIndex ? stepClasses.line.completed : stepClasses.line.pending);
             }
             if (prevLineElement){
                  prevLineElement.classList.remove(stepClasses.line.completed, stepClasses.line.pending);
                  prevLineElement.classList.add(index <= newStatusIndex ? stepClasses.line.completed : stepClasses.line.pending);
             }
        });

         // Tampilkan waktu update terakhir (hanya jika bukan cancelled)
         if (lastUpdatedElement) {
             lastUpdatedElement.textContent = `Diperbarui: ${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
         }
         // Flash effect (optional)
         flashStatusUpdate();
    } // Akhir dari blok else (untuk status selain cancelled)
}

    // --- Fetch & Polling ---
    async function fetchStatus(manual = false) {
        if (pollingIndicator && manual) pollingIndicator.classList.remove('hidden'); // Tampilkan spinner saat manual refresh

        const baseUrl = window.APP_BASE_URL || '';
        try {
            const response = await fetch(`${baseUrl}/order/get_status/${orderId}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                if (response.status === 404) {
                    console.error('Pesanan tidak ditemukan saat polling. Menghentikan.');
                    stopPolling();
                } else {
                    console.error(`Gagal mengambil status: ${response.status}`);
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success && result.status) {
                const newStatusKey = result.status;

                if (newStatusKey !== currentStatusKey) {
                    console.log(`Status berubah: ${currentStatusKey} -> ${newStatusKey}`);
                    updateStepperUI(newStatusKey); // Panggil fungsi update UI
                    currentStatusKey = newStatusKey; // Update status lokal
                } else if (manual) {
                    // Jika manual refresh dan status sama, beri feedback singkat
                    if (lastUpdatedElement) lastUpdatedElement.textContent = `Status tidak berubah (${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })})`;
                }

                // Hentikan polling jika status final
                if (FINAL_STATUSES.includes(newStatusKey)) {
                    stopPolling();
                }
            } else if (!result.success) {
                console.error('API error:', result.message);
                 if (response.status === 404) stopPolling();
            }

        } catch (error) {
            console.error('Error saat polling status:', error);
            // Handle error UI, mungkin tampilkan pesan
        } finally {
             if (pollingIndicator && manual) {
                setTimeout(() => pollingIndicator.classList.add('hidden'), 500); // Sembunyikan spinner setelah delay
             }
        }
    }

    function flashStatusUpdate() {
        if (stepperContainer) {
             stepperContainer.classList.remove('opacity-100');
             stepperContainer.classList.add('opacity-60', 'transition-opacity', 'duration-300');
            setTimeout(() => {
                stepperContainer.classList.remove('opacity-60');
                stepperContainer.classList.add('opacity-100');
            }, 300);
        }
    }

    function startPolling() {
        // Jangan mulai polling jika status awal sudah final
        if (FINAL_STATUSES.includes(currentStatusKey)) {
            console.log('Status awal sudah final. Polling tidak dimulai.');
            if (lastUpdatedElement) lastUpdatedElement.textContent = `Status Final`;
            return;
        }
        console.log(`Memulai polling status untuk order ID: ${orderId}`);
        // Panggil sekali di awal untuk sinkronisasi cepat
        fetchStatus();
        // Lalu set interval
        pollingInterval = setInterval(fetchStatus, POLLING_RATE);
         if (lastUpdatedElement) lastUpdatedElement.textContent = `Memeriksa status...`;
    }

    function stopPolling() {
        if (pollingInterval) {
            console.log(`Menghentikan polling status untuk order ID: ${orderId}`);
            clearInterval(pollingInterval);
            pollingInterval = null; // Reset interval ID
            if (lastUpdatedElement) {
                // Beri teks final yang sesuai
                 lastUpdatedElement.textContent = FINAL_STATUSES.includes(currentStatusKey) ? `Status Final (${statusMap[currentStatusKey]?.text || currentStatusKey})` : `Polling dihentikan.`;
            }
             if (pollingIndicator) pollingIndicator.classList.add('hidden');
        }
    }

    // --- Event Listener ---
    if (refreshButton) {
        refreshButton.addEventListener('click', () => fetchStatus(true)); // Panggil fetchStatus dengan flag manual
    }

    // --- Initialization ---
    startPolling();

}); // Akhir DOMContentLoaded