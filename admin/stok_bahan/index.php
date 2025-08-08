<?php
require_once '../includes/header.php';

// Ambil parameter filter
$nama_bahan = isset($_GET['nama_bahan']) ? trim($_GET['nama_bahan']) : '';
$jumlah_stok = isset($_GET['jumlah_stok']) ? $_GET['jumlah_stok'] : 'all';

// Bangun query dengan filter
$sql = "SELECT * FROM bahan_baku WHERE 1=1";

if (!empty($nama_bahan)) {
    $nama_bahan_escaped = $conn->real_escape_string($nama_bahan);
    $sql .= " AND nama_bahan LIKE '%$nama_bahan_escaped%'";
}

if ($jumlah_stok == 'tersedia') {
    $sql .= " AND jumlah_stok > 0";
} elseif ($jumlah_stok == 'habis') {
    $sql .= " AND jumlah_stok = 0";
}

$sql .= " ORDER BY nama_bahan";

$bahan = query($sql);
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
                            <h2>Data Bahan Baku</h2>
                        </div>

                        <!-- Filter -->
                        <form method="GET" class="row g-3 mb-3">
                            <div class="col-md-6">
                                <input type="text" name="nama_bahan" class="form-control" placeholder="Cari Nama Bahan"
                                    value="<?= htmlspecialchars($nama_bahan) ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="jumlah_stok" class="form-select">
                                    <option value="all" <?= $jumlah_stok == 'all' ? 'selected' : '' ?>>Semua Stok</option>
                                    <option value="tersedia" <?= $jumlah_stok == 'tersedia' ? 'selected' : '' ?>>Stok Tersedia</option>
                                    <option value="habis" <?= $jumlah_stok == 'habis' ? 'selected' : '' ?>>Stok Habis</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-filter"></i> Filter
                                </button>
                                <?php if (!empty($nama_bahan) || $jumlah_stok != 'all'): ?>
                                    <a href="index.php" class="btn btn-secondary ms-2">
                                        <i class="bx bx-reset"></i> Reset
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>

                        <div class="card p-3">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th scope="col">No</th>
                                            <th scope="col">Nama Bahan</th>
                                            <th scope="col">Harga</th>
                                            <th scope="col">Stok</th>
                                            <th scope="col">Supplier</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($bahan as $b): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($b['nama_bahan']) ?></td>
                                                <td><?= formatRupiah($b['harga_per_satuan']) ?></td>
                                                <td class="text-center"><?= $b['jumlah_stok'] ?></td>
                                                <td><?= htmlspecialchars(substr($b['supplier'], 0, 50)) ?>...</td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($bahan)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data bahan</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-hapus');
            deleteButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Yakin hapus data bahan baku?',
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
                });
            });
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>

</html>