<?php
// Memulai session untuk menyimpan pesan notifikasi
session_start();

// Cek status login pengguna
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('location: ../../login.php?pesan=2');
    exit(); // Tambahkan exit setelah header untuk menghentikan eksekusi
} else {
    // DITAMBAHKAN: Panggil file koneksi database di sini
    require_once '../../config/database.php'; 

    // Pastikan hanya bisa diakses jika tombol "ubah KONTRAK" ditekan
    if (isset($_POST['ubahKontrak'])) {
        $id = (int)$_POST['id'];

        // Membersihkan data nominal sebelum disimpan
        $kontrak_nominal_clean = (int)preg_replace("/[^0-9]/", "", $_POST['kontrak_nominal']);
        $realisasi_nominal_clean = (int)preg_replace("/[^0-9]/", "", $_POST['realisasi_nominal']);

        // Membersihkan data lainnya
        $nama_program = mysqli_real_escape_string($mysqli, trim($_POST['nama_program']));
        $kategori_tc = mysqli_real_escape_string($mysqli, trim($_POST['kategori_tc']));
        $tgl_surat = mysqli_real_escape_string($mysqli, trim($_POST['tgl_surat']));
        $status_tc = mysqli_real_escape_string($mysqli, trim($_POST['status_tc']));
        $keterangan_program = mysqli_real_escape_string($mysqli, trim($_POST['keterangan_program']));

        // Query update dengan data yang sudah dibersihkan
        $update = mysqli_query($mysqli, "UPDATE tbl_rk_training_center 
                                          SET 
                                            nama_program        = '$nama_program',
                                            kategori_tc         = '$kategori_tc',
                                            kontrak_nominal     = '$kontrak_nominal_clean',
                                            tgl_surat           = '$tgl_surat',
                                            status_tc           = '$status_tc',
                                            realisasi_nominal   = '$realisasi_nominal_clean',
                                            keterangan_program  = '$keterangan_program'
                                          WHERE id = '$id'")
                                          or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));

        // Cek hasil eksekusi query dan berikan notifikasi
        if ($update) {
            // Jika berhasil, alihkan dengan pesan sukses
            header('location: ../../main.php?module=training_center&pesan=2');
            exit(); // Pastikan exit dipanggil setelah header
        } else {
            // Jika gagal, alihkan dengan pesan error
            header('location: ../../main.php?module=training_center&pesan=4');
            exit(); // Pastikan exit dipanggil setelah header
        } 
    
    //ubah Ongoing

    } else if (isset($_POST['ubahOngoing'])) {
        $id = (int)$_POST['id'];

        // Membersihkan data nominal sebelum disimpan
        $ongoing_nominal_clean = (int)preg_replace("/[^0-9]/", "", $_POST['ongoing_nominal']);
        $kontrak_nominal_clean = (int)preg_replace("/[^0-9]/", "", $_POST['kontrak_nominal']);
        $realisasi_nominal_clean = (int)preg_replace("/[^0-9]/", "", $_POST['realisasi_nominal']);

        // Membersihkan data lainnya
        $nama_program = mysqli_real_escape_string($mysqli, trim($_POST['nama_program']));
        $kategori_tc = mysqli_real_escape_string($mysqli, trim($_POST['kategori_tc']));
        $tgl_surat = mysqli_real_escape_string($mysqli, trim($_POST['tgl_surat']));
        $status_tc = mysqli_real_escape_string($mysqli, trim($_POST['status_tc']));
        $keterangan_program = mysqli_real_escape_string($mysqli, trim($_POST['keterangan_program']));

        // Query update dengan data yang sudah dibersihkan
        $update = mysqli_query($mysqli, "UPDATE tbl_rk_training_center 
                                          SET 
                                            nama_program        = '$nama_program',
                                            kategori_tc         = '$kategori_tc',
                                            ongoing_nominal     = '$ongoing_nominal_clean',
                                            tgl_surat           = '$tgl_surat',
                                            status_tc           = '$status_tc',
                                            kontrak_nominal     = '$kontrak_nominal_clean',
                                            realisasi_nominal   = '$realisasi_nominal_clean',
                                            keterangan_program  = '$keterangan_program'
                                          WHERE id = '$id'")
                                          or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));

        // Cek hasil eksekusi query dan berikan notifikasi
        if ($update) {
            // Jika berhasil, alihkan dengan pesan sukses
            header('location: ../../main.php?module=training_center&pesan=2');
            exit(); // Pastikan exit dipanggil setelah header
        } else {
            // Jika gagal, alihkan dengan pesan error
            header('location: ../../main.php?module=training_center&pesan=4');
            exit(); // Pastikan exit dipanggil setelah header
        }
    
    //ubah Realisasi
    } else if (isset($_POST['ubahRealisasi'])) {
        $id = (int)$_POST['id'];

        // Membersihkan data nominal sebelum disimpan

        $realisasi_nominal_clean = (int)preg_replace("/[^0-9]/", "", $_POST['realisasi_nominal']);

        // Membersihkan data lainnya
        $nama_program = mysqli_real_escape_string($mysqli, trim($_POST['nama_program']));
        $kategori_tc = mysqli_real_escape_string($mysqli, trim($_POST['kategori_tc']));
        $tgl_surat = mysqli_real_escape_string($mysqli, trim($_POST['tgl_surat']));
        $status_tc = mysqli_real_escape_string($mysqli, trim($_POST['status_tc']));
        $keterangan_program = mysqli_real_escape_string($mysqli, trim($_POST['keterangan_program']));

        // Query update dengan data yang sudah dibersihkan
        $update = mysqli_query($mysqli, "UPDATE tbl_rk_training_center 
                                          SET 
                                            nama_program        = '$nama_program',
                                            kategori_tc         = '$kategori_tc',
                                            tgl_surat           = '$tgl_surat',
                                            status_tc           = '$status_tc',
                                            realisasi_nominal   = '$realisasi_nominal_clean',
                                            keterangan_program  = '$keterangan_program'
                                          WHERE id = '$id'")
                                          or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));

        // Cek hasil eksekusi query dan berikan notifikasi
        if ($update) {
            // Jika berhasil, alihkan dengan pesan sukses
            header('location: ../../main.php?module=training_center&pesan=2');
            exit(); // Pastikan exit dipanggil setelah header
        } else {
            // Jika gagal, alihkan dengan pesan error
            header('location: ../../main.php?module=training_center&pesan=4');
            exit(); // Pastikan exit dipanggil setelah header
        }
    
    //ubah Rencana
    } else if (isset($_POST['ubahRencana'])) {
        $id = (int)$_POST['id'];

        // Membersihkan data nominal sebelum disimpan

        $target_nominal_clean = (int)preg_replace("/[^0-9]/", "", $_POST['target_nominal']);

        // Membersihkan data lainnya
        $nama_program = mysqli_real_escape_string($mysqli, trim($_POST['nama_program']));
        //$kategori_tc = mysqli_real_escape_string($mysqli, trim($_POST['kategori_tc']));
        $tgl_surat = mysqli_real_escape_string($mysqli, trim($_POST['tgl_surat']));
        //$status_tc = mysqli_real_escape_string($mysqli, trim($_POST['status_tc']));
        $keterangan_program = mysqli_real_escape_string($mysqli, trim($_POST['keterangan_program']));

        // Query update dengan data yang sudah dibersihkan
        $update = mysqli_query($mysqli, "UPDATE tbl_rk_training_center 
                                          SET 
                                            nama_program        = '$nama_program',
                                            tgl_surat           = '$tgl_surat',
                                            target_nominal     = '$target_nominal_clean',
                                            keterangan_program  = '$keterangan_program'
                                          WHERE id = '$id'")
                                          or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));

        // Cek hasil eksekusi query dan berikan notifikasi
        if ($update) {
            // Jika berhasil, alihkan dengan pesan sukses
            header('location: ../../main.php?module=training_center&pesan=2');
            exit(); // Pastikan exit dipanggil setelah header
        } else {
            // Jika gagal, alihkan dengan pesan error
            header('location: ../../main.php?module=training_center&pesan=4');
            exit(); // Pastikan exit dipanggil setelah header
        }
    }

    else {
        // Jika file diakses langsung tanpa menekan tombol, kembalikan ke halaman utama
        header('Location: ../../main.php');
        exit();
    }
}
?>