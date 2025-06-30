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
    // Ambil data dari form
    $mitra_id = (int) $_POST['mitra_id'];
    $pic_bagian_id = (int) $_POST['pic_bagian_id'];
    $no_dokumen = mysqli_real_escape_string($mysqli, trim($_POST['no_dokumen']));
    $tentang = mysqli_real_escape_string($mysqli, trim($_POST['tentang']));
    $tanggal_penandatanganan = mysqli_real_escape_string($mysqli, $_POST['tanggal_penandatanganan']);
    $jangka_waktu_tahun = (int) $_POST['jangka_waktu_tahun'];
    $link_dokumen_MoU = mysqli_real_escape_string($mysqli, trim($_POST['link_dokumen_moU']));

    // Query untuk menyimpan data ke tabel tbl_mou
    $query = mysqli_query($mysqli, "INSERT INTO tbl_mou(mitra_id, pic_bagian_id, no_dokumen, tentang, tanggal_penandatanganan, jangka_waktu_tahun, link_dokumen_moU)
                                     VALUES('$mitra_id', '$pic_bagian_id', '$no_dokumen', '$tentang', '$tanggal_penandatanganan', '$jangka_waktu_tahun', '$link_dokumen_MoU')")
        or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));

    if ($query) {
        // Alihkan ke halaman utama BKI dengan pesan sukses
        header('location: ../../main.php?module=bki&pesan=1');
        exit();
    }
}
?>