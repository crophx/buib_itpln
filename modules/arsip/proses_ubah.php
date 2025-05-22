<?php
session_start();      // mengaktifkan session

// pengecekan session login user 
// jika user belum login
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    // alihkan ke halaman login dan tampilkan pesan peringatan login
    header('location: ../../login.php?pesan=2');
}
// jika user sudah login, maka jalankan perintah untuk update
else {
    // panggil file "database.php" untuk koneksi ke database
    require_once "../../config/database.php";

    // mengecek data hasil submit dari form
    if (isset($_POST['simpan'])) {
        // ambil data hasil submit dari form
        $id_arsip       = mysqli_real_escape_string($mysqli, $_POST['id_arsip']);
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

        // mengecek data file dokumen elektronik hasil submit dari form
        // jika dokumen elektronik tidak diubah
        if (empty($nama_file)) {
            // sql statement untuk update data di tabel "tbl_arsip" berdasarkan "id_arsip"
            $update = mysqli_query($mysqli, "UPDATE tbl_arsip
                                            SET jenis_dokumen='$jenis_dokumen', bulan_tahun='$bulan_tahun', tahun_anggaran='$tahun_anggaran', dipa='$dipa'
                                            WHERE id_arsip='$id_arsip'")
                                            or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
            // cek query
            // jika proses update berhasil
            if ($update) {
                // alihkan ke halaman arsip dan tampilkan pesan berhasil ubah data
                header('location: ../../main.php?module=arsip&pesan=2');
            }
        }
        // jika dokumen elektronik diubah
        else {
            // mengecek tipe file dan ukuran file dokumen sebelum diunggah
            // jika tipe file yang diunggah sesuai dengan "allowed_extensions"
            if (in_array($extension, $allowed_extensions)) {
                // jika ukuran file yang diunggah <= 10 Mb
                if ($ukuran_file <= 10000000) {
                    // lakukan proses unggah file
                    // jika file berhasil diunggah
                    if (move_uploaded_file($tmp_file, $path)) {
                        // sql statement untuk update data di tabel "tbl_arsip" berdasarkan "id_arsip"
                        $update = mysqli_query($mysqli, "UPDATE tbl_arsip
                                                        SET jenis_dokumen='$jenis_dokumen', bulan_tahun='$bulan_tahun', tahun_anggaran='$tahun_anggaran', dipa='$dipa', dokumen_elektronik='$nama_file_enkripsi'
                                                        WHERE id_arsip='$id_arsip'")
                                                        or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
                        // cek query
                        // jika proses update berhasil
                        if ($update) {
                            // alihkan ke halaman arsip dan tampilkan pesan berhasil ubah data
                            header('location: ../../main.php?module=arsip&pesan=2');
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
}
