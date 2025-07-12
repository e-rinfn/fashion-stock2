<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

header('Content-Type: application/json');

$id_bahan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cek relasi dengan tabel lain
$cek_pengiriman = query("SELECT id_pengiriman_potong FROM pengiriman_pemotong WHERE id_bahan = $id_bahan LIMIT 1");
$cek_pembelian = query("SELECT id_pembelian FROM pembelian_bahan WHERE id_bahan = $id_bahan LIMIT 1");

if ($cek_pengiriman || $cek_pembelian) {
    echo json_encode([
        'can_delete' => false,
        'message' => 'Bahan baku tidak dapat dihapus karena masih digunakan dalam data pengiriman atau pembelian!'
    ]);
} else {
    echo json_encode([
        'can_delete' => true,
        'message' => ''
    ]);
}
