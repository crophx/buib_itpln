<?php
session_start();

// Cek apakah user sudah login
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    // alihkan ke halaman login dan tampilkan pesan peringatan login
    header('location: ../../login.php?pesan=2');
    exit();
}
else {
    require_once "../../config/database.php";

    // Fungsi untuk konversi format tanggal
    function convertDateFormat($tgl_input) {
        if (!empty($tgl_input)) {
            $date_parts = explode('/', $tgl_input);
            if (count($date_parts) == 3) {
                // Format yang diharapkan adalah DD/MM/YYYY
                return $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
            }
        }
        return date('Y-m-d');
    }

    // Cek apakah form realisasi yang disubmit
    if (isset($_POST['simpan_realisasi'])) {
    
        // === PROSES FORM REALISASI ===
        
        $nama_program    = mysqli_real_escape_string($mysqli, $_POST['nama_program']);
        $deputy_buib       = mysqli_real_escape_string($mysqli, $_POST['deputy_buib']);
        $realisasi_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['realisasi_nominal'])));
        $tgl_surat      = mysqli_real_escape_string($mysqli, $_POST['tgl_surat']);
        $status_buib      = mysqli_real_escape_string($mysqli, $_POST['status_buib']);
        $dokumen_rk_buib = mysqli_real_escape_string($mysqli, $_POST['dokumen_rk_buib']);
        $keterangan_program = mysqli_real_escape_string($mysqli, $_POST['keterangan_program']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_surat);

        // -- PERUBAHAN VALIDASI: Menggunakan !is_numeric() untuk nominal --
        if (empty($nama_program) || empty($deputy_buib) || !is_numeric($realisasi_nominal) || 
            empty($tgl_input_mysql) || empty($status_buib)) {
            header('location: ../../main.php?module=buib&pesan=7');
            exit();
        }

        // Insert data simpan realisasi
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_buib (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            deputy_buib,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            status_buib,
                                            dokumen_rk_buib
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            0, 
                                            '$realisasi_nominal', 
                                            '$tgl_input_mysql',
                                            '$deputy_buib',
                                            0,
                                            0,
                                            '$status_buib',
                                            '$dokumen_rk_buib'
                                        )")
                                        or die('Ada kesalahan pada query insert realisasi: ' . mysqli_error($mysqli));

        // Jika insert berhasil, arahkan ke halaman utama dengan pesan sukses
        if ($insert) {
            header('location: ../../main.php?module=buib&pesan=1');
        } else {
            header('location: ../../main.php?module=buib&pesan=2');
        }
    }

    // Cek apakah form TERKONTRAK yang disubmit
    elseif (isset($_POST['simpan_terkontrak'])) {
        
        // === PROSES FORM TERKONTRAK ===
        
        // Ambil data dari form TERKONTRAK
        $nama_program    = mysqli_real_escape_string($mysqli, $_POST['nama_program']);
        $deputy_buib       = mysqli_real_escape_string($mysqli, $_POST['deputy_buib']);
        $kontrak_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['kontrak_nominal'])));
        $tgl_surat      = mysqli_real_escape_string($mysqli, $_POST['tgl_surat']);
        $status_buib      = mysqli_real_escape_string($mysqli, $_POST['status_buib']);
        $dokumen_rk_buib = mysqli_real_escape_string($mysqli, $_POST['dokumen_rk_buib']);
        $keterangan_program = mysqli_real_escape_string($mysqli, $_POST['keterangan_program']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_surat);

        // -- PERUBAHAN VALIDASI: Menggunakan !is_numeric() untuk nominal --
        if (empty($nama_program) || empty($deputy_buib) || !is_numeric($kontrak_nominal) || 
            empty($tgl_input_mysql) || empty($status_buib)) {
            header('location: ../../main.php?module=buib&pesan=7');
            exit();
        }


        // Insert data simpan terkontrak
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_buib (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            deputy_buib,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            status_buib,
                                            dokumen_rk_buib
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            0, 
                                            0, 
                                            '$tgl_input_mysql',
                                            '$deputy_buib',
                                            '$kontrak_nominal',
                                            0,
                                            '$status_buib',
                                            '$dokumen_rk_buib'
                                        )")
                                        or die('Ada kesalahan pada query insert terkontrak: ' . mysqli_error($mysqli));

        // Jika insert berhasil, arahkan ke halaman utama dengan pesan sukses
        if ($insert) {
            header('location: ../../main.php?module=buib&pesan=1');
        } else {
            header('location: ../../main.php?module=buib&pesan=2');
        }

    }

    // Cek apakah form Ongoing yang disubmit
    elseif (isset($_POST['simpan_ongoing'])) {
        
        // === PROSES FORM ONGOING ===
        
        // Ambil data dari form ONGOING
        $nama_program    = mysqli_real_escape_string($mysqli, $_POST['nama_program']);
        $deputy_buib       = mysqli_real_escape_string($mysqli, $_POST['deputy_buib']);
        $ongoing_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['ongoing_nominal'])));
        $tgl_surat      = mysqli_real_escape_string($mysqli, $_POST['tgl_surat']);
        $status_buib      = mysqli_real_escape_string($mysqli, $_POST['status_buib']);
        $dokumen_rk_buib = mysqli_real_escape_string($mysqli, $_POST['dokumen_rk_buib']);
        $keterangan_program = mysqli_real_escape_string($mysqli, $_POST['keterangan_program']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_surat);

        // -- PERUBAHAN VALIDASI: Menggunakan !is_numeric() untuk nominal --
        if (empty($nama_program) || empty($deputy_buib) || !is_numeric($ongoing_nominal) || 
            empty($tgl_input_mysql) || empty($status_buib)) {
            header('location: ../../main.php?module=buib&pesan=7');
            exit();
        }


        // Insert data simpan onoging
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_buib (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            deputy_buib,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            status_buib,
                                            dokumen_rk_buib
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            0, 
                                            0, 
                                            '$tgl_input_mysql',
                                            '$deputy_buib',
                                            0,
                                            '$ongoing_nominal',
                                            '$status_buib',
                                            '$dokumen_rk_buib'
                                        )")
                                        or die('Ada kesalahan pada query insert ongoing: ' . mysqli_error($mysqli));

        // Jika insert berhasil, arahkan ke halaman utama dengan pesan sukses
        if ($insert) {
            header('location: ../../main.php?module=buib&pesan=1');
        } else {
            header('location: ../../main.php?module=buib&pesan=2');
        }

    }


    
    // Cek apakah form rencana kegiatan yang disubmit
    elseif (isset($_POST['simpan_rencana'])) {
        
        // === PROSES FORM RENCANA KEGIATAN ===
        
        // Ambil data dari form rencana kegiatan
        $nama_program    = mysqli_real_escape_string($mysqli, $_POST['nama_program']);
        $deputy_buib       = mysqli_real_escape_string($mysqli, $_POST['deputy_buib']);
        $target_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['target_nominal'])));
        $tgl_surat      = mysqli_real_escape_string($mysqli, $_POST['tgl_surat']);
        $status_buib      = mysqli_real_escape_string($mysqli, $_POST['status_buib']);
        $dokumen_rk_buib = mysqli_real_escape_string($mysqli, $_POST['dokumen_rk_buib']);
        $keterangan_program = mysqli_real_escape_string($mysqli, $_POST['keterangan_program']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_surat);

        // -- PERUBAHAN VALIDASI: Menggunakan !is_numeric() untuk nominal --
        if (empty($nama_program) || empty($deputy_buib) || !is_numeric($target_nominal) || 
            empty($tgl_input_mysql) || empty($status_buib)) {
            header('location: ../../main.php?module=buib&pesan=7');
            exit();
        }

        // Insert data simpan onoging
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_buib (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            deputy_buib,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            status_buib,
                                            dokumen_rk_buib
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            '$target_nominal', 
                                            0, 
                                            '$tgl_input_mysql',
                                            '$deputy_buib',
                                            0,
                                            0,
                                            '$status_buib',
                                            '$dokumen_rk_buib'
                                        )")
                                        or die('Ada kesalahan pada query insert rencana: ' . mysqli_error($mysqli));

        // Jika insert berhasil, arahkan ke halaman utama dengan pesan sukses
        if ($insert) {
            header('location: ../../main.php?module=buib&pesan=1');
        } else {
            header('location: ../../main.php?module=buib&pesan=2');
        }
    }
    
    // Jika tidak ada form yang disubmit atau form tidak dikenali
    else {
        header('location: ../../main.php?module=buib&pesan=2');
    }
}
?>