<?php
// Lokasi File: app/views/public/index.php
// Halaman ini akan dimuat oleh Public\HomeController::index()

// Tangkap output untuk dimasukkan ke layout
ob_start();

// Ambil data dari controller (contoh)
$welcomeMessage = $welcome_message ?? 'Nikmati suasana nyaman dan sajian istimewa kami.';
// $featuredItems = $featuredItems ?? [];
?>

<div class="text-center pt-10 pb-16">
    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-4 animate-fade-in-down">
        Selamat Datang di <span class="text-primary"><?= APP_NAME ?></span>
    </h1>
    <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-2xl mx-auto animate-fade-in-up">
        <?= htmlspecialchars($welcomeMessage) ?>
    </p>
    <div class="space-x-4 animate-fade-in">
        <a href="<?= url_for('/menu') ?>"
           class="bg-primary hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full text-lg shadow-md transition duration-300 ease-in-out transform hover:-translate-y-1">
            Lihat Menu
        </a>
        <?php if (!isset($_SESSION['current_table_id'])): // Tampilkan jika belum scan meja ?>
        <a href="#how-to-order"
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded-full text-lg shadow-sm transition duration-300 ease-in-out">
            Cara Pesan
        </a>
        <?php endif; ?>
    </div>
</div>

<?php /*
if (!empty($featuredItems)) {
    <div class="mt-16 mb-10">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Menu Spesial Kami</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            // Loop featured items
            foreach ($featuredItems as $item) {
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="<?= asset($item->image_path ?? 'images/default-menu.jpg') ?>" alt="<?= htmlspecialchars($item->name) ?>" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-gray-900 mb-2"><?= htmlspecialchars($item->name) ?></h3>
                        <p class="text-gray-600 text-sm mb-3"><?= htmlspecialchars(substr($item->description ?? '', 0, 50)) ?>...</p>
                        <div class="flex justify-between items-center">
                            <span class="text-primary font-bold"><?= format_rupiah($item->price) ?></span>
                            <a href="<?= url_for('/menu#' . $item->id) ?>" class="text-sm text-blue-600 hover:underline">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            }
        </div>
    </div>
}
*/?>

<?php if (!isset($_SESSION['current_table_id'])): ?>
<div id="how-to-order" class="mt-10 mb-10 p-8 bg-white rounded-lg shadow-md border border-gray-200">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Cara Memesan di Meja</h2>
    <ol class="list-decimal list-inside space-y-3 text-gray-700 max-w-lg mx-auto">
        <li>Cari <strong class="text-primary">QR Code</strong> yang tersedia di meja Anda.</li>
        <li><strong class="text-primary">Pindai (Scan)</strong> QR Code tersebut menggunakan kamera ponsel Anda.</li>
        <li>Anda akan diarahkan ke halaman menu digital kami.</li>
        <li>Pilih menu favorit Anda, tambahkan ke keranjang, lalu selesaikan pesanan.</li>
        <li>Pantau status pesanan Anda langsung dari ponsel.</li>
        <li>Lakukan pembayaran di kasir setelah pesanan Anda siap atau selesai disajikan.</li>
    </ol>
</div>
<?php endif; ?>


<?php
// Akhiri tangkapan output dan masukkan ke variabel $content
$content = ob_get_clean();

// Sertakan layout utama
require APPROOT . '/views/layouts/public.php';
?>