<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
// redirectIfNotLoggedIn();
// checkRole('admin');

$id_penjualan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data penjualan
$penjualan = query("SELECT p.*, r.nama_reseller 
                    FROM penjualan p
                    JOIN reseller r ON p.id_reseller = r.id_reseller
                    WHERE p.id_penjualan = $id_penjualan")[0] ?? null;

// if (!$penjualan || $penjualan['status_pembayaran'] != 'cicilan') {
//     header("Location: list.php");
//     exit();
// }

// Ambil data cicilan
$cicilan = query("SELECT * FROM cicilan WHERE id_penjualan = $id_penjualan ORDER BY tanggal_bayar DESC");

// Hitung total sudah dibayar
$total_dibayar = array_sum(array_column($cicilan, 'jumlah_cicilan'));
// $total_dibayar = array_sum(array_map('intval', array_column($cicilan, 'jumlah_cicilan')));


// Sisa hutang
$sisa_hutang = $penjualan['total_harga'] - $total_dibayar;

$bukti_pembayaran = null; // default null

if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['bukti_pembayaran']['tmp_name'];
    $fileName = $_FILES['bukti_pembayaran']['name'];
    $fileSize = $_FILES['bukti_pembayaran']['size'];
    $fileType = $_FILES['bukti_pembayaran']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Allowed extensions
    $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

    if (in_array($fileExtension, $allowedfileExtensions)) {
        if ($fileSize <= 2 * 1024 * 1024) { // max 2MB
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;


            // $uploadFileDir = './bukti/';

            $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/admin/penjualan/bukti/';


            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $bukti_pembayaran = $newFileName;
            } else {
                $error = "Gagal meng-upload file bukti pembayaran.";
            }
        } else {
            $error = "Ukuran file terlalu besar. Maksimal 2MB.";
        }
    } else {
        $error = "Format file tidak diizinkan. Hanya jpg, jpeg, png, dan pdf.";
    }
}


// Proses tambah cicilan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_cicilan'])) {
    $jumlah = floatval(str_replace('.', '', $_POST['jumlah']));
    $tanggal = $_POST['tanggal'];
    $metode = $conn->real_escape_string($_POST['metode']);
    $keterangan = $conn->real_escape_string($_POST['keterangan'] ?? '');

    if ($jumlah > 0 && $jumlah <= $sisa_hutang) {
        // $sql = "INSERT INTO cicilan (id_penjualan, jumlah_cicilan, tanggal_jatuh_tempo, tanggal_bayar, status, metode_pembayaran) 
        //         VALUES ($id_penjualan, $jumlah, '$tanggal', '$tanggal', 'lunas', '$metode')";

        $sql = "INSERT INTO cicilan (id_penjualan, jumlah_cicilan, tanggal_jatuh_tempo, tanggal_bayar, status, metode_pembayaran, bukti_pembayaran) 
        VALUES ($id_penjualan, $jumlah, '$tanggal', '$tanggal', 'lunas', '$metode', " . ($bukti_pembayaran ? "'$bukti_pembayaran'" : "NULL") . ")";


        if ($conn->query($sql)) {
            // Update status jika sudah lunas
            $new_total = $total_dibayar + $jumlah;
            if ($new_total >= $penjualan['total_harga']) {
                $conn->query("UPDATE penjualan SET status_pembayaran = 'lunas' WHERE id_penjualan = $id_penjualan");
            }

            header("Location: cicilan.php?id=$id_penjualan&success=1");
            exit();
        } else {
            $error = "Gagal menambahkan cicilan: " . $conn->error;
        }
    } else {
        $error = "Jumlah cicilan tidak valid!";
    }
}
?>

<?php include '../includes/header.php'; ?>

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
                            <h2>Informasi Cicilan</h2>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">Pembayaran cicilan berhasil dicatat!</div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h3>Detail Cicilan</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="40%">Reseller</th>
                                                <td class="text-center"><?= $penjualan['nama_reseller'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Total Harga</th>
                                                <td class="text-center"><span class="badge bg-info fs-5"><?= formatRupiah($penjualan['total_harga']) ?></span></td>
                                            </tr>

                                            <tr>
                                                <th>Total Dibayar</th>
                                                <td class="text-center"><span class="badge bg-success fs-5"><?= formatRupiah($total_dibayar) ?></span></td>
                                            </tr>


                                            <tr>
                                                <th>Sisa Hutang</th>
                                                <td class="text-center"><span class="badge bg-warning fs-5 text-dark"><?= formatRupiah($sisa_hutang) ?></span></td>
                                            </tr>

                                        </table>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h3>Tambah Pembayaran</h3>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" enctype="multipart/form-data">
                                            <div class="form-group mt-2">
                                                <label>Jumlah Cicilan</label>
                                                <!-- <input type="text" name="jumlah" class="form-control money"
                                                    placeholder="Masukkan jumlah" required
                                                    value="<?= $sisa_hutang ?>"> -->
                                                <input type="text" name="jumlah" class="form-control money"
                                                    placeholder="Masukkan jumlah" required
                                                    value="0">

                                            </div>

                                            <div class="form-group mt-2">
                                                <label>Tanggal Pembayaran</label>
                                                <input type="date" name="tanggal" class="form-control"
                                                    value="<?= date('Y-m-d') ?>" required>
                                            </div>

                                            <div class="form-group mt-2">
                                                <label>Metode Pembayaran</label>
                                                <select name="metode" class="form-control" required>
                                                    <option value="transfer">Transfer Bank</option>
                                                    <option value="tunai">Tunai</option>
                                                    <option value="e-wallet">E-Wallet</option>
                                                </select>
                                            </div>

                                            <div class="form-group mt-2">
                                                <label>Bukti Pembayaran (jpg, png, pdf max 2MB)</label>
                                                <input type="file" name="bukti_pembayaran" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                            </div>



                                            <button type="submit" name="tambah_cicilan" class="btn btn-primary mt-3">
                                                Simpan Pembayaran
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3>Riwayat Pembayaran</h3>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($cicilan)): ?>
                                            <div class="alert alert-info">Belum ada pembayaran cicilan</div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>No</th>
                                                            <th>Tanggal</th>
                                                            <th>Jumlah</th>
                                                            <th>Metode</th>
                                                            <th>Bukti</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($cicilan as $i => $c): ?>
                                                            <tr>
                                                                <td><?= $i + 1 ?></td>
                                                                <td><?= date('d/m/Y', strtotime($c['tanggal_bayar'])) ?></td>
                                                                <td><?= formatRupiah($c['jumlah_cicilan']) ?></td>
                                                                <td><?= ucfirst($c['metode_pembayaran']) ?></td>
                                                                <td>
                                                                    <?php if ($c['bukti_pembayaran']): ?>
                                                                        <?php
                                                                        $ext = pathinfo($c['bukti_pembayaran'], PATHINFO_EXTENSION);
                                                                        $fileUrl = "bukti/" . $c['bukti_pembayaran'];
                                                                        ?>
                                                                        <?php if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])): ?>
                                                                            <a href="<?= $fileUrl ?>" target="_blank">
                                                                                <img src="<?= $fileUrl ?>" alt="Bukti" style="max-height: 50px;">
                                                                            </a>
                                                                        <?php else: ?>
                                                                            <a href="<?= $fileUrl ?>" target="_blank">Lihat Bukti</a>
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>

                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
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

    <script>
        // Format input uang
        document.querySelectorAll('.money').forEach(input => {
            input.addEventListener('keyup', function(e) {
                let value = this.value.replace(/[^0-9]/g, '');
                this.value = formatRupiahInput(value);
            });
        });

        function formatRupiahInput(angka) {
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>







    <!-- Core JS footer -->
    <?php include '../includes/footer.php'; ?>
    <!-- /Core JS footer -->


</body>

</html>