<?php
// Lokasi File: app/views/admin/reports/index.php
// Dimuat oleh Admin\ReportController::index() (Controller belum dibuat)

ob_start();
// Data: $title, $pageTitle, $reportData, $filters
?>

<div class="mb-6 bg-white p-4 rounded-lg shadow-sm border">
    <form action="<?= url_for('/admin/reports') ?>" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 items-end">
        <div>
            <label for="report_type" class="block text-xs font-medium text-gray-600 mb-1">Jenis Laporan</label>
            <select id="report_type" name="report_type" class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm bg-white" required>
                <option value="sales_summary" <?= (($filters['report_type'] ?? '') == 'sales_summary') ? 'selected' : '' ?>>Ringkasan Penjualan</option>
                <option value="sales_detail" <?= (($filters['report_type'] ?? '') == 'sales_detail') ? 'selected' : '' ?>>Detail Penjualan per Item</option>
                <option value="payment_methods" <?= (($filters['report_type'] ?? '') == 'payment_methods') ? 'selected' : '' ?>>Metode Pembayaran</option>
                <?php // Tambahkan jenis laporan lain ?>
            </select>
        </div>
         <div>
            <label for="start_date" class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai</label>
            <input type="date" id="start_date" name="start_date"
                   value="<?= htmlspecialchars($filters['start_date'] ?? date('Y-m-01')) // Default awal bulan ?>"
                   class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm bg-white" required>
        </div>
         <div>
            <label for="end_date" class="block text-xs font-medium text-gray-600 mb-1">Tanggal Akhir</label>
            <input type="date" id="end_date" name="end_date"
                   value="<?= htmlspecialchars($filters['end_date'] ?? date('Y-m-d')) // Default hari ini ?>"
                   class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm bg-white" required>
        </div>
        <div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1.5 px-4 rounded-md text-sm">Tampilkan Laporan</button>
        </div>
    </form>
</div>

<div id="report-results" class="mt-8">
    <?php if (isset($reportData)): ?>
        <?php
        // Di sini Anda akan memproses $reportData berdasarkan $filters['report_type']
        // dan menampilkan tabel atau grafik yang sesuai.
        // Contoh: Tampilkan tabel jika reportData adalah array
        if (is_array($reportData) && !empty($reportData)) {
            // Dapatkan header tabel dari key array pertama
            $headers = array_keys((array) $reportData[0]);
            echo '<div class="overflow-x-auto bg-white rounded-lg shadow">';
            echo '<table class="min-w-full">';
            echo '<thead class="bg-gray-100"><tr class="text-left">';
            foreach($headers as $header) {
                echo '<th class="py-2 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">' . ucfirst(str_replace('_', ' ', $header)) . '</th>';
            }
            echo '</tr></thead>';
            echo '<tbody class="divide-y divide-gray-200">';
            foreach($reportData as $row) {
                echo '<tr class="hover:bg-gray-50">';
                foreach((array) $row as $cell) {
                    // Format cell jika perlu (misal angka jadi rupiah)
                    $formattedCell = is_numeric($cell) ? format_rupiah($cell) : htmlspecialchars($cell);
                    echo '<td class="py-3 px-4 text-sm text-gray-700">' . $formattedCell . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table></div>';
        } elseif (!empty($reportData)) {
             // Mungkin grafik? Perlu library JS (Chart.js) dan data JSON
             echo '<div class="bg-white p-6 rounded-lg shadow text-center text-gray-700">';
             echo 'Tampilan grafik untuk laporan ini belum diimplementasikan.';
             echo '<canvas id="reportChart" class="mt-4"></canvas>'; // Placeholder canvas
             // Kirim data JSON ke JS untuk render chart
             // echo '<script> const chartData = ' . json_encode($reportData) . '; /* ... render chart ... */ </script>';
             echo '</div>';
        } else {
             echo '<div class="bg-yellow-100 text-yellow-700 p-4 rounded-lg text-center">Tidak ada data ditemukan untuk periode atau jenis laporan yang dipilih.</div>';
        }
        ?>
    <?php else: ?>
        <div class="bg-blue-100 text-blue-700 p-4 rounded-lg text-center">
            Silakan pilih jenis laporan dan periode tanggal, lalu klik "Tampilkan Laporan".
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
// $pageScript = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>'; // Load Chart.js jika perlu
require APPROOT . '/views/layouts/admin.php';
?>