<?php
session_start();
require_once "../../config/database.php";

// Pengecekan hak akses
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'BKS'])) {
    header('location: main.php?module=bks&pesan=6');
    exit();
}

// Cek jika form telah disubmit
if (isset($_POST['simpan'])) {
    // Ambil data dari form dan lakukan sanitasi
    $id = (int) $_POST['id'];
    $mitra_id = (int) $_POST['mitra_id'];
    $status_kerjasama_id = (int) $_POST['status_kerjasama_id'];

    $klasifikasi_mitra = mysqli_real_escape_string($mysqli, $_POST['klasifikasi_mitra']);
    $bentuk_kerjasama = mysqli_real_escape_string($mysqli, $_POST['bentuk_kerjasama']);
    $perihal = mysqli_real_escape_string($mysqli, trim($_POST['perihal'])); // Kolom baru
    $keterangan = mysqli_real_escape_string($mysqli, trim($_POST['keterangan'])); // 'keterangan' dari form disimpan ke 'tentang'
    $target_realisasi = mysqli_real_escape_string($mysqli, $_POST['target_realisasi']); // Format sudah Y-m-d dari form

    // Query untuk memperbarui (UPDATE) data di tabel tbl_rk_bks
    $query = mysqli_query($mysqli, "UPDATE tbl_rk_bks SET 
                                        mitra_id = '$mitra_id',
                                        klasifikasi_mitra = '$klasifikasi_mitra',
                                        bentuk_kerjasama = '$bentuk_kerjasama',
                                        perihal = '$perihal',
                                        keterangan = '$keterangan',
                                        target_realisasi = '$target_realisasi',
                                        status_kerjasama_id = '$status_kerjasama_id'
                                    WHERE id = '$id'")
        or die('Ada kesalahan pada query update Rencana Kegiatan: ' . mysqli_error($mysqli));

    // Cek jika query berhasil
    if ($query) {
        // Alihkan kembali ke halaman utama BKS dengan pesan sukses (pesan=2 untuk 'diubah')
        header('location: main.php?module=bks&pesan=2');
        exit();
    }
}
?>