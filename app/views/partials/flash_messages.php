<?php
// Partial untuk menampilkan semua flash message
$flashMessages = get_flash_messages(); // Ambil semua pesan dari helper
?>

<?php if (!empty($flashMessages)): ?>
    <div class="flash-messages mb-4 space-y-2">
        <?php foreach ($flashMessages as $type => $messages): ?>
            <?php foreach ($messages as $flash): ?>
                <?php
                // Tentukan class default berdasarkan tipe jika tidak diset spesifik
                $defaultClasses = [
                    'success' => 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative',
                    'error'   => 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative',
                    'warning' => 'bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative',
                    'info'    => 'bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative',
                ];
                $messageClass = $flash['class'] ?? ($defaultClasses[$type] ?? $defaultClasses['info']);
                ?>
                <div class="<?= $messageClass ?>" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($flash['message']) ?></span>
                     <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3" aria-label="Close">
                        <svg class="fill-current h-6 w-6 <?= strpos($messageClass, 'text-red') !== false ? 'text-red-500' : (strpos($messageClass, 'text-green') !== false ? 'text-green-500' : (strpos($messageClass, 'text-yellow') !== false ? 'text-yellow-500' : 'text-blue-500')) ?>" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>