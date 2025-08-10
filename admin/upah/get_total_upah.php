<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json');

$id_penerima = intval($_POST['id_penerima']);
$jenis_penerima = $conn->real_escape_string($_POST['jenis_penerima']);

function getTotalUpahBelumDibayar($id_penerima, $jenis_penerima)
{
    global $conn;

    $tabel_hasil = ($jenis_penerima == 'pemotong') ? 'hasil_pemotongan' : 'hasil_penjahitan';
    $tabel_pengiriman = ($jenis_penerima == 'pemotong') ? 'pengiriman_pemotong' : 'pengiriman_penjahit';
    $kolom_relasi = ($jenis_penerima == 'pemotong') ? 'id_pengiriman_potong' : 'id_pengiriman_jahit';
    $kolom_penerima = ($jenis_penerima == 'pemotong') ? 'id_pemotong' : 'id_penjahit';

    $sql = "SELECT SUM(h.total_upah) as total
            FROM $tabel_hasil h
            JOIN $tabel_pengiriman p ON h.$kolom_relasi = p.$kolom_relasi
            WHERE p.$kolom_penerima = $id_penerima
            AND NOT EXISTS (
                SELECT 1 FROM detail_pembayaran_upah d 
                WHERE d.id_hasil = h.id_hasil_" . substr($jenis_penerima, 0, 4) . "
                AND d.jenis_hasil = '" . substr($jenis_penerima, 0, 4) . "'
            )";

    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'] ?? 0;
}

$total = getTotalUpahBelumDibayar($id_penerima, $jenis_penerima);
echo json_encode(['total' => $total]);
