<?php
// Mulai session untuk menyimpan pesan notifikasi
session_start();

// Panggil file koneksi database
require_once "../../config/database.php";

// Mencegah akses langsung, tetapi izinkan permintaan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location: ../../404.html');
    exit();
}
// Jika file di-include, jalankan proses
else {
    // Proses untuk Tambah Data (Create)
    // Cek jika tombol 'simpan' ditekan dan berasal dari form
    if (isset($_POST['simpan'])) {
        // Ambil data dari form dan sanitasi input
        $nama_entity_lemtera = mysqli_real_escape_string($mysqli, trim($_POST['nama_entity_lemtera']));

        // Cek apakah kategori sudah ada
        $query_cek = mysqli_query($mysqli, "SELECT nama_entity_lemtera FROM tbl_entity_lemtera WHERE nama_entity_lemtera = '$nama_entity_lemtera'");
        
        // Jika kategori sudah ada, kirim notifikasi error
        if (mysqli_num_rows($query_cek) > 0) {
            $_SESSION['alert'] = [
                'type'    => 'error',
                'message' => 'Nama kategori ' . htmlspecialchars($nama_entity_lemtera) . ' sudah ada.'
            ];
        } 
        // Jika kategori belum ada, lakukan proses insert
        else {
            $query_insert = mysqli_query($mysqli, "INSERT INTO tbl_entity_lemtera(nama_entity_lemtera) VALUES ('$nama_entity_lemtera')");

            // Cek jika proses insert berhasil
            if ($query_insert) {
            header('location: ../../main.php?module=lemtera&pesan=1');
            exit();
            } else {
                header('location: ../../main.php?module=lemtera&pesan=2');
                exit();
            }
        }
    }

    // Proses untuk Ubah Data (Update)
    // Cek jika tombol 'ubah' ditekan dan berasal dari form
    elseif (isset($_POST['ubah'])) {
        // Ambil data dari form dan sanitasi input
        $id_entity   = (int)$_POST['id_entity'];
        $nama_entity_lemtera = mysqli_real_escape_string($mysqli, trim($_POST['nama_entity_lemtera']));

        // Buat query untuk update data
        $query_update = mysqli_query($mysqli, "UPDATE tbl_entity_lemtera SET nama_entity_lemtera = '$nama_entity_lemtera' WHERE id_entity = $id_entity");

        // Cek jika proses update berhasil
        if ($query_update) {
            // Jika berhasil, alihkan dengan pesan sukses
            header('location: ../../main.php?module=lemtera&pesan=2');
            exit(); // Pastikan exit dipanggil setelah header
        }
        // Jika gagal, kirim notifikasi error
        else {
            $_SESSION['alert'] = [
                'type'    => 'error',
                'message' => 'Gagal mengubah data kategori peserta! Kesalahan: ' . mysqli_error($mysqli)
            ];
        }
    }
    
    // Proses untuk Hapus Data (Delete)
    // Cek jika request untuk 'hapus' dikirim
    // Catatan: Form hapus di file Anda mengirimkan request ke file view itu sendiri.
    // Kode di bawah ini akan bekerja jika Anda mengubah action form hapus ke file ini.
    elseif (isset($_POST['hapus'])) {
        // Ambil data dari form
        $id_entity = (int)$_POST['id_entity'];

        // Buat query untuk menghapus data
        $query_delete = mysqli_query($mysqli, "DELETE FROM tbl_entity_lemtera WHERE id_entity = $id_entity");

        // Cek jika proses delete berhasil
        if ($query_delete) {
            header('location: ../../main.php?module=lemtera&pesan=3');
            exit();
        } 
        // Jika gagal, kirim notifikasi error
        else {
            // Ini bisa terjadi jika ada foreign key constraint (misalnya, kategori masih digunakan di tabel lain)
            $_SESSION['alert'] = [
                'type'    => 'error',
                'message' => 'Gagal menghapus data! Kategori mungkin masih digunakan oleh data lain.'
            ];
        }
    }

    // Setelah semua proses selesai, alihkan kembali ke halaman utama data kategori
    header('location: ../../main.php?module=kategori_peserta');
    exit();
}
?>