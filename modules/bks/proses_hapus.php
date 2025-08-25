<?php
session_start();
require_once "../../config/database.php";

// Pengecekan hak akses
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan', 'BKS'])) {
    // Jika tidak punya hak akses, alihkan dengan pesan error
    header('location: ../../../main.php?module=bks&pesan=6');
    exit();
}

// Mengecek apakah ID dari URL ada dan valid
if (isset($_GET['id'])) {
    // Sanitasi ID untuk memastikan itu adalah angka
    $id = (int) $_GET['id'];

    // Query untuk menghapus data dari tabel tbl_rk_bks berdasarkan ID
    $query = mysqli_query($mysqli, "DELETE FROM tbl_rk_bks WHERE id = '$id'")
        or die('Ada kesalahan pada query hapus Rencana Kegiatan: ' . mysqli_error($mysqli));

    // Cek jika query berhasil dijalankan
    if ($query) {
        // Alihkan kembali ke halaman utama BKS dengan pesan sukses (pesan=3 untuk 'dihapus')
        header('location: ../../../main.php?module=bks&pesan=3');
        exit();
    }
} else {
    // Jika file diakses tanpa ID, alihkan kembali ke halaman utama
    header('location: main.php?module=bks');
    exit();
}
?>