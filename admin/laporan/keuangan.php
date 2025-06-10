<?php
$pageTitle = "Laporan Keuangan";
require_once '../includes/header.php';

// Filter bulan
$bulan = $_GET['bulan'] ?? date('Y-m');
$startDate = date('Y-m-01', strtotime($bulan));
$endDate = date('Y-m-t', strtotime($bulan));

// Query data keuangan
$pemasukan_lunas = query("SELECT SUM(total_harga) as total FROM penjualan 
                         WHERE status_pembayaran = 'lunas'
                         AND tanggal_penjualan BETWEEN '$startDate' AND '$endDate'")[0]['total'] ?? 0;

$pemasukan_belum_lunas = query("SELECT SUM(total_harga) as total FROM penjualan 
                               WHERE status_pembayaran = 'cicilan'
                               AND tanggal_penjualan BETWEEN '$startDate' AND '$endDate'")[0]['total'] ?? 0;

$pengeluaran = query("SELECT SUM(total_harga) as total FROM pembelian_bahan
                     WHERE tanggal_pembelian BETWEEN '$startDate' AND '$endDate'")[0]['total'] ?? 0;

$laba = $pemasukan_lunas - $pengeluaran;

// Detail transaksi
$transaksi = query("
    (SELECT 'Penjualan' as jenis, tanggal_penjualan as tanggal, 
            CONCAT('Invoice #', id_penjualan) as keterangan, 
            total_harga as jumlah, 
            CASE 
                WHEN status_pembayaran = 'lunas' THEN 'pemasukan-lunas'
                ELSE 'pemasukan-belum-lunas'
            END as tipe,
            status_pembayaran
     FROM penjualan
     WHERE tanggal_penjualan BETWEEN '$startDate' AND '$endDate')
    
    UNION ALL
    
    (SELECT 'Pembelian Bahan' as jenis, tanggal_pembelian as tanggal, 
            CONCAT('Pembelian ', nama_bahan) as keterangan, 
            total_harga as jumlah, 'pengeluaran' as tipe,
            NULL as status_pembayaran
     FROM pembelian_bahan pb
     JOIN bahan_baku bb ON pb.id_bahan = bb.id_bahan
     WHERE tanggal_pembelian BETWEEN '$startDate' AND '$endDate')
    
    ORDER BY tanggal DESC
");
?>


<style>
    /* Paksa SweetAlert berada di atas segalanya */
    .swal2-container {
        z-index: 99999 !important;
    }
</style>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            <?php include '../includes/sidebar.php'; ?>
            <!-- / Sidebar -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <?php include '../includes/navbar.php'; ?>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Data Produk</h2>
                        </div>

                        <div class="row">
                            <!-- Card Pemasukan Lunas -->
                            <div class="col-md-4">
                                <div class="card border-start-success mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title text-success">Pemasukan Lunas</h5>
                                        <h2 class="card-text"><?= formatRupiah($pemasukan_lunas) ?></h2>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Pemasukan Belum Lunas -->
                            <div class="col-md-4">
                                <div class="card border-start-warning mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title text-warning">Pemasukan Belum Lunas</h5>
                                        <h2 class="card-text"><?= formatRupiah($pemasukan_belum_lunas) ?></h2>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Pengeluaran -->
                            <div class="col-md-4">
                                <div class="card border-start-primary mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">Total Setelah Lunas</h5>
                                        <h2 class="card-text"><?= formatRupiah($pemasukan_lunas + $pemasukan_belum_lunas) ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Detail Transaksi</h5>
                            </div>
                            <div class="card-body">
                                <form method="get" class="row g-2">
                                    <div class="col-auto">
                                        <input type="month" name="bulan" value="<?= $bulan ?>" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                        <a href="keuangan.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                                    </div>
                                </form>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Jenis</th>
                                                <th>Keterangan</th>
                                                <th>Jumlah</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($transaksi)) : ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">Tidak ada transaksi</td>
                                                </tr>
                                            <?php else : ?>
                                                <?php foreach ($transaksi as $trx) : ?>
                                                    <tr>
                                                        <td><?= date('d/m/Y', strtotime($trx['tanggal'])) ?></td>
                                                        <td><?= htmlspecialchars($trx['jenis']) ?></td>
                                                        <td><?= htmlspecialchars($trx['keterangan']) ?></td>
                                                        <td class="text-end <?= str_contains($trx['tipe'], 'pemasukan') ? 'text-success' : 'text-danger' ?>">
                                                            <?= str_contains($trx['tipe'], 'pemasukan') ? '+' : '-' ?>
                                                            <?= formatRupiah($trx['jumlah']) ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php if ($trx['jenis'] === 'Penjualan') : ?>
                                                                <span class="badge bg-<?= $trx['status_pembayaran'] === 'lunas' ? 'success' : 'warning' ?>">
                                                                    <?= $trx['status_pembayaran'] === 'lunas' ? 'Lunas' : 'Belum Lunas' ?>
                                                                </span>
                                                            <?php else : ?>
                                                                <span class="badge bg-secondary">Pengeluaran</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="table-active">
                                                    <th colspan="3" class="text-end">TOTAL LABA/RUGI</th>
                                                    <th class="text-center fs-6 <?= $laba >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        <?= formatRupiah($laba) ?>
                                                    </th>
                                                    <th></th>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Pemasukan per Reseller</h5>
                                    </div>
                                    <hr>
                                    <div class="card-body text-center">
                                        <canvas id="pemasukanChart" style="max-width: 100%; max-height: 400px; width: 100%;" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- / Content -->

                <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart Pemasukan per Reseller
            const pemasukanCtx = document.getElementById('pemasukanChart').getContext('2d');
            new Chart(pemasukanCtx, {
                type: 'pie',
                data: {
                    labels: <?= json_encode(array_column(query("SELECT nama_reseller, SUM(total_harga) as total FROM penjualan p JOIN reseller r ON p.id_reseller = r.id_reseller WHERE p.tanggal_penjualan BETWEEN '$startDate' AND '$endDate' AND p.status_pembayaran = 'lunas' GROUP BY p.id_reseller"), 'nama_reseller')) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column(query("SELECT SUM(total_harga) as total FROM penjualan p JOIN reseller r ON p.id_reseller = r.id_reseller WHERE p.tanggal_penjualan BETWEEN '$startDate' AND '$endDate' AND p.status_pembayaran = 'lunas' GROUP BY p.id_reseller"), 'total')) ?>,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(255, 206, 86, 0.7)'
                        ]
                    }]
                }
            });
        });
    </script>

    <!-- Core JS footer -->
    <?php include '../includes/footer.php'; ?>
    <!-- /Core JS footer -->


</body>

</html>