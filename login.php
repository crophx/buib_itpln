<?php 
// panggil file "database.php" untuk koneksi ke database
require_once "config/database.php";

// sql statement untuk menampilkan data dari tabel "tbl_profil"
$query = mysqli_query($mysqli, "SELECT nama, logo FROM tbl_profil")
								or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
// ambil data hasil query
$data = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="description" content="Sistem Informasi Pengelolaan Arsip Digital Dokumen Keuangan (SIPADU)" />
	<meta name="author" content="Indra Styawantoro" />

	<!-- Title -->
	<title>BUIB - BADAN USAHA DAN INKUBASI BISNIS</title>

	<!-- Favicon icon -->
	<link rel="icon" href="assets/img/favicon.ico" type="image/x-icon" />

	<!-- Fonts and icons -->
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
	<script src="assets/js/plugin/webfont/webfont.min.js"></script>
	<script>
		WebFont.load({
			google: {
				"families": ["Lato:300,400,700,900"]
			},
			custom: {
				"families": ["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"],
				urls: ['assets/css/fonts.min.css']
			},
			active: function() {
				sessionStorage.fonts = true;
			}
		});
	</script>

	<!-- CSS Files -->
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/atlantis.min.css">
	<link rel="stylesheet" href="assets/css/login.css">
</head>

<body class="login">
	<div class="wrapper wrapper-login">
		<div class="container container-login animated fadeIn">
			<!-- logo -->
			<div class="text-center mb-4"><img src="assets/img/<?php echo $data['logo']; ?>" alt="Logo" width="93px"></div>
			<!-- judul -->
			<h1 class="text-center fw-bold mb-0">BUIB</h1>
			<h3 style="text-transform: uppercase;" class="text-center mb-5"><?php echo $data['nama']; ?></h3>
			<!-- form login -->
			<form action="proses_login.php" method="post" class="needs-validation" novalidate>
				<div class="form-group form-floating-label">
					<div class="user-icon"><i class="fas fas fa-user"></i></div>
					<input type="text" id="username" name="username" class="form-control input-border-bottom" autocomplete="off" required>
					<label for="username" class="placeholder">Username</label>
					<div class="invalid-feedback">Username tidak boleh kosong.</div>
				</div>

				<div class="form-group form-floating-label">
					<div class="user-icon"><i class="fas fa-lock"></i></div>
					<div class="show-password"><i class="flaticon-interface"></i></div>
					<input type="password" id="password" name="password" class="form-control input-border-bottom" autocomplete="off" required>
					<label for="password" class="placeholder">Password</label>
					<div class="invalid-feedback">Password tidak boleh kosong.</div>
				</div>

				<div class="form-action mt-2">
					<!-- button login -->
					<input type="submit" name="login" value="LOGIN" class="btn btn-success btn-rounded btn-login btn-block">
				</div>

				<!-- footer -->
				<div class="login-footer mt-5">
					<span class="msg">&copy; 2023 -</span>
					<a href="https://pustakakoding.com/" class="text-brand">Pustaka Koding</a>
				</div>
			</form>
		</div>
	</div>

	<!--   Core JS Files   -->
	<script src="assets/js/core/jquery.3.2.1.min.js"></script>
	<script src="assets/js/core/popper.min.js"></script>
	<script src="assets/js/core/bootstrap.min.js"></script>

	<!-- jQuery UI -->
	<script src="assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
	<!-- Bootstrap Notify -->
	<script type="text/javascript" src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

	<!-- Template JS -->
	<script src="assets/js/ready.js"></script>

	<!-- Custom Scripts -->
	<script src="assets/js/form-validation.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
			// dapatkan parameter URL
			let queryString = window.location.search;
			let urlParams = new URLSearchParams(queryString);
			// ambil data dari URL
			let pesan = urlParams.get('pesan');

			// menampilkan pesan sesuai dengan proses yang dijalankan
			// jika pesan = 1
			if (pesan === '1') {
				// tampilkan pesan gagal login
				$.notify({
					title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal Login!</h5>',
					message: 'Username atau Password salah. Cek kembali Username dan Password Anda.'
				}, {
					type: 'danger'
				});
			}
			// jika pesan = 2
			else if (pesan === '2') {
				// tampilkan pesan peringatan login
				$.notify({
					title: '<h5 class="text-warning font-weight-bold mb-1"><i class="fas fa-exclamation-circle mr-2"></i>Peringatan!</h5>',
					message: 'Anda harus login terlebih dahulu.'
				}, {
					type: 'warning'
				});
			}
			// jika pesan = 3
			else if (pesan === '3') {
				// tampilkan pesan sukses logout
				$.notify({
					title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
					message: 'Anda telah berhasil logout.'
				}, {
					type: 'success'
				});
			}
		});
	</script>
</body>

</html>