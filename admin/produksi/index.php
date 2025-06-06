<?php
require_once '../../config/functions.php';
require_once __DIR__ . '../../includes/header.php';
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
                            <h2>Produksi</h2>
                        </div>

                        <div class="row">
                            <div class="col-lg-3 col-md-12 col-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-cut fs-2 text-danger"></i>
                                            </div>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Pengiriman Pemotong</span>
                                        <div class="d-flex justify-content-end">
                                            <a href="pengiriman_pemotong.php" class="btn btn-sm btn-outline-danger">
                                                <i class="bx bx-show-alt me-1"></i> Lihat
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12 col-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-cut fs-2 text-warning"></i>
                                            </div>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Hasil Potong</span>
                                        <div class="d-flex justify-content-end">
                                            <a href="hasil_pemotongan.php" class="btn btn-sm btn-outline-warning">
                                                <i class="bx bx-show-alt me-1"></i> Lihat
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12 col-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-box fs-2 text-primary"></i>
                                            </div>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Pengiriman Penjahit</span>
                                        <div class="d-flex justify-content-end">
                                            <a href="pengiriman_penjahit.php" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-show-alt me-1"></i> Lihat
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12 col-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-package fs-2 text-success"></i>
                                            </div>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Hasil Penjahitan</span>
                                        <div class="d-flex justify-content-end">
                                            <a href="hasil_penjahitan.php" class="btn btn-sm btn-outline-success">
                                                <i class="bx bx-show-alt me-1"></i> Lihat
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 mb-4 order-1">
                                <div class="card">
                                    <div class="row align-items-center">
                                        <!-- Kolom Teks -->
                                        <div class="col-md-9 col-sm-12">
                                            <div class="card-body">
                                                <h5 class="card-title text-primary">Halaman Pengelolaan Produksi</h5>
                                                <p class="mb-4" style="text-align: justify;">
                                                    Selamat datang di halaman pengelolaan produksi. Melalui halaman ini, Anda dapat memantau
                                                    dan mengelola seluruh proses produksi, mulai dari pengiriman bahan ke pemotong, pencatatan
                                                    hasil pemotongan, pengiriman ke penjahit, hingga pendataan hasil penjahitan. Gunakan fitur-fitur
                                                    yang tersedia untuk memastikan kelancaran dan efisiensi dalam setiap tahapan produksi.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Kolom Gambar -->
                                        <div class="col-md-3 col-sm-12 text-center">
                                            <div class="card-body pb-0 px-0 px-md-4">
                                                <img
                                                    src="../../assets/img/illustrations/produksi.png"
                                                    height="140"
                                                    alt="View Badge User"
                                                    data-app-dark-img="illustrations/man-with-laptop-dark.png"
                                                    data-app-light-img="illustrations/man-with-laptop-light.png" />
                                            </div>
                                        </div>
                                    </div>
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
    <?php include __DIR__ . '../../includes/footer.php'; ?>
    <!-- /Core JS footer -->


</body>

</html>