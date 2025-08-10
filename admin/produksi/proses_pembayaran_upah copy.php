<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

session_start();

// Validasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Akses tidak sah';
    header('Location: riwayat_hasil_pemotongan.php');
    exit();
}

// Validasi input yang diperlukan
if (empty($_POST['id_pemotong'])) {
    $_SESSION['error'] = 'ID Pemotong tidak boleh kosong';
    header('Location: riwayat_hasil_pemotongan.php');
    exit();
}

// Escape input
$id_pemotong = $conn->real_escape_string($_POST['id_pemotong']);
$metode = $conn->real_escape_string($_POST['metode_pembayaran'] ?? 'transfer');
$tanggal = $conn->real_escape_string($_POST['tanggal_pembayaran'] ?? date('Y-m-d'));
$keterangan = $conn->real_escape_string($_POST['keterangan'] ?? '');
$tgl_awal = $conn->real_escape_string($_POST['tgl_awal'] ?? '');
$tgl_akhir = $conn->real_escape_string($_POST['tgl_akhir'] ?? '');

// Buat kondisi WHERE untuk filter
$where = ["pg.id_pemotong = $id_pemotong"];
$where[] = "h.id_hasil_potong NOT IN (
            SELECT id_hasil FROM detail_pembayaran_upah 
            WHERE jenis_hasil = 'potong'
           )";

if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where[] = "h.tanggal_selesai BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$where_sql = implode(' AND ', $where);

// Mulai transaksi
$conn->begin_transaction();

try {
    // 1. Hitung total upah yang belum dibayar
    $query_total = "
        SELECT 
            SUM(h.total_upah) as total_upah, 
            COUNT(*) as jumlah,
            MIN(h.tanggal_selesai) as tanggal_awal,
            MAX(h.tanggal_selesai) as tanggal_akhir
        FROM hasil_pemotongan h
        JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
        WHERE $where_sql
    ";

    $result = $conn->query($query_total);

    if (!$result) {
        throw new Exception("Gagal menghitung total upah: " . $conn->error);
    }

    $data = $result->fetch_assoc();
    $total_upah = $data['total_upah'] ?? 0;
    $jumlah_produksi = $data['jumlah'] ?? 0;
    $tanggal_awal = $data['tanggal_awal'] ?? $tgl_awal;
    $tanggal_akhir = $data['tanggal_akhir'] ?? $tgl_akhir;

    if ($jumlah_produksi == 0 || $total_upah == 0) {
        throw new Exception("Tidak ada upah yang perlu dibayar untuk pemotong ini dalam periode yang dipilih");
    }

    // 2. Buat record pembayaran
    $sql_pembayaran = "
        INSERT INTO pembayaran_upah 
        (id_penerima, jenis_penerima, periode_awal, periode_akhir, total_upah, 
         tanggal_bayar, metode_pembayaran, status, catatan)
        VALUES (
            $id_pemotong, 'pemotong', 
            " . (!empty($tanggal_awal) ? "'$tanggal_awal'" : "NULL") . ", 
            " . (!empty($tanggal_akhir) ? "'$tanggal_akhir'" : "NULL") . ",
            $total_upah, '$tanggal', '$metode', 'dibayar', '$keterangan'
        )
    ";

    if (!$conn->query($sql_pembayaran)) {
        throw new Exception("Gagal menyimpan pembayaran: " . $conn->error);
    }

    $id_pembayaran = $conn->insert_id;

    // 3. Simpan detail pembayaran
    $sql_detail = "
        INSERT INTO detail_pembayaran_upah 
        (id_pembayaran, id_hasil, jenis_hasil, jumlah_unit, tarif_per_unit, subtotal)
        SELECT 
            $id_pembayaran, h.id_hasil_potong, 'potong', h.jumlah_hasil, 
            COALESCE(t.tarif_per_unit, 0), h.total_upah
        FROM hasil_pemotongan h
        JOIN pengiriman_pemotong pg ON h.id_pengiriman_potong = pg.id_pengiriman_potong
        LEFT JOIN tarif_upah t ON h.id_tarif = t.id_tarif
        WHERE $where_sql
    ";

    if (!$conn->query($sql_detail)) {
        throw new Exception("Gagal menyimpan detail pembayaran: " . $conn->error);
    }

    $conn->commit();
    $_SESSION['success'] = "Pembayaran upah berhasil dicatat dengan ID: $id_pembayaran";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
}

// Redirect kembali dengan parameter filter
$redirect_params = [];
if (!empty($tgl_awal)) $redirect_params['tgl_awal'] = $tgl_awal;
if (!empty($tgl_akhir)) $redirect_params['tgl_akhir'] = $tgl_akhir;
if (!empty($id_pemotong)) $redirect_params['pemotong'] = $id_pemotong;

header('Location: riwayat_hasil_pemotongan.php?' . http_build_query($redirect_params));
exit();
