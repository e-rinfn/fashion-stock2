<?php
require_once '../includes/header.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header("Location: {$base_url}auth/login.php");
    exit;
}

// Query data bahan baku
$sql = "SELECT * FROM bahan_baku ORDER BY nama_bahan";
$bahan_baku = query($sql);

// Proses tambah stok jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_stok'])) {
    $id_bahan = intval($_POST['id_bahan']);
    $jumlah = floatval($_POST['jumlah']);

    // Validasi input
    if ($id_bahan > 0 && $jumlah > 0) {
        // Update stok di database
        $sql_update = "UPDATE bahan_baku SET jumlah_stok = jumlah_stok + $jumlah WHERE id_bahan = $id_bahan";
        if ($conn->query($sql_update)) {
            $_SESSION['success'] = "Stok berhasil ditambahkan";
            header("Location: list.php");
            exit();
        } else {
            $error = "Gagal menambahkan stok: " . $conn->error;
        }
    } else {
        $error = "Jumlah tidak valid!";
    }
}

// Query data bahan baku
$sql = "SELECT * FROM bahan_baku ORDER BY nama_bahan";
$bahan_baku = query($sql);

// Proses tambah stok jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah_stok'])) {
        $id_bahan = intval($_POST['id_bahan']);
        $jumlah = floatval($_POST['jumlah']);

        // Validasi input
        if ($id_bahan > 0 && $jumlah > 0) {
            // Update stok di database
            $sql_update = "UPDATE bahan_baku SET jumlah_stok = jumlah_stok + $jumlah WHERE id_bahan = $id_bahan";
            if ($conn->query($sql_update)) {
                $_SESSION['success'] = "Stok berhasil ditambahkan";
                header("Location: list.php");
                exit();
            } else {
                $error = "Gagal menambahkan stok: " . $conn->error;
            }
        } else {
            $error = "Jumlah tidak valid!";
        }
    }

    // Proses adjust stok (tambah/kurang)
    if (isset($_POST['adjust_stok'])) {
        $id_bahan = intval($_POST['id_bahan']);
        $jumlah = floatval($_POST['adjust_jumlah']);
        $tipe = $_POST['adjust_tipe']; // 'tambah' atau 'kurang'

        // Validasi input
        if ($id_bahan > 0 && $jumlah > 0) {
            // Update stok berdasarkan tipe
            if ($tipe == 'kurang') {
                // Cek stok cukup
                $current_stock = query("SELECT jumlah_stok FROM bahan_baku WHERE id_bahan = $id_bahan")[0]['jumlah_stok'];
                if ($current_stock < $jumlah) {
                    $error = "Stok tidak cukup! Stok tersedia: $current_stock";
                } else {
                    $sql_update = "UPDATE bahan_baku SET jumlah_stok = jumlah_stok - $jumlah WHERE id_bahan = $id_bahan";
                }
            } else {
                $sql_update = "UPDATE bahan_baku SET jumlah_stok = jumlah_stok + $jumlah WHERE id_bahan = $id_bahan";
            }

            if (isset($sql_update) && $conn->query($sql_update)) {
                $_SESSION['success'] = "Stok berhasil disesuaikan";
                header("Location: list.php");
                exit();
            } elseif (!isset($sql_update)) {
                $error = $error ?? "Gagal menyesuaikan stok";
            } else {
                $error = "Gagal menyesuaikan stok: " . $conn->error;
            }
        } else {
            $error = "Jumlah tidak valid!";
        }
    }
}
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
                            <h2>Data Bahan Baku</h2>
                            <div>
                                <a href="add.php" class="btn btn-success">
                                    <i class="bx bx-plus-circle"></i> Tambah Bahan Baku
                                </a>
                                <!-- <a href="../laporan/stok.php" class="btn btn-warning btn-sm m-1">
                                    <i class="bx bx-file"></i> Laporan
                                </a> -->
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
                                            <th>No</th>
                                            <th>Nama Bahan</th>
                                            <th>Stok</th>
                                            <th>Satuan</th>
                                            <th>Harga/Satuan</th>
                                            <th>Supplier</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($bahan_baku)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada data bahan baku</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($bahan_baku as $bahan): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++; ?></td>
                                                    <td><?= htmlspecialchars($bahan['nama_bahan']); ?></td>
                                                    <td class="text-end"><?= $bahan['jumlah_stok']; ?></td>
                                                    <td class="text-center"><?= htmlspecialchars($bahan['satuan']); ?></td>
                                                    <td class="text-end"><?= formatRupiah($bahan['harga_per_satuan']); ?></td>
                                                    <td><?= htmlspecialchars($bahan['supplier']); ?></td>
                                                    <td class="text-center">
                                                        <a href="edit.php?id=<?= $bahan['id_bahan']; ?>" class="btn btn-primary btn-sm me-1 mb-1">
                                                            <i class="bx bx-edit"></i> Edit
                                                        </a>

                                                        <a href="delete.php?id=<?= $bahan['id_bahan']; ?>"
                                                            class="btn btn-danger btn-sm me-1 mb-1 btn-delete"
                                                            data-id="<?= $bahan['id_bahan']; ?>">
                                                            <i class="bx bx-trash"></i> Hapus
                                                        </a>

                                                        <!-- <button type="button"
                                                            class="btn btn-info btn-sm mb-1"
                                                            onclick="showStokForm(<?= $bahan['id_bahan']; ?>)">
                                                            <i class="bx bx-plus"></i> Stok
                                                        </button> -->

                                                        <button type="button"
                                                            class="btn btn-warning btn-sm mb-1"
                                                            onclick="showAdjustForm(<?= $bahan['id_bahan']; ?>)">
                                                            <i class="bx bx-adjust"></i> Adjust
                                                        </button>
                                                    </td>


                                                </tr>
                                                <!-- Form tambah stok (hidden) -->
                                                <tr id="stok-form-<?= $bahan['id_bahan']; ?>" class="stok-form" style="display:none;">
                                                    <td colspan="7">
                                                        <form method="post" class="d-flex flex-wrap align-items-center gap-2">
                                                            <input type="hidden" name="id_bahan" value="<?= $bahan['id_bahan']; ?>">
                                                            <label class="form-label mb-0">Jumlah Tambahan:</label>
                                                            <input type="number" name="jumlah" step="0.01" min="0.01" required
                                                                class="form-control w-auto" placeholder="Masukkan jumlah">
                                                            <span><?= $bahan['satuan']; ?></span>
                                                            <button type="submit" name="tambah_stok" class="btn btn-success btn-sm">Simpan</button>
                                                            <button type="button" class="btn btn-secondary btn-sm"
                                                                onclick="hideStokForm(<?= $bahan['id_bahan']; ?>)">Batal</button>
                                                        </form>
                                                    </td>
                                                </tr>

                                                <!-- Form adjust stok (hidden) -->
                                                <tr id="adjust-form-<?= $bahan['id_bahan']; ?>" class="adjust-form" style="display:none;">
                                                    <td colspan="7">
                                                        <form method="post" class="d-flex flex-wrap align-items-center gap-2">
                                                            <input type="hidden" name="id_bahan" value="<?= $bahan['id_bahan']; ?>">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <select name="adjust_tipe" class="form-select w-auto">
                                                                    <option value="tambah">Tambah</option>
                                                                    <option value="kurang">Kurangi</option>
                                                                </select>
                                                                <input type="number" name="adjust_jumlah" step="0.01" min="0.01" required
                                                                    class="form-control w-auto" placeholder="Jumlah">
                                                                <span><?= $bahan['satuan']; ?></span>
                                                            </div>
                                                            <button type="submit" name="adjust_stok" class="btn btn-warning btn-sm">Simpan</button>
                                                            <button type="button" class="btn btn-secondary btn-sm"
                                                                onclick="hideAdjustForm(<?= $bahan['id_bahan']; ?>)">Batal</button>
                                                        </form>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    const url = this.getAttribute('href');

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
                            window.location.href = url;
                        }
                    });
                });
            });
        });
    </script>


    <script>
        // Fungsi untuk menampilkan/sembunyikan form adjust stok
        function showAdjustForm(id) {
            // Sembunyikan semua form yang mungkin terbuka
            document.querySelectorAll('.stok-form, .adjust-form').forEach(form => {
                form.style.display = 'none';
            });

            // Tampilkan form adjust untuk bahan yang dipilih
            document.getElementById('adjust-form-' + id).style.display = '';
        }

        function hideAdjustForm(id) {
            document.getElementById('adjust-form-' + id).style.display = 'none';
        }

        // Pertahankan fungsi sebelumnya
        function showStokForm(id) {
            document.querySelectorAll('.stok-form, .adjust-form').forEach(form => {
                form.style.display = 'none';
            });
            document.getElementById('stok-form-' + id).style.display = '';
        }

        function hideStokForm(id) {
            document.getElementById('stok-form-' + id).style.display = 'none';
        }
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