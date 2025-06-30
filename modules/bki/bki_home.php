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
						<h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Bagian Kerjasama Internasional (BKI)</h4>
						<!-- breadcrumbs -->
						<ul class="breadcrumbs">
							<li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
							<li class="separator"><i class="flaticon-right-arrow"></i></li>
							<li class="nav-item"><a href="?module=beranda">Beranda</a></li>
							<li class="separator"><i class="flaticon-right-arrow"></i></li>
							<li class="nav-item"><a>BKI</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>


		<!-- Tabel MoU -->
		<div class="page-inner mt--5">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div class="card-title">Data Memorandum of Understanding (MoU)</div>

					<div>
						<!-- Button tambah mitra -->
						<a href="?module=mitra_bki" class="btn btn-warning btn-round">
							<span class="btn-label"><i class="fas fa-users mr-2"></i></span> Mitra
						</a>
						<!-- Button tambah jenis dokumen -->
						<a href="?module=jenis_dokumen_bki" class="btn btn-primary btn-round ml-2">
							<span class="btn-label"><i class="fas fa-sitemap mr-2"></i></span> Jenis Dokumen
						</a>
						<!-- Button tambah dokumen -->
						<a href="?module=form_entri_MoU_bki" class="btn btn-success btn-round ml-2">
							<span class="btn-label"><i class="fas fa-edit mr-2"></i></span> Input MoU
						</a>
					</div>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<!-- tabel untuk menampilkan data dari database -->
						<table id="mou-datatables" class="display table table-bordered table-striped table-hover">
							<thead>
								<tr>
									<th class="text-center">No.</th>
									<th class="text-center">No Dokumen</th>
									<th class="text-center">Mitra</th>
									<th class="text-center">Jangka Waktu (Tahun)</th>
									<th class="text-center">Tentang</th>
									<th class="text-center">Tanggal Penandatanganan</th>
									<th class="text-center">PIC</th>
									<th class="text-center">Link Dokumen</th>
									<th class="text-center">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php
								// variabel untuk nomor urut tabel
								$no = 1;
								// sql statement untuk menampilkan data dari tabel "tbl_bki" dan "tbl_jenis_dokumen_bki" dan "tbl_mitra_bki"
								$query = mysqli_query($mysqli, "SELECT 
									mou.id,
									mou.no_dokumen,
									mou.tentang,
									mou.tanggal_penandatanganan,
									mou.link_dokumen_MoU,
									mou.jangka_waktu_tahun,
									mitra.nama_mitra,
									mitra.negara,
									pic.nama_bagian AS pic_nama
								FROM 
									tbl_mou AS mou
								LEFT JOIN 
									tbl_mitra_bki AS mitra ON mou.mitra_id = mitra.id
								LEFT JOIN 
									tbl_pic_bagian AS pic ON mou.pic_bagian_id = pic.id 
								ORDER BY 
									mou.id DESC;")
									or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
								// ambil data hasil query
								while ($data = mysqli_fetch_assoc($query)) {
									?>
									<!-- tampilkan data -->
									<tr>
										<td width="30" class="text-center"><?php echo $no++; ?>
										</td>
										<td width="80" class="text-center">
											<?php echo $data['no_dokumen']; ?>
										</td>
										<td width="100" class="text-center"><?php echo $data['nama_mitra']; ?>
										</td>
										<td width="80" class="text-center"><?php echo $data['jangka_waktu_tahun'] . ' Tahun'; ?>
										</td>
										<td width="80" class="text-center"><?php echo $data['tentang']; ?></td>
										<td width="80" class="text-center"><?php echo $data['tanggal_penandatanganan']; ?></td>
										<td width="80" class="text-center"><?php echo $data['pic_nama']; ?></td>
										<td width="80" class="text-center">
											<?php if (!empty($data['link_dokumen_MoU'])): ?>
												<a href="<?php echo htmlspecialchars($data['link_dokumen_MoU']); ?>" target="_blank"
													rel="noopener noreferrer" class="btn btn-info btn-sm" title="Buka Dokumen">
													<i class="fas fa-link"></i>
												</a>
											<?php else: ?>
												-
											<?php endif; ?>
										</td>
										<td width="80" class="text-center">
											<div>
												<!-- Button Ubah -->
												<a href="#" class="btn btn-icon btn-round btn-success btn-sm mr-md-1"
													data-toggle="modal" data-target="#modalUbah<?php echo $data['id']; ?>"
													data-tooltip="tooltip" data-placement="top" title="Ubah"> <i
														class="fas fa-pencil-alt fa-sm"></i>
												</a>
												<!-- modalUbah -->
												<div class="modal fade" id="modalUbah<?php echo $data['id']; ?>" tabindex="-1"
													role="dialog" aria-labelledby="modalUbahLabel" aria-hidden="true">
													<div class="modal-dialog modal-xl" role="document">
														<div class="modal-content">
															<form action="modules/bki/proses_ubah.php" method="post">
																<div class="modal-header bg-warning">
																	<h5 class="modal-title" id="modalUbahLabel"><i
																			class="fas fa-pencil-alt mr-2"></i>Ubah Data</h5>
																</div>
																<div class="modal-body text-left">
																	<input type="hidden" name="id"
																		value="<?php echo $data['id']; ?>">

																	<div class="form-group">
																		<label>Mitra <span class="text-danger">*</span></label>

																		<select name="mitra_id" class="form-control" required>
																			<option value="<?php echo $data['nama_mitra']; ?>"
																				selected>
																				<?php echo htmlspecialchars($data['nama_mitra']); // Teks yang ditampilkan untuk pengguna tetap nama mitranya ?>
																			</option>

																			<option disabled>-- Pilih Mitra Lain --</option>

																			<?php
																			// Query untuk mengambil opsi mitra lain
																			$query_mitra_modal = mysqli_query($mysqli, "SELECT id, nama_mitra FROM tbl_mitra_bki ORDER BY nama_mitra ASC");
																			while ($data_mitra_modal = mysqli_fetch_assoc($query_mitra_modal)) {
																				// Tampilkan hanya jika ID-nya berbeda dengan yang sedang dipilih
																				if ($data_mitra_modal['id'] != $data['mitra_id']) {
																					echo "<option value='{$data_mitra_modal['id']}'>{$data_mitra_modal['nama_mitra']}</option>";
																				}
																			}
																			?>
																		</select>
																	</div>

																	<div class="form-group">
																		<label>No. Dokumen <span
																				class="text-danger">*</span></label>
																		<input type="text" name="no_dokumen" class="form-control"
																			value="<?php echo htmlspecialchars($data['no_dokumen']); ?>"
																			required>
																	</div>

																	<div class="form-group">
																		<label>PIC (Bagian / Prodi) <span
																				class="text-danger">*</span></label>
																		<select name="pic_bagian_id" class="form-control" required>
																			<option value="<?php echo $data['pic_nama']; ?>"
																				selected>
																				<?php echo htmlspecialchars($data['pic_nama']); ?>
																			</option>
																			<option disabled>-- Pilih Mitra Lain --</option>
																			<?php
																			$query_pic_modal = mysqli_query($mysqli, "SELECT id, nama_bagian FROM tbl_pic_bagian ORDER BY nama_bagian ASC");
																			while ($data_pic_modal = mysqli_fetch_assoc($query_pic_modal)) {
																				if ($data_pic_modal['id'] != $data['pic_bagian_id']) {
																					echo "<option value='{$data_pic_modal['id']}'>{$data_pic_modal['nama_bagian']}</option>";
																				}
																			}
																			?>
																		</select>
																	</div>

																	<div class="form-group">
																		<label>Tentang <span class="text-danger">*</span></label>
																		<textarea name="tentang" class="form-control" rows="3"
																			required><?php echo htmlspecialchars($data['tentang']); ?></textarea>
																	</div>

																	<div class="form-group">
																		<label>Jangka Waktu (Tahun) <span
																				class="text-danger">*</span></label>
																		<input type="number" name="jangka_waktu_tahun"
																			class="form-control"
																			value="<?php echo $data['jangka_waktu_tahun']; ?>"
																			required>
																	</div>

																	<div class="form-group">
																		<label>Tanggal Penandatanganan<span
																				class="text-danger">*</span></label>
																		<input type="date" name="tanggal_penandatanganan"
																			class="form-control"
																			value="<?php echo $data['tanggal_penandatanganan']; ?>"
																			required>
																	</div>

																	<div class="form-group">
																		<label>Link Dokumen MoU<span
																				class="text-danger"></span></label>
																		<input type="urls" name="link_dokumen_MoU"
																			class="form-control" placeholder="Masukkan link..."
																			value="<?php echo $data['link_dokumen_MoU']; ?>">
																	</div>

																</div>

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
												<!-- Button Hapus -->
												<a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal"
													data-target="#modalHapus<?php echo $data['id']; ?>" data-tooltip="tooltip"
													data-placement="top" title="Hapus"> <i class="fas fa-trash fa-sm"></i>
												</a>
												<!-- modalHapus -->
												<div class="modal fade" id="modalHapus<?php echo $data['id']; ?>" tabindex="-1"
													role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
													<div class="modal-dialog modal-sm" role="document">
														<div class="modal-content">
															<div class="modal-header">
																<h5 class="modal-title" id="modalHapusLabel"><i
																		class="fas fa-trash mr-2"></i> Hapus Data</h5>
															</div>
															<div class="modal-body text-left">
																Anda yakin ingin menghapus dokumen
																<strong>
																	<?php echo htmlspecialchars($data['jenis_dokumen']); ?>
																</strong>
																dengan nomor
																<strong>
																	<?php echo htmlspecialchars($data['no_dokumen']); ?>
																</strong>?
															</div>
															<div class="modal-footer">
																<button type="button" class="btn btn-default btn-round"
																	data-dismiss="modal">Batal</button>
																<a href="modules/bki/proses_hapus.php?id=<?php echo $data['id']; ?>"
																	class="btn btn-danger btn-round">Ya, Hapus</a>
															</div>
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

		<!-- Tabel PKS -->
		<div class="page-inner mt--5">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div class="card-title">Daftar Dokumen PKS</div>
					<div>
						<!-- Button tambah mitra -->
						<a href="?module=mitra_bki" class="btn btn-warning btn-round">
							<span class="btn-label"><i class="fas fa-users mr-2"></i></span> Mitra
						</a>
						<!-- Button tambah jenis dokumen -->
						<a href="?module=jenis_dokumen_bki" class="btn btn-primary btn-round ml-2">
							<span class="btn-label"><i class="fas fa-sitemap mr-2"></i></span> Jenis Dokumen
						</a>
						<!-- Button tambah dokumen -->
						<a href="?module=form_entri_PKS_bki" class="btn btn-success btn-round ml-2">
							<span class="btn-label"><i class="fas fa-edit mr-2"></i></span> Input PKS
						</a>
					</div>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table id="pks-datatables" class="display table table-bordered table-striped table-hover">
							<thead>
								<tr>
									<th class="text-center">No.</th>
									<th>No. Dokumen PKS</th>
									<th>Mitra</th>
									<th>Tentang</th>
									<th class="text-center">Tgl. TTD</th>
									<th class="text-center">PIC</th>
									<th class="text-center">MoU Induk</th>
									<th class="text-center">Link Dokumen PKS</th>
									<th class="text-center">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$no = 1;
								// Query yang sudah kita buat tadi
								$query = mysqli_query($mysqli, "SELECT
                                                                pks.id AS pks_id, pks.no_dokumen AS no_dokumen_pks,
																negara, pks.jangka_waktu_tahun, pks.mitra_id, pks.pic_bagian_id,
                                                                pks.tentang AS tentang_pks, pks.tanggal_penandatanganan AS tgl_pks,
                                                                pks.link_dokumen_pks, mitra.nama_mitra,
                                                                pic.nama_bagian AS pic_pks, mou.no_dokumen AS no_dokumen_mou
                                                            FROM tbl_pks AS pks
                                                            LEFT JOIN tbl_mou AS mou ON pks.mou_id = mou.id
                                                            LEFT JOIN tbl_mitra_bki AS mitra ON pks.mitra_id = mitra.id
                                                            LEFT JOIN tbl_pic_bagian AS pic ON pks.pic_bagian_id = pic.id
                                                            ORDER BY pks.tanggal_penandatanganan DESC")
									or die('Ada kesalahan pada query tampil data PKS: ' . mysqli_error($mysqli));

								while ($data = mysqli_fetch_assoc($query)) {
									// Format tanggal agar lebih mudah dibaca
									$tanggal_pks = date('d-m-Y', strtotime($data['tgl_pks']));
									?>
									<tr>
										<td width="30" class="text-center"><?php echo $no++; ?></td>
										<td width="150"><?php echo htmlspecialchars($data['no_dokumen_pks']); ?></td>
										<td width="180"><?php echo htmlspecialchars($data['nama_mitra']); ?></td>
										<td><?php echo htmlspecialchars($data['tentang_pks']); ?></td>
										<td width="100" class="text-center"><?php echo $tanggal_pks; ?></td>
										<td width="100" class="text-center"><?php echo htmlspecialchars($data['pic_pks']); ?></td>
										<td width="120" class="text-center">
											<?php echo !empty($data['no_dokumen_mou']) ? htmlspecialchars($data['no_dokumen_mou']) : '-'; ?>
										</td>
										<td width="120" class="text-center">
											<?php if (!empty($data['link_dokumen_pks'])) { ?>
												<a href="<?php echo htmlspecialchars($data['link_dokumen_pks']); ?>" target="_blank"
													class="btn btn-icon btn-info btn-sm" data-toggle="tooltip" data-placement="top"
													title="Lihat Dokumen">
													<i class="fas fa-link"></i>
												</a>
											<?php } ?>
										</td>
										<td width="80" class="text-center">
											<div>
												<!-- Button Ubah -->
												<a href="#" class="btn btn-icon btn-round btn-success btn-sm mr-md-1"
													data-toggle="modal" data-target="#modalUbahpks<?php echo $data['pks_id']; ?>"
													data-tooltip="tooltip" data-placement="top" title="Ubah"> <i
														class="fas fa-pencil-alt fa-sm"></i>
												</a>
												<!-- modalUbah -->
												<div class="modal fade" id="modalUbahpks<?php echo $data['pks_id']; ?>"
													tabindex="-1" role="dialog" aria-labelledby="modalUbahLabel" aria-hidden="true">
													<div class="modal-dialog modal-xl" role="document">
														<div class="modal-content">
															<form action="modules/bki/pks/proses_ubah.php" method="post">
																<div class="modal-header bg-warning">
																	<h5 class="modal-title" id="modalUbahLabel"><i
																			class="fas fa-pencil-alt mr-2"></i>Ubah Data</h5>
																</div>
																<div class="modal-body text-left">
																	<input type="hidden" name="pks_id"
																		value="<?php echo $data['pks_id']; ?>">

																	<div class="form-group">
																		<label>Mitra <span class="text-danger">*</span></label>

																		<select name="mitra_id" class="form-control" required>
																			<option value="<?php echo $data['nama_mitra']; ?>"
																				selected>
																				<?php echo htmlspecialchars($data['nama_mitra']); // Teks yang ditampilkan untuk pengguna tetap nama mitranya ?>
																			</option>

																			<option disabled>-- Pilih Mitra Lain --</option>

																			<?php
																			// Query untuk mengambil opsi mitra lain
																			$query_mitra_modal = mysqli_query($mysqli, "SELECT id, nama_mitra FROM tbl_mitra_bki ORDER BY nama_mitra ASC");
																			while ($data_mitra_modal = mysqli_fetch_assoc($query_mitra_modal)) {
																				// Tampilkan hanya jika ID-nya berbeda dengan yang sedang dipilih
																				if ($data_mitra_modal['id'] != $data['mitra_id']) {
																					echo "<option value='{$data_mitra_modal['id']}'>{$data_mitra_modal['nama_mitra']}</option>";
																				}
																			}
																			?>
																		</select>
																	</div>

																	<div class="form-group">
																		<label>No. Dokumen <span
																				class="text-danger">*</span></label>
																		<input type="text" name="no_dokumen_pks"
																			class="form-control"
																			value="<?php echo htmlspecialchars($data['no_dokumen_pks']); ?>"
																			required>
																	</div>

																	<div class="form-group">
																		<label>No. Dokumen MoU<span
																				class="text-danger">*</span></label>
																		<input type="text" name="no_dokumen_mou"
																			class="form-control"
																			value="<?php echo htmlspecialchars($data['no_dokumen_mou']); ?>"
																			required>
																	</div>

																	<div class="form-group">
																		<label>PIC (Bagian / Prodi) <span
																				class="text-danger">*</span></label>
																		<select name="pic_pks" class="form-control" required>
																			<option value="<?php echo $data['pic_pks']; ?>"
																				selected>
																				<?php echo htmlspecialchars($data['pic_pks']); ?>
																			</option>
																			<option disabled>-- Pilih Mitra Lain --</option>
																			<?php
																			$query_pic_modal = mysqli_query($mysqli, "SELECT id, nama_bagian FROM tbl_pic_bagian ORDER BY nama_bagian ASC");
																			while ($data_pic_modal = mysqli_fetch_assoc($query_pic_modal)) {
																				if ($data_pic_modal['id'] != $data['pic']) {
																					echo "<option value='{$data_pic_modal['id']}'>{$data_pic_modal['pic_pks']}</option>";
																				}
																			}
																			?>
																		</select>
																	</div>

																	<div class="form-group">
																		<label>Tentang <span class="text-danger">*</span></label>
																		<textarea name="tentang_pks" class="form-control" rows="3"
																			required><?php echo htmlspecialchars($data['tentang_pks']); ?></textarea>
																	</div>

																	<div class="form-group">
																		<label>Jangka Waktu (Tahun) <span
																				class="text-danger">*</span></label>
																		<input type="number" name="jangka_waktu_tahun"
																			class="form-control"
																			value="<?php echo $data['jangka_waktu_tahun']; ?>"
																			required>
																	</div>

																	<div class="form-group">
																		<label>Tanggal Penandatanganan<span
																				class="text-danger">*</span></label>
																		<input type="date" name="tgl_pks" class="form-control"
																			value="<?php echo $data['tgl_pks']; ?>" required>
																	</div>

																	<div class="form-group">
																		<label>Link Dokumen PKS<span
																				class="text-danger"></span></label>
																		<input type="urls" name="link_dokumen_pks"
																			class="form-control" placeholder="Masukkan link..."
																			value="<?php echo $data['link_dokumen_pks']; ?>">
																	</div>

																</div>

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
												<!-- Button Hapus -->
												<a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal"
													data-target="#modalHapus<?php echo $data['pks_id']; ?>" data-tooltip="tooltip"
													data-placement="top" title="Hapus"> <i class="fas fa-trash fa-sm"></i>
												</a>
												<!-- modalHapus -->
												<div class="modal fade" id="modalHapus<?php echo $data['pks_id']; ?>" tabindex="-1"
													role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
													<div class="modal-dialog modal-sm" role="document">
														<div class="modal-content">
															<div class="modal-header">
																<h5 class="modal-title" id="modalHapusLabel"><i
																		class="fas fa-trash mr-2"></i> Hapus Data</h5>
															</div>
															<div class="modal-body text-left">
																Anda yakin ingin menghapus dokumen
																<strong>
																	<?php echo htmlspecialchars($data['jenis_dokumen']); ?>
																</strong>
																dengan nomor
																<strong>
																	<?php echo htmlspecialchars($data['no_dokumen']); ?>
																</strong>?
															</div>
															<div class="modal-footer">
																<button type="button" class="btn btn-default btn-round"
																	data-dismiss="modal">Batal</button>
																<a href="modules/bki/proses_hapus.php?id=<?php echo $data['id']; ?>"
																	class="btn btn-danger btn-round">Ya, Hapus</a>
															</div>
														</div>
													</div>
												</div>
											</div>
										</td>
									</tr>

									<div class="modal fade" id="modalHapus<?php echo $data['pks_id']; ?>" tabindex="-1"
										role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
										<div class="modal-dialog" role="document">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title" id="modalHapusLabel"><i
															class="fas fa-exclamation-triangle mr-2"></i> Konfirmasi Hapus</h5>
													<button type="button" class="close" data-dismiss="modal" aria-label="Close">
														<span aria-hidden="true">&times;</span>
													</button>
												</div>
												<div class="modal-body">
													<p>Anda yakin ingin menghapus data PKS dengan nomor
														<strong><?php echo htmlspecialchars($data['no_dokumen_pks']); ?></strong>?
													</p>
												</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-secondary btn-round"
														data-dismiss="modal">Batal</button>
													<a href="modules/pks/proses_hapus.php?id=<?php echo $data['pks_id']; ?>"
														class="btn btn-danger btn-round">Ya, Hapus</a>
												</div>
											</div>
										</div>
									</div>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- Table Rencana -->
		<div class="page-inner mt--5">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div class="card-title mb-0">Data Rencana Kegiatan BKI</div>
					<div>
						<!-- Button tambah mitra -->
						<a href="?module=mitra_bki" class="btn btn-warning btn-round">
							<span class="btn-label"><i class="fas fa-users mr-2"></i></span> Mitra
						</a>
						<!-- Button tambah rencana  -->
						<a href="#" class="btn btn-success btn-round ml-2" data-toggle="modal"
							data-target="#modalTambahRencana">
							<span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Tambahkan Rencana
						</a>
					</div>
					<!-- Tambahkan Modal -->
					<div class="modal fade" id="modalTambahRencana" tabindex="-1" role="dialog"
						aria-labelledby="modalTambahLabel" aria-hidden="true">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<form action="modules/bki/proses_simpan_rencana.php" method="post">
									<div class="modal-header">
										<h5 class="modal-title" id="modalTambahLabel"><i class="fas fa-plus-circle mr-2"></i>
											Tambah Rencana Kegiatan</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<div class="form-group">
											<label>Rencana Mitra Internasional <span class="text-danger">*</span></label>
											<select name="mitra_id" class="form-control" required>
												<option value="" selected disabled>-- Pilih Mitra --</option>
												<?php
												// Mengambil data mitra untuk ditampilkan di dropdown
												$query_mitra_rencana = mysqli_query($mysqli, "SELECT id, nama_mitra FROM tbl_mitra_bki ORDER BY nama_mitra ASC");
												while ($data_mitra = mysqli_fetch_assoc($query_mitra_rencana)) {
													echo "<option value='{$data_mitra['id']}'>{$data_mitra['nama_mitra']}</option>";
												}
												?>
											</select>
										</div>
										<div class="form-group">
											<label>Tentang (Deskripsi Rencana) <span class="text-danger">*</span></label>
											<textarea name="tentang" class="form-control" rows="3" required></textarea>
										</div>
										<div class="form-group">
											<label>Bulan Target Realisasi <span class="text-danger">*</span></label>
											<input type="date" name="target_realisasi" class="form-control" required>
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default btn-round"
											data-dismiss="modal">Batal</button>
										<input type="submit" name="simpan" value="Simpan Rencana"
											class="btn btn-success btn-round">
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<!-- tabel untuk menampilkan data dari database -->
						<table id="basic-datatables" class="display table table-bordered table-striped table-hover">
							<thead>
								<tr>
									<th class="text-center">No.</th>
									<th class="text-center">Rencana Mitra Internasional</th>
									<th class="text-center">Negara</th>
									<th class="text-center">Tentang</th>
									<th class="text-center">Bulan Target Realisasi</th>
									<th class="text-center">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php
								// variabel untuk nomor urut tabel
								$no = 1;
								// sql statement untuk menampilkan data dari tabel "tbl_bki" dan "tbl_jenis_dokumen_bki" dan "tbl_mitra_bki"
								$query = mysqli_query($mysqli, "SELECT 
										rk.id,
										m.nama_mitra AS rencana_mitra_internasional,
										m.negara,
										rk.tentang,
										-- Menggunakan DATE_FORMAT untuk mengubah format tanggal
										DATE_FORMAT(rk.target_realisasi, '%M %Y') AS bulan_target_realisasi
										FROM 
											tbl_rk_bki AS rk
										JOIN 
											tbl_mitra_bki AS m ON rk.mitra_id = m.id
										ORDER BY 
											rk.target_realisasi ASC;")
									or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
								// ambil data hasil query
								while ($data = mysqli_fetch_assoc($query)) {
									?>
									<!-- tampilkan data -->
									<tr>
										<td width="30" class="text-center"><?php echo $no++; ?>
										</td>
										<td width="80">
											<?php echo $data['rencana_mitra_internasional']; ?>
										</td>
										<td width="100" class="text-center"><?php echo $data['negara']; ?></td>
										<td width="120" class="text-center"><?php echo $data['tentang']; ?></td>
										<td width="80" class="text-center"><?php echo $data['bulan_target_realisasi']; ?></td>
										<td width="10" class="text-center">
											<div>
												<!-- Button Hapus -->
												<a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal"
													data-target="#modalHapus<?php echo $data['id']; ?>" data-tooltip="tooltip"
													data-placement="top" title="Hapus"> <i class="fas fa-trash fa-sm"></i>
												</a>
												<!-- modalHapus -->
												<div class="modal fade" id="modalHapus<?php echo $data['id']; ?>" tabindex="-1"
													role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
													<div class="modal-dialog modal-sm" role="document">
														<div class="modal-content">
															<div class="modal-header">
																<h5 class="modal-title" id="modalHapusLabel"><i
																		class="fas fa-trash mr-2"></i> Hapus Data</h5>
															</div>
															<div class="modal-body text-left">
																Anda yakin ingin menghapus dokumen
																<strong>
																	<?php echo htmlspecialchars($data['jenis_dokumen']); ?>
																</strong>
																dengan nomor
																<strong>
																	<?php echo htmlspecialchars($data['no_dokumen']); ?>
																</strong>?
															</div>
															<div class="modal-footer">
																<button type="button" class="btn btn-default btn-round"
																	data-dismiss="modal">Batal</button>
																<a href="modules/bki/proses_hapus.php?id=<?php echo $data['no_urut']; ?>"
																	class="btn btn-danger btn-round">Ya, Hapus</a>
															</div>
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

		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

		<script type="text/javascript">
			$(document).ready(function () {
				// Inisialisasi DataTables untuk setiap tabel
				$('#mou-datatables').DataTable({ "pageLength": 5 });
				$('#pks-datatables').DataTable({ "pageLength": 5 });

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
						message: 'Data dokumen BKI berhasil disimpan.'
					}, {
						type: 'success'
					});
				}
				// jika pesan = 2
				else if (pesan === '2') {
					// tampilkan pesan sukses ubah data
					$.notify({
						title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
						message: 'Data dokumen BKI berhasil diubah.'
					}, {
						type: 'success'
					});
				}
				// jika pesan = 3
				else if (pesan === '3') {
					// tampilkan pesan sukses hapus data
					$.notify({
						title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
						message: 'Data dokumen BKI berhasil dihapus.'
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
				// jika pesan = 6
				else if (pesan === '6') {
					// tampilkan pesan gagal unggah file
					$.notify({
						title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
						message: 'Anda tidak berhak untuk menghapus atau mengakses file ini.'
					}, {
						type: 'danger'
					});
				}
			});
		</script>
		<?php
	}
}
?>