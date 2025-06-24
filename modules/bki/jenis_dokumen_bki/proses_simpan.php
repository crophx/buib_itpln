<?php
// panggil file untuk koneksi ke database
require_once "../../../config/database.php";

// mengecek data post dari form
if (isset($_POST['simpan'])) {
    // ambil data hasil submit dari form
    $nama_dokumen = mysqli_real_escape_string($mysqli, $_POST['nama_dokumen']);
    $kode_singkat = mysqli_real_escape_string($mysqli, $_POST['kode_singkat']);
    $keterangan   = mysqli_real_escape_string($mysqli, $_POST['keterangan']);

    // sql statement untuk insert data ke tabel
    $query = mysqli_query($mysqli, "INSERT INTO tbl_jenis_dokumen_bki(nama_dokumen, kode_singkat, keterangan) 
                                     VALUES('$nama_dokumen', '$kode_singkat', '$keterangan')")
                                     or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));
    // cek query
    if ($query) {
        // jika berhasil tampilkan pesan berhasil simpan data
        header('location: ../../../main.php?module=jenis_dokumen_bki&pesan=1');
    }
}
?>