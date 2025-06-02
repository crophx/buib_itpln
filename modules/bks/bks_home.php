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
	// jika hak akses = Administrator atau hak akses = Bendahara, tampilkan konten
	if ($_SESSION['hak_akses'] == 'SuperAdmin' || $_SESSION['hak_akses'] == 'BKS') { 
		
		// Query untuk mengambil semua data yang diperlukan
		$main_query = mysqli_query($mysqli, "SELECT a.*, b.nama_jenis 
										  FROM tbl_bks as a 
										  INNER JOIN tbl_jenis as b ON a.jenis_dokumen=b.id_jenis 
										  ORDER BY a.tgl_upload DESC")
										  or die('Error pada query data BKS: ' . mysqli_error($mysqli));
		
		// Data untuk charts dan summary
		$yearly_data = [];
		$jenis_data = [];
		$total_target_calc = 0;
		$total_realisasi_calc = 0;
		$total_doc_calc = 0;
		$table_data = [];
		
		// Reset pointer query
		mysqli_data_seek($main_query, 0);
		
		while ($data = mysqli_fetch_assoc($main_query)) {
			$tahun = date('Y', strtotime($data['tgl_upload']));
			$persentase_item = $data['target_nominal'] > 0 ? round(($data['realisasi_nominal'] / $data['target_nominal']) * 100, 2) : 0;
			
			// Kumpulkan data untuk summary cards
			$total_target_calc += $data['target_nominal'];
			$total_realisasi_calc += $data['realisasi_nominal'];
			$total_doc_calc++;
			
			// Kumpulkan data untuk chart tahunan
			if (!isset($yearly_data[$tahun])) {
				$yearly_data[$tahun] = ['target' => 0, 'realisasi' => 0];
			}
			$yearly_data[$tahun]['target'] += $data['target_nominal'];
			$yearly_data[$tahun]['realisasi'] += $data['realisasi_nominal'];
			
			// Kumpulkan data untuk chart per jenis
			if (!isset($jenis_data[$data['nama_jenis']])) {
				$jenis_data[$data['nama_jenis']] = ['target' => 0, 'realisasi' => 0];
			}
			$jenis_data[$data['nama_jenis']]['target'] += $data['target_nominal'];
			$jenis_data[$data['nama_jenis']]['realisasi'] += $data['realisasi_nominal'];
			
			// Simpan data untuk tabel
			$table_data[] = $data;
		}
		
		$persentase_total = $total_target_calc > 0 ? round(($total_realisasi_calc / $total_target_calc) * 100, 2) : 0;
		?>
		<div class="panel-header">
			<div class="page-inner py-45">
				<div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
					<div class="page-header">
						<!-- judul halaman -->
						<h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Badan Usaha dan Inkubasi Bisnis</h4>
						<!-- breadcrumbs -->
						<ul class="breadcrumbs">
							<li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
							<li class="separator"><i class="flaticon-right-arrow"></i></li>
							<li class="nav-item"><a href="?module=beranda">Beranda</a></li>
							<li class="separator"><i class="flaticon-right-arrow"></i></li>
							<li class="nav-item"><a>Data</a></li>
						</ul>
					</div>
					<div class="ml-md-auto py-2 py-md-0">
						<!-- button entri data -->
						<a href="?module=form_entri_bks" class="btn btn-success btn-round">
							<span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Entri Data
						</a>
					</div>
				</div>
			</div>
		</div>

        <div class="page-inner mt--5">
        <!-- Filter Section -->
        <div class="row mb-0">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <label for="filterTahun" class="form-label">Filter Tahun:</label>
                                <select class="form-control" id="filterTahun" onchange="updateCharts()">
                                    <option value="all">Semua Tahun</option>
                                    <?php
                                    // Query untuk mendapatkan daftar tahun
                                    $tahun_query = mysqli_query($mysqli, "SELECT DISTINCT YEAR(tgl_upload) as tahun FROM tbl_bks ORDER BY tahun DESC")
                                                                 or die('Error pada query tahun: ' . mysqli_error($mysqli));
                                    while($tahun_data = mysqli_fetch_assoc($tahun_query)) {
                                        echo "<option value='".$tahun_data['tahun']."'>".$tahun_data['tahun']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterJenis" class="form-label">Filter Jenis:</label>
                                <select class="form-control" id="filterJenis" onchange="updateCharts()">
                                    <option value="all">Semua Jenis</option>
                                    <?php
                                    // Query untuk mendapatkan daftar jenis dokumen
                                    $jenis_query = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis")
                                                                 or die('Error pada query jenis: ' . mysqli_error($mysqli));
                                    while($jenis_data_option = mysqli_fetch_assoc($jenis_query)) {
                                        echo "<option value='".$jenis_data_option['id_jenis']."'>".$jenis_data_option['nama_jenis']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 text-right">
                                <button class="btn btn-primary" onclick="refreshData()">
                                    <i class="fas fa-sync-alt mr-2"></i>Refresh Data
                                </button>
                                <button class="btn btn-success" onclick="exportData()">
                                    <i class="fas fa-download mr-2"></i>Export Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-0">
            <div class="col-md-3 mb-3">
                <div class="card card-stats card-round">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big icon-success bubble-shadow-small">
                                    <i class="fas fa-bullseye"></i>
                                </div> 
                            </div>
                            <div class="col">
                                <div class="numbers">
                                    <p class="card-category mb-1">Total Target</p>
                                    <h4 class="card-title mb-0">Rp <?php echo number_format($total_target_calc, 0, ',', '.'); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card card-stats card-round shadow-sm">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big bubble-shadow-small icon-primary">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                            <div class="col">
                                <div class="numbers">
                                    <p class="card-category text-muted mb-1">Total Realisasi</p>
                                    <h4 class="card-title mb-0">Rp <?php echo number_format($total_realisasi_calc, 0, ',', '.'); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card card-stats card-round shadow-sm">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big bubble-shadow-small icon-warning">
                                    <i class="fas fa-percentage"></i>
                                </div>
                            </div>
                            <div class="col">
                                <div class="numbers">
                                    <p class="card-category text-muted mb-1">Persentase Capaian</p>
                                    <h4 class="card-title mb-0"><?php echo $persentase_total; ?>%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card card-stats card-round shadow-sm">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big bubble-shadow-small icon-secondary">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                            <div class="col">
                                <div class="numbers">
                                    <p class="card-category text-muted mb-1">Total Dokumen</p>
                                    <h4 class="card-title mb-0"><?php echo number_format($total_doc_calc, 0, ',', '.'); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <!-- Line Chart - Target vs Realisasi per Tahun -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>Target vs Realisasi Kumulatif per Tahun
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="lineChart" style="width: 100%; height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Doughnut Chart - Persentase Capaian -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>Capaian vs Target
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="doughnutChart" style="width: 100%; height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bar Chart - Target vs Realisasi per Jenis Dokumen -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Target vs Realisasi per Jenis Dokumen
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="barChart" style="width: 100%; height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-table mr-2"></i>Detail Data BKS
                </div>
                <div class="card-tools">
                    <button class="btn btn-sm btn-info" onclick="toggleDetailView()">
                        <i class="fas fa-expand-alt" id="detailToggleIcon"></i>
                        <span id="detailToggleText">Detail View</span>
                    </button>
                </div>
            </div>

            <!-- Tampil Data TABLE BKS-->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="bksDataTable" class="display table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Mitra</th>
                                <th class="text-center">No. Dokumen</th>
                                <th class="text-center">Jenis Dokumen</th>
                                <th class="text-center">Target Nominal</th>
                                <th class="text-center">Realisasi Nominal</th>
                                <th class="text-center">Persentase</th>
                                <th class="text-center">Tanggal Upload</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Detail Dokumen</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($table_data as $data) {
                                $persentase_item = $data['target_nominal'] > 0 ? round(($data['realisasi_nominal'] / $data['target_nominal']) * 100, 2) : 0;
                                $status = $persentase_item >= 100 ? 'Tercapai' : ($persentase_item >= 80 ? 'Hampir Tercapai' : 'Belum Tercapai');
                                $status_class = $persentase_item >= 100 ? 'success' : ($persentase_item >= 80 ? 'warning' : 'danger');
                                ?>
                                <tr>
                                    <td width="30" class="text-center"><?php echo $no++; ?></td>
                                    <td width="100"><?php echo htmlspecialchars($data['mitra']); ?></td>
                                    <td width="100"><?php echo htmlspecialchars($data['no_dokumen']); ?></td>
                                    <td width="100"><?php echo htmlspecialchars($data['nama_jenis']); ?></td>
                                    <td width="80" class="text-right">Rp <?php echo number_format($data['target_nominal'], 0, ',', '.'); ?></td>
                                    <td width="80" class="text-right">Rp <?php echo number_format($data['realisasi_nominal'], 0, ',', '.'); ?></td>
                                    <td width="80" class="text-center">
                                        <span class="badge badge-<?php echo $status_class; ?>"><?php echo $persentase_item; ?>%</span>
                                    </td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($data['tgl_upload'])); ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="?module=tampil_detail_bks&id=<?php echo $data['id_bks']; ?>" class="btn btn-icon btn-round btn-warning btn-sm mr-md-1" data-tooltip="tooltip" data-placement="top" title="Tampilkan Dokumen">
                                            <i class="far fa-file-alt"></i>
                                        </a>
                                    </td>
                                    <td width="80" class="text-center">
                                        <div>
                                            <!-- button ubah data -->
                                            <a href="?module=form_ubah_bks&id=<?php echo $data['id_bks']; ?>" class="btn btn-icon btn-round btn-success btn-sm mr-md-1" data-tooltip="tooltip" data-placement="top" title="Ubah">
                                                <i class="fas fa-pencil-alt fa-sm"></i>
                                            </a>
                                            <!-- button hapus data -->
                                            <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapus<?php echo $data['id_bks']; ?>" data-tooltip="tooltip" data-placement="top" title="Hapus">
                                                <i class="fas fa-trash fa-sm"></i>
                                            </a>
                                            <!-- Modal Hapus -->
                                            <div class="modal fade" id="modalHapus<?php echo $data['id_bks']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-trash mr-2"></i>Hapus Data Arsip Dokumen</h5>
                                                        </div>
                                                        <div class="modal-body text-left">Anda yakin ingin menghapus data arsip dokumen <strong><?php echo $data['nama_jenis']; ?></strong> Waktu <strong><?php echo $data['tgl_upload']; ?></strong>?</div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default btn-round" data-dismiss="modal">Batal</button>
                                                            <a href="modules/bks/proses_hapus.php?id=<?php echo $data['id_bks']; ?>" class="btn btn-danger btn-round">Ya, Hapus</a>
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

    <!-- Chart.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <script>
        // Data untuk charts dari PHP
        const yearlyData = <?php echo json_encode($yearly_data); ?>;
        const jenisData = <?php echo json_encode($jenis_data); ?>;

        // Prepare data untuk line chart (yearly)
        const years = Object.keys(yearlyData).sort();
        const targetData = years.map(year => yearlyData[year].target);
        const realisasiData = years.map(year => yearlyData[year].realisasi);

        // Prepare data untuk bar chart (per jenis)
        const jenisLabels = Object.keys(jenisData);
        const jenisTargetData = jenisLabels.map(jenis => jenisData[jenis].target);
        const jenisRealisasiData = jenisLabels.map(jenis => jenisData[jenis].realisasi);

        // Variabel untuk menyimpan instance chart
        let lineChart, doughnutChart, barChart;

        // Function untuk inisialisasi semua chart
        function initializeCharts() {
            // Destroy existing charts jika ada
            if (lineChart) lineChart.destroy();
            if (doughnutChart) doughnutChart.destroy();
            if (barChart) barChart.destroy();

            // Line Chart - Target vs Realisasi per Tahun
            const lineCtx = document.getElementById('lineChart').getContext('2d');
            lineChart = new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: years,
                    datasets: [{
                        label: 'Target',
                        data: targetData,
                        borderColor: '#FF6384',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Realisasi',
                        data: realisasiData,
                        borderColor: '#36A2EB',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            // Doughnut Chart - Capaian vs Target
            const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
            const totalTarget = <?php echo $total_target_calc; ?>;
            const totalRealisasi = <?php echo $total_realisasi_calc; ?>;
            const sisaTarget = totalTarget - totalRealisasi;

            doughnutChart = new Chart(doughnutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Tercapai', 'Belum Tercapai'],
                    datasets: [{
                        data: [totalRealisasi, sisaTarget > 0 ? sisaTarget : 0],
                        backgroundColor: ['#4BC0C0', '#FFE0E0'],
                        borderColor: ['#36A2EB', '#FF6384'],
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
                                label: function(context) {
                                    const percentage = ((context.parsed / totalTarget) * 100).toFixed(1);
                                    return context.label + ': Rp ' + context.parsed.toLocaleString('id-ID') + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // Bar Chart - Target vs Realisasi per Jenis
            const barCtx = document.getElementById('barChart').getContext('2d');
            barChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: jenisLabels,
                    datasets: [{
                        label: 'Target',
                        data: jenisTargetData,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: '#FF6384',
                        borderWidth: 1
                    }, {
                        label: 'Realisasi',
                        data: jenisRealisasiData,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: '#36A2EB',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {  
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }
                    }
                }
            });
        }

        // Utility functions
        function updateCharts() {
            // Implementasi filter charts berdasarkan tahun dan jenis
            console.log('Updating charts...');
            // Tambahkan logika filter sesuai kebutuhan
        }

        function refreshData() {
            location.reload();
        }

        function exportData() {
            // Implementasi export data
            console.log('Exporting data...');
        }

        function toggleDetailView() {
            const icon = document.getElementById('detailToggleIcon');
            const text = document.getElementById('detailToggleText');
            
            if (icon.classList.contains('fa-expand-alt')) {
                icon.classList.remove('fa-expand-alt');
                icon.classList.add('fa-compress-alt');
                text.textContent = 'Simple View';
            } else {
                icon.classList.remove('fa-compress-alt');
                icon.classList.add('fa-expand-alt');
                text.textContent = 'Detail View';
            }
        }

        // Initialize semua saat DOM ready
        $(document).ready(function() {
            // Initialize DataTable dengan destroy option untuk refresh
            if ($.fn.DataTable.isDataTable('#bksDataTable')) {
                $('#bksDataTable').DataTable().destroy();
            }
            
            $('#bksDataTable').DataTable({
                "pageLength": 25,
                "order": [[ 7, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 10] }
                ]
            });

            // Initialize charts
            initializeCharts();
        });
    </script>

    <script type="text/javascript">
			$(document).ready(function() {
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
						message: 'Data berhasil disimpan.'
					}, {
						type: 'success'
					});
					
					// Refresh halaman setelah notifikasi untuk memperbarui chart dan tabel
					setTimeout(function() {
						// Hapus parameter dari URL dan refresh
						window.history.replaceState({}, document.title, window.location.pathname + '?module=bks');
						location.reload();
					}, 2000);
				}
				// jika pesan = 2
				else if (pesan === '2') {
					// tampilkan pesan sukses ubah data
					$.notify({
						title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
						message: 'Data dokumen berhasil diubah.'
					}, {
						type: 'success'
					});
					
					// Refresh halaman setelah notifikasi
					setTimeout(function() {
						window.history.replaceState({}, document.title, window.location.pathname + '?module=bks');
						location.reload();
					}, 2000);
				}
				// jika pesan = 3
				else if (pesan === '3') {
					// tampilkan pesan sukses hapus data
					$.notify({
						title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
						message: 'Data dokumen berhasil dihapus.'
					}, {
						type: 'success'
					});
					
					// Refresh halaman setelah notifikasi
					setTimeout(function() {
						window.history.replaceState({}, document.title, window.location.pathname + '?module=bks');
						location.reload();
					}, 2000);
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

<?php } 
}
?>