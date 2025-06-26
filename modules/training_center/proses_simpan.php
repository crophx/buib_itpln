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
        
        // Ambil data dari form realisasi
        $nama_program    = mysqli_real_escape_string($mysqli, $_POST['nama_program']);
        $kategori_tc       = mysqli_real_escape_string($mysqli, $_POST['kategori_tc']);
        $realisasi_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['realisasi_nominal'])));
        $jml_peserta = mysqli_real_escape_string($mysqli, $_POST['jml_peserta']);
        $tempat_kegiatan = mysqli_real_escape_string($mysqli, $_POST['tempat_kegiatan']);
        $tgl_surat      = mysqli_real_escape_string($mysqli, $_POST['tgl_surat']);
        $status_tc      = mysqli_real_escape_string($mysqli, $_POST['status_tc']);
        $keterangan_program = mysqli_real_escape_string($mysqli, $_POST['keterangan_program']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_surat);

        // Validasi input wajib
        if (empty($nama_program) || empty($kategori_tc) || empty($realisasi_nominal) || empty($jml_peserta) ||
            empty($tempat_kegiatan) || empty($tgl_input_mysql) || empty($status_tc) || empty($keterangan_program)) {
            header('location: ../../main.php?module=training_center&pesan=7');
            exit();
        }

        // Validasi bahwa target ongoing adalah angka
        if (!is_numeric($realisasi_nominal) || $realisasi_nominal <= 0) {
            header('location: ../../main.php?module=training_center&pesan=8');
            exit();
        }


        // Cek apakah data sudah ada
        $cek_data = mysqli_query($mysqli, "SELECT id FROM tbl_rk_training_center 
                                          WHERE nama_program = '$nama_program' 
                                          AND keterangan_program = '$keterangan_program'")
                                          or die('Error pada query cek data: ' . mysqli_error($mysqli));

        if (mysqli_num_rows($cek_data) > 0) {
            header('location: ../../main.php?module=rencana&pesan=9');
            exit();
        }

        // Insert data realisasi
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_training_center (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            kategori_tc,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            jml_peserta,
                                            tempat_kegiatan,
                                            status_tc
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            0, 
                                            '$realisasi_nominal', 
                                            '$tgl_input_mysql',
                                            '$kategori_tc',
                                            0,
                                            0,
                                            '$jml_peserta',
                                            '$tempat_kegiatan',
                                            '$status_tc'
                                        )")
                                        or die('Ada kesalahan pada query insert realisasi: ' . mysqli_error($mysqli));

        if ($insert) {
            header('location: ../../main.php?module=training_center&pesan=1');
        } else {
            header('location: ../../main.php?module=training_center&pesan=3');
        }
    }

    // Cek apakah form TERKONTRAK yang disubmit
    elseif (isset($_POST['simpan_terkontrak'])) {
        
        // === PROSES FORM TERKONTRAK ===
        
        // Ambil data dari form TERKONTRAK
        $nama_program    = mysqli_real_escape_string($mysqli, $_POST['nama_program']);
        $kategori_tc       = mysqli_real_escape_string($mysqli, $_POST['kategori_tc']);
        $kontrak_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['kontrak_nominal'])));
        $tgl_surat      = mysqli_real_escape_string($mysqli, $_POST['tgl_surat']);
        $status_tc      = mysqli_real_escape_string($mysqli, $_POST['status_tc']);
        $keterangan_program = mysqli_real_escape_string($mysqli, $_POST['keterangan_program']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_surat);

        // Validasi input wajib
        if (empty($nama_program) || empty($kategori_tc) || empty($kontrak_nominal) || 
            empty($tgl_input_mysql) || empty($status_tc) || empty($keterangan_program)) {
            header('location: ../../main.php?module=training_center&pesan=7');
            exit();
        }

        // Validasi bahwa target terkontrak adalah angka
        if (!is_numeric($kontrak_nominal) || $kontrak_nominal <= 0) {
            header('location: ../../main.php?module=training_center&pesan=8');
            exit();
        }


        // Cek apakah data sudah ada
        $cek_data = mysqli_query($mysqli, "SELECT id FROM tbl_rk_training_center 
                                          WHERE nama_program = '$nama_program' 
                                          AND keterangan_program = '$keterangan_program'")
                                          or die('Error pada query cek data: ' . mysqli_error($mysqli));

        if (mysqli_num_rows($cek_data) > 0) {
            header('location: ../../main.php?module=rencana&pesan=9');
            exit();
        }

        // Insert data simpan terkontrak
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_training_center (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            kategori_tc,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            jml_peserta,
                                            tempat_kegiatan,
                                            status_tc
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            0, 
                                            0, 
                                            '$tgl_input_mysql',
                                            '$kategori_tc',
                                            '$kontrak_nominal',
                                            0,
                                            0,
                                            '',
                                            '$status_tc'
                                        )")
                                        or die('Ada kesalahan pada query insert terkontrak: ' . mysqli_error($mysqli));

        // Jika insert berhasil, arahkan ke halaman utama dengan pesan sukses
        if ($insert) {
            header('location: ../../main.php?module=training_center&pesan=1');
        } else {
            header('location: ../../main.php?module=training_center&pesan=2');
        }

    }

    // Cek apakah form Ongoing yang disubmit
    elseif (isset($_POST['simpan_ongoing'])) {
        
        // === PROSES FORM ONGOING ===
        
        // Ambil data dari form ONGOING
        $nama_program    = mysqli_real_escape_string($mysqli, $_POST['nama_program']);
        $kategori_tc       = mysqli_real_escape_string($mysqli, $_POST['kategori_tc']);
        $ongoing_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['ongoing_nominal'])));
        $tgl_surat      = mysqli_real_escape_string($mysqli, $_POST['tgl_surat']);
        $status_tc      = mysqli_real_escape_string($mysqli, $_POST['status_tc']);
        $keterangan_program = mysqli_real_escape_string($mysqli, $_POST['keterangan_program']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_surat);

        // Validasi input wajib
        if (empty($nama_program) || empty($kategori_tc) || empty($ongoing_nominal) || 
            empty($tgl_input_mysql) || empty($status_tc) || empty($keterangan_program)) {
            header('location: ../../main.php?module=training_center&pesan=7');
            exit();
        }

        // Validasi bahwa target ongoing adalah angka
        if (!is_numeric($ongoing_nominal) || $ongoing_nominal <= 0) {
            header('location: ../../main.php?module=training_center&pesan=8');
            exit();
        }


        // Cek apakah data sudah ada
        $cek_data = mysqli_query($mysqli, "SELECT id FROM tbl_rk_training_center 
                                          WHERE nama_program = '$nama_program' 
                                          AND keterangan_program = '$keterangan_program'")
                                          or die('Error pada query cek data: ' . mysqli_error($mysqli));

        if (mysqli_num_rows($cek_data) > 0) {
            header('location: ../../main.php?module=rencana&pesan=9');
            exit();
        }

        // Insert data simpan onoging
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_training_center (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            kategori_tc,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            jml_peserta,
                                            tempat_kegiatan,
                                            status_tc
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            0, 
                                            0, 
                                            '$tgl_input_mysql',
                                            '$kategori_tc',
                                            0,
                                            '$ongoing_nominal',
                                            0,
                                            '',
                                            '$status_tc'
                                        )")
                                        or die('Ada kesalahan pada query insert terkontrak: ' . mysqli_error($mysqli));

        // Jika insert berhasil, arahkan ke halaman utama dengan pesan sukses
        if ($insert) {
            header('location: ../../main.php?module=training_center&pesan=1');
        } else {
            header('location: ../../main.php?module=training_center&pesan=2');
        }

    }


    
    // Cek apakah form rencana kegiatan yang disubmit
    elseif (isset($_POST['simpan_rencana'])) {
        
        // === PROSES FORM RENCANA KEGIATAN ===
        
        // Ambil data dari form rencana kegiatan
        $nama_program    = mysqli_real_escape_string($mysqli, $_POST['nama_program']);
        $target_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['target_nominal'])));
        $tgl_surat      = mysqli_real_escape_string($mysqli, $_POST['tgl_surat']);
        $status_tc      = mysqli_real_escape_string($mysqli, $_POST['status_tc']);
        $keterangan_program = mysqli_real_escape_string($mysqli, $_POST['keterangan_program']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_surat);

        // Validasi input wajib
        if (empty($nama_program) ||  empty($target_nominal) || 
            empty($tgl_input_mysql) || empty($status_tc) || empty($keterangan_program)) {
            header('location: ../../main.php?module=tc&pesan=7');
            exit();
        }

        // Validasi bahwa target ongoing adalah angka
        if (!is_numeric($target_nominal) || $target_nominal <= 0) {
            header('location: ../../main.php?module=tc&pesan=8');
            exit();
        }


        // Cek apakah data sudah ada
        $cek_data = mysqli_query($mysqli, "SELECT id FROM tbl_rk_training_center 
                                          WHERE nama_program = '$nama_program' 
                                          AND keterangan_program = '$keterangan_program'")
                                          or die('Error pada query cek data: ' . mysqli_error($mysqli));

        if (mysqli_num_rows($cek_data) > 0) {
            header('location: ../../main.php?module=rencana&pesan=9');
            exit();
        }

        // Insert data simpan onoging
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_training_center (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            kategori_tc,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            jml_peserta,
                                            tempat_kegiatan,
                                            status_tc
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            '$target_nominal', 
                                            0, 
                                            '$tgl_input_mysql',
                                            1,
                                            0,
                                            0,
                                            0,
                                            '',
                                            '$status_tc'
                                        )")
                                        or die('Ada kesalahan pada query insert rencana: ' . mysqli_error($mysqli));

        // Jika insert berhasil, arahkan ke halaman utama dengan pesan sukses
        if ($insert) {
            header('location: ../../main.php?module=training_center&pesan=1');
        } else {
            header('location: ../../main.php?module=training_center&pesan=2');
        }
    }
    
    // Jika tidak ada form yang disubmit atau form tidak dikenali
    else {
        header('location: ../../main.php?module=training_center&pesan=2');
    }
}
?>