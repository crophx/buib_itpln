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
					<div class="ml-md-auto py-2 py-md-0">
						<!-- button entri data -->
						<a href="?module=mitra_bki" class="btn btn-warning btn-round">
							<span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Mitra
						</a>
					</div>
					<div class="ml-md-auto py-2 py-md-0">
						<!-- button jenis dokumen -->
						<a href="?module=jenis_dokumen_bki" class="btn btn-primary btn-round">
							<span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Jenis Dokumen
						</a>
					</div>
					<div class="ml-md-auto py-2 py-md-0">
						<!-- button entri data -->
						<a href="?module=form_entri_dokumen_bki" class="btn btn-success btn-round">
							<span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Entri Dokumen
						</a>
					</div>
				</div>
			</div>
		</div>

		<?php
		// Query untuk data chart - Jumlah dokumen per negara
		$query_chart_negara = mysqli_query($mysqli, "SELECT 
		m.negara, 
		COUNT(b.id) as jumlah_dokumen,
		SUM(CASE WHEN b.link_dokumen_MoU IS NOT NULL AND b.link_dokumen_MoU != '' THEN 1 ELSE 0 END) as jumlah_mou,
		SUM(CASE WHEN b.link_dokumen_PKS IS NOT NULL AND b.link_dokumen_PKS != '' THEN 1 ELSE 0 END) as jumlah_pks
		FROM tbl_bki AS b
		LEFT JOIN tbl_mitra_bki AS m ON b.mitra_id = m.id
		WHERE m.negara IS NOT NULL AND m.negara != ''
		GROUP BY m.negara
		ORDER BY jumlah_dokumen DESC")
			or die('Ada kesalahan pada query chart negara: ' . mysqli_error($mysqli));

		// Query untuk data chart - Jenis dokumen
		$query_chart_jenis = mysqli_query($mysqli, "SELECT 
		jd.kode_singkat,
		jd.nama_dokumen,
		COUNT(b.id) as jumlah
		FROM tbl_bki AS b
		LEFT JOIN tbl_jenis_dokumen_bki AS jd ON b.jenis_dokumen_id = jd.id
		WHERE jd.kode_singkat IS NOT NULL
		GROUP BY jd.id, jd.kode_singkat, jd.nama_dokumen
		ORDER BY jumlah DESC")
			or die('Ada kesalahan pada query chart jenis dokumen: ' . mysqli_error($mysqli));

		// Menyiapkan data untuk JavaScript
		$data_negara = [];
		$data_jenis = [];
		$labels_negara = [];
		$values_negara = [];
		$values_mou = [];
		$values_pks = [];
		$labels_jenis = [];
		$values_jenis = [];

		// Mengambil data negara
		while ($row = mysqli_fetch_assoc($query_chart_negara)) {
			$data_negara[] = $row;
			$labels_negara[] = $row['negara'];
			$values_negara[] = (int) $row['jumlah_dokumen'];
			$values_mou[] = (int) $row['jumlah_mou'];
			$values_pks[] = (int) $row['jumlah_pks'];
		}

		// Mengambil data jenis dokumen
		while ($row = mysqli_fetch_assoc($query_chart_jenis)) {
			$data_jenis[] = $row;
			$labels_jenis[] = $row['kode_singkat'];
			$values_jenis[] = (int) $row['jumlah'];
		}

		// Query untuk statistik umum
		$query_stats = mysqli_query($mysqli, "SELECT 
			COUNT(DISTINCT m.negara) as total_negara,
			COUNT(DISTINCT m.id) as total_mitra,
			COUNT(b.id) as total_dokumen,
			SUM(CASE WHEN b.link_dokumen_MoU IS NOT NULL AND b.link_dokumen_MoU != '' THEN 1 ELSE 0 END) as total_mou,
			SUM(CASE WHEN b.link_dokumen_PKS IS NOT NULL AND b.link_dokumen_PKS != '' THEN 1 ELSE 0 END) as total_pks
		FROM tbl_bki AS b
		LEFT JOIN tbl_mitra_bki AS m ON b.mitra_id = m.id")
			or die('Ada kesalahan pada query statistik: ' . mysqli_error($mysqli));

		$stats = mysqli_fetch_assoc($query_stats);
		?>

		<!-- Charts dan Statistics Section -->
		<div class="page-inner mt--5">
			<!-- Statistics Cards -->
			<div class="row">
				<div class="col-sm-6 col-md-3">
					<div class="card card-stats card-round">
						<div class="card-body">
							<div class="row align-items-center">
								<div class="col-icon">
									<div class="icon-big text-center icon-primary bubble-shadow-small">
										<i class="fas fa-globe"></i>
									</div>
								</div>
								<div class="col col-stats ml-3 ml-sm-0">
									<div class="numbers">
										<p class="card-category">Total Negara</p>
										<h4 class="card-title"><?php echo $stats['total_negara']; ?></h4>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-3">
					<div class="card card-stats card-round">
						<div class="card-body">
							<div class="row align-items-center">
								<div class="col-icon">
									<div class="icon-big text-center icon-info bubble-shadow-small">
										<i class="fas fa-handshake"></i>
									</div>
								</div>
								<div class="col col-stats ml-3 ml-sm-0">
									<div class="numbers">
										<p class="card-category">Total Mitra</p>
										<h4 class="card-title"><?php echo $stats['total_mitra']; ?></h4>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-3">
					<div class="card card-stats card-round">
						<div class="card-body">
							<div class="row align-items-center">
								<div class="col-icon">
									<div class="icon-big text-center icon-success bubble-shadow-small">
										<i class="fas fa-file-contract"></i>
									</div>
								</div>
								<div class="col col-stats ml-3 ml-sm-0">
									<div class="numbers">
										<p class="card-category">Total MoU</p>
										<h4 class="card-title"><?php echo $stats['total_mou']; ?></h4>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-3">
					<div class="card card-stats card-round">
						<div class="card-body">
							<div class="row align-items-center">
								<div class="col-icon">
									<div class="icon-big text-center icon-warning bubble-shadow-small">
										<i class="fas fa-file-signature"></i>
									</div>
								</div>
								<div class="col col-stats ml-3 ml-sm-0">
									<div class="numbers">
										<p class="card-category">Total PKS</p>
										<h4 class="card-title"><?php echo $stats['total_pks']; ?></h4>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Charts Row -->
			<div class="row">
				<!-- Bar Chart - Dokumen per Negara -->
				<div class="col-md-8">
					<div class="card">
						<div class="card-header">
							<div class="card-title">Distribusi Dokumen Kerjasama per Negara</div>
						</div>
						<div class="card-body">
							<div class="chart-container" style="min-height: 375px">
								<canvas id="chartNegara"></canvas>
							</div>
						</div>
					</div>
				</div>

				<!-- Doughnut Chart - Jenis Dokumen -->
				<div class="col-md-4">
					<div class="card">
						<div class="card-header">
							<div class="card-title">Distribusi Jenis Dokumen</div>
						</div>
						<div class="card-body">
							<div class="chart-container" style="min-height: 375px">
								<canvas id="chartJenis"></canvas>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Line Chart - MoU vs PKS per Negara -->
			<div class="row">
				<div class="col-md-12">
					<div class="card">
						<div class="card-header">
							<div class="card-title">Perbandingan MoU dan PKS per Negara</div>
						</div>
						<div class="card-body">
							<div class="chart-container" style="min-height: 400px">
								<canvas id="chartComparison"></canvas>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script>
			$(document).ready(function () {
				// Data dari PHP
				const labelsNegara = <?php echo json_encode($labels_negara); ?>;
				const valuesNegara = <?php echo json_encode($values_negara); ?>;
				const valuesMou = <?php echo json_encode($values_mou); ?>;
				const valuesPks = <?php echo json_encode($values_pks); ?>;
				const labelsJenis = <?php echo json_encode($labels_jenis); ?>;
				const valuesJenis = <?php echo json_encode($values_jenis); ?>;

				// Chart 1: Bar Chart - Dokumen per Negara
				const ctx1 = document.getElementById('chartNegara').getContext('2d');
				new Chart(ctx1, {
					type: 'bar',
					data: {
						labels: labelsNegara,
						datasets: [{
							label: 'Total Dokumen',
							data: valuesNegara,
							backgroundColor: 'rgba(54, 162, 235, 0.8)',
							borderColor: 'rgba(54, 162, 235, 1)',
							borderWidth: 1
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						scales: {
							y: {
								beginAtZero: true,
								ticks: {
									stepSize: 1
								}
							}
						},
						plugins: {
							legend: {
								display: true,
								position: 'top'
							},
							tooltip: {
								callbacks: {
									label: function (context) {
										return context.dataset.label + ': ' + context.parsed.y + ' dokumen';
									}
								}
							}
						}
					}
				});

				// Chart 2: Doughnut Chart - Jenis Dokumen
				const ctx2 = document.getElementById('chartJenis').getContext('2d');
				const colors = [
					'rgba(255, 99, 132, 0.8)',
					'rgba(54, 162, 235, 0.8)',
					'rgba(255, 205, 86, 0.8)',
					'rgba(75, 192, 192, 0.8)',
					'rgba(153, 102, 255, 0.8)',
					'rgba(255, 159, 64, 0.8)'
				];

				new Chart(ctx2, {
					type: 'doughnut',
					data: {
						labels: labelsJenis,
						datasets: [{
							data: valuesJenis,
							backgroundColor: colors.slice(0, labelsJenis.length),
							borderColor: colors.slice(0, labelsJenis.length).map(color => color.replace('0.8', '1')),
							borderWidth: 2
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins: {
							legend: {
								position: 'bottom'
							},
							tooltip: {
								callbacks: {
									label: function (context) {
										const label = context.label || '';
										const value = context.parsed;
										const total = context.dataset.data.reduce((a, b) => a + b, 0);
										const percentage = ((value / total) * 100).toFixed(1);
										return label + ': ' + value + ' (' + percentage + '%)';
									}
								}
							}
						}
					}
				});

				// Chart 3: Line Chart - MoU vs PKS per Negara
				const ctx3 = document.getElementById('chartComparison').getContext('2d');
				new Chart(ctx3, {
					type: 'line',
					data: {
						labels: labelsNegara,
						datasets: [{
							label: 'MoU',
							data: valuesMou,
							borderColor: 'rgba(75, 192, 192, 1)',
							backgroundColor: 'rgba(75, 192, 192, 0.2)',
							borderWidth: 3,
							fill: false,
							tension: 0.4
						}, {
							label: 'PKS',
							data: valuesPks,
							borderColor: 'rgba(255, 99, 132, 1)',
							backgroundColor: 'rgba(255, 99, 132, 0.2)',
							borderWidth: 3,
							fill: false,
							tension: 0.4
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						scales: {
							y: {
								beginAtZero: true,
								ticks: {
									stepSize: 1
								}
							}
						},
						plugins: {
							legend: {
								display: true,
								position: 'top'
							},
							tooltip: {
								mode: 'index',
								intersect: false,
								callbacks: {
									label: function (context) {
										return context.dataset.label + ': ' + context.parsed.y + ' dokumen';
									}
								}
							}
						},
						interaction: {
							mode: 'nearest',
							axis: 'x',
							intersect: false
						}
					}
				});
			});
		</script>

		<!-- Tabel Realisasi -->
		<div class="page-inner mt--5">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div class="card-title">Program Realisasi BKI</div>

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
						<a href="?module=form_entri_dokumen_bki" class="btn btn-success btn-round ml-2">
							<span class="btn-label"><i class="fas fa-edit mr-2"></i></span> Entri Dokumen
						</a>
					</div>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<!-- tabel untuk menampilkan data dari database -->
						<table id="basic-datatables" class="display table table-bordered table-striped table-hover">
							<thead>
								<tr>
									<th class="text-center">No.</th>
									<th class="text-center">Jenis Dokumen</th>
									<th class="text-center">No. Dokumen</th>
									<th class="text-center">Tentang</th>
									<th class="text-center">Tanggal Kerjasama/Penandatanganan</th>
									<th class="text-center">Mitra</th>
									<th class="text-center">PIC</th>
									<th class="text-center">Negara</th>
									<th class="text-center">Dokumen MoU</th>
									<th class="text-center">Dokumen PKS</th>
									<th class="text-center">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php
								// variabel untuk nomor urut tabel
								$no = 1;
								// sql statement untuk menampilkan data dari tabel "tbl_bki" dan "tbl_jenis_dokumen_bki" dan "tbl_mitra_bki"
								$query = mysqli_query($mysqli, "SELECT 
                                                                    b.id,
                                                                    b.no_dokumen,
                                                                    b.tentang,
                                                                    b.tanggal_penandatanganan,
                                                                    b.jangka_waktu_hari,
                                                                    b.link_dokumen_MoU,
                                                                    b.link_dokumen_PKS,
                                                                    m.nama_mitra,
                                                                    m.negara,
                                                                    jd.kode_singkat AS jenis_dokumen,
                                                                    pb.kode_bagian AS pic,
                                                                    b.mitra_id,
                                                                    b.jenis_dokumen_id,
                                                                    b.pic_bagian_id
                                                                FROM 
                                                                    tbl_bki AS b
                                                                LEFT JOIN 
                                                                    tbl_mitra_bki AS m ON b.mitra_id = m.id
                                                                LEFT JOIN 
                                                                    tbl_jenis_dokumen_bkI AS jd ON b.jenis_dokumen_id = jd.id
                                                                LEFT JOIN 
                                                                    tbl_pic_bagian AS pb ON b.pic_bagian_id = pb.id
                                                                ORDER BY 
                                                                    b.id DESC")
									or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
								// ambil data hasil query
								while ($data = mysqli_fetch_assoc($query)) {
									?>
									<!-- tampilkan data -->
									<tr>
										<td width="30" class="text-center"><?php echo $no++; ?></td>
										<td width="80"><?php echo $data['jenis_dokumen']; ?></td>
										<td width="100" class="text-center"><?php echo $data['no_dokumen']; ?></td>
										<td width="120" class="text-center"><?php echo $data['tentang']; ?></td>
										<td width="80" class="text-center"><?php echo $data['tanggal_penandatanganan']; ?></td>
										<td width="80" class="text-center"><?php echo $data['nama_mitra']; ?></td>
										<td width="80" class="text-center"><?php echo $data['pic']; ?></td>
										<td width="80" class="text-center"><?php echo $data['negara']; ?></td>
										<td class="text-center">
											<?php if (!empty($data['link_dokumen_MoU'])): ?>
												<a href="<?php echo htmlspecialchars($data['link_dokumen_MoU']); ?>" target="_blank"
													rel="noopener noreferrer" class="btn btn-info btn-sm" title="Buka Dokumen">
													<i class="fas fa-link"></i>
												</a>
											<?php else: ?>
												-
											<?php endif; ?>
										</td>
										<td class="text-center">
											<?php if (!empty($data['link_dokumen_PKS'])): ?>
												<a href="<?php echo htmlspecialchars($data['link_dokumen_PKS']); ?>" target="_blank"
													rel="noopener noreferrer" class="btn btn-info btn-sm" title="Buka Dokumen">
													<i class="fas fa-link"></i>
												</a>
											<?php else: ?>
												-
											<?php endif; ?>
										</td>
										<td width="10" class="text-center">
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
																			<option value="<?php echo $data['mitra_id']; ?>"
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
																		<label>Jenis Dokumen <span
																				class="text-danger">*</span></label>
																		<select name="jenis_dokumen_id" class="form-control"
																			required>
																			<option value="<?php echo $data['jenis_dokumen_id']; ?>"
																				selected>
																				<?php echo htmlspecialchars($data['jenis_dokumen']); ?>
																			</option>

																			<option disabled>-- Pilih Jenis Dokumen Lain --</option>
																			<?php
																			// Query ini mengambil semua opsi lain untuk dropdown
																			$query_jenis_modal = mysqli_query($mysqli, "SELECT id, kode_singkat, nama_dokumen FROM tbl_jenis_dokumen_bki ORDER BY nama_dokumen ASC");
																			while ($data_jenis_modal = mysqli_fetch_assoc($query_jenis_modal)) {
																				// Logika untuk tidak menampilkan duplikat
																				if ($data_jenis_modal['id'] != $data['jenis_dokumen_id']) {
																					echo "<option value='{$data_jenis_modal['id']}'>{$data_jenis_modal['nama_dokumen']} ({$data_jenis_modal['kode_singkat']})</option>";
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
																			<option value="<?php echo $data['pic_bagian_id']; ?>"
																				selected>
																				<?php echo htmlspecialchars($data['pic']); ?>
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
																		<label>Jangka Waktu (Hari) <span
																				class="text-danger">*</span></label>
																		<input type="number" name="jangka_waktu_hari"
																			class="form-control"
																			value="<?php echo $data['jangka_waktu_hari']; ?>"
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

																	<div class="form-group">
																		<label>Link Dokumen PKS<span
																				class="text-danger">*</span></label>
																		<input type="urls" name="link_dokumen_PKS"
																			class="form-control" placeholder="Masukkan link..."
																			value="<?php echo $data['link_dokumen_PKS']; ?>"
																			required>
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
													data-placement="top" title="Hapus">
													<i class="fas fa-trash fa-sm"></i>
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
																<strong><?php echo htmlspecialchars($data['jenis_dokumen']); ?></strong>
																dengan nomor
																<strong><?php echo htmlspecialchars($data['no_dokumen']); ?></strong>?
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
										<td width="30" class="text-center"><?php echo $no++; ?></td>
										<td width="80"><?php echo $data['rencana_mitra_internasional']; ?></td>
										<td width="100" class="text-center"><?php echo $data['negara']; ?></td>
										<td width="120" class="text-center"><?php echo $data['tentang']; ?></td>
										<td width="80" class="text-center"><?php echo $data['bulan_target_realisasi']; ?></td>
										<td width="10" class="text-center">
											<div>
												<!-- Button Hapus -->
												<a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal"
													data-target="#modalHapus<?php echo $data['id']; ?>" data-tooltip="tooltip"
													data-placement="top" title="Hapus">
													<i class="fas fa-trash fa-sm"></i>
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
																<strong><?php echo htmlspecialchars($data['jenis_dokumen']); ?></strong>
																dengan nomor
																<strong><?php echo htmlspecialchars($data['no_dokumen']); ?></strong>?
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