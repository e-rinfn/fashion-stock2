<?php
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$id = $conn->real_escape_string($_GET['id']);

// Cek apakah bahan digunakan di tabel lain sebelum menghapus
$check_sql = "SELECT COUNT(*) as total FROM pengiriman_pemotong WHERE id_bahan = '$id'";
$check_result = $conn->query($check_sql);
$check_data = $check_result->fetch_assoc();

if ($check_data['total'] > 0) {
    $_SESSION['error'] = "Bahan baku tidak dapat dihapus karena sudah digunakan dalam produksi";
    header("Location: list.php");
    exit();
}

$sql = "DELETE FROM bahan_baku WHERE id_bahan = '$id'";

if ($conn->query($sql)) {
    $_SESSION['success'] = "Bahan baku berhasil dihapus";
} else {
    $_SESSION['error'] = "Gagal menghapus bahan baku: " . $conn->error;
}

header("Location: list.php");
exit();
