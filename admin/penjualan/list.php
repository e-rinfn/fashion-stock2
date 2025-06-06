<?php
require_once '../includes/header.php';

// Ambil semua reseller untuk dropdown
$resellers = query("SELECT * FROM reseller");

// Cek apakah filter reseller diterapkan
$id_reseller = isset($_GET['id_reseller']) ? (int)$_GET['id_reseller'] : 0;

if ($id_reseller > 0) {
    $sql = "SELECT p.*, r.nama_reseller 
            FROM penjualan p 
            JOIN reseller r ON p.id_reseller = r.id_reseller 
            WHERE p.id_reseller = $id_reseller
            ORDER BY p.tanggal_penjualan DESC";
} else {
    $sql = "SELECT p.*, r.nama_reseller 
            FROM penjualan p 
            JOIN reseller r ON p.id_reseller = r.id_reseller 
            ORDER BY p.tanggal_penjualan DESC";
}

$penjualan = query($sql);
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
                            <h2>Data Penjualan</h2>
                            <a href="new.php" class="btn btn-success">
                                <i class="bx bx-plus-circle"></i> Tambah Penjualan
                            </a>
                        </div>

                        <!-- Filter Reseller -->
                        <form method="GET" class="row g-3 mb-3">
                            <div class="col-md-4">
                                <select name="id_reseller" class="form-select" onchange="this.form.submit()">
                                    <option value="0">Semua Reseller</option>
                                    <?php foreach ($resellers as $res): ?>
                                        <option value="<?= $res['id_reseller'] ?>" <?= ($id_reseller == $res['id_reseller']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($res['nama_reseller']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>

                        <div class="card p-3">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th style="width: 50px;">No</th>
                                            <th>Tanggal</th>
                                            <th>Reseller</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th style="width: 200px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($penjualan)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Tidak ada data penjualan</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($penjualan as $jual): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($jual['tanggal_penjualan'])) ?></td>
                                                    <td><?= htmlspecialchars($jual['nama_reseller']) ?></td>
                                                    <td><?= formatRupiah($jual['total_harga']) ?></td>
                                                    <td class="text-center">
                                                        <?php if ($jual['status_pembayaran'] == 'lunas'): ?>
                                                            <span class="badge bg-success">LUNAS</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark">CICILAN</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="detail.php?id=<?= $jual['id_penjualan'] ?>" class="btn btn-sm btn-primary me-2 mb-1">Detail</a>
                                                        <?php if ($jual['status_pembayaran'] == 'cicilan'): ?>
                                                            <a href="cicilan.php?id=<?= $jual['id_penjualan'] ?>" class="btn btn-sm btn-warning me-2 mb-1">Cicilan</a>
                                                        <?php endif; ?>
                                                        <a href="nota.php?id=<?= $jual['id_penjualan'] ?>" target="_blank" class="btn btn-sm btn-info me-2 mb-1">Nota</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-hapus');
            deleteButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
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
                });
            });
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>

</html>