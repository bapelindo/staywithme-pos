// File: public/assets/js/customer-menu.js (Cart does NOT auto-open on add)

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

    // --- State Keranjang ---
    let cart = loadCart();
    let isCartOpen = false;

    // --- Fungsi Keranjang ---
    function loadCart() {
        const storedCart = localStorage.getItem('staywithme_cart');
        return storedCart ? JSON.parse(storedCart) : {};
    }

    function saveCart() {
        localStorage.setItem('staywithme_cart', JSON.stringify(cart));
    }

    function addToCart(item) {
        const itemId = item.id;
        if (cart[itemId]) {
            cart[itemId].quantity += 1;
        } else {
            cart[itemId] = { ...item, quantity: 1, notes: '' };
        }
        saveCart();
        updateCartUI();
        // We still want feedback, so flash the header
        flashCartHeader();
    }

    function updateQuantity(itemId, change) {
        if (cart[itemId]) {
            cart[itemId].quantity += change;
            if (cart[itemId].quantity <= 0) {
                delete cart[itemId];
            }
            saveCart();
            updateCartUI();
        }
    }

     function updateItemNotes(itemId, notes) {
         if (cart[itemId]) {
            cart[itemId].notes = notes.trim();
            saveCart();
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
        return new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR',
            minimumFractionDigits: 0, maximumFractionDigits: 0
        }).format(amount);
    }

    // --- Update Tampilan Keranjang ---
    function updateCartUI() {
        if (!cartItemsContainer || !cartTotalElement || !placeOrderBtn || !cartEmptyMessage || !cartItemCountElement) {
            console.error("Cart elements missing in updateCartUI!"); return;
        }

        cartItemsContainer.innerHTML = ''; // Clear old items
        let total = calculateTotal();
        let itemCount = Object.keys(cart).length;

        cartItemCountElement.textContent = itemCount;

        if (itemCount === 0) {
            cartEmptyMessage.style.display = 'block';
            placeOrderBtn.disabled = true;
             // Close the cart if it becomes empty and is open
             if (isCartOpen) {
                 toggleCart(false);
             }
        } else {
            cartEmptyMessage.style.display = 'none';
            placeOrderBtn.disabled = false;

            Object.values(cart).forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.className = 'cart-item py-2 border-b border-slate-100 flex items-start';
                itemElement.innerHTML = `
                    <div class="flex-grow pr-2">
                        <p class="text-sm font-medium text-slate-800">${SanitizeHelper.html(item.name)}</p>
                        <p class="text-xs text-indigo-600">${formatCurrency(item.price)}</p>
                        <input type="text" placeholder="Catatan item..." value="${SanitizeHelper.html(item.notes ?? '')}" data-item-id="${item.id}" class="item-notes-input mt-1 text-xs p-1 border rounded w-full" maxlength="100">
                    </div>
                    <div class="flex items-center space-x-1.5 flex-shrink-0">
                        <button class="quantity-change bg-slate-200 text-slate-600 hover:bg-slate-300 rounded-full w-5 h-5 text-sm leading-none flex items-center justify-center" data-item-id="${item.id}" data-change="-1">-</button>
                        <span class="text-sm font-medium w-5 text-center">${item.quantity}</span>
                        <button class="quantity-change bg-slate-200 text-slate-600 hover:bg-slate-300 rounded-full w-5 h-5 text-sm leading-none flex items-center justify-center" data-item-id="${item.id}" data-change="1">+</button>
                    </div>
                `;
                cartItemsContainer.appendChild(itemElement);
            });
        }

        cartTotalElement.textContent = formatCurrency(total);
        addCartItemEventListeners(); // Re-attach listeners
    }

    // --- Add Listeners to Dynamic Cart Elements ---
    function addCartItemEventListeners() {
        // Use event delegation on the container is generally better, but re-attaching is simpler here
        cartItemsContainer.querySelectorAll('.quantity-change').forEach(button => {
             button.replaceWith(button.cloneNode(true));
        });
         cartItemsContainer.querySelectorAll('.quantity-change').forEach(button => {
            button.addEventListener('click', (e) => {
                const btn = e.currentTarget;
                const itemId = btn.dataset.itemId;
                const change = parseInt(btn.dataset.change);
                updateQuantity(itemId, change);
            });
        });

        cartItemsContainer.querySelectorAll('.item-notes-input').forEach(input => {
             input.replaceWith(input.cloneNode(true));
        });
         cartItemsContainer.querySelectorAll('.item-notes-input').forEach(input => {
             input.addEventListener('change', (e) => {
                 const inputEl = e.currentTarget;
                 const itemId = inputEl.dataset.itemId;
                 updateItemNotes(itemId, inputEl.value);
             });
             input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault(); input.blur();
                }
             });
         });
    }

    // --- Cart Visibility and Feedback ---
    function toggleCart(forceState = null) {
        if (!cartHeader || !cartContent || !cartSection || !cartToggleIcon) return;
        const targetOpenState = (forceState === null) ? !isCartOpen : forceState;
        if (targetOpenState === isCartOpen) return; // No change needed

        isCartOpen = targetOpenState;
        if (isCartOpen) {
            cartSection.style.transform = 'translateY(0)';
            cartContent.style.maxHeight = '400px';
            cartToggleIcon.style.transform = 'rotate(0deg)';
        } else {
            cartContent.style.maxHeight = '0px';
            cartToggleIcon.style.transform = 'rotate(180deg)';
            setTimeout(() => {
                 if (!isCartOpen) {
                    cartSection.style.transform = `translateY(calc(100% - ${cartHeader.offsetHeight}px))`;
                 }
            }, 300);
        }
    }

    function flashCartHeader() {
        if (cartHeader) {
            cartHeader.classList.remove('bg-indigo-50', 'hover:bg-indigo-100');
            cartHeader.classList.add('bg-indigo-200', 'transition-colors', 'duration-200');
            setTimeout(() => {
                cartHeader.classList.remove('bg-indigo-200');
                cartHeader.classList.add('bg-indigo-50', 'hover:bg-indigo-100');
            }, 300);
        }
    }

    // --- Event Listeners Setup ---
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', () => {
            const item = {
                id: button.dataset.id,
                name: button.dataset.name,
                price: parseFloat(button.dataset.price)
            };
            if (!isNaN(item.price)) {
                addToCart(item); // This now only adds item and flashes header
                                 // It NO LONGER forces the cart open
            } else {
                console.error('Invalid item price:', button.dataset.price);
            }
        });
    });

    if (cartHeader) {
        // Only the header click toggles the cart open/close state
        cartHeader.addEventListener('click', () => toggleCart(null));
    }

    // --- Send Order Logic ---
    async function sendOrder() { // (Keep existing sendOrder function as is)
        if (!tableIdInput || !tableIdInput.value) {
            showOrderMessage('Error: ID Meja tidak ditemukan.', 'error'); return;
        }
        if (Object.keys(cart).length === 0) {
            showOrderMessage('Keranjang Anda kosong.', 'info'); return;
        }

        placeOrderBtn.disabled = true;
        placeOrderBtn.textContent = 'Memproses...';
        showOrderMessage('');

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
            const fetchUrl = `${baseUrl}/order/place`;
            console.log("Sending order to:", fetchUrl);

            const response = await fetch(fetchUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(orderData)
            });

            if (!response.ok) {
                 const errorText = await response.text();
                 console.error('Server response error text:', errorText);
                 throw new Error(`Server error: ${response.status} ${response.statusText}. Response: ${errorText.substring(0, 100)}...`);
            }

            const result = await response.json();

            if (result.success) {
                showOrderMessage('Pesanan berhasil! Mengalihkan...', 'success');
                cart = {}; saveCart(); updateCartUI();
                setTimeout(() => {
                    if (result.redirect_url) { window.location.href = result.redirect_url; }
                    else { showOrderMessage('Pesanan berhasil, tapi URL status tidak ditemukan.', 'warning'); placeOrderBtn.textContent = 'Pesan Sekarang'; placeOrderBtn.disabled = false; }
                }, 1500);
            } else { throw new Error(result.message || 'Gagal mengirim pesanan.'); }

        } catch (error) {
            console.error('Error placing order:', error);
            let displayError = 'Terjadi kesalahan saat mengirim pesanan.';
             if (error.message && error.message.includes('Server error:')) { displayError = `Gagal mengirim pesanan. Server: ${error.message.split('.')[1] || 'Unknown Error'}`; }
             else if (error.message) { displayError = `Error: ${error.message}`; }
            showOrderMessage(displayError, 'error');
            placeOrderBtn.disabled = false; placeOrderBtn.textContent = 'Pesan Sekarang';
        }
    }

    function showOrderMessage(message, type = 'info') {
        if (orderMessageElement) {
            orderMessageElement.textContent = message;
            orderMessageElement.className = 'mt-3 text-sm text-center font-medium';
            const colorClasses = { success: 'text-green-600', error: 'text-red-600', warning: 'text-yellow-600', info: 'text-slate-500' };
            orderMessageElement.classList.add(colorClasses[type] || colorClasses['info']);
        }
    }

    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', sendOrder);
    }

    // --- Initialization ---
    updateCartUI();

    if (cartSection && cartHeader) {
        setTimeout(() => {
             if (!isCartOpen) {
                cartSection.style.transform = `translateY(calc(100% - ${cartHeader.offsetHeight}px))`;
             }
        }, 100);
    }

    // Helper for sanitizing HTML
    const SanitizeHelper = {
        html: (text) => { const temp = document.createElement('div'); temp.textContent = text ?? ''; return temp.innerHTML; }
    };

}); // End DOMContentLoaded