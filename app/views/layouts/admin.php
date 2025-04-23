<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin Area') ?> - <?= APP_NAME ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
     <script>
        // Kustomisasi dasar Tailwind via CDN (opsional)
        tailwind.config = { /* ... bisa beda theme untuk admin ... */ }
    </script>

    <link rel="icon" href="<?= asset('images/favicon.ico') ?>" type="image/x-icon">

    <link rel="stylesheet" href="<?= asset('css/custom_admin.css') ?>">

    <script>
      window.APP_URLROOT = '<?= URLROOT ?>';
    </script>

</head>
<body class="bg-gray-200 font-sans text-gray-900 antialiased">

    <div class="flex h-screen bg-gray-200">
        <?php require APPROOT . '/views/partials/admin_sidebar.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <?php require APPROOT . '/views/partials/admin_header.php'; ?>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">

                    <h3 class="text-gray-700 text-3xl font-semibold mb-6"><?= htmlspecialchars($pageTitle ?? $title ?? 'Dashboard') ?></h3>

                    <?php require APPROOT . '/views/partials/flash_messages.php'; ?>

                    <div class="bg-white p-6 rounded-lg shadow-md">
                          <?= $content ?? ''; ?>
                     </div>

                </div>
            </main>

            <?php // require APPROOT . '/views/partials/admin_footer.php'; ?>
        </div>
    </div>

    <script src="<?= asset('js/admin_global.js') ?>"></script>

    <?= $pageScript ?? ''; ?>

</body>
</html>