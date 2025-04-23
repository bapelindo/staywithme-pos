// File: public/assets/js/customer-menu.js

document.addEventListener('DOMContentLoaded', () => {
    // Seleksi semua elemen penting di sini
    const cartSection = document.getElementById('cart-section');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalElement = document.getElementById('cart-total');
    const placeOrderBtn = document.getElementById('place-order-btn');
    const cartEmptyMessage = cartItemsContainer?.querySelector('.cart-empty-message');
    const orderMessageElement = document.getElementById('order-message');
    const tableIdInput = document.getElementById('table-id'); // Input hidden berisi table ID
    const cartItemCountElement = document.getElementById('cart-item-count'); // *** Seleksi elemen count di sini ***
    const cartHeader = document.getElementById('cart-header'); // Untuk toggle
    const cartContent = document.getElementById('cart-content'); // Untuk toggle
    const cartToggleIcon = document.getElementById('cart-toggle-icon'); // Untuk toggle

    // --- State Keranjang ---
    let cart = loadCart(); // Load cart dari localStorage saat halaman dimuat

    // --- Fungsi Keranjang ---

    function loadCart() {
        const storedCart = localStorage.getItem('staywithme_cart');
        return storedCart ? JSON.parse(storedCart) : {}; // { itemId: { id, name, price, quantity, notes } }
    }

    function saveCart() {
        localStorage.setItem('staywithme_cart', JSON.stringify(cart));
    }

    function addToCart(item) {
        const itemId = item.id;
        if (cart[itemId]) {
            cart[itemId].quantity += 1;
        } else {
            cart[itemId] = { ...item, quantity: 1, notes: '' }; // notes default kosong
        }
        saveCart();
        updateCartUI();
        showCartTemporarily(); // Panggil fungsi untuk buka cart saat item ditambah
    }

    function updateQuantity(itemId, change) {
        if (cart[itemId]) {
            cart[itemId].quantity += change;
            if (cart[itemId].quantity <= 0) {
                delete cart[itemId]; // Hapus item jika kuantitas 0 atau kurang
            }
            saveCart();
            updateCartUI();
        }
    }

     function updateItemNotes(itemId, notes) {
         if (cart[itemId]) {
            cart[itemId].notes = notes.trim();
            saveCart();
            // Tidak perlu update UI penuh jika hanya notes
         }
     }

    function calculateTotal() {
        let total = 0;
        for (const itemId in cart) {
            total += cart[itemId].price * cart[itemId].quantity;
        }
        return total;
    }

    function formatCurrency(amount) {
        // Gunakan Intl.NumberFormat untuk format Rupiah yang lebih baik
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0, // Tidak pakai desimal untuk Rupiah
            maximumFractionDigits: 0
        }).format(amount);
    }

    // --- Update Tampilan Keranjang ---
    function updateCartUI() {
        // Pastikan semua elemen ada sebelum digunakan
        if (!cartItemsContainer || !cartTotalElement || !placeOrderBtn || !cartEmptyMessage || !cartItemCountElement) {
            console.error("Elemen keranjang penting tidak ditemukan di updateCartUI!");
            return;
        }

        cartItemsContainer.innerHTML = ''; // Kosongkan item lama
        let total = calculateTotal();
        // Hitung jumlah jenis item unik
        let itemCount = Object.keys(cart).length;

        // *** PERBAIKAN: Update text content untuk jumlah item ***
        cartItemCountElement.textContent = itemCount;
        // ********************************************************

        if (itemCount === 0) {
            cartEmptyMessage.style.display = 'block';
            placeOrderBtn.disabled = true;
        } else {
            cartEmptyMessage.style.display = 'none';
            placeOrderBtn.disabled = false;

            // Render item keranjang
            Object.values(cart).forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.className = 'cart-item py-2 border-b border-gray-100 flex items-start'; // Sesuaikan class jika perlu
                itemElement.innerHTML = `
                    <div class="flex-grow pr-2">
                        <p class="text-sm font-medium text-gray-800">${item.name}</p>
                        <p class="text-xs text-indigo-600">${formatCurrency(item.price)}</p>
                        <input type="text" placeholder="Catatan item..." value="${item.notes ?? ''}" data-item-id="${item.id}" class="item-notes-input mt-1 text-xs p-1 border rounded w-full" maxlength="100">
                    </div>
                    <div class="flex items-center space-x-1.5">
                        <button class="quantity-change bg-gray-200 text-gray-600 hover:bg-gray-300 rounded-full w-5 h-5 text-xs leading-none" data-item-id="${item.id}" data-change="-1">-</button>
                        <span class="text-sm font-medium w-4 text-center">${item.quantity}</span>
                        <button class="quantity-change bg-gray-200 text-gray-600 hover:bg-gray-300 rounded-full w-5 h-5 text-xs leading-none" data-item-id="${item.id}" data-change="1">+</button>
                    </div>
                `;
                cartItemsContainer.appendChild(itemElement);
            });
        }

        cartTotalElement.textContent = formatCurrency(total);

        // Tambahkan kembali event listener ke tombol +/- dan input notes
        addCartItemEventListeners();
    }

    // Fungsi untuk menambahkan listener ke elemen dinamis di keranjang
    function addCartItemEventListeners() {
        document.querySelectorAll('.quantity-change').forEach(button => {
             // Hapus listener lama sebelum menambah yg baru (mencegah duplikasi)
             button.replaceWith(button.cloneNode(true));
        });
         document.querySelectorAll('.quantity-change').forEach(button => {
            button.addEventListener('click', () => {
                const itemId = button.dataset.itemId;
                const change = parseInt(button.dataset.change);
                updateQuantity(itemId, change);
            });
        });

        document.querySelectorAll('.item-notes-input').forEach(input => {
             // Hapus listener lama
             input.replaceWith(input.cloneNode(true));
        });
         document.querySelectorAll('.item-notes-input').forEach(input => {
             input.addEventListener('change', (e) => {
                 const itemId = input.dataset.itemId;
                 updateItemNotes(itemId, e.target.value);
             });
         });
    }

    // --- Fungsi untuk menampilkan/menyembunyikan cart ---
    let isCartOpen = false;
    function toggleCart(forceOpen = false) {
        if (!cartHeader || !cartContent || !cartSection || !cartToggleIcon) return;

        if (forceOpen) {
            isCartOpen = true;
        } else {
             isCartOpen = !isCartOpen;
        }

        if (isCartOpen) {
            cartSection.style.transform = 'translateY(0)';
            cartContent.style.maxHeight = '400px'; // Sesuaikan max-height
            cartToggleIcon.style.transform = 'rotate(0deg)';
        } else {
            cartContent.style.maxHeight = '0px';
            cartToggleIcon.style.transform = 'rotate(180deg)';
            // Beri jeda sebelum translate agar transisi max-height selesai
            setTimeout(() => {
                 if (!isCartOpen) cartSection.style.transform = `translateY(calc(100% - ${cartHeader.offsetHeight}px))`; // Gunakan tinggi header dinamis
            }, 300);
        }
    }

    // Fungsi dipanggil saat item ditambah
    function showCartTemporarily() {
        if (!isCartOpen) {
             toggleCart(true); // Paksa buka cart
        }
    }

    // --- Event Listeners Halaman Menu ---
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', () => {
            const item = {
                id: button.dataset.id,
                name: button.dataset.name,
                price: parseFloat(button.dataset.price) // Pastikan ini angka
            };
            if (!isNaN(item.price)) {
                addToCart(item);
            } else {
                console.error('Harga item tidak valid:', button.dataset.price);
            }
        });
    });

    // Listener untuk toggle cart header
     if (cartHeader) {
        cartHeader.addEventListener('click', () => toggleCart());
     }


    // --- Fungsi Kirim Pesanan ---
    async function sendOrder() {
        if (!tableIdInput || !tableIdInput.value) {
            showOrderMessage('Error: ID Meja tidak ditemukan.', 'error');
            return;
        }
        if (Object.keys(cart).length === 0) {
            showOrderMessage('Keranjang Anda kosong.', 'info');
            return;
        }

        placeOrderBtn.disabled = true;
        placeOrderBtn.textContent = 'Memproses...';
        showOrderMessage(''); // Hapus pesan lama

        const orderData = {
            table_id: parseInt(tableIdInput.value),
            items: Object.values(cart).map(item => ({
                menu_item_id: parseInt(item.id),
                quantity: item.quantity,
                notes: item.notes ?? ''
            })),
        };

        try {
            const baseUrl = window.APP_BASE_URL || '';
            // *** Pastikan URL Fetch Benar (Tanpa Double Slash) ***
            const fetchUrl = `${baseUrl}/order/place`; // Harusnya baseUrl sudah tanpa slash akhir
            console.log("Sending order to:", fetchUrl); // Debug URL

            const response = await fetch(fetchUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(orderData)
            });

            // Cek status sebelum mencoba parse JSON
            if (!response.ok) {
                 // Coba baca response text jika bukan JSON (misal error HTML)
                 const errorText = await response.text();
                 console.error('Server response error text:', errorText);
                 throw new Error(`Server error: ${response.status} ${response.statusText}. Response: ${errorText.substring(0, 100)}...`);
            }

            const result = await response.json();

            if (result.success) {
                showOrderMessage('Pesanan berhasil! Mengalihkan...', 'success');
                cart = {};
                saveCart();
                updateCartUI();

                setTimeout(() => {
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                         showOrderMessage('Pesanan berhasil, tapi URL status tidak ditemukan.', 'warning');
                         placeOrderBtn.textContent = 'Pesan Sekarang'; // Kembalikan teks tombol
                         placeOrderBtn.disabled = false; // Aktifkan lagi
                    }
                }, 1500);

            } else {
                throw new Error(result.message || 'Gagal mengirim pesanan.');
            }

        } catch (error) {
            console.error('Error placing order:', error);
            // Tampilkan pesan error yang lebih informatif jika memungkinkan
            let displayError = 'Terjadi kesalahan saat mengirim pesanan.';
            if (error instanceof SyntaxError) {
                 displayError = 'Format respons server tidak valid.';
            } else if (error.message) {
                 // Coba tampilkan pesan error dari server jika ada
                 if (error.message.includes('Server error:')) {
                    displayError = `Gagal mengirim pesanan. Server: ${error.message.split('.')[1] || 'Unknown Error'}`;
                 } else {
                    displayError = `Error: ${error.message}`;
                 }
            }
            showOrderMessage(displayError, 'error');
            placeOrderBtn.disabled = false;
            placeOrderBtn.textContent = 'Pesan Sekarang';
        }
    }

    function showOrderMessage(message, type = 'info') {
        if (orderMessageElement) {
            orderMessageElement.textContent = message;
            orderMessageElement.className = 'mt-3 text-sm text-center font-medium'; // Reset class + tambah font-medium
            if (type === 'success') {
                orderMessageElement.classList.add('text-green-600');
            } else if (type === 'error') {
                orderMessageElement.classList.add('text-red-600');
            } else if (type === 'warning') {
                orderMessageElement.classList.add('text-yellow-600');
            } else {
                 orderMessageElement.classList.add('text-gray-500');
            }
        }
    }

    // Listener untuk tombol pesan
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', sendOrder);
    }

    // --- Inisialisasi ---
    updateCartUI(); // Tampilkan cart saat halaman pertama kali dimuat

    // Inisialisasi posisi cart (sedikit terlihat)
    if (cartSection && cartHeader) {
        setTimeout(() => { // Beri sedikit waktu agar offsetHeight benar
             cartSection.style.transform = `translateY(calc(100% - ${cartHeader.offsetHeight}px))`;
        }, 100);
    }

});