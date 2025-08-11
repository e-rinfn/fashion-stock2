<?php
require_once '../config/functions.php';
require_once './includes/header.php';
?>


<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            <?php include './includes/sidebar.php'; ?>
            <!-- / Sidebar -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <?php include './includes/navbar.php'; ?>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="col-lg-4 col-md-4 order-1">
                                <div class="row">
                                    <div class="col-lg-6 col-md-12 col-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between">
                                                    <div class="avatar flex-shrink-0">
                                                        <i class="bx bx-box fs-2 text-warning"></i>
                                                    </div>
                                                </div>
                                                <span class="fw-semibold d-block mb-1">Jenis Bahan</span>
                                                <?php
                                                $sql = "SELECT COUNT(*) as total FROM bahan_baku";
                                                $result = query($sql);
                                                $total_bahan = $result[0]['total'];
                                                ?>
                                                <h3 class="card-title mb-2"><?= $total_bahan ?></h3>
                                                <div class="d-flex justify-content-end">
                                                    <a href="bahan_baku/list.php" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-show-alt me-1"></i> Lihat
                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12 col-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between">
                                                    <div class="avatar flex-shrink-0">
                                                        <i class="bx bx-package fs-2 text-success"></i>
                                                    </div>
                                                </div>
                                                <span class="fw-semibold d-block mb-1">Jenis Produk</span>
                                                <?php
                                                $sql = "SELECT COUNT(*) as total FROM produk";
                                                $result = query($sql);
                                                $total_produk = $result[0]['total'];
                                                ?>
                                                <h3 class="card-title text-nowrap mb-2"><?= $total_produk ?></h3>
                                                <div class="d-flex justify-content-end">
                                                    <a href="produk/list.php" class="btn btn-sm btn-outline-success">
                                                        <i class="bx bx-show-alt me-1"></i> Lihat
                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8 mb-4 order-1">
                                <div class="card">
                                    <div class="d-flex align-items-end row">
                                        <div class="col-sm-12">
                                            <div class="card-body">
                                                <h5 class="card-title text-primary">Selamat Datang, Admin</h5>
                                                <p class="mb-4" style="text-align: justify;">
                                                    Selamat datang di dashboard aplikasi. Melalui halaman ini, Anda dapat mengelola data, memantau
                                                    aktivitas, dan mengatur berbagai fitur. Silakan gunakan menu yang tersedia untuk mengakses informasi
                                                    dalam melakukan pengelolaan sistem secara efisien.
                                                </p>
                                            </div>
                                        </div>
                                        <!-- <div class="col-sm-3 text-center text-sm-left">
                                            <div class="card-body pb-0 px-0 px-md-4">
                                                <img
                                                    src="../assets/img/illustrations/man-with-laptop-light.png"
                                                    height="140"
                                                    alt="View Badge User"
                                                    data-app-dark-img="illustrations/man-with-laptop-dark.png"
                                                    data-app-light-img="illustrations/man-with-laptop-light.png" />
                                            </div>
                                        </div> -->
                                    </div>
                                </div>
                            </div>


                            <!--/ Total Revenue -->
                            <!-- <div class="col-12 col-md-8 col-lg-4 order-3 order-md-2">
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <div class="card shadow-sm border-0 h-100">
                                            <div class="card-body d-flex flex-column justify-content-between">
                                                <?php
                                                $today = date('Y-m-d');
                                                $sql = "SELECT COUNT(*) as total FROM penjualan WHERE DATE(tanggal_penjualan) = '$today'";
                                                $result = query($sql);
                                                $total_penjualan = $result[0]['total'];

                                                $sql = "SELECT SUM(total_harga) as total FROM penjualan WHERE DATE(tanggal_penjualan) = '$today'";
                                                $result = query($sql);
                                                $total_harga = $result[0]['total'] ?? 0;
                                                ?>
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <h6 class="text-muted mb-1">Penjualan Hari Ini</h6>
                                                        <h3 class="mb-0 text-warning fw-semibold"><?= formatRupiah($total_harga) ?></h3>
                                                        <small class="text-muted"><?= $total_penjualan ?> Transaksi</small>
                                                    </div>
                                                    <div class="text-white bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                                        <i class="bx bx-chart fs-2"></i>
                                                    </div>
                                                </div>
                                                <div class="mt-auto text-end">
                                                    <a href="penjualan/new.php" class="btn btn-sm btn-success">
                                                        <i class="bx bx-plus"></i> Tambah Penjualan
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->


                            <!-- Total Aktivitas Terakhir -->
                            <div class="col-12 col-lg-12 order-2 order-md-3 order-lg-2 mb-4">
                                <div class="card">
                                    <div class="row row-bordered g-0">
                                        <div class="col-12">
                                            <h5 class="card-header m-0 me-2 pb-3">Aktivitas Terakhir</h5>

                                            <!-- <div class="table-responsive text-nowrap px-3 pb-3"> -->
                                            <div class="table-responsive text-nowrap px-3 pb-3 mb-4" style="max-height: 300px; overflow-y: auto;">
                                                <table class="table table-striped table-bordered align-middle">
                                                    <thead class="table-light">
                                                        <tr class="text-center">
                                                            <th>Tanggal</th>
                                                            <th>Aktivitas</th>
                                                            <th>Detail</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        // Gabungkan beberapa tabel untuk menampilkan aktivitas terakhir
                                                        $sql = "

                                                            (SELECT 'pembelian' as type, tanggal_pembelian as waktu, 
                                                                CONCAT('Pembelian ke: ', nama_supplier) as aktivitas, 
                                                                CONCAT('Total Rp.', FORMAT(total_harga, 0)) as detail
                                                            FROM pembelian p 
                                                            JOIN supplier r ON p.id_supplier = r.id_supplier 
                                                            ORDER BY waktu DESC LIMIT 3)

                                                            UNION

                                                            (SELECT 'penjualan' as type, tanggal_penjualan as waktu, 
                                                                CONCAT('Penjualan Barang ke: ', nama_reseller) as aktivitas, 
                                                                CONCAT('Total Rp.', FORMAT(total_harga, 0)) as detail
                                                            FROM penjualan p 
                                                            JOIN reseller r ON p.id_reseller = r.id_reseller 
                                                            ORDER BY waktu DESC LIMIT 3)

                                                            UNION

                                                            (SELECT 'produksi' as type, created_at as waktu, 
                                                                'Hasil Penjahitan' as aktivitas, 
                                                                CONCAT(jumlah_produk_jadi, ' pcs produk jadi') as detail
                                                            FROM hasil_penjahitan 
                                                            ORDER BY waktu DESC LIMIT 3)

                                                            UNION

                                                            (SELECT 'pembelian_bahan' as type, tanggal_pembelian as waktu, 
                                                                CONCAT('Pembelian Bahan dari: ', nama_supplier) as aktivitas, 
                                                                CONCAT('Total Rp.', FORMAT(total_harga, 0)) as detail
                                                            FROM pembelian_bahan pb 
                                                            JOIN supplier s ON pb.id_supplier = s.id_supplier 
                                                            ORDER BY waktu DESC LIMIT 3)

                                                            UNION

                                                            (SELECT 'penjualan_bahan' as type, tanggal_penjualan_bahan as waktu, 
                                                                CONCAT('Penjualan Bahan ke: ', nama_reseller) as aktivitas, 
                                                                CONCAT('Total Rp.', FORMAT(total_harga, 0)) as detail
                                                            FROM penjualan_bahan pb 
                                                            JOIN reseller r ON pb.id_reseller = r.id_reseller 
                                                            ORDER BY waktu DESC LIMIT 3)

                                                            ORDER BY waktu DESC LIMIT 5
                                                        ";

                                                        $activities = query($sql);

                                                        if (empty($activities)) {
                                                            echo "<tr><td colspan='3' class='text-center'>Tidak ada aktivitas terakhir</td></tr>";
                                                        } else {
                                                            foreach ($activities as $activity) {
                                                                echo "<tr>";
                                                                echo "<td>" . date('d M Y H:i', strtotime($activity['waktu'])) . "</td>";
                                                                echo "<td>" . htmlspecialchars($activity['aktivitas']) . "</td>";
                                                                echo "<td>" . htmlspecialchars($activity['detail']) . "</td>";
                                                                echo "</tr>";
                                                            }
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
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
    <?php include './includes/footer.php'; ?>
    <!-- /Core JS footer -->


</body>

</html>