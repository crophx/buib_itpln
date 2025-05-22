<?php
session_start();      // mengaktifkan session

// pengecekan session login user 
// jika user belum login
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    // alihkan ke halaman login dan tampilkan pesan peringatan login
    header('location: ../../login.php?pesan=2');
}
// jika user sudah login, maka jalankan perintah untuk insert
else {
    // panggil file "database.php" untuk koneksi ke database
    require_once "../../config/database.php";

    // mengecek data hasil submit dari form
    if (isset($_POST['simpan'])) {
        // ambil data hasil submit dari form
        $jenis_dokumen  = mysqli_real_escape_string($mysqli, $_POST['jenis_dokumen']);
        $bulan          = mysqli_real_escape_string($mysqli, $_POST['bulan']);
        $tahun          = mysqli_real_escape_string($mysqli, trim($_POST['tahun']));
        $tahun_anggaran = mysqli_real_escape_string($mysqli, trim($_POST['tahun_anggaran']));
        $dipa           = mysqli_real_escape_string($mysqli, $_POST['dipa']);

        // gabungkan data bulan dan tahun sebelum disimpan ke database
        $bulan_tahun = $bulan . ' ' . $tahun;

        // ambil data file dokumen elektronik hasil submit dari form
        $nama_file          = $_FILES['dokumen_elektronik']['name'];
        $ukuran_file        = $_FILES['dokumen_elektronik']['size'];
        $tipe_file          = $_FILES['dokumen_elektronik']['type'];
        $tmp_file           = $_FILES['dokumen_elektronik']['tmp_name'];
        // tentukan tipe file dokumen yang diperbolehkan
        $allowed_extensions = array('pdf');
        // seleksi tipe file dari input edoc
        $extension          = array_pop(explode(".", $nama_file));
        // enkripsi nama file
        $nama_file_enkripsi = sha1(md5(time() . $nama_file)) . '.' . $extension;
        // tentukan direktori penyimpanan file dokumen
        $path               = "../../dokumen/" . $nama_file_enkripsi;

        // mengecek tipe file dan ukuran file dokumen sebelum diunggah
        // jika tipe file yang diunggah sesuai dengan "allowed_extensions"
        if (in_array($extension, $allowed_extensions)) {
            // jika ukuran file yang diunggah <= 10 Mb
            if ($ukuran_file <= 10000000) {
                // lakukan proses unggah file
                // jika file berhasil diunggah
                if (move_uploaded_file($tmp_file, $path)) {
                    // sql statement untuk insert data ke tabel "tbl_arsip"
                    $insert = mysqli_query($mysqli, "INSERT INTO tbl_arsip(jenis_dokumen, bulan_tahun, tahun_anggaran, dipa, dokumen_elektronik) 
                                                     VALUES('$jenis_dokumen', '$bulan_tahun', '$tahun_anggaran', '$dipa', '$nama_file_enkripsi')")
                                                     or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));
                    // cek query
                    // jika proses insert berhasil
                    if ($insert) {
                        // alihkan ke halaman arsip dan tampilkan pesan berhasil simpan data
                        header('location: ../../main.php?module=arsip&pesan=1');
                    }
                }
            }
            // jika ukuran file yang diunggah lebih dari 10 Mb
            else {
                // alihkan ke halaman arsip dan tampilkan pesan gagal unggah file
                header('location: ../../main.php?module=arsip&pesan=5');
            }
        }
        // jika tipe file yang diunggah tidak sesuai dengan "allowed_extensions"
        else {
            // alihkan ke halaman arsip dan tampilkan pesan gagal unggah file
            header('location: ../../main.php?module=arsip&pesan=4');
        }
    }
}
