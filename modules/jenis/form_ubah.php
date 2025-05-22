<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    // alihkan ke halaman error 404
    header('location: 404.html');
}
// jika file di include oleh file lain, tampilkan isi file
else {
    // mengecek data GET "id_jenis"
    if (isset($_GET['id'])) {
        // ambil data GET dari button ubah
        $id_jenis = $_GET['id'];

        // sql statement untuk menampilkan data dari tabel "tbl_jenis" berdasarkan "id_jenis"
        $query = mysqli_query($mysqli, "SELECT * FROM tbl_jenis WHERE id_jenis='$id_jenis'")
                                        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
        // ambil data hasil query
        $data = mysqli_fetch_assoc($query);
    }
?>
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
                    <li class="nav-item"><a>Ubah</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <!-- judul form -->
                <div class="card-title">Ubah Data Jenis Dokumen</div>
            </div>
            <!-- form ubah data -->
            <form action="modules/jenis/proses_ubah.php" method="post" class="needs-validation" novalidate>
                <div class="card-body">
                    <input type="hidden" name="id_jenis" value="<?php echo $data['id_jenis']; ?>">

                    <div class="form-group col-lg-5">
						<label>Jenis Dokumen <span class="text-danger">*</span></label>
						<input type="text" name="nama_jenis" class="form-control" autocomplete="off" value="<?php echo $data['nama_jenis']; ?>" required>
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