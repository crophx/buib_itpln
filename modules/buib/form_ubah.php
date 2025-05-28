<?php
if(basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('location: 404.html');
}
else {
    if (isset($_GET['id'])) {
        $id_buib = $_GET['id'];

        $query = mysqli_query($mysqli, "SELECT a.id_buib, a.mitra, a.jenis_dokumen, a.no_dokumen, a.target_nominal, a.realisasi_nominal, a.tgl_upload, a.dokumen_buib, b.nama_jenis
        FROM tbl_buib as a INNER JOIN tbl_jenis as b ON a.jenis_dokumen=b.id_jenis
        WHERE a.id_buib='$id_buib'")
        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));

        //ambil data hasil query
        $data = mysqli_fetch_assoc($query);
    }
?>
    <div class="panel-header">
        <div class="page-inner py-4">
            <div class="page-header">
            <!-- Judul Halaman -->
                <h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Arsip Dokumen</h4>
             <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=arsip">Arsip</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Ubah</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <!-- judul form -->
                <div class="card-title">Ubah Data Dokumen BUIB</div>
            </div>
            <!-- Form ubah data -->
            <form action="modules/buib/proses_ubah.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="card-body">
                    <input type="hidden" name="id_buib" value="<?php echo $data['id_buib']; ?>">
                    <!-- Hidden field untuk menyimpan nama file dokumen lama -->
                    <input type="hidden" name="dokumen_lama" value="<?php echo $data['dokumen_buib']; ?>">
                    
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <!-- Mitra-->
                                    <label>Nama Mitra <span class="text-danger">*</span></label>
                                    <input type="text" name="mitra" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($data['mitra']); ?>" required>
                                    <div class="invalid-feedback">Mitra tidak boleh kosong.</div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group ">
                                    <!-- No Dokumen-->
                                    <label>No Dokumen<span class="text-danger">*</span></label>
                                    <input type="text" name="no_dokumen" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($data['no_dokumen']); ?>" required>
                                    <div class="invalid-feedback">No Dokumen tidak boleh kosong.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Jenis Dokumen <span class="text-danger">*</span></label>
                                    <select name="jenis_dokumen" class="form-control select2-single" autocomplete="off" required>
                                        <option value="<?php echo $data['jenis_dokumen']; ?>"><?php echo htmlspecialchars($data['nama_jenis']); ?></option>
                                        <option disabled value="">-- Pilih --</option>
                                        <?php
                                        // sql statement untuk menampilkan data dari tabel 'tbl_jenis'
                                        $query_jenis = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis ASC")
                                                                            or die('Ada kesalahan pada query tampilan data : '. mysqli_error($mysqli));

                                        while ($data_jenis = mysqli_fetch_assoc($query_jenis)){
                                            // Jangan tampilkan option yang sudah terpilih
                                            if($data_jenis['id_jenis'] != $data['jenis_dokumen']) {
                                                echo "<option value='" . $data_jenis['id_jenis'] . "'>" . htmlspecialchars($data_jenis['nama_jenis']) . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">Jenis dokumen tidak boleh kosong.</div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Target Nominal<span class="text-danger">*</span></label>
                                    <input type="text" name="target_nominal" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($data['target_nominal']); ?>" onKeyPress="return goodchars(event,'0123456789',this)" required>
                                    <div class="invalid-feedback">Target nominal tidak boleh kosong.</div>
                                    <small class="form-text text-muted">Masukkan angka tanpa titik atau koma</small>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Realisasi Nominal<span class="text-danger">*</span></label>
                                    <input type="text" name="realisasi_nominal" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($data['realisasi_nominal']); ?>" onKeyPress="return goodchars(event,'0123456789',this)" required>
                                    <div class="invalid-feedback">Realisasi nominal tidak boleh kosong.</div>
                                    <small class="form-text text-muted">Masukkan angka tanpa titik atau koma</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Dokumen</label>
                                    <input type="file" accept=".pdf" name="dokumen_buib" class="form-control" autocomplete="off">
                                    <div class="invalid-feedback">Dokumen tidak boleh kosong.</div>
                                    <small class="form-text text-primary pt-1">
                                        Keterangan : <br>
                                        - Tipe file yang bisa diunggah adalah *pdf. <br>
                                        - Ukuran file yang bisa diunggah maksimal 10mb.<br>
                                        - Kosongkan jika tidak ingin mengubah dokumen.<br>
                                        <?php if(!empty($data['dokumen_buib'])): ?>
                                        - Dokumen saat ini: <?php echo htmlspecialchars($data['dokumen_buib']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Tanggal Surat <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="tgl_upload" class="form-control datepicker" placeholder="dd/mm/yyyy" value="<?php echo date('d/m/Y', strtotime($data['tgl_upload'])); ?>" autocomplete="off" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="fa fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Tanggal surat tidak boleh kosong.</div>
                                    <small class="form-text text-muted">Pilih tanggal surat dengan mengklik pada kalender</small>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="card-action">
                    <!-- button simpan data -->
                    <input type="submit" name="simpan" value="Simpan" class="btn btn-success btn-round pl-4 pr-4 mr-2">
                    <!-- button kembali ke halaman tampil data -->
                    <a href="?module=buib" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Script untuk menginisialisasi datepicker -->
    <script>
        $(document).ready(function() {
            // Inisialisasi datepicker
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                orientation: 'bottom auto',
                language: 'id'
            });
        });
    </script>

<?php 
}
?>