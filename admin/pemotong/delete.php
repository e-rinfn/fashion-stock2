<?php
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$id = $conn->real_escape_string($_GET['id']);

// Cek apakah pemotong digunakan di tabel lain sebelum menghapus
$check_sql = "SELECT COUNT(*) as total FROM pengiriman_pemotong WHERE id_pemotong = '$id'";
$check_result = $conn->query($check_sql);
$check_data = $check_result->fetch_assoc();

if ($check_data['total'] > 0) {
    $_SESSION['error'] = "Pemotong tidak dapat dihapus karena sudah digunakan dalam produksi";
    header("Location: list.php");
    exit();
}

$sql = "DELETE FROM pemotong WHERE id_pemotong = '$id'";

if ($conn->query($sql)) {
    $_SESSION['success'] = "Pemotong berhasil dihapus";
} else {
    $_SESSION['error'] = "Gagal menghapus pemotong: " . $conn->error;
}

header("Location: list.php");
exit();
