<?php
require_once '../includes/header.php';
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

// koneksi database
$conn = new mysqli("localhost", "root", "", "fashion-stock2-fix");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tambah atau Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tarif = $_POST['id_tarif'] ?? '';
    $jenis_tarif = $_POST['jenis_tarif'];
    $tarif_per_unit = str_replace('.', '', $_POST['tarif_per_unit']); // hilangkan titik format
    $berlaku_sejak = $_POST['berlaku_sejak'];
    $keterangan = $_POST['keterangan'];

    if ($id_tarif) {
        // Update
        $stmt = $conn->prepare("UPDATE tarif_upah SET jenis_tarif=?, tarif_per_unit=?, berlaku_sejak=?, keterangan=? WHERE id_tarif=?");
        $stmt->bind_param("sdssi", $jenis_tarif, $tarif_per_unit, $berlaku_sejak, $keterangan, $id_tarif);
        $stmt->execute();
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO tarif_upah (jenis_tarif, tarif_per_unit, berlaku_sejak, keterangan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $jenis_tarif, $tarif_per_unit, $berlaku_sejak, $keterangan);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Hapus
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM tarif_upah WHERE id_tarif = $id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Ambil semua data
$result = $conn->query("SELECT * FROM tarif_upah ORDER BY berlaku_sejak DESC");

?>

<style>
    .swal2-container {
        z-index: 99999 !important;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include '../includes/sidebar.php'; ?>

            <div class="layout-page">
                <?php include '../includes/navbar.php'; ?>

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Kelola Upah</h2>
                        </div>

                        <div class="card p-3">
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>

                            <form method="POST">
                                <input type="hidden" name="id_tarif" id="id_tarif">
                                <div class="mb-3">
                                    <label>Jenis Tarif</label>
                                    <select name="jenis_tarif" id="jenis_tarif" class="form-select" required>
                                        <option value="">Pilih...</option>
                                        <option value="pemotongan">Pemotongan</option>
                                        <option value="penjahitan">Penjahitan</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Tarif per Unit (Rp)</label>
                                    <input type="text" name="tarif_per_unit" id="tarif_per_unit" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Berlaku Sejak</label>
                                    <input type="date" name="berlaku_sejak" id="berlaku_sejak" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Keterangan</label>
                                    <textarea name="keterangan" id="keterangan" class="form-control"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <button type="reset" class="btn btn-secondary" onclick="resetForm()">Batal</button>
                            </form>
                        </div>
                        <hr>
                        <div class="card p-3">
                            <!-- Tabel Data -->
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Jenis</th>
                                        <th>Tarif (Rp)</th>
                                        <th>Berlaku Sejak</th>
                                        <th>Keterangan</th>
                                        <th>Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) : ?>
                                        <tr>
                                            <td><?= ucfirst($row['jenis_tarif']) ?></td>
                                            <td><?= number_format($row['tarif_per_unit'], 0, ',', '.') ?></td>
                                            <td><?= $row['berlaku_sejak'] ?></td>
                                            <td><?= $row['keterangan'] ?></td>
                                            <td><?= $row['created_at'] ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" onclick='editData(<?= json_encode($row) ?>)'>Edit</button>
                                                <a href="?delete=<?= $row['id_tarif'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>
        </div>


    </div>
    <script>
        function editData(data) {
            document.getElementById('id_tarif').value = data.id_tarif;
            document.getElementById('jenis_tarif').value = data.jenis_tarif;
            document.getElementById('tarif_per_unit').value = data.tarif_per_unit;
            document.getElementById('berlaku_sejak').value = data.berlaku_sejak;
            document.getElementById('keterangan').value = data.keterangan;
        }

        function resetForm() {
            document.getElementById('id_tarif').value = '';
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    const url = this.getAttribute('href');

                    // Check if supplier can be deleted
                    fetch(`check_delete.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.can_delete) {
                                Swal.fire({
                                    title: 'Yakin hapus supplier?',
                                    text: "Data yang dihapus tidak bisa dikembalikan!",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: 'Ya, hapus!',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = url;
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: 'Tidak Dapat Dihapus',
                                    text: data.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error',
                                text: 'Terjadi kesalahan saat memeriksa data',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                });
            });
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>

</html>