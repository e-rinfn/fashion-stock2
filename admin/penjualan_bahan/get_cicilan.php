<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

header('Content-Type: application/json');

$id_cicilan_penjualan_bahan = isset($_GET['id']) ? intval($_GET['id']) : 0;

$cicilan_penjualan_bahan = query("SELECT * FROM cicilan_penjualan_bahan WHERE id_cicilan_penjualan_bahan = $id_cicilan_penjualan_bahan")[0] ?? null;

if ($cicilan_penjualan_bahan) {
    echo json_encode([
        'id_cicilan_penjualan_bahan' => $cicilan_penjualan_bahan['id_cicilan_penjualan_bahan'],
        'jumlah_cicilan_penjualan_bahan' => $cicilan_penjualan_bahan['jumlah_cicilan_penjualan_bahan'],
        'tanggal_bayar' => date('Y-m-d', strtotime($cicilan_penjualan_bahan['tanggal_bayar'])),
        'metode_pembayaran' => $cicilan_penjualan_bahan['metode_pembayaran']
    ]);
} else {
    echo json_encode(null);
}
