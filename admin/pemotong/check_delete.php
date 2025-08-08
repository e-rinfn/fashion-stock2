<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

header('Content-Type: application/json');

$id_pemotong = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_pemotong <= 0) {
    echo json_encode([
        'can_delete' => false,
        'message' => 'ID pemotong tidak valid'
    ]);
    exit;
}

// Cek apakah pemotong digunakan di tabel pengiriman_pemotong
$cek_pengiriman = query("SELECT id_pengiriman_potong FROM pengiriman_pemotong WHERE id_pemotong = $id_pemotong LIMIT 1");

if ($cek_pengiriman) {
    echo json_encode([
        'can_delete' => false,
        'message' => 'Pemotong tidak dapat dihapus karena masih digunakan dalam data pengiriman!'
    ]);
} else {
    echo json_encode([
        'can_delete' => true,
        'message' => ''
    ]);
}
