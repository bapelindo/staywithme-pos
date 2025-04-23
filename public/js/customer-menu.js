// File: public/assets/js/customer-menu.js

document.addEventListener('DOMContentLoaded', () => {
    const cartSection = document.getElementById('cart-section');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalElement = document.getElementById('cart-total');
    const placeOrderBtn = document.getElementById('place-order-btn');
    const cartEmptyMessage = cartItemsContainer?.querySelector('.cart-empty-message');
    const orderMessageElement = document.getElementById('order-message');
    const tableIdInput = document.getElementById('table-id'); // Input hidden berisi table ID

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
        showCartTemporarily(); // Tampilkan cart sebentar saat item ditambahkan
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
        if (!cartItemsContainer || !cartTotalElement || !placeOrderBtn || !cartEmptyMessage) {
            console.error("Elemen keranjang tidak ditemukan!");
            return;
        }

        cartItemsContainer.innerHTML = ''; // Kosongkan item lama
        let total = calculateTotal();
        let itemCount = Object.keys(cart).length;

        if (itemCount === 0) {
            cartEmptyMessage.style.display = 'block';
            placeOrderBtn.disabled = true;
        } else {
            cartEmptyMessage.style.display = 'none';
            placeOrderBtn.disabled = false;

            // Urutkan item berdasarkan nama atau urutan penambahan jika perlu
            Object.values(cart).forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.className = 'cart-item py-2 border-b border-gray-100 flex items-start';
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

        // Tambahkan event listener ke tombol +/- dan input notes di dalam keranjang
        addCartItemEventListeners();
    }

    function addCartItemEventListeners() {
        document.querySelectorAll('.quantity-change').forEach(button => {
             // Hapus listener lama sebelum menambah yg baru untuk cegah duplikasi
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
             input.addEventListener('change', (e) => { // 'change' lebih baik dari 'input' agar tidak trigger terus menerus
                 const itemId = input.dataset.itemId;
                 updateItemNotes(itemId, e.target.value);
             });
         });
    }


    let cartTimeout;
    function showCartTemporarily(duration = 3000) {
        if(cartSection) {
            // cartSection.style.transform = 'scale(1)'; // Ganti cara menampilkan/menyembunyikan jika perlu
            cartSection.style.opacity = '1';
            cartSection.style.pointerEvents = 'auto';

            clearTimeout(cartTimeout); // Reset timer jika sudah ada
             // Sembunyikan lagi setelah durasi tertentu (jika diperlukan)
             /*
            cartTimeout = setTimeout(() => {
                // cartSection.style.transform = 'scale(0.9)';
                cartSection.style.opacity = '0';
                cartSection.style.pointerEvents = 'none';
            }, duration);
             */
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
            // Format items sesuai kebutuhan backend (OrderController::placeOrder)
            items: Object.values(cart).map(item => ({
                menu_item_id: parseInt(item.id),
                quantity: item.quantity,
                notes: item.notes ?? '' // Pastikan notes dikirim
            })),
            // Tambahkan catatan umum jika ada inputnya di UI
            // notes: document.getElementById('general-order-notes')?.value ?? null
        };

        try {
            // Ambil BASE_URL dari global JS variable jika ada, atau hardcode (kurang ideal)
            const baseUrl = window.APP_BASE_URL || ''; // Anda perlu mendefinisikan ini di layout PHP
            const response = await fetch(`${baseUrl}/order/place`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    // Tambahkan header CSRF token jika backend memerlukannya
                },
                body: JSON.stringify(orderData)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showOrderMessage('Pesanan berhasil! Mengalihkan...', 'success');
                cart = {}; // Kosongkan cart state
                saveCart(); // Hapus dari localStorage
                updateCartUI();

                // Redirect ke halaman status setelah beberapa saat
                setTimeout(() => {
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                         showOrderMessage('Pesanan berhasil, tapi URL status tidak ditemukan.', 'warning');
                         placeOrderBtn.textContent = 'Pesan Lagi?'; // Atau state lain
                         placeOrderBtn.disabled = false;
                    }
                }, 1500);

            } else {
                throw new Error(result.message || 'Gagal mengirim pesanan.');
            }

        } catch (error) {
            console.error('Error placing order:', error);
            showOrderMessage(`Error: ${error.message}`, 'error');
            placeOrderBtn.disabled = false;
            placeOrderBtn.textContent = 'Pesan Sekarang';
        }
    }

    function showOrderMessage(message, type = 'info') {
        if (orderMessageElement) {
            orderMessageElement.textContent = message;
            orderMessageElement.className = 'mt-2 text-sm text-center'; // Reset class
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

    // Jika keranjang tidak kosong, tampilkan saat load
    if (Object.keys(cart).length > 0) {
       // showCartTemporarily(100); // Tampilkan sebentar saja
    }

});