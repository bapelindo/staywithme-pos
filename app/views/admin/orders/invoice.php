<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\NumberHelper;
use App\Helpers\DateHelper;

$order = $order ?? null;
$payment = $payment ?? null;
// Ambil detail cafe dari Config atau Model Settings jika ada
$cafeName = SanitizeHelper::html(APP_NAME ?? 'Stay With Me Cafe');
$cafeAddress = SanitizeHelper::html(CAFE_ADDRESS ?? 'Jl. Contoh No. 123, Kota Anda');
$cafePhone = SanitizeHelper::html(CAFE_PHONE ?? '0812-3456-7890');
$pageTitle = "Invoice #" . ($order ? SanitizeHelper::html($order['order_number']) : 'Error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= \App\Helpers\UrlHelper::baseUrl('css/admin_output.css') ?>">
    <style>
        body {
            font-family: 'sans-serif'; /* Font dasar yang umum */
            color: #333;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important; /* Force print background colors/images */
            print-color-adjust: exact !important;
        }
        .invoice-container {
            width: 100%;
            max-width: 800px; /* Lebar maks invoice */
            margin: 20px auto;
            padding: 25px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .invoice-header h1 {
            font-size: 1.8em; /* Ukuran nama cafe */
            margin: 0 0 5px 0;
            color: #111;
            font-weight: 600;
        }
        .invoice-header p {
             font-size: 0.85em;
             color: #666;
             margin: 2px 0;
        }
        .invoice-details {
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
             font-size: 0.9em;
        }
        .invoice-details div { line-height: 1.4; }
        .invoice-details strong { font-weight: 600; color: #000; }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .invoice-table th, .invoice-table td {
            border: 1px solid #eee;
            padding: 8px 10px;
            font-size: 0.9em;
            text-align: left;
        }
        .invoice-table th {
             background-color: #f9f9f9;
             font-weight: 600;
        }
        .invoice-table td.qty, .invoice-table th.qty { text-align: center; }
        .invoice-table td.price, .invoice-table th.price { text-align: right; }
        .invoice-totals {
            margin-top: 20px;
            text-align: right;
             font-size: 0.95em;
        }
         .invoice-totals table {
            width: 50%; /* Tabel total di kanan */
            margin-left: auto;
            border-collapse: collapse;
         }
         .invoice-totals td { padding: 5px 8px; }
         .invoice-totals .grand-total td {
             border-top: 2px solid #333;
             font-weight: bold;
             font-size: 1.1em;
             padding-top: 10px;
         }
         .payment-status {
             margin-top: 25px;
             padding-top: 15px;
             border-top: 1px solid #eee;
             font-weight: bold;
             text-align: center;
             font-size: 1.1em;
         }
         .payment-status.paid { color: green; }
         .payment-status.unpaid { color: orange; }
         .invoice-footer {
             text-align: center;
             margin-top: 30px;
             font-size: 0.8em;
             color: #777;
         }
         .print-button-container { text-align: center; margin-top: 20px; }

        /* Print Styles */
        @media print {
            body { margin: 0; padding: 0; background-color: white; }
            .invoice-container {
                box-shadow: none;
                border: none;
                margin: 0;
                max-width: 100%;
                padding: 5mm; /* Margin cetak */
            }
            .print-button-container { display: none; }
             /* Pastikan background tercetak (browser setting juga berpengaruh) */
             .invoice-table th { background-color: #f9f9f9 !important; }
        }
    </style>
</head>
<body>

    <?php if ($order): ?>
    <div class="invoice-container">
        <div class="print-button-container">
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition">
                Cetak Invoice
            </button>
            <a href="<?= UrlHelper::baseUrl('/admin/orders/show/' . $order['id']) ?>" class="ml-2 text-sm text-indigo-600 hover:underline">Kembali ke Detail</a>
        </div>

        <div class="invoice-header">
            <h1><?= $cafeName ?></h1>
            <p><?= $cafeAddress ?></p>
            <p><?= $cafePhone ?></p>
        </div>

        <div class="invoice-details">
            <div>
                 <strong>Pesanan #:</strong> <?= SanitizeHelper::html($order['order_number']) ?><br>
                 <strong>Tanggal:</strong> <?= DateHelper::formatIndonesian($order['order_time'], 'dateonly') ?><br>
                 <strong>Waktu:</strong> <?= DateHelper::formatIndonesian($order['order_time'], 'H:i:s') ?>
            </div>
             <div>
                 <strong>Meja #:</strong> <?= SanitizeHelper::html($order['table_number']) ?><br>
                 <?php if ($payment && isset($payment['processed_by_user_name'])): ?>
                    <strong>Kasir:</strong> <?= SanitizeHelper::html($payment['processed_by_user_name']) ?>
                 <?php endif; ?>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th class="qty">Qty</th>
                    <th>Deskripsi Item</th>
                    <th class="price">Harga/Unit</th>
                    <th class="price">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($order['items'])): ?>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td class="qty"><?= SanitizeHelper::html($item['quantity']) ?></td>
                            <td>
                                <?= SanitizeHelper::html($item['menu_item_name']) ?>
                                <?php if (!empty($item['notes'])): ?>
                                    <br><span style="font-size: 0.8em; color: #555; font-style: italic;">(Cat: <?= SanitizeHelper::html($item['notes']) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="price"><?= NumberHelper::formatCurrencyIDR($item['price_at_order'], false) // Tanpa simbol ?></td>
                            <td class="price"><?= NumberHelper::formatCurrencyIDR($item['subtotal'], false) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center;">(Tidak ada item)</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="invoice-totals">
             <table>
                 <tr class="grand-total">
                     <td>Total:</td>
                     <td style="text-align:right;"><?= NumberHelper::formatCurrencyIDR($order['total_amount']) ?></td>
                 </tr>
             </table>
        </div>

         <?php
            $isPaid = $payment || ($order['status'] === 'paid' || $order['status'] === 'served'); // Asumsi 'served' juga dianggap akan lunas
            $paymentText = $isPaid ? 'LUNAS' : 'BELUM LUNAS';
            $paymentClass = $isPaid ? 'paid' : 'unpaid';
        ?>
        <div class="payment-status <?= $paymentClass ?>">
            <?= $paymentText ?>
            <?php if ($payment): ?>
                (<?= SanitizeHelper::html(ucfirst($payment['payment_method'])) ?>)
            <?php endif; ?>
        </div>

        <div class="invoice-footer">
            Terima kasih atas kunjungan Anda!
        </div>

    </div>
    <?php else: ?>
        <div style="padding: 20px; text-align: center; color: red;">Invoice tidak dapat dibuat karena data pesanan tidak ditemukan.</div>
    <?php endif; ?>

</body>
</html>