<?php
require_once __DIR__ . '../../includes/header.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

function getTotalUpahBelumDibayar($id_penerima, $jenis_penerima)
{
    global $conn;

    $tabel_hasil = ($jenis_penerima == 'pemotong') ? 'hasil_pemotongan' : 'hasil_penjahitan';
    $tabel_pengiriman = ($jenis_penerima == 'pemotong') ? 'pengiriman_pemotong' : 'pengiriman_penjahit';
    $kolom_relasi = ($jenis_penerima == 'pemotong') ? 'id_pengiriman_potong' : 'id_pengiriman_jahit';
    $kolom_penerima = ($jenis_penerima == 'pemotong') ? 'id_pemotong' : 'id_penjahit';

    $sql = "SELECT SUM(h.total_upah) as total
            FROM $tabel_hasil h
            JOIN $tabel_pengiriman p ON h.$kolom_relasi = p.$kolom_relasi
            WHERE p.$kolom_penerima = $id_penerima
            AND NOT EXISTS (
                SELECT 1 FROM detail_pembayaran_upah d 
                WHERE d.id_hasil = h.id_hasil_" . substr($jenis_penerima, 0, 4) . "
                AND d.jenis_hasil = '" . substr($jenis_penerima, 0, 4) . "'
            )";

    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'] ?? 0;
}

// Proses pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bayar'])) {
    $id_penerima = intval($_POST['id_penerima']);
    $jenis_penerima = $conn->real_escape_string($_POST['jenis_penerima']);
    $periode_awal = $conn->real_escape_string($_POST['periode_awal']);
    $periode_akhir = $conn->real_escape_string($_POST['periode_akhir']);
    $metode_pembayaran = $conn->real_escape_string($_POST['metode_pembayaran']);
    $catatan = $conn->real_escape_string($_POST['catatan']);

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // 1. Buat record pembayaran
        $sql1 = "INSERT INTO pembayaran_upah 
                (id_penerima, jenis_penerima, periode_awal, periode_akhir, total_upah, metode_pembayaran, catatan)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql1);
        $total_upah = getTotalUpahBelumDibayar($id_penerima, $jenis_penerima);
        $stmt->bind_param(
            "isssdss",
            $id_penerima,
            $jenis_penerima,
            $periode_awal,
            $periode_akhir,
            $total_upah,
            $metode_pembayaran,
            $catatan
        );
        $stmt->execute();
        $id_pembayaran = $conn->insert_id;

        // 2. Ambil detail hasil yang belum dibayar
        $tabel_hasil = ($jenis_penerima == 'pemotong') ? 'hasil_pemotongan' : 'hasil_penjahitan';
        $tabel_pengiriman = ($jenis_penerima == 'pemotong') ? 'pengiriman_pemotong' : 'pengiriman_penjahit';
        $kolom_relasi = ($jenis_penerima == 'pemotong') ? 'id_pengiriman_potong' : 'id_pengiriman_jahit';
        $kolom_penerima = ($jenis_penerima == 'pemotong') ? 'id_pemotong' : 'id_penjahit';
        $jenis_hasil = substr($jenis_penerima, 0, 4);
        $kolom_id_hasil = "id_hasil_" . $jenis_hasil;

        $sql2 = "SELECT h.*, t.tarif_per_unit
                FROM $tabel_hasil h
                JOIN $tabel_pengiriman p ON h.$kolom_relasi = p.$kolom_relasi
                JOIN tarif_upah t ON h.id_tarif = t.id_tarif
                WHERE p.$kolom_penerima = $id_penerima
                AND h.tanggal_selesai BETWEEN '$periode_awal' AND '$periode_akhir'
                AND NOT EXISTS (
                    SELECT 1 FROM detail_pembayaran_upah d 
                    WHERE d.id_hasil = h.$kolom_id_hasil
                    AND d.jenis_hasil = '$jenis_hasil'
                )";

        $result = $conn->query($sql2);
        $daftar_hasil = $result->fetch_all(MYSQLI_ASSOC);

        // 3. Simpan detail pembayaran
        foreach ($daftar_hasil as $hasil) {
            $sql3 = "INSERT INTO detail_pembayaran_upah
                    (id_pembayaran, id_hasil, jenis_hasil, jumlah_unit, tarif_per_unit, subtotal)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql3);
            $stmt->bind_param(
                "iisidd",
                $id_pembayaran,
                $hasil[$kolom_id_hasil],
                $jenis_hasil,
                $hasil['jumlah_' . ($jenis_penerima == 'pemotong' ? 'hasil' : 'produk_jadi')],
                $hasil['tarif_per_unit'],
                $hasil['total_upah']
            );
            $stmt->execute();
        }

        // 4. Update status pembayaran
        $sql4 = "UPDATE pembayaran_upah SET status = 'dibayar', tanggal_bayar = CURDATE() 
                WHERE id_pembayaran = $id_pembayaran";
        $conn->query($sql4);

        $conn->commit();
        $_SESSION['success'] = "Pembayaran upah berhasil dicatat dengan ID: $id_pembayaran";
        header("Location: pembayaran_upah.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Ambil data pemotong dan penjahit
$pemotong = query("SELECT * FROM pemotong ORDER BY nama_pemotong");
$penjahit = query("SELECT * FROM penjahit ORDER BY nama_penjahit");

// Ambil riwayat pembayaran
$riwayat_pembayaran = query("SELECT pu.*, 
                            CASE 
                                WHEN pu.jenis_penerima = 'pemotong' THEN p.nama_pemotong
                                ELSE j.nama_penjahit
                            END as nama_penerima
                            FROM pembayaran_upah pu
                            LEFT JOIN pemotong p ON pu.id_penerima = p.id_pemotong AND pu.jenis_penerima = 'pemotong'
                            LEFT JOIN penjahit j ON pu.id_penerima = j.id_penjahit AND pu.jenis_penerima = 'penjahit'
                            ORDER BY pu.tanggal_bayar DESC");
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h2>Pembayaran Upah</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Form Pembayaran Upah</h5>
            <form method="post">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jenis Penerima</label>
                        <select name="jenis_penerima" id="jenis_penerima" class="form-select" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="pemotong">Pemotong</option>
                            <option value="penjahit">Penjahit</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Penerima</label>
                        <select name="id_penerima" id="id_penerima" class="form-select" required>
                            <option value="">-- Pilih Penerima --</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Upah Belum Dibayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="total_upah" class="form-control" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Periode Awal</label>
                        <input type="date" name="periode_awal" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Periode Akhir</label>
                        <input type="date" name="periode_akhir" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="metode_pembayaran" class="form-select" required>
                            <option value="transfer">Transfer Bank</option>
                            <option value="tunai">Tunai</option>
                            <option value="e-wallet">E-Wallet</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="catatan" class="form-control">
                    </div>
                </div>

                <button type="submit" name="bayar" class="btn btn-primary">Proses Pembayaran</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Riwayat Pembayaran</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Penerima</th>
                            <th>Jenis</th>
                            <th>Periode</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat_pembayaran as $p): ?>
                            <tr>
                                <td><?= $p['id_pembayaran'] ?></td>
                                <td><?= dateIndo($p['tanggal_bayar']) ?></td>
                                <td><?= $p['nama_penerima'] ?></td>
                                <td><?= ucfirst($p['jenis_penerima']) ?></td>
                                <td><?= dateIndo($p['periode_awal']) ?> - <?= dateIndo($p['periode_akhir']) ?></td>
                                <td>Rp <?= number_format($p['total_upah'], 0, ',', '.') ?></td>
                                <td><?= ucfirst($p['metode_pembayaran']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $p['status'] == 'dibayar' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($p['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="detail_pembayaran.php?id=<?= $p['id_pembayaran'] ?>" class="btn btn-sm btn-info">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Update dropdown penerima berdasarkan jenis
    document.getElementById('jenis_penerima').addEventListener('change', function() {
        const jenis = this.value;
        const penerimaSelect = document.getElementById('id_penerima');

        penerimaSelect.innerHTML = '<option value="">-- Pilih Penerima --</option>';

        if (jenis === 'pemotong') {
            <?php foreach ($pemotong as $p): ?>
                penerimaSelect.innerHTML += `<option value="<?= $p['id_pemotong'] ?>"><?= $p['nama_pemotong'] ?></option>`;
            <?php endforeach; ?>
        } else if (jenis === 'penjahit') {
            <?php foreach ($penjahit as $p): ?>
                penerimaSelect.innerHTML += `<option value="<?= $p['id_penjahit'] ?>"><?= $p['nama_penjahit'] ?></option>`;
            <?php endforeach; ?>
        }

        // Reset total upah
        document.getElementById('total_upah').value = '';
    });

    // Hitung total upah saat penerima dipilih
    document.getElementById('id_penerima').addEventListener('change', function() {
        const idPenerima = this.value;
        const jenisPenerima = document.getElementById('jenis_penerima').value;

        if (idPenerima && jenisPenerima) {
            // AJAX request untuk mendapatkan total upah
            fetch('get_total_upah.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_penerima=${idPenerima}&jenis_penerima=${jenisPenerima}`
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('total_upah').value = data.total.toLocaleString('id-ID');
                });
        }
    });

    // Set default tanggal periode
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

        document.querySelector('input[name="periode_awal"]').valueAsDate = firstDay;
        document.querySelector('input[name="periode_akhir"]').valueAsDate = today;
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>