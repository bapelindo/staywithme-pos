<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\AuthHelper; // Untuk info user & logout
use App\Helpers\SessionHelper;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? SanitizeHelper::html($pageTitle) . ' - ' : '' ?>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', sans-serif; }
        /* Styling tambahan */
        [x-cloak] { display: none; } /* Untuk Alpine.js jika dipakai */
        .sidebar-link.active { background-color: #4f46e5; color: white; } /* Indigo-600 */
        .sidebar-link:not(.active):hover { background-color: #eef2ff; color: #3730a3; } /* Indigo-50 / Indigo-800 */

        /* Styling untuk Flash Message Overlap */
        .flash-message-overlap {
            position: fixed;
            top: 1.25rem; /* Sekitar 20px */
            right: 1.25rem; /* Sekitar 20px */
            z-index: 1000; /* Pastikan di atas elemen lain */
            max-width: 24rem; /* Lebar maks 384px */
            width: 90%; /* Lebar relatif untuk layar kecil */
            pointer-events: none; /* Biarkan klik "menembus" area kosong */
        }
        .flash-message-overlap > div { /* Target div di dalamnya */
             pointer-events: auto; /* Pesan flash bisa diklik jika perlu */
             margin-bottom: 0.75rem; /* Jarak antar pesan jika muncul >1 */
             box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
    </style>
    <script>
        var APP_BASE_URL = "<?= rtrim(UrlHelper::baseUrl(), '/') ?>"; // Gunakan rtrim()
    </script>
</head>
<body class="bg-slate-100">

    <div class="flex h-screen bg-slate-100 overflow-hidden">
        <div id="sidebar-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

        <aside id="admin-sidebar" class="fixed inset-y-0 left-0 bg-white shadow-lg w-64 z-40 transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
            <div class="flex items-center justify-between p-4 border-b h-16">
                <a href="<?= UrlHelper::baseUrl('/admin/dashboard') ?>" class="text-2xl font-bold text-indigo-600">Admin POS</a>
                <button id="sidebar-close-btn" class="text-slate-500 hover:text-slate-700 lg:hidden">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <nav class="py-4 px-2 flex-grow overflow-y-auto">
                <ul class="space-y-1">
                    <?php
                        // Helper untuk menentukan link aktif (bisa dibuat di UrlHelper atau Controller)
                        $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
                        $baseAdminPath = trim(parse_url(UrlHelper::baseUrl('/admin'), PHP_URL_PATH), '/');
                        $relativeAdminPath = substr($currentPath, strlen($baseAdminPath));

                        function isAdminLinkActive($linkPath, $currentRelativePath) {
                            // Cocokkan awal path
                            if ($linkPath === '/' && $currentRelativePath === '') return true; // Dashboard
                            if ($linkPath !== '/' && str_starts_with($currentRelativePath, ltrim($linkPath, '/'))) return true;
                            return false;
                        }
                    ?>
                    <li><a href="<?= UrlHelper::baseUrl('/admin/dashboard') ?>" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= isAdminLinkActive('/', $relativeAdminPath) ? 'active' : '' ?>">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        Dashboard
                    </a></li>
                     <li><a href="<?= UrlHelper::baseUrl('/admin/kds') ?>" target="_blank" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" /></svg>
                        Kitchen Display (KDS)
                    </a></li>
                    <li><a href="<?= UrlHelper::baseUrl('/admin/orders') ?>" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= isAdminLinkActive('/orders', $relativeAdminPath) ? 'active' : '' ?>">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.125 1.125 0 010 2.25H5.625a1.125 1.125 0 010-2.25z" /></svg>
                        Pesanan
                        <span id="new-order-count-badge" class="ml-auto bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full hidden">0</span>
                    </a></li>
                    <li><a href="<?= UrlHelper::baseUrl('/admin/menu') ?>" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= (isAdminLinkActive('/menu', $relativeAdminPath) || isAdminLinkActive('/categories', $relativeAdminPath)) ? 'active' : '' ?>">
                         <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18c-2.305 0-4.408.867-6 2.292m0-14.25v14.25" /></svg>
                         Menu & Kategori
                    </a></li>
                     <li><a href="<?= UrlHelper::baseUrl('/admin/tables') ?>" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= isAdminLinkActive('/tables', $relativeAdminPath) ? 'active' : '' ?>">
                         <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-8.25zM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-2.25z" /></svg>
                         Meja
                    </a></li>
                    <?php if (AuthHelper::getUserRole() === 'admin'): // Hanya admin bisa kelola user & laporan? ?>
                        <li><a href="<?= UrlHelper::baseUrl('/admin/users') ?>" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= isAdminLinkActive('/users', $relativeAdminPath) ? 'active' : '' ?>">
                            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                            Pengguna
                        </a></li>
                        <li><a href="<?= UrlHelper::baseUrl('/admin/reports') ?>" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= isAdminLinkActive('/reports', $relativeAdminPath) ? 'active' : '' ?>">
                            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1.125-1.5M12 16.5v5.25m0 0l1.125-1.5m-1.125 1.5l-1.125-1.5" /></svg>
                            Laporan
                        </a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="p-4 border-t mt-auto">
                 <a href="<?= UrlHelper::baseUrl('/admin/logout') ?>" class="flex items-center w-full px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:bg-red-50 transition duration-150">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                    Logout
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                 <button id="sidebar-open-btn" class="text-slate-500 hover:text-slate-700 lg:hidden">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                 </button>
                 <h1 class="text-lg font-semibold text-slate-700 hidden md:block">
                     <?= isset($pageTitle) ? SanitizeHelper::html($pageTitle) : 'Admin Dashboard' ?>
                 </h1>
                 <div class="text-sm text-slate-600">
                     Halo, <span class="font-medium"><?= SanitizeHelper::html(AuthHelper::getUserName() ?? 'User') ?></span>!
                     (<span class="text-xs"><?= SanitizeHelper::html(ucfirst(AuthHelper::getUserRole() ?? '')) ?></span>)
                 </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-100 p-4 sm:p-6 lg:p-8">
                 <?php
                    // Tampilkan pesan flash admin
                    SessionHelper::displayAllFlashMessages('p-4 mb-6 rounded-md text-sm border', [
                         'success' => 'bg-green-100 border-green-300 text-green-800',
                         'error'   => 'bg-red-100 border-red-300 text-red-800',
                         'info'    => 'bg-blue-100 border-blue-300 text-blue-800',
                         'warning' => 'bg-yellow-100 border-yellow-300 text-yellow-800'
                    ]);

                    // =========================================
                    // == KONTEN VIEW SPESIFIK DIMUAT DI SINI ==
                    // =========================================
                    if (isset($viewPath) && file_exists($viewPath)) {
                        require $viewPath;
                    } else {
                        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">Error: Admin view content could not be loaded.</div>';
                    }
                    // =========================================
                 ?>
            </main>
        </div>
    </div>

    <script src="<?= UrlHelper::baseUrl('js/admin-main.js') ?>" defer></script>
    <script src="<?= UrlHelper::baseUrl('js/admin-notifications.js') ?>" defer></script>
    </body>
</html>