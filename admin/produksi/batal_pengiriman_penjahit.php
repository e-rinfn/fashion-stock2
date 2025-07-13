<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

$conn->begin_transaction();

try {
    // Ambil pengiriman terakhir dengan status 'dikirim'
    $pengiriman = query("SELECT * FROM pengiriman_penjahit 
                         WHERE status = 'dikirim' 
                         ORDER BY id_pengiriman_jahit DESC 
                         LIMIT 1")[0] ?? null;

    if (!$pengiriman) {
        throw new Exception("Tidak ada pengiriman yang bisa dibatalkan");
    }

    // Kembalikan stok bahan mentah ke hasil pemotongan (bisa dibagi 1/2 jika perlu)
    $jumlah_kembali = $pengiriman['jumlah_bahan_mentah']; // atau bagi 2 jika ada ketentuan
    $conn->query("UPDATE hasil_pemotongan 
                  SET jumlah_hasil = jumlah_hasil  
                  WHERE id_hasil_potong = {$pengiriman['id_hasil_potong']}");

    // Hapus data pengiriman penjahit
    $conn->query("DELETE FROM pengiriman_penjahit 
                  WHERE id_pengiriman_jahit = {$pengiriman['id_pengiriman_jahit']}");

    $conn->commit();
    $_SESSION['success'] = "Pengiriman terakhir berhasil dibatalkan.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Gagal membatalkan pengiriman: " . $e->getMessage();
}

header("Location: pengiriman_penjahit.php");
exit();
