<?php
// Lokasi File: app/views/public/about.php
// Dimuat oleh Public\HomeController::about()

ob_start();
// Data dari controller: $title, $description, $address, $phone
?>

<div class="bg-white p-8 rounded-lg shadow-md max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold text-center text-gray-800 mb-6"><?= htmlspecialchars($title ?? 'Tentang Kami') ?></h1>

    <div class="prose prose-lg max-w-none text-gray-700">
        <p>
            <?= nl2br(htmlspecialchars($description ?? 'Informasi tentang kafe Stay With Me akan ditampilkan di sini. Kami berkomitmen untuk menyajikan kopi terbaik dan suasana yang nyaman untuk Anda.')) ?>
        </p>

        <h2 class="text-2xl font-semibold mt-8 mb-4">Lokasi Kami</h2>
        <p>
            Kunjungi kami di: <br>
            <strong><?= htmlspecialchars($address ?? 'Alamat belum tersedia.') ?></strong>
        </p>
        <?php if(!empty($phone)): ?>
        <p>
            Hubungi kami di: <strong><?= htmlspecialchars($phone) ?></strong>
        </p>
        <?php endif; ?>

        <div class="mt-6 aspect-w-16 aspect-h-9 bg-gray-200 rounded-md overflow-hidden">
              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d...YOUR_EMBED_CODE..." width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
              <?php // Ganti src dengan kode embed Google Maps Anda ?>
         </div>

    </div>
</div>


<?php
$content = ob_get_clean();
require APPROOT . '/views/layouts/public.php';
?>