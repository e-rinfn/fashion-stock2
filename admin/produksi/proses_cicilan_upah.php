<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pembayaran = $_POST['id_pembayaran'];
    $jumlah_cicilan = $_POST['jumlah_cicilan'];
    $tanggal_cicilan = $_POST['tanggal_cicilan'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $keterangan = $_POST['keterangan'];

    // Validasi input
    if (empty($id_pembayaran) || empty($jumlah_cicilan) || empty($tanggal_cicilan) || empty($metode_pembayaran)) {
        $_SESSION['error'] = "Semua field wajib diisi!";
        header("Location: detail_pembayaran.php?id=" . $id_pembayaran);
        exit;
    }

    try {
        $conn->beginTransaction();

        // 1. Dapatkan data pembayaran
        $pembayaran = query("SELECT * FROM pembayaran_upah WHERE id_pembayaran = ?", [$id_pembayaran]);

        if (empty($pembayaran)) {
            $_SESSION['error'] = "Data pembayaran tidak ditemukan!";
            header("Location: riwayat_hasil_pemotongan.php");
            exit;
        }

        $pembayaran = $pembayaran[0];

        // 2. Validasi jumlah cicilan
        if ($jumlah_cicilan <= 0) {
            $_SESSION['error'] = "Jumlah cicilan harus lebih dari 0!";
            header("Location: detail_pembayaran.php?id=" . $id_pembayaran);
            exit;
        }

        if ($jumlah_cicilan > $pembayaran['sisa_upah']) {
            $_SESSION['error'] = "Jumlah cicilan tidak boleh melebihi sisa upah!";
            header("Location: detail_pembayaran.php?id=" . $id_pembayaran);
            exit;
        }

        // 3. Tambahkan cicilan
        $query_cicilan = "
            INSERT INTO cicilan_upah 
            (id_pembayaran, jumlah_cicilan, tanggal_cicilan, metode_pembayaran, keterangan) 
            VALUES (?, ?, ?, ?, ?)
        ";

        $stmt_cicilan = $conn->prepare($query_cicilan);
        $stmt_cicilan->execute([
            $id_pembayaran,
            $jumlah_cicilan,
            $tanggal_cicilan,
            $metode_pembayaran,
            $keterangan
        ]);

        // 4. Update sisa upah di tabel pembayaran
        $sisa_upah_baru = $pembayaran['sisa_upah'] - $jumlah_cicilan;
        $status_baru = $sisa_upah_baru <= 0 ? 'dibayar' : 'cicilan';

        $query_update = "
            UPDATE pembayaran_upah 
            SET sisa_upah = ?, status = ?
            WHERE id_pembayaran = ?
        ";

        $stmt_update = $conn->prepare($query_update);
        $stmt_update->execute([$sisa_upah_baru, $status_baru, $id_pembayaran]);

        // 5. Jika sudah lunas, tambahkan detail pembayaran untuk semua hasil yang belum dibayar
        if ($status_baru === 'dibayar') {
            // Dapatkan semua hasil potong yang belum dibayar untuk pemotong ini
            $query_hasil = "
                SELECT h.id_hasil_potong, h.total_upah
                FROM hasil_pemotongan h
                JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
                LEFT JOIN detail_pembayaran_upah d ON h.id_hasil_potong = d.id_hasil AND d.jenis_hasil = 'potong'
                WHERE pg.id_pemotong = ? AND d.id_detail IS NULL
            ";

            $stmt_hasil = $conn->prepare($query_hasil);
            $stmt_hasil->execute([$pembayaran['id_penerima']]);
            $hasil_belum_dibayar = $stmt_hasil->fetchAll(PDO::FETCH_ASSOC);

            // Buat detail untuk semua hasil potong
            foreach ($hasil_belum_dibayar as $hasil) {
                $query_detail = "
                    INSERT INTO detail_pembayaran_upah 
                    (id_pembayaran, id_hasil, jenis_hasil, jumlah) 
                    VALUES (?, ?, 'potong', ?)
                ";
                $stmt_detail = $conn->prepare($query_detail);
                $stmt_detail->execute([$id_pembayaran, $hasil['id_hasil_potong'], $hasil['total_upah']]);
            }
        }

        $conn->commit();

        $_SESSION['success'] = "Cicilan berhasil ditambahkan!";
        header("Location: detail_pembayaran.php?id=" . $id_pembayaran);
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Gagal menambahkan cicilan: " . $e->getMessage();
        header("Location: detail_pembayaran.php?id=" . $id_pembayaran);
        exit;
    }
} else {
    header("Location: riwayat_hasil_pemotongan.php");
    exit;
}
