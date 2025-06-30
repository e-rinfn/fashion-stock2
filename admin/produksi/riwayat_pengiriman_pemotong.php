<?php
require_once __DIR__ . '../../includes/header.php';

$pengiriman = query("SELECT p.*, b.nama_bahan, pm.nama_pemotong 
                    FROM pengiriman_pemotong p
                    JOIN bahan_baku b ON p.id_bahan = b.id_bahan
                    JOIN pemotong pm ON p.id_pemotong = pm.id_pemotong
                    ORDER BY p.tanggal_kirim DESC");
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
                            <h2>Riwayat Data Pengiriman Pemotong</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <!-- <a href="#" class="btn btn-outline-warning">Kembali</a> -->
                                <a href="pengiriman_pemotong.php" class="btn btn-secondary">Kembali</a>
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

                            <h4>Riwayat Pengiriman</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th>Tanggal</th>
                                            <th>Bahan Baku</th>
                                            <th>Pemotong</th>
                                            <th>Jumlah</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($pengiriman as $p): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= date('d/m/Y', strtotime($p['tanggal_kirim'])) ?></td>
                                                <td><?= $p['nama_bahan'] ?></td>
                                                <td><?= $p['nama_pemotong'] ?></td>
                                                <td><?= $p['jumlah_bahan'] ?></td>
                                                <!-- <td><?= ucfirst($p['status']) ?></td> -->
                                                <td class="text-center">
                                                    <span class="badge <?= $p['status'] == 'dikirim' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                        <?= $p['status'] == 'dikirim' ? 'Dalam Proses' : 'Selesai' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($pengiriman)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Belum ada data pengiriman.</td>
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