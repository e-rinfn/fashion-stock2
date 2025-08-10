<?php
require_once __DIR__ . '/../includes/header.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

$id_pembayaran = intval($_GET['id']);

// Ambil data pembayaran
$pembayaran = query("SELECT pu.*, 
                    CASE 
                        WHEN pu.jenis_penerima = 'pemotong' THEN p.nama_pemotong
                        ELSE j.nama_penjahit
                    END as nama_penerima
                    FROM pembayaran_upah pu
                    LEFT JOIN pemotong p ON pu.id_penerima = p.id_pemotong AND pu.jenis_penerima = 'pemotong'
                    LEFT JOIN penjahit j ON pu.id_penerima = j.id_penjahit AND pu.jenis_penerima = 'penjahit'
                    WHERE pu.id_pembayaran = $id_pembayaran");

if (empty($pembayaran)) {
    $_SESSION['error'] = "Data pembayaran tidak ditemukan";
    header("Location: pembayaran_upah.php");
    exit();
}

$pembayaran = $pembayaran[0];

// Ambil detail pembayaran
$detail_pembayaran = query("SELECT d.*, 
                           CASE 
                               WHEN d.jenis_hasil = 'potong' THEN hp.jumlah_hasil
                               ELSE hj.jumlah_produk_jadi
                           END as jumlah_unit,
                           CASE 
                               WHEN d.jenis_hasil = 'potong' THEN hp.tanggal_selesai
                               ELSE hj.tanggal_selesai
                           END as tanggal_hasil
                           FROM detail_pembayaran_upah d
                           LEFT JOIN hasil_pemotongan hp ON d.id_hasil = hp.id_hasil_potong AND d.jenis_hasil = 'potong'
                           LEFT JOIN hasil_penjahitan hj ON d.id_hasil = hj.id_hasil_jahit AND d.jenis_hasil = 'jahit'
                           WHERE d.id_pembayaran = $id_pembayaran");
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h2>Detail Pembayaran Upah #<?= $pembayaran['id_pembayaran'] ?></h2>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Penerima</th>
                            <td><?= $pembayaran['nama_penerima'] ?> (<?= ucfirst($pembayaran['jenis_penerima']) ?>)</td>
                        </tr>
                        <tr>
                            <th>Periode</th>
                            <td><?= dateIndo($pembayaran['periode_awal']) ?> - <?= dateIndo($pembayaran['periode_akhir']) ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Pembayaran</th>
                            <td><?= dateIndo($pembayaran['tanggal_bayar']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Total Upah</th>
                            <td>Rp <?= number_format($pembayaran['total_upah'], 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <th>Metode Pembayaran</th>
                            <td><?= ucfirst($pembayaran['metode_pembayaran']) ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?= $pembayaran['status'] == 'dibayar' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($pembayaran['status']) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <h5>Catatan:</h5>
                <p><?= $pembayaran['catatan'] ?: '-' ?></p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Detail Item Pembayaran</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Hasil</th>
                            <th>Jenis</th>
                            <th>Jumlah Unit</th>
                            <th>Tarif/Unit</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($detail_pembayaran as $d): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= dateIndo($d['tanggal_hasil']) ?></td>
                                <td><?= ucfirst($d['jenis_hasil']) ?></td>
                                <td><?= number_format($d['jumlah_unit']) ?> pcs</td>
                                <td>Rp <?= number_format($d['tarif_per_unit'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="pembayaran_upah.php" class="btn btn-secondary">Kembali</a>
        <button onclick="window.print()" class="btn btn-primary">Cetak</button>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>