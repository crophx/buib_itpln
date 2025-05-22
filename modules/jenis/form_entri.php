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
                <h4 class="page-title"><i class="fas fa-clone mr-2"></i> Jenis Dokumen</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=jenis">Jenis</a></li>
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
                <div class="card-title">Entri Data Jenis Dokumen</div>
            </div>
            <!-- form entri data -->
            <form action="modules/jenis/proses_simpan.php" method="post" class="needs-validation" novalidate>
                <div class="card-body">
                    <div class="form-group col-lg-5">
						<label>Jenis Dokumen <span class="text-danger">*</span></label>
						<input type="text" name="nama_jenis" class="form-control" autocomplete="off" required>
						<div class="invalid-feedback">Jenis dokumen tidak boleh kosong.</div>
					</div>
                </div>
                <div class="card-action">
                    <!-- button simpan data -->
                    <input type="submit" name="simpan" value="Simpan" class="btn btn-success btn-round pl-4 pr-4 mr-2">
                    <!-- button kembali ke halaman tampil data -->
                    <a href="?module=jenis" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php } ?>