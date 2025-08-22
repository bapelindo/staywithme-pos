document.addEventListener('DOMContentLoaded', function() {
    // Fungsi konfirmasi untuk form hapus (tidak berubah)
    const deleteForms = document.querySelectorAll('.delete-confirm-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const message = form.dataset.confirmMessage || 'Apakah Anda yakin ingin menghapus item ini?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Fungsi untuk toggle ketersediaan item menu (DIPERBAIKI)
    const availabilityButtons = document.querySelectorAll('.toggle-availability-btn');
    availabilityButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const itemId = this.dataset.itemId;
            const parentCell = this.parentElement; // Ambil parent container (<td>)
            const messageEl = parentCell.querySelector('.temp-message');
            
            // Sembunyikan tombol menggunakan kelas 'hidden' dari Tailwind CSS
            this.classList.add('hidden');

            // Tampilkan pesan loading
            messageEl.textContent = 'Memperbarui...';
            messageEl.className = 'temp-message text-xs ml-2 text-slate-500';

            try {
                const response = await fetch(`${APP_BASE_URL}/admin/menu/toggle_availability/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Respons jaringan tidak baik: ' + response.statusText);
                }

                // Cek jika response kosong sebelum parsing JSON
                const responseText = await response.text();
                if (!responseText) {
                    throw new Error('Respons dari server kosong.');
                }
                const result = JSON.parse(responseText);

                if (result.success) {
                    // Update tampilan tombol berdasarkan status baru dari server
                    const indicator = this.querySelector('.availability-indicator');
                    const text = this.querySelector('.availability-text');

                    if (result.is_available) {
                        this.classList.remove('border-red-400', 'bg-red-50', 'text-red-700', 'hover:bg-red-100');
                        this.classList.add('border-green-400', 'bg-green-50', 'text-green-700', 'hover:bg-green-100');
                        indicator.classList.remove('bg-red-500');
                        indicator.classList.add('bg-green-500');
                        text.textContent = 'Tersedia';
                    } else {
                        this.classList.remove('border-green-400', 'bg-green-50', 'text-green-700', 'hover:bg-green-100');
                        this.classList.add('border-red-400', 'bg-red-50', 'text-red-700', 'hover:bg-red-100');
                        indicator.classList.remove('bg-green-500');
                        indicator.classList.add('bg-red-500');
                        text.textContent = 'Habis';
                    }

                    // Tampilkan pesan sukses
                    messageEl.textContent = 'Status diperbarui.';
                    messageEl.className = 'temp-message text-xs ml-2 text-green-600';

                } else {
                    throw new Error(result.message || 'Gagal memperbarui status.');
                }

            } catch (error) {
                console.error('Error:', error);
                messageEl.textContent = 'Error! Coba lagi.';
                messageEl.className = 'temp-message text-xs ml-2 text-red-600';
            } finally {
                // Setelah beberapa detik, hilangkan pesan DAN tampilkan kembali tombolnya
                setTimeout(() => {
                    messageEl.textContent = ''; // Hapus teks pesan
                    this.classList.remove('hidden'); // Tampilkan kembali tombol dengan menghapus kelas 'hidden'
                }, 2000); // 2 detik
            }
        });
    });
});