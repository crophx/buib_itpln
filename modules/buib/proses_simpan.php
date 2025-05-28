<?php
session_start();

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    // alihkan ke halaman login dan tampilkan pesan peringatan login
    header('location: ../../login.php?pesan=2');
}

else{
    require_once "../../config/database.php";

    // mengecek data hasil submit dari form
    if (isset($_POST['simpan'])) {
        // ambil data hasil submit dari form
        $mitra              = mysqli_real_escape_string($mysqli, $_POST['mitra']);
        $no_dokumen         = mysqli_real_escape_string($mysqli, $_POST['no_dokumen']);
        $jenis_dokumen      = mysqli_real_escape_string($mysqli, $_POST['jenis_dokumen']);
        $target_nominal     = mysqli_real_escape_string($mysqli, trim($_POST['target_nominal']));
        $realisasi_nominal  = mysqli_real_escape_string($mysqli, trim($_POST['realisasi_nominal']));
        $tgl_upload         = mysqli_real_escape_string($mysqli, $_POST['tgl_upload']);

        // ambil data file dokumen elektronik hasil submit dari form
        $nama_file          = $_FILES['dokumen_buib']['name'];
        $ukuran_file        = $_FILES['dokumen_buib']['size'];
        $tipe_file          = $_FILES['dokumen_buib']['type'];
        $tmp_file           = $_FILES['dokumen_buib']['tmp_name'];
        // tentukan tipe file dokumen yang diperbolehkan
        $allowed_extensions = array('pdf');
        // seleksi tipe file dari input edoc
        $file_parts         = explode(".", $nama_file);
        $extension          = strtolower(end($file_parts));
        // enkripsi nama file
        $nama_file_enkripsi = sha1(md5(time() . $nama_file)) . '.' . $extension;
        // tentukan direktori penyimpanan file dokumen
        $path               = "../../dokumen/buib/" . $nama_file_enkripsi;

        // Convert date format from DD/MM/YYYY to MySQL format YYYY-MM-DD
        if (!empty($tgl_upload)) {
            $date_parts = explode('/', $tgl_upload);
            if (count($date_parts) == 3) {
                // Assuming format is DD/MM/YYYY
                $tgl_upload_mysql = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
            } else {
                // If not in expected format, use current date
                $tgl_upload_mysql = date('Y-m-d');
            }
        } else {
            $tgl_upload_mysql = date('Y-m-d');
        }

        // mengecek tipe file dan ukuran file dokumen sebelum diunggah
        // jika tipe file yang diunggah sesuai dengan "allowed_extensions"
        if (in_array($extension, $allowed_extensions)) {
            // jika ukuran file yang diunggah <= 10 Mb
            if ($ukuran_file <= 10000000) {
                // lakukan proses unggah file
                // jika file berhasil diunggah
                if (move_uploaded_file($tmp_file, $path)) {
                    // sql statement untuk insert data ke tabel "tbl_buib"
                    $insert = mysqli_query($mysqli, "INSERT INTO tbl_buib(mitra, no_dokumen, jenis_dokumen, target_nominal, realisasi_nominal, tgl_upload, dokumen_buib) 
                                                     VALUES('$mitra', '$no_dokumen', '$jenis_dokumen', '$target_nominal', '$realisasi_nominal', '$tgl_upload_mysql', '$nama_file_enkripsi')")
                                                     or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));
                    // cek query
                    // jika proses insert berhasil
                    if ($insert) {
                        header('location:../../main.php?module=buib&pesan=1');
                    }
                }
                else {
                    // gagal upload file
                    header('location:../../main.php?module=buib&pesan=6');
                }
            }
            else{
                // alihkan ke halaman arsip dan tampilkan pesan gagal unggah file
                header('location:../../main.php?module=buib&pesan=5');
            }
        }
        else{
            // alihkan ke halaman arsip dan tampilkan pesan gagal unggah file
            header('location: ../../main.php?module=buib&pesan=4');
        }

    }
}
?>