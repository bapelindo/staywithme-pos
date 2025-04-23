// File: public/assets/js/admin-menu.js

document.addEventListener('DOMContentLoaded', () => {
    const menuTable = document.querySelector('.menu-items-table'); // Target tabel atau container item menu

    if (!menuTable) {
        // Berhenti jika tidak di halaman yang relevan
        return;
    }

    menuTable.addEventListener('click', async (event) => {
        // Target tombol/switch toggle saja
        const toggleButton = event.target.closest('.toggle-availability-btn');
        if (!toggleButton) {
            return; // Klik bukan pada tombol toggle
        }

        const itemId = toggleButton.dataset.itemId;
        const availabilityText = toggleButton.querySelector('.availability-text'); // Span untuk teks
        const availabilityIndicator = toggleButton.querySelector('.availability-indicator'); // Dot indikator

        if (!itemId || !availabilityText || !availabilityIndicator) {
            console.error('Tombol toggle tidak memiliki data-item-id atau elemen teks/indikator.');
            return;
        }

        // Tampilkan status loading
        toggleButton.disabled = true;
        const originalText = availabilityText.textContent;
        availabilityText.textContent = '...';
        toggleButton.classList.add('opacity-70', 'cursor-wait');

        const baseUrl = window.APP_BASE_URL || '';
        try {
            const response = await fetch(`${baseUrl}/admin/menu/toggle_availability/${itemId}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    // Tambahkan header CSRF jika diperlukan
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Update tampilan tombol berdasarkan status baru
                const isAvailable = result.is_available;
                availabilityText.textContent = isAvailable ? 'Tersedia' : 'Habis';
                // Update style tombol/indikator
                availabilityIndicator.classList.toggle('bg-green-500', isAvailable);
                availabilityIndicator.classList.toggle('bg-red-500', !isAvailable);
                // Update style tombol utama jika perlu (misal warna border/bg)
                toggleButton.classList.toggle('border-green-500', isAvailable);
                 toggleButton.classList.toggle('text-green-700', isAvailable);
                 toggleButton.classList.toggle('border-red-500', !isAvailable);
                 toggleButton.classList.toggle('text-red-700', !isAvailable);

                // Tampilkan pesan sukses singkat (opsional)
                 showTempMessage(toggleButton, 'Status diperbarui!', 'success');

            } else {
                throw new Error(result.message || 'Gagal mengubah status.');
            }

        } catch (error) {
            console.error('Error toggling availability:', error);
            availabilityText.textContent = originalText; // Kembalikan teks jika error
            showTempMessage(toggleButton, `Error: ${error.message}`, 'error'); // Tampilkan error
        } finally {
            // Hapus status loading
            toggleButton.disabled = false;
            toggleButton.classList.remove('opacity-70', 'cursor-wait');
        }
    });

    // Fungsi helper untuk pesan sementara dekat tombol
    function showTempMessage(buttonElement, message, type = 'info') {
        let msgElement = buttonElement.nextElementSibling;
        // Buat elemen pesan jika belum ada
        if (!msgElement || !msgElement.classList.contains('temp-message')) {
            msgElement = document.createElement('span');
            msgElement.className = 'temp-message text-xs ml-2 transition-opacity duration-300';
             buttonElement.parentNode.insertBefore(msgElement, buttonElement.nextSibling);
        }

        msgElement.textContent = message;
        if(type === 'success') msgElement.classList.add('text-green-600');
        else if(type === 'error') msgElement.classList.add('text-red-600');
        else msgElement.classList.add('text-gray-500');

        msgElement.classList.remove('opacity-0');
        msgElement.classList.add('opacity-100');

        // Hilangkan pesan setelah beberapa detik
        setTimeout(() => {
            msgElement.classList.remove('opacity-100');
            msgElement.classList.add('opacity-0');
            // Hapus elemen setelah hilang (optional)
             // setTimeout(() => msgElement.remove(), 300);
        }, 2500);
    }

});