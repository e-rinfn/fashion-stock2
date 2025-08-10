<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pembayaran = $_POST['id_pembayaran'];
    $alasan = $_POST['alasan'];

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Hapus semua cicilan terkait pembayaran ini
        $query_hapus_cicilan = "DELETE FROM cicilan_upah WHERE id_pembayaran = $id_pembayaran";
        $result_hapus_cicilan = mysqli_query($koneksi, $query_hapus_cicilan);

        if (!$result_hapus_cicilan) {
            throw new Exception("Gagal menghapus cicilan");
        }

        // 2. Hapus detail pembayaran
        $query_hapus_detail = "DELETE FROM detail_pembayaran_upah WHERE id_pembayaran = $id_pembayaran";
        $result_hapus_detail = mysqli_query($koneksi, $query_hapus_detail);

        if (!$result_hapus_detail) {
            throw new Exception("Gagal menghapus detail pembayaran");
        }

        // 3. Hapus pembayaran
        $query_hapus_pembayaran = "DELETE FROM pembayaran_upah WHERE id_pembayaran = $id_pembayaran";
        $result_hapus_pembayaran = mysqli_query($koneksi, $query_hapus_pembayaran);

        if (!$result_hapus_pembayaran) {
            throw new Exception("Gagal menghapus pembayaran");
        }

        // Commit transaksi
        mysqli_commit($koneksi);

        $_SESSION['success'] = "Pembayaran berhasil dibatalkan";
        header("Location: riwayat_hasil_pemotongan.php");
        exit;
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($koneksi);
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: riwayat_hasil_pemotongan.php");
        exit;
    }
} else {
    header("Location: riwayat_hasil_pemotongan.php");
    exit;
}
