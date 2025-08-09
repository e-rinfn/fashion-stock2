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

// Ambil semua supplier untuk dropdown
$suppliers = query("SELECT * FROM supplier");

// Cek filter yang diterapkan
$id_supplier = isset($_GET['id_supplier']) ? (int)$_GET['id_supplier'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Bangun query berdasarkan filter
$sql = "SELECT p.*, s.nama_supplier
        FROM pembelian p
        JOIN supplier s ON p.id_supplier = s.id_supplier
        WHERE 1=1";

// Filter supplier
if ($id_supplier > 0) {
    $sql .= " AND p.id_supplier = $id_supplier";
}

// Filter status
if ($status != 'all') {
    $sql .= " AND p.status_pembayaran = '$status'";
}

$sql .= " ORDER BY p.tanggal_pembelian DESC";

$pembelian = query($sql);



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
                            <h2>Data Pembelian Barang</h2>
                            <a href="new.php" class="btn btn-success">
                                <i class="bx bx-plus-circle"></i> Tambah Pesanan
                            </a>
                        </div>

                        <!-- Tampilkan pesan error atau success -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>
                        <!-- /Tampilkan pesan error atau success -->

                        <!-- Filter Form -->
                        <form method="GET" class="row g-3 mb-3">
                            <div class="col-md-6">
                                <select name="id_supplier" class="form-select">
                                    <option value="0">Semua Supplier</option>
                                    <?php foreach ($suppliers as $sup): ?>
                                        <option value="<?= $sup['id_supplier'] ?>" <?= ($id_supplier == $sup['id_supplier']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sup['nama_supplier']) ?>
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
                                <?php if ($id_supplier > 0 || $status != 'all'): ?>
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
                                            <th>Supplier</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th style="width: 200px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pembelian)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Tidak ada data pembelian</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($pembelian as $p): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><?= dateIndo($p['tanggal_pembelian']) ?></td>
                                                    <td><?= htmlspecialchars($p['nama_supplier']) ?></td>
                                                    <td><?= formatRupiah($p['total_harga']) ?></td>
                                                    <td class="text-center">
                                                        <?php if ($p['status_pembayaran'] == 'lunas'): ?>
                                                            <span class="badge bg-success">LUNAS</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark">CICILAN</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group" role="group" aria-label="Aksi Pembelian">
                                                            <!-- Tombol Batal (hanya muncul jika belum lunas) namun kali ini saya aktifkan untuk fungsi menghapus status lunas -->
                                                            <?php if ($p['status_pembayaran'] != ''): ?>
                                                            <?php endif; ?>
                                                            <?php if ($p['status_pembayaran'] == 'cicilan'): ?>
                                                            <?php endif; ?>
                                                            <button class="btn btn-sm btn-danger btn-batal" data-id="<?= $p['id_pembelian'] ?>" title="Batalkan Pembelian">
                                                                <i class="bx bx-x-circle"></i>
                                                            </button>
                                                            <a href="cicilan.php?id=<?= $p['id_pembelian'] ?>" class="btn btn-sm btn-warning" title="Pembayaran">
                                                                <i class="bx bx-money"></i>
                                                            </a>
                                                            <!-- <a href="detail.php?id=<?= $p['id_pembelian'] ?>" class="btn btn-sm btn-primary" title="Detail">
                                                                <i class="bx bx-detail"></i>
                                                            </a> -->
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
        document.querySelectorAll('.btn-batal').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Yakin ingin membatalkan pembelian ini?',
                    text: "Tindakan ini akan mengembalikan stok produk dan menghapus data pembelian!",
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