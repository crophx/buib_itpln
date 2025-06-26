<?php
// panggil file untuk koneksi ke database
require_once "../../../config/database.php";

// mengecek data post dari form
if (isset($_POST['simpan'])) {
    // ambil data hasil submit dari form
    $id           = mysqli_real_escape_string($mysqli, $_POST['id']);
    $nama_dokumen = mysqli_real_escape_string($mysqli, $_POST['nama_dokumen']);
    $kode_singkat = mysqli_real_escape_string($mysqli, $_POST['kode_singkat']); 
    $keterangan   = mysqli_real_escape_string($mysqli, $_POST['keterangan']);

    // sql statement untuk update data di tabel
    $query = mysqli_query($mysqli, "UPDATE tbl_jenis_dokumen_bki
                                     SET nama_dokumen = '$nama_dokumen', kode_singkat = '$kode_singkat', keterangan = '$keterangan'
                                     WHERE id = '$id'")
                                     or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
    // cek query
    if ($query) {
        // jika berhasil tampilkan pesan berhasil ubah data.
        header('location: ../../../main.php?module=jenis_dokumen_bki&pesan=2');
    }
}
?>