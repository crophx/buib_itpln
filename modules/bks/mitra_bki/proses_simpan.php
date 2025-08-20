<?php
// panggil file untuk koneksi ke database
require_once "../../../config/database.php";

// mengecek data post dari form
if (isset($_POST['simpan'])) {
    // ambil data hasil submit dari form
    $nama_mitra = mysqli_real_escape_string($mysqli, $_POST['nama_mitra']);
    $negara = mysqli_real_escape_string($mysqli, $_POST['negara']);
    $alamat = mysqli_real_escape_string($mysqli, $_POST['alamat']);

    // sql statement untuk insert data ke tabel
    $query = mysqli_query($mysqli, "INSERT INTO tbl_mitra_bki(nama_mitra, negara, alamat) 
                                     VALUES('$nama_mitra', '$negara', '$alamat')")
        or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));
    // cek query
    if ($query) {
        // jika berhasil tampilkan pesan berhasil simpan data
        header('location: ../../../main.php?module=mitra_bki&pesan=1');
    }
}
?>