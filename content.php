<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
	// alihkan ke halaman error 404
	header('location: 404.html');
}
// jika file di include oleh file lain, tampilkan isi file
else {
	// pemanggilan file halaman konten sesuai dengan hak akses dan "module" yang dipilih
	// jika module yang dipilih "beranda"
	if ($_GET['module'] == 'beranda') {
		// panggil file tampil data beranda
		include "modules/beranda/tampil_data.php";
	}
	// -- MODULE BUIB -- //
	// jika module yang dipilih "buib"
	elseif ($_GET['module'] == 'buib' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan'])) {
		// panggil file tampil home buib
		include "modules/buib/buib_home.php";
	}
	// jika module yang dipilih "form entri buib"
	elseif ($_GET['module'] == 'form_entri_buib' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan'])) {
		// panggil file tampil entri data buib
		include "modules/buib/form_entri.php";
	}
	// jika module yang dipilih "form entri buib"
	elseif ($_GET['module'] == 'form_entri_realisasi_buib') {
		// panggil file tampil entri data buib
		include "modules/buib/form_entri_realisasi.php";
	}
	// jika module yang dipilih "form entri rk buib"
	elseif ($_GET['module'] == 'form_entri_rk_buib' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan'])) {
		// panggil file tampil entri data buib
		include "modules/buib/form_entri_rk.php";
	}
	// jika module yang dipilih "form ubah buib"
	elseif ($_GET['module'] == 'form_ubah_buib' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan'])) {
		// panggil file tampil entri data buib
		include "modules/buib/form_ubah.php";
	}
	// jika module yang dipilih "tampil buib"
	elseif ($_GET['module'] == 'tampil_detail_buib' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan'])) {
		// panggil file tampil entri data buib
		include "modules/buib/tampil_detail.php";
	}
	// -- MODULE Pusat Bisnis -- //
	elseif ($_GET['module'] == 'pusat_bisnis' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'PusatBisnis'])) {
		// panggil file tampil home pusat_bisnis
		include "modules/pusat_bisnis/pusat_bisnis_home.php";
	}
	// -- MODULE BKS -- //
	elseif ($_GET['module'] == 'bks' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'BKS'])) {
		// panggil file tampil home bks
		include "modules/bks/bks_home.php";
	}
	// -- MODULE BKI -- //
	elseif ($_GET['module'] == 'bki' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'BKI'])) {
		// panggil file tampil home bki
		include "modules/bki/bki_home.php";
	}
	// jika module yang dipilih "form entri bki"
	elseif ($_GET['module'] == 'form_entri_realisasi_bki') {
		// panggil file tampil entri data buib
		include "modules/bki/form_entri_realisasi.php";
	}
	// jika module yang dipilih "form entri rk bki"
	elseif ($_GET['module'] == 'form_entri_rk_bki' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BKI', 'Pimpinan', 'SekretarisPimpinan'])) {
		// panggil file tampil entri data bki
		include "modules/bki/form_entri_rk.php";
	}
	// jika module yang dipilih "form entri bks"
	elseif ($_GET['module'] == 'form_entri_realisasi_bks') {
		// panggil file tampil entri data buib
		include "modules/bks/form_entri_realisasi.php";
	}
	// jika module yang dipilih "form entri rk bks"
	elseif ($_GET['module'] == 'form_entri_rk_bks' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BKS', 'Pimpinan', 'SekretarisPimpinan'])) {
		// panggil file tampil entri data lemtera
		include "modules/bks/form_entri_rk.php";
	}
	// -- MODULE LEMTERA -- //
	elseif ($_GET['module'] == 'lemtera' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'lemtera', 'Pimpinan', 'SekretarisPimpinan', 'LEMTERA'])) {
		// panggil file tampil home lemtera
		include "modules/lemtera/lemtera_home.php";
	}
	// jika module yang dipilih "form entri lemtera"
	elseif ($_GET['module'] == 'form_entri_realisasi_lemtera') {
		// panggil file tampil entri data buib
		include "modules/lemtera/form_entri_realisasi.php";
	}
	// jika module yang dipilih "form entri rk lemtera"
	elseif ($_GET['module'] == 'form_entri_rk_lemtera' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'lemtera', 'Pimpinan', 'SekretarisPimpinan'])) {
		// panggil file tampil entri data lemtera
		include "modules/lemtera/form_entri_rk.php";
	}
	// -- MODULE Training Center -- //
	elseif ($_GET['module'] == 'training_center' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'TrainingCenter'])) {
		// panggil file tampil home training_center
		include "modules/training_center/training_center_home.php";
	}
	// jika module yang dipilih "form entri training center"
	elseif ($_GET['module'] == 'form_entri_realisasi_training_center') {
		// panggil file tampil entri data training center
		include "modules/training_center/form_entri_realisasi.php";
	}
	// jika module yang dipilih "form entri rk training center"
	elseif ($_GET['module'] == 'form_entri_rk_training_center' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BKI', 'Pimpinan', 'SekretarisPimpinan'])) {
		// panggil file tampil entri data bki
		include "modules/training_center/form_entri_rk.php";
	}
	// jika module yang dipilih "arsip"
	elseif ($_GET['module'] == 'arsip') {
		// panggil file tampil data arsip
		include "modules/arsip/tampil_data.php";
	}
	// jika module yang dipilih "form_entri_arsip"
	elseif ($_GET['module'] == 'form_entri_arsip') {
		// panggil file form entri arsip
		include "modules/arsip/form_entri.php";
	}
	// jika module yang dipilih "form_ubah_arsip"
	elseif ($_GET['module'] == 'form_ubah_arsip') {
		// panggil file form ubah arsip
		include "modules/arsip/form_ubah.php";
	}
	// jika module yang dipilih "tampil_detail_arsip"
	elseif ($_GET['module'] == 'tampil_detail_arsip') {
		// panggil file tampil detail arsip
		include "modules/arsip/tampil_detail.php";
	}
	// jika module yang dipilih "jenis"
	elseif ($_GET['module'] == 'jenis') {
		// panggil file tampil data jenis
		include "modules/jenis/tampil_data.php";
	}
	// jika module yang dipilih "form_entri_jenis"
	elseif ($_GET['module'] == 'form_entri_jenis') {
		// panggil file form entri jenis
		include "modules/jenis/form_entri.php";
	}
	// jika module yang dipilih "form_ubah_jenis"
	elseif ($_GET['module'] == 'form_ubah_jenis') {
		// panggil file form ubah jenis
		include "modules/jenis/form_ubah.php";
	}
	// jika module yang dipilih "pengajuan"
	elseif ($_GET['module'] == 'pengajuan_surat') {
		// panggil file form ubah jenis
		include "modules/pengajuan_surat/tampil_data.php";
	}
	// jika module yang dipilih "antrian_surat" dan hak akses "SuperAdmin", "BUIB", "Pimpinan", "SekretarisPimpinan"
	elseif ($_GET['module'] == 'antrian_surat' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan'])) {
		// panggil file tampil data profil
		include "modules/antrian_surat/tampil_data.php";
	}
	// jika module yang dipilih "profil" dan hak akses "SuperAdmin", "BUIB"
	elseif ($_GET['module'] == 'profil' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB'])) {
		// panggil file tampil data profil
		include "modules/profil/tampil_data.php";
	}
	// jika module yang dipilih "form_ubah_profil" dan hak akses "SuperAdmin", "BUIB"
	elseif ($_GET['module'] == 'form_ubah_profil' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB'])) {
		// panggil file form ubah profil
		include "modules/profil/form_ubah.php";
	}
	// jika module yang dipilih "user" dan hak akses "SuperAdmin"
	elseif ($_GET['module'] == 'user' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'SuperAdmin', 'BUIB'])) {
		// panggil file tampil data user
		include "modules/user/tampil_data.php";
	}
	// jika module yang dipilih "form_entri_user" dan hak akses "SuperAdmin"
	elseif ($_GET['module'] == 'form_entri_user' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'SuperAdmin', 'BUIB'])) {
		// panggil file form entri user
		include "modules/user/form_entri.php";
	}
	// jika module yang dipilih "form_ubah_user" dan hak akses "SuperAdmin"
	elseif ($_GET['module'] == 'form_ubah_user' && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'SuperAdmin', 'BUIB'])) {
		// panggil file form ubah user
		include "modules/user/form_ubah.php";
	}
	// jika module yang dipilih "form_ubah_password"
	elseif ($_GET['module'] == 'form_ubah_password') {
		// panggil file form ubah password
		include "modules/password/form_ubah.php";
	}
	// jika module yang dipilih "tentang"
	elseif ($_GET['module'] == 'tentang') {
		// panggil file tampil data tentang
		include "modules/tentang/tampil_data.php";
	}
}
