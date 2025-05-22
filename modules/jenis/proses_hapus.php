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

    // mengecek data GET "id_jenis"
    if (isset($_GET['id'])) {
        // ambil data GET dari button hapus
        $id_jenis = mysqli_real_escape_string($mysqli, $_GET['id']);

        // mengecek data jenis untuk mencegah penghapusan data jenis dokumen yang sudah digunakan di tabel "tbl_arsip"
        // sql statement untuk menampilkan data "jenis_dokumen" dari tabel "tbl_arsip" berdasarkan input "id_jenis"
        $query = mysqli_query($mysqli, "SELECT jenis_dokumen FROM tbl_arsip WHERE jenis_dokumen='$id_jenis'")
                                        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
        // ambil jumlah baris data hasil query
        $rows = mysqli_num_rows($query);

        // cek hasil query
        // jika data jenis sudah ada di tabel "tbl_arsip"
        if ($rows <> 0) {
            // alihkan ke halaman jenis dan tampilkan pesan gagal hapus data
            header('location: ../../main.php?module=jenis&pesan=5');
        }
        // jika data jenis belum ada di tabel "tbl_arsip"
        else {
            // sql statement untuk delete data dari tabel "tbl_jenis" berdasarkan "id_jenis"
            $delete = mysqli_query($mysqli, "DELETE FROM tbl_jenis WHERE id_jenis='$id_jenis'")
                                            or die('Ada kesalahan pada query delete : ' . mysqli_error($mysqli));
            // cek query
            // jika proses delete berhasil
            if ($delete) {
                // alihkan ke halaman jenis dan tampilkan pesan berhasil hapus data
                header('location: ../../main.php?module=jenis&pesan=3');
            }
        }
    }
}
