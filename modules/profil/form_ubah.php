<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    // alihkan ke halaman error 404
    header('location: 404.html');
}
// jika file di include oleh file lain, tampilkan isi file
else {
    // sql statement untuk menampilkan data dari tabel "tbl_profil"
    $query = mysqli_query($mysqli, "SELECT * FROM tbl_profil")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    // ambil data hasil query
    $data = mysqli_fetch_assoc($query);
?>
    <div class="panel-header">
        <div class="page-inner py-4">
            <div class="page-header">
                <!-- judul halaman -->
                <h4 class="page-title"><i class="fas fa-cog mr-2"></i> Profil Instansi</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=jenis">Profil</a></li>
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
                <div class="card-title">Ubah Profil Instansi</div>
            </div>
            <!-- form ubah data -->
            <form action="modules/profil/proses_ubah.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label>Nama Instansi <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" autocomplete="off" value="<?php echo $data['nama']; ?>" required>
                                <div class="invalid-feedback">Nama Instansi tidak boleh kosong.</div>
                            </div>

                            <div class="form-group">
                                <label>Alamat <span class="text-danger">*</span></label>
                                <textarea name="alamat" rows="2" class="form-control" autocomplete="off" required><?php echo $data['alamat']; ?></textarea>
                                <div class="invalid-feedback">Alamat tidak boleh kosong.</div>
                            </div>

                            <div class="form-group">
                                <label>Telepon <span class="text-danger">*</span></label>
                                <input type="text" name="telepon" class="form-control" maxlength="13" autocomplete="off" onKeyPress="return goodchars(event,'0123456789',this)" value="<?php echo $data['telepon']; ?>" required>
                                <div class="invalid-feedback">Telepon tidak boleh kosong.</div>
                            </div>

                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" autocomplete="off" value="<?php echo $data['email']; ?>" required>
                                <div class="invalid-feedback">Email tidak boleh kosong.</div>
                            </div>

                            <div class="form-group">
                                <label>Website <span class="text-danger">*</span></label>
                                <input type="text" name="website" class="form-control" autocomplete="off" value="<?php echo $data['website']; ?>" required>
                                <div class="invalid-feedback">Website tidak boleh kosong.</div>
                            </div>
                        </div>
                        <div class="col-md-5 ml-auto">
                            <div class="form-group">
                                <label>Logo</label>
                                <input type="file" accept=".jpg, .jpeg, .png" id="logo" name="logo" class="form-control" autocomplete="off">
                                <div class="card mt-3 mb-3">
                                    <div class="card-body text-center">
                                        <?php
                                        // mengecek data logo
                                        // jika data "logo" tidak ada di database
                                        if (is_null($data['logo'])) { ?>
                                            <!-- tampilkan logo default -->
                                            <img style="max-height:200px" src="assets/img/no_image.png" class="img-fluid logo-preview" alt="Logo">
                                        <?php
                                        }
                                        // jika data "logo" ada di database
                                        else { ?>
                                            <!-- tampilkan logo dari database -->
                                            <img style="max-height:200px" src="assets/img/<?php echo $data['logo']; ?>" class="img-fluid logo-preview" alt="Logo">
                                        <?php } ?>
                                    </div>
                                </div>
                                <small class="form-text text-secondary">
                                    Keterangan : <br>
                                    - Tipe file yang bisa diunggah adalah *.jpg atau *.png. <br>
                                    - Ukuran file yang bisa diunggah maksimal 1 Mb.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-action">
                    <!-- button simpan data -->
                    <input type="submit" name="simpan" value="Simpan" class="btn btn-success btn-round pl-4 pr-4 mr-2">
                    <!-- button kembali ke halaman tampil data -->
                    <a href="?module=profil" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            // validasi file dan preview file sebelum diunggah
            $('#logo').change(function() {
                // mengambil value dari file
                var filePath = $('#logo').val();
                var fileSize = $('#logo')[0].files[0].size;
                // tentukan extension file yang diperbolehkan
                var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

                // Jika tipe file yang diunggah tidak sesuai dengan "allowedExtensions"
                if (!allowedExtensions.exec(filePath)) {
                    // tampilkan pesan peringatan tipe file tidak sesuai
                    $.notify({
                        title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
                        message: 'Tipe file tidak sesuai. Harap unggah file yang memiliki tipe *.jpg atau *.png.'
                    }, {
                        type: 'danger'
                    });
                    // reset input file
                    $('#logo').val('');
                    // tampilkan file default
                    $('.logo-preview').attr('src', 'assets/img/no_image.png');

                    return false;
                }
                // jika ukuran file yang diunggah lebih dari 1 Mb
                else if (fileSize > 1000000) {
                    // tampilkan pesan peringatan ukuran file tidak sesuai
                    $.notify({
                        title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
                        message: 'Ukuran file lebih dari 1 Mb. Harap unggah file yang memiliki ukuran maksimal 1 Mb.'
                    }, {
                        type: 'danger'
                    });
                    // reset input file
                    $('#logo').val('');
                    // tampilkan file default
                    $('.logo-preview').attr('src', 'assets/img/no_image.png');

                    return false;
                }
                // jika file yang diunggah sudah sesuai, tampilkan preview file
                else {
                    var fileInput = document.getElementById('logo');

                    if (fileInput.files && fileInput.files[0]) {
                        var reader = new FileReader();

                        reader.onload = function(e) {
                            // preview file
                            $('.logo-preview').attr('src', e.target.result);
                        };
                    };
                    reader.readAsDataURL(fileInput.files[0]);
                }
            });
        });
    </script>
<?php } ?>