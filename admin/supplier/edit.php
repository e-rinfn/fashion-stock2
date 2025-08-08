<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
// redirectIfNotLoggedIn();
// checkRole('admin');

require_once '../includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data supplier
$sql = "SELECT * FROM supplier WHERE id_supplier = $id";
$supplier = query($sql);
if (empty($supplier)) {
    header("Location: list.php");
    exit();
}
$supplier = $supplier[0];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $conn->real_escape_string($_POST['nama']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $kontak = $conn->real_escape_string($_POST['kontak']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);

    $sql = "UPDATE supplier SET 
            nama_supplier = '$nama',
            alamat = '$alamat',
            kontak = '$kontak',
            tanggal_bergabung = '$tanggal'
            WHERE id_supplier = $id";

    if ($conn->query($sql)) {
        $_SESSION['success'] = "Data supplier berhasil diupdate";
        header("Location: list.php");
        exit();
    } else {
        $error = "Gagal update data: " . $conn->error;
    }
}
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
                            <h2>Edit Data Supplier</h2>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-body">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?= $error ?></div>
                                <?php endif; ?>

                                <form method="post">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Supplier</label>
                                        <input type="text" id="nama" name="nama" class="form-control" value="<?= htmlspecialchars($supplier['nama_supplier']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea id="alamat" name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($supplier['alamat']) ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="kontak" class="form-label">Kontak</label>
                                        <input type="text" id="kontak" name="kontak" class="form-control" value="<?= htmlspecialchars($supplier['kontak']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="tanggal" class="form-label">Tanggal Bergabung</label>
                                        <input type="date" id="tanggal" name="tanggal" class="form-control" value="<?= $supplier['tanggal_bergabung'] ?>" required>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save"></i> Simpan
                                        </button>
                                        <a href="list.php" class="btn btn-secondary">
                                            <i class="bx bx-x"></i> Batal
                                        </a>
                                    </div>
                                </form>
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