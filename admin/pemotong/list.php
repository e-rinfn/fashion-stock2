<?php
require_once '../includes/header.php';

$sql = "SELECT * FROM pemotong ORDER BY nama_pemotong";
$pemotong = query($sql);
?>

<style>
    .swal2-container {
        z-index: 99999 !important;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include '../includes/sidebar.php'; ?>

            <div class="layout-page">
                <?php include '../includes/navbar.php'; ?>

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Data Pemotong</h2>
                            <div>
                                <a href="add.php" class="btn btn-success">
                                    <i class="bx bx-plus-circle"></i> Tambah Pemotong
                                </a>
                            </div>
                        </div>

                        <div class="card p-3">
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pemotong</th>
                                            <th>Alamat</th>
                                            <th>Kontak</th>
                                            <th>Aksi</th>
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    const url = this.getAttribute('href');

                    // Check if pemotong can be deleted
                    fetch(`check_delete.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.can_delete) {
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
                            } else {
                                Swal.fire({
                                    title: 'Tidak Dapat Dihapus',
                                    text: data.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error',
                                text: 'Gagal memverifikasi data',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                });
            });
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>

</html>