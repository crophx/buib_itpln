<?php
session_start();
require_once "../../../config/database.php";

// Pastikan hanya user dengan hak akses yang benar yang bisa mengakses file ini
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'BKS'])) {
    header('location: ../../../main.php?module=bks&pesan=6');
    exit();
}

// Cek jika form disubmit
if (isset($_POST['simpan'])) {
    // Ambil dan sanitasi semua data yang diterima dari form modal
    $id = (int) $_POST['id'];
    $mitra_id = (int) $_POST['mitra_id'];
    $pic_bagian_id = (int) $_POST['pic_bagian_id'];

    // Ambil data ENUM
    $klasifikasi_mitra = mysqli_real_escape_string($mysqli, $_POST['klasifikasi_mitra']);
    $bentuk_kerjasama_bks = mysqli_real_escape_string($mysqli, $_POST['bentuk_kerjasama_bks']);

    // Ambil dan sanitasi data lainnya
    $no_dokumen = mysqli_real_escape_string($mysqli, trim($_POST['no_dokumen']));
    $tentang = mysqli_real_escape_string($mysqli, trim($_POST['tentang']));
    $tanggal_awal = mysqli_real_escape_string($mysqli, $_POST['tanggal_awal']);
    $tanggal_akhir = mysqli_real_escape_string($mysqli, $_POST['tanggal_akhir']);
    $link_dokumen_bks = mysqli_real_escape_string($mysqli, trim($_POST['link_dokumen_bks']));

    // Query untuk memperbarui (UPDATE) data di tabel tbl_mou_bks
    $query = mysqli_query($mysqli, "UPDATE tbl_mou_bks SET 
                                        mitra_id = '$mitra_id',
                                        pic_bagian_id = '$pic_bagian_id',
                                        klasifikasi_mitra = '$klasifikasi_mitra',
                                        bentuk_kerjasama_bks = '$bentuk_kerjasama_bks',
                                        jenis_dokumen_bks = '$jenis_dokumen_bks',
                                        no_dokumen = '$no_dokumen',
                                        tentang = '$tentang',
                                        tanggal_awal = '$tanggal_awal',
                                        tanggal_akhir = '$tanggal_akhir',
                                        link_dokumen_bks = '$link_dokumen_bks'
                                    WHERE id = '$id'")
        or die('Ada kesalahan pada query update MoU BKS: ' . mysqli_error($mysqli));

    // Cek jika query berhasil
    if ($query) {
        // Alihkan kembali ke halaman utama BKS dengan pesan sukses (pesan=2 untuk 'diubah')
        header('location: ../../../main.php?module=bks&pesan=2');
        exit();
    }
}
?>