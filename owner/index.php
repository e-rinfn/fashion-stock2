<?php
// owner/index.php
require_once '../config/functions.php';
require_once './includes/header.php';

// Query data statistik
$total_produk = query("SELECT COUNT(*) as total FROM produk")[0]['total'];
$total_reseller = query("SELECT COUNT(*) as total FROM reseller")[0]['total'];
$penjualan_hari_ini = query("SELECT SUM(total_harga) as total FROM penjualan WHERE DATE(tanggal_penjualan) = CURDATE()")[0]['total'] ?? 0;
$penjualan_bulan_ini = query("SELECT SUM(total_harga) as total FROM penjualan WHERE MONTH(tanggal_penjualan) = MONTH(CURDATE())")[0]['total'] ?? 0;

// Data untuk chart (contoh: penjualan 7 hari terakhir)
$penjualan_7hari = query("
    SELECT DATE(tanggal_penjualan) as tanggal, SUM(total_harga) as total 
    FROM penjualan 
    WHERE tanggal_penjualan >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(tanggal_penjualan)
    ORDER BY tanggal
");

// Data stok bahan baku
$stok_bahan = query("SELECT nama_bahan, jumlah_stok FROM bahan_baku ORDER BY jumlah_stok ASC LIMIT 5");
?>


<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            <?php include './includes/sidebar.php'; ?>
            <!-- / Sidebar -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <?php include './includes/navbar.php'; ?>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <!-- Statistik Ringkas -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">Total Produk</h5>
                                            <h2 class="card-text"><?= $total_produk ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">Total Reseller</h5>
                                            <h2 class="card-text"><?= $total_reseller ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">Penjualan Hari Ini</h5>
                                            <h2 class="card-text"><?= formatRupiah($penjualan_hari_ini) ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-dark">
                                        <div class="card-body">
                                            <h5 class="card-title">Penjualan Bulan Ini</h5>
                                            <h2 class="card-text"><?= formatRupiah($penjualan_bulan_ini) ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>




                            <!-- Stok Bahan Baku -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">

                                        <!-- Grafik Penjualan -->

                                        <div class="card-header">
                                            <h5>Penjualan 7 Hari Terakhir</h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="salesChart" width="auto"></canvas>
                                        </div>


                                        <!-- <div class="card-header">
                                            <h5>Stok Bahan Baku Terendah</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Nama Bahan</th>
                                                            <th>Stok</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($stok_bahan as $bahan): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($bahan['nama_bahan']) ?></td>
                                                                <td><?= $bahan['jumlah_stok'] ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div> -->
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Aksi Cepat</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-grid gap-2">
                                                <a href="laporan/penjualan.php" class="btn btn-primary">Laporan Penjualan</a>
                                                <a href="laporan/produksi.php" class="btn btn-secondary">Laporan Produksi</a>
                                                <a href="laporan/keuangan.php" class="btn btn-success">Laporan Keuangan</a>
                                            </div>
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


    <!-- Core JS footer -->

    <!-- /Core JS footer -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Grafik Penjualan
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    <?php foreach ($penjualan_7hari as $data): ?> '<?= date('d M', strtotime($data['tanggal'])) ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Total Penjualan',
                    data: [
                        <?php foreach ($penjualan_7hari as $data): ?>
                            <?= $data['total'] ?? 0 ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>

<?php include './includes/footer.php'; ?>

</html>