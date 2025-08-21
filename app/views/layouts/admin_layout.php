<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\AuthHelper;
use App\Helpers\SessionHelper;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? SanitizeHelper::html($pageTitle) . ' - ' : '' ?>Admin Panel</title>
    <link rel="stylesheet" href="<?= \App\Helpers\UrlHelper::baseUrl('css/admin_output.css') ?>">
    <link rel="stylesheet" href="<?= \App\Helpers\UrlHelper::baseUrl('css/all.min.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
        .sidebar-link.active { background-color: #4f46e5; color: white; }
        .sidebar-link:not(.active):hover { background-color: #eef2ff; color: #3730a3; }
        .flash-message-overlap { position: fixed; top: 1.25rem; right: 1.25rem; z-index: 1000; max-width: 24rem; width: 90%; pointer-events: none; }
        .flash-message-overlap > div { pointer-events: auto; margin-bottom: 0.75rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); }
        .sidebar-link i.fa-fw { margin-right: 0.75rem; }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* === Tambahkan Kelas Kustom Untuk Indentasi === */
        .dropdown-indent-1 { padding-left: 0.75rem; /* Ganti nilai sesuai keinginan (contoh: 32px) */ }
        .dropdown-indent-2 { padding-left: 0.5rem; /* Ganti nilai sesuai keinginan (contoh: 24px) */ }
    </style>
    <script> var APP_BASE_URL = "<?= rtrim(UrlHelper::baseUrl(), '/') ?>"; </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100">

    <div class="flex h-screen bg-slate-100 overflow-hidden">
        <div id="sidebar-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

        <aside id="admin-sidebar" class="fixed inset-y-0 left-0 bg-white shadow-lg w-64 z-40 transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col">
            <div class="flex items-center justify-between p-4 border-b h-16 flex-shrink-0">
                <a href="<?= UrlHelper::baseUrl('/admin/dashboard') ?>" class="text-2xl font-bold text-indigo-600">Admin POS</a>
                <button id="sidebar-close-btn" class="text-slate-500 hover:text-slate-700 lg:hidden">
                    <i class="fas fa-times fa-fw"></i>
                </button>
            </div>

            <nav class="py-4 px-2 flex-grow overflow-y-auto no-scrollbar">
                <ul class="space-y-1">
                    <?php
                        $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
                        $baseAdminPath = trim(parse_url(UrlHelper::baseUrl('/admin'), PHP_URL_PATH), '/');
                        $relativeAdminPath = '';
                        if (!empty($baseAdminPath) && str_starts_with($currentPath, $baseAdminPath)) {
                             $relativeAdminPath = substr($currentPath, strlen($baseAdminPath));
                        } elseif ($currentPath === $baseAdminPath) {
                            $relativeAdminPath = '';
                        }

                        function isAdminLinkActive($linkPath, $currentRelativePath) {
                            $normalizedLinkPath = ltrim($linkPath, '/');
                             if ($normalizedLinkPath === '' && $currentRelativePath === '') return true;
                            if ($normalizedLinkPath !== '' && str_starts_with($currentRelativePath, '/' . $normalizedLinkPath)) return true;
                            return false;
                        }

                        function isReportLinkActive($baseReportPath, $currentRelativePath) {
                            if (!is_string($baseReportPath) || !is_string($currentRelativePath)) return false;
                            return str_starts_with($currentRelativePath, ltrim($baseReportPath, '/'));
                        }

                        $isReportActive = false;
                        if (isset($relativeAdminPath) && is_string($relativeAdminPath)) {
                            $isReportActive = isReportLinkActive('/reports', $relativeAdminPath);
                        }
                    ?>
                    <li><a href="<?= UrlHelper::baseUrl('/admin/dashboard') ?>" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= isAdminLinkActive('/', $relativeAdminPath) ? 'active' : '' ?>">
                        <i class="fa-solid fa-gauge-high fa-fw"></i>
                        Dashboard
                    </a></li>
                     <li><a href="<?= UrlHelper::baseUrl('/admin/kds') ?>" target="_blank" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150">
                         <i class="fa-solid fa-desktop fa-fw"></i>
                        Kitchen Display (KDS)
                    </a></li>
                    <li><a href="<?= UrlHelper::baseUrl('/cds') ?>" target="_blank" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150">
                        <i class="fa-solid fa-tv fa-fw"></i>
                        Customer Display (CDS)
                    </a></li>
                    <li><a href="<?= UrlHelper::baseUrl('/admin/orders') ?>" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= isAdminLinkActive('/orders', $relativeAdminPath) ? 'active' : '' ?>">
                        <i class="fa-solid fa-receipt fa-fw"></i>
                        Pesanan
                        <span id="new-order-count-badge" class="ml-auto bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full hidden">0</span>
                    </a></li>
                    <li><a href="<?= UrlHelper::baseUrl('/admin/menu') ?>" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= (isAdminLinkActive('/menu', $relativeAdminPath) || isAdminLinkActive('/categories', $relativeAdminPath)) ? 'active' : '' ?>">
                         <i class="fa-solid fa-utensils fa-fw"></i>
                         Menu & Kategori
                    </a></li>
                     <li><a href="<?= UrlHelper::baseUrl('/admin/tables') ?>" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= isAdminLinkActive('/tables', $relativeAdminPath) ? 'active' : '' ?>">
                         <i class="fa-solid fa-table-cells fa-fw"></i>
                         Meja
                    </a></li>
                    <?php if (AuthHelper::getUserRole() === 'admin'): ?>
                        <li><a href="<?= UrlHelper::baseUrl('/admin/users') ?>" class="sidebar-link flex items-center px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= isAdminLinkActive('/users', $relativeAdminPath) ? 'active' : '' ?>">
                            <i class="fa-solid fa-users fa-fw"></i>
                            Pengguna
                        </a></li>
                        <li x-data="{ open: <?= $isReportActive ? 'true' : 'false' ?> }" class="space-y-1">
                            <button @click="open = !open" class="sidebar-link flex items-center justify-between w-full px-3 py-2.5 rounded-md text-sm font-medium text-slate-700 transition duration-150 <?= $isReportActive ? 'active' : '' ?>">
                                <span class="flex items-center">
                                    <i class="fa-solid fa-chart-line fa-fw"></i>
                                    Laporan
                                </span>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            </button>
                            <ul x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" class="dropdown-indent-1 pr-2 space-y-1">
                                <li x-data="{ openSales: <?= isReportLinkActive('/reports/sales', $relativeAdminPath) ? 'true' : 'false' ?> }" class="space-y-1">
                                    <button @click="openSales = !openSales" class="flex items-center justify-between w-full px-3 py-2 rounded-md text-xs font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition duration-150">
                                        <span>Laporan Penjualan</span>
                                        <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': openSales }" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                    </button>
    
                                    <ul x-show="openSales" x-cloak x-transition class="dropdown-indent-2 space-y-1">
                                        <li><a href="<?= UrlHelper::baseUrl('admin/reports/summary') ?>" class="block px-3 py-1.5 rounded-md text-xs text-slate-500 hover:bg-indigo-50 hover:text-indigo-700 <?= isAdminLinkActive('/reports/sales/summary', $relativeAdminPath) ? 'font-semibold text-indigo-700' : '' ?>">Ringkasan Penjualan</a></li>
                                        <li><a href="<?= UrlHelper::baseUrl('/admin/reports/sales/detail') ?>" class="block px-3 py-1.5 rounded-md text-xs text-slate-500 hover:bg-indigo-50 hover:text-indigo-700 <?= isAdminLinkActive('/reports/sales/detail', $relativeAdminPath) ? 'font-semibold text-indigo-700' : '' ?>">Detail Penjualan</a></li>
                                        <li><a href="<?= UrlHelper::baseUrl('/admin/reports') ?>" class="block px-3 py-1.5 rounded-md text-xs text-slate-500 hover:bg-indigo-50 hover:text-indigo-700 <?= ($relativeAdminPath === '/reports' || $relativeAdminPath === '/reports/') ? 'font-semibold text-indigo-700' : '' ?>">Grafik Penjualan</a></li>
                                    </ul>
                                </li>
                                <li x-data="{ openProduct: <?= isReportLinkActive('/reports/product', $relativeAdminPath) ? 'true' : 'false' ?> }" class="space-y-1">
                                    <button @click="openProduct = !openProduct" class="flex items-center justify-between w-full px-3 py-2 rounded-md text-xs font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition duration-150">
                                        <span>Laporan Produk</span>
                                        <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': openProduct }" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                    </button>
    
                                    <ul x-show="openProduct" x-cloak x-transition class="dropdown-indent-2 space-y-1">
                                        <li><a href="<?= UrlHelper::baseUrl('/admin/reports/product/by-item') ?>" class="block px-3 py-1.5 rounded-md text-xs text-slate-500 hover:bg-indigo-50 hover:text-indigo-700 <?= isAdminLinkActive('/reports/product/by-item', $relativeAdminPath) ? 'font-semibold text-indigo-700' : '' ?>">Penjualan per Produk</a></li>
                                        <li><a href="<?= UrlHelper::baseUrl('/admin/reports/product/by-category') ?>" class="block px-3 py-1.5 rounded-md text-xs text-slate-500 hover:bg-indigo-50 hover:text-indigo-700 <?= isAdminLinkActive('/reports/product/by-category', $relativeAdminPath) ? 'font-semibold text-indigo-700' : '' ?>">Penjualan per Kategori</a></li>
                                    </ul>
                                </li>
                                <li x-data="{ openCashier: <?= isReportLinkActive('/reports/cashier', $relativeAdminPath) ? 'true' : 'false' ?> }" class="space-y-1">
                                    <button @click="openCashier = !openCashier" class="flex items-center justify-between w-full px-3 py-2 rounded-md text-xs font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition duration-150">
                                        <span>Laporan Kasir</span>
                                         <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-90': openCashier }" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                    </button>
    
                                     <ul x-show="openCashier" x-cloak x-transition class="dropdown-indent-2 space-y-1">
                                        <li><a href="<?= UrlHelper::baseUrl('/admin/reports/cashier/cash-summary') ?>" class="block px-3 py-1.5 rounded-md text-xs text-slate-500 hover:bg-indigo-50 hover:text-indigo-700 <?= isAdminLinkActive('/reports/cashier/cash-summary', $relativeAdminPath) ? 'font-semibold text-indigo-700' : '' ?>">Laporan Kas Kasir</a></li>
                                        <li><a href="<?= UrlHelper::baseUrl('/admin/reports/cashier/closing') ?>" class="block px-3 py-1.5 rounded-md text-xs text-slate-500 hover:bg-indigo-50 hover:text-indigo-700 <?= isAdminLinkActive('/reports/cashier/closing', $relativeAdminPath) ? 'font-semibold text-indigo-700' : '' ?>">Laporan Tutup Kasir</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="p-4 border-t mt-auto flex-shrink-0">
                 <a href="<?= UrlHelper::baseUrl('/admin/logout') ?>" class="flex items-center w-full px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:bg-red-50 transition duration-150">
                    <i class="fa-solid fa-right-from-bracket fa-fw"></i>
                    Logout
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
             <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                 <button id="sidebar-open-btn" class="text-slate-500 hover:text-slate-700 lg:hidden">
                     <i class="fas fa-bars fa-fw"></i>
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
                    if (isset($viewPath) && file_exists($viewPath)) {
                        require $viewPath;
                    } else {
                        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
                        echo '<strong class="font-bold">Error!</strong>';
                        echo '<span class="block sm:inline"> Admin view content ('. htmlspecialchars($viewPath ?? 'path not set', ENT_QUOTES) .') could not be loaded or found.</span>';
                        echo '</div>';
                    }
                 ?>
            </main>
        </div>
    </div>

     <div class="flash-message-overlap">
        <?php
        if (method_exists('App\Helpers\SessionHelper', 'displayAllFlashMessages')) {
            SessionHelper::displayAllFlashMessages('p-4 rounded-md text-sm border', [
                 'success' => 'bg-green-100 border-green-300 text-green-800',
                 'error'   => 'bg-red-100 border-red-300 text-red-800',
                 'info'    => 'bg-blue-100 border-blue-300 text-blue-800',
                 'warning' => 'bg-yellow-100 border-yellow-300 text-yellow-800'
            ], 'overlap');
        } else {
             echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
             echo 'Error: SessionHelper::displayAllFlashMessages() method not found.';
             echo '</div>';
        }
        ?>
     </div>

    <script src="<?= UrlHelper::baseUrl('js/admin-main.js') ?>" defer></script>
    <script src="<?= UrlHelper::baseUrl('js/admin-notifications.js') ?>" defer></script>
    <?php
        if (!empty($scripts) && is_array($scripts)) {
            foreach ($scripts as $script) {
                echo '<script src="' . SanitizeHelper::html(UrlHelper::baseUrl($script)) . '" defer></script>' . "\n";
            }
        }
    ?>
    </body>
</html>