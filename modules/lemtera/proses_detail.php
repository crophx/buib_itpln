<?php
session_start();
require_once '../../config/database.php';

// Pastikan user sudah login
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('location: ../../login.php?pesan=2');
    exit();
}

if (isset($_POST['simpan_detail'])) {
    $id_rk_lemtera = (int)$_POST['id_rk_lemtera'];
    $id_detail = isset($_POST['id_detail']) ? (int)$_POST['id_detail'] : 0;

    mysqli_begin_transaction($mysqli);

    try {
        // 1. Update tbl_detail_lemtera
        $loi_url = mysqli_real_escape_string($mysqli, $_POST['loi_url']);
        $spk_pks_url = mysqli_real_escape_string($mysqli, $_POST['spk_pks_url']);
        $tanggal_akhir = !empty($_POST['tanggal_akhir']) ? mysqli_real_escape_string($mysqli, $_POST['tanggal_akhir']) : NULL;

        if ($id_detail > 0) {
            $stmt_detail = mysqli_prepare($mysqli, "UPDATE tbl_detail_lemtera SET loi_url = ?, spk_pks_url = ?, tanggal_akhir = ? WHERE id_detail = ?");
            mysqli_stmt_bind_param($stmt_detail, 'sssi', $loi_url, $spk_pks_url, $tanggal_akhir, $id_detail);
        } else {
            $stmt_detail = mysqli_prepare($mysqli, "INSERT INTO tbl_detail_lemtera (id_rk_lemtera, loi_url, spk_pks_url, tanggal_akhir) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_detail, 'isss', $id_rk_lemtera, $loi_url, $spk_pks_url, $tanggal_akhir);
        }
        mysqli_stmt_execute($stmt_detail);
        mysqli_stmt_close($stmt_detail);

        function clean_nominal($nominal) { return (float)preg_replace("/[^0-9]/", "", $nominal); }
        
        // 2. Proses Termin (Delete and Insert)
        mysqli_query($mysqli, "DELETE FROM tbl_termin_pembayaran WHERE id_rk_lemtera = $id_rk_lemtera");
        if (isset($_POST['nominal_termin']) && is_array($_POST['nominal_termin'])) {
            $stmt_insert_termin = mysqli_prepare($mysqli, "INSERT INTO tbl_termin_pembayaran (id_rk_lemtera, tanggal_termin, nominal_termin, keterangan_termin, bukti_pembayaran_termin) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['nominal_termin'] as $key => $nominal) {
                if (clean_nominal($nominal) > 0) {
                    $tanggal = !empty($_POST['tanggal_termin'][$key]) ? $_POST['tanggal_termin'][$key] : null;
                    mysqli_stmt_bind_param($stmt_insert_termin, 'isdss', $id_rk_lemtera, $tanggal, clean_nominal($nominal), $_POST['keterangan_termin'][$key], $_POST['bukti_termin'][$key]);
                    mysqli_stmt_execute($stmt_insert_termin);
                }
            }
            mysqli_stmt_close($stmt_insert_termin);
        }

        // 3. Proses Biaya Operasional (Delete and Insert)
        mysqli_query($mysqli, "DELETE FROM tbl_biaya_operasional WHERE id_rk_lemtera = $id_rk_lemtera");
        if (isset($_POST['jumlah_biaya']) && is_array($_POST['jumlah_biaya'])) {
            $stmt_insert_op = mysqli_prepare($mysqli, "INSERT INTO tbl_biaya_operasional (id_rk_lemtera, tanggal_biaya, deskripsi_biaya, jumlah_biaya) VALUES (?, ?, ?, ?)");
            foreach ($_POST['jumlah_biaya'] as $key => $jumlah) {
                if (clean_nominal($jumlah) > 0) {
                    $tanggal = !empty($_POST['tanggal_biaya'][$key]) ? $_POST['tanggal_biaya'][$key] : null;
                    mysqli_stmt_bind_param($stmt_insert_op, 'issd', $id_rk_lemtera, $tanggal, $_POST['deskripsi_biaya'][$key], clean_nominal($jumlah));
                    mysqli_stmt_execute($stmt_insert_op);
                }
            }
            mysqli_stmt_close($stmt_insert_op);
        }
        
        // 4. **BARU**: Proses Penalty (Delete and Insert)
        mysqli_query($mysqli, "DELETE FROM tbl_penalty_program WHERE id_rk_lemtera = $id_rk_lemtera");
        if (isset($_POST['jumlah_penalty']) && is_array($_POST['jumlah_penalty'])) {
            $stmt_insert_penalty = mysqli_prepare($mysqli, "INSERT INTO tbl_penalty_program (id_rk_lemtera, tanggal_penalty, deskripsi_penalty, jumlah_penalty) VALUES (?, ?, ?, ?)");
            foreach ($_POST['jumlah_penalty'] as $key => $jumlah) {
                if (clean_nominal($jumlah) > 0) {
                    $tanggal = !empty($_POST['tanggal_penalty'][$key]) ? $_POST['tanggal_penalty'][$key] : null;
                    mysqli_stmt_bind_param($stmt_insert_penalty, 'issd', $id_rk_lemtera, $tanggal, $_POST['deskripsi_penalty'][$key], clean_nominal($jumlah));
                    mysqli_stmt_execute($stmt_insert_penalty);
                }
            }
            mysqli_stmt_close($stmt_insert_penalty);
        }

        mysqli_commit($mysqli);
        header('location: ../../main.php?module=detail_lemtera&id=' . $id_rk_lemtera . '&pesan=1');

    } catch (Exception $e) {
        mysqli_rollback($mysqli);
        header('location: ../../main.php?module=detail_lemtera&id=' . $id_rk_lemtera . '&pesan=2');
    }

} else {
    header('location: ../../main.php?module=lemtera');
    exit();
}
?>