// File: public/js/customer-menu.js (Versi Final - Bersih)

// Helper Sanitasi Sederhana (Definisikan di Awal!)
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

    if (!cartItemsContainer || !cartTotalElement || !placeOrderBtn || !cartItemCountElement) {
        console.error("Cart UI elements missing! Cart functionality may be limited.");
    }

    // --- State Keranjang ---
    let cart = loadCart();
    let isCartOpen = false;

    // --- Fungsi Keranjang ---
    function loadCart() {
        const storedCart = localStorage.getItem('staywithme_cart');
        if (storedCart) {
            try {
                const parsedCart = JSON.parse(storedCart);
                if (typeof parsedCart === 'object' && parsedCart !== null) {
                    Object.keys(parsedCart).forEach(key => {
                        if (typeof parsedCart[key] !== 'object' || parsedCart[key] === null || !parsedCart[key].id || !parsedCart[key].name || isNaN(parseFloat(parsedCart[key].price)) || isNaN(parseInt(parsedCart[key].quantity))) {
                            delete parsedCart[key];
                        }
                        if (parsedCart[key] && typeof parsedCart[key].notes === 'undefined') {
                             parsedCart[key].notes = '';
                         }
                    });
                    return parsedCart;
                } else { throw new Error("Stored cart data is not a valid object."); }
            } catch (e) {
                console.error("Error parsing cart from localStorage or invalid data found:", e);
                localStorage.removeItem('staywithme_cart');
                return {};
            }
        }
        return {};
    }

    function saveCart() {
        try {
            localStorage.setItem('staywithme_cart', JSON.stringify(cart));
        } catch (e) {
            console.error("Error saving cart to localStorage:", e);
        }
    }

    function addToCart(item) {
        const itemId = String(item.id);
        if (!itemId) { return; }
        if (cart[itemId]) {
            cart[itemId].quantity += 1;
        } else {
            cart[itemId] = {
                id: itemId, name: item.name ?? 'Item', price: parseFloat(item.price ?? 0),
                quantity: 1, notes: ''
             };
        }
        saveCart();
        updateCartUI();
        flashCartHeader();
    }

    function updateQuantity(itemId, change) {
         const strItemId = String(itemId);
        if (!strItemId || !cart[strItemId]) return;
        cart[strItemId].quantity += change;
        if (cart[strItemId].quantity <= 0) { delete cart[strItemId]; }
        saveCart();
        updateCartUI();
    }

     function updateItemNotes(itemId, notes) {
        const strItemId = String(itemId);
        if (!strItemId || !cart[strItemId]) return;
        cart[strItemId].notes = notes.trim();
        saveCart();
     }

    function calculateTotal() {
        let total = 0;
        for (const itemId in cart) {
            const price = parseFloat(cart[itemId].price ?? 0);
            const quantity = parseInt(cart[itemId].quantity ?? 0);
            if (!isNaN(price) && !isNaN(quantity)) { total += price * quantity; }
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

    // --- Update Tampilan Keranjang ---
    function updateCartUI() {
        const currentCartItemsContainer = document.getElementById('cart-items');
        const currentCartTotalElement = document.getElementById('cart-total');
        const currentPlaceOrderBtn = document.getElementById('place-order-btn');
        const currentCartEmptyMessage = currentCartItemsContainer?.querySelector('.cart-empty-message');
        const currentCartItemCountElement = document.getElementById('cart-item-count');

        if (!currentCartItemsContainer || !currentCartTotalElement || !currentPlaceOrderBtn || !currentCartItemCountElement) { return; }

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
                 if (!item || !item.id || !item.name || isNaN(parseFloat(item.price)) || isNaN(parseInt(item.quantity))) { return; }

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
                        <button class="quantity-change bg-bg-dark-tertiary text-accent-primary hover:bg-bg-dark-secondary rounded-md w-6 h-6 text-lg leading-none flex items-center justify-center transition-colors" data-item-id="${item.id}" data-change="-1">-</button>
                        <span class="text-md font-semibold text-white w-6 text-center">${item.quantity}</span>
                        <button class="quantity-change bg-bg-dark-tertiary text-accent-primary hover:bg-bg-dark-secondary rounded-md w-6 h-6 text-lg leading-none flex items-center justify-center transition-colors" data-item-id="${item.id}" data-change="1">+</button>
                    </div>
                     <button class="cart-item-remove text-red-500 hover:text-red-400 ml-2 flex-shrink-0 text-xl font-bold" data-item-id="${item.id}" title="Hapus item">&times;</button>
                `;
                currentCartItemsContainer.appendChild(itemElement);
            });
        }

        currentCartTotalElement.textContent = formatCurrency(total);
        addCartItemEventListeners();
    }

    // --- Add Listeners to Dynamic Cart Elements (Event Delegation) ---
    function addCartItemEventListeners() {
        const container = document.getElementById('cart-items');
        if (!container) return;

         container.replaceWith(container.cloneNode(true));
         const newContainer = document.getElementById('cart-items');
         if (!newContainer) return;

        newContainer.addEventListener('click', (e) => {
            const target = e.target;
            const button = target.closest('button');
            if (!button) return;

            if (button.classList.contains('quantity-change')) {
                const itemId = button.dataset.itemId;
                const change = parseInt(button.dataset.change);
                if (itemId && !isNaN(change)) { updateQuantity(itemId, change); }
            }

            if (button.classList.contains('cart-item-remove')) {
                const itemId = button.dataset.itemId;
                const itemInCart = cart[String(itemId)];
                 if (itemId && itemInCart && confirm(`Hapus "${SanitizeHelper.html(itemInCart.name)}" dari keranjang?`)) {
                    delete cart[String(itemId)];
                    saveCart();
                    updateCartUI();
                 }
            }
        });

        newContainer.addEventListener('change', (e) => {
            if (e.target.classList.contains('item-notes-input')) {
                 const inputEl = e.target;
                 const itemId = inputEl.dataset.itemId;
                 if (itemId) { updateItemNotes(itemId, inputEl.value); }
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
    }

    // --- Cart Visibility and Feedback ---
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
        currentCartContent.style.maxHeight = isCartOpen ? '60vh' : '0px'; // Tinggi cart
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

    // --- Event Listeners Setup ---
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', () => {
            const item = {
                id: button.dataset.id, name: button.dataset.name, price: parseFloat(button.dataset.price)
            };
             if (item.id && item.name && !isNaN(item.price)) { addToCart(item); }
             else { console.error('Invalid item data on button:', button.dataset); }
        });
    });

    const initialCartHeader = document.getElementById('cart-header');
    if (initialCartHeader) {
        initialCartHeader.addEventListener('click', () => toggleCart(null));
    }

    // --- Send Order Logic ---
    async function sendOrder() {
        const currentTableIdInput = document.getElementById('table-id');
        const currentPlaceOrderBtn = document.getElementById('place-order-btn');
        if (!currentTableIdInput || !currentTableIdInput.value) { showOrderMessage('Error: ID Meja tidak ditemukan.', 'error'); return; }
        const currentCart = loadCart();
        if (Object.keys(currentCart).length === 0) { showOrderMessage('Keranjang Anda kosong.', 'info'); return; }
        if (!currentPlaceOrderBtn) { return; }

        currentPlaceOrderBtn.disabled = true;
        currentPlaceOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        showOrderMessage('Mengirim pesanan...', 'info');

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

            if (!response.ok) { throw new Error(result?.message || `Server error: ${response.status}`); }

            if (result.success) {
                showOrderMessage('Pesanan berhasil! Mengalihkan...', 'success');
                cart = {};
                localStorage.removeItem('staywithme_cart');
                updateCartUI();
                setTimeout(() => {
                    if (result.redirect_url) { window.location.href = result.redirect_url; }
                    else { showOrderMessage('Pesanan berhasil, tapi gagal mengalihkan.', 'warning'); }
                }, 1500);
            } else { throw new Error(result.message || 'Gagal mengirim pesanan.'); }

        } catch (error) {
            console.error('Error placing order:', error);
            showOrderMessage(`Error: ${error.message}`, 'error');
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

    const initialPlaceOrderBtn = document.getElementById('place-order-btn');
    if (initialPlaceOrderBtn) {
        initialPlaceOrderBtn.addEventListener('click', sendOrder);
    }

    // --- Initialization ---
    updateCartUI();

    const initialCartSection = document.getElementById('cart-section');
    const initialCartHeaderForHeight = document.getElementById('cart-header');
    if (initialCartSection && initialCartHeaderForHeight) {
        setTimeout(() => {
             if (!isCartOpen) {
                const headerHeight = initialCartHeaderForHeight.offsetHeight || 52;
                initialCartSection.style.transform = `translateY(calc(100% - ${headerHeight}px))`;
             }
        }, 150);
    }

}); // End DOMContentLoaded