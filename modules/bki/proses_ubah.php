<?php
session_start();      // mengaktifkan session

// Panggil file untuk koneksi ke database
require_once "../../config/database.php";

// Pengecekan hak akses (opsional tapi sangat direkomendasikan untuk keamanan)
// Pastikan hanya user dengan hak akses tertentu yang bisa menghapus
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'BKI'])) {
    // Jika tidak punya hak akses, alihkan ke halaman login atau halaman utama
    header('location: ../../main.php?module=bki&pesan=6');
}
// jika user sudah login, maka jalankan perintah untuk update
else {

    // mengecek data hasil submit dari form
    if (isset($_POST['simpan'])) {
        // Ambil semua data dari form
        $id = (int) $_POST['id'];
        $mitra_id = (int) $_POST['mitra_id'];
        $jenis_dokumen_id = (int) $_POST['jenis_dokumen_id'];
        $pic_bagian_id = (int) $_POST['pic_bagian_id'];
        $jangka_waktu_hari = (int) $_POST['jangka_waktu_hari'];

        // Sanitasi semua data yang berupa string/teks
        $no_dokumen = mysqli_real_escape_string($mysqli, trim($_POST['no_dokumen']));
        $tentang = mysqli_real_escape_string($mysqli, trim($_POST['tentang']));
        $tanggal_penandatanganan = mysqli_real_escape_string($mysqli, trim($_POST['tanggal_penandatanganan']));
        $link_dokumen_MoU = mysqli_real_escape_string($mysqli, trim($_POST['link_dokumen_MoU']));
        $link_dokumen_PKS = mysqli_real_escape_string($mysqli, trim($_POST['link_dokumen_PKS']));

        // sql statement untuk update data di tabel "tbl_bki"
        $query = mysqli_query($mysqli, "UPDATE tbl_bki SET 
                                        mitra_id                = '$mitra_id',
                                        jenis_dokumen_id        = '$jenis_dokumen_id',
                                        pic_bagian_id           = '$pic_bagian_id',
                                        no_dokumen              = '$no_dokumen',
                                        tentang                 = '$tentang',
                                        tanggal_penandatanganan = '$tanggal_penandatanganan',
                                        jangka_waktu_hari       = '$jangka_waktu_hari',
                                        link_dokumen_MoU        = '$link_dokumen_MoU',
                                        link_dokumen_PKS        = '$link_dokumen_PKS'
                                    WHERE id = '$id'")
            or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));

        // cek jika query berhasil
        if ($query) {
            // jika berhasil, alihkan ke halaman utama BKI dengan pesan sukses (pesan=2 untuk ubah)
            header('location: ../../main.php?module=bki&pesan=2');
        }
    }
}
