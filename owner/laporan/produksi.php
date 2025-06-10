<?php
$pageTitle = "Laporan Produksi";
require_once '../includes/header.php';

// Filter parameter
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$id_pemotong = $_GET['id_pemotong'] ?? 0;
$id_penjahit = $_GET['id_penjahit'] ?? 0;
$id_produk = $_GET['id_produk'] ?? 0;

// Query data untuk dropdown filter
$pemotong = query("SELECT * FROM pemotong ORDER BY nama_pemotong");
$penjahit = query("SELECT * FROM penjahit ORDER BY nama_penjahit");
$produk = query("SELECT * FROM produk ORDER BY nama_produk");

// Query data produksi dengan filter
$sql = "SELECT pp.tanggal_kirim, p.id_pemotong, p.nama_pemotong, h.jumlah_hasil, 
               t.id_penjahit, t.nama_penjahit, hp.jumlah_produk_jadi, 
               pr.id_produk, pr.nama_produk
        FROM pengiriman_pemotong pp
        JOIN pemotong p ON pp.id_pemotong = p.id_pemotong
        JOIN hasil_pemotongan h ON pp.id_pengiriman_potong = h.id_pengiriman_potong
        JOIN pengiriman_penjahit pj ON h.id_hasil_potong = pj.id_hasil_potong
        JOIN penjahit t ON pj.id_penjahit = t.id_penjahit
        JOIN hasil_penjahitan hp ON pj.id_pengiriman_jahit = hp.id_pengiriman_jahit
        JOIN produk pr ON hp.id_produk = pr.id_produk
        WHERE pp.tanggal_kirim BETWEEN '$start_date' AND '$end_date'";

if ($id_pemotong > 0) {
    $sql .= " AND p.id_pemotong = $id_pemotong";
}

if ($id_penjahit > 0) {
    $sql .= " AND t.id_penjahit = $id_penjahit";
}

if ($id_produk > 0) {
    $sql .= " AND pr.id_produk = $id_produk";
}

$sql .= " ORDER BY pp.tanggal_kirim DESC";

$produksi = query($sql);
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
                            <h2>Data Produksi</h2>
                        </div>

                        <!-- Filter Form -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="get" class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Tanggal Mulai</label>
                                        <input type="date" name="start_date" class="form-control"
                                            value="<?= htmlspecialchars($start_date) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Tanggal Selesai</label>
                                        <input type="date" name="end_date" class="form-control"
                                            value="<?= htmlspecialchars($end_date) ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Pemotong</label>
                                        <select name="id_pemotong" class="form-select">
                                            <option value="0">Semua Pemotong</option>
                                            <?php foreach ($pemotong as $potong) : ?>
                                                <option value="<?= $potong['id_pemotong'] ?>"
                                                    <?= ($id_pemotong == $potong['id_pemotong']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($potong['nama_pemotong']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Penjahit</label>
                                        <select name="id_penjahit" class="form-select">
                                            <option value="0">Semua Penjahit</option>
                                            <?php foreach ($penjahit as $jahit) : ?>
                                                <option value="<?= $jahit['id_penjahit'] ?>"
                                                    <?= ($id_penjahit == $jahit['id_penjahit']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($jahit['nama_penjahit']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Produk</label>
                                        <select name="id_produk" class="form-select">
                                            <option value="0">Semua Produk</option>
                                            <?php foreach ($produk as $prod) : ?>
                                                <option value="<?= $prod['id_produk'] ?>"
                                                    <?= ($id_produk == $prod['id_produk']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($prod['nama_produk']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-filter"></i> Filter
                                        </button>
                                        <a href="produksi.php" class="btn btn-secondary">
                                            <i class="bx bx-reset"></i> Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Tabel Produksi -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Data Produksi</h5>
                                <small class="text-muted">
                                    Periode: <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?>
                                </small>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Pemotong</th>
                                                <th>Hasil Potong</th>
                                                <th>Penjahit</th>
                                                <th>Produk Jadi</th>
                                                <th>Jenis Produk</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($produksi)) : ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">Tidak ada data produksi</td>
                                                </tr>
                                            <?php else : ?>
                                                <?php foreach ($produksi as $prod) : ?>
                                                    <tr>
                                                        <td><?= date('d/m/Y', strtotime($prod['tanggal_kirim'])) ?></td>
                                                        <td><?= htmlspecialchars($prod['nama_pemotong']) ?></td>
                                                        <td class="text-end"><?= number_format($prod['jumlah_hasil']) ?> pcs</td>
                                                        <td><?= htmlspecialchars($prod['nama_penjahit']) ?></td>
                                                        <td class="text-end"><?= number_format($prod['jumlah_produk_jadi']) ?> pcs</td>
                                                        <td><?= htmlspecialchars($prod['nama_produk']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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


</body>

</html>