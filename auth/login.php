<?php
// Pastikan session_start() dipanggil di paling atas
session_start();

require_once '../config/database.php';
require_once '../config/functions.php';
include_once '../config/config.php';

// Inisialisasi variabel error
$error = '';

// Debug: Tampilkan semua error
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek jika form login disubmit
if (isset($_POST['login'])) {
  $username = $conn->real_escape_string($_POST['username']);
  $password = $_POST['password'];

  // Debug: Lihat input yang diterima
  error_log("Login attempt - Username: $username, Password: $password");

  $sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
  $result = $conn->query($sql);

  if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Debug: Lihat data user dan hash password dari database
    error_log("User found: " . print_r($user, true));
    error_log("Stored hash: " . $user['password']);
    error_log("Input password: " . $password);

    // Verifikasi password
    if (password_verify($password, $user['password'])) {
      // Set session
      $_SESSION['user_id'] = $user['id_user'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['nama'] = $user['nama_lengkap'];

      // Debug: Session values
      error_log("Session set: " . print_r($_SESSION, true));

      // Redirect berdasarkan role
      if ($user['role'] == 'admin') {
        header("Location: ../admin/index.php");
      } else {
        header("Location: ../owner/index.php");
      }
      exit();
    } else {
      $error = "Password yang Anda masukkan salah!";
      error_log("Password verification failed");
    }
  } else {
    $error = "Username tidak ditemukan!";
    error_log("User not found");
  }

  // Jika ada error, simpan di session untuk ditampilkan setelah redirect
  if (!empty($error)) {
    $_SESSION['login_error'] = $error;
  }
}
?>

<!DOCTYPE html>

<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="<?= $base_url ?>/assets/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Login Fashion Stock</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?= $base_url ?>/assets/img/Logo.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <!-- Icons. Uncomment required icon fonts -->
  <link rel="stylesheet" href="<?= $base_url ?>/assets/vendor/fonts/boxicons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="<?= $base_url ?>/assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="<?= $base_url ?>/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="<?= $base_url ?>/assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="<?= $base_url ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <!-- Page CSS -->
  <!-- Page -->
  <link rel="stylesheet" href="<?= $base_url ?>/assets/vendor/css/pages/page-auth.css" />
  <!-- Helpers -->
  <script src="<?= $base_url ?>/assets/vendor/js/helpers.js"></script>

  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="<?= $base_url ?>/assets/js/config.js"></script>
</head>

<body>
  <!-- Content -->

  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <!-- Register -->
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <img src="<?= $base_url ?>/assets/img/Logo.png" width="100px" alt="Bakery Logo" class="mx-auto d-block mb-3">
            <div class="text-center">
              <div class="app-brand-link d-block">
                <span class="text-body fw-bolder fs-3">Inventaris Barang <br> FASHION STOCK</span>
              </div>
            </div>
            <hr>
            <!-- /Logo -->
            <h4 class="mb-2">Login Fashion Stock! 👋</h4>
            <p class="mb-4">Masukan username dan password yang valid</p>
            <!-- <?php if (isset($error)): ?>
              <div class="alert"><?= $error ?></div>
            <?php endif; ?> -->

            <!-- Tambahkan ini di bagian atas form untuk menampilkan error -->
            <?php
            // Tampilkan error dari session jika ada
            if (isset($_SESSION['login_error'])) {
              echo '<div class="alert alert-danger alert-dismissible" role="alert">';
              echo htmlspecialchars($_SESSION['login_error']);
              echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
              echo '</div>';
              unset($_SESSION['login_error']); // Hapus setelah ditampilkan
            }
            ?>


            <form class="mb-3" method="POST">
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                  placeholder="Masukan username" autofocus required>
              </div>
              <div class="mb-3 form-password-toggle">
                <div class="d-flex justify-content-between">
                  <label class="form-label" for="password">Password</label>
                </div>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control" name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" required>
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
              </div>
              <div class="mb-3">
                <button class="btn btn-primary d-grid w-100" style="background-color: #0d6efd;"
                  name="login" type="submit">Masuk</button>
              </div>
            </form>
          </div>
        </div>
        <!-- /Register -->
      </div>
    </div>
  </div>

  <!-- / Content -->


  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->
  <script src="<?= $base_url ?>/assets/vendor/libs/jquery/jquery.js"></script>
  <script src="<?= $base_url ?>/assets/vendor/libs/popper/popper.js"></script>
  <script src="<?= $base_url ?>/assets/vendor/js/bootstrap.js"></script>
  <script src="<?= $base_url ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

  <script src="<?= $base_url ?>/assets/vendor/js/menu.js"></script>
  <!-- endbuild -->

  <!-- Vendors JS -->

  <!-- Main JS -->
  <script src="<?= $base_url ?>/assets/js/main.js"></script>

  <!-- Page JS -->

  <!-- Place this tag in your head or just before your close body tag. -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>