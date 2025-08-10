<?php
require_once '../includes/header.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

$title = "Input Hasil Pemotongan";
include __DIR__ . '/../includes/header.php';

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pengiriman_potong = (int)$_POST['id_pengiriman_potong'];
    $jumlah_hasil = (int)$_POST['jumlah_hasil'];
    $tanggal_selesai = sanitizeInput($conn, $_POST['tanggal_selesai']);
    $keterangan = sanitizeInput($conn, $_POST['keterangan']);

    // Insert data hasil pemotongan
    $query = "INSERT INTO hasil_pemotongan 
              (id_pengiriman_potong, jumlah_hasil, tanggal_selesai, keterangan)
              VALUES ($id_pengiriman_potong, $jumlah_hasil, '$tanggal_selesai', '$keterangan')";

    if (mysqli_query($conn, $query)) {
        $id_hasil_potong = mysqli_insert_id($conn);

        // Hitung upah otomatis
        $total_upah = hitungUpahPemotong($conn, $id_hasil_potong);

        if ($total_upah !== false) {
            $success = "Hasil pemotongan berhasil disimpan. Total upah: " . formatRupiah($total_upah);
        } else {
            $error = "Hasil pemotongan disimpan tetapi terjadi kesalahan dalam menghitung upah";
        }

        // Update status pengiriman jika selesai
        $query = "UPDATE pengiriman_pemotong SET status = 'selesai' 
                 WHERE id_pengiriman_potong = $id_pengiriman_potong";
        mysqli_query($conn, $query);
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Dapatkan daftar pengiriman yang belum selesai
$query = "SELECT * FROM pengiriman_pemotong WHERE status = 'dikirim'";
$result = mysqli_query($conn, $query);
$pengiriman_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $pengiriman_list[] = $row;
}
?>

<div class="container">
    <h2 class="my-4">Input Hasil Pemotongan</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="id_pengiriman_potong" class="form-label">Pengiriman</label>
            <select class="form-select" id="id_pengiriman_potong" name="id_pengiriman_potong" required>
                <option value="">Pilih Pengiriman</option>
                <?php foreach ($pengiriman_list as $pengiriman): ?>
                    <option value="<?= $pengiriman['id_pengiriman_potong'] ?>">
                        ID: <?= $pengiriman['id_pengiriman_potong'] ?> -
                        Dikirim: <?= formatTanggal($pengiriman['tanggal_kirim']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="jumlah_hasil" class="form-label">Jumlah Hasil (pcs)</label>
            <input type="number" class="form-control" id="jumlah_hasil" name="jumlah_hasil" required>
        </div>

        <div class="mb-3">
            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
            <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required
                value="<?= date('Y-m-d') ?>">
        </div>

        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>