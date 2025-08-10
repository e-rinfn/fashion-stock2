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

// Fungsi untuk mendapatkan tarif upah terbaru
function getTarifUpah($jenis)
{
    global $conn;
    $result = $conn->query("SELECT tarif_per_unit FROM tarif_upah 
                          WHERE jenis_tarif = '$jenis' 
                          ORDER BY berlaku_sejak DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['tarif_per_unit'];
    }
    return 0; // Default jika tidak ada tarif
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

    // Dapatkan tarif upah terbaru
    $tarif = getTarifUpah('pemotongan');
    $total_upah = $jumlah_hasil * $tarif;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update status pengiriman
        $update = $conn->query("UPDATE pengiriman_pemotong SET status = 'selesai', tanggal_diterima = '$tanggal' 
                             WHERE id_pengiriman_potong = $id_pengiriman");

        if (!$update) {
            throw new Exception("Gagal mengupdate status pengiriman: " . $conn->error);
        }

        // Catat hasil pemotongan dengan upah
        $sql = "INSERT INTO hasil_pemotongan 
                (id_pengiriman_potong, jumlah_hasil, tanggal_selesai, id_tarif, total_upah)
                VALUES ($id_pengiriman, $jumlah_hasil, '$tanggal', 
                (SELECT id_tarif FROM tarif_upah WHERE jenis_tarif = 'pemotongan' ORDER BY berlaku_sejak DESC LIMIT 1), 
                $total_upah)";

        if (!$conn->query($sql)) {
            throw new Exception("Gagal mencatat hasil pemotongan: " . $conn->error);
        }

        $conn->commit();
        $_SESSION['success'] = "Hasil pemotongan berhasil dicatat. Total upah: Rp " . number_format($total_upah, 0, ',', '.');
        header("Location: hasil_pemotongan.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Ambil tarif upah saat ini untuk ditampilkan
$tarif_sekarang = getTarifUpah('pemotongan');
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
                                    <div class="col-md-3 mb-3">
                                        <label for="jumlah_hasil" class="form-label">Jumlah Hasil (pcs bahan mentah)</label>
                                        <div class="input-group">
                                            <input type="number" name="jumlah_hasil" id="jumlah_hasil" class="form-control" required min="1">
                                            <span class="input-group-text">Pcs</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Tarif Upah</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="tarif_upah" class="form-control" readonly value="<?= number_format($tarif_sekarang, 0, ',', '.') ?>">
                                            <span class="input-group-text">/Pcs</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Total Upah</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="total_upah" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="tanggal" class="form-label">Tanggal Selesai</label>
                                        <input type="date" name="tanggal" id="tanggal" class="form-control" required value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">Catat Hasil</button>
                                    <div class="btn-group">
                                        <a href="riwayat_hasil_pemotongan.php" class="btn btn-secondary">Riwayat Hasil</a>
                                        <a href="#" class="btn btn-danger" id="btnBatalHasil">
                                            Batal Catat Hasil
                                        </a>
                                    </div>
                                </div>

                                <script>
                                    // Hitung total upah otomatis saat jumlah hasil diubah
                                    document.getElementById('jumlah_hasil').addEventListener('input', function() {
                                        const jumlah = parseInt(this.value) || 0;
                                        const tarif = <?= $tarif_sekarang ?>;
                                        const total = jumlah * tarif;
                                        document.getElementById('total_upah').value = total.toLocaleString('id-ID');
                                    });

                                    // Konfirmasi pembatalan
                                    document.getElementById('btnBatalHasil').addEventListener('click', function(e) {
                                        e.preventDefault();

                                        Swal.fire({
                                            title: 'Yakin?',
                                            text: "Ingin membatalkan hasil potong terakhir?",
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#d33',
                                            cancelButtonColor: '#6c757d',
                                            confirmButtonText: 'Ya, batalkan!',
                                            cancelButtonText: 'Batal'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location.href = 'batal_hasil_potong.php';
                                            }
                                        });
                                    });
                                </script>
                            </form>

                            <small class="text-end text-danger">Pembatalan tidak dapat dilakukan karena data sudah masuk ke tahap pengiriman ke penjahit.</small>
                            <hr>

                            <h4 class="mb-3">Riwayat Hasil Pemotongan</h4>

                            <?php
                            $riwayat = query("SELECT h.*, p.nama_bahan, p.satuan, pm.nama_pemotong, pg.jumlah_bahan,
                                            t.tarif_per_unit, h.total_upah
                                            FROM hasil_pemotongan h
                                            JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
                                            JOIN bahan_baku p ON pg.id_bahan = p.id_bahan
                                            JOIN pemotong pm ON pg.id_pemotong = pm.id_pemotong
                                            LEFT JOIN tarif_upah t ON h.id_tarif = t.id_tarif
                                            ORDER BY h.tanggal_selesai DESC LIMIT 5");
                            ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Bahan Baku</th>
                                            <th>Pemotong</th>
                                            <th>Bahan Digunakan</th>
                                            <th>Jumlah Hasil</th>
                                            <th>Tarif</th>
                                            <th>Total Upah</th>
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
                                                <td class="text-center"><?= number_format($r['jumlah_bahan']) ?> <?= htmlspecialchars($r['satuan']) ?></td>
                                                <td class="text-center"><?= number_format($r['jumlah_hasil']) ?> pcs</td>
                                                <td class="text-center">Rp <?= number_format($r['tarif_per_unit'] ?? 0, 0, ',', '.') ?></td>
                                                <td class="text-center">Rp <?= number_format($r['total_upah'] ?? 0, 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($riwayat)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center">Belum ada data hasil pemotongan.</td>
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