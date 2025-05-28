<?php
session_start();

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('location: ../../login.php?pesan=2');
}
else {
    require_once "../../config/database.php";

    if (isset($_POST['simpan'])) {
        $id_buib = mysqli_real_escape_string($mysqli, $_POST['id_buib']);
        $mitra = mysqli_real_escape_string($mysqli, $_POST['mitra']);
        $jenis_dokumen = mysqli_real_escape_string($mysqli, $_POST['jenis_dokumen']);
        $no_dokumen = mysqli_real_escape_string($mysqli, $_POST['no_dokumen']);
        $target_nominal = mysqli_real_escape_string($mysqli, $_POST['target_nominal']);
        $realisasi_nominal = mysqli_real_escape_string($mysqli, $_POST['realisasi_nominal']);
        $tgl_upload_input = mysqli_real_escape_string($mysqli, $_POST['tgl_upload']);
        $dokumen_lama = mysqli_real_escape_string($mysqli, $_POST['dokumen_lama']);
        
        // Konversi format tanggal dari dd/mm/yyyy ke yyyy-mm-dd
        $tgl_parts = explode('/', $tgl_upload_input);
        if (count($tgl_parts) == 3) {
            $tgl_upload = $tgl_parts[2] . '-' . $tgl_parts[1] . '-' . $tgl_parts[0];
        } else {
            $tgl_upload = $tgl_upload_input; // fallback jika format tidak sesuai
        }
        
        // Cek apakah ada file yang diupload
        $ada_file_baru = isset($_FILES['dokumen_buib']) && !empty($_FILES['dokumen_buib']['name']);
        
        if ($ada_file_baru) {
            // ambil data file dokumen elektronik hasil submit dari form
            $nama_file = $_FILES['dokumen_buib']['name'];
            $ukuran_file = $_FILES['dokumen_buib']['size'];
            $tipe_file = $_FILES['dokumen_buib']['type'];
            $tmp_file = $_FILES['dokumen_buib']['tmp_name'];
            
            // tentukan tipe file dokumen yang diperbolehkan
            $allowed_extensions = array('pdf');
            // seleksi tipe file dari input file
            $file_parts = explode(".", $nama_file);
            $extension = strtolower(end($file_parts));
            // enkripsi nama file
            $nama_file_enkripsi = sha1(md5(time() . $nama_file)) . '.' . $extension;
            // tentukan direktori penyimpanan file dokumen
            $path = "../../dokumen/buib/" . $nama_file_enkripsi;
        }

        // mengecek data file dokumen elektronik hasil submit dari form
        // jika dokumen elektronik tidak diubah (tidak ada file baru)
        if (!$ada_file_baru) {
            // sql statement untuk update data di tabel "tbl_buib" berdasarkan "id_buib"
            $update = mysqli_query($mysqli, "UPDATE tbl_buib
                                            SET mitra='$mitra', 
                                                jenis_dokumen='$jenis_dokumen', 
                                                no_dokumen='$no_dokumen', 
                                                target_nominal='$target_nominal', 
                                                realisasi_nominal='$realisasi_nominal', 
                                                tgl_upload='$tgl_upload'
                                            WHERE id_buib='$id_buib'")
                                            or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
            // cek query
            // jika proses update berhasil
            if ($update) {
                // alihkan ke halaman buib dan tampilkan pesan berhasil ubah data
                header('location: ../../main.php?module=buib&pesan=2');
            }
        }
        // jika ada dokumen elektronik baru yang diupload
        else {
            // mengecek tipe file dan ukuran file dokumen sebelum diunggah
            // jika tipe file yang diunggah sesuai dengan "allowed_extensions"
            if (in_array($extension, $allowed_extensions)) {
                // jika ukuran file yang diunggah <= 10 Mb
                if ($ukuran_file <= 10000000) {
                    // lakukan proses unggah file
                    // jika file berhasil diunggah
                    if (move_uploaded_file($tmp_file, $path)) {
                        // Hapus file lama jika ada
                        if (!empty($dokumen_lama) && file_exists("../../dokumen/buib/" . $dokumen_lama)) {
                            unlink("../../dokumen/buib/" . $dokumen_lama);
                        }
                        
                        // sql statement untuk update data di tabel "tbl_buib" berdasarkan "id_buib"
                        $update = mysqli_query($mysqli, "UPDATE tbl_buib
                                                        SET mitra='$mitra', 
                                                            jenis_dokumen='$jenis_dokumen', 
                                                            no_dokumen='$no_dokumen', 
                                                            target_nominal='$target_nominal', 
                                                            realisasi_nominal='$realisasi_nominal', 
                                                            tgl_upload='$tgl_upload', 
                                                            dokumen_buib='$nama_file_enkripsi'
                                                        WHERE id_buib='$id_buib'")
                                                        or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));
                        // cek query
                        // jika proses update berhasil
                        if ($update) {
                            // alihkan ke halaman buib dan tampilkan pesan berhasil ubah data
                            header('location: ../../main.php?module=buib&pesan=2');
                        }
                    }
                    // jika file gagal diunggah
                    else {
                        // alihkan ke halaman buib dan tampilkan pesan gagal unggah file
                        header('location: ../../main.php?module=buib&pesan=6');
                    }
                }
                // jika ukuran file yang diunggah lebih dari 10 Mb
                else {
                    // alihkan ke halaman buib dan tampilkan pesan gagal unggah file
                    header('location: ../../main.php?module=buib&pesan=5');
                }
            }
            // jika tipe file yang diunggah tidak sesuai dengan "allowed_extensions"
            else {
                // alihkan ke halaman buib dan tampilkan pesan gagal unggah file
                header('location: ../../main.php?module=buib&pesan=4');
            }
        }
    }
}
?>