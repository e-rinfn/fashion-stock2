<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
// redirectIfNotLoggedIn();
// checkRole('admin');

$produk = query("SELECT * FROM produk WHERE stok > 0 ORDER BY nama_produk");
$supplier = query("SELECT * FROM supplier ORDER BY nama_supplier");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_pembelian'])) {
    $id_supplier = intval($_POST['id_supplier']);
    $metode_pembayaran = $conn->real_escape_string($_POST['metode_pembayaran']);
    $status_pembayaran = $conn->real_escape_string($_POST['status_pembayaran']);
    $items = $_POST['items'];

    // Validasi duplikasi produk
    $productIds = array_column($items, 'id_produk');
    if (count($productIds) !== count(array_unique($productIds))) {
        $error = "Tidak boleh ada produk yang duplikat dalam satu pembelian!";
    } else {
        $total_harga = 0;

        foreach ($items as $item) {
            $id_produk = intval($item['id_produk']);
            $qty = intval($item['qty']);
            $harga = floatval($item['harga']);

            if ($qty <= 0) {
                $error = "Jumlah produk tidak boleh nol.";
                break;
            }

            if ($harga <= 0) {
                $error = "Harga produk tidak boleh nol atau negatif.";
                break;
            }

            $total_harga += $harga * $qty;
        }

        if (!isset($error)) {
            $conn->autocommit(FALSE);
            try {
                $sql_pembelian = "INSERT INTO pembelian (id_supplier, tanggal_pembelian, total_harga, status_pembayaran, metode_pembayaran) 
                                  VALUES ($id_supplier, NOW(), $total_harga, '$status_pembayaran', '$metode_pembayaran')";
                if (!$conn->query($sql_pembelian)) throw new Exception("Gagal menyimpan pembelian");

                $id_pembelian = $conn->insert_id;

                foreach ($items as $item) {
                    $id_produk = intval($item['id_produk']);
                    $qty = intval($item['qty']);
                    $harga = floatval($item['harga']);
                    $produk = query("SELECT stok FROM produk WHERE id_produk = $id_produk")[0];

                    $subtotal = $harga * $qty;

                    $sql_detail = "INSERT INTO detail_pembelian (id_pembelian, id_produk, jumlah, harga_satuan, subtotal) 
                                   VALUES ($id_pembelian, $id_produk, $qty, $harga, $subtotal)";
                    if (!$conn->query($sql_detail)) throw new Exception("Gagal menyimpan detail pembelian");

                    $new_stok = $produk['stok'] + $qty;
                    $sql_update = "UPDATE produk SET stok = $new_stok WHERE id_produk = $id_produk";
                    if (!$conn->query($sql_update)) throw new Exception("Gagal update stok produk");
                }

                $conn->commit();
                $conn->autocommit(TRUE);
                header("Location: cicilan.php?id=$id_pembelian");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $conn->autocommit(TRUE);
                $error = $e->getMessage();
            }
        }
    }
}
?>

<style>
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

    .harga-input {
        min-width: 120px;
    }
</style>

<?php include '../includes/header.php'; ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include '../includes/sidebar.php'; ?>

            <div class="layout-page">
                <?php include '../includes/navbar.php'; ?>

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Tambah Pembelian Produk</h2>
                        </div>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'];
                                                            unset($_SESSION['error']); ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <form method="post" id="formPembelian">
                                    <div class="card border border-dark shadow-sm rounded-3">
                                        <div class="card-body">
                                            <?php if (isset($error)): ?>
                                                <div class="alert error"><?= $error ?></div>
                                            <?php endif; ?>

                                            <div class="row g-3 align-items-center">
                                                <div class="col-md-6">
                                                    <label class="form-label">Supplier</label>
                                                    <select name="id_supplier" class="form-control" required>
                                                        <option value="">-- Pilih Supplier --</option>
                                                        <?php foreach ($supplier as $s): ?>
                                                            <option value="<?= $s['id_supplier'] ?>"><?= $s['nama_supplier'] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div hidden class="col-md-3">
                                                    <label class="form-label">Metode Pembayaran</label>
                                                    <select name="metode_pembayaran" class="form-control" required>
                                                        <option value="transfer">Transfer</option>
                                                        <option value="tunai">Tunai</option>
                                                    </select>
                                                </div>

                                                <div hidden class="col-md-3">
                                                    <label class="form-label">Status Pembayaran</label>
                                                    <select name="status_pembayaran" class="form-control" required>
                                                        <option value="cicilan">Cicilan</option>
                                                        <option value="lunas">Lunas</option>
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
                                                    <tr class="text-center">
                                                        <th>Produk</th>
                                                        <th>Harga/Pcs</th>
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

                                            <button type="button" class="btn btn-secondary mt-3" id="tambahProduk">
                                                <i class="bx bx-plus"></i> Tambah Produk
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" name="simpan_pembelian" class="btn btn-primary">
                                            <i class="bx bx-save"></i> Simpan Pembelian
                                        </button>
                                        <a href="list.php" class="btn btn-danger">
                                            <i class="bx bx-x"></i> Batal
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const produkData = <?= json_encode($produk) ?>;
        let selectedProducts = [];

        document.getElementById('tambahProduk').addEventListener('click', function() {
            const container = document.getElementById('produkContainer');
            const rowId = Date.now();

            const availableProducts = produkData.filter(p => !selectedProducts.includes(p.id_produk));

            if (availableProducts.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak ada produk tersedia',
                    text: 'Semua produk sudah ditambahkan',
                    confirmButtonText: 'OK'
                });
                return;
            }

            let options = '<option value="">Pilih Produk</option>';
            availableProducts.forEach(produk => {
                options += `<option value="${produk.id_produk}" data-harga="${produk.harga_jual}" data-stok="${produk.stok}">
                                ${produk.nama_produk}
                            </option>`;
            });

            const row = document.createElement('tr');
            row.id = `row-${rowId}`;
            row.innerHTML = `
                <td>
                    <select name="items[${rowId}][id_produk]" class="form-control select-produk" required>
                        ${options}
                    </select>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="items[${rowId}][harga]" class="form-control harga-input" min="1" required>
                    </div>
                </td>
                <td class="stok">0</td>
                <td>
                    <div class="input-group">
                        <input type="number" name="items[${rowId}][qty]" class="form-control qty" min="1" value="1" required>
                        <span class="input-group-text">Pcs</span>
                    </div>
                </td>
                <td class="currency-format subtotal">0</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger hapus-produk" data-row="${rowId}">
                        Hapus
                    </button>
                </td>
            `;
            container.appendChild(row);
            initRowEvents(rowId);
        });

        function initRowEvents(rowId) {
            const row = document.getElementById(`row-${rowId}`);
            const select = row.querySelector('.select-produk');
            const hargaInput = row.querySelector('.harga-input');
            const qtyInput = row.querySelector('.qty');
            const stokDisplay = row.querySelector('.stok');

            select.addEventListener('change', function() {
                const previousProductId = select.dataset.previousValue;

                if (previousProductId) {
                    selectedProducts = selectedProducts.filter(id => id != previousProductId);
                }

                const newProductId = this.value;
                const selectedOption = this.options[this.selectedIndex];

                if (newProductId) {
                    selectedProducts.push(newProductId);
                    select.dataset.previousValue = newProductId;

                    // Set nilai default
                    const defaultHarga = selectedOption.getAttribute('data-harga');
                    const stok = selectedOption.getAttribute('data-stok');

                    hargaInput.value = defaultHarga;
                    stokDisplay.textContent = stok;
                    qtyInput.value = 1;

                    hitungSubtotal(rowId);
                } else {
                    select.dataset.previousValue = '';
                    hargaInput.value = '';
                    stokDisplay.textContent = '0';
                    qtyInput.value = 1;
                }

                updateProductDropdowns();
            });

            hargaInput.addEventListener('input', function() {
                if (this.value < 1) this.value = 1;
                hitungSubtotal(rowId);
            });

            qtyInput.addEventListener('input', function() {
                if (this.value < 1) this.value = 1;
                hitungSubtotal(rowId);
            });

            // Trigger change jika ada nilai awal
            if (select.value) select.dispatchEvent(new Event('change'));
        }

        document.addEventListener('click', function(e) {
            if (e.target.closest('.hapus-produk')) {
                const rowId = e.target.closest('.hapus-produk').dataset.row;
                const row = document.getElementById(`row-${rowId}`);
                const select = row.querySelector('.select-produk');

                if (select.value) {
                    selectedProducts = selectedProducts.filter(id => id != select.value);
                }

                row.remove();
                hitungTotal();
                updateProductDropdowns();
            }
        });

        function updateProductDropdowns() {
            document.querySelectorAll('.select-produk').forEach(select => {
                const currentValue = select.value;
                const availableProducts = produkData.filter(p =>
                    !selectedProducts.includes(p.id_produk) || p.id_produk == currentValue
                );

                let options = '<option value="">Pilih Produk</option>';
                availableProducts.forEach(produk => {
                    const selected = produk.id_produk == currentValue ? 'selected' : '';
                    options += `<option value="${produk.id_produk}" data-harga="${produk.harga_jual}" data-stok="${produk.stok}" ${selected}>
                                    ${produk.nama_produk}
                                </option>`;
                });

                select.innerHTML = options;
            });
        }

        function formatCurrency(amount) {
            return 'Rp ' + Number(amount).toLocaleString('id-ID');
        }

        function hitungSubtotal(rowId) {
            const row = document.getElementById(`row-${rowId}`);
            const harga = parseFloat(row.querySelector('.harga-input').value) || 0;
            const qty = parseInt(row.querySelector('.qty').value) || 0;
            const subtotal = harga * qty;
            row.querySelector('.subtotal').textContent = formatCurrency(subtotal);
            hitungTotal();
        }

        function hitungTotal() {
            let total = 0;
            document.querySelectorAll('#produkContainer tr').forEach(row => {
                const subtotalText = row.querySelector('.subtotal').textContent.replace(/[^\d]/g, '');
                total += parseFloat(subtotalText) || 0;
            });
            document.getElementById('totalHarga').textContent = formatCurrency(total);
        }

        // Tambah produk pertama saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('tambahProduk').click();
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>

</html>