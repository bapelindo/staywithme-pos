// File: public/assets/js/admin-orders.js

document.addEventListener('DOMContentLoaded', () => {
    // Hanya jalankan jika kita berada di halaman daftar pesanan admin
    const ordersTableBody = document.querySelector('#admin-orders-table-body'); // Pastikan ID ini ada di <tbody> view PHP
    if (!ordersTableBody) {
        // console.log('Bukan halaman daftar pesanan admin, polling tidak aktif.');
        return;
    }

    console.log('Admin Orders JS: Polling diaktifkan.');

    const POLLING_RATE_ADMIN_ORDERS = 5000; // Check setiap 15 detik
    let lastSeenOrderId = 0;

    // Fungsi untuk mendapatkan ID order tertinggi yang sedang tampil di tabel
    function getHighestOrderId() {
        let maxId = 0;
        // Cari semua baris yang punya data-order-id
        ordersTableBody.querySelectorAll('tr[data-order-id]').forEach(row => {
            const id = parseInt(row.dataset.orderId);
            if (!isNaN(id) && id > maxId) {
                maxId = id;
            }
        });
        console.log('Admin Orders JS: Highest current Order ID:', maxId);
        return maxId;
    }

    // Fungsi untuk membuat HTML baris tabel baru dari data pesanan
    function createOrderRowHtml(order) {
        // Struktur HTML ini harus sama persis dengan <tr> di view admin/orders/index.php
        // Sesuaikan jika struktur HTML di view Anda berbeda
        return `
            <tr data-order-id="${order.id}" class="new-order-highlight">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                    ${order.order_number || 'N/A'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                    ${order.table_number || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                    <span title="${order.order_time_full || ''}">
                        ${order.order_time_ago || 'N/A'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 text-right font-medium">
                    ${order.total_amount || 'Rp 0'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-medium">
                    <span class="px-2.5 py-0.5 rounded-full ${order.status_class || 'bg-gray-100 text-gray-600'}">
                        ${order.status_text || order.status || 'N/A'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <a href="${window.APP_BASE_URL || ''}/admin/orders/show/${order.id}" class="text-indigo-600 hover:text-indigo-800" title="Lihat Detail">Detail</a>
                    <a href="${window.APP_BASE_URL || ''}/admin/orders/invoice/${order.id}" class="text-green-600 hover:text-green-800" title="Lihat Invoice" target="_blank">Invoice</a>
                </td>
            </tr>
        `;
    }

    // Fungsi utama untuk fetch data baru
    async function fetchNewAdminOrders() {
        // Update lastSeenOrderId hanya jika belum ada (saat pertama kali poll)
        if (lastSeenOrderId === 0) {
            lastSeenOrderId = getHighestOrderId();
        }

        const baseUrl = window.APP_BASE_URL || '';
        try {
            // Panggil endpoint /admin/orders/new dengan ID terakhir
            const response = await fetch(`${baseUrl}/admin/orders/new?lastSeenId=${lastSeenOrderId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' // Tandai sebagai AJAX request
                }
            });

            if (!response.ok) {
                console.error('Admin Orders JS: Gagal fetch pesanan baru, status:', response.status);
                // Mungkin hentikan polling jika ada error serius?
                // clearInterval(pollingInterval);
                return;
            }

            const result = await response.json();

            // Proses jika sukses dan ada pesanan baru
            if (result.success && Array.isArray(result.new_orders) && result.new_orders.length > 0) {
                console.log(`Admin Orders JS: Ditemukan ${result.new_orders.length} pesanan baru.`);
                let maxNewId = lastSeenOrderId;

                // Balik urutan agar ID terbesar diproses terakhir, lalu tambahkan ke atas
                result.new_orders.reverse().forEach(order => {
                    // Cek dulu apakah order ID ini sudah ada di tabel (antisipasi duplikasi)
                    if (!ordersTableBody.querySelector(`tr[data-order-id="${order.id}"]`)) {
                         const newRowHtml = createOrderRowHtml(order);
                         // Tambahkan baris baru di paling atas tbody
                         ordersTableBody.insertAdjacentHTML('afterbegin', newRowHtml);
                    }
                    // Selalu update maxNewId untuk polling berikutnya
                    if (order.id > maxNewId) {
                        maxNewId = order.id;
                    }
                });

                lastSeenOrderId = maxNewId; // Update ID terakhir yang dilihat

                // Opsional: Hapus highlight setelah beberapa detik
                setTimeout(() => {
                    document.querySelectorAll('.new-order-highlight').forEach(el => el.classList.remove('new-order-highlight'));
                }, 5000); // 5 detik


            } else if (result.success && result.new_orders.length === 0) {
                // Tidak ada order baru, tidak perlu lakukan apa-apa
                // console.log('Admin Orders JS: Tidak ada pesanan baru.');
            } else if (!result.success) {
                console.error('Admin Orders JS: API mengembalikan error:', result.message);
            }

        } catch (error) {
            console.error('Admin Orders JS: Error saat polling:', error);
        }
    }



    // --- Inisialisasi dan Mulai Polling ---
    lastSeenOrderId = getHighestOrderId(); // Dapatkan ID tertinggi saat halaman dimuat
    const pollingInterval = setInterval(fetchNewAdminOrders, POLLING_RATE_ADMIN_ORDERS);
    console.log(`Admin Orders JS: Polling dimulai setiap ${POLLING_RATE_ADMIN_ORDERS / 1000} detik.`);

    // CSS untuk highlight baris baru (tambahkan ke file CSS utama atau biarkan di sini)
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInHighlight {
            from { background-color: rgba(224, 231, 255, 0.7); } /* Indigo 100 with transparency */
            to { background-color: transparent; }
        }
        .new-order-highlight {
            animation: fadeInHighlight 5s ease-out;
        }
    `;
    document.head.appendChild(style);

});