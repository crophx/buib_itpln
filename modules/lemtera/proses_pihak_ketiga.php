<?php
session_start();
require_once '../../config/database.php';

// Mencegah akses langsung dan memastikan user memiliki hak
if (empty($_SESSION['username']) && empty($_SESSION['password']) && !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'LEMTERA', 'Pimpinan', 'SekretarisPimpinan'])) {
    header('location: ../../login?alert=1');
    exit();
}

// Proses hanya jika tombol simpan ditekan
if (isset($_POST['simpan'])) {
    $id_rk_lemtera = (int)$_POST['id_rk_lemtera'];

    // Mulai transaksi
    mysqli_begin_transaction($mysqli);

    try {
        // 1. HAPUS DATA LAMA
        // Ambil semua ID pihak ketiga yang ada di DB untuk program ini
        $result_db_ids = mysqli_query($mysqli, "SELECT id_pihak_ketiga FROM tbl_pihak_ketiga WHERE id_rk_lemtera = $id_rk_lemtera");
        $db_pk_ids = [];
        while ($row = mysqli_fetch_assoc($result_db_ids)) {
            $db_pk_ids[] = $row['id_pihak_ketiga'];
        }

        // Ambil semua ID pihak ketiga yang dikirim dari form
        $form_pk_ids = [];
        foreach ($_POST['id_pihak_ketiga'] as $index => $id_pk) {
            if ($id_pk !== 'new') {
                $form_pk_ids[] = (int)$id_pk;
            }
        }

        // Cari ID yang ada di DB tapi tidak ada di form, lalu hapus
        $ids_to_delete = array_diff($db_pk_ids, $form_pk_ids);
        if (!empty($ids_to_delete)) {
            $delete_list = implode(',', $ids_to_delete);
            mysqli_query($mysqli, "DELETE FROM tbl_pihak_ketiga WHERE id_pihak_ketiga IN ($delete_list)");
        }

        // 2. PROSES UPDATE & INSERT PIHAK KETIGA DAN TERMINNYA
        if (isset($_POST['id_pihak_ketiga'])) {
            foreach ($_POST['id_pihak_ketiga'] as $index => $id_pk) {
                $nama = mysqli_real_escape_string($mysqli, $_POST['nama_pihak_ketiga'][$index]);
                $role = mysqli_real_escape_string($mysqli, $_POST['role_pihak_ketiga'][$index]);
                $link_surat = mysqli_real_escape_string($mysqli, $_POST['link_surat'][$index]);
                
                $current_pk_id = 0;

                if ($id_pk === 'new') {
                    // INSERT Pihak Ketiga Baru
                    mysqli_query($mysqli, "INSERT INTO tbl_pihak_ketiga (id_rk_lemtera, nama_pihak_ketiga, role_pihak_ketiga, link_surat) VALUES ($id_rk_lemtera, '$nama', '$role', '$link_surat')");
                    $current_pk_id = mysqli_insert_id($mysqli);
                } else {
                    // UPDATE Pihak Ketiga Lama
                    $current_pk_id = (int)$id_pk;
                    mysqli_query($mysqli, "UPDATE tbl_pihak_ketiga SET nama_pihak_ketiga='$nama', role_pihak_ketiga='$role', link_surat='$link_surat' WHERE id_pihak_ketiga=$current_pk_id");
                }

                // Proses termin untuk pihak ketiga ini
                // Hapus dulu termin lama yg mungkin dihapus user
                $result_db_termin_ids = mysqli_query($mysqli, "SELECT id_termin_pk FROM tbl_termin_pihak_ketiga WHERE id_pihak_ketiga = $current_pk_id");
                $db_termin_ids = []; while ($row = mysqli_fetch_assoc($result_db_termin_ids)) {$db_termin_ids[] = $row['id_termin_pk'];}
                $form_termin_ids = []; if(isset($_POST['id_termin_pk'][$index])) { foreach($_POST['id_termin_pk'][$index] as $id_t) { if($id_t !== 'new') {$form_termin_ids[] = (int)$id_t;} } }
                $termins_to_delete = array_diff($db_termin_ids, $form_termin_ids);
                if (!empty($termins_to_delete)) { $delete_termin_list = implode(',', $termins_to_delete); mysqli_query($mysqli, "DELETE FROM tbl_termin_pihak_ketiga WHERE id_termin_pk IN ($delete_termin_list)");}

                // Insert/Update termin
                if (isset($_POST['id_termin_pk'][$index])) {
                    foreach ($_POST['id_termin_pk'][$index] as $t_idx => $id_termin) {
                        $tanggal = $_POST['tanggal_termin_pk'][$index][$t_idx] ? "'" . mysqli_real_escape_string($mysqli, $_POST['tanggal_termin_pk'][$index][$t_idx]) . "'" : 'NULL';
                        $nominal = (float)preg_replace('/[Rp. ]/', '', $_POST['nominal_termin_pk'][$index][$t_idx]);
                        $bukti = mysqli_real_escape_string($mysqli, $_POST['bukti_termin_pk'][$index][$t_idx]);

                        if ($id_termin === 'new') {
                            mysqli_query($mysqli, "INSERT INTO tbl_termin_pihak_ketiga (id_pihak_ketiga, tanggal_termin, nominal_termin, link_bukti_bayar) VALUES ($current_pk_id, $tanggal, $nominal, '$bukti')");
                        } else {
                            $current_termin_id = (int)$id_termin;
                            mysqli_query($mysqli, "UPDATE tbl_termin_pihak_ketiga SET tanggal_termin=$tanggal, nominal_termin=$nominal, link_bukti_bayar='$bukti' WHERE id_termin_pk=$current_termin_id");
                        }
                    }
                }
            }
        }
        
        // Jika semua berhasil, commit transaksi
        mysqli_commit($mysqli);
        header('location: ../../main.php?module=pihak_ketiga&id=' . $id_rk_lemtera . '&alert=1');

    } catch (Exception $e) {
        // Jika ada error, rollback semua perubahan
        mysqli_rollback($mysqli);
        header('location: ../../main.php?module=pihak_ketiga&id=' . $id_rk_lemtera . '&alert=2');
    }
}
?>