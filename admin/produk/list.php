<?php
require_once '../includes/header.php';

$sql = "SELECT * FROM produk ORDER BY nama_produk";
$produk = query($sql);
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
                            <h2>Data Produk</h2>
                            <div>
                                <a href="add.php" class="btn btn-success">
                                    <i class="bx bx-plus-circle"></i> Tambah Produk
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
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                                <?php unset($_SESSION['error']); ?>
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
                                            <th scope="col">Nama Produk</th>
                                            <th scope="col">Harga</th>
                                            <th scope="col">Stok</th>
                                            <th scope="col">Deskripsi</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($produk as $p) : ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($p['nama_produk']) ?></td>
                                                <td><?= formatRupiah($p['harga_jual']) ?></td>
                                                <td class="text-center"><?= $p['stok'] ?></td>
                                                <td><?= htmlspecialchars(substr($p['deskripsi'], 0, 50)) ?>...</td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-2">
                                                        <a href="edit.php?id=<?= $p['id_produk'] ?>" class="btn btn-sm btn-primary">
                                                            <i class="bx bx-edit"></i> Edit
                                                        </a>
                                                        <a href="#"
                                                            class="btn btn-sm btn-danger btn-hapus"
                                                            data-id="<?= $p['id_produk'] ?>">
                                                            <i class="bx bx-trash"></i> Hapus
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-hapus');

            deleteButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');

                    // Cek relasi produk via AJAX
                    fetch(`check_produk.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.can_delete) {
                                Swal.fire({
                                    title: 'Yakin hapus data produk?',
                                    text: "Data yang dihapus tidak bisa dikembalikan!",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: 'Ya, hapus!',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = 'delete.php?id=' + id;
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: 'Tidak Dapat Dihapus',
                                    html: data.message,
                                    icon: 'error',
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                });
            });
        });
    </script>

    <!-- Core JS footer -->
    <?php include '../includes/footer.php'; ?>
    <!-- /Core JS footer -->

</body>

</html>