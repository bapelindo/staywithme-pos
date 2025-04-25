// File: public/js/customer-status.js (Revisi Final untuk Pesan Status)

document.addEventListener('DOMContentLoaded', () => {
    // --- Get DOM Elements ---
    const orderIdInput = document.getElementById('order-id');
    const initialStatusKeyInput = document.getElementById('current-status-key');
    const stepperContainer = document.getElementById('status-stepper-container');
    const stepItems = stepperContainer ? stepperContainer.querySelectorAll('.step-item') : [];
    const lastUpdatedElement = document.getElementById('status-last-updated');
    const refreshButton = document.getElementById('refresh-status-btn');
    const pollingIndicator = document.getElementById('polling-indicator');
    // === TAMBAHKAN: Ambil elemen pesan utama ===
    const orderStatusTextElement = document.getElementById('order-status-text');
    // ==========================================

    // --- Check if essential elements exist ---
    if (!orderIdInput || !initialStatusKeyInput || !stepperContainer || stepItems.length === 0 || !orderStatusTextElement) { // Tambahkan cek orderStatusTextElement
        console.warn('Elemen penting (status, ID, stepper, atau teks status) tidak ditemukan. Polling tidak aktif.');
        return;
    }

    // --- Config & State ---
    const orderId = orderIdInput.value;
    let currentStatusKey = initialStatusKeyInput.value;
    let pollingInterval;
    const POLLING_RATE = 8000;
    const FINAL_STATUSES = ['served', 'paid', 'cancelled'];

    // --- Mappings & Styles (Tetap sama) ---
    const statusMap = { /* ... mapping status ... */
        'pending': { index: 0, text: 'Menunggu', icon: 'fas fa-hourglass-half' },
        'received': { index: 1, text: 'Diterima', icon: 'fas fa-receipt' },
        'preparing': { index: 2, text: 'Disiapkan', icon: 'fas fa-utensils' },
        'ready': { index: 3, text: 'Siap', icon: 'fas fa-bell' },
        'served': { index: 4, text: 'Disajikan', icon: 'fas fa-check-circle' },
        'paid': { index: 4, text: 'Lunas', icon: 'fas fa-check-double' },
        'cancelled': { index: -1, text: 'Dibatalkan', icon: 'fas fa-times-circle' }
    };
    const stepClasses = { /* ... definisi class ... */
        icon: { active: 'text-accent-primary animate-pulse', completed: 'text-green-400', upcoming: 'text-gray-500' },
        text: { active: 'text-accent-primary', completed: 'text-green-400', upcoming: 'text-gray-500' },
        bg: { active: 'bg-accent-primary/10', completed: 'bg-green-800/30', upcoming: 'bg-gray-800/30' },
        line: { completed: 'bg-green-500', pending: 'bg-gray-700' }
    };

    // --- UI Update Function (Modifikasi) ---
    function updateStepperUI(newStatusKey) {
        if (!statusMap[newStatusKey]) {
            console.warn(`Status tidak dikenal diterima: ${newStatusKey}`);
            return;
        }

        const newStatusInfo = statusMap[newStatusKey];
        const newStatusIndex = newStatusInfo.index;
        const finalStepIndex = stepItems.length - 1;

        // === Update Pesan Status Utama ===
        if (orderStatusTextElement) {
            if (newStatusKey === 'paid') {
                orderStatusTextElement.textContent = 'Pesanan ini sudah lunas. Terima kasih telah berkunjung!';
            } else if (newStatusKey === 'served') {
                orderStatusTextElement.textContent = 'Pesanan Anda telah disajikan. Harap lakukan pembayaran di kasir.';
             } else if (newStatusKey === 'ready') {
                 orderStatusTextElement.textContent = 'Pesanan Anda sudah siap. Harap lakukan pembayaran di kasir.';
            } else if (newStatusKey === 'cancelled') {
                orderStatusTextElement.textContent = 'Jika ada pertanyaan mengenai pembatalan, silakan hubungi staf kami.';
            } else {
                // Jika belum final, tampilkan pesan default
                orderStatusTextElement.textContent = 'Status akan diperbarui secara otomatis. Anda juga bisa menyegarkan secara manual.';
            }
        }
        // ================================

        if (newStatusKey === 'cancelled') {
            if (stepperContainer) {
                stepperContainer.innerHTML = `
                    <div class="text-center p-4 bg-red-900/30 border border-red-700 rounded-lg">
                         <i class="${newStatusInfo.icon} text-red-400 text-4xl mb-3"></i>
                         <p class="text-xl font-semibold text-red-300">${newStatusInfo.text}</p>
                         <p class="text-sm text-red-400 mt-1">Pesanan ini telah dibatalkan.</p>
                    </div>`;
            }
             // stopPolling(); // Akan dipanggil oleh fetchStatus
        } else {
            // Update langkah terakhir jika status final berubah (Served -> Paid)
             if (newStatusIndex === finalStepIndex) {
                // ... (logika update ikon/teks langkah terakhir tetap sama) ...
                  const lastStepItem = stepItems[finalStepIndex];
                  const lastStepIcon = lastStepItem.querySelector('.step-icon i');
                  const lastStepText = lastStepItem.querySelector('.step-text');
                  if (lastStepIcon) lastStepIcon.className = `${newStatusInfo.icon} text-lg sm:text-xl ${stepClasses.icon.completed}`;
                  if (lastStepText) lastStepText.textContent = newStatusInfo.text;
             }

            // Update class untuk setiap step (Logika ini tetap sama)
            stepItems.forEach((step, index) => {
                // ... (logika update class icon, text, bg, line tetap sama) ...
                 const iconElement = step.querySelector('.step-icon i');
                 const textElement = step.querySelector('.step-text');
                 const iconBgElement = step.querySelector('.step-icon');
                 const lineAfterElement = step.querySelector('.step-line-after div'); // Target div di dalam

                 let currentState = 'upcoming';
                 if (index === newStatusIndex && index < finalStepIndex) { // Aktif hanya jika BUKAN final
                     currentState = 'active';
                 } else if (index < newStatusIndex || (index <= newStatusIndex && newStatusIndex >= finalStepIndex)) { // Selesai jika di belakang atau sudah final
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

                 // Update garis konektor setelah step
                 if (lineAfterElement) {
                     lineAfterElement.classList.remove(stepClasses.line.completed, stepClasses.line.pending);
                     lineAfterElement.classList.add(currentState === 'completed' ? stepClasses.line.completed : stepClasses.line.pending);
                 }
            });

            // Flash effect (optional)
            flashStatusUpdate();
        }

        // Update teks waktu terakhir (hanya jika BUKAN cancelled)
        // Kita pindahkan ini ke stopPolling agar lebih konsisten
        /*
        if (lastUpdatedElement && newStatusKey !== 'cancelled') {
             lastUpdatedElement.textContent = `Diperbarui: ${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
        }
        */
    }

    // --- Fetch & Polling (Fungsi fetchStatus tetap sama) ---
    async function fetchStatus(manual = false) {
        // ... (kode fetchStatus lengkap seperti sebelumnya) ...
        if (pollingIndicator && manual) pollingIndicator.classList.remove('hidden');
        const baseUrl = window.APP_BASE_URL || '';
        try {
            const response = await fetch(`${baseUrl}/order/get_status/${orderId}`, { method: 'GET', headers: { 'Accept': 'application/json' }});
            if (!response.ok) { if (response.status === 404) { stopPolling(); } throw new Error(`HTTP error! status: ${response.status}`); }
            const result = await response.json();
            if (result.success && result.status) {
                const newStatusKey = result.status;
                if (newStatusKey !== currentStatusKey) {
                    console.log(`Status berubah: ${currentStatusKey} -> ${newStatusKey}`);
                    updateStepperUI(newStatusKey);
                    currentStatusKey = newStatusKey;
                } else if (manual) {
                    if (lastUpdatedElement) lastUpdatedElement.textContent = `Status tidak berubah (${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })})`;
                }
                if (FINAL_STATUSES.includes(newStatusKey)) { stopPolling(newStatusKey); } // << Kirim status final ke stopPolling
            } else if (!result.success) { console.error('API error:', result.message); if (response.status === 404) stopPolling(); }
        } catch (error) { console.error('Error saat polling status:', error);
        } finally { if (pollingIndicator && manual) { setTimeout(() => pollingIndicator.classList.add('hidden'), 500); } }
    }

    // --- Fungsi Flash Status (Tetap sama) ---
    function flashStatusUpdate() { /* ... kode flash ... */
         if (stepperContainer) {
             stepperContainer.classList.remove('opacity-100');
             stepperContainer.classList.add('opacity-60', 'transition-opacity', 'duration-300');
            setTimeout(() => {
                stepperContainer.classList.remove('opacity-60');
                stepperContainer.classList.add('opacity-100');
            }, 300);
        }
    }

    // --- Fungsi Start Polling (Modifikasi sedikit) ---
    function startPolling() {
        if (FINAL_STATUSES.includes(currentStatusKey)) {
            console.log('Status awal sudah final. Polling tidak dimulai.');
            // Panggil stopPolling untuk set teks awal yg benar
            stopPolling(currentStatusKey); // << Kirim status awal
            return;
        }
        console.log(`Memulai polling status untuk order ID: ${orderId}`);
        fetchStatus();
        pollingInterval = setInterval(fetchStatus, POLLING_RATE);
         if (lastUpdatedElement) lastUpdatedElement.textContent = `Memeriksa status...`;
    }

    // --- Fungsi Stop Polling (Modifikasi) ---
    function stopPolling(finalStatusKey = null) { // << Terima status final
        if (pollingInterval) {
            console.log(`Menghentikan polling status untuk order ID: ${orderId}`);
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
        // Update teks terakhir berdasarkan status saat berhenti
        if (lastUpdatedElement) {
             const statusInfo = finalStatusKey ? statusMap[finalStatusKey] : null;
             const statusText = statusInfo ? (statusInfo.text || finalStatusKey) : currentStatusKey; // Fallback ke current key
             lastUpdatedElement.textContent = `Status Final: ${statusText} (${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })})`;
        }
         if (pollingIndicator) pollingIndicator.classList.add('hidden');
    }

    // --- Event Listener (Tetap sama) ---
    if (refreshButton) {
        refreshButton.addEventListener('click', () => fetchStatus(true));
    }

    // --- Initialization ---
    startPolling();

}); // Akhir DOMContentLoaded