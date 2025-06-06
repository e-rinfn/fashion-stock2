<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
// redirectIfNotLoggedIn();
// checkRole('admin');

$id_penjualan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data penjualan
$penjualan = query("SELECT p.*, r.nama_reseller 
                    FROM penjualan p
                    JOIN reseller r ON p.id_reseller = r.id_reseller
                    WHERE p.id_penjualan = $id_penjualan")[0] ?? null;

if (!$penjualan) {
    header("Location: list.php");
    exit();
}

// Ambil detail penjualan
$detail = query("SELECT d.*, pr.nama_produk 
                FROM detail_penjualan d
                JOIN produk pr ON d.id_produk = pr.id_produk
                WHERE d.id_penjualan = $id_penjualan");

// Hitung total cicilan
$cicilan = query("SELECT SUM(jumlah_cicilan) as total FROM cicilan WHERE id_penjualan = $id_penjualan AND status = 'lunas'")[0];
$total_cicilan = $cicilan['total'] ?? 0;
?>

<style>
    /* Paksa SweetAlert berada di atas segalanya */
    .swal2-container {
        z-index: 99999 !important;
    }
</style>

<?php include '../includes/header.php'; ?>

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

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Detail Penjualan #<?= $id_penjualan ?></h2>
                            <div>
                                <a href="list.php" class="btn btn-secondary">Kembali</a>
                                <a href="nota.php?id=<?= $id_penjualan ?>" class="btn btn-info" target="_blank">Cetak Nota</a>
                                <a href="cicilan.php?id=<?= $id_penjualan ?>" class="btn btn-primary" target="_blank">Cicilan</a>
                                <!-- <?php if ($penjualan['status_pembayaran'] == 'cicilan'): ?>
                                    <a href="cicilan.php?id=<?= $id_penjualan ?>" class="btn btn-primary">Kelola Cicilan</a>
                                <?php endif; ?> -->
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Reseller</th>
                                                <td><?= $penjualan['nama_reseller'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal</th>
                                                <td><?= date('d/m/Y H:i', strtotime($penjualan['tanggal_penjualan'])) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Status Pembayaran</th>
                                                <td>
                                                    <?= ucfirst($penjualan['status_pembayaran']) ?>
                                                    <?php if ($penjualan['status_pembayaran'] == 'cicilan'): ?>
                                                        (Dibayar: <?= formatRupiah($total_cicilan) ?> dari <?= formatRupiah($penjualan['total_harga']) ?>)
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Total Harga</th>
                                                <td><?= formatRupiah($penjualan['total_harga']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Metode Pembayaran</th>
                                                <td><?= ucfirst($penjualan['metode_pembayaran']) ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3>Daftar Produk</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Produk</th>
                                            <th>Harga Satuan</th>
                                            <th>Qty</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detail as $i => $d): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><?= $d['nama_produk'] ?></td>
                                                <td><?= formatRupiah($d['harga_satuan']) ?></td>
                                                <td><?= $d['jumlah'] ?></td>
                                                <td><?= formatRupiah($d['subtotal']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right">Total</th>
                                            <th class="fs-6 text-center"><?= formatRupiah($penjualan['total_harga']) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
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
    <?php include '../includes/footer.php'; ?>
    <!-- /Core JS footer -->

</body>

</html>