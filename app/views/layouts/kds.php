<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'KDS') ?> - <?= APP_NAME ?></title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="<?= asset('css/custom_kds.css') ?>">

    <script>
      window.APP_URLROOT = '<?= URLROOT ?>';
    </script>
    <style>
        /* Style dasar KDS */
        body { background-color: #333; color: #fff; }
        /* Tambahkan style lain untuk kartu pesanan, dll. */
    </style>
</head>
<body class="font-mono antialiased"> <?php // Font mono sering dipakai di KDS ?>

    <div class="container-fluid p-2 md:p-4"> <?php // KDS sering full-width ?>

         <h1 class="text-xl font-bold text-center mb-4 text-yellow-300"><?= htmlspecialchars($title ?? 'Kitchen Display System') ?></h1>

         <?= $content ?? ''; ?>

    </div>

    <script src="<?= asset('js/kds_script.js') ?>"></script>

    <?= $pageScript ?? ''; ?>

</body>
</html>