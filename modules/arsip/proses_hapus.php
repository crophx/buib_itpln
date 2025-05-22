<?php
session_start();      // mengaktifkan session

// pengecekan session login user 
// jika user belum login
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    // alihkan ke halaman login dan tampilkan pesan peringatan login
    header('location: ../../login.php?pesan=2');
}
// jika user sudah login, maka jalankan perintah untuk delete
else {
    // panggil file "database.php" untuk koneksi ke database
    require_once "../../config/database.php";

    // mengecek data GET "id_arsip"
    if (isset($_GET['id'])) {
        // ambil data GET dari button hapus
        $id_arsip = mysqli_real_escape_string($mysqli, $_GET['id']);

        // sql statement untuk menampilkan data "dokumen_elektronik" dari tabel "tbl_arsip" berdasarkan "id_arsip"
        $query = mysqli_query($mysqli, "SELECT dokumen_elektronik FROM tbl_arsip WHERE id_arsip='$id_arsip'")
                                        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
        // ambil data hasil query
        $data = mysqli_fetch_assoc($query);

        // hapus file dokumen elektronik dari folder dokumen
        $hapus_file = unlink("../../dokumen/$data[dokumen_elektronik]");

        // sql statement untuk delete data dari tabel "tbl_arsip" berdasarkan "id_arsip"
        $delete = mysqli_query($mysqli, "DELETE FROM tbl_arsip WHERE id_arsip='$id_arsip'")
                                        or die('Ada kesalahan pada query delete : ' . mysqli_error($mysqli));
        // cek query
        // jika proses delete berhasil
        if ($delete) {
            // alihkan ke halaman arsip dan tampilkan pesan berhasil hapus data
            header('location: ../../main.php?module=arsip&pesan=3');
        }
}
}
