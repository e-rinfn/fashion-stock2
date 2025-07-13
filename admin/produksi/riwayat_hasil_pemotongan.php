<?php
require_once __DIR__ . '../../includes/header.php';

// Ambil data pengiriman yang belum selesai
$pengiriman = query("SELECT pp.id_pengiriman_potong, pp.tanggal_kirim, pp.jumlah_bahan, 
                    b.nama_bahan, b.satuan, p.nama_pemotong
                    FROM pengiriman_pemotong pp
                    JOIN bahan_baku b ON pp.id_bahan = b.id_bahan
                    JOIN pemotong p ON pp.id_pemotong = p.id_pemotong
                    WHERE pp.status = 'dikirim' OR pp.status = 'selesai'
                    ORDER BY pp.tanggal_kirim DESC");

$riwayat = query("SELECT h.*, p.nama_bahan, pm.nama_pemotong 
                    FROM hasil_pemotongan h
                    JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
                    JOIN bahan_baku p ON pg.id_bahan = p.id_bahan
                    JOIN pemotong pm ON pg.id_pemotong = pm.id_pemotong
                    ORDER BY h.tanggal_selesai DESC");

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
                            <h2>Riwayat Data Hasil Pemotongan</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <!-- <a href="#" class="btn btn-outline-warning">Kembali</a> -->
                                <a href="hasil_pemotongan.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </div>

                        <div class="card p-4 shadow-sm">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>



                            <h3 class="mb-3">Riwayat Hasil Pemotongan</h3>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th>Tanggal</th>
                                            <th>Bahan Baku</th>
                                            <th>Pemotong</th>
                                            <th class="text-center">Jumlah Hasil (pcs)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($riwayat as $r): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= date('d/m/Y', strtotime($r['tanggal_selesai'])) ?></td>
                                                <td><?= $r['nama_bahan'] ?></td>
                                                <td><?= $r['nama_pemotong'] ?></td>
                                                <td class="text-center"><?= $r['jumlah_hasil'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($riwayat)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Belum ada data hasil pemotongan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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