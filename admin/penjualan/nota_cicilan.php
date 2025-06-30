<?php
require_once '../../vendor/autoload.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

$id_cicilan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data cicilan
$cicilan = query("
    SELECT c.*, p.tanggal_penjualan, p.total_harga, 
           r.nama_reseller, r.alamat as alamat_reseller, r.kontak as kontak_reseller,
           (SELECT SUM(jumlah_cicilan) FROM cicilan WHERE id_penjualan = c.id_penjualan) as total_dibayar
    FROM cicilan c
    JOIN penjualan p ON c.id_penjualan = p.id_penjualan
    JOIN reseller r ON p.id_reseller = r.id_reseller
    WHERE c.id_cicilan = $id_cicilan
")[0] ?? null;

if (!$cicilan) {
    die("Data cicilan tidak ditemukan");
}

// Hitung sisa hutang
$sisa_hutang = $cicilan['total_harga'] - $cicilan['total_dibayar'];

// Create new PDF document
$pdf = new TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Sistem Mukena');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Nota Cicilan #' . $id_cicilan);
$pdf->SetSubject('Nota Pembayaran Cicilan');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(10, 10, 10);

// Add a page
$pdf->AddPage();

// Content
$html = '
<style>
    .header {
        text-align: center;
        margin-bottom: 10px;
    }
    .title {
        font-size: 16px;
        font-weight: bold;
    }
    .subtitle {
        font-size: 14px;
    }
    .info {
        font-size: 10px;
        margin-bottom: 10px;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
        margin-bottom: 10px;
    }
    .table th {
        background-color: #f2f2f2;
        border: 1px solid #ddd;
        padding: 5px;
        text-align: left;
    }
    .table td {
        border: 1px solid #ddd;
        padding: 5px;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .footer {
        margin-top: 15px;
        font-size: 10px;
    }
    .signature {
        width: 70mm;
        margin-top: 20px;
        text-align: center;
    }
</style>

<div class="header">
    <div class="title">BISNIS FASHION MUKENA</div>
    <div class="subtitle">Jl. Contoh No. 123, Kota Anda</div>
    <div class="subtitle">Telp: 0812-3456-7890</div>
</div>

<div class="header">
    <div class="title">NOTA PEMBAYARAN CICILAN</div>
    <div class="info">No: ' . $id_cicilan . ' | Tanggal: ' . date('d/m/Y', strtotime($cicilan['tanggal_bayar'])) . '</div>
</div>

<table class="info">
    <tr>
        <td width="50%">
            <strong>No. Penjualan:</strong> ' . $cicilan['id_penjualan'] . '<br>
            <strong>Tanggal Penjualan:</strong> ' . date('d/m/Y', strtotime($cicilan['tanggal_penjualan'])) . '
        </td>
        <td width="50%">
            <strong>Reseller:</strong> ' . $cicilan['nama_reseller'] . '<br>
            <strong>Kontak:</strong> ' . $cicilan['kontak_reseller'] . '
        </td>
    </tr>
</table>

<table class="table">
    <thead>
        <tr>
            <th width="70%">Deskripsi</th>
            <th width="30%" class="text-right">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Pembayaran Cicilan (' . ucfirst($cicilan['metode_pembayaran']) . ')</td>
            <td class="text-right">' . formatRupiah($cicilan['jumlah_cicilan']) . '</td>
        </tr>
        <tr>
            <td><strong>Total Pembayaran</strong></td>
            <td class="text-right"><strong>' . formatRupiah($cicilan['jumlah_cicilan']) . '</strong></td>
        </tr>
    </tbody>
</table>

<div class="info">
    <p><strong>Informasi Pembayaran:</strong></p>
    <table>
        <tr>
            <td width="50%">Total Harga Penjualan:</td>
            <td width="50%">' . formatRupiah($cicilan['total_harga']) . '</td>
        </tr>
        <tr>
            <td>Total Dibayar:</td>
            <td>' . formatRupiah($cicilan['total_dibayar']) . '</td>
        </tr>
        <tr>
            <td><strong>Sisa Hutang:</strong></td>
            <td><strong>' . formatRupiah($sisa_hutang) . '</strong></td>
        </tr>
    </table>
</div>

<div style="margin-top: 10px; font-size: 9px;">
    <p>Keterangan: Pembayaran ini merupakan bagian dari penjualan #' . $cicilan['id_penjualan'] . '</p>
</div>

<div style="display: flex; justify-content: space-between; margin-top: 20px;">
    <div class="signature">
        <div style="border-top: 1px solid #000; width: 70mm; padding-top: 5px;">
            Hormat Kami,
        </div>
    </div>
    <div class="signature">
        <div style="border-top: 1px solid #000; width: 70mm; padding-top: 5px;">
            Penerima,
        </div>
    </div>
</div>';

// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// Close and output PDF document
$pdf->Output('nota_cicilan_' . $id_cicilan . '.pdf', 'I');
