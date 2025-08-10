<?php
function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function formatTanggal($date)
{
    return date('d/m/Y', strtotime($date));
}

function sanitizeInput($conn, $input)
{
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($input)));
}
?>

<?php
require_once __DIR__ . '/../config/database.php';

// Fungsi untuk mendapatkan tarif upah terbaru
function getTarifTerbaru($conn, $jenis)
{
    $query = "SELECT * FROM tarif_upah 
              WHERE jenis_tarif = '$jenis' 
              ORDER BY berlaku_sejak DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) == 0) {
        return false;
    }

    return mysqli_fetch_assoc($result);
}

// Fungsi untuk menambahkan tarif baru
function tambahTarif($conn, $data)
{
    $jenis = sanitizeInput($conn, $data['jenis_tarif']);
    $tarif = sanitizeInput($conn, $data['tarif_per_unit']);
    $tanggal = sanitizeInput($conn, $data['berlaku_sejak']);
    $keterangan = sanitizeInput($conn, $data['keterangan']);

    $query = "INSERT INTO tarif_upah 
              (jenis_tarif, tarif_per_unit, berlaku_sejak, keterangan) 
              VALUES ('$jenis', '$tarif', '$tanggal', '$keterangan')";

    return mysqli_query($conn, $query);
}

// Fungsi untuk menghitung upah pemotong
function hitungUpahPemotong($conn, $id_hasil_potong)
{
    // Dapatkan tarif terbaru
    $tarif = getTarifTerbaru($conn, 'pemotongan');
    if (!$tarif) {
        return false;
    }

    // Dapatkan data hasil potong
    $query = "SELECT * FROM hasil_pemotongan WHERE id_hasil_potong = $id_hasil_potong";
    $result = mysqli_query($conn, $query);
    $hasil = mysqli_fetch_assoc($result);

    if (!$hasil) {
        return false;
    }

    // Hitung total upah
    $total_upah = $hasil['jumlah_hasil'] * $tarif['tarif_per_unit'];

    // Update database
    $query = "UPDATE hasil_pemotongan 
              SET id_tarif = {$tarif['id_tarif']}, total_upah = $total_upah
              WHERE id_hasil_potong = $id_hasil_potong";

    return mysqli_query($conn, $query) ? $total_upah : false;
}

// Fungsi untuk menghitung upah penjahit
function hitungUpahPenjahit($conn, $id_hasil_jahit)
{
    // Dapatkan tarif terbaru
    $tarif = getTarifTerbaru($conn, 'penjahitan');
    if (!$tarif) {
        return false;
    }

    // Dapatkan data hasil jahit
    $query = "SELECT * FROM hasil_penjahitan WHERE id_hasil_jahit = $id_hasil_jahit";
    $result = mysqli_query($conn, $query);
    $hasil = mysqli_fetch_assoc($result);

    if (!$hasil) {
        return false;
    }

    // Hitung total upah
    $total_upah = $hasil['jumlah_produk_jadi'] * $tarif['tarif_per_unit'];

    // Update database
    $query = "UPDATE hasil_penjahitan 
              SET id_tarif = {$tarif['id_tarif']}, total_upah = $total_upah
              WHERE id_hasil_jahit = $id_hasil_jahit";

    return mysqli_query($conn, $query) ? $total_upah : false;
}
