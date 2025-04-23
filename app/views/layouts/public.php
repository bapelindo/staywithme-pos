<?php
// Lokasi File: app/views/layouts/public.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? APP_NAME ?? 'StayWithMe') ?></title> <?php // Tambahkan fallback APP_NAME ?>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Kustomisasi dasar Tailwind via CDN (opsional)
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3498db', // Contoh warna primer (biru)
                        secondary: '#2ecc71', // Contoh warna sekunder (hijau)
                        accent: '#e74c3c', // Contoh warna aksen (merah)
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', /* ... */], // Contoh font
                    }
                }
            }
        }
    </script>

    <?php // Perbaikan: Gunakan asset_url() ?>
    <link rel="icon" href="<?= asset_url('images/favicon.ico') ?>" type="image/x-icon">

    <?php // Perbaikan: Gunakan asset_url() ?>
    <link rel="stylesheet" href="<?= asset_url('css/custom_public.css') ?>">

    <script>
      <?php // Pastikan URLROOT terdefinisi di config.php atau functions.php ?>
      window.APP_BASE_URL = '<?= BASE_URL ?? '' ?>'; // Ganti URLROOT dengan BASE_URL jika lebih konsisten
    </script>

    <style> /* Efek flash sederhana */
        .flash-update { animation: flash-bg 1s ease-out; }
        @keyframes flash-bg { 0%, 100% { background-color: transparent; } 50% { background-color: #a7f3d0; /* Contoh warna flash */ } }
    </style>

</head>
<body class="bg-gray-100 font-sans text-gray-800 antialiased">

    <?php // Pastikan partial header ada ?>
    <?php if (file_exists(APPROOT . '/app/views/partials/public_header.php')) require APPROOT . '/app/views/partials/public_header.php'; ?>

    <div class="container mx-auto px-4 py-6 md:py-8 min-h-[calc(100vh-150px)]"> <?php // Sesuaikan min-h berdasarkan tinggi header/footer ?>

        <?php // Pastikan partial flash message ada ?>
        <?php if (function_exists('display_flash_messages')) echo display_flash_messages(); // Panggil fungsi jika ada ?>
        <?php // Atau jika pakai file terpisah: ?>
        <?php // if (file_exists(APPROOT . '/app/views/partials/flash_messages.php')) require APPROOT . '/app/views/partials/flash_messages.php'; ?>


        <?= $content ?? ''; // Output konten utama dari view ?>

    </div>

    <?php // Pastikan partial footer ada (jika digunakan) ?>
    <?php // if (file_exists(APPROOT . '/app/views/partials/public_footer.php')) require APPROOT . '/app/views/partials/public_footer.php'; ?>

    <?php // Perbaikan: Gunakan asset_url() ?>
    <script src="<?= asset_url('js/public_global.js') ?>"></script>

    <?= $pageScript ?? ''; // Output script spesifik halaman jika ada ?>

</body>
</html>