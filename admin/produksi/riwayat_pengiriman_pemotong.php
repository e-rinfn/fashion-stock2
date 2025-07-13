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

// Ambil semua pemotong untuk dropdown
$all_pemotong = query("SELECT * FROM pemotong ORDER BY nama_pemotong");

// Ambil filter dari URL
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$id_pemotong = $_GET['id_pemotong'] ?? '';

// Buat filter query
$where = "WHERE 1=1";
if (!empty($tgl_awal)) {
    $where .= " AND p.tanggal_kirim >= '$tgl_awal'";
}
if (!empty($tgl_akhir)) {
    $where .= " AND p.tanggal_kirim <= '$tgl_akhir'";
}
if (!empty($id_pemotong)) {
    $where .= " AND p.id_pemotong = $id_pemotong";
}

// Ambil data pengiriman berdasarkan filter
$pengiriman = query("SELECT p.*, b.nama_bahan, b.satuan, pm.nama_pemotong 
                    FROM pengiriman_pemotong p
                    JOIN bahan_baku b ON p.id_bahan = b.id_bahan
                    JOIN pemotong pm ON p.id_pemotong = pm.id_pemotong
                    $where
                    ORDER BY p.tanggal_kirim DESC");
?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include '../includes/sidebar.php'; ?>
            <div class="layout-page">
                <?php include '../includes/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Riwayat Data Pengiriman Pemotong</h2>
                            <a href="pengiriman_pemotong.php" class="btn btn-secondary">Kembali</a>
                        </div>

                        <div class="card p-4 shadow-sm">
                            <!-- Filter Form -->
                            <form method="get" class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Awal</label>
                                    <input type="date" name="tgl_awal" class="form-control" value="<?= htmlspecialchars($tgl_awal) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Akhir</label>
                                    <input type="date" name="tgl_akhir" class="form-control" value="<?= htmlspecialchars($tgl_akhir) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pemotong</label>
                                    <select name="id_pemotong" class="form-select">
                                        <option value="">-- Semua Pemotong --</option>
                                        <?php foreach ($all_pemotong as $pm): ?>
                                            <option value="<?= $pm['id_pemotong'] ?>" <?= ($id_pemotong == $pm['id_pemotong']) ? 'selected' : '' ?>>
                                                <?= $pm['nama_pemotong'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary me-2">Terapkan Filter</button>
                                    <a href="riwayat_pengiriman_pemotong.php" class="btn btn-secondary">Reset</a>
                                </div>
                            </form>

                            <h4>Tabel Riwayat Pengiriman</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th>Tanggal</th>
                                            <th>Bahan Baku</th>
                                            <th>Pemotong</th>
                                            <th>Jumlah</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($pengiriman as $p): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= dateIndo($p['tanggal_kirim']) ?></td>
                                                <td><?= $p['nama_bahan'] ?></td>
                                                <td><?= $p['nama_pemotong'] ?></td>
                                                <td class="text-center"><?= number_format($p['jumlah_bahan'], 0, "", "") . " " . htmlspecialchars($p['satuan']) ?></td>
                                                <td class="text-center">
                                                    <span class="badge <?= $p['status'] == 'dikirim' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                        <?= $p['status'] == 'dikirim' ? 'Dalam Proses' : 'Selesai' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($pengiriman)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Belum ada data pengiriman.</td>
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
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>

</html>