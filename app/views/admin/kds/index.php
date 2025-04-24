<?php
use App\Helpers\SanitizeHelper;
use App\Helpers\UrlHelper;
use App\Helpers\DateHelper;

// Data awal dari KdsController
$orders = $orders ?? [];
$pageTitle = $pageTitle ?? 'Kitchen Display System';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="180">
    <title><?= SanitizeHelper::html($pageTitle) ?> - Stay With Me</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #e2e8f0; /* Slate 200 */ padding: 1rem; }
        h1, h2, h3, .font-poppins { font-family: 'Poppins', sans-serif; }
        /* KDS Card Styling */
        .kds-order-card { transition: all 0.3s ease-out; }
        /* Scrollbar */
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        ::-webkit-scrollbar-track { background: #cbd5e1; border-radius: 5px; }
        /* Header KDS */
        .kds-header {
            background-color: #475569; /* Slate 600 */
            color: white;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .kds-header h1 { font-size: 1.5rem; font-weight: 600; }
    </style>
<script>
    var APP_BASE_URL = "<?= rtrim(UrlHelper::baseUrl(), '/') ?>";
    var KDS_AUDIO_URL = "<?= UrlHelper::asset('audio/kds_new_order.mp3') ?>";
</script>
</head>
<body class="antialiased">

    <div class="kds-header">
        <h1 class="font-poppins">Kitchen Display System</h1>
    </div>

    <div id="kds-order-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
        <?php if (empty($orders)): ?>
            <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <?php
                    $isReceived = $order['status'] === 'received';
                    $cardBorderColor = $isReceived ? 'border-blue-400' : 'border-yellow-400';
                    $cardHeaderBg = $isReceived ? 'bg-blue-50' : 'bg-yellow-50';
                    $cardHeaderColor = $isReceived ? 'text-blue-800' : 'text-yellow-800';
                ?>
                <div class="kds-order-card bg-white rounded-lg shadow-md border-2 <?= $cardBorderColor ?> p-4 flex flex-col" data-order-id="<?= $order['id'] ?>" data-status="<?= $order['status'] ?>">
                    <div class="flex justify-between items-center mb-3 pb-2 border-b <?= $cardBorderColor ?>">
                        <span class="font-bold text-xl <?= $cardHeaderColor ?>">#<?= SanitizeHelper::html(str_replace('STW-', '', $order['order_number'])) ?></span>
                        <span class="text-sm text-slate-600 font-medium">Meja: <?= SanitizeHelper::html($order['table_number']) ?></span>
                        <span class="text-xs text-slate-500"><?= DateHelper::timeAgo($order['order_time']) ?></span>
                    </div>
                    <ul class="space-y-1.5 text-sm mb-4 flex-grow list-disc list-inside pl-1 text-slate-800">
                        <?php foreach ($order['items'] as $item): ?>
                             <li>
                                 <span class="font-semibold"><?= SanitizeHelper::html($item['quantity']) ?>x</span> <?= SanitizeHelper::html($item['menu_item_name']) ?>
                                 <?php if (!empty($item['notes'])): ?>
                                     <span class="block text-xs text-orange-700 italic ml-4">- <?= SanitizeHelper::html($item['notes']) ?></span>
                                 <?php endif; ?>
                             </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-auto flex space-x-2">
                         <?php if ($isReceived): ?>
                             <button class="kds-action-btn flex-1 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-bold py-2 px-3 rounded-md transition duration-150 shadow-sm" data-order-id="<?= $order['id'] ?>" data-new-status="preparing">
                                Mulai Siapkan
                            </button>
                         <?php elseif ($order['status'] === 'preparing'): ?>
                             <button class="kds-action-btn flex-1 bg-green-500 hover:bg-green-600 text-white text-sm font-bold py-2 px-3 rounded-md transition duration-150 shadow-sm" data-order-id="<?= $order['id'] ?>" data-new-status="ready">
                                Siap Disajikan
                            </button>
                            <button class="kds-action-btn bg-red-500 hover:bg-red-600 text-white text-xs font-medium py-2 px-2 rounded-md transition duration-150 shadow-sm" data-order-id="<?= $order['id'] ?>" data-new-status="cancelled" title="Batalkan Pesanan Ini">
                                 X
                             </button>
                         <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

     <p id="kds-empty-message" class="text-center text-xl text-slate-500 mt-16 py-10 <?= empty($orders) ? 'block' : 'hidden' ?>">
         Tidak ada pesanan yang perlu disiapkan saat ini.
     </p>

    <script src="<?= UrlHelper::baseUrl('js/admin-kds.js') ?>" defer></script>
    

</body>
</html>