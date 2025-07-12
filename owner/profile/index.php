<?php

// owner/profile/index.php
require_once '../../config/database.php';
require_once '../../config/functions.php';
require_once '../includes/header.php';

// Pastikan sudah login dan role owner
// redirectIfNotLoggedIn();
// checkRole('owner');

// Ambil data user yang sedang login
$user_id = $_SESSION['user_id'];
$user = query("SELECT * FROM users WHERE id_user = $user_id")[0];

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $kontak = $conn->real_escape_string($_POST['kontak']);
    $username = $conn->real_escape_string($_POST['username']);

    // Validasi username unik
    $check_username = query("SELECT id_user FROM users WHERE username = '$username' AND id_user != $user_id");
    if ($check_username) {
        $error = "Username sudah digunakan!";
    } else {
        // Update data profil
        $sql = "UPDATE users SET 
                nama_lengkap = '$nama_lengkap',
                kontak = '$kontak',
                username = '$username'
                WHERE id_user = $user_id";

        if ($conn->query($sql)) {
            $_SESSION['success'] = "Profil berhasil diperbarui";
            $_SESSION['nama'] = $nama_lengkap;
            $_SESSION['username'] = $username;
            header("Refresh:0");
            exit();
        } else {
            $error = "Gagal memperbarui profil: " . $conn->error;
        }
    }
}

// Proses update password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Verifikasi password saat ini
    if (!password_verify($current_pass, $user['password'])) {
        $error_pass = "Password saat ini salah!";
    } elseif ($new_pass !== $confirm_pass) {
        $error_pass = "Password baru tidak cocok!";
    } else {
        // Update password
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = '$hashed_pass' WHERE id_user = $user_id";

        if ($conn->query($sql)) {
            $_SESSION['success_pass'] = "Password berhasil diubah";
            header("Refresh:0");
            exit();
        } else {
            $error_pass = "Gagal mengubah password: " . $conn->error;
        }
    }
}
?>


<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            <?php include __DIR__ . '../../includes/sidebar.php'; ?>
            <!-- / Sidebar -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <?php include __DIR__ . '../../includes/navbar.php'; ?>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Profile</h2>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <h5 class="card-header">Detail Profil</h5>
                                    <div class="card-body">
                                        <?php if (isset($_SESSION['success'])): ?>
                                            <div class="alert alert-success"><?= $_SESSION['success'];
                                                                                unset($_SESSION['success']); ?></div>
                                        <?php endif; ?>
                                        <?php if (isset($error)): ?>
                                            <div class="alert alert-danger"><?= $error; ?></div>
                                        <?php endif; ?>

                                        <form method="post">
                                            <div class="mb-3">
                                                <label class="form-label">Username</label>
                                                <input type="text" class="form-control" name="username"
                                                    value="<?= htmlspecialchars($user['username']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nama Lengkap</label>
                                                <input type="text" class="form-control" name="nama_lengkap"
                                                    value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Kontak</label>
                                                <input type="text" class="form-control" name="kontak"
                                                    value="<?= htmlspecialchars($user['kontak']) ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Role</label>
                                                <input type="text" class="form-control"
                                                    value="<?= htmlspecialchars(ucfirst($user['role'])) ?>" disabled>
                                            </div>
                                            <button type="submit" name="update_profile" class="btn btn-primary">
                                                Simpan Perubahan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <h5 class="card-header">Ubah Password</h5>
                                    <div class="card-body">
                                        <?php if (isset($_SESSION['success_pass'])): ?>
                                            <div class="alert alert-success"><?= $_SESSION['success_pass'];
                                                                                unset($_SESSION['success_pass']); ?></div>
                                        <?php endif; ?>
                                        <?php if (isset($error_pass)): ?>
                                            <div class="alert alert-danger"><?= $error_pass; ?></div>
                                        <?php endif; ?>

                                        <form method="post">
                                            <div class="mb-3">
                                                <label class="form-label">Password Saat Ini</label>
                                                <input type="password" class="form-control" name="current_password" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Password Baru</label>
                                                <input type="password" class="form-control" name="new_password" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Konfirmasi Password Baru</label>
                                                <input type="password" class="form-control" name="confirm_password" required>
                                            </div>
                                            <button type="submit" name="update_password" class="btn btn-primary">
                                                Ubah Password
                                            </button>
                                        </form>
                                    </div>
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