<?php
require_once __DIR__ . '../../includes/header.php';

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

// Ambil data pengiriman yang belum selesai
$pengiriman = query("SELECT pp.id_pengiriman_potong, pp.tanggal_kirim, pp.jumlah_bahan, 
                    b.nama_bahan, b.satuan, p.nama_pemotong
                    FROM pengiriman_pemotong pp
                    JOIN bahan_baku b ON pp.id_bahan = b.id_bahan
                    JOIN pemotong p ON pp.id_pemotong = p.id_pemotong
                    WHERE pp.status = 'dikirim' OR pp.status = 'selesai'
                    ORDER BY pp.tanggal_kirim DESC");

// Proses hasil pemotongan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pengiriman = $conn->real_escape_string($_POST['id_pengiriman']);
    $jumlah_hasil = $conn->real_escape_string($_POST['jumlah_hasil']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);

    // Update status pengiriman
    $conn->query("UPDATE pengiriman_pemotong SET status = 'selesai', tanggal_diterima = '$tanggal' 
                 WHERE id_pengiriman_potong = $id_pengiriman");

    // Catat hasil pemotongan
    $sql = "INSERT INTO hasil_pemotongan (id_pengiriman_potong, jumlah_hasil, tanggal_selesai)
            VALUES ($id_pengiriman, $jumlah_hasil, '$tanggal')";

    if ($conn->query($sql)) {
        $_SESSION['success'] = "Hasil pemotongan berhasil dicatat";
        header("Location: hasil_pemotongan.php");
        exit();
    } else {
        $error = "Gagal mencatat hasil pemotongan: " . $conn->error;
    }
}
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
                            <h2>2. Tambah Data Hasil Pemotongan</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <a href="pengiriman_pemotong.php" class="btn btn-outline-warning">Kembali</a>
                                <a href="pengiriman_penjahit.php" class="btn btn-outline-primary">Next</a>
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

                            <form method="post" class="mb-4">

                                <div class="mb-3">
                                    <label for="id_pengiriman" class="form-label">Pengiriman Bahan</label>
                                    <select name="id_pengiriman" id="id_pengiriman" class="form-select" required>
                                        <option value="">- Pilih Pengiriman -</option>
                                        <?php foreach ($pengiriman as $p): ?>
                                            <option value="<?= $p['id_pengiriman_potong'] ?>">
                                                <?= dateIndo($p['tanggal_kirim']) ?> -
                                                <?= htmlspecialchars($p['nama_bahan']) ?> ke
                                                <?= htmlspecialchars($p['nama_pemotong']) ?>
                                                (<?= number_format($p['jumlah_bahan']) ?> <?= htmlspecialchars($p['satuan']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="jumlah_hasil" class="form-label">Jumlah Hasil (pcs bahan mentah)</label>
                                        <input type="number" name="jumlah_hasil" id="jumlah_hasil" class="form-control" required>
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label for="tanggal" class="form-label">Tanggal Selesai</label>
                                        <input type="date" name="tanggal" id="tanggal" class="form-control" required value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">Catat Hasil</button>
                                    <a href="riwayat_hasil_pemotongan.php" class="btn btn-secondary">Riwayat Hasil</a>
                                </div>
                            </form>

                            <h3 class="mb-3">Riwayat Hasil Pemotongan</h3>
                            <?php
                            $riwayat = query("SELECT h.*, p.nama_bahan, pm.nama_pemotong 
                                                    FROM hasil_pemotongan h
                                                    JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
                                                    JOIN bahan_baku p ON pg.id_bahan = p.id_bahan
                                                    JOIN pemotong pm ON pg.id_pemotong = pm.id_pemotong
                                                    ORDER BY h.tanggal_selesai DESC");
                            ?>
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
                                                <td><?= dateIndo($p['tanggal_kirim']) ?></td>
                                                <td><?= $r['nama_bahan'] ?></td>
                                                <td><?= $r['nama_pemotong'] ?></td>
                                                <td class="text-center"><?= $r['jumlah_hasil'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
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