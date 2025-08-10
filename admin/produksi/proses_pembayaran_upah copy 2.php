<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pemotong = $_POST['id_pemotong'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $tanggal_pembayaran = $_POST['tanggal_pembayaran'];
    $keterangan = $_POST['keterangan'];
    $tgl_awal = $_POST['tgl_awal'] ?? '';
    $tgl_akhir = $_POST['tgl_akhir'] ?? '';

    // Ambil total upah yang belum dibayar
    $query_total = "
        SELECT SUM(h.total_upah) as total_upah
        FROM hasil_pemotongan h
        JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
        LEFT JOIN detail_pembayaran_upah d ON h.id_hasil_potong = d.id_hasil AND d.jenis_hasil = 'potong'
        WHERE pg.id_pemotong = $id_pemotong AND d.id_detail IS NULL
    ";

    if ($tgl_awal && $tgl_akhir) {
        $query_total .= " AND h.tanggal_selesai BETWEEN '$tgl_awal' AND '$tgl_akhir'";
    }

    $result_total = mysqli_query($koneksi, $query_total);
    $total_upah = mysqli_fetch_assoc($result_total)['total_upah'];

    if ($total_upah <= 0) {
        $_SESSION['error'] = "Tidak ada upah yang perlu dibayarkan untuk pemotong ini";
        header("Location: riwayat_hasil_pemotongan.php");
        exit;
    }

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Buat record pembayaran
        $query_pembayaran = "
            INSERT INTO pembayaran_upah 
            (id_penerima, jenis_penerima, total_upah, sisa_upah, metode_pembayaran, tanggal_pembayaran, keterangan, status) 
            VALUES 
            ($id_pemotong, 'pemotong', $total_upah, $total_upah, '$metode_pembayaran', '$tanggal_pembayaran', '$keterangan', 'belum_lunas')
        ";

        $result_pembayaran = mysqli_query($koneksi, $query_pembayaran);
        $id_pembayaran = mysqli_insert_id($koneksi);

        if (!$result_pembayaran || !$id_pembayaran) {
            throw new Exception("Gagal membuat record pembayaran");
        }

        // 2. Ambil semua hasil potong yang belum dibayar untuk pemotong ini
        $query_hasil = "
            SELECT h.id_hasil_potong
            FROM hasil_pemotongan h
            JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
            LEFT JOIN detail_pembayaran_upah d ON h.id_hasil_potong = d.id_hasil AND d.jenis_hasil = 'potong'
            WHERE pg.id_pemotong = $id_pemotong AND d.id_detail IS NULL
        ";

        if ($tgl_awal && $tgl_akhir) {
            $query_hasil .= " AND h.tanggal_selesai BETWEEN '$tgl_awal' AND '$tgl_akhir'";
        }

        $result_hasil = mysqli_query($koneksi, $query_hasil);

        if (!$result_hasil) {
            throw new Exception("Gagal mengambil data hasil pemotongan");
        }

        // 3. Buat detail pembayaran untuk setiap hasil potong
        while ($row = mysqli_fetch_assoc($result_hasil)) {
            $id_hasil = $row['id_hasil_potong'];
            $query_detail = "
                INSERT INTO detail_pembayaran_upah 
                (id_pembayaran, id_hasil, jenis_hasil) 
                VALUES 
                ($id_pembayaran, $id_hasil, 'potong')
            ";

            $result_detail = mysqli_query($koneksi, $query_detail);

            if (!$result_detail) {
                throw new Exception("Gagal membuat detail pembayaran");
            }
        }

        // Commit transaksi jika semua berhasil
        mysqli_commit($koneksi);

        $_SESSION['success'] = "Pembayaran berhasil dibuat. Silahkan input cicilan pembayaran.";
        header("Location: detail_pembayaran.php?id=$id_pembayaran");
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
