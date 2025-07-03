<?php
// Pastikan sesi sudah dimulai
session_start();

// Panggil file untuk koneksi ke database
require_once "../../../config/database.php";

// Pengecekan hak akses
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'BKI'])) {
    // Jika tidak punya hak akses, alihkan dan hentikan skrip
    header('location: ../../../main.php?module=bki&pesan=6');
    exit();
}

// Mengecek data GET "id"
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Query untuk menghapus data dari tbl_pks
    $query = mysqli_query($mysqli, "DELETE FROM tbl_pks WHERE id = '$id'")
        or die('Ada kesalahan pada query hapus PKS: ' . mysqli_error($mysqli));

    // Cek jika query berhasil
    if ($query) {
        // PERBAIKAN: Menggunakan path relatif dan menambahkan exit()
        header('location: ../../../main.php?module=bki&pesan=3');
        exit();
    }
} else {
    // Jika file diakses tanpa ID, alihkan kembali
    header('location: ../../../main.php?module=bki');
    exit();
}
?>