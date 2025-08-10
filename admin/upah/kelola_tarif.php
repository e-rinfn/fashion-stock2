<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/general_functions.php';
require_once __DIR__ . '/../functions/upah_functions.php';

$title = "Kelola Tarif Upah";
include __DIR__ . '/../includes/header.php';

// Proses tambah tarif
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_tarif'])) {
    if (tambahTarif($conn, $_POST)) {
        $success = "Tarif upah berhasil ditambahkan";
    } else {
        $error = "Gagal menambahkan tarif: " . mysqli_error($conn);
    }
}

// Dapatkan daftar tarif
$query = "SELECT * FROM tarif_upah ORDER BY jenis_tarif, berlaku_sejak DESC";
$result = mysqli_query($conn, $query);
$tarif_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tarif_list[] = $row;
}

// Dapatkan tarif aktif saat ini
$query = "SELECT t.* FROM tarif_upah t
          INNER JOIN (
              SELECT jenis_tarif, MAX(berlaku_sejak) as max_tanggal
              FROM tarif_upah
              WHERE berlaku_sejak <= CURDATE()
              GROUP BY jenis_tarif
          ) tm ON t.jenis_tarif = tm.jenis_tarif AND t.berlaku_sejak = tm.max_tanggal";
$result = mysqli_query($conn, $query);
$tarif_aktif = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tarif_aktif[$row['jenis_tarif']] = $row;
}
?>

<div class="container">
    <h2 class="my-4">Kelola Tarif Upah</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Tarif Aktif Saat Ini</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Tarif/Unit</th>
                                <th>Berlaku Sejak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tarif_aktif as $jenis => $tarif): ?>
                                <tr>
                                    <td><?= ucfirst($jenis) ?></td>
                                    <td><?= formatRupiah($tarif['tarif_per_unit']) ?></td>
                                    <td><?= formatTanggal($tarif['berlaku_sejak']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Tambah Tarif Baru</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="jenis_tarif" class="form-label">Jenis Tarif</label>
                            <select class="form-select" id="jenis_tarif" name="jenis_tarif" required>
                                <option value="">Pilih Jenis</option>
                                <option value="pemotongan">Pemotongan</option>
                                <option value="penjahitan">Penjahitan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tarif_per_unit" class="form-label">Tarif per Unit (Rp)</label>
                            <input type="number" class="form-control" id="tarif_per_unit"
                                name="tarif_per_unit" min="0" step="100" required>
                        </div>

                        <div class="mb-3">
                            <label for="berlaku_sejak" class="form-label">Berlaku Sejak</label>
                            <input type="date" class="form-control" id="berlaku_sejak"
                                name="berlaku_sejak" required value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
                        </div>

                        <button type="submit" name="tambah_tarif" class="btn btn-primary">Simpan Tarif</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Riwayat Tarif</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Tarif/Unit</th>
                        <th>Berlaku Sejak</th>
                        <th>Keterangan</th>
                        <th>Dibuat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tarif_list as $tarif): ?>
                        <tr>
                            <td><?= ucfirst($tarif['jenis_tarif']) ?></td>
                            <td><?= formatRupiah($tarif['tarif_per_unit']) ?></td>
                            <td><?= formatTanggal($tarif['berlaku_sejak']) ?></td>
                            <td><?= $tarif['keterangan'] ?></td>
                            <td><?= formatTanggal($tarif['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>