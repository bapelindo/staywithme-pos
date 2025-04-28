// File: public/js/customer-menu.js (Lengkap & Final dengan Animasi)

// Helper Sanitasi Sederhana
const SanitizeHelper = {
    html: (text) => {
         if (text === null || typeof text === 'undefined') return '';
         const temp = document.createElement('div');
         temp.textContent = String(text);
         return temp.innerHTML;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Seleksi semua elemen penting
    const cartSection = document.getElementById('cart-section');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalElement = document.getElementById('cart-total');
    const placeOrderBtn = document.getElementById('place-order-btn');
    const cartEmptyMessage = cartItemsContainer?.querySelector('.cart-empty-message');
    const orderMessageElement = document.getElementById('order-message');
    const tableIdInput = document.getElementById('table-id');
    const cartItemCountElement = document.getElementById('cart-item-count');
    const cartHeader = document.getElementById('cart-header');
    const cartContent = document.getElementById('cart-content');
    const cartToggleIcon = document.getElementById('cart-toggle-icon');
    // Target untuk animasi terbang (ikon keranjang atau header)
    const cartTargetElement = document.querySelector('#cart-header i.fa-shopping-cart') || document.getElementById('cart-header');

    // Validasi elemen penting
    if (!cartItemsContainer || !cartTotalElement || !placeOrderBtn || !cartItemCountElement || !cartTargetElement || !tableIdInput) {
        console.error("Elemen UI Keranjang (container, total, button, count, target, atau tableId) tidak ditemukan! Fungsi keranjang/animasi mungkin terbatas.");
        // Pertimbangkan untuk tidak melanjutkan jika elemen krusial hilang
        // return;
    }

    // --- State Keranjang ---
    let cart = loadCart(); // Muat keranjang dari localStorage
    let isCartOpen = false; // Status awal cart tertutup

    // --- Fungsi Keranjang (Memuat & Menyimpan) ---
    function loadCart() {
        const storedCart = localStorage.getItem('staywithme_cart');
        if (storedCart) {
            try {
                const parsedCart = JSON.parse(storedCart);
                // Validasi lebih ketat data yang dimuat
                if (typeof parsedCart === 'object' && parsedCart !== null) {
                    Object.keys(parsedCart).forEach(key => {
                        const item = parsedCart[key];
                        if (typeof item !== 'object' || item === null ||
                            !item.id || typeof item.name !== 'string' ||
                            isNaN(parseFloat(item.price)) || isNaN(parseInt(item.quantity)) || item.quantity < 1) {
                            console.warn(`Item tidak valid ditemukan di keranjang localStorage (ID: ${key}), menghapus.`);
                            delete parsedCart[key];
                        }
                        // Pastikan properti notes ada
                        if (item && typeof item.notes === 'undefined') {
                             item.notes = '';
                         }
                    });
                    return parsedCart;
                } else { throw new Error("Data keranjang tersimpan bukan objek valid."); }
            } catch (e) {
                console.error("Gagal parse keranjang dari localStorage atau data tidak valid:", e);
                localStorage.removeItem('staywithme_cart'); // Hapus data korup
                return {};
            }
        }
        return {}; // Kembalikan objek kosong jika tidak ada data
    }

    function saveCart() {
        try {
            localStorage.setItem('staywithme_cart', JSON.stringify(cart));
        } catch (e) {
            console.error("Gagal menyimpan keranjang ke localStorage:", e);
        }
    }

    // --- Fungsi Kalkulasi & Format ---
    function calculateTotal() {
        let total = 0;
        for (const itemId in cart) {
            const item = cart[itemId];
            const price = parseFloat(item.price ?? 0);
            const quantity = parseInt(item.quantity ?? 0);
            if (!isNaN(price) && !isNaN(quantity) && quantity > 0) {
                 total += price * quantity;
            }
        }
        return total;
    }

    function formatCurrency(amount) {
         if (isNaN(parseFloat(amount))) { amount = 0; }
        return new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR',
            minimumFractionDigits: 0, maximumFractionDigits: 0
        }).format(amount);
    }

    // --- Fungsi Modifikasi Keranjang ---
    function addToCart(item) {
        const itemId = String(item.id);
        if (!itemId) return;

        if (cart[itemId]) {
            cart[itemId].quantity += 1;
        } else {
            cart[itemId] = {
                id: itemId,
                name: item.name ?? 'Item Tidak Dikenal',
                price: parseFloat(item.price ?? 0),
                quantity: 1,
                notes: ''
             };
        }
        saveCart();
        updateCartUI();
        flashCartHeader();
        // toggleCart(true); // <-- Baris ini sudah dihapus/dikomentari
    }

    function updateQuantity(itemId, change) {
         const strItemId = String(itemId);
        if (!strItemId || !cart[strItemId]) return;
        cart[strItemId].quantity += change;
        if (cart[strItemId].quantity <= 0) {
            delete cart[strItemId];
        }
        saveCart();
        updateCartUI();
    }

     function updateItemNotes(itemId, notes) {
        const strItemId = String(itemId);
        if (!strItemId || !cart[strItemId]) return;
        cart[strItemId].notes = notes.trim();
        saveCart();
     }

    // --- Update Tampilan Keranjang ---
    function updateCartUI() {
        const currentCartItemsContainer = document.getElementById('cart-items');
        const currentCartTotalElement = document.getElementById('cart-total');
        const currentPlaceOrderBtn = document.getElementById('place-order-btn');
        const currentCartEmptyMessage = currentCartItemsContainer?.querySelector('.cart-empty-message');
        const currentCartItemCountElement = document.getElementById('cart-item-count');

        if (!currentCartItemsContainer || !currentCartTotalElement || !currentPlaceOrderBtn || !currentCartItemCountElement) {
            console.error("Update UI keranjang gagal: elemen tidak ditemukan.");
            return;
        }

        currentCartItemsContainer.innerHTML = '';
        let total = calculateTotal();
        let itemCount = Object.keys(cart).length;

        currentCartItemCountElement.textContent = itemCount;

        if (itemCount === 0) {
            if(currentCartEmptyMessage) currentCartEmptyMessage.style.display = 'block';
            currentPlaceOrderBtn.disabled = true;
        } else {
            if(currentCartEmptyMessage) currentCartEmptyMessage.style.display = 'none';
            currentPlaceOrderBtn.disabled = false;

            Object.values(cart).forEach(item => {
                 if (!item || !item.id || !item.name || isNaN(parseFloat(item.price)) || isNaN(parseInt(item.quantity))) {
                    return; // Lewati item tidak valid
                 }

                const itemElement = document.createElement('div');
                itemElement.className = 'cart-item py-3 border-b border-border-dark/50 flex items-center gap-3';
                itemElement.setAttribute('data-id', item.id);

                const safeName = SanitizeHelper.html(item.name);
                const safeNotes = SanitizeHelper.html(item.notes ?? '');
                const formattedPrice = formatCurrency(item.price);

                itemElement.innerHTML = `
                    <div class="flex-grow min-w-0">
                        <p class="text-sm font-medium text-white truncate" title="${safeName}">${safeName}</p>
                        <p class="text-xs text-accent-secondary">${formattedPrice}</p>
                        <input type="text" placeholder="Catatan..." value="${safeNotes}" data-item-id="${item.id}" class="item-notes-input mt-1 text-xs p-1 border border-border-dark bg-bg-dark-tertiary text-text-dark-secondary rounded w-full focus:ring-1 focus:ring-accent-primary focus:border-accent-primary" maxlength="100">
                    </div>
                    <div class="flex items-center space-x-2 flex-shrink-0">
                        <button class="quantity-change bg-bg-dark-tertiary text-accent-primary hover:bg-bg-dark-secondary rounded-md w-6 h-6 text-lg leading-none flex items-center justify-center transition-colors" data-item-id="${item.id}" data-change="-1" aria-label="Kurangi jumlah ${safeName}">-</button>
                        <span class="text-md font-semibold text-white w-6 text-center" aria-label="Jumlah ${safeName}">${item.quantity}</span>
                        <button class="quantity-change bg-bg-dark-tertiary text-accent-primary hover:bg-bg-dark-secondary rounded-md w-6 h-6 text-lg leading-none flex items-center justify-center transition-colors" data-item-id="${item.id}" data-change="1" aria-label="Tambah jumlah ${safeName}">+</button>
                    </div>
                     <button class="cart-item-remove text-red-500 hover:text-red-400 ml-2 flex-shrink-0 text-xl font-bold" data-item-id="${item.id}" title="Hapus ${safeName}" aria-label="Hapus ${safeName}">&times;</button>
                `;
                currentCartItemsContainer.appendChild(itemElement);
            });
        }

        currentCartTotalElement.textContent = formatCurrency(total);
        addCartItemEventListeners(); // Wajib dipanggil setelah render ulang
    }

    // --- Pasang Listener ke Item Keranjang Dinamis (Event Delegation) ---
    function addCartItemEventListeners() {
        const container = document.getElementById('cart-items');
        if (!container) return;

        // Hapus listener lama sebelum pasang yang baru untuk mencegah duplikasi
        // Cara sederhana: ganti elemen dengan kloningannya
        const newContainer = container.cloneNode(true);
        container.parentNode.replaceChild(newContainer, container);

        // Pasang listener ke container baru
        newContainer.addEventListener('click', (e) => {
            const button = e.target.closest('button');
            if (!button) return;

            const itemId = button.dataset.itemId;
            if (!itemId) return;

            if (button.classList.contains('quantity-change')) {
                const change = parseInt(button.dataset.change);
                if (!isNaN(change)) {
                    updateQuantity(itemId, change);
                }
            } else if (button.classList.contains('cart-item-remove')) {
                const itemInCart = cart[String(itemId)];
                 if (itemInCart && confirm(`Hapus "${SanitizeHelper.html(itemInCart.name)}" dari keranjang?`)) {
                    delete cart[String(itemId)];
                    saveCart();
                    updateCartUI();
                 }
            }
        });

        newContainer.addEventListener('focusout', (e) => {
            if (e.target.classList.contains('item-notes-input')) {
                 const inputEl = e.target;
                 const itemId = inputEl.dataset.itemId;
                  if (itemId && cart[String(itemId)] && cart[String(itemId)].notes !== inputEl.value.trim()) {
                    updateItemNotes(itemId, inputEl.value);
                 }
            }
        });

        newContainer.addEventListener('keydown', (e) => {
             if (e.key === 'Enter' && e.target.classList.contains('item-notes-input')) {
                  e.preventDefault();
                  e.target.blur();
             }
        });
    }

    // --- Fungsi Tampilan Keranjang ---
    function toggleCart(forceState = null) {
        const currentCartHeader = document.getElementById('cart-header');
        const currentCartContent = document.getElementById('cart-content');
        const currentCartSection = document.getElementById('cart-section');
        const currentCartToggleIcon = document.getElementById('cart-toggle-icon');
        if (!currentCartHeader || !currentCartContent || !currentCartSection || !currentCartToggleIcon) return;

        const targetOpenState = (forceState === null) ? !isCartOpen : forceState;
        if (targetOpenState === isCartOpen) return;

        isCartOpen = targetOpenState;
        const cartHeaderHeight = currentCartHeader.offsetHeight || 52;
        currentCartSection.style.transform = isCartOpen ? 'translateY(0)' : `translateY(calc(100% - ${cartHeaderHeight}px))`;
        currentCartContent.style.maxHeight = isCartOpen ? '60vh' : '0px';
        currentCartToggleIcon.style.transform = isCartOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    function flashCartHeader() {
        const currentCartHeader = document.getElementById('cart-header');
        if (currentCartHeader) {
            currentCartHeader.style.transition = 'background-color 0.1s ease-in-out';
            currentCartHeader.style.backgroundColor = 'rgba(56, 189, 248, 0.3)';
            setTimeout(() => { currentCartHeader.style.backgroundColor = ''; }, 200);
        }
     }

    // --- Fungsi Animasi Tambah ke Keranjang ---
    function animateAddToCart(buttonElement) {
        const menuItemCard = buttonElement.closest('.menu-item');
        const itemImage = menuItemCard?.querySelector('img');

        if (!itemImage || !cartTargetElement) {
            // console.warn('Elemen gambar item atau target keranjang tidak ditemukan untuk animasi.');
            return;
        }
        if (itemImage.offsetParent === null) {
             // console.log('Source image is not visible, skipping animation.');
             return; // Jangan animasi jika gambar tidak terlihat
         }

        const imgRect = itemImage.getBoundingClientRect();
        const cartRect = cartTargetElement.getBoundingClientRect();

        if (imgRect.width === 0 || imgRect.height === 0 || cartRect.width === 0 || cartRect.height === 0) {
             // console.warn('Source or target element has zero dimensions, skipping animation.');
             return;
        }

        const flyingElement = itemImage.cloneNode(true);
        flyingElement.classList.add('flying-item-animation'); // Terapkan class CSS animasi

        flyingElement.style.position = 'fixed';
        flyingElement.style.left = `${imgRect.left}px`;
        flyingElement.style.top = `${imgRect.top}px`;
        flyingElement.style.width = `${imgRect.width}px`;
        flyingElement.style.height = `${imgRect.height}px`;
        flyingElement.style.opacity = '0.8';
        flyingElement.style.borderRadius = '8px';

        document.body.appendChild(flyingElement);

        const targetX = cartRect.left + (cartRect.width / 2);
        const targetY = cartRect.top + (cartRect.height / 2);

        // Minta browser untuk memulai animasi di frame berikutnya
        requestAnimationFrame(() => {
             requestAnimationFrame(() => { // Double requestAnimationFrame
                // Pindah ke target, kecilkan, dan hilangkan
                flyingElement.style.transform = `translate(${targetX - imgRect.left - (imgRect.width/2)}px, ${targetY - imgRect.top - (imgRect.height/2)}px) scale(0.1)`;
                flyingElement.style.opacity = '0';
             });
        });

        // Hapus elemen setelah animasi selesai
        setTimeout(() => {
            flyingElement.remove();
        }, 600); // Durasi = durasi transisi CSS
    }

    // --- Logika Pengiriman Pesanan ---
    async function sendOrder() {
        const currentTableIdInput = document.getElementById('table-id');
        const currentPlaceOrderBtn = document.getElementById('place-order-btn');

        if (!currentTableIdInput || !currentTableIdInput.value || currentTableIdInput.value === '0') {
             showOrderMessage('Error: ID Meja tidak valid atau tidak ditemukan.', 'error');
             return;
        }
        const currentCart = loadCart();
        if (Object.keys(currentCart).length === 0) {
             showOrderMessage('Keranjang Anda kosong. Silakan pilih item menu.', 'info');
             return;
        }
        if (!currentPlaceOrderBtn) return;

        currentPlaceOrderBtn.disabled = true;
        currentPlaceOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        showOrderMessage('Mengirim pesanan Anda...', 'info');

        const orderData = {
            table_id: parseInt(currentTableIdInput.value),
            items: Object.values(currentCart).map(item => ({
                menu_item_id: parseInt(item.id), quantity: item.quantity, notes: item.notes ?? ''
            })),
        };

        try {
            const baseUrl = typeof APP_BASE_URL !== 'undefined' ? APP_BASE_URL : '';
            const fetchUrl = `${baseUrl}/order/place`;

            const response = await fetch(fetchUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(orderData)
            });

            let result;
            const responseText = await response.text();
            try { result = JSON.parse(responseText); }
            catch(jsonError) { throw new Error(`Server response error (${response.status}): ${responseText.substring(0, 150)}...`); }

            if (!response.ok) { throw new Error(result?.message || `Gagal mengirim pesanan (Error ${response.status})`); }

            if (result.success) {
                showOrderMessage('Pesanan berhasil dibuat! Mengalihkan ke halaman status...', 'success');
                cart = {};
                localStorage.removeItem('staywithme_cart');
                updateCartUI();
                toggleCart(false);
                setTimeout(() => {
                    if (result.redirect_url) { window.location.href = result.redirect_url; }
                    else { showOrderMessage('Pesanan berhasil, tapi gagal mengalihkan.', 'warning'); }
                }, 1500);
            } else { throw new Error(result.message || 'Gagal memproses pesanan di server.'); }

        } catch (error) {
            console.error('Error placing order:', error);
            showOrderMessage(`Gagal: ${error.message}`, 'error');
            currentPlaceOrderBtn.disabled = false;
            currentPlaceOrderBtn.innerHTML = 'Pesan Sekarang';
        }
    }

    function showOrderMessage(message, type = 'info') {
        const currentOrderMessageElement = document.getElementById('order-message');
        if (currentOrderMessageElement) {
            currentOrderMessageElement.textContent = message;
            currentOrderMessageElement.className = 'mt-3 text-sm text-center font-medium';
            const colorClasses = { success: 'text-green-500', error: 'text-red-500', warning: 'text-yellow-500', info: 'text-blue-400' };
            currentOrderMessageElement.classList.add(colorClasses[type] || colorClasses['info']);
        }
     }

    // --- Pasang Event Listener Awal ---
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', (event) => {
            const btn = event.currentTarget;
            const item = {
                id: btn.dataset.id, name: btn.dataset.name, price: parseFloat(btn.dataset.price)
            };
             if (item.id && item.name && !isNaN(item.price)) {
                animateAddToCart(btn);
                addToCart(item);
             } else {
                 console.error('Data item tidak valid pada tombol:', btn.dataset);
                 alert('Maaf, terjadi kesalahan saat menambahkan item ini.');
             }
        });
    });

    const initialCartHeader = document.getElementById('cart-header');
    if (initialCartHeader) {
        initialCartHeader.addEventListener('click', () => toggleCart(null));
    }

    const initialPlaceOrderBtn = document.getElementById('place-order-btn');
    if (initialPlaceOrderBtn) {
        initialPlaceOrderBtn.addEventListener('click', sendOrder);
    }

    // --- Inisialisasi Awal ---
    updateCartUI();
    // Atur posisi awal keranjang
    const initialCartSection = document.getElementById('cart-section');
    const initialCartHeaderForHeight = document.getElementById('cart-header');
    if (initialCartSection && initialCartHeaderForHeight) {
        // Gunakan timeout kecil untuk memastikan layout sudah stabil sebelum menghitung tinggi header
        setTimeout(() => {
             if (!isCartOpen) {
                const headerHeight = initialCartHeaderForHeight.offsetHeight || 52; // Gunakan fallback height
                initialCartSection.style.transform = `translateY(calc(100% - ${headerHeight}px))`;
             }
        }, 150);
    }

}); // End DOMContentLoaded