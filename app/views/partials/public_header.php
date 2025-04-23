<?php
// Lokasi File: app/views/partials/public_header.php

ensure_session_started(); // Pastikan session aktif untuk cek login/cart/meja

// Cek user login (jika ada fitur login pelanggan) - Hindari $this
$currentUser = null;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) { // Atau session key pelanggan Anda
    $currentUser = (object) [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? null,
        'role' => $_SESSION['user_role'],
    ];
}

// Cek data sesi meja dan keranjang
$currentTableNumber = $_SESSION['current_table_number'] ?? null;
$cartItems = $_SESSION['cart'] ?? [];
$cartCount = count($cartItems);

?>
<header class="bg-white shadow-md sticky top-0 z-50">
    <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
        <a href="<?= url_for('/') ?>" class="flex items-center space-x-2 text-xl font-bold text-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span><?= APP_NAME ?></span>
        </a>

        <div class="hidden md:flex items-center space-x-6">
            <a href="<?= url_for('/menu') ?>" class="text-gray-600 hover:text-primary transition duration-150 ease-in-out">Menu</a>
            <a href="<?= url_for('/about') ?>" class="text-gray-600 hover:text-primary transition duration-150 ease-in-out">Tentang Kami</a>
            <a href="<?= url_for('/contact') ?>" class="text-gray-600 hover:text-primary transition duration-150 ease-in-out">Kontak</a>

            <?php // Tampilkan info meja & keranjang jika ada sesi meja ?>
            <?php if ($currentTableNumber): ?>
                <a href="<?= url_for('/cart') ?>" class="relative text-gray-600 hover:text-primary transition duration-150 ease-in-out" title="Keranjang Belanja">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <?php if ($cartCount > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-accent text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                 <span class="text-sm text-gray-500 border-l pl-4 ml-4">Meja: <?= htmlspecialchars($currentTableNumber) ?></span>
            <?php endif; ?>

             <?php // Tampilkan info user jika login (contoh) ?>
             <?php /* if ($currentUser): ?>
                 <span class="text-gray-700">Hi, <?= htmlspecialchars($currentUser->name) ?></span>
             <?php endif; */ ?>
        </div>

        <div class="md:hidden">
            <?php // Pastikan ada JS untuk handle klik tombol ini ?>
            <button id="mobile-menu-button" class="text-gray-600 hover:text-primary focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
            </button>
        </div>
    </nav>

    <div id="mobile-menu" class="md:hidden hidden bg-white border-t border-gray-200">
        <a href="<?= url_for('/menu') ?>" class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-primary">Menu</a>
        <a href="<?= url_for('/about') ?>" class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-primary">Tentang Kami</a>
        <a href="<?= url_for('/contact') ?>" class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-primary">Kontak</a>
         <?php if ($currentTableNumber): ?>
             <a href="<?= url_for('/cart') ?>" class="block px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-primary">Keranjang (<?= $cartCount ?>)</a>
             <span class="block px-4 py-3 text-sm text-gray-500">Meja: <?= htmlspecialchars($currentTableNumber) ?></span>
        <?php endif; ?>
    </div>
</header>