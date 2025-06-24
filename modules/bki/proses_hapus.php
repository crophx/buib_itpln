<?php
// Pastikan sesi sudah dimulai jika belum, untuk keamanan
session_start();

// Panggil file untuk koneksi ke database
require_once "../../config/database.php";

// Pengecekan hak akses (opsional tapi sangat direkomendasikan untuk keamanan)
// Pastikan hanya user dengan hak akses tertentu yang bisa menghapus
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'BKI'])) {
    // Jika tidak punya hak akses, alihkan ke halaman login atau halaman utama
    header('location: ../../main.php?module=bki&pesan=6');
    exit();
}

// Mengecek data GET "id"
if (isset($_GET['id'])) {
    // Ambil nilai "id" dari URL dan pastikan nilainya adalah integer untuk keamanan
    $id = (int) $_GET['id'];

    // SQL statement untuk menghapus data dari tabel "tbl_bki" berdasarkan "id"
    $query = mysqli_query($mysqli, "DELETE FROM tbl_bki WHERE id = '$id'")
        or die('Ada kesalahan pada query delete : ' . mysqli_error($mysqli));

    // Cek jika query berhasil dijalankan
    if ($query) {
        // Jika berhasil, alihkan kembali ke halaman utama BKI dengan pesan sukses
        // Pesan=3 menandakan sukses hapus data
        header('location: ../../main.php?module=bki&pesan=3');
    }
} else {
    // Jika tidak ada "id" di URL, alihkan ke halaman utama untuk mencegah error
    header('location: ../../main.php?module=bki');
}
?>