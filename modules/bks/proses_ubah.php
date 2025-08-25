<?php
session_start();
require_once "../../config/database.php";

// Pengecekan hak akses
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'BKS'])) {
    header('location: ../../../main.php?module=bks&pesan=6');
    exit();
}

// Cek jika form telah disubmit
if (isset($_POST['simpan'])) {
    // Ambil data dari form dan lakukan sanitasi
    $id = (int) $_POST['id'];
    $mitra_id = (int) $_POST['mitra_id'];
    $tentang = mysqli_real_escape_string($mysqli, trim($_POST['tentang']));

    // =============================================================
    // TAMBAHAN: Ambil data ENUM dari form
    // =============================================================
    $klasifikasi_mitra = mysqli_real_escape_string($mysqli, $_POST['klasifikasi_mitra']);
    $bentuk_kerjasama = mysqli_real_escape_string($mysqli, $_POST['bentuk_kerjasama']);
    // =============================================================

    // Ambil dan konversi format tanggal
    $target_realisasi_from_form = mysqli_real_escape_string($mysqli, $_POST['target_realisasi']);
    $tanggal_parts = explode('/', $target_realisasi_from_form);
    if (count($tanggal_parts) == 3) {
        $target_realisasi_sql = $tanggal_parts[2] . '-' . $tanggal_parts[1] . '-' . $tanggal_parts[0];
    } else {
        // Jika format tanggal dari form salah, gunakan format yang sudah ada
        $target_realisasi_sql = $target_realisasi_from_form;
    }

    // =============================================================
    // PERBAIKAN: Tambahkan kolom baru ke dalam query UPDATE
    // =============================================================
    $query = mysqli_query($mysqli, "UPDATE tbl_rk_bks SET 
                                        mitra_id = '$mitra_id',
                                        klasifikasi_mitra = '$klasifikasi_mitra',
                                        bentuk_kerjasama = '$bentuk_kerjasama',
                                        tentang = '$tentang',
                                        target_realisasi = '$target_realisasi_sql'
                                    WHERE id = '$id'")
        or die('Ada kesalahan pada query update: ' . mysqli_error($mysqli));
    // =============================================================

    // Cek jika query berhasil
    if ($query) {
        // Alihkan kembali ke halaman utama BKS dengan pesan sukses (pesan=2 untuk 'diubah')
        header('location: ../../../main.php?module=bks&pesan=2');
        exit();
    }
}
?>