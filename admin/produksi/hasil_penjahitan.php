<?php
require_once __DIR__ . '../../includes/header.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: {$base_url}auth/login.php");
    exit;
}

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

function getTarifUpah($jenis)
{
    global $conn;
    $stmt = $conn->prepare("SELECT id_tarif, tarif_per_unit FROM tarif_upah 
                          WHERE jenis_tarif = ? 
                          ORDER BY berlaku_sejak DESC LIMIT 1");
    $stmt->bind_param("s", $jenis);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return ['id_tarif' => null, 'tarif_per_unit' => 0];
}

// Get unfinished shipments
$sql_pengiriman = "SELECT pj.id_pengiriman_jahit, pj.jumlah_bahan_mentah, 
                   p.nama_penjahit, p.id_penjahit, pj.id_penjahit as penjahit_id,
                   DATE_FORMAT(pj.tanggal_kirim, '%Y-%m-%d') as tanggal_kirim
                   FROM pengiriman_penjahit pj
                   JOIN penjahit p ON pj.id_penjahit = p.id_penjahit
                   WHERE pj.status = 'dikirim'";
$pengiriman = query($sql_pengiriman);

// Get products
$produk = query("SELECT * FROM produk ORDER BY nama_produk");

// Get current sewing wage rate
$tarif_penjahitan = getTarifUpah('penjahitan');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pengiriman = intval($_POST['id_pengiriman']);
    $id_produk = intval($_POST['id_produk']);
    $jumlah = intval($_POST['jumlah']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
    $keterangan = $conn->real_escape_string($_POST['keterangan'] ?? '');

    // Get penjahit ID from shipment
    $id_penjahit = null;
    foreach ($pengiriman as $p) {
        if ($p['id_pengiriman_jahit'] == $id_pengiriman) {
            $id_penjahit = $p['id_penjahit'];
            break;
        }
    }

    if (!$id_penjahit) {
        $_SESSION['error'] = "Data penjahit tidak valid";
        header("Location: hasil_penjahitan.php");
        exit;
    }

    // Calculate total wage
    $total_upah = $jumlah * $tarif_penjahitan['tarif_per_unit'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Record sewing results with wage
        $sql1 = "INSERT INTO hasil_penjahitan 
                (id_pengiriman_jahit, jumlah_produk_jadi, id_produk, tanggal_selesai, keterangan, id_tarif, total_upah)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param(
            "iiissid",
            $id_pengiriman,
            $jumlah,
            $id_produk,
            $tanggal,
            $keterangan,
            $tarif_penjahitan['id_tarif'],
            $total_upah
        );

        if (!$stmt1->execute()) {
            throw new Exception("Gagal mencatat hasil: " . $stmt1->error);
        }

        $id_hasil_penjahitan = $stmt1->insert_id;

        // 2. Update shipment status to completed
        $sql2 = "UPDATE pengiriman_penjahit SET 
                status = 'selesai', 
                tanggal_diterima = ?
                WHERE id_pengiriman_jahit = ?";

        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("si", $tanggal, $id_pengiriman);

        if (!$stmt2->execute()) {
            throw new Exception("Gagal update status: " . $stmt2->error);
        }

        // 3. Update product stock
        $sql3 = "UPDATE produk SET stok = stok + ? WHERE id_produk = ?";

        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("ii", $jumlah, $id_produk);

        if (!$stmt3->execute()) {
            throw new Exception("Gagal update stok: " . $stmt3->error);
        }

        // 4. Record to pembayaran_upah table (status 'terhitung')
        $periode_awal = $tanggal;
        $periode_akhir = $tanggal;

        $sql_pembayaran = "INSERT INTO pembayaran_upah 
                          (id_penerima, jenis_penerima, periode_awal, periode_akhir, total_upah, status)
                          VALUES (?, 'penjahit', ?, ?, ?, 'terhitung')";

        $stmt_pembayaran = $conn->prepare($sql_pembayaran);
        $stmt_pembayaran->bind_param("issd", $id_penjahit, $periode_awal, $periode_akhir, $total_upah);

        if (!$stmt_pembayaran->execute()) {
            throw new Exception("Gagal mencatat pembayaran upah: " . $stmt_pembayaran->error);
        }

        $id_pembayaran = $stmt_pembayaran->insert_id;

        // 5. Record payment details
        $sql_detail = "INSERT INTO detail_pembayaran_upah
                      (id_pembayaran, id_hasil, jenis_hasil, jumlah_unit, tarif_per_unit, subtotal)
                      VALUES (?, ?, 'jahit', ?, ?, ?)";

        $stmt_detail = $conn->prepare($sql_detail);
        $stmt_detail->bind_param(
            "iiidd",
            $id_pembayaran,
            $id_hasil_penjahitan,
            $jumlah,
            $tarif_penjahitan['tarif_per_unit'],
            $total_upah
        );

        if (!$stmt_detail->execute()) {
            throw new Exception("Gagal mencatat detail pembayaran: " . $stmt_detail->error);
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = "Hasil penjahitan berhasil dicatat. Total upah: Rp " . number_format($total_upah, 0, ',', '.');
        header("Location: hasil_penjahitan.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: hasil_penjahitan.php");
        exit();
    }
}
?>

<style>
    /* Paksa SweetAlert berada di atas segalanya */
    .swal2-container {
        z-index: 99999 !important;
    }

    /* Style untuk input upah */
    .upah-container {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
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
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
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
                                                    <?= dateIndo($p['tanggal_kirim']) ?> |
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
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Jumlah Produk Jadi (pcs)</label>
                                        <div class="input-group">
                                            <input type="number" name="jumlah" id="jumlah_produk" min="1" class="form-control" required>
                                            <span class="input-group-text">Pcs</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">

                                        <label class="form-label">Tarif Upah Saat Ini</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control" readonly
                                                value="<?= number_format($tarif_penjahitan['tarif_per_unit'], 0, ',', '.') ?>">
                                            <span class="input-group-text">/Pcs</span>

                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Total Upah</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="total_upah" id="total_upah" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Tanggal Selesai</label>
                                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>

                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control"></textarea>
                                </div>
                                <div class="upah-container mb-4" hidden>
                                    <h5>Informasi Upah Penjahitan</h5>
                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Jumlah Produk Jadi</label>
                                                <input type="number" id="calc_jumlah" class="form-control" min="1" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Total Upah</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="text" id="calc_total_upah" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                    // Hitung total upah otomatis
                                    const tarifUpah = <?= $tarif_penjahitan['tarif_per_unit'] ?>;

                                    // Untuk kotak informasi
                                    document.getElementById('calc_jumlah').addEventListener('input', function() {
                                        const jumlah = parseInt(this.value) || 0;
                                        const total = jumlah * tarifUpah;
                                        document.getElementById('calc_total_upah').value = total.toLocaleString('id-ID');
                                    });

                                    // Untuk form sebenarnya
                                    document.getElementById('jumlah_produk').addEventListener('input', function() {
                                        const jumlah = parseInt(this.value) || 0;
                                        const total = jumlah * tarifUpah;
                                        document.getElementById('total_upah').value = total.toLocaleString('id-ID');
                                        document.getElementById('calc_jumlah').value = jumlah;
                                        document.getElementById('calc_total_upah').value = total.toLocaleString('id-ID');
                                    });

                                    document.getElementById('btnBatalHasil').addEventListener('click', function(e) {
                                        e.preventDefault();
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
                                                window.location.href = 'batal_hasil_penjahitan.php';
                                            }
                                        });
                                    });
                                </script>
                            </form>
                            <small class="text-end text-danger">Pembatalan dapat mengurangi stok produksi.</small>

                            <hr>

                            <h4>Riwayat Hasil Penjahitan</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Penjahit</th>
                                            <th>Produk</th>
                                            <th>Bahan Mentah</th>
                                            <th>Produk Jadi</th>
                                            <th>Tarif</th>
                                            <th>Total Upah</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_history = "
                                            SELECT hp.*, 
                                                p.nama_produk, 
                                                pj.jumlah_bahan_mentah,
                                                pen.nama_penjahit,
                                                t.tarif_per_unit,
                                                DATE_FORMAT(hp.tanggal_selesai, '%d-%m-%Y') as tgl_selesai
                                            FROM hasil_penjahitan hp
                                            JOIN produk p ON hp.id_produk = p.id_produk
                                            JOIN pengiriman_penjahit pj ON hp.id_pengiriman_jahit = pj.id_pengiriman_jahit
                                            JOIN penjahit pen ON pj.id_penjahit = pen.id_penjahit
                                            LEFT JOIN tarif_upah t ON hp.id_tarif = t.id_tarif
                                            ORDER BY hp.tanggal_selesai DESC 
                                            LIMIT 5
                                        ";
                                        $history = query($sql_history);
                                        $no = 1;
                                        foreach ($history as $h):
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= dateIndo($h['tgl_selesai']) ?></td>
                                                <td><?= htmlspecialchars($h['nama_penjahit']) ?></td>
                                                <td><?= htmlspecialchars($h['nama_produk']) ?></td>
                                                <td class="text-center"><?= number_format($h['jumlah_bahan_mentah']) ?> pcs</td>
                                                <td class="text-center"><?= number_format($h['jumlah_produk_jadi']) ?> pcs</td>
                                                <td class="text-center">Rp <?= number_format($h['tarif_per_unit'] ?? 0, 0, ',', '.') ?></td>
                                                <td class="text-center">Rp <?= number_format($h['total_upah'] ?? 0, 0, ',', '.') ?></td>
                                                <td><?= htmlspecialchars($h['keterangan']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($history)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center">Belum ada data hasil penjahitan.</td>
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