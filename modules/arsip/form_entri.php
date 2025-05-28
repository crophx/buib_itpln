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
                <h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Arsip Dokumen</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=arsip">Arsip</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Entri</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <!-- judul form -->
                <div class="card-title">Entri Data Arsip Dokumen</div>
            </div>
            <!-- form entri data -->
            <form action="modules/arsip/proses_simpan.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 pr-lg-0">
                            <div class="form-group">
                                <label>Jenis Dokumen <span class="text-danger">*</span></label>
                                <select name="jenis_dokumen" class="form-control select2-single" autocomplete="off" required>
                                    <option selected disabled value="">-- Pilih --</option>
                                    <?php
                                    // sql statement untuk menampilkan data dari tabel "tbl_jenis"
                                    $query_jenis = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis ASC")
                                                                        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
                                    // ambil data hasil query
                                    while ($data_jenis = mysqli_fetch_assoc($query_jenis)) {
                                        // tampilkan data
                                        echo "<option value='$data_jenis[id_jenis]'>$data_jenis[nama_jenis]</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Jenis dokumen tidak boleh kosong.</div>
                            </div>
                        </div>

                        <div class="col-lg-4 pr-lg-0">
                            <div class="form-group">
                                <label>No Dokumen <span class="text-danger">*</span></label>
                                <input type="text" name="no-dokumen" class="form-control" autocomplete="off" required>
                                <div class="invalid-feedback">No Dokumen tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <!-- No. Dokumen
                    <div class="col-lg-4 pr-lg-0">
                            <label>No Dokumen <span class="text-danger">*</span></label>
                            <input type="text" name="no-dokumen" class="form-control" autocomplete="off" required>
                            <div class="invalid-feedback">No Dokumen tidak boleh kosong.</div>
                    </div> 
                    -->
                    
                        <div class="form-group col-lg-4">
                            <label>Tipe Kerjasama<span class="text-danger">*</span></label>
                                <select name="Tipe_kerjasama" class="form-control select2-single" autocomplete="off" required>
                                    <option selected disabled value="">-- Pilih --</option>
                                    <option value="Akademik">Akademik</option>
                                    <option value="Non-Akademik">Non-Akademik</option>
                                </select>
                                <div class="invalid-feedback">Tipe Kerjasama Tidak Boleh Kosong.</div>
                        </div>


                    <div class="row">
                        <div class="col-lg-4 pr-lg-0">
                            <div class="form-group">
                                <label>Nama Pihak Pertama <span class="text-danger">*</span></label>
                                <input type="text" name="pihak-pertama" class="form-control" autocomplete="off" required>
                                <div class="invalid-feedback">Nama Pihak Pertama Tidak Boleh Kosong.</div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 pr-lg-0">
                            <div class="form-group">
                                <label>Nama Pihak Kedua <span class="text-danger">*</span></label>
                                <input type="text" name="pihak-kedua" class="form-control" autocomplete="off" required>
                                <div class="invalid-feedback">Nama Pihak Kedua Tidak Boleh Kosong.</div>
                            </div>
                        </div>
                    </div>
                            

                    <div class="row">
                        <div class="col-lg-4 pr-lg-0">
                            <div class="form-group">
                                <label>Bulan <span class="text-danger">*</span></label>
                                <select name="bulan" class="form-control select2-single" autocomplete="off" required>
                                    <option selected disabled value="">-- Pilih --</option>
                                    <option value="Januari">Januari</option>
                                    <option value="Februari">Februari</option>
                                    <option value="Maret">Maret</option>
                                    <option value="April">April</option>
                                    <option value="Mei">Mei</option>
                                    <option value="Juni">Juni</option>
                                    <option value="Juli">Juli</option>
                                    <option value="Agustus">Agustus</option>
                                    <option value="September">September</option>
                                    <option value="Oktober">Oktober</option>
                                    <option value="November">November</option>
                                    <option value="Desember">Desember</option>
                                </select>
                                <div class="invalid-feedback">Bulan tidak boleh kosong.</div>
                            </div>
                        </div>

                        <div class="col-lg-4 pr-lg-0">
                            <div class="form-group">
                                <label>Tahun <span class="text-danger">*</span></label>
                                <input type="text" name="tahun" class="form-control" maxlength="4" autocomplete="off" onKeyPress="return goodchars(event,'0123456789',this)" required>
                                <div class="invalid-feedback">Tahun tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-4">
                        <hr class="mt-2 mb-1">
                    </div>

                    <div class="row">
                        <div class="col-lg-2 pr-lg-0">
                            <div class="form-group">
                                <label>Tahun Anggaran <span class="text-danger">*</span></label>
                                <input type="text" name="tahun_anggaran" class="form-control" maxlength="4" autocomplete="off" onKeyPress="return goodchars(event,'0123456789',this)" required>
                                <div class="invalid-feedback">Tahun anggaran tidak boleh kosong.</div>
                            </div>
                        </div>

                        <div class="col-lg-2 pr-lg-0">
                            <div class="form-group">
                                <label>DIPA <span class="text-danger">*</span></label>
                                <select name="dipa" class="form-control select2-single" autocomplete="off" required>
                                    <option selected disabled value="">-- Pilih --</option>
                                    <option value="01">01</option>
                                    <option value="03">03</option>
                                </select>
                                <div class="invalid-feedback">DIPA tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-4">
                        <hr class="mt-2 mb-1">
                    </div>

                    <div class="form-group col-lg-4">
                        <label>Dokumen Elektronik <span class="text-danger">*</span></label>
                        <input type="file" accept=".pdf" name="dokumen_elektronik" class="form-control" autocomplete="off" required>
                        <div class="invalid-feedback">Dokumen elektronik tidak boleh kosong.</div>
                        <small class="form-text text-primary pt-1">
                            Keterangan : <br>
                            - Tipe file yang bisa diunggah adalah *.pdf. <br>
                            - Ukuran file yang bisa diunggah maksimal 10 Mb.
                        </small>
                    </div>
                </div>
                <div class="card-action">
                    <!-- button simpan data -->
                    <input type="submit" name="simpan" value="Simpan" class="btn btn-success btn-round pl-4 pr-4 mr-2">
                    <!-- button kembali ke halaman tampil data -->
                    <a href="?module=arsip" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php } ?>