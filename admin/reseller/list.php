<?php
require_once '../includes/header.php';

function dateIndo($tanggal)
{
    $bulanIndo = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    $tanggal = date('Y-m-d', strtotime($tanggal));
    $pecah = explode('-', $tanggal);
    return $pecah[2] . ' ' . $bulanIndo[(int)$pecah[1]] . ' ' . $pecah[0];
}
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
                            <h2>Data Reseller</h2>
                            <div>
                                <a href="add.php" class="btn btn-success">
                                    <i class="bx bx-plus-circle"></i> Tambah Reseller
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
                                            <th>Nama Reseller</th>
                                            <th>Kontak</th>
                                            <th>Tanggal Bergabung</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM reseller ORDER BY nama_reseller";
                                        $reseller = query($sql);
                                        $no = 1;

                                        if (empty($reseller)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data reseller</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($reseller as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><?= htmlspecialchars($row['nama_reseller']) ?></td>
                                                    <td><?= htmlspecialchars($row['kontak']) ?></td>
                                                    <td class="text-end"><?= dateIndo($row['tanggal_bergabung']) ?></td>
                                                    <td class="text-center">
                                                        <a href="edit.php?id=<?= $row['id_reseller'] ?>" class="btn btn-sm btn-primary me-1">
                                                            <i class="bx bx-edit"></i> Edit
                                                        </a>
                                                        <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="<?= $row['id_reseller'] ?>">
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
                    const url = `delete.php?id=${id}`;

                    Swal.fire({
                        title: 'Konfirmasi Hapus Reseller',
                        text: "Apakah Anda yakin ingin menghapus reseller ini?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return fetch(`check_delete.php?id=${id}`)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (!data || typeof data.can_delete === 'undefined') {
                                        throw new Error('Invalid response from server');
                                    }
                                    return data;
                                })
                                .catch(error => {
                                    Swal.showValidationMessage(
                                        `Gagal memverifikasi: ${error.message}`
                                    );
                                    return null;
                                });
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            if (result.value.can_delete) {
                                window.location.href = url;
                            } else {
                                Swal.fire({
                                    title: 'Tidak Dapat Dihapus',
                                    text: result.value.message || 'Reseller tidak dapat dihapus karena memiliki relasi data',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        }
                    });
                });
            });
        });
    </script>
    <?php include '../includes/footer.php'; ?>
</body>

</html>