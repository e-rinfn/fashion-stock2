<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
// redirectIfNotLoggedIn();
// checkRole('admin');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cek apakah supplier memiliki transaksi
$sql_check = "SELECT COUNT(*) as total FROM pembelian WHERE id_supplier = $id";
$result = $conn->query($sql_check);
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    $_SESSION['error'] = "Supplier tidak dapat dihapus karena memiliki riwayat pembelian";
    header("Location: list.php");
    exit();
}

$sql = "DELETE FROM supplier WHERE id_supplier = $id";
if ($conn->query($sql)) {
    $_SESSION['success'] = "Supplier berhasil dihapus";
} else {
    $_SESSION['error'] = "Gagal menghapus supplier: " . $conn->error;
}

header("Location: list.php");
exit();
