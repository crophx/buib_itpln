<?php
session_start();
require_once "../../../config/database.php";

if (isset($_POST['simpan'])) {
    $id = (int) $_POST['id'];
    $mitra_id = (int) $_POST['mitra_id'];
    $pic_bagian_id = (int) $_POST['pic_bagian_id'];
    $jangka_waktu_tahun = (int) $_POST['jangka_waktu_tahun'];

    $no_dokumen = mysqli_real_escape_string($mysqli, trim($_POST['no_dokumen']));
    $tentang = mysqli_real_escape_string($mysqli, trim($_POST['tentang']));
    $tanggal_penandatanganan = mysqli_real_escape_string($mysqli, $_POST['tanggal_penandatanganan']);
    $link_dokumen_MoU = mysqli_real_escape_string($mysqli, trim($_POST['link_dokumen_MoU']));

    $query = mysqli_query($mysqli, "UPDATE tbl_mou SET 
                                        mitra_id = '$mitra_id',
                                        pic_bagian_id = '$pic_bagian_id',
                                        no_dokumen = '$no_dokumen',
                                        tentang = '$tentang',
                                        tanggal_penandatanganan = '$tanggal_penandatanganan',
                                        jangka_waktu_tahun = '$jangka_waktu_tahun',
                                        link_dokumen_MoU = '$link_dokumen_MoU'
                                    WHERE id = '$id'")
        or die('Ada kesalahan pada query update MoU: ' . mysqli_error($mysqli));

    if ($query) {
        header('location: ../../../main.php?module=bki&pesan=2');
        exit();
    }
}
?>