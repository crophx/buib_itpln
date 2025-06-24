<?php
require_once "../../config/database.php";

// Pengecekan hak akses (opsional tapi sangat direkomendasikan untuk keamanan)
// Pastikan hanya user dengan hak akses tertentu yang bisa menghapus
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'BKI'])) {
    // Jika tidak punya hak akses, alihkan ke halaman login atau halaman utama
    header('location: ../../main.php?module=bki&pesan=6');
}

if (isset($_POST['simpan'])) {
    // Ambil data dari form
    $mitra_id = (int) $_POST['mitra_id'];
    $jenis_dokumen_id = (int) $_POST['jenis_dokumen_id'];
    $pic_bagian_id = (int) $_POST['pic_bagian_id'];
    $no_dokumen = mysqli_real_escape_string($mysqli, $_POST['no_dokumen']);
    $tentang = mysqli_real_escape_string($mysqli, $_POST['tentang']);
    $tanggal_penandatanganan = mysqli_real_escape_string($mysqli, $_POST['tanggal_penandatanganan']);
    $jangka_waktu_hari = (int) $_POST['jangka_waktu_hari'];
    $link_dokumen_MoU = mysqli_real_escape_string($mysqli, $_POST['link_dokumen_MoU']);
    $link_dokumen_PKS = mysqli_real_escape_string($mysqli, $_POST['link_dokumen_PKS']);

    $query = mysqli_query($mysqli, "INSERT INTO tbl_bki(mitra_id, jenis_dokumen_id, pic_bagian_id, no_dokumen, tentang, tanggal_penandatanganan, jangka_waktu_hari, link_dokumen_MoU, link_dokumen_PKS)
                                     VALUES('$mitra_id', '$jenis_dokumen_id', '$pic_bagian_id', '$no_dokumen', '$tentang', '$tanggal_penandatanganan', '$jangka_waktu_hari', '$link_dokumen_MoU', '$link_dokumen_PKS')")
        or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));

    if ($query) {
        header('location: ../../main.php?module=bki&pesan=1');
    }
}
?>