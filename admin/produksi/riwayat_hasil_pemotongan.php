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

// Ambil filter GET
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$id_pemotong = $_GET['pemotong'] ?? '';
$id_bahan = $_GET['bahan'] ?? '';

// Ambil data filter untuk select option
$list_pemotong = query("SELECT * FROM pemotong ORDER BY nama_pemotong");
$list_bahan = query("SELECT * FROM bahan_baku ORDER BY nama_bahan");

// Query riwayat dengan filter
$where = [];

if ($tgl_awal && $tgl_akhir) {
    $where[] = "h.tanggal_selesai BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}
if ($id_pemotong) {
    $where[] = "pg.id_pemotong = $id_pemotong";
}
if ($id_bahan) {
    $where[] = "pg.id_bahan = $id_bahan";
}

$where_sql = count($where) > 0 ? "WHERE " . implode(' AND ', $where) : "";

$riwayat = query("SELECT h.*, p.nama_bahan, pm.nama_pemotong 
    FROM hasil_pemotongan h
    JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
    JOIN bahan_baku p ON pg.id_bahan = p.id_bahan
    JOIN pemotong pm ON pg.id_pemotong = pm.id_pemotong
    $where_sql
    ORDER BY h.tanggal_selesai DESC");
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
                            <h2>Riwayat Data Hasil Pemotongan</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <!-- <a href="#" class="btn btn-outline-warning">Kembali</a> -->
                                <a href="hasil_pemotongan.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </div>

                        <div class="card p-4 shadow-sm">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>




                            <form class="row g-3 mb-4" method="get">
                                <div class="col-md-3">
                                    <label for="tgl_awal" class="form-label">Tanggal Awal</label>
                                    <input type="date" id="tgl_awal" name="tgl_awal" class="form-control" value="<?= htmlspecialchars($tgl_awal) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="tgl_akhir" class="form-label">Tanggal Akhir</label>
                                    <input type="date" id="tgl_akhir" name="tgl_akhir" class="form-control" value="<?= htmlspecialchars($tgl_akhir) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="pemotong" class="form-label">Pemotong</label>
                                    <select name="pemotong" id="pemotong" class="form-select">
                                        <option value="">-- Semua Pemotong --</option>
                                        <?php foreach ($list_pemotong as $p): ?>
                                            <option value="<?= $p['id_pemotong'] ?>" <?= $id_pemotong == $p['id_pemotong'] ? 'selected' : '' ?>>
                                                <?= $p['nama_pemotong'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="bahan" class="form-label">Bahan Baku</label>
                                    <select name="bahan" id="bahan" class="form-select">
                                        <option value="">-- Semua Bahan --</option>
                                        <?php foreach ($list_bahan as $b): ?>
                                            <option value="<?= $b['id_bahan'] ?>" <?= $id_bahan == $b['id_bahan'] ? 'selected' : '' ?>>
                                                <?= $b['nama_bahan'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary me-2">Terapkan Filter</button>
                                    <a href="riwayat_hasil_pemotongan.php" class="btn btn-secondary">Reset</a>
                                </div>
                            </form>


                            <!-- tabel -->
                            <h4 class="mb-3">Tabel Riwayat Hasil Pemotongan</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th>Tanggal</th>
                                            <th>Bahan Baku</th>
                                            <th>Pemotong</th>
                                            <th class="text-center">Jumlah Hasil (pcs)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($riwayat as $r): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= dateIndo($r['tanggal_selesai']) ?></td>
                                                <td><?= $r['nama_bahan'] ?></td>
                                                <td><?= $r['nama_pemotong'] ?></td>
                                                <td class="text-center"><?= $r['jumlah_hasil'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($riwayat)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Belum ada data hasil pemotongan.</td>
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