<?php
require_once __DIR__ . '../../includes/header.php';

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

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_bahan = $conn->real_escape_string($_POST['id_bahan']);
    $id_pemotong = $conn->real_escape_string($_POST['id_pemotong']);
    $jumlah = $conn->real_escape_string($_POST['jumlah']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);

    // Cek stok cukup
    $cek_stok = $conn->query("SELECT jumlah_stok FROM bahan_baku WHERE id_bahan = $id_bahan");
    $stok = $cek_stok->fetch_assoc();

    if ($stok['jumlah_stok'] >= $jumlah) {
        $sql = "INSERT INTO pengiriman_pemotong (id_bahan, id_pemotong, jumlah_bahan, tanggal_kirim)
                VALUES ($id_bahan, $id_pemotong, $jumlah, '$tanggal')";

        if ($conn->query($sql)) {
            $_SESSION['success'] = "Pengiriman ke pemotong berhasil dicatat";
            header("Location: pengiriman_pemotong.php");
            exit();
        } else {
            $error = "Gagal mencatat pengiriman: " . $conn->error;
        }
    } else {
        $error = "Stok bahan tidak mencukupi! Stok tersedia: " . $stok['jumlah_stok'];
    }
}

// Ambil data untuk dropdown
$bahan = query("SELECT * FROM bahan_baku WHERE jumlah_stok > 0");
$pemotong = query("SELECT * FROM pemotong");
$pengiriman = query("SELECT p.*, b.nama_bahan, b.satuan, pm.nama_pemotong 
                    FROM pengiriman_pemotong p
                    JOIN bahan_baku b ON p.id_bahan = b.id_bahan
                    JOIN pemotong pm ON p.id_pemotong = pm.id_pemotong
                   ORDER BY 
                        (p.status = 'dikirim') DESC, 
                        p.tanggal_kirim DESC
                    LIMIT 5");
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
                            <h2>1. Tambah Data Pengiriman Pemotong</h2>
                            <div class="btn-group ms-auto" role="group" aria-label="Navigasi Form">
                                <!-- <a href="#" class="btn btn-outline-warning">Kembali</a> -->
                                <a href="hasil_pemotongan.php" class="btn btn-outline-primary">Next</a>
                            </div>
                        </div>


                        <div class="card p-4 shadow-sm">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= htmlspecialchars($_SESSION['error']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?= htmlspecialchars($_SESSION['success']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>


                            <form method="post" class="mb-4">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label">Bahan Baku</label>
                                        <select name="id_bahan" class="form-select" required>
                                            <option value="">- Pilih Bahan -</option>
                                            <?php foreach ($bahan as $b): ?>
                                                <option value="<?= $b['id_bahan'] ?>">
                                                    <?= $b['nama_bahan'] ?> | Stok: <?= number_format($b['jumlah_stok'], 0, '', '') ?> <?= $b['satuan'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-6 mb-3">
                                        <label class="form-label">Pemotong</label>
                                        <select name="id_pemotong" class="form-select" required>
                                            <option value="">- Pilih Pemotong -</option>
                                            <?php foreach ($pemotong as $p): ?>
                                                <option value="<?= $p['id_pemotong'] ?>"><?= $p['nama_pemotong'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4 mb-3">
                                        <label class="form-label">Jumlah</label>
                                        <div class="input-group">
                                            <input type="number" name="jumlah" step="1" class="form-control" required>
                                            <span class="input-group-text">Roll</span>
                                        </div>
                                    </div>

                                    <div class="col-8 mb-3">
                                        <label class="form-label">Tanggal Pengiriman <span class="text-danger">(Bulan/Tanggal/Tahun)</span></label>
                                        <input type="date" name="tanggal" class="form-control" required value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">Simpan Pengiriman</button>
                                    <a href="riwayat_pengiriman_pemotong.php" class="btn btn-secondary">Riwayat Pengiriman</a>
                                </div>
                                <!-- <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">Simpan Pengiriman</button>
                                    <div class="btn-group">
                                        <a href="riwayat_pengiriman_pemotong.php" class="btn btn-secondary">Riwayat Pengiriman</a>
                                        <a href="batal_pengiriman_pemotong.php" class="btn btn-danger"
                                            onclick="return confirm('Yakin ingin membatalkan pengiriman terakhir?')">
                                            Batal Simpan Pengiriman
                                        </a>
                                    </div>
                                </div> -->
                            </form>

                            <hr>

                            <h4>Riwayat Pengiriman</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Bahan Baku</th>
                                            <th>Pemotong</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($pengiriman as $p): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= dateIndo($p['tanggal_kirim']) ?></td>
                                                <td><?= $p['nama_bahan'] ?></td>
                                                <td><?= $p['nama_pemotong'] ?></td>
                                                <td class="text-center"><?= number_format($p['jumlah_bahan'], 0, "", "") . " " . htmlspecialchars($p['satuan']) ?></td>
                                                <!-- <td><?= ucfirst($p['status']) ?></td> -->
                                                <td class="text-center">
                                                    <span class="badge <?= $p['status'] == 'dikirim' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                        <?= $p['status'] == 'dikirim' ? 'Dalam Proses' : 'Selesai' ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($p['status'] == 'dikirim'): ?>

                                                            <a href="batal_pengiriman_pemotong.php?id=<?= $p['id_pengiriman_potong'] ?>"
                                                                class="btn btn-danger btn-batal">
                                                                <i class="bx bx-x"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($pengiriman)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Belum ada data pengiriman.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- SweetAlert2 -->
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

                    <script>
                        // Konfirmasi pembatalan
                        document.querySelectorAll('.btn-batal').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.preventDefault();
                                const url = this.getAttribute('href');

                                Swal.fire({
                                    title: 'Yakin membatalkan pengiriman?',
                                    text: "Data yang dibatalkan tidak bisa dikembalikan!",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: 'Ya, Batalkan!',
                                    cancelButtonText: 'Tidak'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = url;
                                    }
                                });
                            });
                        });
                    </script>

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