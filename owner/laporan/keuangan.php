<?php
$pageTitle = "Laporan Keuangan";
require_once '../includes/header.php';

function dateIndo($tanggal)
{
    $bulanIndo = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    $tanggal = date('Y-m-d', strtotime($tanggal));
    $pecah = explode('-', $tanggal);
    return $pecah[2] . ' ' . $bulanIndo[(int)$pecah[1]] . ' ' . $pecah[0];
}

// Filter bulan
$bulan = $_GET['bulan'] ?? date('Y-m');
$startDate = date('Y-m-01', strtotime($bulan));
$endDate = date('Y-m-t', strtotime($bulan));
$namaBulan = date('F Y', strtotime($bulan));

// Query data keuangan
// Pemasukan dari penjualan produk
$pemasukan_lunas = query("SELECT SUM(total_harga) as total FROM penjualan 
                         WHERE status_pembayaran = 'lunas'
                         AND tanggal_penjualan BETWEEN '$startDate' AND '$endDate'")[0]['total'] ?? 0;

$pemasukan_belum_lunas = query("SELECT SUM(total_harga) as total FROM penjualan 
                               WHERE status_pembayaran = 'cicilan'
                               AND tanggal_penjualan BETWEEN '$startDate' AND '$endDate'")[0]['total'] ?? 0;

// Pemasukan dari penjualan bahan
$pemasukan_lunas_bahan = query("SELECT SUM(total_harga) as total FROM penjualan_bahan 
                         WHERE status_pembayaran = 'lunas'
                         AND tanggal_penjualan_bahan BETWEEN '$startDate' AND '$endDate'")[0]['total'] ?? 0;

$pemasukan_belum_lunas_bahan = query("SELECT SUM(total_harga) as total FROM penjualan_bahan 
                               WHERE status_pembayaran = 'cicilan'
                               AND tanggal_penjualan_bahan BETWEEN '$startDate' AND '$endDate'")[0]['total'] ?? 0;

// Pengeluaran untuk pembelian bahan dan barang
$pengeluaran_bahan = query("SELECT SUM(total_harga) as total FROM pembelian_bahan
                     WHERE tanggal_pembelian BETWEEN '$startDate' AND '$endDate'")[0]['total'] ?? 0;

$pengeluaran_barang = query("SELECT SUM(total_harga) as total FROM pembelian
                     WHERE tanggal_pembelian BETWEEN '$startDate' AND '$endDate'")[0]['total'] ?? 0;

// Hitung total
$total_pengeluaran = $pengeluaran_bahan + $pengeluaran_barang;
$total_pemasukan = $pemasukan_lunas + $pemasukan_belum_lunas + $pemasukan_lunas_bahan + $pemasukan_belum_lunas_bahan;
$laba_bersih = $total_pemasukan - $total_pengeluaran;

// Detail transaksi
$transaksi = query("
    SELECT * FROM (
        -- Data Penjualan Produk
        SELECT 
            'Penjualan Produk' AS jenis, 
            tanggal_penjualan AS tanggal, 
            CONCAT('No. #', id_penjualan) AS keterangan, 
            total_harga AS jumlah, 
            CASE 
                WHEN status_pembayaran = 'lunas' THEN 'pemasukan-lunas'
                ELSE 'pemasukan-belum-lunas'
            END AS tipe,
            status_pembayaran,
            'penjualan' AS kategori,
            id_penjualan AS id_transaksi
        FROM penjualan
        WHERE tanggal_penjualan BETWEEN '$startDate' AND '$endDate'
        
        UNION ALL
        
        -- Data Penjualan Bahan
        SELECT 
            'Penjualan Bahan' AS jenis, 
            tanggal_penjualan_bahan AS tanggal, 
            CONCAT('No. #', id_penjualan_bahan) AS keterangan, 
            total_harga AS jumlah, 
            CASE 
                WHEN status_pembayaran = 'lunas' THEN 'pemasukan-lunas'
                ELSE 'pemasukan-belum-lunas'
            END AS tipe,
            status_pembayaran,
            'penjualan_bahan' AS kategori,
            id_penjualan_bahan AS id_transaksi
        FROM penjualan_bahan
        WHERE tanggal_penjualan_bahan BETWEEN '$startDate' AND '$endDate'
        
        UNION ALL
        
        -- Data Pembelian Bahan
        SELECT 
            'Pembelian Bahan' AS jenis, 
            tanggal_pembelian AS tanggal, 
            CONCAT('Pembelian Bahan #', id_pembelian_bahan) AS keterangan, 
            total_harga AS jumlah, 
            'pengeluaran' AS tipe,
            status_pembayaran,
            'pembelian_bahan' AS kategori,
            id_pembelian_bahan AS id_transaksi
        FROM pembelian_bahan
        WHERE tanggal_pembelian BETWEEN '$startDate' AND '$endDate'

        UNION ALL
        
        -- Data Pembelian Barang
        SELECT 
            'Pembelian Barang' AS jenis, 
            tanggal_pembelian AS tanggal, 
            CONCAT('Pembelian Barang #', id_pembelian) AS keterangan, 
            total_harga AS jumlah, 
            'pengeluaran' AS tipe,
            status_pembayaran,
            'pembelian' AS kategori,
            id_pembelian AS id_transaksi
        FROM pembelian
        WHERE tanggal_pembelian BETWEEN '$startDate' AND '$endDate'
    ) AS transaksi
    ORDER BY tanggal DESC;
");
?>

<style>
    .card-summary {
        transition: transform 0.2s;
    }

    .card-summary:hover {
        transform: translateY(-5px);
    }

    .badge-status {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }

    .table-transaksi {
        font-size: 0.9rem;
    }

    .table-transaksi th {
        white-space: nowrap;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }

    .info-box {
        border-left: 4px solid;
        padding: 0.5rem 1rem;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
    }

    .info-box-primary {
        border-left-color: #0d6efd;
    }

    .info-box-success {
        border-left-color: #198754;
    }

    .info-box-warning {
        border-left-color: #ffc107;
    }

    .info-box-danger {
        border-left-color: #dc3545;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include '../includes/sidebar.php'; ?>

            <div class="layout-page">
                <?php include '../includes/navbar.php'; ?>

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Header dan Filter -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Laporan Keuangan <?= $namaBulan ?></h2>
                            <div>
                                <form method="get" class="d-flex">
                                    <input type="month" name="bulan" value="<?= $bulan ?>" class="form-control me-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="keuangan.php" class="btn btn-outline-secondary ms-2">Reset</a>
                                </form>
                            </div>
                        </div>

                        <!-- Ringkasan Keuangan -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="info-box info-box-primary">
                                    <h5 class="mb-1">Ringkasan Keuangan Bulan Ini</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">Total Pemasukan</small>
                                            <h4 class="text-success"><?= formatRupiah($total_pemasukan) ?></h4>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Total Pengeluaran</small>
                                            <h4 class="text-danger"><?= formatRupiah($total_pengeluaran) ?></h4>
                                            <small class="text-muted d-block">
                                                (Bahan: <?= formatRupiah($pengeluaran_bahan) ?>,
                                                Barang: <?= formatRupiah($pengeluaran_barang) ?>)
                                            </small>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Laba Bersih</small>
                                            <h4 class="<?= $laba_bersih >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= formatRupiah($laba_bersih) ?>
                                            </h4>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Piutang</small>
                                            <h4 class="text-warning"><?= formatRupiah($pemasukan_belum_lunas + $pemasukan_belum_lunas_bahan) ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Ringkasan -->
                        <div class="row mb-4">
                            <!-- Penjualan Produk -->
                            <div class="col-md-4">
                                <div class="card card-summary mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Penjualan Produk</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>Lunas:</span>
                                                    <strong class="text-success"><?= formatRupiah($pemasukan_lunas) ?></strong>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>Belum Lunas:</span>
                                                    <strong class="text-warning"><?= formatRupiah($pemasukan_belum_lunas) ?></strong>
                                                </div>
                                                <hr>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>Total:</span>
                                                    <strong><?= formatRupiah($pemasukan_lunas + $pemasukan_belum_lunas) ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="chart-container">
                                                    <canvas id="chartPenjualanProduk"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Penjualan Bahan -->
                            <div class="col-md-4">
                                <div class="card card-summary mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Penjualan Bahan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>Lunas:</span>
                                                    <strong class="text-success"><?= formatRupiah($pemasukan_lunas_bahan) ?></strong>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>Belum Lunas:</span>
                                                    <strong class="text-warning"><?= formatRupiah($pemasukan_belum_lunas_bahan) ?></strong>
                                                </div>
                                                <hr>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>Total:</span>
                                                    <strong><?= formatRupiah($pemasukan_lunas_bahan + $pemasukan_belum_lunas_bahan) ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="chart-container">
                                                    <canvas id="chartPenjualanBahan"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pembelian -->
                            <div class="col-md-4">
                                <div class="card card-summary mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Pembelian</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>Bahan:</span>
                                                    <strong class="text-danger"><?= formatRupiah($pengeluaran_bahan) ?></strong>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>Barang:</span>
                                                    <strong class="text-danger"><?= formatRupiah($pengeluaran_barang) ?></strong>
                                                </div>
                                                <hr>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>Total:</span>
                                                    <strong class="text-danger"><?= formatRupiah($pengeluaran_bahan + $pengeluaran_barang) ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="chart-container">
                                                    <canvas id="chartPembelian"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Transaksi -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Detail Transaksi</h5>
                                <div>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="printReport()">
                                        <i class="bx bx-printer"></i> Cetak Laporan
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-transaksi table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">Tanggal</th>
                                                <th width="20%">Jenis</th>
                                                <th width="30%">Keterangan</th>
                                                <th width="15%" class="text-end">Jumlah</th>
                                                <th width="15%">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($transaksi)) : ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">Tidak ada transaksi pada periode ini</td>
                                                </tr>
                                            <?php else : ?>
                                                <?php $no = 1; ?>
                                                <?php foreach ($transaksi as $trx) : ?>
                                                    <tr>
                                                        <td class="text-center"><?= $no++ ?></td>
                                                        <td><?= dateIndo($trx['tanggal']) ?></td>
                                                        <td><?= htmlspecialchars($trx['jenis']) ?></td>
                                                        <td><?= htmlspecialchars($trx['keterangan']) ?></td>
                                                        <td class="text-end <?= str_contains($trx['tipe'], 'pemasukan') ? 'text-success' : 'text-danger' ?>">
                                                            <?= str_contains($trx['tipe'], 'pemasukan') ? '+' : '-' ?>
                                                            <?= formatRupiah($trx['jumlah']) ?>
                                                        </td>
                                                        <td>
                                                            <?php if (str_contains($trx['tipe'], 'pemasukan')) : ?>
                                                                <span class="badge badge-status bg-<?= $trx['status_pembayaran'] === 'lunas' ? 'success' : 'warning' ?>">
                                                                    <?= $trx['status_pembayaran'] === 'lunas' ? 'Lunas' : 'Belum Lunas' ?>
                                                                </span>
                                                            <?php else : ?>
                                                                <span class="badge badge-status bg-secondary">
                                                                    <?= $trx['jenis'] === 'Pembelian Bahan' ? 'Pembelian Bahan' : 'Pembelian Barang' ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                        <tfoot class="table-active">
                                            <tr>
                                                <th colspan="4" class="text-end">TOTAL</th>
                                                <th class="text-end <?= $laba_bersih >= 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= formatRupiah($laba_bersih) ?>
                                                </th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Grafik Perbandingan -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Perbandingan Pemasukan & Pengeluaran</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="perbandinganChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart Penjualan Produk
            const ctxProduk = document.getElementById('chartPenjualanProduk').getContext('2d');
            new Chart(ctxProduk, {
                type: 'doughnut',
                data: {
                    labels: ['Lunas', 'Belum Lunas'],
                    datasets: [{
                        data: [<?= $pemasukan_lunas ?>, <?= $pemasukan_belum_lunas ?>],
                        backgroundColor: ['#28a745', '#ffc107'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    return `${label}: ${formatRupiahJS(value)}`;
                                }
                            }
                        }
                    }
                }
            });

            // Chart Penjualan Bahan
            const ctxBahan = document.getElementById('chartPenjualanBahan').getContext('2d');
            new Chart(ctxBahan, {
                type: 'doughnut',
                data: {
                    labels: ['Lunas', 'Belum Lunas'],
                    datasets: [{
                        data: [<?= $pemasukan_lunas_bahan ?>, <?= $pemasukan_belum_lunas_bahan ?>],
                        backgroundColor: ['#28a745', '#ffc107'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    return `${label}: ${formatRupiahJS(value)}`;
                                }
                            }
                        }
                    }
                }
            });

            // Chart Pembelian
            const ctxPembelian = document.getElementById('chartPembelian').getContext('2d');
            new Chart(ctxPembelian, {
                type: 'doughnut',
                data: {
                    labels: ['Bahan', 'Barang'],
                    datasets: [{
                        data: [<?= $pengeluaran_bahan ?>, <?= $pengeluaran_barang ?>],
                        backgroundColor: ['#dc3545', '#fd7e14'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    return `${label}: ${formatRupiahJS(value)}`;
                                }
                            }
                        }
                    }
                }
            });

            // Chart Perbandingan Pemasukan & Pengeluaran
            const perbandinganCtx = document.getElementById('perbandinganChart').getContext('2d');
            new Chart(perbandinganCtx, {
                type: 'bar',
                data: {
                    labels: ['Pemasukan', 'Pengeluaran', 'Laba Bersih'],
                    datasets: [{
                        label: 'Jumlah',
                        data: [<?= $total_pemasukan ?>, <?= $total_pengeluaran ?>, <?= $laba_bersih ?>],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.7)',
                            'rgba(220, 53, 69, 0.7)',
                            'rgba(23, 162, 184, 0.7)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(23, 162, 184, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatRupiahJS(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + formatRupiahJS(context.raw);
                                }
                            }
                        }
                    }
                }
            });
        });

        function formatRupiahJS(number) {
            return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function printReport() {
            window.print();
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>

</html>