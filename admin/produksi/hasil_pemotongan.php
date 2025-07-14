<?php
require_once __DIR__ . '/../includes/header.php';

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
                    WHERE pp.status = 'dikirim'");

// Proses hasil pemotongan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pengiriman = $conn->real_escape_string($_POST['id_pengiriman']);
    $jumlah_hasil = $conn->real_escape_string($_POST['jumlah_hasil']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update status pengiriman
        $update = $conn->query("UPDATE pengiriman_pemotong SET status = 'selesai', tanggal_diterima = '$tanggal' 
                             WHERE id_pengiriman_potong = $id_pengiriman");

        if (!$update) {
            throw new Exception("Gagal mengupdate status pengiriman: " . $conn->error);
        }

        // Catat hasil pemotongan
        $sql = "INSERT INTO hasil_pemotongan (id_pengiriman_potong, jumlah_hasil, tanggal_selesai)
                VALUES ($id_pengiriman, $jumlah_hasil, '$tanggal')";

        if (!$conn->query($sql)) {
            throw new Exception("Gagal mencatat hasil pemotongan: " . $conn->error);
        }

        $conn->commit();
        $_SESSION['success'] = "Hasil pemotongan berhasil dicatat";
        header("Location: hasil_pemotongan.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
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
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>

                            <form method="post" class="mb-4">
                                <div class="mb-3">
                                    <label for="id_pengiriman" class="form-label">Pengiriman Bahan</label>
                                    <select name="id_pengiriman" id="id_pengiriman" class="form-select" required>
                                        <option value="">- Pilih Pengiriman -</option>
                                        <?php foreach ($pengiriman as $p): ?>
                                            <option value="<?= $p['id_pengiriman_potong'] ?>"> Tanggal
                                                <?= dateIndo($p['tanggal_kirim']) ?> |
                                                <?= htmlspecialchars($p['nama_bahan']) ?> ke
                                                <?= htmlspecialchars($p['nama_pemotong']) ?> |
                                                <?= number_format($p['jumlah_bahan']) ?> <?= htmlspecialchars($p['satuan']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="jumlah_hasil" class="form-label">Jumlah Hasil (pcs bahan mentah)</label>
                                        <input type="number" name="jumlah_hasil" id="jumlah_hasil" class="form-control" required min="1">
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label for="tanggal" class="form-label">Tanggal Selesai <span class="text-danger">(Bulan/Tanggal/Tahun)</span></label>
                                        <input type="date" name="tanggal" id="tanggal" class="form-control" required value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">Catat Hasil</button>
                                    <div class="btn-group">
                                        <a href="riwayat_hasil_pemotongan.php" class="btn btn-secondary">Riwayat Hasil</a>
                                        <a href="batal_hasil_potong.php" class="btn btn-danger"
                                            onclick="return confirm('Yakin ingin membatalkan hasil pemotongan terakhir?')">
                                            Batal Catat Hasil
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <hr>

                            <h4 class="mb-3">Riwayat Hasil Pemotongan</h4>
                            <?php
                            // $riwayat = query("SELECT h.*, p.nama_bahan, pm.nama_pemotong 
                            //                 FROM hasil_pemotongan h
                            //                 JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
                            //                 JOIN bahan_baku p ON pg.id_bahan = p.id_bahan
                            //                 JOIN pemotong pm ON pg.id_pemotong = pm.id_pemotong
                            //                 ORDER BY h.tanggal_selesai DESC LIMIT 5");

                            $riwayat = query("SELECT h.*, p.nama_bahan, p.satuan, pm.nama_pemotong, pg.jumlah_bahan
                                            FROM hasil_pemotongan h
                                            JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
                                            JOIN bahan_baku p ON pg.id_bahan = p.id_bahan
                                            JOIN pemotong pm ON pg.id_pemotong = pm.id_pemotong
                                            ORDER BY h.tanggal_selesai DESC LIMIT 5");

                            ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th>Tanggal</th>
                                            <th>Bahan Baku</th>
                                            <th>Pemotong</th>
                                            <th class="text-center">Bahan Digunakan</th> <!-- baru -->
                                            <th class="text-center">Jumlah Hasil (pcs)</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($riwayat as $r): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= dateIndo($r['tanggal_selesai']) ?></td>
                                                <td><?= htmlspecialchars($r['nama_bahan']) ?></td>
                                                <td><?= htmlspecialchars($r['nama_pemotong']) ?></td>
                                                <!-- <td class="text-center"><?= number_format($r['jumlah_bahan']) ?></td> -->
                                                <td class="text-center"><?= number_format($r['jumlah_bahan']) ?> <?= htmlspecialchars($r['satuan']) ?></td>
                                                <td class="text-center"><?= number_format($r['jumlah_hasil']) ?></td>
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