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

// Ambil semua reseller untuk dropdown
$resellers = query("SELECT * FROM reseller");

// Cek filter yang diterapkan
$id_reseller = isset($_GET['id_reseller']) ? (int)$_GET['id_reseller'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Bangun query berdasarkan filter
$sql = "SELECT p.*, r.nama_reseller 
        FROM penjualan p 
        JOIN reseller r ON p.id_reseller = r.id_reseller 
        WHERE 1=1";

// Filter reseller
if ($id_reseller > 0) {
    $sql .= " AND p.id_reseller = $id_reseller";
}

// Filter status
if ($status != 'all') {
    $sql .= " AND p.status_pembayaran = '$status'";
}

$sql .= " ORDER BY p.tanggal_penjualan DESC";

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
                            <h2 class="fw-bold text-danger">DATA PENJUALAN BARANG PRODUK</h2>
                            <a href="new.php" class="btn btn-success">
                                <i class="bx bx-plus-circle"></i> Tambah Pesanan
                            </a>
                        </div>

                        <!-- Filter Form -->
                        <form method="GET" class="row g-3 mb-3">
                            <div class="col-md-6">
                                <select name="id_reseller" class="form-select">
                                    <option value="0">Semua Reseller</option>
                                    <?php foreach ($resellers as $res): ?>
                                        <option value="<?= $res['id_reseller'] ?>" <?= ($id_reseller == $res['id_reseller']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($res['nama_reseller']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="all" <?= ($status == 'all') ? 'selected' : '' ?>>Semua Status</option>
                                    <option value="lunas" <?= ($status == 'lunas') ? 'selected' : '' ?>>Lunas</option>
                                    <option value="cicilan" <?= ($status == 'cicilan') ? 'selected' : '' ?>>Cicilan</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-filter"></i> Filter
                                </button>
                                <?php if ($id_reseller > 0 || $status != 'all'): ?>
                                    <a href="list.php" class="btn btn-secondary ms-2">
                                        <i class="bx bx-reset"></i> Reset
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>

                        <div class="card p-3">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th style="width: 50px;">No</th>
                                            <th>Tanggal</th>
                                            <th>Pelanggan</th>
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
                                                    <td><?= dateIndo($jual['tanggal_penjualan']) ?></td>
                                                    <td><?= htmlspecialchars($jual['nama_reseller']) ?></td>
                                                    <td><?= formatRupiah($jual['total_harga']) ?></td>
                                                    <td class="text-center">
                                                        <?php if ($jual['status_pembayaran'] == 'lunas'): ?>
                                                            <span class="badge bg-success">LUNAS</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark">CICILAN</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="btn-group" role="group" aria-label="Aksi Penjualan">

                                                            <button class="btn btn-sm btn-danger btn-batal" data-id="<?= $jual['id_penjualan'] ?>" title="Batalkan Penjualan">
                                                                <i class="bx bx-x-circle"></i>
                                                            </button>
                                                            <a href="cicilan.php?id=<?= $jual['id_penjualan'] ?>" class="btn btn-sm btn-warning" title="Pembayaran">
                                                                <i class="bx bx-money"></i>
                                                            </a>
                                                            <a href="detail.php?id=<?= $jual['id_penjualan'] ?>" class="btn btn-sm btn-primary" title="Detail">
                                                                <i class="bx bx-detail"></i>
                                                            </a>


                                                            <a href="nota.php?id=<?= $jual['id_penjualan'] ?>" target="_blank" class="btn btn-sm btn-info" title="Nota">
                                                                <i class="bx bx-receipt"></i>
                                                            </a>


                                                        </div>
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

    <script>
        document.querySelectorAll('.btn-batal').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Yakin ingin membatalkan penjualan ini?',
                    text: "Tindakan ini akan menghapus data penjualan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, batalkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'batal.php?id=' + id;
                    }
                });
            });
        });
    </script>


    <?php include '../includes/footer.php'; ?>
</body>

</html>