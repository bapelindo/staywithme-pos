<?php
// Lokasi File: app/views/admin/dashboard/index.php
// Dimuat oleh Admin\DashboardController::index()

ob_start(); // Tangkap output untuk $content

// Data dari controller: $title, $pageTitle, $user, $todayOrdersCount, $pendingOrdersCount, $totalRevenueToday, $activeStaffCount (contoh)
$adminUser = $user ?? null;
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-lg shadow-lg text-white">
        <h4 class="text-lg font-semibold mb-1">Pesanan Hari Ini</h4>
        <p class="text-3xl font-bold"><?= $todayOrdersCount ?? 0 ?></p>
        <p class="text-sm opacity-80">Total pesanan masuk hari ini.</p>
    </div>

    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-6 rounded-lg shadow-lg text-white">
        <h4 class="text-lg font-semibold mb-1">Pesanan Pending</h4>
        <p class="text-3xl font-bold"><?= $pendingOrdersCount ?? 0 ?></p>
        <p class="text-sm opacity-80">Pesanan belum selesai diproses.</p>
    </div>

    <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-lg shadow-lg text-white">
        <h4 class="text-lg font-semibold mb-1">Pendapatan Hari Ini</h4>
        <p class="text-3xl font-bold"><?= format_rupiah($totalRevenueToday ?? 0) ?></p>
        <p class="text-sm opacity-80">Total dari pesanan yang sudah dibayar.</p>
    </div>

    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 p-6 rounded-lg shadow-lg text-white">
        <h4 class="text-lg font-semibold mb-1">Staff Aktif</h4>
        <p class="text-3xl font-bold"><?= $activeStaffCount ?? 0 ?></p>
        <p class="text-sm opacity-80">Jumlah staff/kitchen yang aktif.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded shadow border">
        <h4 class="text-lg font-semibold mb-4 border-b pb-2">Pesanan Terbaru</h4>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            <?php /* Loop pesanan terbaru dari controller ($recentOrders) */ ?>
             <div class="text-gray-500 text-center py-10 italic">Data pesanan terbaru akan ditampilkan di sini.</div>
             <?php /*
             foreach($recentOrders as $order) {
                echo '<div class="flex justify-between items-center text-sm border-b pb-1">';
                echo '<span>#' . htmlspecialchars($order->order_code) . ' (Meja ' . htmlspecialchars($order->table_number) . ')</span>';
                echo '<span class="font-medium">' . format_rupiah($order->total_amount) . '</span>';
                echo '<span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700">' . htmlspecialchars($order->order_status) . '</span>';
                echo '</div>';
             }
             */ ?>
        </div>
    </div>

    <div class="bg-white p-4 rounded shadow border">
         <h4 class="text-lg font-semibold mb-4 border-b pb-2">Aktivitas Lain</h4>
         <div class="text-gray-500 text-center py-10 italic">Grafik atau aktivitas lain bisa ditampilkan di sini.</div>
         <?php /* Bisa pakai Chart.js atau library lain */ ?>
     </div>
</div>

<?php
// Akhiri tangkapan output
$content = ob_get_clean();

// Sertakan layout admin
require APPROOT . '/views/layouts/admin.php';
?>