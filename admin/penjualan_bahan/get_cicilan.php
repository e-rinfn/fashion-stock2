<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

header('Content-Type: application/json');

$id_cicilan = isset($_GET['id']) ? intval($_GET['id']) : 0;

$cicilan = query("SELECT * FROM cicilan WHERE id_cicilan = $id_cicilan")[0] ?? null;

if ($cicilan) {
    echo json_encode([
        'id_cicilan' => $cicilan['id_cicilan'],
        'jumlah_cicilan' => $cicilan['jumlah_cicilan'],
        'tanggal_bayar' => date('Y-m-d', strtotime($cicilan['tanggal_bayar'])),
        'metode_pembayaran' => $cicilan['metode_pembayaran']
    ]);
} else {
    echo json_encode(null);
}
