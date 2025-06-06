<?php
require_once __DIR__ . '../../includes/header.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';
// redirectIfNotLoggedIn();
// checkRole('admin');

// Ambil data hasil pemotongan yang siap dikirim
$sql_hasil = "SELECT h.id_hasil_potong, h.jumlah_hasil, p.nama_pemotong, 
              DATE_FORMAT(h.tanggal_selesai, '%d-%m-%Y') as tgl_selesai
              FROM hasil_pemotongan h
              JOIN pengiriman_pemotong pp ON h.id_pengiriman_potong = pp.id_pengiriman_potong
              JOIN pemotong p ON pp.id_pemotong = p.id_pemotong
              LEFT JOIN pengiriman_penjahit pj ON h.id_hasil_potong = pj.id_hasil_potong
              WHERE pj.id_pengiriman_jahit IS NULL";
$hasil_potong = query($sql_hasil);

// Ambil data penjahit
$penjahit = query("SELECT * FROM penjahit ORDER BY nama_penjahit");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_hasil = intval($_POST['id_hasil_potong']);
    $id_penjahit = intval($_POST['id_penjahit']);
    $jumlah = intval($_POST['jumlah']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);

    // Validasi jumlah tidak melebihi stok
    $sql_check = "SELECT jumlah_hasil FROM hasil_pemotongan WHERE id_hasil_potong = $id_hasil";
    $stok = query($sql_check)[0]['jumlah_hasil'];

    if ($jumlah > $stok) {
        $error = "Jumlah melebihi stok bahan mentah (Stok: $stok)";
    } else {
        $sql = "INSERT INTO pengiriman_penjahit 
                (id_hasil_potong, id_penjahit, jumlah_bahan_mentah, tanggal_kirim)
                VALUES ($id_hasil, $id_penjahit, $jumlah, '$tanggal')";

        if ($conn->query($sql)) {
            $_SESSION['success'] = "Pengiriman ke penjahit berhasil dicatat";
            header("Location: pengiriman_penjahit.php");
            exit();
        } else {
            $error = "Gagal mencatat pengiriman: " . $conn->error;
        }
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
                            <h2>Tambah Data Pengiriman Penjahit</h2>
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
                                    <label class="form-label">Pilih Hasil Potongan</label>
                                    <select name="id_hasil_potong" class="form-select" required>
                                        <option value="">-- Pilih --</option>
                                        <?php foreach ($hasil_potong as $hp): ?>
                                            <option value="<?= $hp['id_hasil_potong'] ?>">
                                                <?= "ID: {$hp['id_hasil_potong']} - {$hp['nama_pemotong']} - {$hp['jumlah_hasil']} pcs (Selesai: {$hp['tgl_selesai']})" ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Penjahit</label>
                                    <select name="id_penjahit" class="form-select" required>
                                        <option value="">-- Pilih Penjahit --</option>
                                        <?php foreach ($penjahit as $p): ?>
                                            <option value="<?= $p['id_penjahit'] ?>"><?= $p['nama_penjahit'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jumlah Bahan Mentah (pcs)</label>
                                    <input type="number" name="jumlah" min="1" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tanggal Pengiriman</label>
                                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Simpan Pengiriman</button>
                            </form>

                            <hr>

                            <h3 class="mt-4">Riwayat Pengiriman</h3>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered mt-3">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Penjahit</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_history = "SELECT pj.*, p.nama_penjahit, 
                        DATE_FORMAT(pj.tanggal_kirim, '%d-%m-%Y') as tgl_kirim
                        FROM pengiriman_penjahit pj
                        JOIN penjahit p ON pj.id_penjahit = p.id_penjahit
                        ORDER BY pj.tanggal_kirim DESC";
                                        $history = query($sql_history);
                                        $no = 1;
                                        foreach ($history as $h):
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= $h['tgl_kirim'] ?></td>
                                                <td><?= $h['nama_penjahit'] ?></td>
                                                <td><?= $h['jumlah_bahan_mentah'] ?> pcs</td>
                                                <td>
                                                    <span class="badge <?= $h['status'] == 'dikirim' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                        <?= $h['status'] == 'dikirim' ? 'Dalam Proses' : 'Selesai' ?>
                                                    </span>
                                                </td>
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