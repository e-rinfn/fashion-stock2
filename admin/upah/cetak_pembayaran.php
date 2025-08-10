<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

$id_pembayaran = intval($_GET['id']);

// Ambil data pembayaran (sama seperti detail_pembayaran.php)
// ...

?>
<!DOCTYPE html>
<html>

<head>
    <title>Cetak Bukti Pembayaran Upah #<?= $pembayaran['id_pembayaran'] ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .info {
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 50px;
        }

        .signature {
            float: right;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>BUKTI PEMBAYARAN UPAH</h2>
        <h3>No: #<?= $pembayaran['id_pembayaran'] ?></h3>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="20%">Penerima</td>
                <td width="30%">: <?= $pembayaran['nama_penerima'] ?></td>
                <td width="20%">Jenis</td>
                <td width="30%">: <?= ucfirst($pembayaran['jenis_penerima']) ?></td>
            </tr>
            <tr>
                <td>Periode</td>
                <td>: <?= dateIndo($pembayaran['periode_awal']) ?> - <?= dateIndo($pembayaran['periode_akhir']) ?></td>
                <td>Tanggal Bayar</td>
                <td>: <?= dateIndo($pembayaran['tanggal_bayar']) ?></td>
            </tr>
            <tr>
                <td>Metode</td>
                <td>: <?= ucfirst($pembayaran['metode_pembayaran']) ?></td>
                <td>Total</td>
                <td>: Rp <?= number_format($pembayaran['total_upah'], 0, ',', '.') ?></td>
            </tr>
        </table>
    </div>

    <div class="detail">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Tarif</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($detail_pembayaran as $d): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= dateIndo($d['tanggal_hasil']) ?></td>
                        <td><?= ucfirst($d['jenis_hasil']) ?></td>
                        <td><?= number_format($d['jumlah_unit']) ?> pcs</td>
                        <td class="text-right">Rp <?= number_format($d['tarif_per_unit'], 0, ',', '.') ?></td>
                        <td class="text-right">Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="5" class="text-right"><strong>TOTAL</strong></td>
                    <td class="text-right"><strong>Rp <?= number_format($pembayaran['total_upah'], 0, ',', '.') ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div class="signature">
            <p>Mengetahui,</p>
            <br><br><br>
            <p>(___________________)</p>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>

</html>