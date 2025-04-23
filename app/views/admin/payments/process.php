<?php
// Lokasi File: app/views/admin/payments/process.php
// Dimuat oleh Admin\PaymentController::process() & store() jika validasi gagal

ob_start();
// Data: $title, $pageTitle, $order, $errors, $old
$orderData = $order ?? null;
?>

<div class="max-w-lg mx-auto">
     <a href="<?= url_for('/admin/orders') ?>" class="text-sm text-blue-600 hover:underline mb-4 inline-block">&larr; Kembali ke Daftar Pesanan</a>

    <?php if ($orderData): ?>
        <div class="bg-white p-6 rounded-lg shadow-md border mb-6">
            <h3 class="text-lg font-semibold border-b pb-2 mb-3">Detail Pesanan</h3>
            <div class="grid grid-cols-3 gap-x-4 gap-y-1 text-sm">
                <span class="text-gray-500 font-medium">Kode Order:</span>
                <span class="col-span-2 text-gray-800 font-semibold"><?= htmlspecialchars($orderData->order_code) ?></span>

                <span class="text-gray-500 font-medium">Meja:</span>
                <span class="col-span-2 text-gray-800"><?= htmlspecialchars($orderData->table_number ?? 'N/A') // Asumsi sudah di-join ?></span>

                <span class="text-gray-500 font-medium">Waktu Pesan:</span>
                <span class="col-span-2 text-gray-800"><?= format_datetime($orderData->ordered_at) ?></span>

                 <span class="text-gray-500 font-medium">Status Order:</span>
                <span class="col-span-2 text-gray-800"><?= ucfirst(htmlspecialchars($orderData->order_status)) ?></span>

                <span class="text-gray-500 font-medium">Total Tagihan:</span>
                <span class="col-span-2 text-gray-900 text-lg font-bold"><?= format_rupiah($orderData->total_amount) ?></span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md border">
             <h3 class="text-lg font-semibold border-b pb-2 mb-4">Input Pembayaran Tunai</h3>
             <form id="payment-form" action="<?= url_for('/admin/payments/store/' . $orderData->id) ?>" method="POST">
                 <?php // CSRF ?>
                 <input type="hidden" id="total-amount" value="<?= (float)$orderData->total_amount ?>">

                <div class="mb-4">
                    <label for="amount_paid" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Uang Diterima (Rp) <span class="text-red-500">*</span></label>
                    <input type="text" id="amount_paid" name="amount_paid" required autofocus
                           value="<?= htmlspecialchars($old['amount_paid'] ?? '') ?>"
                           class="w-full border <?= isset($errors['amount_paid']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md px-3 py-2 text-lg font-semibold focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Contoh: 100000" inputmode="numeric"> <?php // inputmode untuk keyboard numerik di mobile ?>
                    <?php if (isset($errors['amount_paid'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors['amount_paid'][0]) ?></p>
                    <?php endif; ?>
                </div>

                 <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kembalian (Rp)</label>
                     <p id="change-amount" class="text-xl font-bold text-green-600 h-8">
                         -
                     </p>
                </div>


                 <div class="flex justify-end">
                     <a href="<?= url_for('/admin/orders') ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-md text-sm mr-2">
                        Batal
                     </a>
                     <button type="submit"
                            class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-md text-sm shadow-sm">
                        Konfirmasi Pembayaran
                     </button>
                 </div>
             </form>
        </div>

    <?php else: ?>
         <p class="text-center text-red-500 bg-red-100 p-4 rounded">Data pesanan tidak ditemukan.</p>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();

// Tambahkan JS untuk kalkulasi kembalian
ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountPaidInput = document.getElementById('amount_paid');
    const totalAmountInput = document.getElementById('total-amount');
    const changeAmountElement = document.getElementById('change-amount');

    if (amountPaidInput && totalAmountInput && changeAmountElement) {
        const totalAmount = parseFloat(totalAmountInput.value) || 0;

        const formatRupiah = (number) => {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
        };

        const calculateChange = () => {
            // Bersihkan input dari non-digit
            let amountPaidValue = amountPaidInput.value.replace(/[^\d]/g, '');
            let amountPaid = parseFloat(amountPaidValue) || 0;

            // Format input sambil user mengetik (opsional)
            // amountPaidInput.value = new Intl.NumberFormat('id-ID').format(amountPaid);

            if (amountPaid >= totalAmount) {
                const change = amountPaid - totalAmount;
                changeAmountElement.textContent = formatRupiah(change);
                changeAmountElement.classList.remove('text-red-600');
                changeAmountElement.classList.add('text-green-600');
            } else if (amountPaidValue.length > 0) {
                 changeAmountElement.textContent = 'Uang Kurang';
                 changeAmountElement.classList.remove('text-green-600');
                 changeAmountElement.classList.add('text-red-600');
            }
            else {
                changeAmountElement.textContent = '-';
                changeAmountElement.classList.remove('text-red-600', 'text-green-600');
            }
        };

        // Panggil saat input berubah
        amountPaidInput.addEventListener('input', calculateChange);

        // Panggil saat halaman load jika ada value lama
        calculateChange();

         // Format input saat kehilangan fokus (opsional)
        // amountPaidInput.addEventListener('blur', () => {
        //     let amountPaidValue = amountPaidInput.value.replace(/[^\d]/g, '');
        //     let amountPaid = parseFloat(amountPaidValue) || 0;
        //     amountPaidInput.value = formatRupiah(amountPaid);
        // });

         // Fokus ke input saat halaman dimuat
        // amountPaidInput.focus();
    }
});
</script>
<?php
$pageScript = ob_get_clean();

require APPROOT . '/views/layouts/admin.php';
?>