<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cicilan'])) {
    $id_cicilan = $_POST['id_cicilan'];
    $id_pembayaran = $_POST['id_pembayaran'];

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // 1. Ambil data cicilan yang akan dihapus
        $cicilan = query("SELECT jumlah_cicilan FROM cicilan_upah WHERE id_cicilan = $id_cicilan")[0];

        if (!$cicilan) {
            throw new Exception("Data cicilan tidak ditemukan");
        }

        $jumlah_cicilan = $cicilan['jumlah_cicilan'];

        // 2. Hapus cicilan
        if (!$conn->query("DELETE FROM cicilan_upah WHERE id_cicilan = $id_cicilan")) {
            throw new Exception("Gagal menghapus cicilan");
        }

        // 3. Update sisa upah di pembayaran
        $update_sisa = "
            UPDATE pembayaran_upah 
            SET sisa_upah = sisa_upah + $jumlah_cicilan,
                status = IF(sisa_upah + $jumlah_cicilan = total_upah, 'cicilan', status)
            WHERE id_pembayaran = $id_pembayaran
        ";

        if (!$conn->query($update_sisa)) {
            throw new Exception("Gagal mengupdate sisa upah");
        }

        $conn->commit();
        $_SESSION['success'] = "Cicilan berhasil dihapus";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: detail_pembayaran.php?id=$id_pembayaran");
    exit;
} else {
    header("Location: riwayat_hasil_pemotongan.php");
    exit;
}
