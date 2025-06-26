<?php
session_start();
require_once "../../config/database.php";

// Cek hak akses
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'BKI'])) {
    header('location: ../../main.php?module=bki&pesan=6');
    exit();
}

// Cek jika form disubmit
if (isset($_POST['simpan'])) {
    // Ambil data dari form dan lakukan sanitasi
    $mitra_id = (int) $_POST['mitra_id'];
    $tentang = mysqli_real_escape_string($mysqli, trim($_POST['tentang']));
    $target_realisasi = mysqli_real_escape_string($mysqli, trim($_POST['target_realisasi']));

    // Query untuk menyimpan data ke tabel tbl_rk_bki
    // Kolom 'status' akan otomatis terisi 'Direncanakan' karena nilai DEFAULT di database
    $query = mysqli_query($mysqli, "INSERT INTO tbl_rk_bki(mitra_id, tentang, target_realisasi) 
                                     VALUES('$mitra_id', '$tentang', '$target_realisasi')")
        or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));

    if ($query) {
        // Alihkan kembali ke halaman bki dengan pesan sukses
        header('location: ../../main.php?module=bki&pesan=1');
        exit();
    }
}
?>