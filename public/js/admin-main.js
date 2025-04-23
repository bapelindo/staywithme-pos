// File: public/assets/js/admin-main.js

document.addEventListener('DOMContentLoaded', () => {

    // === Sidebar Toggle (Contoh Implementasi) ===
    const sidebar = document.getElementById('admin-sidebar');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');
    const sidebarOpenBtn = document.getElementById('sidebar-open-btn'); // Tombol burger di header mobile
    const sidebarCloseBtn = document.getElementById('sidebar-close-btn'); // Tombol close di dalam sidebar

    function openSidebar() {
        if (sidebar && sidebarBackdrop) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            sidebarBackdrop.classList.remove('hidden');
            sidebarBackdrop.classList.add('block');
            // Optional: disable scroll on body
            document.body.style.overflow = 'hidden';
        }
    }

    function closeSidebar() {
         if (sidebar && sidebarBackdrop) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
            sidebarBackdrop.classList.remove('block');
            sidebarBackdrop.classList.add('hidden');
             // Optional: enable scroll on body
            document.body.style.overflow = '';
        }
    }

    sidebarOpenBtn?.addEventListener('click', openSidebar);
    sidebarCloseBtn?.addEventListener('click', closeSidebar);
    sidebarBackdrop?.addEventListener('click', closeSidebar);


    // === Konfirmasi Hapus (Contoh Implementasi) ===
    // Menargetkan form yang memiliki class 'delete-confirm-form'
    document.querySelectorAll('.delete-confirm-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Cegah submit form default

            const message = this.dataset.confirmMessage || 'Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.';
            const confirmed = window.confirm(message); // Gunakan konfirmasi browser bawaan

            if (confirmed) {
                // Jika dikonfirmasi, submit form secara programmatic
                this.submit();
            }
            // Jika tidak dikonfirmasi, tidak terjadi apa-apa
        });
    });

    // === Inisialisasi lain (jika ada) ===
    console.log('Admin Main JS loaded.');

});