<?php
require_once '../includes/header.php';

$sql = "SELECT * FROM pemotong ORDER BY nama_pemotong";
$pemotong = query($sql);
?>

<style>
    /* Paksa SweetAlert berada di atas segalanya */
    .swal2-container {
        z-index: 99999 !important;
    }
</style>

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
                            <h2>Data Pemotong</h2>
                            <div>
                                <a href="add.php" class="btn btn-success">
                                    <i class="bx bx-plus-circle"></i> Tambah Pemotong
                                </a>
                                <!-- 
            <a href="../laporan/stok.php" class="btn btn-warning btn-sm m-1">
                <i class="bx bx-file"></i> Laporan
            </a> 
            -->
                            </div>
                        </div>

                        <div class="card p-3">

                            <!-- Tampilkan pesan error atau success -->
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>
                            <!-- /Tampilkan pesan error atau success -->

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th scope="col">No</th>
                                            <th scope="col">Nama Pemotong</th>
                                            <th scope="col">Alamat</th>
                                            <th scope="col">Kontak</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pemotong)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data pemotong</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($pemotong as $ptg): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++; ?></td>
                                                    <td><?= htmlspecialchars($ptg['nama_pemotong']); ?></td>
                                                    <td><?= htmlspecialchars($ptg['alamat']); ?></td>
                                                    <td><?= htmlspecialchars($ptg['kontak']); ?></td>
                                                    <td class="text-center">
                                                        <a href="edit.php?id=<?= $ptg['id_pemotong']; ?>" class="btn btn-sm btn-primary me-1">
                                                            <i class="bx bx-edit"></i> Edit
                                                        </a>
                                                        <a href="delete.php?id=<?= $ptg['id_pemotong']; ?>"
                                                            class="btn btn-sm btn-danger btn-delete"
                                                            data-id="<?= $ptg['id_pemotong']; ?>">
                                                            <i class="bx bx-trash"></i> Hapus
                                                        </a>

                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
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

    <!-- SweetAlert2 -->
    <!-- Tambahkan di <head> atau sebelum penutup </body> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault(); // Mencegah langsung redirect
                    const id = this.getAttribute('data-id');
                    const url = `delete.php?id=${id}`;

                    Swal.fire({
                        title: 'Yakin hapus data pemotong?',
                        text: "Data yang dihapus tidak bisa dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                });
            });
        });
    </script>



    <script>
        function showStokForm(id) {
            // Sembunyikan semua form stok yang mungkin terbuka
            document.querySelectorAll('.stok-form').forEach(form => {
                form.style.display = 'none';
            });

            // Tampilkan form stok untuk bahan yang dipilih
            document.getElementById('stok-form-' + id).style.display = '';
        }

        function hideStokForm(id) {
            document.getElementById('stok-form-' + id).style.display = 'none';
        }
    </script>

    <!-- Core JS footer -->
    <?php include '../includes/footer.php'; ?>
    <!-- /Core JS footer -->


</body>

</html>