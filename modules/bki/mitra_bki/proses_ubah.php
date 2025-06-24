<?php
// panggil file untuk koneksi ke database
require_once "../../../config/database.php";

// mengecek data post dari form
if (isset($_POST['simpan'])) {
    // ambil data hasil submit dari form
    $id = mysqli_real_escape_string($mysqli, $_POST['id']);
    $nama_mitra = mysqli_real_escape_string($mysqli, $_POST['nama_mitra']);
    $negara = mysqli_real_escape_string($mysqli, $_POST['negara']);
    $alamat = mysqli_real_escape_string($mysqli, $_POST['alamat']);

    // sql statement untuk update data di tabel
    $query = mysqli_query($mysqli, "UPDATE tbl_mitra_bki
                                     SET nama_mitra = '$nama_mitra', negara = '$negara', alamat = '$alamat'
                                     WHERE id = '$id'")
        or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
    // cek query
    if ($query) {
        // jika berhasil tampilkan pesan berhasil ubah data.
        header('location: ../../../main.php?module=mitra_bki&pesan=2');
    }
}
?>