// File: public/assets/js/admin-reports.js

document.addEventListener('DOMContentLoaded', () => {
    // --- Contoh Sales Chart (Line) ---
    const salesChartCtx = document.getElementById('salesChart')?.getContext('2d');
    if (salesChartCtx && typeof Chart !== 'undefined') { // Pastikan Chart.js sudah dimuat
        // Ambil data dari variabel global JS yang di-render oleh PHP View
        // atau dari data attributes pada canvas element
        // Contoh data dummy:
        const salesChartData = window.salesReportData || {
            labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            revenue: [120000, 190000, 150000, 250000, 230000, 310000, 280000],
            orders: [5, 8, 6, 10, 9, 12, 11]
        };

        new Chart(salesChartCtx, {
            type: 'line',
            data: {
                labels: salesChartData.labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: salesChartData.revenue,
                    borderColor: 'rgb(79, 70, 229)', // Indigo
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.1,
                    fill: true,
                    yAxisID: 'yRevenue' // Kaitkan ke sumbu Y kiri
                }, {
                    label: 'Jumlah Pesanan',
                    data: salesChartData.orders,
                    borderColor: 'rgb(245, 158, 11)', // Amber
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.1,
                     yAxisID: 'yOrders' // Kaitkan ke sumbu Y kanan
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yRevenue: { // Sumbu Y Kiri untuk Revenue
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                             // Format Rupiah
                             callback: function(value, index, values) {
                                 return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
                             }
                        }
                    },
                     yOrders: { // Sumbu Y Kanan untuk Orders
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                             drawOnChartArea: false, // only want the grid lines for one axis to show up
                        },
                        ticks: {
                           stepSize: 1 // Tampilkan angka bulat untuk jumlah order
                        }
                     }
                },
                 plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                     if (context.dataset.yAxisID === 'yRevenue') {
                                         label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed.y);
                                     } else {
                                         label += context.parsed.y;
                                     }
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    } else if (document.getElementById('salesChart')) {
        console.warn('Canvas #salesChart ditemukan, tetapi Chart.js tidak dimuat.');
    }

    // --- Contoh Popular Items Chart (Bar) ---
    const popularItemsCtx = document.getElementById('popularItemsChart')?.getContext('2d');
    if (popularItemsCtx && typeof Chart !== 'undefined') {
        // Contoh data dummy:
         const popularItemsData = window.popularItemsReportData || {
            labels: ['Kopi Susu', 'Nasi Goreng', 'Kentang', 'Teh Tarik', 'Roti Bakar'],
            quantities: [120, 95, 80, 75, 60]
         };

        new Chart(popularItemsCtx, {
            type: 'bar',
            data: {
                labels: popularItemsData.labels,
                datasets: [{
                    label: 'Jumlah Terjual',
                    data: popularItemsData.quantities,
                    backgroundColor: [ // Berikan warna berbeda untuk setiap bar
                        'rgba(79, 70, 229, 0.7)',
                        'rgba(5, 150, 105, 0.7)', // Emerald
                        'rgba(217, 119, 6, 0.7)', // Amber
                        'rgba(219, 39, 119, 0.7)', // Fuchsia
                        'rgba(107, 114, 128, 0.7)' // Gray
                    ],
                    borderColor: [
                        'rgb(79, 70, 229)',
                        'rgb(5, 150, 105)',
                         'rgb(217, 119, 6)',
                         'rgb(219, 39, 119)',
                         'rgb(107, 114, 128)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // Buat bar horizontal agar label panjang muat
                responsive: true,
                maintainAspectRatio: false,
                 scales: {
                    x: {
                        beginAtZero: true,
                         ticks: { stepSize: 10 } // Sesuaikan step
                    }
                },
                plugins: {
                    legend: {
                        display: false // Sembunyikan legend jika tidak perlu
                    }
                }
            }
        });
     } else if (document.getElementById('popularItemsChart')) {
         console.warn('Canvas #popularItemsChart ditemukan, tetapi Chart.js tidak dimuat.');
     }


    // --- Inisialisasi Chart Lain ---

});