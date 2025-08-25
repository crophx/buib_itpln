<?php
session_start();
require_once "../../config/database.php";

// Pengecekan hak akses
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan', 'BKS'])) {
    header('location: ../../../main.php?module=bks&pesan=6');
// Pengecekan hak akses
if (empty($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan', 'BKS'])) {
    header('location: ../../../main.php?module=bks&pesan=6');
    exit();
}

// Cek jika form telah disubmit
if (isset($_POST['simpan_rencana'])) {
    // Ambil data dari form dan lakukan sanitasi
    $mitra_id = (int) $_POST['mitra_id'];
    $bentuk_kerjasama = mysqli_real_escape_string($mysqli, $_POST['bentuk_kerjasama']); // Sesuaikan name ini di form Anda
    $keterangan = mysqli_real_escape_string($mysqli, trim($_POST['keterangan']));
    $perihal = mysqli_real_escape_string($mysqli, trim($_POST['perihal']));
    $status_kerjasama_id = (int) $_POST['status_kerjasama_id'];


    // Proses tanggal dari form menjadi format YYYY-MM-DD
    $tgl_input = $_POST['tgl_input']; // format: dd/mm/yyyy
    // Ubah format tanggal dari dd/mm/yyyy menjadi yyyy-mm-dd
    $tanggal_parts = explode('/', $tgl_input);
    if (count($tanggal_parts) == 3) {
        $target_realisasi = $tanggal_parts[2] . '-' . $tanggal_parts[1] . '-' . $tanggal_parts[0];
    } else {
        // Jika format salah, gunakan tanggal hari ini sebagai default
        $target_realisasi = date('Y-m-d');
    }

    // Query untuk menyimpan data ke tabel tbl_rk_bks
    $query = mysqli_query($mysqli, "INSERT INTO tbl_rk_bks(mitra_id, bentuk_kerjasama, perihal, keterangan, target_realisasi, status_kerjasama_id) 
                                     VALUES('$mitra_id', '$bentuk_kerjasama', '$perihal','$keterangan', '$target_realisasi', '$status_kerjasama_id')")
        or die('Ada kesalahan pada query insert Rencana Kegiatan: ' . mysqli_error($mysqli));

    // Cek jika query berhasil
    if ($query) {
        header('location: ../../../main.php?module=bks&pesan=1');
        exit();
    // Cek jika query berhasil
    if ($query) {
        header('location: ../../../main.php?module=bks&pesan=1');
        exit();
    }
}
?>