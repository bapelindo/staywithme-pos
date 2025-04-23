<?php
// Lokasi File: app/views/public/contact.php
// Dimuat oleh Public\HomeController::contact()

ob_start();
// Data dari controller: $title, $errors, $old
?>

<div class="bg-white p-8 rounded-lg shadow-md max-w-lg mx-auto">
    <h1 class="text-3xl font-bold text-center text-gray-800 mb-8"><?= htmlspecialchars($title ?? 'Hubungi Kami') ?></h1>

    <p class="text-center text-gray-600 mb-6">
        Punya pertanyaan atau masukan? Jangan ragu untuk menghubungi kami melalui form di bawah ini.
    </p>

    <form action="<?= url_for('/contact') ?>" method="POST">
        <?php // CSRF Token jika ada ?>

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Anda</label>
            <input type="text" id="name" name="name" required
                   value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                   class="w-full border <?= isset($errors['name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-primary focus:border-primary"
                   placeholder="Nama Lengkap">
            <?php if (isset($errors['name'])): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['name'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Anda</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                   class="w-full border <?= isset($errors['email']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-primary focus:border-primary"
                   placeholder="you@example.com">
             <?php if (isset($errors['email'])): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['email'][0]) ?></p>
            <?php endif; ?>
        </div>

         <div class="mb-6">
            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Pesan Anda</label>
            <textarea id="message" name="message" rows="5" required
                      class="w-full border <?= isset($errors['message']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-sm focus:ring-primary focus:border-primary"
                      placeholder="Tulis pesan Anda di sini..."><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
             <?php if (isset($errors['message'])): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['message'][0]) ?></p>
            <?php endif; ?>
        </div>

         <button type="submit"
                 class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg shadow-md transition duration-300 ease-in-out">
             Kirim Pesan
         </button>

    </form>
</div>

<?php
$content = ob_get_clean();
require APPROOT . '/views/layouts/public.php';
?>