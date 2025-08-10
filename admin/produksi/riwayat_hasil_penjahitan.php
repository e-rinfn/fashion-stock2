<?php
require_once __DIR__ . '../../includes/header.php';
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

// Get filter parameters
$filter_produk   = isset($_GET['produk']) ? intval($_GET['produk']) : 0;
$filter_penjahit = isset($_GET['penjahit']) ? intval($_GET['penjahit']) : 0;
$filter_awal     = isset($_GET['awal']) ? $_GET['awal'] : '';
$filter_akhir    = isset($_GET['akhir']) ? $_GET['akhir'] : '';

// Get product list for filter dropdown
$produk = query("SELECT * FROM produk ORDER BY nama_produk");

// Get penjahit list for filter dropdown
$penjahit = query("SELECT * FROM penjahit ORDER BY nama_penjahit");

// Build filter conditions
$filter_conditions = [];
if ($filter_produk > 0) {
    $filter_conditions[] = "hp.id_produk = $filter_produk";
}
if ($filter_penjahit > 0) {
    $filter_conditions[] = "pj.id_penjahit = $filter_penjahit";
}
if (!empty($filter_awal) && !empty($filter_akhir)) {
    $filter_conditions[] = "hp.tanggal_selesai BETWEEN '$filter_awal' AND '$filter_akhir'";
}

$where_clause = !empty($filter_conditions)
    ? "WHERE " . implode(" AND ", $filter_conditions)
    : "";

// Get sewing history with raw material data
$sql_history = "
                SELECT hp.*, 
                    p.nama_produk, 
                    pj.jumlah_bahan_mentah,
                    pen.nama_penjahit,
                    t.tarif_per_unit,
                    DATE_FORMAT(hp.tanggal_selesai, '%d-%m-%Y') as tgl_selesai
                FROM hasil_penjahitan hp
                JOIN produk p ON hp.id_produk = p.id_produk
                JOIN pengiriman_penjahit pj ON hp.id_pengiriman_jahit = pj.id_pengiriman_jahit
                JOIN penjahit pen ON pj.id_penjahit = pen.id_penjahit
                LEFT JOIN tarif_upah t ON hp.id_tarif = t.id_tarif
                ORDER BY hp.tanggal_selesai DESC 
                LIMIT 5
            ";
$history = query($sql_history);
?>

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
                            <h2>Riwayat Data Hasil Penjahitan</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <a href="hasil_penjahitan.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </div>

                        <div class="card p-4 shadow-sm">
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>

                            <form method="GET" class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Tanggal Awal</label>
                                    <input type="date" name="awal" class="form-control" value="<?= htmlspecialchars($filter_awal) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tanggal Akhir</label>
                                    <input type="date" name="akhir" class="form-control" value="<?= htmlspecialchars($filter_akhir) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Produk</label>
                                    <select name="produk" class="form-select">
                                        <option value="">-- Semua Produk --</option>
                                        <?php foreach ($produk as $p): ?>
                                            <option value="<?= $p['id_produk'] ?>" <?= ($filter_produk == $p['id_produk']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['nama_produk']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Penjahit</label>
                                    <select name="penjahit" class="form-select">
                                        <option value="">-- Semua Penjahit --</option>
                                        <?php foreach ($penjahit as $p): ?>
                                            <option value="<?= $p['id_penjahit'] ?>" <?= ($filter_penjahit == $p['id_penjahit']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['nama_penjahit']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary me-2">Terapkan Filter</button>
                                    <a href="riwayat_hasil_penjahitan.php" class="btn btn-secondary">Reset</a>
                                </div>
                            </form>

                            <h4>Tabel Riwayat Hasil Penjahitan</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Penjahit</th>
                                            <th>Produk</th>
                                            <th>Bahan Mentah</th>
                                            <th>Produk Jadi</th>
                                            <th>Tarif</th>
                                            <th>Total Upah</th>
                                            <th>Keterangan</th>
                                        </tr>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($history)): ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($history as $h): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= dateIndo($h['tgl_selesai']) ?></td>
                                                    <td><?= htmlspecialchars($h['nama_penjahit']) ?></td>
                                                    <td><?= htmlspecialchars($h['nama_produk']) ?></td>
                                                    <td class="text-center"><?= number_format($h['jumlah_bahan_mentah']) ?> pcs</td>
                                                    <td class="text-center"><?= number_format($h['jumlah_produk_jadi']) ?> pcs</td>
                                                    <td class="text-center">Rp <?= number_format($h['tarif_per_unit'] ?? 0, 0, ',', '.') ?></td>
                                                    <td class="text-center">Rp <?= number_format($h['total_upah'] ?? 0, 0, ',', '.') ?></td>
                                                    <td><?= htmlspecialchars($h['keterangan']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center">Belum ada data hasil penjahitan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
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