// File: public/js/admin-main.js (Revisi Konfirmasi Ganda)

document.addEventListener('DOMContentLoaded', () => {

    // === Sidebar Toggle (Tetap Sama) ===
    const sidebar = document.getElementById('admin-sidebar');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');
    const sidebarOpenBtn = document.getElementById('sidebar-open-btn');
    const sidebarCloseBtn = document.getElementById('sidebar-close-btn');

    function openSidebar() {
        if (sidebar && sidebarBackdrop) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            sidebarBackdrop.classList.remove('hidden');
            sidebarBackdrop.classList.add('block');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeSidebar() {
         if (sidebar && sidebarBackdrop) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
            sidebarBackdrop.classList.remove('block');
            sidebarBackdrop.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    sidebarOpenBtn?.addEventListener('click', openSidebar);
    sidebarCloseBtn?.addEventListener('click', closeSidebar);
    sidebarBackdrop?.addEventListener('click', closeSidebar);


    // === Konfirmasi Submit Form (Revisi - Pencegahan Listener Ganda) ===
    function attachConfirmationListener(form) {
        // Cek apakah listener sudah terpasang sebelumnya
        if (form.dataset.confirmationAttached === 'true') {
            // console.log('Listener konfirmasi sudah ada untuk form:', form); // Debugging
            return; // Jangan pasang lagi
        }

        form.addEventListener('submit', function(event) {
            // Simpan referensi form asli
            const currentForm = this;

            // Cegah submit default agar bisa tampilkan confirm() dulu
            event.preventDefault();

            const message = currentForm.dataset.confirmMessage || 'Apakah Anda yakin? Tindakan ini tidak dapat dibatalkan.';
            const confirmed = window.confirm(message); // Tampilkan dialog konfirmasi

            if (confirmed) {
                // Jika pengguna klik OK, submit form secara programatik
                // Penting: Panggil form.submit() asli, bukan trigger event lagi
                currentForm.submit();
            }
            // Jika pengguna klik Cancel, tidak terjadi apa-apa karena event.preventDefault()
        });

        // Tandai form bahwa listener sudah terpasang
        form.dataset.confirmationAttached = 'true';
        // console.log('Listener konfirmasi berhasil dipasang untuk form:', form); // Debugging
    }

    // Terapkan listener ke semua form yang relevan saat halaman dimuat
    document.querySelectorAll('.delete-confirm-form').forEach(form => {
        attachConfirmationListener(form);
    });

    // (Opsional) Jika Anda memuat form baru secara dinamis (misal via AJAX),
    // Anda perlu memanggil `attachConfirmationListener(newFormElement)`
    // setelah form baru ditambahkan ke DOM. Cara alternatif adalah
    // menggunakan event delegation pada elemen parent yang statis.


    // === Inisialisasi lain ===
    console.log('Admin Main JS loaded.');

});