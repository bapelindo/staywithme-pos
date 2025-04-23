// Lokasi File: public/js/kds_script.js

document.addEventListener('DOMContentLoaded', () => {
    const kdsGrid = document.getElementById('kds-grid');
    const noOrdersMessage = document.getElementById('no-orders-message');
    const refreshInterval = window.KDS_REFRESH_INTERVAL || 15000; // Interval dari PHP
    const fetchUrl = window.KDS_FETCH_URL;
    const itemUpdateBaseUrl = window.KDS_ITEM_UPDATE_URL;
    const orderReadyBaseUrl = window.KDS_ORDER_READY_URL;
    // const orderPreparingBaseUrl = window.KDS_ORDER_PREPARING_URL; // Jika ada

    let currentOrderIds = new Set(); // Set ID order yg sedang ditampilkan
    let isFetching = false; // Flag untuk mencegah fetch ganda
    let pollingTimer = null;

    // --- Fungsi Helper ---

    // Fungsi untuk memformat waktu (misal: 5 menit lalu)
    const formatTimeAgo = (timestamp) => {
        const now = Math.floor(Date.now() / 1000);
        const seconds = now - timestamp;
        if (seconds < 60) return 'Baru saja';
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return `${minutes} m lalu`;
        const hours = Math.floor(minutes / 60);
        return `${hours} j lalu`;
    };

    // Fungsi untuk memainkan suara notifikasi (perlu file audio)
    const playNotificationSound = () => {
        // const audio = new Audio('/path/to/notification.mp3'); // Ganti path
        // audio.play().catch(e => console.warn("Gagal memainkan suara:", e));
        console.log("PLING! Pesanan baru!"); // Fallback console log
    };

    // Fungsi membuat HTML kartu item
    const createItemCardHtml = (item) => {
        const itemStatus = item.item_status || 'pending';
        const itemTextClass = (itemStatus === 'ready') ? 'line-through text-gray-400' : ((itemStatus === 'preparing') ? 'text-yellow-300' : '');
        const notesHtml = item.notes ? `<p class="text-xs italic text-yellow-200 mt-0.5">Note: ${escapeHtml(item.notes)}</p>` : '';

        let actionButtonsHtml = '';
        if (itemStatus !== 'ready') {
             actionButtonsHtml += `<div class="flex-shrink-0 space-x-1">`;
             if (itemStatus === 'pending' || itemStatus === 'received') {
                 actionButtonsHtml += `<button class="kds-item-action-btn p-1 bg-blue-600 hover:bg-blue-700 rounded text-xs" data-action="preparing" title="Tandai Sedang Disiapkan">Siap</button>`;
             }
             if (itemStatus === 'pending' || itemStatus === 'received' || itemStatus === 'preparing') {
                 actionButtonsHtml += `<button class="kds-item-action-btn p-1 bg-green-600 hover:bg-green-700 rounded text-xs" data-action="ready" title="Tandai Selesai">✓</button>`;
             }
            actionButtonsHtml += `</div>`;
        }

        return `
            <div id="item-${item.id}" class="item-card flex justify-between items-start border-b border-gray-600 border-opacity-50 pb-1" data-item-id="${item.id}" data-item-status="${itemStatus}">
                <div class="flex-grow mr-2 ${itemTextClass}">
                    <span class="font-semibold">${escapeHtml(item.quantity)}x</span>
                    <span class="ml-1">${escapeHtml(item.menu_item_name || 'Item tidak dikenal')}</span>
                    ${notesHtml}
                </div>
                ${actionButtonsHtml}
            </div>
        `;
    };

    // Fungsi membuat HTML kartu pesanan
    const createOrderCardHtml = (order) => {
        const orderTimestamp = Math.floor(new Date(order.ordered_at.replace(' ', 'T')+'Z').getTime() / 1000); // Perlu parse timestamp SQL
        const timeAgo = formatTimeAgo(orderTimestamp);
        const timeSinceOrder = Math.floor(Date.now() / 1000) - orderTimestamp;

        let cardBgColor = 'bg-gray-700';
        if (timeSinceOrder > 600) cardBgColor = 'bg-yellow-800';
        if (timeSinceOrder > 1200) cardBgColor = 'bg-red-800';
        if (order.order_status === 'preparing') cardBgColor = 'bg-blue-800';

        const itemsHtml = (order.items && order.items.length > 0)
            ? order.items.map(createItemCardHtml).join('')
            : '<p class="text-xs italic text-gray-400">Tidak ada detail item.</p>';

        const orderNotesHtml = order.notes ? `
            <div class="p-2 border-t border-gray-600 text-xs italic bg-black bg-opacity-10">
                Note Pesanan: ${escapeHtml(order.notes)}
            </div>` : '';

        let orderActionHtml = '';
         if (order.order_status === 'received') {
             orderActionHtml = `<button class="kds-order-action-btn w-full bg-blue-600 hover:bg-blue-700 py-1 rounded text-sm font-semibold" data-action="preparing" data-order-id="${order.id}">Mulai Siapkan Pesanan Ini</button>`;
         } else if (order.order_status === 'preparing') {
             orderActionHtml = `<button class="kds-order-action-btn w-full bg-green-600 hover:bg-green-700 py-1 rounded text-sm font-semibold" data-action="ready" data-order-id="${order.id}">Tandai Semua Selesai (Siap)</button>`;
         }

        return `
            <div id="order-card-${order.id}" class="order-card border border-gray-600 rounded-md shadow-md ${cardBgColor} text-white flex flex-col" data-order-id="${order.id}" data-ordered-at="${orderTimestamp}">
                <div class="p-2 border-b border-gray-600 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-lg">#${escapeHtml(order.order_code)}</span>
                        <span class="text-sm ml-2">(Meja: ${escapeHtml(order.table_number || '?')})</span>
                    </div>
                    <span class="time-ago text-xs font-mono bg-black bg-opacity-20 px-1.5 py-0.5 rounded" title="${escapeHtml(order.ordered_at)}">${timeAgo}</span>
                </div>
                <div class="order-items-container p-2 space-y-1.5 flex-grow overflow-y-auto max-h-60">
                    ${itemsHtml}
                </div>
                ${orderNotesHtml}
                <div class="p-2 border-t border-gray-600 text-center">
                    ${orderActionHtml}
                </div>
            </div>
        `;
    };

    // Fungsi escape HTML sederhana
    const escapeHtml = (unsafe) => {
        if (typeof unsafe !== 'string') return unsafe;
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    };

    // --- Fungsi Aksi (Update Status) ---

    const handleItemAction = async (itemId, action) => {
        const url = `${itemUpdateBaseUrl}/${itemId}`;
        console.log(`Mengirim aksi item: ${action} ke ${url}`);
         try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded', // atau application/json
                    'X-Requested-With': 'XMLHttpRequest',
                    // Tambahkan header CSRF jika ada
                },
                 body: `status=${action}` // Kirim status target
            });
            const result = await response.json();
            if (result.success) {
                console.log(`Item ${itemId} status updated to ${action}`);
                // Update UI item tersebut (akan dihandle oleh fetchUpdates berikutnya)
                // Atau update langsung di sini untuk responsivitas
                const itemCard = document.getElementById(`item-${itemId}`);
                if(itemCard) {
                     itemCard.dataset.itemStatus = action;
                     // Refresh kartu ordernya untuk update tampilan item & tombol order
                     const orderId = itemCard.closest('.order-card')?.dataset.orderId;
                     if(orderId) updateOrderCard(orderId); // Panggil fungsi update spesifik
                }
            } else {
                console.error(`Gagal update item ${itemId}:`, result.message);
                alert(`Error: ${result.message}`); // Tampilkan error ke user
            }
        } catch (error) {
            console.error('Error mengirim aksi item:', error);
            alert('Terjadi kesalahan jaringan saat update status item.');
        }
    };

    const handleOrderAction = async (orderId, action) => {
        let url = '';
        if (action === 'ready') {
             url = `${orderReadyBaseUrl}/${orderId}`;
        } else if (action === 'preparing') {
             // url = `${orderPreparingBaseUrl}/${orderId}`; // Jika ada endpoint terpisah
             console.warn('Aksi order "preparing" belum diimplementasikan sepenuhnya.');
             return;
        } else {
            console.error('Aksi order tidak dikenal:', action);
            return;
        }

        console.log(`Mengirim aksi order: ${action} ke ${url}`);
         try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    // CSRF header
                },
                // Tidak perlu body jika hanya menandai
            });
            const result = await response.json();
            if (result.success) {
                 console.log(`Order ${orderId} status updated to ${action}`);
                 // Hapus kartu dari KDS jika status 'ready' (atau biarkan fetchUpdates menghapusnya)
                 if (action === 'ready') {
                    const orderCard = document.getElementById(`order-card-${orderId}`);
                    if (orderCard) {
                         orderCard.remove();
                         currentOrderIds.delete(String(orderId)); // Hapus dari Set
                         checkIfEmpty(); // Cek apakah grid jadi kosong
                    }
                 } else {
                     // Refresh kartu ordernya untuk update tombol/status
                     updateOrderCard(orderId);
                 }
            } else {
                console.error(`Gagal update order ${orderId}:`, result.message);
                alert(`Error: ${result.message}`);
            }
        } catch (error) {
            console.error('Error mengirim aksi order:', error);
            alert('Terjadi kesalahan jaringan saat update status pesanan.');
        }
    };

     // Fungsi untuk mengupdate kartu order spesifik (dipanggil setelah aksi item/order berhasil)
     // Ini akan melakukan fetch ulang hanya untuk order tsb, atau memicu fetchUpdates global
     const updateOrderCard = (orderId) => {
        console.log(`Meminta update untuk kartu order #${orderId}`);
        // Cara paling mudah: panggil fetchUpdates lagi untuk sinkronisasi penuh
        fetchAndUpdateOrders();
        // Atau: buat endpoint API terpisah untuk fetch detail 1 order lalu render ulang card tsb
     };


    // --- Fungsi Fetch & Update Tampilan ---

    const fetchAndUpdateOrders = async () => {
        if (isFetching || !fetchUrl) return; // Hindari fetch ganda atau jika URL tidak ada
        isFetching = true;
        console.log('Fetching updates...');

        try {
            const response = await fetch(fetchUrl, {
                 headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            const fetchedOrders = data.orders || [];
            const fetchedOrderIds = new Set(fetchedOrders.map(order => String(order.id)));
            let newOrderDetected = false;

            // 1. Hapus kartu pesanan yang sudah tidak ada di data baru
            currentOrderIds.forEach(orderId => {
                if (!fetchedOrderIds.has(orderId)) {
                    const cardToRemove = document.getElementById(`order-card-${orderId}`);
                    if (cardToRemove) {
                        console.log(`Menghapus kartu order #${orderId}`);
                        cardToRemove.remove();
                    }
                    currentOrderIds.delete(orderId);
                }
            });

            // 2. Tambah atau Update kartu pesanan dari data baru
            fetchedOrders.forEach(order => {
                const orderIdStr = String(order.id);
                const existingCard = document.getElementById(`order-card-${orderIdStr}`);
                const newCardHtml = createOrderCardHtml(order);

                if (!existingCard) {
                    // Pesanan baru, tambahkan ke grid
                    console.log(`Menambahkan kartu order baru #${orderIdStr}`);
                    kdsGrid.insertAdjacentHTML('beforeend', newCardHtml); // Tambah di akhir
                    currentOrderIds.add(orderIdStr);
                    newOrderDetected = true; // Tandai ada pesanan baru
                } else {
                    // Pesanan sudah ada, cek apakah perlu diupdate
                    // Cara simpel: replace saja HTMLnya jika berbeda
                    // Cara lebih canggih: bandingkan detail item/status (lebih kompleks)
                    if (existingCard.outerHTML !== newCardHtml) { // Perbandingan HTML string (kurang efisien)
                        console.log(`Mengupdate kartu order #${orderIdStr}`);
                        existingCard.outerHTML = newCardHtml;
                         // Re-attach event listener diperlukan jika mereplace outerHTML
                         // (Lebih baik update innerHTML atau elemen spesifik)
                    } else {
                        // Update time ago saja jika tidak ada perubahan lain
                         const timeAgoElement = existingCard.querySelector('.time-ago');
                         if(timeAgoElement){
                              const orderTimestamp = existingCard.dataset.orderedAt;
                              timeAgoElement.textContent = formatTimeAgo(orderTimestamp);
                         }
                    }
                }
            });

            // 3. Urutkan kartu berdasarkan waktu pesan (opsional)
            // sortOrderCards();

            // 4. Mainkan suara jika ada pesanan baru
            if (newOrderDetected) {
                playNotificationSound();
            }

             // 5. Tampilkan/sembunyikan pesan "Tidak ada pesanan"
            checkIfEmpty();

        } catch (error) {
            console.error("Gagal fetch KDS updates:", error);
            // Tampilkan pesan error di UI?
        } finally {
            isFetching = false; // Selesai fetch
        }
    };

     // Fungsi cek apakah grid kosong
     const checkIfEmpty = () => {
         const orderCards = kdsGrid.querySelectorAll('.order-card');
         if (orderCards.length === 0) {
             noOrdersMessage.classList.remove('hidden');
         } else {
             noOrdersMessage.classList.add('hidden');
         }
     };

    // --- Event Listeners ---
    kdsGrid.addEventListener('click', (event) => {
        const target = event.target;

        // Cek apakah tombol aksi item yang diklik
        if (target.classList.contains('kds-item-action-btn')) {
             const itemCard = target.closest('.item-card');
             const itemId = itemCard?.dataset.itemId;
             const action = target.dataset.action;
             if (itemId && action) {
                 handleItemAction(itemId, action);
             }
        }

        // Cek apakah tombol aksi order yang diklik
        if (target.classList.contains('kds-order-action-btn')) {
            const orderId = target.dataset.orderId;
            const action = target.dataset.action;
            if (orderId && action) {
                handleOrderAction(orderId, action);
            }
        }
    });


    // --- Inisialisasi ---
    const startPolling = () => {
        if(pollingTimer) clearInterval(pollingTimer); // Hentikan timer lama jika ada
        console.log(`KDS Polling dimulai, interval: ${refreshInterval / 1000} detik`);
        fetchAndUpdateOrders(); // Panggil pertama kali
        pollingTimer = setInterval(fetchAndUpdateOrders, refreshInterval);
    };

    // Mulai polling setelah halaman siap
    startPolling();
    checkIfEmpty(); // Cek kondisi awal
});