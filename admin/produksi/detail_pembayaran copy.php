<?php
require_once __DIR__ . '../../includes/header.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

$id_pembayaran = $_GET['id'] ?? 0;

// Ambil data pembayaran
$pembayaran = query("
    SELECT pb.*, 
           CASE pb.jenis_penerima 
               WHEN 'pemotong' THEN (SELECT nama_pemotong FROM pemotong WHERE id_pemotong = pb.id_penerima)
               WHEN 'penjahit' THEN (SELECT nama_penjahit FROM penjahit WHERE id_penjahit = pb.id_penerima)
           END as nama_penerima
    FROM pembayaran_upah pb
    WHERE pb.id_pembayaran = $id_pembayaran
")[0] ?? null;

if (!$pembayaran) {
    $_SESSION['error'] = "Data pembayaran tidak ditemukan";
    header("Location: riwayat_hasil_pemotongan.php");
    exit;
}

// Ambil detail hasil yang dibayarkan
$detail_hasil = query("
    SELECT d.*, 
           CASE d.jenis_hasil
               WHEN 'potong' THEN (SELECT tanggal_selesai FROM hasil_pemotongan WHERE id_hasil_potong = d.id_hasil)
               WHEN 'jahit' THEN (SELECT tanggal_selesai FROM hasil_penjahitan WHERE id_hasil_jahit = d.id_hasil)
           END as tanggal_hasil,
           CASE d.jenis_hasil
               WHEN 'potong' THEN (SELECT total_upah FROM hasil_pemotongan WHERE id_hasil_potong = d.id_hasil)
               WHEN 'jahit' THEN (SELECT total_upah FROM hasil_penjahitan WHERE id_hasil_jahit = d.id_hasil)
           END as jumlah_upah
    FROM detail_pembayaran_upah d
    WHERE d.id_pembayaran = $id_pembayaran
");

// Ambil riwayat cicilan
$cicilan = query("
    SELECT * FROM cicilan_upah 
    WHERE id_pembayaran = $id_pembayaran
    ORDER BY tanggal_cicilan DESC
");

// Hitung total cicilan yang sudah dibayarkan
$total_cicilan = query("
    SELECT IFNULL(SUM(jumlah_cicilan), 0) as total 
    FROM cicilan_upah 
    WHERE id_pembayaran = $id_pembayaran
")[0]['total'];

// Hitung sisa upah
$sisa_upah = $pembayaran['total_upah'] - $total_cicilan;

// Update sisa upah di database jika berbeda
if ($pembayaran['sisa_upah'] != $sisa_upah) {
    mysqli_query($conn, "
        UPDATE pembayaran_upah 
        SET sisa_upah = $sisa_upah,
            status = CASE 
                WHEN $sisa_upah <= 0 THEN 'dibayar' 
                ELSE 'terhitung' 
            END
        WHERE id_pembayaran = $id_pembayaran
    ");

    // Refresh data pembayaran
    $pembayaran = query("
        SELECT pb.*, 
               CASE pb.jenis_penerima 
                   WHEN 'pemotong' THEN (SELECT nama_pemotong FROM pemotong WHERE id_pemotong = pb.id_penerima)
                   WHEN 'penjahit' THEN (SELECT nama_penjahit FROM penjahit WHERE id_penjahit = pb.id_penerima)
               END as nama_penerima
        FROM pembayaran_upah pb
        WHERE pb.id_pembayaran = $id_pembayaran
    ")[0];
}

// Tangani form tambah cicilan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_cicilan'])) {
    $jumlah_cicilan = str_replace(['.', ','], ['', '.'], $_POST['jumlah_cicilan']);
    $tanggal_cicilan = $_POST['tanggal_cicilan'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $keterangan = $_POST['keterangan'];

    // Validasi
    if ($jumlah_cicilan <= 0) {
        $_SESSION['error'] = "Jumlah cicilan harus lebih dari 0";
    } elseif ($jumlah_cicilan > $sisa_upah) {
        $_SESSION['error'] = "Jumlah cicilan tidak boleh melebihi sisa upah yang belum dibayar";
    } else {
        // Insert cicilan
        $query = "
            INSERT INTO cicilan_upah 
            (id_pembayaran, jumlah_cicilan, tanggal_cicilan, metode_pembayaran, keterangan) 
            VALUES 
            ($id_pembayaran, $jumlah_cicilan, '$tanggal_cicilan', '$metode_pembayaran', '$keterangan')
        ";

        $result = mysqli_query($conn, $query);

        if ($result) {
            $_SESSION['success'] = "Cicilan berhasil ditambahkan";
            header("Location: detail_pembayaran.php?id=$id_pembayaran");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menambahkan cicilan: " . mysqli_error($conn);
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
                            <h2>Detail Pembayaran Upah</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <a href="riwayat_hasil_pemotongan.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </div>

                        <div class="card p-4 shadow-sm">
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>

                            <!-- Info Pembayaran -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5>Informasi Pembayaran</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Penerima</th>
                                            <td><?= htmlspecialchars($pembayaran['nama_penerima']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Upah</th>
                                            <td>Rp <?= number_format($pembayaran['total_upah'], 0, ',', '.') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Dibayarkan</th>
                                            <td>Rp <?= number_format($total_cicilan, 0, ',', '.') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Sisa Upah</th>
                                            <td>Rp <?= number_format($sisa_upah, 0, ',', '.') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <?php if ($pembayaran['status'] == 'dibayar'): ?>
                                                    <span class="badge bg-success">Dibayarkan</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Belum Dibayarkan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Detail Pembayaran</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Tanggal Pembayaran</th>
                                            <td><?= date('d F Y', strtotime($pembayaran['tanggal_bayar'])) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Metode Pembayaran</th>
                                            <td><?= ucfirst($pembayaran['metode_pembayaran']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Keterangan</th>
                                            <td><?= htmlspecialchars($pembayaran['catatan']) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Form Tambah Cicilan -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Tambah Cicilan Pembayaran</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Jumlah Cicilan</label>
                                                <input type="text" class="form-control" name="jumlah_cicilan"
                                                    id="jumlah_cicilan" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Tanggal Cicilan</label>
                                                <input type="date" class="form-control" name="tanggal_cicilan"
                                                    value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Metode Pembayaran</label>
                                                <select class="form-select" name="metode_pembayaran" required>
                                                    <option value="transfer">Transfer Bank</option>
                                                    <option value="tunai">Tunai</option>
                                                    <option value="e-wallet">E-Wallet</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Keterangan</label>
                                                <input type="text" class="form-control" name="keterangan">
                                            </div>
                                            <div class="col-md-12">
                                                <button type="submit" name="tambah_cicilan"
                                                    class="btn btn-primary">
                                                    Tambah Cicilan
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Daftar Cicilan -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Riwayat Cicilan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal</th>
                                                    <th>Jumlah</th>
                                                    <th>Metode</th>
                                                    <th>Keterangan</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($cicilan)): ?>
                                                    <?php $no = 1;
                                                    foreach ($cicilan as $c): ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= date('d F Y', strtotime($c['tanggal_cicilan'])) ?></td>
                                                            <td>Rp <?= number_format($c['jumlah_cicilan'], 0, ',', '.') ?></td>
                                                            <td><?= ucfirst($c['metode_pembayaran']) ?></td>
                                                            <td><?= htmlspecialchars($c['keterangan']) ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-danger btn-hapus-cicilan"
                                                                    data-id="<?= $c['id_cicilan'] ?>">
                                                                    Hapus
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">Belum ada cicilan</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Daftar Hasil yang Dibayarkan -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Daftar Hasil yang Dibayarkan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal Hasil</th>
                                                    <th>Jenis</th>
                                                    <th>Jumlah Upah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($detail_hasil)): ?>
                                                    <?php $no = 1;
                                                    foreach ($detail_hasil as $dh): ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= date('d F Y', strtotime($dh['tanggal_hasil'])) ?></td>
                                                            <td><?= ucfirst($dh['jenis_hasil']) ?></td>
                                                            <td>Rp <?= number_format($dh['jumlah_upah'], 0, ',', '.') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center">Tidak ada data hasil</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Modal Hapus Cicilan -->
        <div class="modal fade" id="modalHapusCicilan" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus Cicilan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Anda yakin ingin menghapus cicilan ini?</p>
                        <form id="formHapusCicilan" method="post" action="hapus_cicilan.php">
                            <input type="hidden" name="id_cicilan" id="id_cicilan_hapus">
                            <input type="hidden" name="id_pembayaran" value="<?= $id_pembayaran ?>">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" form="formHapusCicilan" class="btn btn-danger">Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Format input jumlah cicilan
            document.getElementById('jumlah_cicilan').addEventListener('input', function(e) {
                let value = this.value.replace(/[^0-9]/g, '');
                this.value = new Intl.NumberFormat('id-ID').format(value);
            });

            // Tangani klik tombol hapus cicilan
            document.querySelectorAll('.btn-hapus-cicilan').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    document.getElementById('id_cicilan_hapus').value = id;

                    var modal = new bootstrap.Modal(document.getElementById('modalHapusCicilan'));
                    modal.show();
                });
            });
        </script>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS footer -->
    <?php include '../includes/footer.php'; ?>
    <!-- /Core JS footer -->
</body>

</html>