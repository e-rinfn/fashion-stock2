<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

$conn->begin_transaction();

try {
    // Ambil data hasil penjahitan terakhir
    $hasil = query("SELECT * FROM hasil_penjahitan 
                    ORDER BY id_hasil_jahit DESC LIMIT 1")[0] ?? null;

    if (!$hasil) {
        throw new Exception("Tidak ada hasil penjahitan yang bisa dibatalkan.");
    }

    // 1. Kembalikan status pengiriman ke 'dikirim' dan hapus tanggal diterima
    $conn->query("UPDATE pengiriman_penjahit 
                  SET status = 'dikirim', tanggal_diterima = NULL 
                  WHERE id_pengiriman_jahit = {$hasil['id_pengiriman_jahit']}");

    // 2. Kurangi stok produk yang sudah ditambahkan
    $conn->query("UPDATE produk 
                  SET stok = stok - {$hasil['jumlah_produk_jadi']} 
                  WHERE id_produk = {$hasil['id_produk']}");

    // 3. Hapus hasil penjahitan
    $conn->query("DELETE FROM hasil_penjahitan 
                  WHERE id_hasil_jahit = {$hasil['id_hasil_jahit']}");

    $conn->commit();
    $_SESSION['success'] = "Hasil jahit terakhir berhasil dibatalkan.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Gagal membatalkan hasil jahit: " . $e->getMessage();
}

header("Location: hasil_penjahitan.php");
exit;
