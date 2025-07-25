<?php
require_once '../includes/header.php';
// Cek apakah user sudah login
if (!isLoggedIn()) {
    header("Location: {$base_url}auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $conn->real_escape_string($_POST['nama_bahan']);
    $stok = $conn->real_escape_string($_POST['jumlah_stok']);
    $satuan = $conn->real_escape_string($_POST['satuan']);
    // $harga = $conn->real_escape_string($_POST['harga_per_satuan']);
    $supplier = $conn->real_escape_string($_POST['supplier']);

    $sql = "INSERT INTO bahan_baku (nama_bahan, jumlah_stok, satuan, supplier) 
            VALUES ('$nama', '$stok', '$satuan', '$supplier')";

    if ($conn->query($sql)) {
        $_SESSION['success'] = "Bahan baku berhasil ditambahkan";
        header("Location: list.php");
        exit();
    } else {
        $error = "Gagal menambahkan bahan baku: " . $conn->error;
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
                            <h2>Tambah Data Bahan Baku</h2>
                        </div>

                        <div class="card p-4 shadow-sm">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="mb-3">
                                    <label for="nama_bahan" class="form-label">Nama Bahan</label>
                                    <input type="text" id="nama_bahan" name="nama_bahan" class="form-control" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="jumlah_stok" class="form-label">Jumlah Stok</label>
                                        <input type="number" step="0.01" id="jumlah_stok" name="jumlah_stok" class="form-control" required>
                                    </div>

                                    <div class="col-md-8 mb-3">
                                        <label for="satuan" class="form-label">Satuan</label>
                                        <input type="text" id="satuan" name="satuan" class="form-control" required>
                                    </div>
                                </div>

                                <!-- <div class="mb-3">
                                    <label for="harga_per_satuan" class="form-label">Harga per Satuan</label>
                                    <input type="number" id="harga_per_satuan" name="harga_per_satuan" class="form-control" required>
                                </div> -->

                                <div class="mb-3">
                                    <label for="supplier" class="form-label">Supplier</label>
                                    <input type="text" id="supplier" name="supplier" class="form-control">
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