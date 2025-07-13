<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

$id = query("SELECT id_hasil_potong FROM hasil_pemotongan ORDER BY id_hasil_potong DESC LIMIT 1")[0]['id_hasil_potong'] ?? null;

if ($id) {
    $conn->begin_transaction();

    try {
        // Ambil data hasil potong
        $data = query("SELECT * FROM hasil_pemotongan WHERE id_hasil_potong = $id")[0] ?? null;
        if (!$data) throw new Exception("Data tidak ditemukan");

        // Kembalikan status pengiriman ke 'dikirim'
        $conn->query("UPDATE pengiriman_pemotong SET status = 'dikirim', tanggal_diterima = NULL 
                      WHERE id_pengiriman_potong = {$data['id_pengiriman_potong']}");

        // Hapus hasil pemotongan
        $conn->query("DELETE FROM hasil_pemotongan WHERE id_hasil_potong = $id");

        $conn->commit();
        $_SESSION['success'] = "Hasil pemotongan terakhir berhasil dibatalkan";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Gagal membatalkan hasil: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Tidak ada data hasil pemotongan untuk dibatalkan";
}

header("Location: hasil_pemotongan.php");
exit();
