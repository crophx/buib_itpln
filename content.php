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
