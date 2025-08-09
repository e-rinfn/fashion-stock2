<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Dapatkan data pengiriman untuk mengembalikan stok
$pengiriman = query("SELECT id_bahan, jumlah_bahan 
                    FROM pengiriman_pemotong 
                    WHERE id_pengiriman_potong = $id AND status = 'dikirim'")[0] ?? null;

if ($pengiriman) {

    // Cek apakah data masih dipakai di hasil_pemotongan
    $check = $conn->query("SELECT 1 FROM hasil_pemotongan WHERE id_pengiriman_potong = $id LIMIT 1");
    if ($check && $check->num_rows > 0) {
        $_SESSION['error'] = "Data tidak bisa dihapus karena masih digunakan di hasil pemotongan.";
        header("Location: pengiriman_pemotong.php");
        exit();
    }

    // Kembalikan stok
    $conn->query("UPDATE bahan_baku 
                 SET jumlah_stok = jumlah_stok + {$pengiriman['jumlah_bahan']} 
                 WHERE id_bahan = {$pengiriman['id_bahan']}");

    // Hapus pengiriman
    if ($conn->query("DELETE FROM pengiriman_pemotong WHERE id_pengiriman_potong = $id")) {
        $_SESSION['success'] = "Pengiriman berhasil dibatalkan dan stok dikembalikan";
    } else {
        $_SESSION['error'] = "Gagal membatalkan pengiriman: " . $conn->error;
    }
} else {
    $_SESSION['error'] = "Data pengiriman tidak ditemukan atau sudah diproses";
}


header("Location: pengiriman_pemotong.php");
exit();
