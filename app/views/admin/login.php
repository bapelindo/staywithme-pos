<?php
// Lokasi File: app/views/admin/login.php
// Halaman ini dimuat oleh DashboardController::login()
// Halaman ini TIDAK menggunakan layout admin utama karena user belum login.

// Ambil pesan flash jika ada (dari redirect setelah gagal auth atau logout)
$flashMessages = get_flash_messages(); // Pastikan helper ini ada dan berfungsi
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin Login') ?> - <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link rel="icon" href="<?= asset('images/favicon.ico') ?>" type="image/x-icon">
    <style>
        /* Background sederhana untuk halaman login */
        body { background-color: #e2e8f0; /* gray-200 */ }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-xl border border-gray-200">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-2"><?= APP_NAME ?></h1>
        <h2 class="text-xl font-semibold text-center text-gray-700 mb-6">Admin Area Login</h2>

        <?php // --- Menampilkan Flash Messages --- ?>
        <?php if (!empty($flashMessages)): ?>
            <?php foreach ($flashMessages as $type => $messages): ?>
                <?php foreach ($messages as $flash): ?>
                    <?php
                        // Default class berdasarkan tipe flash message
                        $bgColor = 'bg-blue-100'; $borderColor = 'border-blue-400'; $textColor = 'text-blue-700'; // Default (info/success)
                        if ($type === 'error') {
                            $bgColor = 'bg-red-100'; $borderColor = 'border-red-400'; $textColor = 'text-red-700';
                        } elseif ($type === 'warning') {
                             $bgColor = 'bg-yellow-100'; $borderColor = 'border-yellow-400'; $textColor = 'text-yellow-700';
                        } elseif ($type === 'success') {
                             $bgColor = 'bg-green-100'; $borderColor = 'border-green-400'; $textColor = 'text-green-700';
                        }
                        // Jika class spesifik diberikan dari set_flash(), gunakan itu
                        $alertClass = $flash['type'] ?? "$bgColor border $borderColor $textColor";
                    ?>
                     <div class="<?= $alertClass ?> px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php // --- Akhir Flash Messages --- ?>


        <?php // Action form HARUS mengarah ke method processLogin di controller Anda ?>
        <form action="<?= url_for('/admin/processLogin') // Pastikan route ini benar ?> " method="POST">

            <?php // TODO: Tambahkan CSRF token jika diimplementasikan ?>
            <?php /* <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>"> */ ?>

            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" id="username" name="username" required autofocus
                       <?php // Opsional: Isi kembali username jika login gagal ?>
                       <?php // value="<?= htmlspecialchars(get_flash_input('username') ?? '') ?>
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Masukkan username Anda">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Masukkan password Anda">
                 <?php // Tambahkan link "Lupa Password?" jika ada fiturnya ?>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md text-lg transition duration-150 ease-in-out shadow-sm">
                Login
            </button>
        </form>

         <p class="text-xs text-gray-500 text-center mt-6">
            &copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.
        </p>

    </div>

</body>
</html>