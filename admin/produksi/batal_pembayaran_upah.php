<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_pembayaran'])) {
    $id_pembayaran = $_POST['id_pembayaran'];
    $alasan = $_POST['alasan'];

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // 1. Ambil detail pembayaran untuk dikembalikan
        $detail_pembayaran = query("
            SELECT id_hasil, jenis_hasil, jumlah_upah 
            FROM detail_pembayaran_upah 
            WHERE id_pembayaran = $id_pembayaran
        ");

        // 2. Hapus cicilan terkait
        if (!$conn->query("DELETE FROM cicilan_upah WHERE id_pembayaran = $id_pembayaran")) {
            throw new Exception("Gagal menghapus cicilan");
        }

        // 3. Hapus detail pembayaran
        if (!$conn->query("DELETE FROM detail_pembayaran_upah WHERE id_pembayaran = $id_pembayaran")) {
            throw new Exception("Gagal menghapus detail pembayaran");
        }

        // 4. Hapus pembayaran
        if (!$conn->query("DELETE FROM pembayaran_upah WHERE id_pembayaran = $id_pembayaran")) {
            throw new Exception("Gagal menghapus data pembayaran");
        }

        $conn->commit();
        $_SESSION['success'] = "Pembayaran berhasil dibatalkan";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: riwayat_hasil_pemotongan.php");
    exit;
} else {
    header("Location: riwayat_hasil_pemotongan.php");
    exit;
}
