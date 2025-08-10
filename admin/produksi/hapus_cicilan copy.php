<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cicilan = $_POST['id_cicilan'];
    $id_pembayaran = $_POST['id_pembayaran'];

    // Mulai transaksi
    mysqli_begin_transaction($conn);

    try {
        // 1. Ambil data cicilan yang akan dihapus
        $query_cicilan = "SELECT * FROM cicilan_upah WHERE id_cicilan = $id_cicilan";
        $result_cicilan = mysqli_query($conn, $query_cicilan);
        $cicilan = mysqli_fetch_assoc($result_cicilan);

        if (!$cicilan) {
            throw new Exception("Data cicilan tidak ditemukan");
        }

        // 2. Hapus cicilan
        $query_hapus = "DELETE FROM cicilan_upah WHERE id_cicilan = $id_cicilan";
        $result_hapus = mysqli_query($conn, $query_hapus);

        if (!$result_hapus) {
            throw new Exception("Gagal menghapus cicilan");
        }

        // 3. Update sisa upah di pembayaran
        $query_update = "
            UPDATE pembayaran_upah 
            SET sisa_upah = sisa_upah + {$cicilan['jumlah_cicilan']},
                status = CASE 
                    WHEN (total_upah - (SELECT IFNULL(SUM(jumlah_cicilan), 0) FROM cicilan_upah WHERE id_pembayaran = $id_pembayaran)) <= 0 
                    THEN 'dibayar' 
                    ELSE 'terhitung' 
                END
            WHERE id_pembayaran = $id_pembayaran
        ";

        $result_update = mysqli_query($conn, $query_update);

        if (!$result_update) {
            throw new Exception("Gagal memperbarui sisa upah");
        }

        // Commit transaksi
        mysqli_commit($conn);

        $_SESSION['success'] = "Cicilan berhasil dihapus";
        header("Location: detail_pembayaran.php?id=$id_pembayaran");
        exit;
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: detail_pembayaran.php?id=$id_pembayaran");
        exit;
    }
} else {
    header("Location: riwayat_hasil_pemotongan.php");
    exit;
}
