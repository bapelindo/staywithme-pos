<?php
// File: buat_admin.php (HANYA untuk membuat hash password awal)

// --- Ganti password ini dengan password yang Anda inginkan ---
$passwordAdmin = 'password'; // Contoh password, GANTI DENGAN YANG AMAN!
$usernameAdmin = 'admin'; // Username yang diinginkan
$namaAdmin = 'Administrator Utama'; // Nama lengkap
// -----------------------------------------------------------

// Buat hash password menggunakan algoritma default PHP (aman)
$hashedPassword = password_hash($passwordAdmin, PASSWORD_DEFAULT);

if ($hashedPassword === false) {
    echo "ERROR: Gagal membuat hash password!";
} else {
    echo "====== User Admin Siap Dimasukkan ke Database ======\n";
    echo "Username     : " . $usernameAdmin . "\n";
    echo "Password Asli: " . $passwordAdmin . " (Gunakan ini untuk login di web)\n";
    echo "Nama Lengkap : " . $namaAdmin . "\n";
    echo "Peran (role) : admin\n";
    echo "Status Aktif : 1\n";
    echo "-----------------------------------------------------\n";
    echo "!!! HASH PASSWORD (Salin dan tempel ini ke kolom 'password' di database) !!!\n";
    echo $hashedPassword . "\n";
    echo "-----------------------------------------------------\n";
    echo "Setelah hash disalin, Anda bisa menghapus file ini (buat_admin.php).\n";
}
?>