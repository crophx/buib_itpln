<?php
// mencegah direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('location: 404.html');
} else {
    if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan', 'BKS'])) { ?>
        <div class="panel-header">
            <div class="page-inner py-4">
                <div class="page-header">
                    <h4 class="page-title"><i class="fas fa-file-signature mr-2"></i> Entri Dokumen PKS</h4>
                    <ul class="breadcrumbs">
                        <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a href="?module=bks">BKS</a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a>Entri IA</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Formulir Entri Implementation of Arrangement (IA)</div>
                </div>
                <form action="modules/bks/implementation_arrangement/proses_simpan.php" method="post" class="needs-validation"
                    novalidate>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Terhubung ke MoU Induk (Opsional)</label>
                            <select name="mou_id" class="form-control select2-single">
                                <option value="" selected>-- IA Mandiri (Tidak terhubung ke MoU) --</option>
                                <?php
                                $query_pks = mysqli_query(
                                    $mysqli,
                                    "SELECT pks.id, pks.no_dokumen, m.nama_mitra, negara 
                                     FROM tbl_mou_bks AS pks JOIN tbl_mitra_bki AS m ON pks.mitra_id = m.id 
                                     ORDER BY m.nama_mitra DESC"
                                );

                                while ($data_pks = mysqli_fetch_assoc($query_pks)) {
                                    echo "<option value='{$data_pks['id']}'>{$data_pks['no_dokumen']} - {$data_pks['nama_mitra']} -{$data_pks['negara']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-4">
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
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Bentuk Kerjasama <span class="text-danger">*</span></label>
                                    <select name="bentuk_kerjasama_bks" class="form-control select2-single" required>
                                        <option value="" disabled selected>-- Pilih Bentuk Kerjasama --</option>
                                        <option value="Akademik">Akademik</option>
                                        <option value="Non-Akademik">Non-Akademik</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Tentang Implementation of Arrangement (Judul) <span class="text-danger">*</span></label>
                            <textarea name="tentang" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"><label>Nomor Dokumen IA <span class="text-danger">*</span></label><input
                                        type="text" name="no_dokumen" class="form-control" required></div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"><label>Tanggal Penandatanganan <span
                                            class="text-danger">*</span></label><input type="date" name="tanggal_awal"
                                        class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"><label>Jangka Berakhir <span class="text-danger">*</span></label><input
                                        type="date" name="tanggal_akhir" class="form-control" required></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Klasifikasi Mitra <span class="text-danger">*</span></label>
                            <select name="klasifikasi_mitra" class="form-control select2-single" required>
                                <option value="" disabled selected>-- Pilih Klasifikasi --</option>
                                <option value="Industri">Industri</option>
                                <option value="PLN Group">PLN Group</option>
                                <option value="Pemerintahan">Pemerintahan</option>
                                <option value="Start-Up">Start-Up</option>
                                <option value="Perusahaan Multinasional">Perusahaan Multinasional</option>
                                <option value="Perguruan Tinggi">Perguruan Tinggi</option>
                                <option value="Institusi/Organisasi Multilateral">Institusi/Organisasi Multilateral
                                </option>
                            </select>
                            <div class="invalid-feedback">Klasifikasi Mitra tidak boleh kosong.</div>
                        </div>

                        <div class="form-group">
                            <label>Link Dokumen IA <span class="text-danger">*</span></label>
                            <input type="url" name="link_dokumen_ia" class="form-control" placeholder="https://..." required>
                            <small class="form-text text-muted">Masukkan link ke file atau folder Google Drive, dll.</small>
                        </div>
                    </div>
                    <div class="card-action">
                        <input type="submit" name="simpan" value="Simpan Data" class="btn btn-success btn-round pl-4 pr-4 mr-2">
                        <a href="?module=bks" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}
?>