<?php
require_once __DIR__ . '../../includes/header.php';

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_bahan = $conn->real_escape_string($_POST['id_bahan']);
    $id_pemotong = $conn->real_escape_string($_POST['id_pemotong']);
    $jumlah = $conn->real_escape_string($_POST['jumlah']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);

    // Cek stok cukup
    $cek_stok = $conn->query("SELECT jumlah_stok FROM bahan_baku WHERE id_bahan = $id_bahan");
    $stok = $cek_stok->fetch_assoc();

    if ($stok['jumlah_stok'] >= $jumlah) {
        $sql = "INSERT INTO pengiriman_pemotong (id_bahan, id_pemotong, jumlah_bahan, tanggal_kirim)
                VALUES ($id_bahan, $id_pemotong, $jumlah, '$tanggal')";

        if ($conn->query($sql)) {
            $_SESSION['success'] = "Pengiriman ke pemotong berhasil dicatat";
            header("Location: pengiriman_pemotong.php");
            exit();
        } else {
            $error = "Gagal mencatat pengiriman: " . $conn->error;
        }
    } else {
        $error = "Stok bahan tidak mencukupi! Stok tersedia: " . $stok['jumlah_stok'];
    }
}

// Ambil data untuk dropdown
$bahan = query("SELECT * FROM bahan_baku WHERE jumlah_stok > 0");
$pemotong = query("SELECT * FROM pemotong");
$pengiriman = query("SELECT p.*, b.nama_bahan, pm.nama_pemotong 
                    FROM pengiriman_pemotong p
                    JOIN bahan_baku b ON p.id_bahan = b.id_bahan
                    JOIN pemotong pm ON p.id_pemotong = pm.id_pemotong
                    ORDER BY p.tanggal_kirim DESC");
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
                            <h2>1. Tambah Data Pengiriman Pemotong</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <!-- <a href="#" class="btn btn-outline-warning">Kembali</a> -->
                                <a href="hasil_pemotongan.php" class="btn btn-outline-primary">Next</a>
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

                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Bahan Baku</label>
                                    <select name="id_bahan" class="form-select" required>
                                        <option value="">- Pilih Bahan -</option>
                                        <?php foreach ($bahan as $b): ?>
                                            <option value="<?= $b['id_bahan'] ?>">
                                                <?= $b['nama_bahan'] ?> (Stok: <?= $b['jumlah_stok'] ?> <?= $b['satuan'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Pemotong</label>
                                    <select name="id_pemotong" class="form-select" required>
                                        <option value="">- Pilih Pemotong -</option>
                                        <?php foreach ($pemotong as $p): ?>
                                            <option value="<?= $p['id_pemotong'] ?>"><?= $p['nama_pemotong'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jumlah</label>
                                    <input type="number" name="jumlah" step="0.01" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tanggal Pengiriman</label>
                                    <input type="date" name="tanggal" class="form-control" required value="<?= date('Y-m-d') ?>">
                                </div>

                                <button type="submit" class="btn btn-primary">Simpan Pengiriman</button>
                            </form>

                            <hr class="my-4">

                            <h4>Riwayat Pengiriman</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th>Tanggal</th>
                                            <th>Bahan Baku</th>
                                            <th>Pemotong</th>
                                            <th>Jumlah</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($pengiriman as $p): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= date('d/m/Y', strtotime($p['tanggal_kirim'])) ?></td>
                                                <td><?= $p['nama_bahan'] ?></td>
                                                <td><?= $p['nama_pemotong'] ?></td>
                                                <td><?= $p['jumlah_bahan'] ?></td>
                                                <!-- <td><?= ucfirst($p['status']) ?></td> -->
                                                <td class="text-center">
                                                    <span class="badge <?= $p['status'] == 'dikirim' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                        <?= $p['status'] == 'dikirim' ? 'Dalam Proses' : 'Selesai' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($pengiriman)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Belum ada data pengiriman.</td>
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