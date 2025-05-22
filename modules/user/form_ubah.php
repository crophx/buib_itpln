<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
	// alihkan ke halaman error 404
	header('location: 404.html');
}
// jika file di include oleh file lain, tampilkan isi file
else {
	// mengecek data GET "id_user"
	if (isset($_GET['id'])) {
		// ambil data GET dari button ubah
		$id_user = $_GET['id'];

		// sql statement untuk menampilkan data dari tabel "tbl_user" berdasarkan "id_user"
		$query = mysqli_query($mysqli, "SELECT * FROM tbl_user WHERE id_user='$id_user'")
										or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
		// ambil data hasil query
		$data = mysqli_fetch_assoc($query);
	}
?>
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
					<li class="nav-item"><a>Ubah</a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="page-inner mt--5">
		<div class="card">
			<div class="card-header">
				<!-- judul form -->
				<div class="card-title">Ubah Data Pengguna Aplikasi</div>
			</div>
			<!-- form ubah data -->
			<form action="modules/user/proses_ubah.php" method="post" class="needs-validation" novalidate>
				<div class="card-body">
					<input type="hidden" name="id_user" value="<?php echo $data['id_user']; ?>">

					<div class="form-group col-lg-5">
						<label>Nama Pengguna <span class="text-danger">*</span></label>
						<input type="text" name="nama_user" class="form-control" autocomplete="off" value="<?php echo $data['nama_user']; ?>" required>
						<div class="invalid-feedback">Nama pengguna tidak boleh kosong.</div>
					</div>

					<div class="form-group col-lg-5">
						<label>Username <span class="text-danger">*</span></label>
						<input type="text" name="username" class="form-control" autocomplete="off" value="<?php echo $data['username']; ?>" required>
						<div class="invalid-feedback">Username tidak boleh kosong.</div>
					</div>

					<div class="form-group col-lg-5">
						<label>Password</label>
						<input type="password" name="password" class="form-control" placeholder="Kosongkan password jika tidak diubah" autocomplete="off">
					</div>

					<div class="form-group col-lg-5">
						<label>Hak Akses <span class="text-danger">*</span></label>
						<select name="hak_akses" class="form-control select2-single" autocomplete="off" required>
							<option value="<?php echo $data['hak_akses']; ?>"><?php echo $data['hak_akses']; ?></option>
							<option disabled value="">-- Pilih --</option>
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