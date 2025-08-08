<?php
require_once '../../vendor/autoload.php';
require_once '../../config/database.php';
require_once '../../config/functions.php';

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

$sisa_hutang = $cicilan['total_harga'] - $cicilan['total_dibayar'];

// Inisialisasi TCPDF
$pdf = new TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// Font
$pdf->SetFont('helvetica', '', 10);

// Header
$pdf->Cell(0, 6, 'NAMA PERUSAHAAN', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, 'Jl. Contoh No. 123, Kota Tasikmalaya', 0, 1, 'C');
$pdf->Cell(0, 5, 'Telp: 0812-3456-7890', 0, 1, 'C');
$pdf->Ln(5);

// Judul
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, 'NOTA PEMBAYARAN CICILAN', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, 'No Cicilan: ' . '#' . $id_cicilan . ' | Tanggal: ' . dateIndo($cicilan['tanggal_bayar']), 0, 1, 'C');
$pdf->Ln(5);

// Informasi Penjualan dan Reseller
$pdf->SetFont('helvetica', '',  9);
$pdf->Cell(40, 5, 'No. Penjualan', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(60, 5, '# ' . $cicilan['id_penjualan'], 0, 1);

$pdf->Cell(40, 5, 'Tanggal Penjualan', 0, 0,);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(60, 5, dateIndo(($cicilan['tanggal_penjualan'])), 0, 1);

$pdf->Cell(40, 5, 'Reseller', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(60, 5, $cicilan['nama_reseller'], 0, 1);

$pdf->Cell(40, 5, 'Kontak', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(60, 5, $cicilan['kontak_reseller'], 0, 1);
$pdf->Ln(5);

// Pembayaran
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(90, 6, 'Keterangan', 1, 0);
$pdf->Cell(40, 6, 'Jumlah', 1, 1, 'R');

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(90, 6, 'Pembayaran Cicilan (' . ucfirst($cicilan['metode_pembayaran']) . ')', 1, 0);
$pdf->Cell(40, 6, formatRupiah($cicilan['jumlah_cicilan']), 1, 1, 'R');

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(90, 6, 'Total Pembayaran', 1, 0);
$pdf->Cell(40, 6, formatRupiah($cicilan['jumlah_cicilan']), 1, 1, 'R');
$pdf->Ln(5);

// Informasi Tambahan
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(60, 5, 'Total Harga Penjualan', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(40, 5, formatRupiah($cicilan['total_harga']), 0, 1);

$pdf->Cell(60, 5, 'Total Dibayar', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(40, 5, formatRupiah($cicilan['total_dibayar']), 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(60, 5, 'Sisa Hutang', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(40, 5, formatRupiah($sisa_hutang), 0, 1);
$pdf->Ln(5);

// Keterangan
$pdf->SetFont('helvetica', '', 9);
$pdf->MultiCell(0, 5, 'Keterangan: Pembayaran ini merupakan bagian dari penjualan #' . $cicilan['id_penjualan'], 0, 'C');
$pdf->Ln(10);

// Output
$pdf->Output('nota_cicilan_' . $id_cicilan . '.pdf', 'I');
