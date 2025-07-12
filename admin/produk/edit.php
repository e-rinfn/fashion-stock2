<?php
require_once '../includes/header.php';

// Cek apakah parameter ID ada
if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$id_produk = intval($_GET['id']);

// Ambil data produk yang akan diedit
$sql = "SELECT * FROM produk WHERE id_produk = $id_produk";
$result = $conn->query($sql);
$produk = $result->fetch_assoc();

if (!$produk) {
    header("Location: list.php");
    exit;
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $conn->real_escape_string($_POST['nama']);
    $harga = $conn->real_escape_string($_POST['harga']);
    $stok = $conn->real_escape_string($_POST['stok']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);

    $sql = "UPDATE produk SET 
            nama_produk = '$nama',
            harga_jual = '$harga',
            stok = '$stok',
            deskripsi = '$deskripsi'
            WHERE id_produk = $id_produk";

    if ($conn->query($sql)) {
        $_SESSION['success'] = "Produk berhasil diperbarui";
        header("Location: list.php");
        exit();
    } else {
        $error = "Gagal memperbarui produk: " . $conn->error;
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
                            <h2>Edit Data Produk</h2>
                        </div>

                        <div class="card p-4 shadow-sm">

                            <form method="post">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Produk</label>
                                    <input type="text" id="nama" name="nama" class="form-control"
                                        value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="harga" class="form-label">Harga Jual</label>
                                        <input type="number" id="harga" name="harga" class="form-control"
                                            value="<?= rtrim(rtrim($produk['harga_jual'], '0'), '.') ?>" min="0" required>
                                    </div>

                                    <div class="col-md-8 mb-3">
                                        <label for="stok" class="form-label">Stok</label>
                                        <div class="input-group">
                                            <input type="number" id="stok" name="stok" class="form-control"
                                                value="<?= htmlspecialchars($produk['stok']) ?>" min="0" required>
                                            <span class="input-group-text">Pcs</span>
                                        </div>
                                    </div>

                                </div>
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea id="deskripsi" name="deskripsi" rows="5" class="form-control" required><?=
                                                                                                                        htmlspecialchars($produk['deskripsi']) ?></textarea>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save"></i> Simpan Perubahan
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