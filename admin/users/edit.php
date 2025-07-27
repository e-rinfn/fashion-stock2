<?php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = query("SELECT * FROM users WHERE id_user = $id");

if (!$user) {
    header("Location: index.php");
    exit();
}

$user = $user[0];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $nama = $conn->real_escape_string($_POST['nama']);
    $role = $conn->real_escape_string($_POST['role']);
    $kontak = $conn->real_escape_string($_POST['kontak']);

    // Jika password diisi
    $password_update = "";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_update = ", password = '$password'";
    }

    $sql = "UPDATE users SET 
            username = '$username',
            nama_lengkap = '$nama',
            role = '$role',
            kontak = '$kontak'
            $password_update
            WHERE id_user = $id";

    if ($conn->query($sql)) {
        $_SESSION['success'] = "User berhasil diperbarui";
        header("Location: index.php");
        exit();
    } else {
        $error = "Gagal memperbarui user: " . $conn->error;
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
                            <h2>Edit Data Pengguna</h2>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-body">
                                <?php if (isset($error)) : ?>
                                    <div class="alert alert-danger"><?= $error; ?></div>
                                <?php endif; ?>

                                <form method="post">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control"
                                            value="<?= htmlspecialchars($user['username']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Password (Kosongkan jika tidak diubah)</label>
                                        <input type="password" name="password" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" name="nama" class="form-control"
                                            value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="role" class="form-select" required>
                                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="owner" <?= $user['role'] == 'owner' ? 'selected' : ''; ?>>Owner</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Kontak</label>
                                        <input type="text" name="kontak" class="form-control"
                                            value="<?= htmlspecialchars($user['kontak']); ?>">
                                    </div>

                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    <a href="index.php" class="btn btn-secondary">Batal</a>
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