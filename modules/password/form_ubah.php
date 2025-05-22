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
                <h4 class="page-title"><i class="fas fa-user-lock mr-2"></i> Password</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Password</a></li>
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
                <div class="card-title">Ubah Password</div>
            </div>
            <!-- form ubah data -->
            <form action="modules/password/proses_ubah.php" method="post" class="needs-validation" novalidate>
                <div class="card-body">
                    <div class="form-group col-lg-5">
                        <label>Password Lama <span class="text-danger">*</span></label>
                        <input type="password" name="password_lama" class="form-control" autocomplete="off" required>
                        <div class="invalid-feedback">Password lama tidak boleh kosong.</div>
                    </div>

                    <div class="form-group col-lg-5">
                        <label>Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="password_baru" class="form-control" autocomplete="off" required>
                        <div class="invalid-feedback">Password baru tidak boleh kosong.</div>
                    </div>

                    <div class="form-group col-lg-5">
                        <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="konfirmasi_password" class="form-control" autocomplete="off" required>
                        <div class="invalid-feedback">Konfirmasi password baru tidak boleh kosong.</div>
                    </div>
                </div>
                <div class="card-action">
                    <!-- button simpan data -->
                    <input type="submit" name="simpan" value="Simpan" class="btn btn-success btn-round pl-4 pr-4 mr-2">
                </div>
            </form>
        </div>
    </div>

    <script type="text/javascript">
		$(document).ready(function() {
			// dapatkan parameter URL
			let queryString = window.location.search;
			let urlParams = new URLSearchParams(queryString);
			// ambil data dari URL
			let pesan = urlParams.get('pesan');
			let username = urlParams.get('username');

			// menampilkan pesan sesuai dengan proses yang dijalankan
			// jika pesan = 1
			if (pesan === '1') {
				// tampilkan pesan gagal ubah data
				$.notify({
					title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
					message: 'Password Lama yang Anda masukan salah.'
				}, {
					type: 'danger'
				});
			}
			// jika pesan = 2
			else if (pesan === '2') {
				// tampilkan pesan gagal ubah data
				$.notify({
					title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
					message: 'Password Baru dan Konfirmasi Password Baru tidak cocok.'
				}, {
					type: 'danger'
				});
			}
			// jika pesan = 3
			else if (pesan === '3') {
				// tampilkan pesan sukses ubah data
				$.notify({
					title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
					message: 'Password berhasil diubah.'
				}, {
					type: 'success'
				});
			}
		});
	</script>
<?php } ?>