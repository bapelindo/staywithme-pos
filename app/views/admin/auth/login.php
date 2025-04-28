<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;

// $error berisi pesan error dari AuthController (jika ada)
$error = $error ?? null;
$pageTitle = $pageTitle ?? 'Admin Login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SanitizeHelper::html($pageTitle) ?> - Stay With Me Cafe</title>
    <link rel="stylesheet" href="<?= \App\Helpers\UrlHelper::baseUrl('css/admin_output.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gradient-to-br from-indigo-100 via-white to-purple-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md px-8 py-10 bg-white rounded-xl shadow-2xl">
        <h1 class="text-3xl font-bold text-center text-indigo-600 mb-2" style="font-family: 'Poppins', sans-serif;">
            Admin Login
        </h1>
        <p class="text-center text-sm text-slate-500 mb-8">Stay With Me Cafe POS</p>

        <?php if ($error): ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm" role="alert">
                <?= SanitizeHelper::html($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= UrlHelper::baseUrl('/admin/login') ?>" method="POST" novalidate>
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                <input type="text" id="username" name="username" required
                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                       placeholder="Masukkan username Anda">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                       placeholder="Masukkan password Anda">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md">
                Login
            </button>
        </form>
        </div>

</body>
</html>