<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
	// alihkan ke halaman error 404
	header('location: 404.html');
}
// jika file di include oleh file lain, tampilkan isi file
else {
	// pengecekan hak akses untuk menampilkan menu sesuai dengan hak akses
	// jika hak akses = SuperAdmin, tampilkan menu
	if (isset($_SESSION['hak_akses']) && in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB'])) {
		// pengecekan menu aktif
		// jika menu beranda dipilih, menu beranda aktif
		if ($_GET['module'] == 'beranda') { ?>
			<li class="nav-item active">
				<a href="?module=beranda">
					<i class="fas fa-home"></i>
					<p>Beranda</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu beranda tidak aktif
		else { ?>
			<li class="nav-item">
				<a href="?module=beranda">
					<i class="fas fa-home"></i>
					<p>Beranda</p>
				</a>
			</li>
		<?php
		}

		// jika menu arsip dokumen (tampil data / tampil detail / form entri / form ubah) dipilih, menu arsip dokumen aktif
		if ($_GET['module'] == 'arsip' || $_GET['module'] == 'tampil_detail_arsip' || $_GET['module'] == 'form_entri_arsip' || $_GET['module'] == 'form_ubah_arsip') { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Arsip</h4>
			</li>

			<li class="nav-item active">
				<a href="?module=arsip">
					<i class="fas fa-folder-open"></i>
					<p>Arsip Dokumen</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu arsip dokumen tidak aktif
		else { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Arsip</h4>
			</li>

			<li class="nav-item">
				<a href="?module=arsip">
					<i class="fas fa-folder-open"></i>
					<p>Arsip Dokumen</p>
				</a>
			</li>
		<?php
		}

		// jika menu jenis dokumen (tampil data / form entri / form ubah) dipilih, menu jenis dokumen aktif
		if ($_GET['module'] == 'jenis' || $_GET['module'] == 'form_entri_jenis' || $_GET['module'] == 'form_ubah_jenis') { ?>
			<li class="nav-item active">
				<a href="?module=jenis">
					<i class="fas fa-clone"></i>
					<p>Jenis Dokumen</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu jenis dokumen tidak aktif
		else { ?>
			<li class="nav-item">
				<a href="?module=jenis">
					<i class="fas fa-clone"></i>
					<p>Jenis Dokumen</p>
				</a>
			</li>
		<?php
		}
		
		// jika menu profil instansi (tampil data / form ubah) dipilih, menu profil instansi aktif
		if ($_GET['module'] == 'pengajuan_surat') { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Surat</h4>
			</li>

			<li class="nav-item ">
				<a href="?module=pengajuan_surat">
					<i class="fas fa-window-restore"></i>
					<p>Pengajuan Surat</p>
				</a>
			</li>
			<li class="nav-item ">
				<a href="?module=pengajuan_surat">
					<i class="fas fa-envelope"></i>
					<p>Pengajuan</p>
				</a>
			</li>
			<li class="nav-item ">
				<a href="?module=pengajuan_surat">
					<i class="fas fa-folder"></i>
					<p>Riwayat Pengajuan</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu profil instansi tidak aktif
		else { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Surat</h4>
			</li>

			<li class="nav-item">
				<a href="?module=pengajuan_surat">
					<i class="fas fa-window-restore"></i>
					<p>Pengajuan Surat</p>
				</a>
			</li>
			<li class="nav-item ">
				<a href="?module=pengajuan_surat">
					<i class="fas fa-envelope"></i>
					<p>Pengajuan</p>
				</a>
			</li>
			<li class="nav-item ">
				<a href="?module=pengajuan_surat">
					<i class="fas fa-folder"></i>
					<p>Riwayat Pengajuan</p>
				</a>
			</li>
		<?php
		}

		// jika menu profil instansi (tampil data / form ubah) dipilih, menu profil instansi aktif
		if ($_GET['module'] == 'profil' || $_GET['module'] == 'form_ubah_profil') { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Pengaturan</h4>
			</li>

			<li class="nav-item active">
				<a href="?module=profil">
					<i class="fas fa-cog"></i>
					<p>Profil Instansi</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu profil instansi tidak aktif
		else { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Pengaturan</h4>
			</li>

			<li class="nav-item">
				<a href="?module=profil">
					<i class="fas fa-cog"></i>
					<p>Profil Instansi</p>
				</a>
			</li>
		<?php
		}

		// jika menu pengguna aplikasi (tampil data / form entri / form ubah) dipilih, menu pengguna aplikasi aktif
		if ($_GET['module'] == 'user' || $_GET['module'] == 'form_entri_user' || $_GET['module'] == 'form_ubah_user') { ?>
			<li class="nav-item active">
				<a href="?module=user">
					<i class="fas fa-user"></i>
					<p>Pengguna Aplikasi</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu pengguna aplikasi tidak aktif
		else { ?>
			<li class="nav-item">
				<a href="?module=user">
					<i class="fas fa-user"></i>
					<p>Pengguna Aplikasi</p>
				</a>
			</li>
		<?php
		}

		// jika menu tentang aplikasi dipilih, menu tentang aplikasi aktif
		if ($_GET['module'] == 'tentang') { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Bantuan</h4>
			</li>

			<li class="nav-item active">
				<a href="?module=tentang">
					<i class="fas fa-info-circle"></i>
					<p>Tentang Aplikasi</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu tentang aplikasi tidak aktif
		else { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Bantuan</h4>
			</li>

			<li class="nav-item">
				<a href="?module=tentang">
					<i class="fas fa-info-circle"></i>
					<p>Tentang Aplikasi</p>
				</a>
			</li>
		<?php
		}
	}
	// jika hak akses = BUIB, tampilkan menu
	elseif (in_array($_SESSION['hak_akses'], haystack: ['Pimpinan', 'SekretarisPimpinan'])) {
		// pengecekan menu aktif
		// jika menu beranda dipilih, menu beranda aktif
		if ($_GET['module'] == 'beranda') { ?>
			<li class="nav-item active">
				<a href="?module=beranda">
					<i class="fas fa-home"></i>
					<p>Beranda</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu beranda tidak aktif
		else { ?>
			<li class="nav-item">
				<a href="?module=beranda">
					<i class="fas fa-home"></i>
					<p>Beranda</p>
				</a>
			</li>
		<?php
		}

		// jika menu arsip dokumen (tampil data / tampil detail / form entri / form ubah) dipilih, menu arsip dokumen aktif
		if ($_GET['module'] == 'arsip' || $_GET['module'] == 'tampil_detail_arsip' || $_GET['module'] == 'form_entri_arsip' || $_GET['module'] == 'form_ubah_arsip') { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Arsip</h4>
			</li>

			<li class="nav-item active">
				<a href="?module=arsip">
					<i class="fas fa-folder-open"></i>
					<p>Arsip Dokumen</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu arsip dokumen tidak aktif
		else { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Arsip</h4>
			</li>

			<li class="nav-item">
				<a href="?module=arsip">
					<i class="fas fa-folder-open"></i>
					<p>Arsip Dokumen</p>
				</a>
			</li>
		<?php
		}

		// jika menu jenis dokumen (tampil data / form entri / form ubah) dipilih, menu jenis dokumen aktif
		if ($_GET['module'] == 'jenis' || $_GET['module'] == 'form_entri_jenis' || $_GET['module'] == 'form_ubah_jenis') { ?>
			<li class="nav-item active">
				<a href="?module=jenis">
					<i class="fas fa-clone"></i>
					<p>Jenis Dokumen</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu jenis dokumen tidak aktif
		else { ?>
			<li class="nav-item">
				<a href="?module=jenis">
					<i class="fas fa-clone"></i>
					<p>Jenis Dokumen</p>
				</a>
			</li>
		<?php
		}

		// jika menu tentang aplikasi dipilih, menu tentang aplikasi aktif
		if ($_GET['module'] == 'tentang') { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Bantuan</h4>
			</li>

			<li class="nav-item active">
				<a href="?module=tentang">
					<i class="fas fa-info-circle"></i>
					<p>Tentang Aplikasi</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu tentang aplikasi tidak aktif
		else { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Bantuan</h4>
			</li>

			<li class="nav-item">
				<a href="?module=tentang">
					<i class="fas fa-info-circle"></i>
					<p>Tentang Aplikasi</p>
				</a>
			</li>
		<?php
		}
	}
	// jika hak akses = Pengguna, tampilkan menu
	elseif ($_SESSION['hak_akses'] == 'Pengguna') {
		// pengecekan menu aktif
		// jika menu beranda dipilih, menu beranda aktif
		if ($_GET['module'] == 'beranda') { ?>
			<li class="nav-item active">
				<a href="?module=beranda">
					<i class="fas fa-home"></i>
					<p>Beranda</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu beranda tidak aktif
		else { ?>
			<li class="nav-item">
				<a href="?module=beranda">
					<i class="fas fa-home"></i>
					<p>Beranda</p>
				</a>
			</li>
		<?php
		}

		// jika menu arsip dokumen (tampil data / tampil detail) dipilih, menu arsip dokumen aktif
		if ($_GET['module'] == 'arsip' || $_GET['module'] == 'tampil_detail_arsip') { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Arsip</h4>
			</li>

			<li class="nav-item active">
				<a href="?module=arsip">
					<i class="fas fa-folder-open"></i>
					<p>Arsip Dokumen</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu arsip dokumen tidak aktif
		else { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Arsip</h4>
			</li>

			<li class="nav-item">
				<a href="?module=arsip">
					<i class="fas fa-folder-open"></i>
					<p>Arsip Dokumen</p>
				</a>
			</li>
		<?php
		}

		// jika menu tentang aplikasi dipilih, menu tentang aplikasi aktif
		if ($_GET['module'] == 'tentang') { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Bantuan</h4>
			</li>

			<li class="nav-item active">
				<a href="?module=tentang">
					<i class="fas fa-info-circle"></i>
					<p>Tentang Aplikasi</p>
				</a>
			</li>
		<?php
		}
		// jika tidak dipilih, menu tentang aplikasi tidak aktif
		else { ?>
			<li class="nav-section">
				<span class="sidebar-mini-icon">
					<i class="fa fa-ellipsis-h"></i>
				</span>
				<h4 class="text-section">Bantuan</h4>
			</li>

			<li class="nav-item">
				<a href="?module=tentang">
					<i class="fas fa-info-circle"></i>
					<p>Tentang Aplikasi</p>
				</a>
			</li>
		<?php
		}
	}
}
?>