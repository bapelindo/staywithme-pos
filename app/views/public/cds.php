<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;

// Data dari CdsController
$preparingOrders = $preparingOrders ?? [];
$readyOrders = $readyOrders ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="300">
    <title>Status Pesanan - Stay With Me Cafe</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; background-color: #111827; /* Dark background */ color: #f3f4f6; }
        .cds-container { display: flex; height: 100vh; font-family: 'Poppins', sans-serif; }
        .cds-column { flex: 1; display: flex; flex-direction: column; padding: 2rem 1.5rem; border-left: 2px solid #374151; /* Darker border */ }
        .cds-column:first-child { border-left: none; }
        .cds-header { font-size: 2.5rem; font-weight: 700; text-align: center; padding-bottom: 1.5rem; margin-bottom: 1.5rem; border-bottom: 3px solid; text-transform: uppercase; letter-spacing: 0.05em; }
        .cds-list { flex-grow: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.25rem; align-content: start; padding: 0.5rem;}
        .cds-list-item { font-size: 2.25rem; font-weight: 600; padding: 1rem 0.75rem; text-align: center; border-radius: 0.5rem; background-color: #374151; /* Darker item background */ box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); transition: all 0.3s ease-in-out; border: 1px solid #4b5563; }
        /* Animasi untuk item baru */
        .new-item-animate { animation: newItemPulse 1s ease-out; }
        @keyframes newItemPulse { 0% { transform: scale(0.9); opacity: 0.5; } 70% { transform: scale(1.05); opacity: 1; } 100% { transform: scale(1); opacity: 1; } }
        .cds-empty { grid-column: 1 / -1; text-align: center; font-size: 1.5rem; color: #6b7280; /* Lighter gray */ margin-top: 3rem; } /* Pastikan ini ada jika menggunakan grid */

        /* Warna Kolom Spesifik */
        .cds-preparing .cds-header { border-color: #f59e0b; color: #fbbf24; } /* Amber */
        .cds-ready .cds-header { border-color: #10b981; color: #34d399; }    /* Emerald */
        .cds-preparing .cds-list-item { border-color: #f59e0b; color: #fcd34d; }
        .cds-ready .cds-list-item { border-color: #10b981; color: #6ee7b7; }

        /* Scrollbar Styling Dark */
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        ::-webkit-scrollbar-track { background: #1f2937; border-radius: 5px; }
    </style>
<script>
    var APP_BASE_URL = "<?= rtrim(UrlHelper::baseUrl(), '/') ?>";
    var CDS_AUDIO_URL = "<?= UrlHelper::asset('audio/cds_new_order.mp3') ?>";
</script>
</head>
<body>

    <div class="cds-container">
        <div class="cds-column cds-preparing">
            <h2 class="cds-header">PROCESSING</h2>
            <div id="preparing-list" class="cds-list">
                <?php if (!empty($preparingOrders)): // Render initial items if available ?>
                    <?php foreach ($preparingOrders as $order): ?>
                        <div class="cds-list-item" data-order-number="<?= SanitizeHelper::html($order['order_number']) ?>">
                            <?= SanitizeHelper::html(str_replace('STW-', '', $order['order_number'])) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <p id="preparing-empty-message" class="cds-empty col-span-full" style="display: <?= empty($preparingOrders) ? 'block' : 'none'; ?>;">Waiting for orders...</p>
        </div>

        <div class="cds-column cds-ready">
             <h2 class="cds-header">READY</h2>
             <div id="ready-list" class="cds-list">
                <?php if (!empty($readyOrders)): // Render initial items if available ?>
                    <?php foreach ($readyOrders as $order): ?>
                        <div class="cds-list-item" data-order-number="<?= SanitizeHelper::html($order['order_number']) ?>">
                             <?= SanitizeHelper::html(str_replace('STW-', '', $order['order_number'])) ?>
                        </div>
                    <?php endforeach; ?>
                 <?php endif; ?>
             </div>
              <p id="ready-empty-message" class="cds-empty col-span-full" style="display: <?= empty($readyOrders) ? 'block' : 'none'; ?>;">No orders ready.</p>
        </div>
    </div>

    <script src="<?= App\Helpers\UrlHelper::baseUrl('js/customer-cds.js') ?>" defer></script>

</body>
</html>