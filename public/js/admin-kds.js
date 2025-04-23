// File: public/assets/js/admin-kds.js

document.addEventListener('DOMContentLoaded', () => {
    const kdsOrderContainer = document.getElementById('kds-order-container');
    const kdsEmptyMessage = document.getElementById('kds-empty-message');

    if (!kdsOrderContainer || !kdsEmptyMessage) {
        console.error('Elemen KDS container atau pesan kosong tidak ditemukan.');
        return;
    }

    const POLLING_RATE_KDS = 5000; // Check setiap 7 detik
    let currentOrderIds = new Set(); // Menyimpan ID order yang sedang ditampilkan
    let notificationAudio; // Untuk suara notifikasi

    // --- Fungsi Render Order Card ---
    function createOrderCard(order) {
        const card = document.createElement('div');
        card.className = `kds-order-card bg-white rounded-lg shadow-md border p-4 flex flex-col relative transition-all duration-300 ${order.status === 'received' ? 'border-blue-300' : 'border-yellow-300'}`;
        card.dataset.orderId = order.id;
        card.dataset.status = order.status;

        // Header Card (Nomor Order, Meja, Waktu)
        let headerHtml = `
            <div class="flex justify-between items-center mb-3 pb-2 border-b">
                <span class="font-bold text-lg ${order.status === 'received' ? 'text-blue-700' : 'text-yellow-700'}">#${order.order_number.replace('STW-', '')}</span>
                <span class="text-sm text-gray-600 font-medium">Meja: ${order.table_number}</span>
                <span class="text-xs text-gray-500">${order.order_time_ago || ''}</span>
            </div>
        `;

        // Item List
        let itemsHtml = '<ul class="space-y-1.5 text-sm mb-4 flex-grow list-disc list-inside pl-1">';
        order.items.forEach(item => {
            itemsHtml += `
                <li class="text-gray-800">
                    <span class="font-semibold">${item.quantity}x</span> ${item.name}
                    ${item.notes ? `<span class="block text-xs text-orange-600 italic ml-4">- ${item.notes}</span>` : ''}
                </li>`;
        });
        itemsHtml += '</ul>';

        // Action Buttons
        let actionHtml = '<div class="mt-auto flex space-x-2">';
        if (order.status === 'received') {
            actionHtml += `<button class="kds-action-btn flex-1 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium py-1.5 px-3 rounded transition duration-150" data-order-id="${order.id}" data-new-status="preparing">Mulai Siapkan</button>`;
        } else if (order.status === 'preparing') {
            actionHtml += `<button class="kds-action-btn flex-1 bg-green-500 hover:bg-green-600 text-white text-sm font-medium py-1.5 px-3 rounded transition duration-150" data-order-id="${order.id}" data-new-status="ready">Siap Disajikan</button>`;
             // Tombol Batal (opsional)
             actionHtml += `<button class="kds-action-btn bg-red-500 hover:bg-red-600 text-white text-xs font-medium py-1.5 px-2 rounded transition duration-150" data-order-id="${order.id}" data-new-status="cancelled">X Batal</button>`;
        }
        actionHtml += '</div>';

        card.innerHTML = headerHtml + itemsHtml + actionHtml;
        return card;
    }

    // --- Fungsi Update Tampilan KDS ---
    function renderOrders(orders) {
        kdsOrderContainer.innerHTML = ''; // Kosongkan container
        const newOrderIds = new Set();
        let hasNewReceivedOrder = false;

        if (orders.length === 0) {
            kdsEmptyMessage.style.display = 'block';
            currentOrderIds.clear();
            return;
        }

        kdsEmptyMessage.style.display = 'none';

        orders.forEach(order => {
            newOrderIds.add(order.id.toString()); // Simpan ID sebagai string
            const card = createOrderCard(order);
            kdsOrderContainer.appendChild(card);

            // Cek jika ini order 'received' yang baru muncul
            if (order.status === 'received' && !currentOrderIds.has(order.id.toString())) {
                hasNewReceivedOrder = true;
                 card.classList.add('opacity-0', 'scale-95');
                 setTimeout(() => {
                     card.classList.remove('opacity-0', 'scale-95');
                 }, 50); // Animasikan masuk
            }
        });

        // Update currentOrderIds untuk pengecekan berikutnya
        currentOrderIds = newOrderIds;

        // Tambahkan event listener ke tombol aksi
        addKdsActionListeners();

        // Mainkan suara jika ada order 'received' baru
        if (hasNewReceivedOrder) {
            playNotificationSound();
        }
    }

    // --- Fungsi Tambah Listener ke Tombol Aksi ---
    function addKdsActionListeners() {
        document.querySelectorAll('.kds-action-btn').forEach(button => {
             // Clone & replace untuk cegah multiple listener
             button.replaceWith(button.cloneNode(true));
        });
         document.querySelectorAll('.kds-action-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                 const orderId = e.target.dataset.orderId;
                 const newStatus = e.target.dataset.newStatus;

                 // Konfirmasi jika batal
                 if (newStatus === 'cancelled') {
                     if (!confirm(`Anda yakin ingin membatalkan pesanan #${orderId}?`)) {
                         return;
                     }
                 }

                 e.target.disabled = true;
                 e.target.textContent = '...';

                 const success = await updateOrderStatus(orderId, newStatus);

                 if (success) {
                     // Hapus card dari tampilan atau refresh langsung
                     const cardToRemove = kdsOrderContainer.querySelector(`.kds-order-card[data-order-id="${orderId}"]`);
                     if(cardToRemove) {
                         cardToRemove.classList.add('opacity-0', 'scale-90');
                         setTimeout(() => cardToRemove.remove(), 300);
                     }
                     // Cek apakah container jadi kosong
                     if (kdsOrderContainer.children.length <= 1 && kdsEmptyMessage) { // <=1 karena mungkin ada pesan kosong
                         kdsEmptyMessage.style.display = 'block';
                     }
                     // Atau panggil fetchKdsOrders() lagi untuk refresh data
                     // fetchKdsOrders();
                 } else {
                     // Tampilkan error atau kembalikan state tombol
                     alert(`Gagal mengubah status pesanan #${orderId}.`);
                     e.target.disabled = false;
                     // Kembalikan teks tombol asli
                     if (newStatus === 'preparing') e.target.textContent = 'Mulai Siapkan';
                     else if (newStatus === 'ready') e.target.textContent = 'Siap Disajikan';
                     else if (newStatus === 'cancelled') e.target.textContent = 'X Batal';
                 }
            });
        });
    }


    // --- Fungsi Update Status ke Backend ---
    async function updateOrderStatus(orderId, newStatus) {
        const baseUrl = window.APP_BASE_URL || '';
        try {
            // Ganti endpoint jika berbeda antara KDS dan Admin Order
            const response = await fetch(`${baseUrl}/admin/kds/update_status`, { // atau /admin/orders/update_status
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ order_id: parseInt(orderId), new_status: newStatus })
            });
            const result = await response.json();
            return response.ok && result.success;
        } catch (error) {
            console.error('Error updating order status:', error);
            return false;
        }
    }

    // --- Fungsi Fetch Data (Polling) ---
    async function fetchKdsOrders() {
        const baseUrl = window.APP_BASE_URL || '';
        try {
            const response = await fetch(`${baseUrl}/admin/kds/get_orders`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) {
                console.error('Gagal fetch data KDS, status:', response.status);
                return; // Jangan render jika error
            }
            const result = await response.json();
            if (result.success && Array.isArray(result.orders)) {
                renderOrders(result.orders);
            } else {
                 console.error('Format data KDS tidak sesuai:', result);
                 renderOrders([]); // Tampilkan kosong jika format salah
            }
        } catch (error) {
            console.error('Error saat polling KDS:', error);
             renderOrders([]); // Tampilkan kosong jika network error
        }
    }

     // --- Fungsi Suara Notifikasi ---
    function playNotificationSound() {
        try {
            if (!notificationAudio) {
                 // Ganti dengan path file audio Anda
                 notificationAudio = new Audio(window.KDS_AUDIO_URL); // Suara berbeda?
            }
            if (notificationAudio.paused) {
                 notificationAudio.play().catch(e => console.warn("Gagal memainkan suara KDS:", e));
            }
        } catch (e) {
            console.warn("Tidak dapat memainkan suara KDS:", e);
        }
    }

    // --- Inisialisasi ---
    console.log('Memulai polling KDS...');
    fetchKdsOrders(); // Panggil sekali di awal
    setInterval(fetchKdsOrders, POLLING_RATE_KDS);

});