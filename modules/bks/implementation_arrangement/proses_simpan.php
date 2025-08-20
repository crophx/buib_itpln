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
    // PERBAIKAN 1: Logika untuk menangani mou_id yang opsional
    $mou_id = !empty($_POST['mou_id']) ? (int) $_POST['mou_id'] : NULL;

    $mitra_id = (int) $_POST['mitra_id'];
    $pic_bagian_id = (int) $_POST['pic_bagian_id'];

    // Ambil data ENUM
    $bentuk_kerjasama_bks = mysqli_real_escape_string($mysqli, $_POST['bentuk_kerjasama_bks']);
    // TAMBAHAN: Mengambil data klasifikasi_mitra dari form
    $klasifikasi_mitra = mysqli_real_escape_string($mysqli, $_POST['klasifikasi_mitra']);

    // Ambil dan sanitasi data lainnya
    $no_dokumen = mysqli_real_escape_string($mysqli, trim($_POST['no_dokumen']));
    $tentang = mysqli_real_escape_string($mysqli, trim($_POST['tentang']));
    $tanggal_awal = mysqli_real_escape_string($mysqli, $_POST['tanggal_awal']);
    $tanggal_akhir = mysqli_real_escape_string($mysqli, $_POST['tanggal_akhir']);
    $link_dokumen_ia = mysqli_real_escape_string($mysqli, trim($_POST['link_dokumen_ia']));

    // Menyiapkan nilai mou_id untuk query SQL
    $mou_id_sql = ($mou_id === NULL) ? "NULL" : "'$mou_id'";

    $query = mysqli_query($mysqli, "INSERT INTO tbl_i_a(
                                        mou_id, mitra_id, pic_bagian_id, klasifikasi_mitra, bentuk_kerjasama_bks, 
                                        no_dokumen, tentang, tanggal_awal, tanggal_akhir, link_dokumen_ia
                                    ) 
                                    VALUES(
                                        $mou_id_sql, '$mitra_id', '$pic_bagian_id', '$klasifikasi_mitra', '$bentuk_kerjasama_bks', 
                                        '$no_dokumen', '$tentang', '$tanggal_awal', '$tanggal_akhir', '$link_dokumen_ia'
                                    )")
        or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));

    // Cek jika query berhasil
    if ($query) {
        header('location: ../../../main.php?module=bks&pesan=1');
        exit();
    }
}
?>