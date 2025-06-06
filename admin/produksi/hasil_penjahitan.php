<?php
require_once __DIR__ . '../../includes/header.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';
// redirectIfNotLoggedIn();
// checkRole('admin');

// Ambil data pengiriman yang belum selesai
$sql_pengiriman = "SELECT pj.id_pengiriman_jahit, pj.jumlah_bahan_mentah, 
                   p.nama_penjahit, hp.jumlah_hasil,
                   DATE_FORMAT(pj.tanggal_kirim, '%d-%m-%Y') as tgl_kirim
                   FROM pengiriman_penjahit pj
                   JOIN penjahit p ON pj.id_penjahit = p.id_penjahit
                   JOIN hasil_pemotongan hp ON pj.id_hasil_potong = hp.id_hasil_potong
                   WHERE pj.status = 'dikirim'";
$pengiriman = query($sql_pengiriman);

// Ambil data produk
$produk = query("SELECT * FROM produk ORDER BY nama_produk");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pengiriman = intval($_POST['id_pengiriman']);
    $id_produk = intval($_POST['id_produk']);
    $jumlah = intval($_POST['jumlah']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
    $keterangan = $conn->real_escape_string($_POST['keterangan']);

    // Mulai transaksi
    $conn->autocommit(FALSE);

    try {
        // 1. Catat hasil penjahitan
        $sql1 = "INSERT INTO hasil_penjahitan 
                (id_pengiriman_jahit, jumlah_produk_jadi, id_produk, tanggal_selesai, keterangan)
                VALUES ($id_pengiriman, $jumlah, $id_produk, '$tanggal', '$keterangan')";

        if (!$conn->query($sql1)) {
            throw new Exception("Gagal mencatat hasil: " . $conn->error);
        }

        // 2. Update status pengiriman jadi selesai
        $sql2 = "UPDATE pengiriman_penjahit SET 
                status = 'selesai', 
                tanggal_diterima = '$tanggal'
                WHERE id_pengiriman_jahit = $id_pengiriman";

        if (!$conn->query($sql2)) {
            throw new Exception("Gagal update status: " . $conn->error);
        }

        // 3. Update stok produk
        $sql3 = "UPDATE produk SET stok = stok + $jumlah WHERE id_produk = $id_produk";

        if (!$conn->query($sql3)) {
            throw new Exception("Gagal update stok: " . $conn->error);
        }

        // Commit transaksi
        $conn->commit();
        $_SESSION['success'] = "Hasil penjahitan berhasil dicatat";
        header("Location: hasil_penjahitan.php");
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
                            <h2>Tambah Data Hasil Penjahitan</h2>
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
                                    <label for="pengiriman-select" class="form-label">Pilih Pengiriman</label>
                                    <select name="id_pengiriman" id="pengiriman-select" class="form-select" required>
                                        <option value="">-- Pilih Pengiriman --</option>
                                        <?php foreach ($pengiriman as $p): ?>
                                            <option value="<?= $p['id_pengiriman_jahit'] ?>"
                                                data-jumlah="<?= $p['jumlah_bahan_mentah'] ?>">
                                                <?= "ID: {$p['id_pengiriman_jahit']} - {$p['nama_penjahit']} - {$p['jumlah_bahan_mentah']} pcs (Kirim: {$p['tgl_kirim']})" ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="produk-select" class="form-label">Produk Jadi</label>
                                    <select name="id_produk" id="produk-select" class="form-select" required>
                                        <option value="">-- Pilih Produk --</option>
                                        <?php foreach ($produk as $p): ?>
                                            <option value="<?= $p['id_produk'] ?>"><?= $p['nama_produk'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jumlah Produk Jadi (pcs)</label>
                                    <input type="number" name="jumlah" min="1" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">Catat Hasil</button>
                            </form>

                            <hr class="my-4">

                            <h3>Riwayat Hasil Penjahitan</h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Produk</th>
                                            <th>Jumlah Jadi</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_history = "SELECT hp.*, p.nama_produk, 
                        DATE_FORMAT(hp.tanggal_selesai, '%d-%m-%Y') as tgl_selesai
                        FROM hasil_penjahitan hp
                        JOIN produk p ON hp.id_produk = p.id_produk
                        ORDER BY hp.tanggal_selesai DESC";
                                        $history = query($sql_history);
                                        $no = 1;
                                        foreach ($history as $h):
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= $h['tgl_selesai'] ?></td>
                                                <td><?= $h['nama_produk'] ?></td>
                                                <td><?= $h['jumlah_produk_jadi'] ?> pcs</td>
                                                <td><?= $h['keterangan'] ?></td>
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