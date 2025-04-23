<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\SessionHelper;
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? SanitizeHelper::html($pageTitle) . ' - ' : '' ?>Stay With Me Cafe</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <style>
        /* Sedikit kustomisasi dasar */
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', sans-serif; }
        /* Scrollbar styling (opsional) */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        ::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }

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
        var APP_BASE_URL = "<?= UrlHelper::baseUrl() ?>";
    </script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

    <header class="bg-white shadow-md sticky top-0 z-40">
        <nav class="container mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center">
            <a href="<?= UrlHelper::baseUrl('/') ?>" class="text-2xl font-bold text-indigo-600 hover:text-indigo-800 transition duration-150">
                Stay With Me
            </a>
            <div>
                </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 min-h-[calc(100vh-150px)]">
        <?php
        // Tampilkan pesan flash jika ada
        SessionHelper::displayAllFlashMessages('p-4 mb-6 rounded-md text-sm border', [
             'success' => 'bg-green-50 border-green-300 text-green-800',
             'error'   => 'bg-red-50 border-red-300 text-red-800',
             'info'    => 'bg-blue-50 border-blue-300 text-blue-800'
        ]);

        // =======================================================
        // == MEMUAT KONTEN VIEW SPESIFIK ==
        // =======================================================
        // Variabel $viewPath berasal dari Controller::view()
        if (isset($viewPath) && file_exists($viewPath)) {
            require $viewPath; // Memuat file view (e.g., home.php, menu.php)
        } else {
            // Tampilkan pesan error jika view tidak bisa dimuat
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
            echo '<strong class="font-bold">Internal Layout Error!</strong>';
            echo '<span class="block sm:inline"> View content file could not be loaded. Path: ' . SanitizeHelper::html($viewPath ?? 'Not Set') . '</span>';
            echo '</div>';
        }
        // =======================================================
        ?>
    </main>

    <footer class="bg-white border-t mt-10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 text-center text-slate-500 text-xs">
            &copy; <?= date('Y') ?> Stay With Me Cafe. Dibuat dengan ❤️.
        </div>
    </footer>

    </body>
</html>