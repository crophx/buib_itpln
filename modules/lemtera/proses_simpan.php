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
        $entity_lemtera       = mysqli_real_escape_string($mysqli, $_POST['entity_lemtera']);
        $realisasi_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['realisasi_nominal'])));
        $tgl_surat      = mysqli_real_escape_string($mysqli, $_POST['tgl_surat']);
        $status_lemtera      = mysqli_real_escape_string($mysqli, $_POST['status_lemtera']);
        $keterangan_program = mysqli_real_escape_string($mysqli, $_POST['keterangan_program']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_surat);

        // Validasi input wajib
        if (empty($nama_program) || empty($entity_lemtera) || empty($realisasi_nominal) || 
            empty($tgl_input_mysql) || empty($status_lemtera) || empty($keterangan_program)) {
            header('location: ../../main.php?module=lemtera&pesan=7');
            exit();
        }

        // Validasi bahwa target terkontrak adalah angka
        if (!is_numeric($realisasi_nominal) || $realisasi_nominal <= 0) {
            header('location: ../../main.php?module=lemtera&pesan=8');
            exit();
        }


        // Cek apakah data sudah ada
        $cek_data = mysqli_query($mysqli, "SELECT id FROM tbl_rk_lemtera 
                                          WHERE nama_program = '$nama_program' 
                                          AND keterangan_program = '$keterangan_program'")
                                          or die('Error pada query cek data: ' . mysqli_error($mysqli));

        if (mysqli_num_rows($cek_data) > 0) {
            header('location: ../../main.php?module=rencana&pesan=9');
            exit();
        }

        // Insert data simpan realisasi
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_lemtera (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            entity_lemtera,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            jml_peserta,
                                            tempat_kegiatan,
                                            status_lemtera
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            0, 
                                            '$realisasi_nominal', 
                                            '$tgl_input_mysql',
                                            '$entity_lemtera',
                                            0,
                                            0,
                                            0,
                                            '',
                                            '$status_lemtera'
                                        )")
                                        or die('Ada kesalahan pada query insert realisasi: ' . mysqli_error($mysqli));

        // Jika insert berhasil, arahkan ke halaman utama dengan pesan sukses
        if ($insert) {
            header('location: ../../main.php?module=lemtera&pesan=1');
        } else {
            header('location: ../../main.php?module=lemtera&pesan=2');
        }
    }

    // Cek apakah form TERKONTRAK yang disubmit
    elseif (isset($_POST['simpan_terkontrak'])) {
        
        // === PROSES FORM TERKONTRAK ===
        
        // Ambil data dari form TERKONTRAK
        $nama_program    = mysqli_real_escape_string($mysqli, $_POST['nama_program']);
        $entity_lemtera       = mysqli_real_escape_string($mysqli, $_POST['entity_lemtera']);
        $kontrak_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['kontrak_nominal'])));
        $tgl_surat      = mysqli_real_escape_string($mysqli, $_POST['tgl_surat']);
        $status_lemtera      = mysqli_real_escape_string($mysqli, $_POST['status_lemtera']);
        $keterangan_program = mysqli_real_escape_string($mysqli, $_POST['keterangan_program']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_surat);

        // Validasi input wajib
        if (empty($nama_program) || empty($entity_lemtera) || empty($kontrak_nominal) || 
            empty($tgl_input_mysql) || empty($status_lemtera) || empty($keterangan_program)) {
            header('location: ../../main.php?module=lemtera&pesan=7');
            exit();
        }

        // Validasi bahwa target terkontrak adalah angka
        if (!is_numeric($kontrak_nominal) || $kontrak_nominal <= 0) {
            header('location: ../../main.php?module=lemtera&pesan=8');
            exit();
        }


        // Cek apakah data sudah ada
        $cek_data = mysqli_query($mysqli, "SELECT id FROM tbl_rk_lemtera 
                                          WHERE nama_program = '$nama_program' 
                                          AND keterangan_program = '$keterangan_program'")
                                          or die('Error pada query cek data: ' . mysqli_error($mysqli));

        if (mysqli_num_rows($cek_data) > 0) {
            header('location: ../../main.php?module=rencana&pesan=9');
            exit();
        }

        // Insert data simpan terkontrak
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_lemtera (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            entity_lemtera,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            jml_peserta,
                                            tempat_kegiatan,
                                            status_lemtera
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            0, 
                                            0, 
                                            '$tgl_input_mysql',
                                            '$entity_lemtera',
                                            '$kontrak_nominal',
                                            0,
                                            0,
                                            '',
                                            '$status_lemtera'
                                        )")
                                        or die('Ada kesalahan pada query insert terkontrak: ' . mysqli_error($mysqli));

        // Jika insert berhasil, arahkan ke halaman utama dengan pesan sukses
        if ($insert) {
            header('location: ../../main.php?module=lemtera&pesan=1');
        } else {
            header('location: ../../main.php?module=lemtera&pesan=2');
        }

    }

    // Cek apakah form Ongoing yang disubmit
    elseif (isset($_POST['simpan_ongoing'])) {
        
        // === PROSES FORM ONGOING ===
        
        // Ambil data dari form ONGOING
        $nama_program    = mysqli_real_escape_string($mysqli, $_POST['nama_program']);
        $entity_lemtera       = mysqli_real_escape_string($mysqli, $_POST['entity_lemtera']);
        $ongoing_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['ongoing_nominal'])));
        $tgl_surat      = mysqli_real_escape_string($mysqli, $_POST['tgl_surat']);
        $status_lemtera      = mysqli_real_escape_string($mysqli, $_POST['status_lemtera']);
        $keterangan_program = mysqli_real_escape_string($mysqli, $_POST['keterangan_program']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_surat);

        // Validasi input wajib
        if (empty($nama_program) || empty($ongoing_nominal) || 
            empty($tgl_input_mysql) || empty($status_lemtera) || empty($keterangan_program)) {
            header('location: ../../main.php?module=lemtera&pesan=7');
            exit();
        }

        // Validasi bahwa target ongoing adalah angka
        if (!is_numeric($ongoing_nominal) || $ongoing_nominal <= 0) {
            header('location: ../../main.php?module=lemtera&pesan=8');
            exit();
        }


        // Cek apakah data sudah ada
        $cek_data = mysqli_query($mysqli, "SELECT id FROM tbl_rk_lemtera 
                                          WHERE nama_program = '$nama_program' 
                                          AND keterangan_program = '$keterangan_program'")
                                          or die('Error pada query cek data: ' . mysqli_error($mysqli));

        if (mysqli_num_rows($cek_data) > 0) {
            header('location: ../../main.php?module=rencana&pesan=9');
            exit();
        }

        // Insert data simpan onoging
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_lemtera (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            entity_lemtera,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            jml_peserta,
                                            tempat_kegiatan,
                                            status_lemtera
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            0, 
                                            0, 
                                            '$tgl_input_mysql',
                                            '$entity_lemtera',
                                            0,
                                            '$ongoing_nominal',
                                            0,
                                            '',
                                            '$status_lemtera'
                                        )")
                                        or die('Ada kesalahan pada query insert terkontrak: ' . mysqli_error($mysqli));

        // Jika insert berhasil, arahkan ke halaman utama dengan pesan sukses
        if ($insert) {
            header('location: ../../main.php?module=lemtera&pesan=1');
        } else {
            header('location: ../../main.php?module=lemtera&pesan=2');
        }

    }


    
    // Cek apakah form rencana kegiatan yang disubmit
    elseif (isset($_POST['simpan_rencana'])) {
        
        // === PROSES FORM RENCANA KEGIATAN ===
        
        // Ambil data dari form rencana kegiatan
        $nama_program    = mysqli_real_escape_string($mysqli, $_POST['nama_program']);
        $kegiatan       = mysqli_real_escape_string($mysqli, $_POST['kegiatan']);
        $target_nominal = mysqli_real_escape_string($mysqli, str_replace(['.', ','], '', trim($_POST['target_nominal'])));
        $bulan          = mysqli_real_escape_string($mysqli, $_POST['bulan']);
        $tahun          = mysqli_real_escape_string($mysqli, $_POST['tahun']);
        $tgl_input      = mysqli_real_escape_string($mysqli, $_POST['tgl_input']);

        // Konversi format tanggal
        $tgl_input_mysql = convertDateFormat($tgl_input);

        // Validasi input wajib
        if (empty($nama_program) || empty($kegiatan) || empty($target_nominal) || 
            empty($bulan) || empty($tahun) || empty($tgl_input)) {
            header('location: ../../main.php?module=lemtera&pesan=7');
            exit();
        }

        // Validasi bahwa target nominal adalah angka
        if (!is_numeric($target_nominal) || $target_nominal <= 0) {
            header('location: ../../main.php?module=lemtera&pesan=8');
            exit();
        }

        // Buat keterangan program
        $keterangan_program = $kegiatan;

        // Cek apakah data sudah ada
        $cek_data = mysqli_query($mysqli, "SELECT id FROM tbl_rk_lemtera 
                                          WHERE nama_program = '$nama_program' 
                                          AND keterangan_program = '$keterangan_program'")
                                          or die('Error pada query cek data: ' . mysqli_error($mysqli));

        if (mysqli_num_rows($cek_data) > 0) {
            header('location: ../../main.php?module=rencana&pesan=9');
            exit();
        }

        // Insert data rencana kegiatan
        $insert = mysqli_query($mysqli, "INSERT INTO tbl_rk_lemtera (
                                            nama_program,
                                            keterangan_program, 
                                            target_nominal, 
                                            realisasi_nominal, 
                                            tgl_surat,
                                            entity_lemtera,
                                            kontrak_nominal,
                                            ongoing_nominal,
                                            jml_peserta,
                                            tempat_kegiatan,
                                            status_lemtera
                                        ) VALUES (
                                            '$nama_program',
                                            '$keterangan_program', 
                                            0, 
                                            '$realisasi_nominal', 
                                            '$tgl_input_mysql',
                                            1,
                                            0,
                                            0,
                                            0,
                                            '',
                                            1
                                        )")
                                        or die('Ada kesalahan pada query insert realisasi: ' . mysqli_error($mysqli));

        if ($insert) {
            header('location: ../../main.php?module=lemtera&pesan=1');
        } else {
            header('location: ../../main.php?module=lemtera&pesan=3');
        }
    }
    
    // Jika tidak ada form yang disubmit atau form tidak dikenali
    else {
        header('location: ../../main.php?module=lemtera&pesan=2');
    }
}
?>