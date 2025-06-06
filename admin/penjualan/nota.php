<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
require_once '../../vendor/autoload.php';

$id_penjualan = isset($_GET['id']) ? intval($_GET['id']) : 0;

$penjualan = query("SELECT p.*, r.nama_reseller, r.alamat as alamat_reseller 
                    FROM penjualan p
                    JOIN reseller r ON p.id_reseller = r.id_reseller
                    WHERE p.id_penjualan = $id_penjualan")[0] ?? null;

if (!$penjualan) {
    die('Data penjualan tidak ditemukan.');
}

$detail = query("SELECT d.*, pr.nama_produk 
                FROM detail_penjualan d
                JOIN produk pr ON d.id_produk = pr.id_produk
                WHERE d.id_penjualan = $id_penjualan");

$total_cicilan = 0;
if ($penjualan['status_pembayaran'] == 'cicilan') {
    $cicilan = query("SELECT SUM(jumlah_cicilan) as total FROM cicilan WHERE id_penjualan = $id_penjualan AND status = 'lunas'")[0];
    $total_cicilan = $cicilan['total'] ?? 0;
}

$pdf = new TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
$pdf->SetTitle("Nota Penjualan #$id_penjualan");
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 5, 'IRVEENA', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Jl. Contoh No.123, Tasikmalaya - Telp: 0878-3456-7080', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, "No. Nota: #$id_penjualan", 0, 1);
$pdf->Cell(0, 6, 'Tanggal: ' . date('d/m/Y H:i', strtotime($penjualan['tanggal_penjualan'])), 0, 1);
$pdf->Cell(0, 6, 'Status: ' . ucfirst($penjualan['status_pembayaran']), 0, 1);
if ($penjualan['status_pembayaran'] == 'cicilan') {
    $pdf->Cell(0, 6, 'Dibayar: ' . formatRupiah($total_cicilan) . ' dari ' . formatRupiah($penjualan['total_harga']), 0, 1);
}
$pdf->Ln(2);

$pdf->Cell(0, 6, 'Reseller: ' . $penjualan['nama_reseller'], 0, 1);
$pdf->MultiCell(0, 6, 'Alamat: ' . $penjualan['alamat_reseller'], 0, 1);
$pdf->Ln(4);

// Tabel Produk
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(10, 7, 'No', 1);
$pdf->Cell(50, 7, 'Produk', 1);
$pdf->Cell(25, 7, 'Harga', 1, 0, 'R');
$pdf->Cell(15, 7, 'Qty', 1, 0, 'C');
$pdf->Cell(30, 7, 'Subtotal', 1, 1, 'R');

$pdf->SetFont('helvetica', '', 10);
foreach ($detail as $i => $d) {
    $pdf->Cell(10, 7, $i + 1, 1);
    $pdf->Cell(50, 7, $d['nama_produk'], 1);
    $pdf->Cell(25, 7, formatRupiah($d['harga_satuan']), 1, 0, 'R');
    $pdf->Cell(15, 7, $d['jumlah'], 1, 0, 'C');
    $pdf->Cell(30, 7, formatRupiah($d['subtotal']), 1, 1, 'R');
}

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(100, 7, 'Total', 1);
$pdf->Cell(30, 7, formatRupiah($penjualan['total_harga']), 1, 1, 'R');
$pdf->Ln(4);

// Footer
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 6, 'Metode Pembayaran: ' . ucfirst($penjualan['metode_pembayaran']), 0, 1);
$pdf->Cell(0, 6, 'Terima kasih telah berbelanja!', 0, 1);

$pdf->Ln(10);
$pdf->Cell(0, 6, 'Hormat Kami,', 0, 1, 'R');
$pdf->Ln(15);
$pdf->Cell(0, 6, '(____________________)', 0, 0, 'R');

// Output PDF ke browser
$pdf->Output('nota_penjualan_' . $id_penjualan . '.pdf', 'I');
exit();
