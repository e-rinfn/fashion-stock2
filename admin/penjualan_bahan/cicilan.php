<?php
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

$id_penjualan_bahan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data penjualan
$penjualan_bahan = query("SELECT p.*, r.nama_reseller 
                    FROM penjualan_bahan p
                    JOIN reseller r ON p.id_reseller = r.id_reseller
                    WHERE p.id_penjualan_bahan = $id_penjualan_bahan")[0] ?? null;

// if (!$penjualan || $penjualan['status_pembayaran'] != 'cicilan') {
//     header("Location: list.php");
//     exit();
// }

// Ambil data cicilan
$cicilan = query("SELECT * FROM cicilan_penjualan_bahan WHERE id_penjualan_bahan = $id_penjualan_bahan ORDER BY tanggal_bayar DESC");

// Hitung total sudah dibayar
$total_dibayar = array_sum(array_column($cicilan, 'jumlah_cicilan_penjualan_bahan'));
// $total_dibayar = array_sum(array_map('intval', array_column($cicilan, 'jumlah_cicilan_penjualan_bahan')));


// Sisa hutang
$sisa_hutang = $penjualan_bahan['total_harga'] - $total_dibayar;

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

            // UNTUK LOKAL
            $uploadFileDir = './bukti/';

            // UNTUK SERVER
            // $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/admin/penjualan/bukti/';


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
        // $sql = "INSERT INTO cicilan (id_penjualan, jumlah_cicilan_penjualan_bahan, tanggal_jatuh_tempo, tanggal_bayar, status, metode_pembayaran) 
        //         VALUES ($id_penjualan, $jumlah, '$tanggal', '$tanggal', 'lunas', '$metode')";

        $sql = "INSERT INTO cicilan_penjualan_bahan (id_penjualan_bahan, jumlah_cicilan_penjualan_bahan, tanggal_jatuh_tempo, tanggal_bayar, status, metode_pembayaran, bukti_pembayaran) 
        VALUES ($id_penjualan_bahan, $jumlah, '$tanggal', '$tanggal', 'lunas', '$metode', " . ($bukti_pembayaran ? "'$bukti_pembayaran'" : "NULL") . ")";


        if ($conn->query($sql)) {
            // Update status jika sudah lunas
            $new_total = $total_dibayar + $jumlah;
            if ($new_total >= $penjualan_bahan['total_harga']) {
                $conn->query("UPDATE penjualan_bahan SET status_pembayaran = 'lunas' WHERE id_penjualan_bahan = $id_penjualan_bahan");
            }

            header("Location: cicilan.php?id=$id_penjualan_bahan&success=1");
            exit();
        } else {
            $error = "Gagal menambahkan cicilan: " . $conn->error;
        }
    } else {
        $error = "Jumlah cicilan tidak valid!";
    }
}

// Proses edit cicilan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_cicilan'])) {
    $id_cicilan_penjualan_bahan = intval($_POST['id_cicilan_penjualan_bahan']);
    $jumlah = floatval(str_replace('.', '', $_POST['jumlah']));
    $tanggal = $_POST['tanggal'];
    $metode = $conn->real_escape_string($_POST['metode']);

    // Handle file upload jika ada
    $bukti_update = "";
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        // Proses upload file sama seperti sebelumnya
        // ...
        if ($bukti_pembayaran) {
            $bukti_update = ", bukti_pembayaran = '$bukti_pembayaran'";

            // Hapus file lama jika ada
            $old_file = query("SELECT bukti_pembayaran FROM cicilan WHERE id_cicilan_penjualan_bahan = $id_cicilan_penjualan_bahan")[0]['bukti_pembayaran'];
            if ($old_file && file_exists("bukti/$old_file")) {
                unlink("bukti/$old_file");
            }
        }
    }

    $sql = "UPDATE cicilan_penjualan_bahan SET 
            jumlah_cicilan_penjualan_bahan = $jumlah,
            tanggal_bayar = '$tanggal',
            tanggal_jatuh_tempo = '$tanggal',
            metode_pembayaran = '$metode'
            $bukti_update
            WHERE id_cicilan_penjualan_bahan = $id_cicilan_penjualan_bahan";

    if ($conn->query($sql)) {
        // Update total dibayar di penjualan jika perlu
        $total_dibayar = query("SELECT SUM(jumlah_cicilan_penjualan_bahan) as total FROM cicilan_penjualan_bahan WHERE id_penjualan_bahan = $id_penjualan_bahan")[0]['total'];

        if ($total_dibayar >= $penjualan_bahan['total_harga']) {
            $conn->query("UPDATE penjualan_bahan SET status_pembayaran = 'lunas' WHERE id_penjualan_bahan = $id_penjualan_bahan");
        } else {
            $conn->query("UPDATE penjualan_bahan SET status_pembayaran = 'cicilan' WHERE id_penjualan_bahan = $id_penjualan_bahan");
        }

        $_SESSION['success'] = "Pembayaran cicilan berhasil diperbarui!";
        header("Location: cicilan.php?id=$id_penjualan_bahan");
        exit();
    } else {
        $error = "Gagal memperbarui cicilan: " . $conn->error;
    }
}

// Ambil detail penjualan
$detail = query("SELECT d.*, pr.nama_bahan 
                FROM detail_penjualan_bahan d
                JOIN bahan_baku pr ON d.id_bahan = pr.id_bahan
                WHERE d.id_penjualan_bahan = $id_penjualan_bahan");

// Ambil detail penjualan
$detail = query("SELECT d.*, pr.nama_bahan 
                FROM detail_penjualan_bahan d
                JOIN bahan_baku pr ON d.id_bahan = pr.id_bahan
                WHERE d.id_penjualan_bahan = $id_penjualan_bahan");

// Hitung total cicilan
$cicilan_info = query("SELECT SUM(jumlah_cicilan_penjualan_bahan) as total FROM cicilan_penjualan_bahan WHERE id_penjualan_bahan = $id_penjualan_bahan AND status = 'lunas'")[0];
$total_cicilan = $cicilan_info['total'] ?? 0;


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
                        <h2 class="fw-bold text-warning">PENJUALAN BAHAN BAKU</h2>
                        <hr>


                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">Pembayaran berhasil dicatat!</div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-12 mb-3">

                                <div class="card mb-4">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th width="30%">Reseller</th>
                                                        <td><?= $penjualan_bahan['nama_reseller'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <td><?= dateIndo($penjualan_bahan['tanggal_penjualan_bahan']) . ' ' . date('H:i', strtotime($penjualan_bahan['tanggal_penjualan_bahan'])) ?></td>
                                                    </tr>

                                                </table>
                                            </div>
                                            <div class="col-md-8">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th width="30%">Total Harga</th>
                                                        <td><?= formatRupiah($penjualan_bahan['total_harga']) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status Pembayaran</th>
                                                        <td>
                                                            <?= ucfirst($penjualan_bahan['status_pembayaran']) ?>
                                                            <?php if ($penjualan_bahan['status_pembayaran'] == 'cicilan'): ?> <br>
                                                                Dibayar: <?= formatRupiah($total_cicilan) ?> dari <?= formatRupiah($penjualan_bahan['total_harga']) ?>
                                                                <?php
                                                                $sisa_hutang = $penjualan_bahan['total_harga'] - $total_cicilan;
                                                                ?>
                                                                <br>
                                                                Sisa Hutang: <?= formatRupiah($sisa_hutang) ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <!-- <tr>
                                                <th>Metode Pembayaran</th>
                                                <td><?= ucfirst($penjualan_bahan['metode_pembayaran']) ?></td>
                                            </tr> -->
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between">
                                            <h3>Daftar Produk</h3>
                                            <a href="nota.php?id=<?= $id_penjualan_bahan ?>" class="btn btn-danger" target="_blank">
                                                <i class="bx bx-printer"></i> Cetak Nota
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Produk</th>
                                                    <th>Harga Satuan</th>
                                                    <th>Qty</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($detail as $i => $d): ?>
                                                    <tr>
                                                        <td><?= $i + 1 ?></td>
                                                        <td><?= $d['nama_bahan'] ?></td>
                                                        <td><?= formatRupiah($d['harga_satuan']) ?></td>
                                                        <td><?= $d['jumlah'] ?></td>
                                                        <td><?= formatRupiah($d['subtotal']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="4" class="text-right">Total</th>
                                                    <th class="fs-6 text-center"><?= formatRupiah($penjualan_bahan['total_harga']) ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="col-md-5">
                                <div class="card">
                                    <div class="card-header text-center">
                                        <h3>Tambah Pembayaran</h3>
                                        <hr>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label>Jumlah Dibayarkan</label>
                                                <!-- <input type="text" name="jumlah" class="form-control money"
                                                    placeholder="Masukkan jumlah" required
                                                    value="<?= $sisa_hutang ?>"> -->
                                                <input type="text" name="jumlah" class="form-control money"
                                                    placeholder="Masukkan jumlah" required
                                                    value="0">
                                            </div>
                                            <div class="form-group mt-3">
                                                <label>Tanggal Pembayaran <span class="text-danger">(Bulan/Tanggal/Tahun)</span></label>
                                                <input type="date" name="tanggal" class="form-control"
                                                    value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="form-group mt-3">
                                                <label>Metode Pembayaran</label>
                                                <select name="metode" class="form-control" required>
                                                    <option value="transfer">Transfer Bank</option>
                                                    <option value="tunai">Tunai</option>
                                                    <option value="e-wallet">E-Wallet</option>
                                                </select>
                                            </div>
                                            <div class="form-group mt-3">
                                                <label>Bukti Pembayaran <br> (jpg, png, pdf max 2MB)</label>
                                                <input type="file" name="bukti_pembayaran" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                                                <a href="list.php" class="btn btn-secondary">
                                                    <i class="bx bx-arrow-back"></i> Kembali
                                                </a>
                                                <button type="submit" name="tambah_cicilan" class="btn btn-primary">
                                                    Simpan Pembayaran
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="card">
                                    <div class="card-header text-center">
                                        <h3>Riwayat Pembayaran</h3>
                                        <hr>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($cicilan)): ?>
                                            <div class="alert alert-info">Belum ada pembayaran cicilan</div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr class="text-center">
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
                                                                <td class="text-center"><?= $i + 1 ?></td>
                                                                <td><?= dateIndo($c['tanggal_bayar']) ?></td>
                                                                <td><?= formatRupiah($c['jumlah_cicilan_penjualan_bahan']) ?></td>
                                                                <td><?= ucfirst($c['metode_pembayaran']) ?></td>
                                                                <td class="text-center">
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
                                                                        Tidak ada bukti
                                                                    <?php endif; ?>
                                                                    <hr>
                                                                    <div class="btn-group mt-1">
                                                                        <button class="btn btn-sm btn-warning" onclick="showEditForm(<?= $c['id_cicilan_penjualan_bahan'] ?>)">
                                                                            <i class="bx bx-edit"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-danger" onclick="confirmCancel(<?= $c['id_cicilan_penjualan_bahan'] ?>)">
                                                                            <i class="bx bx-trash"></i>
                                                                        </button>
                                                                        <a href="nota_cicilan.php?id=<?= $c['id_cicilan_penjualan_bahan'] ?>" target="_blank" class="btn btn-sm btn-info">
                                                                            <i class="bx bx-printer"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>

                                                </table>

                                                <!-- Form Edit Cicilan (hidden) -->
                                                <div id="editFormContainer" style="display:none;" class="mt-3">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h4>Edit Pembayaran Cicilan</h4>
                                                        </div>
                                                        <div class="card-body">
                                                            <form id="editCicilanForm" method="post" enctype="multipart/form-data">
                                                                <input type="hidden" name="id_cicilan_penjualan_bahan" id="edit_id_cicilan_penjualan_bahan">
                                                                <input type="hidden" name="edit_cicilan" value="1">

                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Jumlah Cicilan</label>
                                                                            <input type="text" name="jumlah" id="edit_jumlah" class="form-control money" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Tanggal Pembayaran</label>
                                                                            <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row mt-2">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Metode Pembayaran</label>
                                                                            <select name="metode" id="edit_metode" class="form-control" required>
                                                                                <option value="transfer">Transfer Bank</option>
                                                                                <option value="tunai">Tunai</option>
                                                                                <option value="e-wallet">E-Wallet</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Bukti Pembayaran (kosongkan jika tidak diubah)</label>
                                                                            <input type="file" name="bukti_pembayaran" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="mt-3">
                                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                                    <button type="button" class="btn btn-secondary" onclick="hideEditForm()">Batal</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        function confirmCancel(id_cicilan_penjualan_bahan) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan menghapus pembayaran ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Kirim request penghapusan ke server
                    fetch(`cancel_cicilan.php?id=${id_cicilan_penjualan_bahan}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            Swal.close();
                            if (data.success) {
                                Swal.fire(
                                    'Dihapus!',
                                    'Pembayaran telah dihapus.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    data.message || 'Gagal menghapus pembayaran.',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan saat memproses permintaan: ' + error.message,
                                'error'
                            );
                        });
                }
            });
        }

        // Fungsi untuk menampilkan form edit
        function showEditForm(id_cicilan_penjualan_bahan) {
            // Ambil data cicilan via AJAX
            fetch(`get_cicilan.php?id=${id_cicilan_penjualan_bahan}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('edit_id_cicilan_penjualan_bahan').value = data.id_cicilan_penjualan_bahan;
                        // document.getElementById('edit_jumlah').value = data.jumlah_cicilan_penjualan_bahan;
                        document.getElementById('edit_jumlah').value = parseFloat(data.jumlah_cicilan_penjualan_bahan).toString();
                        document.getElementById('edit_tanggal').value = data.tanggal_bayar;
                        document.getElementById('edit_metode').value = data.metode_pembayaran;

                        // Tampilkan form
                        document.getElementById('editFormContainer').style.display = 'block';

                        // Scroll ke form edit
                        document.getElementById('editFormContainer').scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
        }

        // Fungsi untuk menyembunyikan form edit
        function hideEditForm() {
            document.getElementById('editFormContainer').style.display = 'none';
        }
    </script>

    <!-- Core JS footer -->
    <?php include '../includes/footer.php'; ?>
    <!-- /Core JS footer -->


</body>

</html>