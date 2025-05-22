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
				<h4 class="page-title"><i class="fas fa-user mr-2"></i> Pengguna Aplikasi</h4>
				<!-- breadcrumbs -->
				<ul class="breadcrumbs">
					<li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
					<li class="separator"><i class="flaticon-right-arrow"></i></li>
					<li class="nav-item"><a href="?module=user">Pengguna</a></li>
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
				<div class="card-title">Entri Data Pengguna Aplikasi</div>
			</div>
			<!-- form entri data -->
			<form action="modules/user/proses_simpan.php" method="post" class="needs-validation" novalidate>
				<div class="card-body">
					<div class="form-group col-lg-5">
						<label>Nama Pengguna <span class="text-danger">*</span></label>
						<input type="text" name="nama_user" class="form-control" autocomplete="off" required>
						<div class="invalid-feedback">Nama pengguna tidak boleh kosong.</div>
					</div>

					<div class="form-group col-lg-5">
						<label>Username <span class="text-danger">*</span></label>
						<input type="text" name="username" class="form-control" autocomplete="off" required>
						<div class="invalid-feedback">Username tidak boleh kosong.</div>
					</div>

					<div class="form-group col-lg-5">
						<label>Password <span class="text-danger">*</span></label>
						<input type="password" name="password" class="form-control" autocomplete="off" required>
						<div class="invalid-feedback">Password tidak boleh kosong.</div>
					</div>

					<div class="form-group col-lg-5">
						<label>Hak Akses <span class="text-danger">*</span></label>
						<select name="hak_akses" class="form-control select2-single" autocomplete="off" required>
							<option selected disabled value="">-- Pilih --</option>
							<option value="Administrator">Administrator</option>
							<option value="Bendahara">Bendahara</option>
							<option value="Pengguna">Pengguna</option>
						</select>
						<div class="invalid-feedback">Hak akses tidak boleh kosong.</div>
					</div>
				</div>
				<div class="card-action">
					<!-- button simpan data -->
					<input type="submit" name="simpan" value="Simpan" class="btn btn-success btn-round pl-4 pr-4 mr-2">
					<!-- button kembali ke halaman tampil data -->
					<a href="?module=user" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
				</div>
			</form>
		</div>
	</div>
<?php } ?>