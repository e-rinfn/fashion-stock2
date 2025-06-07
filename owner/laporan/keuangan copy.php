<?php
$pageTitle = "Laporan Penjualan";
require_once '../includes/header.php';

// Filter tanggal
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

// Query data penjualan
$penjualan = query("SELECT p.id_penjualan, p.tanggal_penjualan, r.nama_reseller, 
                    p.total_harga, p.status_pembayaran, 
                    (SELECT SUM(jumlah_cicilan) FROM cicilan WHERE id_penjualan = p.id_penjualan) as dibayar
                    FROM penjualan p
                    JOIN reseller r ON p.id_reseller = r.id_reseller
                    WHERE p.tanggal_penjualan BETWEEN '$startDate' AND '$endDate'
                    ORDER BY p.tanggal_penjualan DESC");

// Hitung total penjualan
$totalPenjualan = query("SELECT SUM(total_harga) as total FROM penjualan 
                       WHERE tanggal_penjualan BETWEEN '$startDate' AND '$endDate'")[0]['total'] ?? 0;

// Query data keuangan
$pemasukan = query("SELECT SUM(total_harga) as total FROM penjualan 
                   WHERE status_pembayaran = 'lunas'")[0]['total'] ?? 0;

$pengeluaran = query("SELECT SUM(total_harga) as total FROM pembelian_bahan")[0]['total'] ?? 0;

$laba = $pemasukan - $pengeluaran;
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
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-white bg-success mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Pemasukan</h5>
                                        <h2 class="card-text"><?= formatRupiah($pemasukan) ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-danger mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Pengeluaran</h5>
                                        <h2 class="card-text"><?= formatRupiah($pengeluaran) ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-info mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Laba/Rugi</h5>
                                        <h2 class="card-text"><?= formatRupiah($laba) ?></h2>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="card-header">
                            <h5 class="mb-0">Detail Transaksi</h5>
                        </div>
                        <form method="get" class="row g-2">
                            <div class="col-auto">
                                <input type="date" name="start_date" value="<?= $startDate ?>" class="form-control form-control-sm">
                            </div>
                            <div class="col-auto">
                                <input type="date" name="end_date" value="<?= $endDate ?>" class="form-control form-control-sm">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                <a href="penjualan.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Reseller</th>
                                            <th>Total</th>
                                            <th>Dibayar</th>
                                            <th>Sisa</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($penjualan)) : ?>
                                            <tr>
                                                <td colspan="8" class="text-center">Tidak ada data penjualan</td>
                                            </tr>
                                        <?php else : ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($penjualan as $jual) : ?>
                                                <?php
                                                $dibayar = $jual['dibayar'] ?? 0;
                                                $sisa = $jual['total_harga'] - $dibayar;
                                                ?>
                                                <tr>
                                                    <td><?= $no++; ?></td>
                                                    <td><?= date('d/m/Y', strtotime($jual['tanggal_penjualan'])) ?></td>
                                                    <td><?= htmlspecialchars($jual['nama_reseller']) ?></td>
                                                    <td class="text-end"><?= formatRupiah($jual['total_harga']) ?></td>
                                                    <td class="text-end"><?= formatRupiah($dibayar) ?></td>
                                                    <td class="text-end"><?= formatRupiah($sisa) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $jual['status_pembayaran'] === 'lunas' ? 'success' : 'warning' ?>">
                                                            <?= ucfirst($jual['status_pembayaran']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="../penjualan/detail.php?id=<?= $jual['id_penjualan'] ?>"
                                                            class="btn btn-sm btn-info" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="table-active">
                                                <th colspan="3" class="text-end">TOTAL</th>
                                                <th class="text-end"><?= formatRupiah($totalPenjualan) ?></th>
                                                <th colspan="4"></th>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Statistik Penjualan</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="penjualanChart" height="100"></canvas>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Chart Penjualan
            const ctx = document.getElementById('penjualanChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($penjualan, 'nama_reseller')) ?>,
                    datasets: [{
                        label: 'Total Penjualan',
                        data: <?= json_encode(array_column($penjualan, 'total_harga')) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp' + context.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

    <!-- Core JS footer -->
    <?php include '../includes/footer.php'; ?>
    <!-- /Core JS footer -->


</body>

</html>