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

// Ambil filter GET
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$id_penjahit = $_GET['penjahit'] ?? '';
$id_bahan = $_GET['bahan'] ?? '';

// Ambil data filter untuk select option
$list_penjahit = query("SELECT * FROM penjahit ORDER BY nama_penjahit");
$list_bahan = query("SELECT * FROM bahan_baku ORDER BY nama_bahan");

// Query riwayat dengan filter
$where = [];

if ($tgl_awal && $tgl_akhir) {
    $where[] = "h.tanggal_selesai BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}
if ($id_penjahit) {
    $where[] = "pg.id_penjahit = $id_penjahit";
}
if ($id_bahan) {
    $where[] = "pg.id_bahan = $id_bahan";
}

$where_sql = count($where) > 0 ? "WHERE " . implode(' AND ', $where) : "";


// Ubah query riwayat untuk menambahkan data upah
// $riwayat = query("SELECT h.*, p.nama_bahan, p.satuan, pm.nama_pemotong, pg.jumlah_bahan, 
//                   t.tarif_per_unit, h.total_upah
//                   FROM hasil_penjahitan h
//                   JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
//                   JOIN bahan_baku p ON pg.id_bahan = p.id_bahan
//                   JOIN pemotong pm ON pg.id_pemotong = pm.id_pemotong
//                   LEFT JOIN tarif_upah t ON h.id_tarif = t.id_tarif
//                   $where_sql
//                   ORDER BY h.tanggal_selesai DESC");

// Ubah query riwayat untuk mengecek status pembayaran
// $riwayat = query("
//     SELECT 
//         h.id_hasil_potong, h.tanggal_selesai, h.jumlah_hasil, h.total_upah,
//         p.nama_bahan, p.satuan, 
//         pm.nama_pemotong, 
//         pg.jumlah_bahan, pg.id_pemotong,
//         t.tarif_per_unit,
//         d.id_detail, pb.id_pembayaran,
//         CASE WHEN d.id_detail IS NULL THEN 'Belum Dibayar' ELSE 'Sudah Dibayar' END as status_pembayaran
//     FROM hasil_penjahitan h
//     JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
//     JOIN bahan_baku p ON pg.id_bahan = p.id_bahan
//     JOIN pemotong pm ON pg.id_pemotong = pm.id_pemotong
//     LEFT JOIN tarif_upah t ON h.id_tarif = t.id_tarif
//     LEFT JOIN detail_pembayaran_upah d ON h.id_hasil_potong = d.id_hasil AND d.jenis_hasil = 'potong'
//     LEFT JOIN pembayaran_upah pb ON d.id_pembayaran = pb.id_pembayaran
//     $where_sql
//     ORDER BY h.tanggal_selesai DESC
// ");

// Ubah query riwayat untuk mendapatkan lebih banyak data pembayaran
// $riwayat = query("
//     SELECT 
//         h.id_hasil_jahit, h.tanggal_selesai, h.jumlah_produk_jadi, h.total_upah,
//         p.nama_produk,
//         pm.nama_penjahit, 
//         pg.id_pengiriman_jahit, pg.id_penjahit,
//         t.tarif_per_unit,
//         d.id_detail, pb.id_pembayaran, pb.status as status_pembayaran
//     FROM hasil_penjahitan h
//     JOIN pengiriman_penjahit pg ON h.id_pengiriman_penjahit = pg.id_pengiriman_penjahit
//     JOIN produk p ON pg.id_produk = p.id_produk
//     JOIN penjahit pm ON pg.id_penjahit = pm.id_penjahit
//     LEFT JOIN tarif_upah t ON h.id_tarif = t.id_tarif
//     LEFT JOIN detail_pembayaran_upah d ON h.id_hasil_jahit = d.id_hasil AND d.jenis_hasil = 'penjahitan'
//     LEFT JOIN pembayaran_upah pb ON d.id_pembayaran = pb.id_pembayaran
//     $where_sql
//     ORDER BY h.tanggal_selesai DESC
// ");

// Ubah query riwayat untuk mendapatkan lebih banyak data pembayaran upah jahit
// $riwayat = query("
//     SELECT 
//         h.id_hasil_jahit, h.tanggal_selesai, h.jumlah_produk_jadi, h.total_upah,
//         p.nama_produk, 
//         pm.nama_penjahit, 
//         pg.id_penjahit,
//         t.tarif_per_unit,
//         d.id_detail, pb.id_pembayaran, pb.status as status_pembayaran
//     FROM hasil_penjahitan h
//     JOIN pengiriman_penjahit pg ON h.id_pengiriman_penjahit = pg.id_pengiriman_penjahit
//     JOIN produk p ON pg.id_hasil_jahit = p.id_produk
//     JOIN penjahit pm ON pg.id_penjahit = pm.id_penjahit
//     LEFT JOIN tarif_upah t ON h.id_tarif = t.id_tarif
//     LEFT JOIN detail_pembayaran_upah d ON h.id_hasil_jahit = d.id_hasil AND d.jenis_hasil = 'penjahitan'
//     LEFT JOIN pembayaran_upah pb ON d.id_pembayaran = pb.id_pembayaran
//     $where_sql
//     ORDER BY h.tanggal_selesai DESC
// ");

$riwayat = query("
    SELECT 
        h.id_hasil_jahit, 
        h.tanggal_selesai, 
        h.jumlah_produk_jadi, 
        h.total_upah,
        h.keterangan,
        p.nama_produk, 
        pj.nama_penjahit, 
        pj.id_penjahit,
        t.tarif_per_unit,
        pu.id_pembayaran,
        pu.status as status_pembayaran,
        pu.tanggal_bayar,
        DATE_FORMAT(h.tanggal_selesai, '%d %M %Y') as tanggal_format,
        pp.jumlah_bahan_mentah,
        hp.jumlah_hasil as bahan_mentah_dari_pemotongan
    FROM hasil_penjahitan h
    JOIN pengiriman_penjahit pp ON h.id_pengiriman_jahit = pp.id_pengiriman_jahit
    JOIN hasil_pemotongan hp ON pp.id_hasil_potong = hp.id_hasil_potong
    JOIN produk p ON h.id_produk = p.id_produk
    JOIN penjahit pj ON pp.id_penjahit = pj.id_penjahit
    LEFT JOIN tarif_upah t ON h.id_tarif = t.id_tarif
    LEFT JOIN detail_pembayaran_upah dpu ON (
        dpu.id_hasil = h.id_hasil_jahit AND 
        dpu.jenis_hasil = 'jahit'
    )
    LEFT JOIN pembayaran_upah pu ON dpu.id_pembayaran = pu.id_pembayaran
    ORDER BY h.tanggal_selesai DESC
    LIMIT 50
");

// Tambahkan query ini setelah query $riwayat
// $total_upah_per_pemotong = query("
//     SELECT pm.id_pemotong, pm.nama_pemotong, 
//            SUM(h.total_upah) as total_upah,
//            COUNT(h.id_hasil_potong) as jumlah_produksi
//     FROM hasil_penjahitan h
//     JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
//     JOIN pemotong pm ON pg.id_pemotong = pm.id_pemotong
//     $where_sql
//     GROUP BY pm.id_pemotong, pm.nama_pemotong
//     ORDER BY total_upah DESC
// ");

// $total_upah_per_pemotong = query("
//     SELECT pm.id_pemotong, pm.nama_pemotong, 
//            SUM(h.total_upah) as total_upah,
//            COUNT(h.id_hasil_potong) as jumlah_produksi
//     FROM hasil_penjahitan h
//     JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
//     JOIN pemotong pm ON pg.id_pemotong = pm.id_pemotong
//     LEFT JOIN detail_pembayaran_upah d ON h.id_hasil_potong = d.id_hasil AND d.jenis_hasil = 'potong'
//     WHERE d.id_detail IS NULL
//     " . ($where_sql ? " AND $where_sql" : "") . "
//     GROUP BY pm.id_pemotong, pm.nama_pemotong
//     HAVING SUM(h.total_upah)
//     ORDER BY total_upah DESC
// ");

$total_upah_per_penjahit = query("
    SELECT pm.id_penjahit, pm.nama_penjahit, 
           SUM(h.total_upah) - IFNULL((
               SELECT SUM(c.jumlah_cicilan) 
               FROM cicilan_upah c
               JOIN pembayaran_upah pu ON c.id_pembayaran = pu.id_pembayaran
               WHERE pu.id_penerima = pm.id_penjahit AND pu.jenis_penerima = 'penjahit'
           ), 0) as total_upah,
           COUNT(h.id_hasil_jahit) as jumlah_produksi
    FROM hasil_penjahitan h
    JOIN pengiriman_penjahit pg ON h.id_pengiriman_jahit = pg.id_pengiriman_jahit
    JOIN penjahit pm ON pg.id_penjahit = pm.id_penjahit
    LEFT JOIN detail_pembayaran_upah d ON h.id_hasil_jahit = d.id_hasil AND d.jenis_hasil = 'penjahitan'
    WHERE d.id_detail IS NULL
    " . ($where_sql ? " AND $where_sql" : "") . "
    GROUP BY pm.id_penjahit, pm.nama_penjahit
    HAVING total_upah > 0
    ORDER BY total_upah DESC
");

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
                            <h2>Riwayat Data Hasil penjahitan</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <a href="list.php" class="btn btn-secondary">Kembali</a>
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
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>

                            <form class="row g-3 mb-4" method="get" hidden>
                                <div class="col-md-3">
                                    <label for="tgl_awal" class="form-label">Tanggal Awal</label>
                                    <input type="date" id="tgl_awal" name="tgl_awal" class="form-control" value="<?= htmlspecialchars($tgl_awal) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="tgl_akhir" class="form-label">Tanggal Akhir</label>
                                    <input type="date" id="tgl_akhir" name="tgl_akhir" class="form-control" value="<?= htmlspecialchars($tgl_akhir) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="penjahit" class="form-label">Penjahit</label>
                                    <select name="penjahit" id="penjahit" class="form-select">
                                        <option value="">-- Semua Penjahit --</option>
                                        <?php foreach ($list_penjahit as $p): ?>
                                            <option value="<?= $p['id_penjahit'] ?>" <?= $id_penjahit == $p['id_penjahit'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['nama_penjahit']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="bahan" class="form-label">Bahan Baku</label>
                                    <select name="bahan" id="bahan" class="form-select">
                                        <option value="">-- Semua Bahan --</option>
                                        <?php foreach ($list_bahan as $b): ?>
                                            <option value="<?= $b['id_bahan'] ?>" <?= $id_bahan == $b['id_bahan'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($b['nama_bahan']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary me-2">Terapkan Filter</button>
                                    <a href="riwayat_hasil_penjahitan.php" class="btn btn-secondary">Reset</a>
                                </div>
                            </form>


                            <!-- tabel -->
                            <h4 class="mb-3">Tabel Riwayat Hasil penjahitan</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Tanggal</th>

                                            <th>Penjahit</th>
                                            <th>Bahan Mentah</th>
                                            <th>Produk Jadi (pcs)</th>
                                            <th>Tarif per Unit</th>
                                            <th>Total Upah</th>
                                            <th>Status Pembayaran</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($riwayat)): ?>
                                            <?php $no = 1;
                                            foreach ($riwayat as $r): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><?= dateIndo($r['tanggal_selesai'] ?? '') ?></td>

                                                    <td><?= htmlspecialchars($r['nama_penjahit'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($r['jumlah_bahan_mentah'] ?? '') ?></td>

                                                    <td class="text-center">
                                                        <?= isset($r['jumlah_produk_jadi']) ? number_format($r['jumlah_produk_jadi'], 0, "", "") . ' pcs' : '-' ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <?= isset($r['tarif_per_unit']) ? 'Rp ' . number_format($r['tarif_per_unit'], 0, ',', '.') : '-' ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <?= isset($r['total_upah']) ? 'Rp ' . number_format($r['total_upah'], 0, ',', '.') : '-' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (isset($r['status_pembayaran'])): ?>
                                                            <?php if ($r['status_pembayaran'] == 'dibayar'): ?>
                                                                <span class="badge bg-success">Sudah Dibayar</span>
                                                                <?php if (!empty($r['id_pembayaran'])): ?>
                                                                    <div class="btn-group mt-1" role="group">
                                                                        <a href="detail_pembayaran.php?id=<?= $r['id_pembayaran'] ?>"
                                                                            class="btn btn-sm btn-info" hidden>Detail</a>
                                                                        <button class="btn btn-sm btn-danger btn-batal-bayar"
                                                                            data-id="<?= $r['id_pembayaran'] ?>"
                                                                            data-nama="<?= htmlspecialchars($r['nama_penjahit']) ?>" hidden>
                                                                            Batal
                                                                        </button>
                                                                    </div>
                                                                <?php endif; ?>

                                                            <?php elseif ($r['status_pembayaran'] == 'terhitung'): ?>
                                                                <span class="badge bg-warning text-dark">Terhitung</span>

                                                            <?php elseif ($r['status_pembayaran'] == 'dibatalkan'): ?>
                                                                <span class="badge bg-danger">Dibatalkan</span>

                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Status Tidak Dikenal</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Status Tidak Diketahui</span>
                                                        <?php endif; ?>

                                                        <?php if (!empty($r['id_pembayaran'])): ?>
                                                            <a href="detail_pembayaran.php?id=<?= $r['id_pembayaran'] ?>"
                                                                class="btn btn-sm btn-primary mt-1">
                                                                Detail Pembayaran
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center">Belum ada data hasil penjahitan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Tambahkan setelah tabel riwayat -->
                            <!-- <div class="card mt-4">
                                <div class="card-header">
                                    <h5>Ringkasan Total Upah Belum Dibayar per Penjahit</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>No</th>
                                                    <th>Nama Penjahit</th>
                                                    <th>Jumlah Produksi</th>
                                                    <th>Total Upah Belum Dibayar</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($total_upah_per_penjahit)): ?>
                                                    <?php $no_total = 1;
                                                    foreach ($total_upah_per_penjahit as $total): ?>
                                                        <tr>
                                                            <td class="text-center"><?= $no_total++ ?></td>
                                                            <td><?= htmlspecialchars($total['nama_penjahit']) ?></td>
                                                            <td class="text-center"><?= $total['jumlah_produksi'] ?></td>
                                                            <td class="text-end">Rp <?= number_format($total['total_upah'], 0, ',', '.') ?></td>

                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">Tidak ada upah yang belum dibayarkan</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>

                    <!-- / Content -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Tambahkan sebelum penutup body -->
        <!-- Modal Pembayaran -->
        <div class="modal fade" id="modalPembayaran" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pembayaran Upah Penjahit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="proses_pembayaran_upah.php" method="post">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Penjahit</label>
                                <input type="text" class="form-control" id="nama_penjahit_modal" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Upah</label>
                                <input type="text" class="form-control" id="total_upah_modal" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                                <select class="form-select" name="metode_pembayaran" required>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="tunai">Tunai</option>
                                    <option value="e-wallet">E-Wallet</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_pembayaran" class="form-label">Tanggal Pembayaran</label>
                                <input type="date" class="form-control" name="tanggal_pembayaran" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea class="form-control" name="keterangan" rows="3"></textarea>
                            </div>
                            <input type="hidden" name="id_penjahit" id="id_penjahit_modal">
                            <input type="hidden" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">
                            <input type="hidden" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Batal Pembayaran -->
        <div class="modal fade" id="modalBatalPembayaran" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Pembatalan Pembayaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="batal_pembayaran_upah.php" method="post">
                        <div class="modal-body">
                            <p>Anda yakin ingin membatalkan pembayaran untuk:</p>
                            <p><strong id="nama_penjahit_batal"></strong></p>
                            <input type="hidden" name="id_pembayaran" id="id_pembayaran_batal">
                            <div class="mb-3">
                                <label for="alasan" class="form-label">Alasan Pembatalan</label>
                                <textarea class="form-control" name="alasan" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-danger">Konfirmasi Pembatalan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            // Tangani klik tombol bayar
            document.querySelectorAll('.btn-bayar').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');
                    const total = this.getAttribute('data-total');

                    document.getElementById('nama_penjahit_modal').value = nama;
                    document.getElementById('total_upah_modal').value = 'Rp ' + parseInt(total).toLocaleString('id-ID');
                    document.getElementById('id_penjahit_modal').value = id;

                    var modal = new bootstrap.Modal(document.getElementById('modalPembayaran'));
                    modal.show();
                });
            });

            // Tangani klik tombol batal bayar
            document.querySelectorAll('.btn-batal-bayar').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');

                    document.getElementById('id_pembayaran_batal').value = id;
                    document.getElementById('nama_penjahit_batal').textContent = nama;

                    var modal = new bootstrap.Modal(document.getElementById('modalBatalPembayaran'));
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