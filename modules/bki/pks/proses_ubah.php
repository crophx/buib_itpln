<?php
session_start();      // mengaktifkan session

// Panggil file untuk koneksi ke database
require_once "../../../config/database.php";

// Pengecekan hak akses (opsional tapi sangat direkomendasikan untuk keamanan)
// Pastikan hanya user dengan hak akses tertentu yang bisa menghapus
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'BKI'])) {
    // Jika tidak punya hak akses, alihkan ke halaman login atau halaman utama
    header('location: ../../main.php?module=bki&pesan=6');
}
// jika user sudah login, maka jalankan perintah untuk update
else {

    // Cek apakah form dikirim
    if (isset($_POST['simpan'])) {
        // Ambil data dari form
        $pks_id = $_POST['pks_id'];
        $mitra_id = $_POST['mitra_id'];
        $no_dokumen_pks = trim(mysqli_real_escape_string($mysqli, $_POST['no_dokumen_pks']));
        $no_dokumen_mou = trim(mysqli_real_escape_string($mysqli, $_POST['no_dokumen_mou']));
        $pic_bagian_id = $_POST['pic_bagian_id'];
        $tentang_pks = trim(mysqli_real_escape_string($mysqli, $_POST['tentang_pks']));
        $jangka_waktu_tahun = intval($_POST['jangka_waktu_tahun']);
        $tgl_pks = $_POST['tgl_pks'];
        $link_dokumen_pks = trim(mysqli_real_escape_string($mysqli, $_POST['link_dokumen_pks']));

        // Cari ID MoU berdasarkan nomor dokumen MoU
        $query_mou = mysqli_query($mysqli, "SELECT id FROM tbl_mou WHERE no_dokumen = '$no_dokumen_mou'")
            or die('Gagal mencari ID MoU: ' . mysqli_error($mysqli));

        if (mysqli_num_rows($query_mou) > 0) {
            $data_mou = mysqli_fetch_assoc($query_mou);
            $mou_id = $data_mou['id'];
        } else {
            // Jika tidak ditemukan, set null
            $mou_id = "NULL";
        }

        // Update data ke database
        $query_update = mysqli_query($mysqli, "UPDATE tbl_pks SET
                            mitra_id = '$mitra_id',
                            mou_id = $mou_id,
                            no_dokumen = '$no_dokumen_pks',
                            tentang = '$tentang_pks',
                            jangka_waktu_tahun = '$jangka_waktu_tahun',
                            tanggal_penandatanganan = '$tgl_pks',
                            link_dokumen_pks = '$link_dokumen_pks',
                            pic_bagian_id = '$pic_bagian_id'
                        WHERE id = '$pks_id'")
            or die('Gagal mengubah data PKS: ' . mysqli_error($mysqli));

        // Redirect kembali ke halaman PKS dengan notifikasi
        header("Location: ../../../main.php?module=bki&pesan=2");
    }
}
