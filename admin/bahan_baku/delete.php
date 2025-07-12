<?php
require_once '../includes/header.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header("Location: {$base_url}auth/login.php");
    exit;
}

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

$id_bahan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cek apakah bahan digunakan di tabel lain
$cek_pengiriman = query("SELECT id_pengiriman_potong FROM pengiriman_pemotong WHERE id_bahan = $id_bahan LIMIT 1");
$cek_pembelian = query("SELECT id_pembelian FROM pembelian_bahan WHERE id_bahan = $id_bahan LIMIT 1");

if ($cek_pengiriman || $cek_pembelian) {
    $_SESSION['error'] = "Bahan baku tidak dapat dihapus karena masih terhubung dengan data pengiriman atau pembelian!";
    header("Location: list.php");
    exit();
}

// Jika tidak terhubung, lakukan penghapusan
if ($conn->query("DELETE FROM bahan_baku WHERE id_bahan = $id_bahan")) {
    $_SESSION['success'] = "Bahan baku berhasil dihapus";
} else {
    $_SESSION['error'] = "Gagal menghapus bahan baku: " . $conn->error;
}

header("Location: list.php");
exit();
