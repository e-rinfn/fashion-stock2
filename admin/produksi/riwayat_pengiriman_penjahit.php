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

$filter = [];
if (!empty($_GET['penjahit'])) {
    $id_penjahit = intval($_GET['penjahit']);
    $filter[] = "pj.id_penjahit = $id_penjahit";
}
if (!empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $filter[] = "pj.status = '$status'";
}
if (!empty($_GET['tanggal_awal']) && !empty($_GET['tanggal_akhir'])) {
    $tgl_awal = $conn->real_escape_string($_GET['tanggal_awal']);
    $tgl_akhir = $conn->real_escape_string($_GET['tanggal_akhir']);
    $filter[] = "pj.tanggal_kirim BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$where = count($filter) ? 'WHERE ' . implode(' AND ', $filter) : '';

$sql_history = "
    SELECT pj.*, p.nama_penjahit, 
    DATE_FORMAT(pj.tanggal_kirim, '%d-%m-%Y') as tgl_kirim
    FROM pengiriman_penjahit pj
    JOIN penjahit p ON pj.id_penjahit = p.id_penjahit
    $where
    ORDER BY pj.tanggal_kirim DESC
";
$history = query($sql_history);


// Ambil data penjahit
$penjahit = query("SELECT * FROM penjahit ORDER BY nama_penjahit");
?>

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
                            <h2>Riwayat Data Pengiriman Penjahit</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <!-- <a href="#" class="btn btn-outline-warning">Kembali</a> -->
                                <a href="pengiriman_penjahit.php" class="btn btn-secondary">Kembali</a>
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

                            <form method="GET" class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Tanggal Awal</label>
                                    <input type="date" name="tanggal_awal" class="form-control" value="<?= $_GET['tanggal_awal'] ?? '' ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tanggal Akhir</label>
                                    <input type="date" name="tanggal_akhir" class="form-control" value="<?= $_GET['tanggal_akhir'] ?? '' ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Penjahit</label>
                                    <select name="penjahit" class="form-select">
                                        <option value="">Semua Penjahit</option>
                                        <?php foreach ($penjahit as $p): ?>
                                            <option value="<?= $p['id_penjahit'] ?>" <?= isset($_GET['penjahit']) && $_GET['penjahit'] == $p['id_penjahit'] ? 'selected' : '' ?>>
                                                <?= $p['nama_penjahit'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">Semua</option>
                                        <option value="dikirim" <?= isset($_GET['status']) && $_GET['status'] == 'dikirim' ? 'selected' : '' ?>>Dalam Proses</option>
                                        <option value="selesai" <?= isset($_GET['status']) && $_GET['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                    </select>
                                </div>

                                <div class="col-md-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary me-2">Terapkan Filter</button>
                                    <a href="riwayat_pengiriman_penjahit.php" class="btn btn-secondary">Reset</a>
                                </div>
                            </form>

                            <h4>Tabel Riwayat Pengiriman Penjahit</h3>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered mt-3">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>Tanggal</th>
                                                <th>Penjahit</th>
                                                <th>Jumlah</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $no = 1;
                                            foreach ($history as $h):
                                            ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><?= dateIndo($h['tgl_kirim']) ?></td>
                                                    <td><?= $h['nama_penjahit'] ?></td>
                                                    <td><?= $h['jumlah_bahan_mentah'] ?> pcs</td>
                                                    <td class="text-center">
                                                        <span class="badge <?= $h['status'] == 'dikirim' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                            <?= $h['status'] == 'dikirim' ? 'Dalam Proses' : 'Selesai' ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($history)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">Belum ada data pengiriman.</td>
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