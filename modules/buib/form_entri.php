<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    // alihkan ke halaman error 404
    header('location: 404.html');
}
// jika file di include oleh file lain, tampilkan isi file
else { ?>
    <div class="panel-header">
        <div class="page-inner py-4">
            <div class="page-header">
                <!-- judul halaman -->
                <h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Input Dokumen BUIB</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=buib">Data BUIB</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Entri</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <!--Judul Form-->
                <div class="card-title">Entri Data BUIB</div>
            </div>
            <!--Form Entri Data-->
            <form action="modules/buib/proses_simpan.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Nama Mitra <span class="text-danger">*</span></label>
                                <input type="text" name="mitra" class="form-control" autocomplete="off" required>
                                <div class="invalid-feedback">Nama Mitra tidak boleh kosong.</div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>No Dokumen <span class="text-danger">*</span></label>
                                <input type="text" name="no_dokumen" class="form-control" autocomplete="off" required>
                                <div class="invalid-feedback">No Dokumen tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Jenis Dokumen <span class="text-danger">*</span></label>
                                <select name="jenis_dokumen" class="form-control select2-single" autocomplete="off" required>
                                    <option selected disabled value="">-- Pilih --</option>
                                    <?php
                                    // sql statement untuk menampilkan data dari tabel "tbl_jenis"
                                    $jenis_query = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis ASC")
                                                                or die('Error pada query jenis: ' . mysqli_error($mysqli));
                                    // ambil data hasil query
                                    while ($jenis_data = mysqli_fetch_assoc($jenis_query)) {
                                        // tampilkan data
                                        echo "<option value='".$jenis_data['id_jenis']."'>".$jenis_data['nama_jenis']."</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Jenis dokumen tidak boleh kosong.</div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Target Nominal <span class="text-danger">*</span></label>
                                <input type="text" name="target_nominal" class="form-control" autocomplete="off" onKeyPress="return goodchars(event,'0123456789',this)" required>
                                <div class="invalid-feedback">Target nominal tidak boleh kosong.</div>
                                <small class="form-text text-muted">Masukkan angka tanpa titik atau koma</small>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Realisasi Nominal <span class="text-danger">*</span></label>
                                <input type="text" name="realisasi_nominal" class="form-control" autocomplete="off" onKeyPress="return goodchars(event,'0123456789',this)" required>
                                <div class="invalid-feedback">Realisasi nominal tidak boleh kosong.</div>
                                <small class="form-text text-muted">Masukkan angka tanpa titik atau koma</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Dokumen <span class="text-danger">*</span></label>
                                <input type="file" accept=".pdf" name="dokumen_buib" class="form-control" autocomplete="off" required>
                                <div class="invalid-feedback">Dokumen tidak boleh kosong.</div>
                                <small class="form-text text-primary pt-1">
                                    Keterangan : <br>
                                    - Tipe file yang bisa diunggah adalah *pdf. <br>
                                    - Ukuran file yang bisa diunggah maksimal 10mb.
                                </small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Tanggal Surat <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="tgl_upload" class="form-control datepicker" placeholder="dd/mm/yyyy" autocomplete="off" required>
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
    
<?php }

?>