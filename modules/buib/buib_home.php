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
	if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan'])) { 
		
		// Query untuk mengambil semua data yang diperlukan dari tbl_rk_buib
		$main_query = mysqli_query($mysqli, "SELECT a.*, b.nama_program 
										  FROM tbl_rk_buib as a 
										  INNER JOIN tbl_program_buib as b ON a.program_buib=b.id_program 
										  ORDER BY a.tgl_surat DESC")
										  or die('Error pada query data RK BUIB: ' . mysqli_error($mysqli));
		
		// Data untuk charts dan summary
		$yearly_data = [];
		$monthly_data = [];
		$program_data = [];
		$keterangan_data = [];
		$total_target_calc = 0;
		$total_realisasi_calc = 0;
		$total_doc_calc = 0;
		$table_data = [];
		$realized_data = [];
		$target_data = [];
		
		// Reset pointer query
		mysqli_data_seek($main_query, 0);
		
		while ($data = mysqli_fetch_assoc($main_query)) {
			$tahun = date('Y', strtotime($data['tgl_surat']));
			$bulan = date('Y-m', strtotime($data['tgl_surat']));
			$bulan_nama = date('M Y', strtotime($data['tgl_surat']));
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
			
			// Kumpulkan data untuk chart bulanan (hanya realisasi)
			if (!isset($monthly_data[$bulan])) {
				$monthly_data[$bulan] = ['realisasi' => 0, 'label' => $bulan_nama];
			}
			$monthly_data[$bulan]['realisasi'] += $data['realisasi_nominal'];
			
			// Kumpulkan data untuk chart per program
			if (!isset($program_data[$data['nama_program']])) {
				$program_data[$data['nama_program']] = ['target' => 0, 'realisasi' => 0];
			}
			$program_data[$data['nama_program']]['target'] += $data['target_nominal'];
			$program_data[$data['nama_program']]['realisasi'] += $data['realisasi_nominal'];
			
			// Kumpulkan data berdasarkan keterangan_program
			if (!empty($data['keterangan_program'])) {
				if (!isset($keterangan_data[$data['keterangan_program']])) {
					$keterangan_data[$data['keterangan_program']] = ['realisasi' => 0];
				}
				$keterangan_data[$data['keterangan_program']]['realisasi'] += $data['realisasi_nominal'];
			}
			
			// Pisahkan data untuk tabel berdasarkan status realisasi
			if ($data['realisasi_nominal'] > 0) {
				$realized_data[] = $data;
			}
			if ($data['target_nominal'] > $data['realisasi_nominal']) {
				$target_data[] = $data;
			}
			
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
						<h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Rencana Kerja Badan Usaha dan Inkubasi Bisnis</h4>
						<!-- breadcrumbs -->
						<ul class="breadcrumbs">
							<li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
							<li class="separator"><i class="flaticon-right-arrow"></i></li>
							<li class="nav-item"><a href="?module=beranda">Beranda</a></li>
							<li class="separator"><i class="flaticon-right-arrow"></i></li>
							<li class="nav-item"><a>Data RK BUIB</a></li>
						</ul>
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
                                    $tahun_query = mysqli_query($mysqli, "SELECT DISTINCT YEAR(tgl_surat) as tahun FROM tbl_rk_buib ORDER BY tahun DESC")
                                                                 or die('Error pada query tahun: ' . mysqli_error($mysqli));
                                    while($tahun_data = mysqli_fetch_assoc($tahun_query)) {
                                        echo "<option value='".$tahun_data['tahun']."'>".$tahun_data['tahun']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterProgram" class="form-label">Filter Program:</label>
                                <select class="form-control" id="filterProgram" onchange="updateCharts()">
                                    <option value="all">Semua Program</option>
                                    <?php
                                    // Query untuk mendapatkan daftar program
                                    $program_query = mysqli_query($mysqli, "SELECT * FROM tbl_program_buib ORDER BY nama_program")
                                                                 or die('Error pada query program: ' . mysqli_error($mysqli));
                                    while($program_data_option = mysqli_fetch_assoc($program_query)) {
                                        echo "<option value='".$program_data_option['id_program']."'>".$program_data_option['nama_program']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterPeriode" class="form-label">Tampilan Periode:</label>
                                <select class="form-control" id="filterPeriode" onchange="toggleChartPeriode()">
                                    <option value="yearly">Per Tahun</option>
                                    <option value="monthly">Per Bulan</option>
                                </select>
                            </div>
                            <div class="col-md-3 text-right">
                                <button class="btn btn-primary" onclick="refreshData()">
                                    <i class="fas fa-sync-alt mr-2"></i>Refresh Data
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
                                    <p class="card-category text-muted mb-1">Total Dokumen RK</p>
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
            <!-- Line Chart - Realisasi Kumulatif per Bulan -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>Realisasi Kumulatif per Bulan
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

        <!-- Charts Row 2 -->
        <div class="row mb-4">
            <!-- Bar Chart - Target vs Realisasi per Program -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Target vs Realisasi per Deputy BUIB
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="barChart" style="width: 100%; height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Bar Chart - Realisasi per Keterangan Program -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Realisasi per Keterangan Program
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="keteranganChart" style="width: 100%; height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section --->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-table mr-2"></i>Detail Data Realisasi Jan-April 2025
                </div>
                <div class="ml-md-auto py-2 py-md-0">
                    <!-- button entri data -->
                    <a href="?module=form_entri_realisasi_buib" class="btn btn-success btn-round">
                        <span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Entri Data Realisasi
                    </a>
				</div>
            </div>
        <!-- Tampil Data Realisasi -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="realizedDataTable" class="display table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Deputy BUIB</th>
                                <th class="text-center">Kegiatan</th>
                                <th class="text-center">Realisasi</th>
                                <th class="text-center">Bulan</th>
                                <!-- <th class="text-center">Status</th> -->
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no1 = 1;
                            foreach ($realized_data as $data) {
                                $persentase_item = $data['target_nominal'] > 0 ? round(($data['realisasi_nominal'] / $data['target_nominal']) * 100, 2) : 0;
                                $status = $persentase_item >= 100 ? 'Tercapai' : ($persentase_item >= 80 ? 'Hampir Tercapai' : 'Belum Tercapai');
                                $status_class = $persentase_item >= 100 ? 'success' : ($persentase_item >= 80 ? 'warning' : 'danger');
                                $deputy_class = '';
                                if (strpos($data['nama_program'], 'Deputy Inkubasi Bisnis') !== false) {
                                    $deputy_class = 'primary'; // Biru
                                } elseif (strpos($data['nama_program'], 'Deputy Transfer Teknologi') !== false) {
                                    $deputy_class = 'success'; // Hijau
                                } elseif (strpos($data['nama_program'], 'Deputy Usaha dan Pemberdayaan Aset') !== false) {
                                    $deputy_class = 'warning'; // Orange/Kuning
                                }
                                ?>
                                <tr>
                                    <td width="30" class="text-center"><?php echo $no1++; ?></td>
                                    <td><span class="badge badge-<?php echo $deputy_class; ?>"><?php echo htmlspecialchars($data['nama_program']); ?></span></td>
                                    <td><?php echo htmlspecialchars($data['keterangan_program']); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($data['realisasi_nominal'], 0, ',', '.'); ?></td>
                                    <td width="100" class="text-center"><?php echo date('M-Y', strtotime($data['tgl_surat'])); ?></td>
                                    <!--<td width="120" class="text-center">
                                        <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status; ?></span>
                                    </td> -->
                                    <td width="80" class="text-center">
                                        <a href="?module=form_ubah_rk_buib&id=<?php echo $data['id']; ?>" class="btn btn-icon btn-round btn-success btn-sm mr-1" data-tooltip="tooltip" title="Ubah">
                                            <i class="fas fa-pencil-alt fa-sm"></i>
                                        </a>
                                        <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusRealized<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Hapus">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </a>
                                        
                                        <!-- Modal Hapus -->
                                        <div class="modal fade" id="modalHapusRealized<?php echo $data['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="fas fa-trash mr-2"></i>Hapus Data RK BUIB</h5>
                                                    </div>
                                                    <div class="modal-body text-left">Anda yakin ingin menghapus data RK BUIB <strong><?php echo $data['nama_program']; ?></strong> Tanggal <strong><?php echo date('d/m/Y', strtotime($data['tgl_surat'])); ?></strong>?</div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default btn-round" data-dismiss="modal">Batal</button>
                                                        <a href="modules/rk_buib/proses_hapus.php?id=<?php echo $data['id']; ?>" class="btn btn-danger btn-round">Ya, Hapus</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td class="text-center" colspan="3">
                                    <strong>TOTAL REALISASI</strong>
                                </td>
                                <td class="text-right" style="color: #28a745; font-size: 1.1em;">
                                    <strong>Rp <?php echo number_format($total_realisasi_calc, 0, ',', '.'); ?></strong>
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Tables Section --->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-table mr-2"></i>Rencana Kegiatan 2025
                </div>
                <div class="ml-md-auto py-2 py-md-0">
                    <!-- button entri data -->
                    <a href="?module=form_entri_rk_buib" class="btn btn-success btn-round">
                        <span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Entri Data RK
                    </a>
				</div>
            </div>
        <!-- Tampil Data Table Rencana Kegiatan 2025 --> 
            <div class="card-body">
                <div class="table-responsive">
                    <table id="targetDataTable" class="display table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Deputy BUIB</th>
                                <th class="text-center">Kegiatan</th>
                                <th class="text-center">Target</th>
                                <!-- <th class="text-center">Sisa Target</th> -->
                                <!-- <th class="text-center">Tanggal</th> -->
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no2 = 1;
                            foreach ($target_data as $data) {
                                $sisa_target = $data['target_nominal'] - $data['realisasi_nominal'];
                                $persentase_item = $data['target_nominal'] > 0 ? round(($data['realisasi_nominal'] / $data['target_nominal']) * 100, 2) : 0;
                                $status = $persentase_item >= 100 ? 'Tercapai' : ($persentase_item >= 80 ? 'Hampir Tercapai' : 'Belum Tercapai');
                                $status_class = $persentase_item >= 100 ? 'success' : ($persentase_item >= 80 ? 'warning' : 'danger');
                                // Tentukan deputy class
                                $deputy_class = '';
                                if (strpos($data['nama_program'], 'Deputy Inkubasi Bisnis') !== false) {
                                    $deputy_class = 'primary'; // Biru
                                } elseif (strpos($data['nama_program'], 'Deputy Transfer Teknologi') !== false) {
                                    $deputy_class = 'success'; // Hijau
                                } elseif (strpos($data['nama_program'], 'Deputy Usaha dan Pemberdayaan Aset') !== false) {
                                    $deputy_class = 'warning'; // Orange/Kuning
                                }
                                ?>
                                <tr>
                                    <td width="30" class="text-center"><?php echo $no2++; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $deputy_class; ?>"><?php echo htmlspecialchars($data['nama_program']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($data['keterangan_program']); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($data['target_nominal'], 0, ',', '.'); ?></td>
                                    <!-- <td class="text-right">Rp <?php echo number_format($sisa_target, 0, ',', '.'); ?></td> 
                                    <td width="100" class="text-center"><?php echo date('d/m/Y', strtotime($data['tgl_surat'])); ?></td>-->
                                    <td width="120" class="text-center">
                                        <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status; ?></span>
                                    </td>
                                    <td width="80" class="text-center">
                                        <a href="?module=form_ubah_rk_buib&id=<?php echo $data['id']; ?>" class="btn btn-icon btn-round btn-success btn-sm mr-1" data-tooltip="tooltip" title="Ubah">
                                            <i class="fas fa-pencil-alt fa-sm"></i>
                                        </a>
                                        <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusTarget<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Hapus">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </a>
                                        
                                        <!-- Modal Hapus -->
                                        <div class="modal fade" id="modalHapusTarget<?php echo $data['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="fas fa-trash mr-2"></i>Hapus Data RK BUIB</h5>
                                                    </div>
                                                    <div class="modal-body text-left">Anda yakin ingin menghapus data RK BUIB <strong><?php echo $data['nama_program']; ?></strong> Tanggal <strong><?php echo date('d/m/Y', strtotime($data['tgl_surat'])); ?></strong>?</div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default btn-round" data-dismiss="modal">Batal</button>
                                                        <a href="modules/rk_buib/proses_hapus.php?id=<?php echo $data['id']; ?>" class="btn btn-danger btn-round">Ya, Hapus</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td class="text-center" colspan="2">
                                    <strong>TOTAL RENCANA</strong>
                                </td>
                                <td class="text-right" style="color: #28a745; font-size: 1.1em;">
                                    <strong>Rp <?php echo number_format($total_target_calc, 0, ',', '.'); ?></strong>
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        

    <!-- Chart.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <script>
        // Data untuk charts dari PHP
        const yearlyData = <?php echo json_encode($yearly_data); ?>;
        const monthlyData = <?php echo json_encode($monthly_data); ?>;
        const programData = <?php echo json_encode($program_data); ?>;
        const keteranganData = <?php echo json_encode($keterangan_data); ?>;

        // Variabel untuk menyimpan instance chart
        let lineChart, doughnutChart, barChart, keteranganChart;

        // Function untuk inisialisasi semua chart
        function initializeCharts() {
            // Destroy existing charts jika ada
            if (lineChart) lineChart.destroy();
            if (doughnutChart) doughnutChart.destroy();
            if (barChart) barChart.destroy();
            if (keteranganChart) keteranganChart.destroy();

            // Siapkan data untuk line chart (hanya realisasi per bulan)
            const months = Object.keys(monthlyData).sort();
            const lineChartLabels = months.map(month => monthlyData[month].label);
            const lineChartData = months.map(month => monthlyData[month].realisasi);

            // Line Chart - Realisasi Kumulatif per Bulan
            const lineCtx = document.getElementById('lineChart').getContext('2d');
            lineChart = new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: lineChartLabels,
                    datasets: [{
                        label: 'Realisasi',
                        data: lineChartData,
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

            // Bar Chart - Target vs Realisasi per Program
            const programLabels = Object.keys(programData);
            const programTargetData = programLabels.map(program => programData[program].target);
            const programRealisasiData = programLabels.map(program => programData[program].realisasi);

            const barCtx = document.getElementById('barChart').getContext('2d');
            barChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: programLabels,
                    datasets: [{
                        label: 'Target',
                        data: programTargetData,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: '#FF6384',
                        borderWidth: 1
                    }, {
                        label: 'Realisasi',
                        data: programRealisasiData,
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

            // Initialize keterangan chart
            initializeKeteranganChart();
        }

        // Function untuk inisialisasi chart realisasi per keterangan program
        function initializeKeteranganChart() {
            // Pastikan ada element canvas untuk keterangan chart
            const keteranganCanvas = document.getElementById('keteranganChart');
            if (!keteranganCanvas) {
                console.warn('Canvas element untuk keteranganChart tidak ditemukan');
                return;
            }

            // Filter data keterangan yang memiliki realisasi > 0
            const filteredKeteranganData = {};
            Object.keys(keteranganData).forEach(keterangan => {
                const realisasi = keteranganData[keterangan].realisasi || 0;
                if (realisasi > 0) {
                    filteredKeteranganData[keterangan] = keteranganData[keterangan];
                }
            });

            // Siapkan data untuk keterangan chart (hanya yang realisasi > 0)
            const keteranganLabels = Object.keys(filteredKeteranganData);
            const keteranganRealisasiData = keteranganLabels.map(keterangan => filteredKeteranganData[keterangan].realisasi);
            
            // Generate warna untuk setiap keterangan
            const colors = generateColors(keteranganLabels.length);

            const keteranganCtx = keteranganCanvas.getContext('2d');
            keteranganChart = new Chart(keteranganCtx, {
                type: 'pie',
                data: {
                    labels: keteranganLabels,
                    datasets: [{
                        label: 'Realisasi per Keterangan',
                        data: keteranganRealisasiData,
                        backgroundColor: colors.background,
                        borderColor: colors.border,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map((label, i) => {
                                            const value = data.datasets[0].data[i];
                                            const total = data.datasets[0].data.reduce((sum, val) => sum + val, 0);
                                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            
                                            return {
                                                text: `${label} (${percentage}%)`,
                                                fillStyle: data.datasets[0].backgroundColor[i],
                                                strokeStyle: data.datasets[0].borderColor[i],
                                                lineWidth: data.datasets[0].borderWidth,
                                                hidden: isNaN(data.datasets[0].data[i]) || chart.getDatasetMeta(0).data[i].hidden,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((sum, val) => sum + val, 0);
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                    return context.label + ': Rp ' + context.parsed.toLocaleString('id-ID') + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Function untuk generate warna chart
        function generateColors(count) {
            const baseColors = [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384',
                '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
            ];
            
            const backgroundColors = [];
            const borderColors = [];
            
            for (let i = 0; i < count; i++) {
                const baseColor = baseColors[i % baseColors.length];
                backgroundColors.push(baseColor + '80'); // Add transparency
                borderColors.push(baseColor);
            }
            
            return {
                background: backgroundColors,
                border: borderColors
            };
        }

        // Function untuk toggle chart keterangan antara pie dan horizontal bar
        function toggleKeteranganChartType() {
            const currentType = keteranganChart.config.type;
            const newType = currentType === 'pie' ? 'horizontalBar' : 'pie';
            
            // Destroy chart yang ada
            if (keteranganChart) {
                keteranganChart.destroy();
            }
            
            // Siapkan data (filter yang realisasi > 0)
            const filteredKeteranganData = {};
            Object.keys(keteranganData).forEach(keterangan => {
                const realisasi = keteranganData[keterangan].realisasi || 0;
                if (realisasi > 0) {
                    filteredKeteranganData[keterangan] = keteranganData[keterangan];
                }
            });
            
            const keteranganLabels = Object.keys(filteredKeteranganData);
            const keteranganRealisasiData = keteranganLabels.map(keterangan => filteredKeteranganData[keterangan].realisasi);
            const colors = generateColors(keteranganLabels.length);
            
            const keteranganCtx = document.getElementById('keteranganChart').getContext('2d');
            
            if (newType === 'horizontalBar') {
                // Horizontal Bar Chart
                keteranganChart = new Chart(keteranganCtx, {
                    type: 'bar',
                    data: {
                        labels: keteranganLabels,
                        datasets: [{
                            label: 'Realisasi',
                            data: keteranganRealisasiData,
                            backgroundColor: colors.background,
                            borderColor: colors.border,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': Rp ' + context.parsed.x.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            },
                            y: {
                                ticks: {
                                    maxRotation: 0,
                                    minRotation: 0
                                }
                            }
                        }
                    }
                });
            } else {
                // Pie Chart (default)
                initializeKeteranganChart();
            }
            
            // Update button text
            const toggleBtn = document.getElementById('toggleKeteranganChart');
            if (toggleBtn) {
                toggleBtn.textContent = newType === 'pie' ? 'Switch to Bar' : 'Switch to Pie';
            }
        }

        // Function untuk filter keterangan chart berdasarkan program
        function filterKeteranganByProgram(programName) {
            if (!keteranganData) return;
            
            let filteredData = {};
            
            if (programName === 'all') {
                // Filter semua data yang realisasi > 0
                Object.keys(keteranganData).forEach(keterangan => {
                    const realisasi = keteranganData[keterangan].realisasi || 0;
                    if (realisasi > 0) {
                        filteredData[keterangan] = keteranganData[keterangan];
                    }
                });
            } else {
                // Filter berdasarkan program tertentu dan realisasi > 0
                Object.keys(keteranganData).forEach(keterangan => {
                    const realisasi = keteranganData[keterangan].realisasi || 0;
                    if (keteranganData[keterangan].program === programName && realisasi > 0) {
                        filteredData[keterangan] = keteranganData[keterangan];
                    }
                });
            }
            
            // Update chart dengan data yang sudah difilter
            updateKeteranganChart(filteredData);
        }

        // Function untuk update keterangan chart dengan data baru
        function updateKeteranganChart(newData) {
            if (!keteranganChart) return;
            
            // Filter data yang realisasi > 0
            const filteredData = {};
            Object.keys(newData).forEach(key => {
                const realisasi = newData[key].realisasi || 0;
                if (realisasi > 0) {
                    filteredData[key] = newData[key];
                }
            });
            
            const labels = Object.keys(filteredData);
            const data = labels.map(label => filteredData[label].realisasi);
            const colors = generateColors(labels.length);
            
            keteranganChart.data.labels = labels;
            keteranganChart.data.datasets[0].data = data;
            keteranganChart.data.datasets[0].backgroundColor = colors.background;
            keteranganChart.data.datasets[0].borderColor = colors.border;
            
            keteranganChart.update();
        }

        // Function untuk export keterangan chart data ke CSV
        function exportKeteranganData() {
            if (!keteranganData) {
                alert('Tidak ada data keterangan untuk diekspor');
                return;
            }
            
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Keterangan,Realisasi,Program\n";
            
            Object.keys(keteranganData).forEach(keterangan => {
                const row = [
                    keterangan,
                    keteranganData[keterangan].realisasi || 0,
                    keteranganData[keterangan].program || ''
                ].join(',');
                csvContent += row + "\n";
            });
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "realisasi_keterangan_program.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Function untuk toggle periode chart
        function toggleChartPeriode() {
            const filterPeriode = document.getElementById('filterPeriode').value;
            const chartTitle = document.getElementById('chartPeriodeTitle');
            
            currentPeriode = filterPeriode;
            
            if (filterPeriode === 'monthly') {
                chartTitle.textContent = 'Target vs Realisasi Kumulatif per Bulan';
            } else {
                chartTitle.textContent = 'Target vs Realisasi Kumulatif per Tahun';
            }
            
            initializeCharts();
        }

        // Utility functions
        function updateCharts() {
            // Implementasi filter charts berdasarkan tahun dan program
            console.log('Updating charts...');
            initializeCharts();
        }

        function refreshData() {
            location.reload();
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
            if ($.fn.DataTable.isDataTable('#rkBuibDataTable')) {
                $('#rkBuibDataTable').DataTable().destroy();
            }
            
            $('#rkBuibDataTable').DataTable({
                "pageLength": 25,
                "order": [[ 5, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 8] }
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
                    window.history.replaceState({}, document.title, window.location.pathname + '?module=buib');
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
                    window.history.replaceState({}, document.title, window.location.pathname + '?module=buib');
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
                    window.history.replaceState({}, document.title, window.location.pathname + '?module=buib');
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