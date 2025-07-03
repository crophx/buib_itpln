<?php
// mencegah direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('location: 404.html');
} else {
    if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'BKI'])) { ?>
        <div class="panel-header">
            <div class="page-inner py-4">
                <div class="page-header">
                    <h4 class="page-title"><i class="fas fa-file-signature mr-2"></i> Entri Dokumen PKS</h4>
                    <ul class="breadcrumbs">
                        <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a href="?module=bki">BKI</a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a>Entri PKS</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Formulir Entri Perjanjian Kerja Sama (PKS)</div>
                </div>
                <form action="modules/bki/pks/proses_simpan_pks.php" method="post" class="needs-validation" novalidate>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Terhubung ke MoU Induk (Opsional)</label>
                            <select name="mou_id" class="form-control select2-single">
                                <option value="" selected>-- PKS Mandiri (Tidak terhubung ke MoU) --</option>
                                <?php
                                $query_mou = mysqli_query(
                                    $mysqli,
                                    "SELECT mou.id, mou.no_dokumen, m.nama_mitra, negara 
                                     FROM tbl_mou AS mou JOIN tbl_mitra_bki AS m ON mou.mitra_id = m.id 
                                     ORDER BY m.nama_mitra, mou.tanggal_penandatanganan DESC"
                                );

                                while ($data_mou = mysqli_fetch_assoc($query_mou)) {
                                    echo "<option value='{$data_mou['id']}'>{$data_mou['no_dokumen']} - {$data_mou['nama_mitra']} -{$data_mou['negara']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mitra <span class="text-danger">*</span></label>
                                    <select name="mitra_id" class="form-control select2-single" required>
                                        <option selected disabled value="">-- Pilih Mitra --</option>
                                        <?php
                                        $query_mitra = mysqli_query($mysqli, "SELECT * FROM tbl_mitra_bki ORDER BY nama_mitra ASC");
                                        while ($data_mitra = mysqli_fetch_assoc($query_mitra)) {
                                            echo "<option value='{$data_mitra['id']}'>{$data_mitra['nama_mitra']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>PIC (Bagian / Prodi) <span class="text-danger">*</span></label>
                                    <select name="pic_bagian_id" class="form-control select2-single" required>
                                        <option selected disabled value="">-- Pilih PIC --</option>
                                        <?php
                                        $query_pic = mysqli_query($mysqli, "SELECT * FROM tbl_pic_bagian ORDER BY nama_bagian ASC");
                                        while ($data_pic = mysqli_fetch_assoc($query_pic)) {
                                            echo "<option value='{$data_pic['id']}'>{$data_pic['nama_bagian']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Tentang (Judul PKS) <span class="text-danger">*</span></label>
                            <textarea name="tentang" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"><label>Nomor Dokumen PKS <span
                                            class="text-danger">*</span></label><input type="text" name="no_dokumen"
                                        class="form-control" required></div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"><label>Tanggal Penandatanganan <span
                                            class="text-danger">*</span></label><input type="date"
                                        name="tanggal_penandatanganan" class="form-control" value="<?php echo date('Y-m-d'); ?>"
                                        required></div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"><label>Jangka Waktu (Tahun) <span
                                            class="text-danger">*</span></label><input type="number" name="jangka_waktu_tahun"
                                        class="form-control" placeholder="Contoh: 3" required></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Link Dokumen PKS <span class="text-danger">*</span></label>
                            <input type="url" name="link_dokumen_pks" class="form-control" placeholder="https://..." required>
                            <small class="form-text text-muted">Masukkan link ke file atau folder Google Drive, dll.</small>
                        </div>
                    </div>
                    <div class="card-action">
                        <input type="submit" name="simpan" value="Simpan PKS" class="btn btn-success btn-round pl-4 pr-4 mr-2">
                        <a href="?module=bki" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}
?>