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

$id_pembelian = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data pembelian
$pembelian = query("SELECT p.*, s.nama_supplier
                    FROM pembelian p
                    JOIN supplier s ON p.id_supplier = s.id_supplier
                    WHERE p.id_pembelian = $id_pembelian")[0] ?? null;

if (!$pembelian) {
    header("Location: list.php");
    exit();
}

// Ambil detail pembelian
$detail = query("SELECT d.*, pr.nama_produk 
                FROM detail_pembelian d
                JOIN produk pr ON d.id_produk = pr.id_produk
                WHERE d.id_pembelian = $id_pembelian");

// Hitung total cicilan
$cicilan = query("SELECT SUM(jumlah_cicilan) as total FROM cicilan_pembelian WHERE id_pembelian = $id_pembelian AND status = 'lunas'")[0];
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
                            <h2>Detail Pesanan #<?= $id_pembelian ?></h2>
                            <div class="btn-group mb-3" role="group" aria-label="Aksi Pembelian">
                                <a href="list.php" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back"></i> Kembali
                                </a>
                                <a href="cicilan.php?id=<?= $id_pembelian ?>" class="btn btn-warning">
                                    <i class="bx bx-credit-card"></i> Cicilan
                                </a>
                                <a href="nota.php?id=<?= $id_pembelian ?>" class="btn btn-danger" target="_blank">
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
                                                <th width="30%">Supplier</th>
                                                <td><?= $pembelian['nama_supplier'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal</th>
                                                <td><?= dateIndo($pembelian['tanggal_pembelian']) . ' ' . date('H:i', strtotime($pembelian['tanggal_pembelian'])) ?></td>
                                            </tr>

                                        </table>
                                    </div>
                                    <div class="col-md-8">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Total Harga</th>
                                                <td><?= formatRupiah($pembelian['total_harga']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Status Pembayaran</th>
                                                <td>
                                                    <?= ucfirst($pembelian['status_pembayaran']) ?>
                                                    <?php if ($pembelian['status_pembayaran'] == 'cicilan'): ?> <br>
                                                        Dibayar: <?= formatRupiah($total_cicilan) ?> dari <?= formatRupiah($pembelian['total_harga']) ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <!-- <tr>
                                                <th>Metode Pembayaran</th>
                                                <td><?= ucfirst($pembelian['metode_pembayaran']) ?></td>
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
                                            <th class="fs-6 text-center"><?= formatRupiah($pembelian['total_harga']) ?></th>
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