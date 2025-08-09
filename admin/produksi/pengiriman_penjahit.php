<?php
require_once __DIR__ . '../../includes/header.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';
// redirectIfNotLoggedIn();
// checkRole('admin');

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

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_hasil = intval($_POST['id_hasil_potong']);
    $id_penjahit = intval($_POST['id_penjahit']);
    $jumlah = intval($_POST['jumlah']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);

    // Hitung sisa stok dari hasil potong
    $sql_sisa = "
        SELECT 
            jumlah_hasil - IFNULL((
                SELECT SUM(jumlah_bahan_mentah) 
                FROM pengiriman_penjahit 
                WHERE id_hasil_potong = $id_hasil
            ), 0) AS sisa_stok 
        FROM hasil_pemotongan
        WHERE id_hasil_potong = $id_hasil
    ";
    $sisa = query($sql_sisa)[0]['sisa_stok'];

    if ($jumlah > $sisa) {
        $error = "Jumlah melebihi stok bahan mentah tersedia (Sisa: $sisa)";
    } else {
        $sql_insert = "INSERT INTO pengiriman_penjahit 
            (id_hasil_potong, id_penjahit, jumlah_bahan_mentah, tanggal_kirim)
            VALUES ($id_hasil, $id_penjahit, $jumlah, '$tanggal')";
        if ($conn->query($sql_insert)) {
            $_SESSION['success'] = "Pengiriman ke penjahit berhasil dicatat";
            header("Location: pengiriman_penjahit.php");
            exit();
        } else {
            $error = "Gagal mencatat pengiriman: " . $conn->error;
        }
    }
}

// Ambil data hasil potong dan jumlah yang sudah dikirim
$sql_hasil = "
    SELECT 
        h.id_hasil_potong, 
        h.jumlah_hasil,
        p.nama_pemotong,
        DATE_FORMAT(h.tanggal_selesai, '%d-%m-%Y') as tgl_selesai,
        IFNULL((
            SELECT SUM(jumlah_bahan_mentah) 
            FROM pengiriman_penjahit 
            WHERE id_hasil_potong = h.id_hasil_potong
        ), 0) as jumlah_dikirim
    FROM hasil_pemotongan h
    JOIN pengiriman_pemotong pp ON h.id_pengiriman_potong = pp.id_pengiriman_potong
    JOIN pemotong p ON pp.id_pemotong = p.id_pemotong
";
$hasil_potong = query($sql_hasil);

// Ambil data penjahit
$penjahit = query("SELECT * FROM penjahit ORDER BY nama_penjahit");
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
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            <?php include '../includes/sidebar.php'; ?>

            <!-- Layout page -->
            <div class="layout-page">
                <!-- Navbar -->
                <?php include '../includes/navbar.php'; ?>

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>3. Tambah Data Pengiriman Penjahit</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <a href="hasil_pemotongan.php" class="btn btn-outline-warning">Kembali</a>
                                <a href="hasil_penjahitan.php" class="btn btn-outline-primary">Next</a>
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
                                        <label class="form-label">Pilih Hasil Potongan</label>
                                        <select name="id_hasil_potong" class="form-select" required>
                                            <option value="">-- Pilih Potong --</option>
                                            <?php foreach ($hasil_potong as $hp): ?>
                                                <?php
                                                $sisa_stok = $hp['jumlah_hasil'] - $hp['jumlah_dikirim'];
                                                if ($sisa_stok <= 0) continue;
                                                ?>
                                                <option value="<?= $hp['id_hasil_potong'] ?>">
                                                    Selesai Potong Tanggal <?= dateIndo($hp['tgl_selesai']) . " | {$hp['nama_pemotong']} : {$sisa_stok} pcs" ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Penjahit</label>
                                        <select name="id_penjahit" class="form-select" required>
                                            <option value="">-- Pilih Penjahit --</option>
                                            <?php foreach ($penjahit as $p): ?>
                                                <option value="<?= $p['id_penjahit'] ?>"><?= $p['nama_penjahit'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Jumlah Bahan Mentah (pcs)</label>
                                        <div class="input-group">
                                            <input type="number" name="jumlah" min="1" class="form-control" required>
                                            <span class="input-group-text">Pcs</span>
                                        </div>
                                    </div>

                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Tanggal Pengiriman <span class="text-danger">(Bulan/Tanggal/Tahun)</span></label>
                                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">Simpan Pengiriman</button>
                                    <div class="btn-group">
                                        <a href="riwayat_pengiriman_penjahit.php" class="btn btn-secondary">Riwayat Pengiriman</a>
                                        <a href="#" class="btn btn-danger" id="btnBatalPengiriman">
                                            Batal Simpan Pengiriman
                                        </a>
                                    </div>
                                </div>
                                <script>
                                    document.getElementById('btnBatalPengiriman').addEventListener('click', function(e) {
                                        e.preventDefault(); // Mencegah link langsung berjalan

                                        Swal.fire({
                                            title: 'Yakin?',
                                            text: "Ingin membatalkan pengiriman terakhir?",
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#d33',
                                            cancelButtonColor: '#6c757d',
                                            confirmButtonText: 'Ya, batalkan!',
                                            cancelButtonText: 'Batal'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                // Redirect manual
                                                window.location.href = 'batal_pengiriman_penjahit.php';
                                            }
                                        });
                                    });
                                </script>

                            </form>
                            <small class="text-end text-danger">Pembatalan tidak dapat dilakukan karena data sudah masuk ke tahap penjahitan.</small>

                            <hr>

                            <h4>Riwayat Pengiriman</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Penjahit</th>
                                            <th>Jumlah Kirim</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_history = "
                                            SELECT pj.*, p.nama_penjahit, pj.tanggal_kirim
                                            FROM pengiriman_penjahit pj
                                            JOIN penjahit p ON pj.id_penjahit = p.id_penjahit
                                            ORDER BY pj.tanggal_kirim DESC LIMIT 5
                                        ";

                                        $history = query($sql_history);
                                        $no = 1;
                                        foreach ($history as $h):
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= dateIndo($h['tanggal_kirim']) ?></td>
                                                <td><?= $h['nama_penjahit'] ?></td>
                                                <td class="text-center"><?= $h['jumlah_bahan_mentah'] ?> pcs</td>
                                                <td class="text-center">
                                                    <span class="badge <?= $h['status'] == 'dikirim' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                        <?= $h['status'] == 'dikirim' ? 'Dalam Proses' : 'Selesai' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($history)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Belum ada pengiriman ke penjahit.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <script>
        document.querySelector('select[name="id_hasil_potong"]').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const sisa = selected.textContent.match(/(\d+) pcs tersedia/);
            if (sisa) {
                const max = parseInt(sisa[1]);
                document.querySelector('input[name="jumlah"]').setAttribute('max', max);
            }
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>

</html>