<?php
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$id = $conn->real_escape_string($_GET['id']);

// Cek apakah penjahit pernah terlibat dalam produksi
$checkSql = "SELECT COUNT(*) as total FROM pengiriman_penjahit WHERE id_penjahit = '$id'";
$checkResult = $conn->query($checkSql);
$checkData = $checkResult->fetch_assoc();

if ($checkData['total'] > 0) {
    $_SESSION['error'] = "Penjahit tidak dapat dihapus karena sudah ada dalam data produksi";
    header("Location: list.php");
    exit();
}

$sql = "DELETE FROM penjahit WHERE id_penjahit = '$id'";

if ($conn->query($sql)) {
    $_SESSION['success'] = "Data penjahit berhasil dihapus";
} else {
    $_SESSION['error'] = "Gagal menghapus data: " . $conn->error;
}

header("Location: list.php");
exit();
