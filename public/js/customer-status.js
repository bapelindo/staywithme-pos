// File: public/js/customer-status.js (Lengkap & Final - Tanpa Console Log)

document.addEventListener('DOMContentLoaded', () => {
    // --- Get DOM Elements ---
    const orderIdInput = document.getElementById('order-id');
    const initialStatusKeyInput = document.getElementById('current-status-key');
    const stepperContainer = document.getElementById('status-stepper-container');
    const stepperOlElement = stepperContainer?.querySelector('ol.stepper-list');
    const cancelledMessageElement = stepperContainer?.querySelector('.cancelled-message');
    const stepItems = stepperOlElement ? stepperOlElement.querySelectorAll('.step-item') : [];
    const lastUpdatedElement = document.getElementById('status-last-updated');
    const refreshButton = document.getElementById('refresh-status-btn');
    const pollingIndicator = document.getElementById('polling-indicator');
    const orderStatusTextElement = document.getElementById('order-status-text');
    const orderNumberFullInput = document.getElementById('order-number-full');

    // --- Check if essential elements exist ---
    if (!orderIdInput || !initialStatusKeyInput || !stepperContainer || !orderStatusTextElement || !orderNumberFullInput) {
        console.warn('Elemen penting (status, ID, nomor order, stepper container, atau teks status utama) tidak ditemukan. Polling tidak aktif.');
        return;
    }
    if (!stepperOlElement && !cancelledMessageElement) {
         console.warn('Elemen stepper (<ol class="stepper-list">) atau pesan batal (.cancelled-message) tidak ditemukan di dalam #status-stepper-container. UI mungkin tidak update dengan benar.');
    }

    // --- Config & State ---
    const orderId = orderIdInput.value;
    let currentStatusKey = initialStatusKeyInput.value;
    if (currentStatusKey === 'paid') currentStatusKey = 'served'; // Anggap paid = served
    let pollingInterval = null;
    const POLLING_RATE = 8000;
    const FINAL_STATUSES = ['served', 'cancelled'];
    const orderNumberDisplay = orderNumberFullInput ? `#${orderNumberFullInput.value}` : `#${orderId}`;

    // --- Mappings & Styles ---
    const statusMap = {
        'pending_payment': { index: 0, text: 'Pembayaran', icon: 'fas fa-cash-register' },
        'received': { index: 1, text: 'Diterima', icon: 'fas fa-receipt' },
        'preparing': { index: 2, text: 'Disiapkan', icon: 'fas fa-utensils' },
        'ready': { index: 3, text: 'Siap', icon: 'fas fa-bell' },
        'served': { index: 4, text: 'Disajikan', icon: 'fas fa-check-circle' },
        'cancelled': { index: -1, text: 'Dibatalkan', icon: 'fas fa-times-circle' }
    };
    const stepClasses = {
        icon: { active: 'text-accent-primary animate-pulse', completed: 'text-green-400', upcoming: 'text-gray-500' },
        text: { active: 'text-accent-primary', completed: 'text-green-400', upcoming: 'text-gray-500' },
        bg: { active: 'bg-accent-primary/10', completed: 'bg-green-800/30', upcoming: 'bg-gray-800/30' },
        line: { completed: 'bg-green-500', pending: 'bg-gray-700' }
    };

    // --- UI Update Function ---
    function updateStepperUI(newStatusKey) {
        if (newStatusKey === 'paid') newStatusKey = 'served';
        if (!statusMap[newStatusKey]) { return; } // Abaikan status tidak dikenal

        const newStatusInfo = statusMap[newStatusKey];
        const newStatusIndex = newStatusInfo.index;
        const finalStepIndex = stepItems.length > 0 ? stepItems.length - 1 : 4;

        // 1. Update Teks Status Utama (#order-status-text)
        if (orderStatusTextElement) {
             if (newStatusKey === 'pending_payment') { orderStatusTextElement.textContent = `Silakan lakukan pembayaran tunai di kasir dengan menunjukkan nomor pesanan ${orderNumberDisplay}.`; }
             else if (newStatusKey === 'served') { orderStatusTextElement.textContent = 'Pesanan Anda telah disajikan. Terima kasih!'; }
             else if (newStatusKey === 'ready') { orderStatusTextElement.textContent = 'Pesanan Anda sudah siap. Silakan konfirmasi ke kasir.'; }
             else if (newStatusKey === 'cancelled') { orderStatusTextElement.textContent = 'Pesanan ini telah dibatalkan.'; }
             else { orderStatusTextElement.textContent = 'Status akan diperbarui secara otomatis.'; }
        }

        // 2. Update Tampilan Stepper (<ol>) atau Pesan Batal (.cancelled-message)
        if (newStatusKey === 'cancelled') {
             if (stepperOlElement) stepperOlElement.style.display = 'none';
             if (cancelledMessageElement) cancelledMessageElement.style.display = 'block';
        } else {
             if (stepperOlElement) stepperOlElement.style.display = 'flex';
             if (cancelledMessageElement) cancelledMessageElement.style.display = 'none';

             if (stepItems.length > 0) {
                 const isFinalReached = newStatusIndex >= finalStepIndex;

                 // Update styling langkah terakhir jika final
                 if (isFinalReached) {
                     const lastStepItem = stepItems[finalStepIndex];
                     const lastStepIcon = lastStepItem.querySelector('.step-icon i');
                     const lastStepText = lastStepItem.querySelector('.step-text');
                     const finalInfo = statusMap['served'];
                     if (lastStepIcon && finalInfo.icon) lastStepIcon.className = `${finalInfo.icon} text-lg sm:text-xl ${stepClasses.icon.completed}`;
                     if (lastStepText && finalInfo.text) lastStepText.textContent = finalInfo.text;
                 }

                 // Update class styling untuk setiap langkah stepper
                 stepItems.forEach((step, index) => {
                     const iconElement = step.querySelector('.step-icon i');
                     const textElement = step.querySelector('.step-text');
                     const iconBgElement = step.querySelector('.step-icon');
                     const lineAfterElement = step.querySelector('.step-line-after div');

                     let currentState = 'upcoming';
                     if (index < newStatusIndex || (index <= newStatusIndex && isFinalReached)) { currentState = 'completed'; }
                     else if (index === newStatusIndex && !isFinalReached) { currentState = 'active'; }
                     if (newStatusKey === 'pending_payment' && index === 0) { currentState = 'active'; }

                     // Terapkan class styling
                     if(iconElement) {
                         Object.values(stepClasses.icon).forEach(cls => iconElement.classList.remove(...cls.split(' ')));
                         iconElement.classList.add(...stepClasses.icon[currentState].split(' '));
                     }
                     if(textElement) {
                         Object.values(stepClasses.text).forEach(cls => textElement.classList.remove(...cls.split(' ')));
                         textElement.classList.add(...stepClasses.text[currentState].split(' '));
                         // Update teks sesuai map
                         const stepKey = Object.keys(statusMap).find(key => statusMap[key].index === index);
                         if (stepKey && statusMap[stepKey]) {
                            textElement.textContent = statusMap[stepKey].text;
                            if (index === finalStepIndex && isFinalReached) { textElement.textContent = statusMap['served'].text; }
                         }
                     }
                     if(iconBgElement) {
                          Object.values(stepClasses.bg).forEach(cls => iconBgElement.classList.remove(...cls.split(' ')));
                          iconBgElement.classList.add(...stepClasses.bg[currentState].split(' '));
                     }
                     if (lineAfterElement) {
                         lineAfterElement.classList.remove(stepClasses.line.completed, stepClasses.line.pending);
                         lineAfterElement.classList.add(currentState === 'completed' ? stepClasses.line.completed : stepClasses.line.pending);
                     }
                 });

                 // Hanya flash jika status benar-benar berubah
                 if (currentStatusKey !== newStatusKey) { flashStatusUpdate(); }
             }
        }

        // Update teks waktu terakhir diperbarui
        if (lastUpdatedElement) {
            lastUpdatedElement.textContent = `Diperbarui: ${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
        }
    }

    // --- Fetch & Polling ---
    async function fetchStatus(manual = false) {
        if (pollingIndicator && manual) pollingIndicator.classList.remove('hidden');
        const baseUrl = typeof APP_BASE_URL !== 'undefined' ? APP_BASE_URL : '';

        try {
            const response = await fetch(`${baseUrl}/order/get_status/${orderId}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                if (response.status === 404) { stopPolling(); console.error('Order not found (404). Stopping polling.'); }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success && result.status) {
                let newStatusKey = result.status;
                if (newStatusKey === 'paid') newStatusKey = 'served'; // Treat paid as served

                if (newStatusKey !== currentStatusKey) {
                    // console.log(`Status berubah dari server: ${currentStatusKey} -> ${newStatusKey}`); // <-- Log di-nonaktifkan
                    updateStepperUI(newStatusKey);
                    currentStatusKey = newStatusKey;
                    if (initialStatusKeyInput) initialStatusKeyInput.value = newStatusKey;
                } else if (manual) {
                    if (lastUpdatedElement) lastUpdatedElement.textContent = `Status tidak berubah (${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })})`;
                    flashStatusUpdate(true); // Flash ringan untuk feedback refresh manual
                }

                if (FINAL_STATUSES.includes(newStatusKey)) {
                     stopPolling(newStatusKey);
                }
            } else if (!result.success) {
                console.error('API error:', result.message || 'Unknown API error');
                if (response.status === 404) stopPolling();
            }
        } catch (error) {
             console.error('Error saat polling status:', error);
        } finally {
            if (pollingIndicator && manual) {
                setTimeout(() => pollingIndicator.classList.add('hidden'), 500);
            }
        }
    }

    // --- Fungsi Flash Status ---
    function flashStatusUpdate(isMinor = false) {
         const elementToFlash = stepperContainer;
         if (elementToFlash) {
             elementToFlash.style.transition = 'opacity 0.3s ease-in-out';
             elementToFlash.style.opacity = isMinor ? '0.85' : '0.6';
            setTimeout(() => {
                elementToFlash.style.opacity = '1';
            }, isMinor ? 150 : 300);
        }
    }

    // --- Fungsi Start/Stop Polling ---
    function startPolling() {
        if (FINAL_STATUSES.includes(currentStatusKey)) {
            // console.log('Initial status is final. Polling not started.'); // <-- Log di-nonaktifkan
            stopPolling(currentStatusKey);
            return;
        }
        if (pollingInterval) clearInterval(pollingInterval);
        // console.log(`Starting status polling for order ID: ${orderId}`); // <-- Log di-nonaktifkan
        fetchStatus().then(() => {
             if (!FINAL_STATUSES.includes(currentStatusKey)) {
                pollingInterval = setInterval(fetchStatus, POLLING_RATE);
                if (lastUpdatedElement) lastUpdatedElement.textContent = `Memeriksa status...`;
             } else {
                 stopPolling(currentStatusKey);
             }
        }).catch(error => {
            console.error("Initial fetch failed:", error);
        });
    }

    function stopPolling(finalStatusKey = null) {
        const finalKeyToCheck = finalStatusKey || currentStatusKey;
        if (pollingInterval) {
            // console.log(`Stopping status polling for order ID: ${orderId}. Final status: ${finalKeyToCheck}`); // <-- Log di-nonaktifkan
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
        if (lastUpdatedElement && FINAL_STATUSES.includes(finalKeyToCheck)) {
             const statusInfo = statusMap[finalKeyToCheck];
             const statusText = statusInfo ? (statusInfo.text || finalKeyToCheck) : finalKeyToCheck;
             lastUpdatedElement.textContent = `Status Final: ${statusText} (${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })})`;
        }
         if (pollingIndicator) pollingIndicator.classList.add('hidden');
    }

    // --- Event Listener untuk Tombol Refresh ---
    if (refreshButton) {
        refreshButton.addEventListener('click', () => {
            // console.log('Refresh button clicked.'); // <-- Log di-nonaktifkan
            fetchStatus(true);
            if (!pollingInterval && !FINAL_STATUSES.includes(currentStatusKey)) {
                 // console.log('Polling was stopped but status is not final, restarting...'); // <-- Log di-nonaktifkan
                 startPolling();
            }
        });
    }

    // --- Initialization ---
    updateStepperUI(currentStatusKey);
    startPolling();

}); // Akhir DOMContentLoaded