<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pemotong = $_POST['id_pemotong'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $tanggal_pembayaran = $_POST['tanggal_pembayaran'];
    $keterangan = $_POST['keterangan'];
    $tgl_awal = $_POST['tgl_awal'] ?? null;
    $tgl_akhir = $_POST['tgl_akhir'] ?? null;

    // Ambil semua hasil pemotongan yang belum dibayar untuk pemotong ini
    $where = [];
    $where[] = "pg.id_pemotong = $id_pemotong";
    $where[] = "d.id_detail IS NULL";

    if ($tgl_awal && $tgl_akhir) {
        $where[] = "h.tanggal_selesai BETWEEN '$tgl_awal' AND '$tgl_akhir'";
    }

    $where_sql = implode(' AND ', $where);

    $query = "
        SELECT h.id_hasil_potong, h.total_upah
        FROM hasil_pemotongan h
        JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
        LEFT JOIN detail_pembayaran_upah d ON h.id_hasil_potong = d.id_hasil AND d.jenis_hasil = 'potong'
        WHERE $where_sql
    ";

    $hasil_belum_dibayar = query($query);

    if (empty($hasil_belum_dibayar)) {
        $_SESSION['error'] = "Tidak ada upah yang belum dibayarkan untuk pemotong ini";
        header("Location: riwayat_hasil_pemotongan.php");
        exit;
    }

    // Hitung total upah yang belum dibayar
    $total_upah = array_sum(array_column($hasil_belum_dibayar, 'total_upah'));

    // Mulai transaksi database
    $conn->begin_transaction();

    try {
        // 1. Buat record pembayaran
        $insert_pembayaran = "
            INSERT INTO pembayaran_upah (
                id_penerima, 
                jenis_penerima, 
                total_upah, 
                sisa_upah,
                metode_pembayaran, 
                tanggal_pembayaran, 
                keterangan, 
                status
            ) VALUES (
                $id_pemotong, 
                'pemotong', 
                $total_upah,
                $total_upah,
                '$metode_pembayaran', 
                '$tanggal_pembayaran', 
                '$keterangan', 
                'cicilan'
            )
        ";

        if (!$conn->query($insert_pembayaran)) {
            throw new Exception("Gagal menyimpan data pembayaran: " . $conn->error);
        }

        $id_pembayaran = $conn->insert_id;

        // 2. Simpan detail pembayaran (hasil-hasil yang dibayar)
        foreach ($hasil_belum_dibayar as $hasil) {
            $insert_detail = "
                INSERT INTO detail_pembayaran_upah (
                    id_pembayaran, 
                    id_hasil, 
                    jenis_hasil, 
                    jumlah_upah
                ) VALUES (
                    $id_pembayaran, 
                    {$hasil['id_hasil_potong']}, 
                    'potong', 
                    {$hasil['total_upah']}
                )
            ";

            if (!$conn->query($insert_detail)) {
                throw new Exception("Gagal menyimpan detail pembayaran: " . $conn->error);
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Pembayaran upah berhasil dicatat. Silahkan tambahkan cicilan pembayaran.";
        header("Location: detail_pembayaran.php?id=$id_pembayaran");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: riwayat_hasil_pemotongan.php");
        exit;
    }
} else {
    header("Location: riwayat_hasil_pemotongan.php");
    exit;
}
