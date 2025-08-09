<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

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

$id_penjualan_bahan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data penjualan bahan
$penjualan_bahan = query("SELECT p.*, r.nama_reseller 
                    FROM penjualan_bahan p
                    JOIN reseller r ON p.id_reseller = r.id_reseller
                    WHERE p.id_penjualan_bahan = $id_penjualan_bahan")[0] ?? null;

if (!$penjualan_bahan) {
    header("Location: list.php");
    exit();
}

// Ambil detail penjualan
$detail = query("SELECT d.*, pr.nama_bahan 
                FROM detail_penjualan_bahan d
                JOIN bahan_baku pr ON d.id_bahan = pr.id_bahan
                WHERE d.id_penjualan_bahan = $id_penjualan_bahan");

// Hitung total cicilan
$cicilan = query("SELECT SUM(jumlah_cicilan_penjualan_bahan) as total FROM cicilan_penjualan_bahan WHERE id_penjualan_bahan = $id_penjualan_bahan AND status = 'lunas'")[0];
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
                            <h2>Detail Pesanan #<?= $id_penjualan_bahan ?></h2>
                            <div class="btn-group mb-3" role="group" aria-label="Aksi Penjualan">
                                <a href="list.php" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back"></i> Kembali
                                </a>
                                <a href="cicilan.php?id=<?= $id_penjualan_bahan ?>" class="btn btn-warning">
                                    <i class="bx bx-credit-card"></i> Cicilan
                                </a>
                                <a href="nota.php?id=<?= $id_penjualan_bahan ?>" class="btn btn-danger" target="_blank">
                                    <i class="bx bx-printer"></i> Cetak Nota
                                </a>
                            </div>

                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Reseller</th>
                                                <td><?= $penjualan_bahan['nama_reseller'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal</th>
                                                <td><?= dateIndo($penjualan_bahan['tanggal_penjualan_bahan']) . ' ' . date('H:i', strtotime($penjualan_bahan['tanggal_penjualan_bahan'])) ?></td>
                                            </tr>

                                        </table>
                                    </div>
                                    <div class="col-md-8">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Total Harga</th>
                                                <td><?= formatRupiah($penjualan_bahan['total_harga']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Status Pembayaran</th>
                                                <td>
                                                    <?= ucfirst($penjualan_bahan['status_pembayaran']) ?>
                                                    <?php if ($penjualan_bahan['status_pembayaran'] == 'cicilan'): ?> <br>
                                                        Dibayar: <?= formatRupiah($total_cicilan) ?> dari <?= formatRupiah($penjualan_bahan['total_harga']) ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <!-- <tr>
                                                <th>Metode Pembayaran</th>
                                                <td><?= ucfirst($penjualan_bahan['metode_pembayaran']) ?></td>
                                            </tr> -->
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
                                                <td><?= $d['nama_bahan'] ?></td>
                                                <td><?= formatRupiah($d['harga_satuan']) ?></td>
                                                <td><?= $d['jumlah'] ?></td>
                                                <td><?= formatRupiah($d['subtotal']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right">Total</th>
                                            <th class="fs-6 text-center"><?= formatRupiah($penjualan_bahan['total_harga']) ?></th>
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