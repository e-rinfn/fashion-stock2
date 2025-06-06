<?php
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$id = $conn->real_escape_string($_GET['id']);
$sql = "SELECT * FROM pemotong WHERE id_pemotong = '$id'";
$result = $conn->query($sql);
$pemotong = $result->fetch_assoc();

if (!$pemotong) {
    header("Location: list.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $conn->real_escape_string($_POST['nama_pemotong']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $kontak = $conn->real_escape_string($_POST['kontak']);

    $sql = "UPDATE pemotong SET 
            nama_pemotong = '$nama',
            alamat = '$alamat',
            kontak = '$kontak'
            WHERE id_pemotong = '$id'";

    if ($conn->query($sql)) {
        $_SESSION['success'] = "Data pemotong berhasil diperbarui";
        header("Location: list.php");
        exit();
    } else {
        $error = "Gagal memperbarui data pemotong: " . $conn->error;
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
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Edit Data Pemotong</h2>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-body">

                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?= $error; ?></div>
                                <?php endif; ?>

                                <form method="post">
                                    <div class="mb-3">
                                        <label for="nama_pemotong" class="form-label">Nama Pemotong</label>
                                        <input type="text" class="form-control" id="nama_pemotong" name="nama_pemotong"
                                            value="<?= htmlspecialchars($pemotong['nama_pemotong']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($pemotong['alamat']); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="kontak" class="form-label">Kontak</label>
                                        <input type="text" class="form-control" id="kontak" name="kontak"
                                            value="<?= htmlspecialchars($pemotong['kontak']); ?>" required>
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