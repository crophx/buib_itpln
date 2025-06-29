<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
	// alihkan ke halaman error 404
	header('location: 404.html');
}

// jika file di include oleh file lain, tampilkan isi file
else {
	// pengecekan hak akses untuk menampilkan konten sesuai dengan hak akses
	// jika hak akses = SuperAdmin atau hak akses = Pimpinan, atau hak akses = SekretarisPimpinan, tampilkan konten
	if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'BKI'])) { ?>
		<div class="panel-header">
			<div class="page-inner py-45">
				<div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
					<div class="page-header">
						<!-- judul halaman -->
						<h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Data Jenis Dokumen BKI</h4>
						<!-- breadcrumbs -->
						<ul class="breadcrumbs">
							<li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
							<li class="separator"><i class="flaticon-right-arrow"></i></li>
							<li class="nav-item"><a href="?module=beranda">Beranda</a></li>
							<li class="separator"><i class="flaticon-right-arrow"></i></li>
							<li class="nav-item"><a href="?module=bki">BKI</a></li>
							<li class="separator"><i class="flaticon-right-arrow"></i></li>
							<li class="nav-item"><a>Jenis Data</a></li>
						</ul>
					</div>
					<div class="ml-md-auto py-2 py-md-0">
						<!-- button tambahkan jenis -->
						<a href="#" class="btn btn-success btn-round" data-toggle="modal" data-target="#modalTambah"
							data-tooltip="tooltip" data-placement="top" title="Tambah">
							<i class="fa fa-plus mr-2 text-regular"> Tambahkan Data Baru</i>
						</a>
						<!-- modal tambahkan jenis -->
						<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel"
							aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<form action="modules/bki/jenis_dokumen_bki/proses_simpan.php" method="post">
										<div class="modal-header">
											<h5 class="modal-title" id="modalUbahLabel"><i class="fas fa-pencil-alt mr-2"></i>
												Tambahkan Jenis Dokumen</h5>
										</div>
										<div class="modal-body">
											<input type="hidden" name="id" value="">
											<!-- Form Jenis/Nama Dokumen -->
											<div class="form-group">
												<label>Jenis Dokumen</label>
												<input type="text" name="nama_dokumen" class="form-control" value="">
											</div>
											<!-- bagian Ubah Kode Singkat -->
											<div class="form-group">
												<label>Kode Singkat</label>
												<input type="text" name="kode_singkat" class="form-control" value="">
											</div>
											<!-- bagian Ubah Kode Singkat -->
											<div class="form-group">
												<label>Keterangan</label>
												<input type="text" name="keterangan" class="form-control" value="">
											</div>
										</div>
										<!-- Button Footer -->
										<div class="modal-footer">
											<button type="button" class="btn btn-default btn-round"
												data-dismiss="modal">Batal</button>
											<input type="submit" name="simpan" value="Simpan Perubahan"
												class="btn btn-success btn-round">
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="page-inner mt--5">
			<div class="card">
				<div class="card-header">
					<!-- judul tabel -->
					<div class="card-title">Data Nama Dokumen</div>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<!-- tabel untuk menampilkan data dari database -->
						<table id="basic-datatables" class="display table table-bordered table-striped table-hover">
							<thead>
								<tr>
									<th class="text-center">No.</th>
									<th class="text-center">Jenis Dokumen</th>
									<th class="text-center">Kode Singkat</th>
									<th class="text-center">Keterangan</th>
									<th class="text-center">Edit</th>
								</tr>
							</thead>
							<tbody>
								<?php
								// variabel untuk nomor urut tabel
								$no = 1;
								// sql statement untuk menampilkan data dari tabel "tbl_jenis_dokumen_bki"
								$query = mysqli_query($mysqli, "SELECT * FROM tbl_jenis_dokumen_bki;")
									or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
								// ambil data hasil query
								while ($data = mysqli_fetch_assoc($query)) {
									?>
									<!-- tampilkan data -->
									<tr>
										<td width="30" class="text-center"><?php echo $no++; ?></td>
										<td width="80"><?php echo $data['nama_dokumen']; ?></td>
										<td width="100" class="text-center"><?php echo $data['kode_singkat']; ?></td>
										<td width="120" class="text-center" value="-"><?php echo $data['keterangan']; ?></td>
										<td width="80" class="text-center">
											<div>
												<!-- Button Ubah -->
												<a href="#" class="btn btn-icon btn-round btn-success btn-sm mr-md-1"
													data-toggle="modal" data-target="#modalUbah<?php echo $data['id']; ?>"
													data-tooltip="tooltip" data-placement="top" title="Ubah">
													<i class="fas fa-pencil-alt fa-sm"></i>
												</a>
												<!-- modalUbah -->
												<div class="modal fade" id="modalUbah<?php echo $data['id']; ?>" tabindex="-1"
													role="dialog" aria-labelledby="modalUbahLabel" aria-hidden="true">
													<div class="modal-dialog" role="document">
														<div class="modal-content">
															<form action="modules/bki/jenis_dokumen_bki/proses_ubah.php"
																method="post">
																<div class="modal-header">
																	<h5 class="modal-title" id="modalUbahLabel"><i
																			class="fas fa-pencil-alt mr-2"></i> Ubah Jenis Dokumen
																	</h5>
																</div>
																<div class="modal-body">
																	<input type="hidden" name="id"
																		value="<?php echo $data['id']; ?>">
																	<!-- Form Jenis/Nama Dokumen -->
																	<div class="form-group">
																		<label>Jenis Dokumen</label>
																		<input type="text" name="nama_dokumen" class="form-control"
																			value="<?php echo htmlspecialchars($data['nama_dokumen']); ?>">
																	</div>

																	<!-- bagian Ubah Kode Singkat -->
																	<div class="form-group">
																		<label>Kode Singkat</label>
																		<input type="text" name="kode_singkat" class="form-control"
																			value="<?php echo htmlspecialchars($data['kode_singkat']); ?>">
																	</div>

																	<!-- bagian Ubah Kode Singkat -->
																	<div class="form-group">
																		<label>Keterangan</label>
																		<input type="text" name="keterangan" class="form-control"
																			value="<?php echo htmlspecialchars($data['keterangan']); ?>">
																	</div>
																</div>
																<!-- Button Footer -->
																<div class="modal-footer">
																	<button type="button" class="btn btn-default btn-round"
																		data-dismiss="modal">Batal</button>

																	<input type="submit" name="simpan" value="Simpan Perubahan"
																		class="btn btn-success btn-round">
																</div>
															</form>
														</div>
													</div>
												</div>
											</div>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript">
			$(document).ready(function () {
				// dapatkan parameter URL
				let queryString = window.location.search;
				let urlParams = new URLSearchParams(queryString);
				// ambil data dari URL
				let pesan = urlParams.get('pesan');
				let nomor = urlParams.get('nomor');

				// menampilkan pesan sesuai dengan proses yang dijalankan
				// jika pesan = 1
				if (pesan === '1') {
					// tampilkan pesan sukses simpan data
					$.notify({
						title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
						message: 'Data arsip dokumen berhasil disimpan.'
					}, {
						type: 'success'
					});
				}
				// jika pesan = 2
				else if (pesan === '2') {
					// tampilkan pesan sukses ubah data
					$.notify({
						title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
						message: 'Data nama dokumen berhasil diubah.'
					}, {
						type: 'success'
					});
				}
				// jika pesan = 3
				else if (pesan === '3') {
					// tampilkan pesan sukses hapus data
					$.notify({
						title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
						message: 'Data arsip dokumen berhasil dihapus.'
					}, {
						type: 'success'
					});
				}
				// jika pesan = 4
				else if (pesan === '4') {
					// tampilkan pesan gagal unggah file
					$.notify({
						title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
						message: 'Tipe file dokumen tidak sesuai. Harap unggah file dokumen yang memiliki tipe <strong>*.pdf</strong>.'
					}, {
						type: 'danger'
					});
				}
				// jika pesan = 5
				else if (pesan === '5') {
					// tampilkan pesan gagal unggah file
					$.notify({
						title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
						message: 'Ukuran file dokumen lebih dari 10 Mb. Harap unggah file dokumen yang memiliki ukuran <strong>maksimal 10 Mb</strong>.'
					}, {
						type: 'danger'
					});
				}
			});
		</script>
		<?php
	}
	// jika hak akses = Pengguna, tampilkan konten
	elseif ($_SESSION['hak_akses'] == 'Pengguna') { ?>
		<div class="panel-header">
			<div class="page-inner py-4">
				<div class="page-header">
					<!-- judul halaman -->
					<h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Arsip Dokumen</h4>
					<!-- breadcrumbs -->
					<ul class="breadcrumbs">
						<li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
						<li class="separator"><i class="flaticon-right-arrow"></i></li>
						<li class="nav-item"><a href="?module=arsip">Arsip</a></li>
						<li class="separator"><i class="flaticon-right-arrow"></i></li>
						<li class="nav-item"><a>Data</a></li>
					</ul>
				</div>
			</div>
		</div>

		<div class="page-inner mt--5">
			<div class="card">
				<div class="card-header">
					<!-- judul tabel -->
					<div class="card-title">Data Arsip Dokumen</div>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<!-- tabel untuk menampilkan data dari database -->
						<table id="basic-datatables" class="display table table-bordered table-striped table-hover">
							<thead>
								<tr>
									<th class="text-center">No.</th>
									<th class="text-center">Jenis Dokumen</th>
									<th class="text-center">Bulan, Tahun</th>
									<th class="text-center">Tahun Anggaran</th>
									<th class="text-center">DIPA</th>
									<th class="text-center">E-Doc</th>
								</tr>
							</thead>
							<tbody>
								<?php
								// variabel untuk nomor urut tabel
								$no = 1;
								// sql statement untuk menampilkan data dari tabel "tbl_arsip" dan "tbl_jenis"
								$query = mysqli_query($mysqli, "SELECT a.id_arsip, a.jenis_dokumen, a.bulan_tahun, a.tahun_anggaran, a.dipa, b.nama_jenis 
																FROM tbl_arsip as a INNER JOIN tbl_jenis as b ON a.jenis_dokumen=b.id_jenis 
																ORDER BY a.id_arsip DESC")
									or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
								// ambil data hasil query
								while ($data = mysqli_fetch_assoc($query)) { ?>
									<!-- tampilkan data -->
									<tr>
										<td width="30" class="text-center"><?php echo $no++; ?></td>
										<td width="200"><?php echo $data['nama_jenis']; ?></td>
										<td width="100" class="text-center"><?php echo $data['bulan_tahun']; ?></td>
										<td width="80" class="text-center"><?php echo $data['tahun_anggaran']; ?></td>
										<td width="80" class="text-center"><?php echo $data['dipa']; ?></td>
										<td width="80" class="text-center">
											<a href="?module=tampil_detail_arsip&id=<?php echo $data['id_arsip']; ?>"
												class="btn btn-icon btn-round btn-warning btn-sm mr-md-1" data-tooltip="tooltip"
												data-placement="top" title="Tampilkan Dokumen">
												<i class="far fa-file-alt"></i>
											</a>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
?>