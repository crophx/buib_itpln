<?php
session_start();

// Cek apakah user sudah login
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('location: ../../login.php?pesan=2');
    exit();
}

require_once "../../config/database.php";

// Cek koneksi database
if (!$mysqli) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Cek apakah ada data POST yang dikirim (form submitted)
// Kita cek dengan beberapa cara untuk memastikan form benar-benar disubmit
$form_submitted = (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    (isset($_POST['simpan_pengajuan']) ||
        (isset($_POST['jenis_dokumen']) && isset($_POST['judul_surat']) && isset($_POST['perihal']))
    )
);

if ($form_submitted) {

    // Validasi session user
    if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
        header('location: ../../login.php?pesan=2');
        exit;
    }

    // Ambil data dari form
    $jenis_dokumen = mysqli_real_escape_string($mysqli, $_POST['jenis_dokumen']);
    $nomor_surat = mysqli_real_escape_string($mysqli, $_POST['nomor_surat']);
    $judul_surat = mysqli_real_escape_string($mysqli, $_POST['judul_surat']);
    $perihal = mysqli_real_escape_string($mysqli, $_POST['perihal']);
    $tujuan_surat = mysqli_real_escape_string($mysqli, $_POST['tujuan_surat']);
    $tanggal_pengajuan = mysqli_real_escape_string($mysqli, $_POST['tanggal_pengajuan']);
    $id_pengaju = $_SESSION['id_user'];

    // Cek apakah ini mode edit
    $edit_mode = isset($_POST['id_pengajuan']) && !empty($_POST['id_pengajuan']);
    $id_pengajuan = $edit_mode ? (int) $_POST['id_pengajuan'] : 0;

    // Validasi input wajib
    if (empty($jenis_dokumen) || empty($judul_surat) || empty($perihal) || empty($tanggal_pengajuan)) {
        header('Location: ../../main.php?module=form_entri_pengajuan&pesan=4');
        exit;
    }

    // Proses upload file
    $file_dokumen = '';
    $upload_success = false;

    if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file_dokumen'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validasi ekstensi file
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        if (!in_array($file_ext, $allowed_extensions)) {
            header('Location: ../../main.php?module=form_entri_pengajuan&pesan=4');
            exit;
        }

        // Validasi ukuran file (maksimal 10MB)
        if ($file_size > 10485760) {
            header('Location: ../../main.php?module=form_entri_pengajuan&pesan=5');
            exit;
        }

        // Generate nama file unik
        $new_file_name = 'dokumen_' . time() . '_' . $id_pengaju . '.' . $file_ext;
        $upload_path = '../../dokumen/pengajuan/' . $new_file_name;

        // Pastikan direktori upload ada
        if (!file_exists('../../dokumen/pengajuan/')) {
            mkdir('../../dokumen/pengajuan/', 0755, true);
        }

        // Upload file
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $file_dokumen = 'dokumen/pengajuan/' . $new_file_name;
            $upload_success = true;
        } else {
            header('Location: ../../main.php?module=form_entri_pengajuan&pesan=6');
            exit;
        }
    } else if (!$edit_mode) {
        // File dokumen wajib diupload untuk pengajuan baru
        header('Location: ../../main.php?module=form_entri_pengajuan&pesan=7');
        exit;
    }

    // Mulai transaksi database
    mysqli_begin_transaction($mysqli);

    try {
        if ($edit_mode) {
            // Mode edit - update data existing

            // Ambil data file lama jika tidak ada upload file baru
            if (!$upload_success) {
                $query_old = "SELECT file_dokumen FROM tbl_pengajuan WHERE id_pengajuan = $id_pengajuan AND id_pengaju = $id_pengaju";
                $result_old = mysqli_query($mysqli, $query_old);
                if ($result_old && mysqli_num_rows($result_old) > 0) {
                    $old_data = mysqli_fetch_assoc($result_old);
                    $file_dokumen = $old_data['file_dokumen'];
                }
            }

            // Query update
            $query = "UPDATE tbl_pengajuan SET 
                        jenis_dokumen = '$jenis_dokumen',
                        nomor_surat = '$nomor_surat',
                        judul_surat = '$judul_surat',
                        perihal = '$perihal',
                        tujuan_surat = '$tujuan_surat',
                        tanggal_pengajuan = '$tanggal_pengajuan'";

            // Tambahkan file_dokumen ke query jika ada upload baru
            if ($upload_success) {
                $query .= ", file_dokumen = '$file_dokumen'";
            }

            $query .= ", updated_at = NOW()
                       WHERE id_pengajuan = $id_pengajuan AND id_pengaju = $id_pengaju";

        } else {
            // Mode insert - pengajuan baru
            $query = "INSERT INTO tbl_pengajuan (
                        jenis_dokumen, 
                        nomor_surat, 
                        judul_surat, 
                        perihal, 
                        tujuan_surat, 
                        tanggal_pengajuan, 
                        file_dokumen, 
                        status_pengajuan,
                        catatan_pimpinan, 
                        id_pengaju,
                        created_at
                    ) VALUES (
                        '$jenis_dokumen', 
                        '$nomor_surat', 
                        '$judul_surat', 
                        '$perihal', 
                        '$tujuan_surat', 
                        '$tanggal_pengajuan', 
                        '$file_dokumen', 
                        'Menunggu', 
                        '',
                        '$id_pengaju',
                        NOW()
                    )";
        }

        $result = mysqli_query($mysqli, $query);

        if ($result) {
            // Commit transaksi
            mysqli_commit($mysqli);

            if ($edit_mode) {
                header('location: ../../main.php?module=pengajuan_surat&pesan=3');
            } else {
                header('location: ../../main.php?module=pengajuan_surat&pesan=1');
            }
            exit;
        } else {
            // Rollback jika query gagal
            mysqli_rollback($mysqli);

            // Hapus file yang sudah diupload jika ada error
            if ($upload_success && !empty($file_dokumen) && file_exists('../../' . $file_dokumen)) {
                unlink('../../' . $file_dokumen);
            }

            header('location: ../../main.php?module=pengajuan_surat&pesan=2');
            exit;
        }

    } catch (Exception $e) {
        // Rollback transaksi jika ada error
        mysqli_rollback($mysqli);

        // Hapus file yang sudah diupload jika ada error
        if ($upload_success && !empty($file_dokumen) && file_exists('../../' . $file_dokumen)) {
            unlink('../../' . $file_dokumen);
        }

        // Log error
        error_log('Error saat menyimpan pengajuan: ' . $e->getMessage());

        // Redirect dengan pesan error
        header('Location: ../../main.php?module=form_entri_pengajuan&pesan=8');
        exit;
    }
} else {
    // Jika tidak ada data POST atau akses langsung ke file
    header('Location: ../../main.php?module=form_entri_pengajuan');
    exit;
}
?>