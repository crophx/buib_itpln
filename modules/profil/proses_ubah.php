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
        $nama    = mysqli_real_escape_string($mysqli, trim($_POST['nama']));
        $alamat  = mysqli_real_escape_string($mysqli, trim($_POST['alamat']));
        $telepon = mysqli_real_escape_string($mysqli, trim($_POST['telepon']));
        $email   = mysqli_real_escape_string($mysqli, trim($_POST['email']));
        $website = mysqli_real_escape_string($mysqli, trim($_POST['website']));

        // ambil data file hasil submit dari form
        $nama_file          = $_FILES['logo']['name'];
        $tmp_file           = $_FILES['logo']['tmp_name'];
        $extension          = array_pop(explode(".", $nama_file));
        // enkripsi nama file
        $nama_file_enkripsi = sha1(md5(time() . $nama_file)) . '.' . $extension;
        // tentukan direktori penyimpanan file logo
        $path               = "../../assets/img/" . $nama_file_enkripsi;

        // mengecek data logo dari form ubah data
        // jika data logo tidak ada (logo tidak diubah)
        if (empty($nama_file)) {
            // sql statement untuk update data di tabel "tbl_profil"
            $update = mysqli_query($mysqli, "UPDATE tbl_profil
                                            SET nama='$nama', alamat='$alamat', telepon='$telepon', email='$email', website='$website'")
                                            or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
            // cek query
            // jika proses update berhasil
            if ($update) {
                // alihkan ke halaman profil dan tampilkan pesan berhasil ubah data
                header('location: ../../main.php?module=profil&pesan=1');
            }
        }
        // jika data logo ada (logo diubah)
        else {
            // lakukan proses unggah file
            // jika file berhasil diunggah
            if (move_uploaded_file($tmp_file, $path)) {
                // sql statement untuk update data di tabel "tbl_profil"
                $update = mysqli_query($mysqli, "UPDATE tbl_profil
                                                SET nama='$nama', alamat='$alamat', telepon='$telepon', email='$email', website='$website', logo='$nama_file_enkripsi'")
                                                or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
                // cek query
                // jika proses update berhasil
                if ($update) {
                    // alihkan ke halaman profil dan tampilkan pesan berhasil ubah data
                    header('location: ../../main.php?module=profil&pesan=1');
                }
            }
        }
    }
}
