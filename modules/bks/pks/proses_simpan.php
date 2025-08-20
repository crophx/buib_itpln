<?php
session_start();
require_once "../../../config/database.php";

// Pengecekan hak akses
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'BKS'])) {
    header('location: ../../main.php?module=bks&pesan=6');
    exit();
}

// Cek jika form telah disubmit
if (isset($_POST['simpan'])) {
    // Ambil data dari form dan lakukan sanitasi dasar
    $mou_id = (int) $_POST['mou_id'];
    $mitra_id = (int) $_POST['mitra_id'];
    $pic_bagian_id = (int) $_POST['pic_bagian_id'];

    // Ambil data ENUM
    $bentuk_kerjasama_bks = mysqli_real_escape_string($mysqli, $_POST['bentuk_kerjasama_bks']);
    $klasifikasi_mitra = mysqli_real_escape_string($mysqli, $_POST['klasifikasi_mitra']);

    // Ambil dan sanitasi data lainnya
    $no_dokumen = mysqli_real_escape_string($mysqli, trim($_POST['no_dokumen']));
    $tentang = mysqli_real_escape_string($mysqli, trim($_POST['tentang']));
    $tanggal_awal = mysqli_real_escape_string($mysqli, $_POST['tanggal_awal']);
    $tanggal_akhir = mysqli_real_escape_string($mysqli, $_POST['tanggal_akhir']);
    $link_dokumen_pks = mysqli_real_escape_string($mysqli, trim($_POST['link_dokumen_pks']));

    // Query untuk menyimpan data ke tabel tbl_mou_bks
    $query = mysqli_query($mysqli, "INSERT INTO tbl_pks_bks(mou_id, mitra_id, pic_bagian_id, bentuk_kerjasama_bks, klasifikasi_mitra, no_dokumen, tentang, tanggal_awal, tanggal_akhir, link_dokumen_pks) 
                                     VALUES('$mou_id','$mitra_id', '$pic_bagian_id', '$bentuk_kerjasama_bks', '$klasifikasi_mitra', '$no_dokumen', '$tentang', '$tanggal_awal', '$tanggal_akhir', '$link_dokumen_pks')")
        or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));

    // Cek jika query berhasil
    if ($query) {
        // Alihkan kembali ke halaman BKS dengan pesan sukses
        header('location: ../../../main.php?module=bks&pesan=1');
        exit();
    }
}
?>