<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
// redirectIfNotLoggedIn();
// checkRole('admin');

$produk = query("SELECT * FROM produk WHERE stok > 0 ORDER BY nama_produk");
$reseller = query("SELECT * FROM reseller ORDER BY nama_reseller");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_penjualan'])) {
    $id_reseller = intval($_POST['id_reseller']);
    $metode_pembayaran = $conn->real_escape_string($_POST['metode_pembayaran']);
    $status_pembayaran = $conn->real_escape_string($_POST['status_pembayaran']);
    $items = $_POST['items'];

    // Validasi duplikasi produk
    $productIds = array_column($items, 'id_produk');
    if (count($productIds) !== count(array_unique($productIds))) {
        $error = "Tidak boleh ada produk yang duplikat dalam satu penjualan!";
    } else {
        // Validasi stok
        foreach ($items as $item) {
            $id_produk = intval($item['id_produk']);
            $qty = intval($item['qty']);
            $produk = query("SELECT stok FROM produk WHERE id_produk = $id_produk")[0];

            if ($qty > $produk['stok']) {
                $error = "Jumlah melebihi stok tersedia untuk produk ID $id_produk";
                break;
            }
        }

        if (!isset($error)) {
            $total_harga = 0;

            foreach ($items as $item) {
                $id_produk = intval($item['id_produk']);
                $qty = intval($item['qty']);

                if ($qty <= 0) {
                    $error = "Jumlah produk tidak boleh nol.";
                    break;
                }

                $produk = query("SELECT harga_jual FROM produk WHERE id_produk = $id_produk")[0];
                $total_harga += $produk['harga_jual'] * $qty;
            }

            if (!isset($error)) {
                $conn->autocommit(FALSE);
                try {
                    $sql_penjualan = "INSERT INTO penjualan (id_reseller, tanggal_penjualan, total_harga, status_pembayaran, metode_pembayaran) 
                                      VALUES ($id_reseller, NOW(), $total_harga, '$status_pembayaran', '$metode_pembayaran')";
                    if (!$conn->query($sql_penjualan)) throw new Exception("Gagal menyimpan penjualan");

                    $id_penjualan = $conn->insert_id;

                    foreach ($items as $item) {
                        $id_produk = intval($item['id_produk']);
                        $qty = intval($item['qty']);
                        $produk = query("SELECT harga_jual, stok FROM produk WHERE id_produk = $id_produk")[0];

                        if ($produk['stok'] < $qty) throw new Exception("Stok produk tidak mencukupi untuk produk ID $id_produk");

                        $subtotal = $produk['harga_jual'] * $qty;

                        $sql_detail = "INSERT INTO detail_penjualan (id_penjualan, id_produk, jumlah, harga_satuan, subtotal) 
                                       VALUES ($id_penjualan, $id_produk, $qty, {$produk['harga_jual']}, $subtotal)";
                        if (!$conn->query($sql_detail)) throw new Exception("Gagal menyimpan detail penjualan");

                        $new_stok = $produk['stok'] - $qty;
                        $sql_update = "UPDATE produk SET stok = $new_stok WHERE id_produk = $id_produk";
                        if (!$conn->query($sql_update)) throw new Exception("Gagal update stok produk");
                    }

                    $conn->commit();
                    $conn->autocommit(TRUE);
                    header("Location: detail.php?id=$id_penjualan");
                    exit();
                } catch (Exception $e) {
                    $conn->rollback();
                    $conn->autocommit(TRUE);
                    $error = $e->getMessage();
                }
            }
        }
    }
}
?>

<style>
    /* Paksa SweetAlert berada di atas segalanya */
    .swal2-container {
        z-index: 99999 !important;
    }

    .error {
        color: #dc3545;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
    }

    .currency-format {
        text-align: right;
    }
</style>

<?php include '../includes/header.php'; ?>

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
                            <h2>Tambah Penjualan</h2>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <form method="post" id="formPenjualan">
                                        <div class="card border border-dark shadow-sm rounded-3">
                                            <div class="card-header">
                                                <h3>Informasi Penjualan</h3>
                                            </div>
                                            <div class="card-body">
                                                <?php if (isset($error)): ?>
                                                    <div class="alert error"><?= $error ?></div>
                                                <?php endif; ?>

                                                <div class="row g-3 align-items-center">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Reseller</label>
                                                        <select name="id_reseller" class="form-control" required>
                                                            <option value="">Pilih Reseller</option>
                                                            <?php foreach ($reseller as $r): ?>
                                                                <option value="<?= $r['id_reseller'] ?>"><?= $r['nama_reseller'] ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Metode Pembayaran</label>
                                                        <select name="metode_pembayaran" class="form-control" required>
                                                            <option value="transfer">Transfer Bank</option>
                                                            <option value="tunai">Tunai</option>
                                                            <option value="e-wallet">E-Wallet</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Status Pembayaran</label>
                                                        <select name="status_pembayaran" class="form-control" required>
                                                            <option value="cicilan">Cicilan</option>
                                                            <option value="lunas" hidden>Lunas</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mt-3 border border-dark shadow-sm rounded-3">
                                            <div class="card-header">
                                                <h3>Daftar Produk</h3>
                                            </div>
                                            <div class="card-body">
                                                <table class="table" id="tabelProduk">
                                                    <thead>
                                                        <tr>
                                                            <th>Produk</th>
                                                            <th>Harga</th>
                                                            <th>Stok</th>
                                                            <th>Qty</th>
                                                            <th>Subtotal</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="produkContainer"></tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="4" class="text-right"><strong>Total</strong></td>
                                                            <td class="currency-format"><span id="totalHarga">0</span></td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>

                                                <button type="button" class="btn btn-secondary mt-3" id="tambahProduk">+ Tambah Produk</button>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <button type="submit" name="simpan_penjualan" class="btn btn-primary">Simpan Penjualan</button>
                                            <a href="list.php" class="btn btn-danger">Batal</a>
                                        </div>
                                    </form>
                                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const produkData = <?= json_encode($produk) ?>;
        let selectedProducts = []; // Menyimpan ID produk yang sudah dipilih

        document.getElementById('tambahProduk').addEventListener('click', function() {
            const container = document.getElementById('produkContainer');
            const rowId = Date.now();

            // Filter produk yang belum dipilih
            const availableProducts = produkData.filter(p => !selectedProducts.includes(p.id_produk));

            // if (availableProducts.length === 0) {
            //     alert('Semua produk sudah ditambahkan atau tidak ada stok tersedia');
            //     return;
            // }


            if (availableProducts.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Oops!',
                    text: 'Semua jenis produk sudah ditambahkan atau tidak ada stok tersedia',
                    confirmButtonText: 'Oke'
                });
                return;
            }


            let options = '<option value="">Pilih Produk</option>';
            availableProducts.forEach(produk => {
                options += `<option value="${produk.id_produk}">${produk.nama_produk}</option>`;
            });

            const row = document.createElement('tr');
            row.id = `row-${rowId}`;
            row.innerHTML = `
                <td>
                    <select name="items[${rowId}][id_produk]" class="form-control select-produk" required>
                        ${options}
                    </select>
                </td>
                <td class="currency-format harga">0</td>
                <td class="stok">0</td>
                <td>
                    <input type="number" name="items[${rowId}][qty]" class="form-control qty" min="1" value="1" required>
                    <small class="text-danger stok-error" style="display:none">Melebihi stok tersedia</small>
                </td>
                <td class="currency-format subtotal">0</td>
                <td><button type="button" class="btn btn-sm btn-danger hapus-produk" data-row="${rowId}">Hapus</button></td>
            `;
            container.appendChild(row);
            initRowEvents(rowId);
            hitungTotal();
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('hapus-produk')) {
                const rowId = e.target.dataset.row;
                const row = document.getElementById(`row-${rowId}`);
                const selectedProductId = row.querySelector('.select-produk').value;

                // Hapus produk dari daftar yang sudah dipilih
                if (selectedProductId) {
                    selectedProducts = selectedProducts.filter(id => id != selectedProductId);
                }

                row.remove();
                hitungTotal();
            }
        });

        function initRowEvents(rowId) {
            const row = document.getElementById(`row-${rowId}`);
            const select = row.querySelector('.select-produk');
            const qtyInput = row.querySelector('.qty');
            const stokError = row.querySelector('.stok-error');

            select.addEventListener('change', function() {
                const previousProductId = select.dataset.previousValue;

                // Hapus produk sebelumnya dari daftar yang dipilih
                if (previousProductId) {
                    selectedProducts = selectedProducts.filter(id => id != previousProductId);
                }

                const newProductId = this.value;

                // Jika produk dipilih (bukan kosong)
                if (newProductId) {
                    // Tambahkan ke daftar produk yang dipilih
                    selectedProducts.push(newProductId);
                    select.dataset.previousValue = newProductId;

                    const produk = produkData.find(p => p.id_produk == newProductId);
                    if (produk) {
                        row.querySelector('.harga').textContent = formatCurrency(produk.harga_jual);
                        row.querySelector('.stok').textContent = produk.stok;
                        qtyInput.max = produk.stok;
                        qtyInput.value = 1;
                        hitungSubtotal(rowId);
                    }
                } else {
                    select.dataset.previousValue = '';
                }

                // Update dropdown di semua row
                updateProductDropdowns();
            });

            qtyInput.addEventListener('input', function() {
                const maxStok = parseInt(qtyInput.max) || 0;
                const enteredQty = parseInt(this.value) || 0;

                if (enteredQty > maxStok) {
                    stokError.style.display = 'block';
                    this.value = maxStok;
                } else {
                    stokError.style.display = 'none';
                }

                hitungSubtotal(rowId);
            });
        }

        function updateProductDropdowns() {
            document.querySelectorAll('.select-produk').forEach(select => {
                const currentValue = select.value;
                const rowId = select.closest('tr').id.split('-')[1];

                // Filter produk yang belum dipilih ATAU produk yang sedang dipilih di dropdown ini
                const availableProducts = produkData.filter(p =>
                    !selectedProducts.includes(p.id_produk) || p.id_produk == currentValue
                );

                let options = '<option value="">Pilih Produk</option>';
                availableProducts.forEach(produk => {
                    const selected = produk.id_produk == currentValue ? 'selected' : '';
                    options += `<option value="${produk.id_produk}" ${selected}>${produk.nama_produk}</option>`;
                });

                select.innerHTML = options;
            });
        }

        function formatCurrency(amount) {
            return 'Rp ' + Number(amount).toLocaleString('id-ID');
        }


        function hitungSubtotal(rowId) {
            const row = document.getElementById(`row-${rowId}`);
            const harga = parseFloat(row.querySelector('.harga').textContent.replace(/[^0-9]/g, '')) || 0;
            const qty = parseInt(row.querySelector('.qty').value) || 0;
            const subtotal = harga * qty;
            row.querySelector('.subtotal').textContent = formatCurrency(subtotal);
            hitungTotal();
        }

        function hitungTotal() {
            let total = 0;
            document.querySelectorAll('#produkContainer tr').forEach(row => {
                const subtotal = parseFloat(row.querySelector('.subtotal').textContent.replace(/[^0-9]/g, '')) || 0;
                total += subtotal;
            });
            document.getElementById('totalHarga').textContent = formatCurrency(total);
        }

        // Tambahkan satu produk secara default saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('tambahProduk').click();
        });
    </script>

    <!-- Core JS footer -->
    <?php include '../includes/footer.php'; ?>
    <!-- /Core JS footer -->
</body>

</html>