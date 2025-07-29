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

<style>
    /* Paksa SweetAlert berada di atas segalanya */
    .swal2-container {
        z-index: 99999 !important;
    }
</style>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


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
                            <h2>4. Tambah Data Hasil Penjahitan</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <a href="pengiriman_penjahit.php" class="btn btn-outline-warning">Kembali</a>
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
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="pengiriman-select" class="form-label">Pilih Pengiriman</label>
                                        <select name="id_pengiriman" id="pengiriman-select" class="form-select" required>
                                            <option value="">-- Pilih Pengiriman --</option>
                                            <?php foreach ($pengiriman as $p): ?>
                                                <option value="<?= $p['id_pengiriman_jahit'] ?>"
                                                    data-jumlah="<?= $p['jumlah_bahan_mentah'] ?>"> Tanggal
                                                    <?= dateIndo($p['tgl_kirim']) ?> |
                                                    <?= "{$p['nama_penjahit']} : {$p['jumlah_bahan_mentah']} pcs " ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="produk-select" class="form-label">Produk Jadi</label>
                                        <select name="id_produk" id="produk-select" class="form-select" required>
                                            <option value="">-- Pilih Produk --</option>
                                            <?php foreach ($produk as $p): ?>
                                                <option value="<?= $p['id_produk'] ?>"><?= $p['nama_produk'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Jumlah Produk Jadi (pcs)</label>
                                        <input type="number" name="jumlah" min="1" class="form-control" required>
                                    </div>

                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Tanggal Selesai <span class="text-danger">(Bulan/Tanggal/Tahun)</span></label>
                                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control"></textarea>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">Catat Hasil</button>
                                    <div class="btn-group">
                                        <a href="riwayat_hasil_penjahitan.php" class="btn btn-secondary">Riwayat Hasil</a>
                                        <a href="#" class="btn btn-danger" id="btnBatalHasil">
                                            Batal Catat Hasil
                                        </a>
                                    </div>
                                </div>

                                <script>
                                    document.getElementById('btnBatalHasil').addEventListener('click', function(e) {
                                        e.preventDefault(); // Mencegah link langsung berjalan

                                        Swal.fire({
                                            title: 'Yakin?',
                                            text: "Ingin membatalkan hasil jahit terakhir?",
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#d33',
                                            cancelButtonColor: '#6c757d',
                                            confirmButtonText: 'Ya, batalkan!',
                                            cancelButtonText: 'Batal'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                // Redirect manual
                                                window.location.href = 'batal_hasil_penjahitan.php';
                                            }
                                        });
                                    });
                                </script>

                            </form>

                            <hr class="my-4">

                            <h4>Riwayat Hasil Penjahitan</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Produk</th>
                                            <th>Bahan Mentah</th>
                                            <th>Produk Jadi</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_history = "SELECT hp.*, p.nama_produk, pj.jumlah_bahan_mentah,
                                            DATE_FORMAT(hp.tanggal_selesai, '%d-%m-%Y') as tgl_selesai
                                            FROM hasil_penjahitan hp
                                            JOIN produk p ON hp.id_produk = p.id_produk
                                            JOIN pengiriman_penjahit pj ON hp.id_pengiriman_jahit = pj.id_pengiriman_jahit
                                            ORDER BY hp.tanggal_selesai DESC LIMIT 5";
                                        $history = query($sql_history);
                                        $no = 1;
                                        foreach ($history as $h):
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= dateIndo($h['tgl_selesai']) ?></td>
                                                <td><?= $h['nama_produk'] ?></td>
                                                <td class="text-center"><?= $h['jumlah_bahan_mentah'] ?> pcs</td>
                                                <td class="text-center"><?= $h['jumlah_produk_jadi'] ?> pcs</td>
                                                <td><?= $h['keterangan'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($history)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Belum ada data hasil penjahitan.</td>
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