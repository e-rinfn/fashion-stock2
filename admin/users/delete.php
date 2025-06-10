<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Hanya admin yang bisa menghapus user
// if ($_SESSION['role'] != 'admin') {
//     header("Location: ../../index.php");
//     exit();
// }



$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Tidak boleh menghapus diri sendiri
if ($id == $_SESSION['user_id']) {
    $_SESSION['error'] = "Anda tidak bisa menghapus akun sendiri";
    header("Location: index.php");
    exit();
}

// Hapus user
if ($conn->query("DELETE FROM users WHERE id_user = $id")) {
    $_SESSION['success'] = "User berhasil dihapus";
} else {
    $_SESSION['error'] = "Gagal menghapus user: " . $conn->error;
}

header("Location: index.php");
exit();
