<?php
/**
 * Template Invoice Sederhana (HTML untuk mPDF)
 * Lokasi: app/views/admin/invoice/template.php
 *
 * Variabel yang tersedia dari InvoiceController::show():
 * $cafe_name, $cafe_address, $cafe_phone, $order, $items, $payment, $cashier_name
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= htmlspecialchars($order->order_code ?? '') ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px; /* Ukuran font kecil untuk struk */
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 10px;
            /* Lebar bisa disesuaikan jika target printer thermal */
            /* width: 76mm; */
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 5px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .header p {
            margin: 2px 0;
            font-size: 9px;
        }
        .info {
            margin-bottom: 10px;
            font-size: 9px;
        }
        .info table {
            width: 100%;
            border-collapse: collapse;
        }
        .info td {
            padding: 1px 0;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .items-table th, .items-table td {
            border-bottom: 1px dotted #ccc;
            padding: 3px 2px;
            text-align: left;
            font-size: 9px;
            vertical-align: top;
        }
        .items-table th {
            border-bottom-style: solid;
            font-weight: bold;
        }
        .items-table .qty { text-align: center; width: 20px; }
        .items-table .price, .items-table .subtotal { text-align: right; }
        .totals {
            margin-top: 10px;
            border-top: 1px dashed #ccc;
            padding-top: 5px;
        }
        .totals table {
            width: 100%;
            font-size: 10px;
        }
         .totals td { padding: 1px 0; }
        .totals .label { text-align: left; font-weight:normal; }
        .totals .value { text-align: right; font-weight:bold; }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 9px;
            color: #555;
            border-top: 1px dashed #ccc;
            padding-top: 5px;
        }
        .footer p { margin: 2px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= htmlspecialchars($cafe_name ?? 'Nama Kafe Anda') ?></h1>
            <p><?= htmlspecialchars($cafe_address ?? 'Alamat Kafe') ?></p>
            <p><?= htmlspecialchars($cafe_phone ?? 'No Telepon Kafe') ?></p>
        </div>

        <div class="info">
            <table>
                <tr> <td>No. Order</td> <td>:</td> <td><?= htmlspecialchars($order->order_code ?? 'N/A') ?></td> </tr>
                <tr> <td>Tanggal</td> <td>:</td> <td><?= format_datetime($order->ordered_at ?? time(), 'd/m/Y H:i') ?></td> </tr>
                <tr> <td>Meja</td> <td>:</td> <td><?= htmlspecialchars($order->table_number ?? '?') ?></td> </tr>
                <tr> <td>Pelanggan</td> <td>:</td> <td><?= htmlspecialchars($order->customer_name ?? 'Pelanggan') ?></td> </tr>
                 <?php if ($payment && isset($cashier_name)): ?>
                 <tr> <td>Kasir</td> <td>:</td> <td><?= htmlspecialchars($cashier_name) ?></td> </tr>
                 <?php endif; ?>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="qty">Jml</th>
                    <th>Item</th>
                    <th class="price">Harga</th>
                    <th class="subtotal">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="qty"><?= htmlspecialchars($item->quantity ?? 1) ?></td>
                        <td><?= htmlspecialchars($item->menu_item_name ?? 'Item tidak dikenal') ?></td>
                        <td class="price"><?= number_format($item->price_per_item ?? 0, 0, ',', '.') ?></td>
                        <td class="subtotal"><?= number_format($item->subtotal ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; font-style: italic;">Tidak ada item.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="value"><?= format_rupiah($order->total_amount ?? 0) ?></td>
                </tr>
                <?php // Tambahkan Pajak, Service Charge jika ada perhitungannya ?>
                <tr>
                    <td class="label" style="font-weight:bold; border-top: 1px solid #ccc; padding-top: 3px;">Grand Total</td>
                    <td class="value" style="font-weight:bold; border-top: 1px solid #ccc; padding-top: 3px;"><?= format_rupiah($order->total_amount ?? 0) // Sesuaikan jika ada pajak ?></td>
                </tr>
                <?php if ($payment): ?>
                    <tr>
                        <td class="label">Metode Bayar</td>
                        <td class="value"><?= ucfirst(htmlspecialchars($payment->payment_method ?? 'N/A')) ?></td>
                    </tr>
                    <?php if ($payment->payment_method == 'cash'): ?>
                    <tr>
                        <td class="label">Tunai Diterima</td>
                        <td class="value"><?= format_rupiah($payment->amount_paid ?? 0) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Kembalian</td>
                        <td class="value"><?= format_rupiah($payment->change_given ?? 0) ?></td>
                    </tr>
                    <?php endif; ?>
                <?php endif; ?>
            </table>
        </div>

        <div class="footer">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p><?= APP_NAME ?></p>
            <?php /* Bisa tambahkan info WiFi, sosial media, dll. */ ?>
        </div>
    </div>
</body>
</html>