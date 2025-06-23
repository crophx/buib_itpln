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
	if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'TrainingCenter', 'Pimpinan', 'SekretarisPimpinan'])) { 
		
		// Query untuk mengambil semua data dari tbl_rk_training_center (tanpa join)
		$main_query = mysqli_query($mysqli, "SELECT a.*, b.nama_kategori, c.nama_status
                                    FROM tbl_rk_training_center as a
                                    INNER JOIN tbl_kategori as b ON a.kategori_tc=b.id_kategori
                                    INNER JOIN tbl_status as c ON a.status_tc=c.id_status
                                    ORDER BY tgl_surat ASC")
                                    or die('Error pada query data RK training_center: '. mysqli_error($mysqli));

// Data untuk charts dan summary
$yearly_data = [];
$monthly_data = [];
$program_data = [];
$keterangan_data = [];
$kategori_data = [];
$total_target_calc = 0;
$total_realisasi_calc = 0;
$total_kontrak_calc = 0;
$total_ongoing_calc = 0;
$total_doc_calc = 0;
$table_data = [];
$Realisasi_data = [];
$target_data = [];
$kontrak_data = [];
$ongoing_data = [];
$monthly_accumulative = [];

// Ambil total target tahunan terlebih dahulu
$yearly_target = 0;
while ($data = mysqli_fetch_assoc($main_query)) {
    $yearly_target += $data['target_nominal'];
}

// Hitung target bulanan (total target tahunan dibagi 12)
$monthly_target = $yearly_target / 12;

// Reset pointer query
mysqli_data_seek($main_query, 0);

// Buat array untuk menyimpan data per bulan
$months_data = [];
        while ($data = mysqli_fetch_assoc($main_query)) {
            $tahun = date('Y', strtotime($data['tgl_surat']));
            $bulan = date('Y-m', strtotime($data['tgl_surat']));
            $bulan_nama = date('M Y', strtotime($data['tgl_surat']));
            $persentase_item = $data['target_nominal'] > 0 ? round(($data['realisasi_nominal'] / $data['target_nominal']) * 100, 2) : 0;
            
            // Kumpulkan data untuk summary cards
            $total_target_calc += $data['target_nominal'];
            $total_realisasi_calc += $data['realisasi_nominal'];
            $total_kontrak_calc += $data['kontrak_nominal'];
            $total_ongoing_calc += $data['ongoing_nominal'];
            $total_doc_calc++;
            
            // Kumpulkan data bulanan untuk chart
            if (!isset($months_data[$bulan])) {
                $months_data[$bulan] = [
                    'label' => $bulan_nama,
                    'realisasi' => 0,
                    'kontrak' => 0,
                    'ongoing' => 0
                ];
            }
            
            $months_data[$bulan]['realisasi'] += $data['realisasi_nominal'];
            $months_data[$bulan]['kontrak'] += $data['kontrak_nominal'];
            $months_data[$bulan]['ongoing'] += $data['ongoing_nominal'];
            
            // Data untuk chart lainnya (program, yearly, dll)
            if (!isset($yearly_data[$tahun])) {
                $yearly_data[$tahun] = ['target' => 0, 'realisasi' => 0];
            }
            $yearly_data[$tahun]['target'] += $data['target_nominal'];
            $yearly_data[$tahun]['realisasi'] += $data['realisasi_nominal'];
            
            if (!isset($program_data[$data['nama_program']])) {
                $program_data[$data['nama_program']] = ['target' => 0, 'realisasi' => 0];
            }
            $program_data[$data['nama_program']]['target'] += $data['target_nominal'];
            $program_data[$data['nama_program']]['realisasi'] += $data['realisasi_nominal'];
            
            if (!empty($data['keterangan_program'])) {
                if (!isset($keterangan_data[$data['keterangan_program']])) {
                    $keterangan_data[$data['keterangan_program']] = ['realisasi' => 0];
                }
                $keterangan_data[$data['keterangan_program']]['realisasi'] += $data['realisasi_nominal'];
            }
            
            // Data untuk kategori chart berdasarkan realisasi
            $kategori_nama = $data['nama_kategori'];
            if (!isset($kategori_data[$kategori_nama])) {
                $kategori_data[$kategori_nama] = [
                    'count' => 0,
                    'realisasi' => 0,
                    'target' => 0
                ];
            }
            $kategori_data[$kategori_nama]['count']++;
            $kategori_data[$kategori_nama]['realisasi'] += $data['realisasi_nominal'];
            $kategori_data[$kategori_nama]['target'] += $data['target_nominal'];
            
            // Simpan semua data
            $table_data[] = $data;
            
            // Pisahkan data berdasarkan kondisi
            if ($data['realisasi_nominal'] > 0) {
                $Realisasi_data[] = $data;
            }
            if ($data['target_nominal'] > 0) {
                $target_data[] = $data;
            }
            if ($data['kontrak_nominal'] > 0) {
                $kontrak_data[] = $data;
            }
            if ($data['ongoing_nominal'] > 0) {
                $ongoing_data[] = $data;
            }
        }

        // Reset pointer query untuk penggunaan selanjutnya
        mysqli_data_seek($main_query, 0);

        // Reset pointer query lagi untuk penggunaan selanjutnya
        mysqli_data_seek($main_query, 0);

        // Urutkan data bulanan dan hitung akumulatif
        ksort($months_data);
        $cumulative_target = 0;
        $cumulative_realisasi = 0;
        $cumulative_terkontrak = 0;  // realisasi + kontrak
        $cumulative_ongoing = 0;     // realisasi + kontrak + ongoing

        foreach ($months_data as $month => $data) {
            $cumulative_target += $monthly_target;
            $cumulative_realisasi += $data['realisasi'];
            $cumulative_terkontrak += ($data['realisasi'] + $data['kontrak']);
            $cumulative_ongoing += ($data['realisasi'] + $data['kontrak'] + $data['ongoing']);
            
            $monthly_accumulative[$month] = [
                'label' => $data['label'],
                'target' => $cumulative_target,
                'realisasi' => $cumulative_realisasi,
                'terkontrak' => $cumulative_terkontrak,
                'ongoing' => $cumulative_ongoing
            ];
        }

        $persentase_total = $total_target_calc > 0 ? round(($total_realisasi_calc / $total_target_calc) * 100, 2) : 0;
        ?>
		<div class="panel-header">
			<div class="page-inner py-45">
				<div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
					<div class="page-header">
						<!-- judul halaman -->
						<h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Training Center</h4>
						<!-- breadcrumbs -->
						<ul class="breadcrumbs">
							<li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
							<li class="separator"><i class="flaticon-right-arrow"></i></li>
							<li class="nav-item"><a href="?module=beranda">Beranda</a></li>
							<li class="separator"><i class="flaticon-right-arrow"></i></li>
							<li class="nav-item"><a>Data RK training_center</a></li>
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
                                    $tahun_query = mysqli_query($mysqli, "SELECT DISTINCT YEAR(tgl_surat) as tahun FROM tbl_rk_training_center ORDER BY tahun DESC")
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
                                    // Query untuk mendapatkan daftar program unik dari tbl_rk_training_center
                                    $program_query = mysqli_query($mysqli, "SELECT DISTINCT nama_program FROM tbl_rk_training_center WHERE nama_program IS NOT NULL AND nama_program != '' ORDER BY nama_program")
                                                                 or die('Error pada query program: ' . mysqli_error($mysqli));
                                    while($program_data_option = mysqli_fetch_assoc($program_query)) {
                                        echo "<option value='".$program_data_option['nama_program']."'>".$program_data_option['nama_program']."</option>";
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
            <!-- Bar Chart - Target vs Realisasi per Bulan -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Target vs Realisasi Kumulatif Per Bulan
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="barChart" style="width: 100%; height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Pie Chart - Kategori Program -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Realisasi per Keterangan Program
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="kategoriChart" style="width: 100%; height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section Data Realisasi --->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-table mr-2"></i>Detail Data Realisasi
                </div>
                <div class="ml-md-auto py-2 py-md-0">
                    <!-- button entri data -->
                    <a href="?module=form_entri_realisasi_training_center" class="btn btn-success btn-round">
                        <span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Entri Data Realisasi
                    </a>
				</div>
            </div>
        <!-- Tampil Data Realisasi -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="RealisasiDataTable" class="display table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Nama Program</th>
                                <th class="text-center">Peserta</th>
                                <th class="text-center">Realisasi</th>
                                <th class="text-center">Bulan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no1 = 1;
                            foreach ($Realisasi_data as $data) {
                                $persentase_item = $data['target_nominal'] > 0 ? round(($data['realisasi_nominal'] / $data['target_nominal']) * 100, 2) : 0;
                                $status = $persentase_item >= 100 ? 'Tercapai' : ($persentase_item >= 80 ? 'Hampir Tercapai' : 'Belum Tercapai');
                                $status_class = $persentase_item >= 100 ? 'success' : ($persentase_item >= 80 ? 'warning' : 'danger');
                                
                                ?>
                                <tr>
                                    <td width="30" class="text-center"><?php echo $no1++; ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_program']); ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_kategori']); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($data['realisasi_nominal'], 0, ',', '.'); ?></td>
                                    <td width="100" class="text-center"><?php echo date('M-Y', strtotime($data['tgl_surat'])); ?></td>
                                    <td width="80" class="text-center">
                                        <!-- Button Edit Realisasi -->
                                        <a href="#" class="btn btn-icon btn-round btn-success btn-sm" data-toggle="modal" data-target="#modalUbahRealisasi<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Ubah">
                                            <i class="fas fa-pencil-alt fa-sm"></i>
                                        </a>
                                        <!-- Form Modal Edit Realisasi -->
                                        <div class="modal fade" id="modalUbahRealisasi<?php echo $data['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="modalRealisasiLabel<?php echo $data['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <!-- Modal Header -->
                                                    <div class="modal-header btn-success text-white">
                                                        <h5 class="modal-title font-weight-bold" id="modalRealisasiLabel<?php echo $data['id']; ?>">
                                                            <i class="fas fa-edit mr-2"></i>Edit Data Realisasi
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>

                                                    <form action="modules/training_center/proses_ubah.php" method="POST" id="formUbahRealisasi<?php echo $data['id']; ?>">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                                            
                                                            <!-- Row 1: Edit Realisasi Program -->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="nama_program<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-graduation-cap mr-1 text-primary"></i>Nama Program <span class="text-danger">*</span>
                                                                        </label>
                                                                        <input type="text" autocomplete="off"
                                                                            class="form-control" 
                                                                            id="nama_program<?php echo $data['id']; ?>" 
                                                                            name="nama_program" 
                                                                            value="<?php echo htmlspecialchars($data['nama_program']); ?>" 
                                                                            placeholder="Masukkan nama program"
                                                                            required>                       
                                                                    </div>
                                                                </div>
                                                                <!-- Row 1: Edit Realisasi Kategori -->
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="kategori_tc<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-tags mr-1 text-success"></i>Kategori TC <span class="text-danger">*</span>
                                                                        </label>
                                                                        <select class="form-control" 
                                                                                id="kategori_tc<?php echo $data['id']; ?>" 
                                                                                name="kategori_tc" 
                                                                                required>
                                                                            <option value="">-- Pilih Kategori --</option>
                                                                            <?php
                                                                            $kategori_query = mysqli_query($mysqli, "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");
                                                                            while ($kategori = mysqli_fetch_array($kategori_query)) {
                                                                                $selected = ($kategori['id_kategori'] == $data['kategori_tc']) ? 'selected' : '';
                                                                                echo "<option value='".$kategori['id_kategori']."' ".$selected.">".$kategori['nama_kategori']."</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Row 2: Nominal Realisasi & Tanggal -->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="realisasi_nominal<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-money-bill-wave mr-1 text-warning"></i>Nominal Realisasi <span class="text-danger">*</span>
                                                                        </label>
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text bg-warning text-white">Rp</span>
                                                                            </div>
                                                                            <input type="text" autocomplete="off"
                                                                                class="form-control currency" 
                                                                                id="realisasi_nominal<?php echo $data['id']; ?>" 
                                                                                name="realisasi_nominal" 
                                                                                value="<?php echo number_format($data['realisasi_nominal'], 0, ',', '.'); ?>" 
                                                                                placeholder="0"
                                                                                required>
                                                                        </div>
                                                                        <small class="form-text text-muted">
                                                                            <i class="fas fa-info-circle mr-1"></i>Ubah nominal realisasi
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="tgl_surat<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-calendar-alt mr-1 text-info"></i>Tanggal Surat <span class="text-danger">*</span>
                                                                        </label>
                                                                        <input type="date" 
                                                                            class="form-control" 
                                                                            id="tgl_surat<?php echo $data['id']; ?>" 
                                                                            name="tgl_surat" 
                                                                            value="<?php echo date('Y-m-d', strtotime($data['tgl_surat'])); ?>" 
                                                                            required>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Row 3: Status & Nominal Realisasi -->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="status_tc<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-info-circle mr-1 text-success"></i>Status <span class="text-danger">*</span>
                                                                        </label>
                                                                        <select class="form-control" 
                                                                                id="status_tc<?php echo $data['id']; ?>" 
                                                                                name="status_tc" 
                                                                                required>
                                                                            <option value="">-- Pilih Status --</option>
                                                                            <?php
                                                                            $status_query = mysqli_query($mysqli, "SELECT * FROM tbl_status ORDER BY nama_status ASC");
                                                                            while ($status = mysqli_fetch_array($status_query)) {
                                                                                $selected = ($status['id_status'] == $data['status_tc']) ? 'selected' : '';
                                                                                echo "<option value='".$status['id_status']."' ".$selected.">".$status['nama_status']."</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                        <small class="form-text text-muted">
                                                                            <i class="fas fa-check-circle mr-1"></i>Update jika terjadi perubahan status
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            
                                                            </div>

                                                            <!-- Row 4: Keterangan -->
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="keterangan_program<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-sticky-note mr-1 text-secondary"></i>Keterangan
                                                                        </label>
                                                                        <textarea class="form-control" 
                                                                                id="keterangan_program<?php echo $data['id']; ?>" 
                                                                                name="keterangan_program" 
                                                                                rows="3" 
                                                                                placeholder="Masukkan keterangan tambahan (opsional)"><?php echo isset($data['keterangan_program']) ? htmlspecialchars($data['keterangan_program']) : ''; ?></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Data Sebelumnya -->
                                                            <div class="alert alert-light border mt-3">
                                                                <h6 class="mb-2">
                                                                    <i class="fas fa-history mr-2 text-info"></i>Data Sebelumnya
                                                                </h6>
                                                                <div class="row small">
                                                                    <div class="col-md-4">
                                                                        <strong>Program:</strong><br>
                                                                        <?php echo htmlspecialchars($data['nama_program']); ?><br><br>
                                                                        <strong>Kategori:</strong><br>
                                                                        <span class="badge badge-primary"><?php echo htmlspecialchars($data['nama_kategori']); ?></span>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <strong>Status:</strong><br>
                                                                        <span class="badge badge-success"><?php echo htmlspecialchars($data['nama_status']); ?></span><br><br>
                                                                        <strong>Nominal:</strong><br>
                                                                        <span class="text-warning font-weight-bold">Rp <?php echo number_format($data['realisasi_nominal'], 0, ',', '.'); ?></span>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <strong>Tanggal:</strong><br>
                                                                        <?php echo date('d/m/Y', strtotime($data['tgl_surat'])); ?><br><br>
                                                                        <strong>Periode:</strong><br>
                                                                        <span class="badge badge-info"><?php echo date('M-Y', strtotime($data['tgl_surat'])); ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Modal Footer button simpan edit realisasi-->
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                <i class="fas fa-times mr-1"></i>Batal
                                                            </button>
                                                            <button type="submit" class="btn btn-success" name="ubahRealisasi">
                                                                <i class="fas fa-save mr-1"></i>Simpan Perubahan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Button Hapus Realisasi-->
                                        <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusRealisasi<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Hapus">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </a>
                                        
                                        <!-- Modal Hapus -->
                                        <div class="modal fade" id="modalHapusRealisasi<?php echo $data['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="fas fa-trash mr-2"></i>Hapus Data RK training_center</h5>
                                                    </div>
                                                    <div class="modal-body text-left">Anda yakin ingin menghapus data RK training_center <strong><?php echo $data['nama_program']; ?></strong> Tanggal <strong><?php echo date('d/m/Y', strtotime($data['tgl_surat'])); ?></strong>?</div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default btn-round" data-dismiss="modal">Batal</button>
                                                        <a href="modules/training_center/proses_hapus.php?id=<?php echo $data['id']; ?>" class="btn btn-danger btn-round">Ya, Hapus</a>
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

        <!-- Tables Section Data TERKONTRAK --->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-table mr-2"></i>Detail Data Terkontrak
                </div>
                <div class="ml-md-auto py-2 py-md-0">
                    <!-- button entri data -->
                    <a href="?module=form_entri_terkontrak_training_center" class="btn btn-success btn-round">
                        <span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Entri Data Terkontrak
                    </a>
				</div>
            </div>
            <!-- Tampil Data Kontrak -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="kontrakDataTable" class="display table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Nama Program</th>
                                <th class="text-center">Peserta</th>
                                <th class="text-center">Bulan</th>
                                <th class="text-center">Keterangan</th>
                                <th class="text-center">Nominal Kontrak</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no1 = 1;
                            foreach ($kontrak_data as $data) {
                                $persentase_item = $data['target_nominal'] > 0 ? round(($data['realisasi_nominal'] / $data['target_nominal']) * 100, 2) : 0;
                                $status = $persentase_item >= 100 ? 'Tercapai' : ($persentase_item >= 80 ? 'Hampir Tercapai' : 'Belum Tercapai');
                                $status_class = $persentase_item >= 100 ? 'success' : ($persentase_item >= 80 ? 'warning' : 'danger');
                                
                                ?>
                                <tr>
                                    <td width="30" class="text-center"><?php echo $no1++; ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_program']); ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_kategori']); ?></td>
                                    <td width="100" class="text-center"><?php echo date('M-Y', strtotime($data['tgl_surat'])); ?></td>
                                    <td><?php echo htmlspecialchars($data['keterangan_program']); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($data['kontrak_nominal'], 0, ',', '.'); ?></td>
                                    <td width="80" class="text-center">
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($data['nama_status']); ?></span><br>
                                    </td>
                                    <td width="80" class="text-center">
                                        <a href="#" class="btn btn-icon btn-round btn-success btn-sm mr-1" data-toggle="modal" data-target="#modalUbahKontrak<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Ubah">
                                            <i class="fas fa-pencil-alt fa-sm"></i>
                                        </a>
                                        <!-- Modal Edit Kontrak -->
                                        <div class="modal fade" id="modalUbahKontrak<?php echo $data['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="modalUbahKontrakLabel<?php echo $data['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <!-- Modal Header -->
                                                    <div class="modal-header btn-success text-white">
                                                        <h5 class="modal-title font-weight-bold" id="modalUbahKontrakLabel<?php echo $data['id']; ?>">
                                                            <i class="fas fa-edit mr-2"></i>Edit Data Kontrak
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>

                                                    <form action="modules/training_center/proses_ubah.php" method="POST" id="formUbahKontrak<?php echo $data['id']; ?>">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                                            
                                                            <!-- Row 1: Program & Kategori -->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="nama_program<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-graduation-cap mr-1 text-primary"></i>Nama Program <span class="text-danger">*</span>
                                                                        </label>
                                                                        <input type="text" autocomplete="off"
                                                                            class="form-control" 
                                                                            id="nama_program<?php echo $data['id']; ?>" 
                                                                            name="nama_program" 
                                                                            value="<?php echo htmlspecialchars($data['nama_program']); ?>" 
                                                                            placeholder="Masukkan nama program"
                                                                            required>                       
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="kategori_tc<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-tags mr-1 text-success"></i>Kategori TC <span class="text-danger">*</span>
                                                                        </label>
                                                                        <select class="form-control" 
                                                                                id="kategori_tc<?php echo $data['id']; ?>" 
                                                                                name="kategori_tc" 
                                                                                required>
                                                                            <option value="">-- Pilih Kategori --</option>
                                                                            <?php
                                                                            $kategori_query = mysqli_query($mysqli, "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");
                                                                            while ($kategori = mysqli_fetch_array($kategori_query)) {
                                                                                $selected = ($kategori['id_kategori'] == $data['kategori_tc']) ? 'selected' : '';
                                                                                echo "<option value='".$kategori['id_kategori']."' ".$selected.">".$kategori['nama_kategori']."</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Row 2: Nominal Kontrak & Tanggal -->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="kontrak_nominal<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-money-bill-wave mr-1 text-warning"></i>Nominal Kontrak <span class="text-danger">*</span>
                                                                        </label>
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text bg-warning text-white">Rp</span>
                                                                            </div>
                                                                            <input type="text" autocomplete="off"
                                                                                class="form-control currency" 
                                                                                id="kontrak_nominal<?php echo $data['id']; ?>" 
                                                                                name="kontrak_nominal" 
                                                                                value="<?php echo number_format($data['kontrak_nominal'], 0, ',', '.'); ?>" 
                                                                                placeholder="0"
                                                                                required>
                                                                        </div>
                                                                        <small class="form-text text-muted">
                                                                            <i class="fas fa-info-circle mr-1"></i>Jika sudah terealisasi, isi dengan 0
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="tgl_surat<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-calendar-alt mr-1 text-info"></i>Tanggal Surat <span class="text-danger">*</span>
                                                                        </label>
                                                                        <input type="date" 
                                                                            class="form-control" 
                                                                            id="tgl_surat<?php echo $data['id']; ?>" 
                                                                            name="tgl_surat" 
                                                                            value="<?php echo date('Y-m-d', strtotime($data['tgl_surat'])); ?>" 
                                                                            required>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Row 3: Status & Realisasi pada Edit Kontrak -->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="status_tc<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-info-circle mr-1 text-success"></i>Status <span class="text-danger">*</span>
                                                                        </label>
                                                                        <select class="form-control" 
                                                                                id="status_tc<?php echo $data['id']; ?>" 
                                                                                name="status_tc" 
                                                                                required>
                                                                            <option value="">-- Pilih Status --</option>
                                                                            <?php
                                                                            $status_query = mysqli_query($mysqli, "SELECT * FROM tbl_status ORDER BY nama_status ASC");
                                                                            while ($status = mysqli_fetch_array($status_query)) {
                                                                                $selected = ($status['id_status'] == $data['status_tc']) ? 'selected' : '';
                                                                                echo "<option value='".$status['id_status']."' ".$selected.">".$status['nama_status']."</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                        <small class="form-text text-muted">
                                                                            <i class="fas fa-check-circle mr-1"></i>Update jika sudah terealisasi
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="realisasi_nominal<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-bullseye mr-1 text-primary"></i>Realisasi Nominal
                                                                        </label>
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text bg-primary text-white">Rp</span>
                                                                            </div>
                                                                            <input type="text" 
                                                                                class="form-control currency" 
                                                                                id="realisasi_nominal<?php echo $data['id']; ?>" 
                                                                                name="realisasi_nominal" 
                                                                                value="<?php echo isset($data['realisasi_nominal']) ? number_format($data['realisasi_nominal'], 0, ',', '.') : ''; ?>" 
                                                                                placeholder="0">
                                                                        </div>
                                                                        <small class="form-text text-muted">
                                                                            <i class="fas fa-lightbulb mr-1"></i>Nominal yang sudah diterima
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Row 4: Keterangan Edit Kontrak-->
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="keterangan_program<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-sticky-note mr-1 text-secondary"></i>Keterangan
                                                                        </label>
                                                                        <textarea class="form-control" 
                                                                                id="keterangan_program<?php echo $data['id']; ?>" 
                                                                                name="keterangan_program" 
                                                                                rows="3" 
                                                                                placeholder="Masukkan keterangan tambahan (opsional)"><?php echo isset($data['keterangan_program']) ? htmlspecialchars($data['keterangan_program']) : ''; ?></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Data Sebelumnya -->
                                                            <div class="alert alert-light border mt-3">
                                                                <h6 class="mb-2">
                                                                    <i class="fas fa-history mr-2 text-info"></i>Data Sebelumnya
                                                                </h6>
                                                                <div class="row small">
                                                                    <div class="col-md-4">
                                                                        <strong>Program:</strong><br>
                                                                        <?php echo htmlspecialchars($data['nama_program']); ?><br><br>
                                                                        <strong>Kategori:</strong><br>
                                                                        <span class="badge badge-primary"><?php echo htmlspecialchars($data['nama_kategori']); ?></span>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <strong>Status:</strong><br>
                                                                        <span class="badge badge-success"><?php echo htmlspecialchars($data['nama_status']); ?></span><br><br>
                                                                        <strong>Nominal:</strong><br>
                                                                        <span class="text-warning font-weight-bold">Rp <?php echo number_format($data['kontrak_nominal'], 0, ',', '.'); ?></span>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <strong>Tanggal:</strong><br>
                                                                        <?php echo date('d/m/Y', strtotime($data['tgl_surat'])); ?><br><br>
                                                                        <strong>Periode:</strong><br>
                                                                        <span class="badge badge-info"><?php echo date('M-Y', strtotime($data['tgl_surat'])); ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Modal Footer -->
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                <i class="fas fa-times mr-1"></i>Batal
                                                            </button>
                                                            <button type="submit" class="btn btn-success" name="ubahKontrak">
                                                                <i class="fas fa-save mr-1"></i>Simpan Perubahan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Modal Hapus Kontrak-->
                                        <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusKontrak<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Hapus">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </a>
                                        <!-- Modal Hapus Kontrak-->
                                        <div class="modal fade" id="modalHapusKontrak<?php echo $data['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="fas fa-trash mr-2"></i>Hapus Data Kontrak training_center</h5>
                                                    </div>
                                                    <div class="modal-body text-left">Anda yakin ingin menghapus data Kontrak training_center <strong><?php echo $data['nama_program']; ?></strong> Tanggal <strong><?php echo date('d/m/Y', strtotime($data['tgl_surat'])); ?></strong>?</div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default btn-round" data-dismiss="modal">Batal</button>
                                                        <a href="modules/training_center/proses_hapus.php?id=<?php echo $data['id']; ?>" class="btn btn-danger btn-round">Ya, Hapus</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <!-- FOOTER TOTAL TERKONTRAK -->
                        <tfoot>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td class="text-center" colspan="5">
                                    <strong>TOTAL KONTRAK</strong>
                                </td>
                                <td class="text-right" style="color: #28a745; font-size: 1.1em;">
                                    <strong>Rp <?php echo number_format($total_kontrak_calc, 0, ',', '.'); ?></strong>
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tables Section Data OnGoing --->
        
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-table mr-2"></i>Detail Data On-Going
                </div>
                <div class="ml-md-auto py-2 py-md-0">
                    <!-- button entri data -->
                    <a href="?module=form_entri_ongoing_training_center" class="btn btn-success btn-round">
                        <span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Entri Data On-Going
                    </a>
				</div>
            </div>
        <!-- Tampil Data OnGoing -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="OngoingDataTable" class="display table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Nama Program</th>
                                <th class="text-center">Keterangan</th>
                                <th class="text-center">Bulan</th>
                                <th class="text-center">Nominal On-Going</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no1 = 1;
                            foreach ($ongoing_data as $data) {
                                $persentase_item = $data['target_nominal'] > 0 ? round(($data['realisasi_nominal'] / $data['target_nominal']) * 100, 2) : 0;
                                $status = $persentase_item >= 100 ? 'Tercapai' : ($persentase_item >= 80 ? 'Hampir Tercapai' : 'Belum Tercapai');
                                $status_class = $persentase_item >= 100 ? 'success' : ($persentase_item >= 80 ? 'warning' : 'danger');
                                
                                ?>
                                <tr>
                                    <td width="30" class="text-center"><?php echo $no1++; ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_program']); ?></td>
                                    <td><?php echo htmlspecialchars($data['keterangan_program']); ?></td>
                                    <td width="100" class="text-center"><?php echo date('M-Y', strtotime($data['tgl_surat'])); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($data['ongoing_nominal'], 0, ',', '.'); ?></td>
                                    <td width="80" class="text-center">
                                        <span class="badge badge-danger"><?php echo htmlspecialchars($data['nama_status']); ?></span><br>
                                    </td>
                                    <td width="100" class="text-center">
                                        <!-- Button Edit Ongoing -->
                                        <a href="#" class="btn btn-icon btn-round btn-success btn-sm" data-toggle="modal" data-target="#modalUbahOngoing<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Ubah">
                                            <i class="fas fa-pencil-alt fa-sm"></i>
                                        </a>
                                        <!-- Form Modal Edit Ongoing -->
                                        <div class="modal fade" id="modalUbahOngoing<?php echo $data['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="modalUbahOngoingLabel<?php echo $data['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <!-- Modal Header -->
                                                    <div class="modal-header btn-success text-white">
                                                        <h5 class="modal-title font-weight-bold" id="modalUbahOngoingLabel<?php echo $data['id']; ?>">
                                                            <i class="fas fa-edit mr-2"></i>Edit Data On-Going
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>

                                                    <form action="modules/training_center/proses_ubah.php" method="POST" id="formUbahOngoing<?php echo $data['id']; ?>">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                                            
                                                            <!-- Row 1: Edit Ongoing Program & Kategori -->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="nama_program<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-graduation-cap mr-1 text-primary"></i>Nama Program <span class="text-danger">*</span>
                                                                        </label>
                                                                        <input type="text" autocomplete="off"
                                                                            class="form-control" 
                                                                            id="nama_program<?php echo $data['id']; ?>" 
                                                                            name="nama_program" 
                                                                            value="<?php echo htmlspecialchars($data['nama_program']); ?>" 
                                                                            placeholder="Masukkan nama program"
                                                                            required>                       
                                                                    </div>
                                                                </div>
                                                                <!-- Row 1: Edit Ongoing Kategori -->
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="kategori_tc<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-tags mr-1 text-success"></i>Kategori TC <span class="text-danger">*</span>
                                                                        </label>
                                                                        <select class="form-control" 
                                                                                id="kategori_tc<?php echo $data['id']; ?>" 
                                                                                name="kategori_tc" 
                                                                                required>
                                                                            <option value="">-- Pilih Kategori --</option>
                                                                            <?php
                                                                            $kategori_query = mysqli_query($mysqli, "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");
                                                                            while ($kategori = mysqli_fetch_array($kategori_query)) {
                                                                                $selected = ($kategori['id_kategori'] == $data['kategori_tc']) ? 'selected' : '';
                                                                                echo "<option value='".$kategori['id_kategori']."' ".$selected.">".$kategori['nama_kategori']."</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Row 2: Nominal Ongoing & Tanggal -->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="ongoing_nominal<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-money-bill-wave mr-1 text-warning"></i>Nominal Ongoing <span class="text-danger">*</span>
                                                                        </label>
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text bg-warning text-white">Rp</span>
                                                                            </div>
                                                                            <input type="text" autocomplete="off"
                                                                                class="form-control currency" 
                                                                                id="ongoing_nominal<?php echo $data['id']; ?>" 
                                                                                name="ongoing_nominal" 
                                                                                value="<?php echo number_format($data['ongoing_nominal'], 0, ',', '.'); ?>" 
                                                                                placeholder="0"
                                                                                required>
                                                                        </div>
                                                                        <small class="form-text text-muted">
                                                                            <i class="fas fa-info-circle mr-1"></i>Jika sudah terealisasi, isi dengan 0
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <!--Edit Ongoing Tanggal-->
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="tgl_surat<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-calendar-alt mr-1 text-info"></i>Tanggal Surat <span class="text-danger">*</span>
                                                                        </label>
                                                                        <input type="date" 
                                                                            class="form-control" 
                                                                            id="tgl_surat<?php echo $data['id']; ?>" 
                                                                            name="tgl_surat" 
                                                                            value="<?php echo date('Y-m-d', strtotime($data['tgl_surat'])); ?>" 
                                                                            required>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Row 3: Kontrak Pada Edit Ongoing-->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="kontrak_nominal<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-bullseye mr-1 text-primary"></i>Kontrak Nominal
                                                                        </label>
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text bg-primary text-white">Rp</span>
                                                                            </div>
                                                                            <input type="text" autocomplete="off"
                                                                                class="form-control currency" 
                                                                                id="kontrak_nominal<?php echo $data['id']; ?>" 
                                                                                name="kontrak_nominal" 
                                                                                value="<?php echo isset($data['kontrak_nominal']) ? number_format($data['kontrak_nominal'], 0, ',', '.') : ''; ?>" 
                                                                                placeholder="0">
                                                                        </div>
                                                                        <small class="form-text text-muted">
                                                                            <i class="fas fa-lightbulb mr-1"></i>Kontrak yang sudah disepakati
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <!-- Row 3: Realisasi Pada Edit Ongoing-->
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="realisasi_nominal<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-bullseye mr-1 text-primary"></i>Realisasi Nominal
                                                                        </label>
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text bg-primary text-white">Rp</span>
                                                                            </div>
                                                                            <input type="text" autocomplete="off"
                                                                                class="form-control currency" 
                                                                                id="realisasi_nominal<?php echo $data['id']; ?>" 
                                                                                name="realisasi_nominal" 
                                                                                value="<?php echo isset($data['realisasi_nominal']) ? number_format($data['realisasi_nominal'], 0, ',', '.') : ''; ?>" 
                                                                                placeholder="0">
                                                                        </div>
                                                                        <small class="form-text text-muted">
                                                                            <i class="fas fa-lightbulb mr-1"></i>Nominal yang sudah diterima
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Edit Status OnGoing -->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="status_tc<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-info-circle mr-1 text-success"></i>Status <span class="text-danger">*</span>
                                                                        </label>
                                                                        <select class="form-control" 
                                                                                id="status_tc<?php echo $data['id']; ?>" 
                                                                                name="status_tc" 
                                                                                required>
                                                                            <option value="">-- Pilih Status --</option>
                                                                            <?php
                                                                            $status_query = mysqli_query($mysqli, "SELECT * FROM tbl_status ORDER BY nama_status ASC");
                                                                            while ($status = mysqli_fetch_array($status_query)) {
                                                                                $selected = ($status['id_status'] == $data['status_tc']) ? 'selected' : '';
                                                                                echo "<option value='".$status['id_status']."' ".$selected.">".$status['nama_status']."</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                        <small class="form-text text-muted">
                                                                            <i class="fas fa-check-circle mr-1"></i>Update jika sudah terealisasi
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Row 4: Edit Keterangan  Ongoing -->
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="keterangan_program<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-sticky-note mr-1 text-secondary"></i>Keterangan
                                                                        </label>
                                                                        <textarea class="form-control" 
                                                                                id="keterangan_program<?php echo $data['id']; ?>" 
                                                                                name="keterangan_program" 
                                                                                rows="3" 
                                                                                placeholder="Masukkan keterangan tambahan (opsional)"><?php echo isset($data['keterangan_program']) ? htmlspecialchars($data['keterangan_program']) : ''; ?></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Data Ongoing Sebelumnya -->
                                                            <div class="alert alert-light border mt-3">
                                                                <h6 class="mb-2">
                                                                    <i class="fas fa-history mr-2 text-info"></i>Data Sebelumnya
                                                                </h6>
                                                                <div class="row small">
                                                                    <div class="col-md-4">
                                                                        <strong>Program:</strong><br>
                                                                        <?php echo htmlspecialchars($data['nama_program']); ?><br><br>
                                                                        <strong>Kategori:</strong><br>
                                                                        <span class="badge badge-primary"><?php echo htmlspecialchars($data['nama_kategori']); ?></span>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <strong>Status:</strong><br>
                                                                        <span class="badge badge-success"><?php echo htmlspecialchars($data['nama_status']); ?></span><br><br>
                                                                        <strong>Nominal:</strong><br>
                                                                        <span class="text-warning font-weight-bold">Rp <?php echo number_format($data['ongoing_nominal'], 0, ',', '.'); ?></span>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <strong>Tanggal:</strong><br>
                                                                        <?php echo date('d/m/Y', strtotime($data['tgl_surat'])); ?><br><br>
                                                                        <strong>Periode:</strong><br>
                                                                        <span class="badge badge-info"><?php echo date('M-Y', strtotime($data['tgl_surat'])); ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Modal Footer button simpan -->
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                <i class="fas fa-times mr-1"></i>Batal
                                                            </button>
                                                            <button type="submit" class="btn btn-success" name="ubahOngoing">
                                                                <i class="fas fa-save mr-1"></i>Simpan Perubahan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Modal Hapus ONgoing -->
                                        <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusOngoing<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Hapus">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </a>
                                        <div class="modal fade" id="modalHapusOngoing<?php echo $data['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="fas fa-trash mr-2"></i>Hapus Data OnGoing training_center</h5>
                                                    </div>
                                                    <div class="modal-body text-left">Anda yakin ingin menghapus data OnGoing training_center <strong><?php echo $data['nama_program']; ?></strong> Tanggal <strong><?php echo date('d/m/Y', strtotime($data['tgl_surat'])); ?></strong>?</div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default btn-round" data-dismiss="modal">Batal</button>
                                                        <a href="modules/training_center/proses_hapus.php?id=<?php echo $data['id']; ?>" class="btn btn-danger btn-round">Ya, Hapus</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <!-- FOOTER TOTAL ONGOING-->
                        <tfoot>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td class="text-center" colspan="4">
                                    <strong>TOTAL OnGoing</strong>
                                </td>
                                <td class="text-right" style="color: #28a745; font-size: 1.1em;">
                                    <strong>Rp <?php echo number_format($total_ongoing_calc, 0, ',', '.'); ?></strong>
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Tables Section RENCANA KEGIATAN --->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-table mr-2"></i>Rencana Kegiatan 2025
                </div>
                <div class="ml-md-auto py-2 py-md-0">
                    <!-- button entri data -->
                    <a href="?module=form_entri_rk_training_center" class="btn btn-success btn-round">
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
                                <th class="text-center">Nama Program</th>
                                <th class="text-center">Keterangan</th>
                                <th class="text-center">Target</th>
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
                                $status_persentase = $persentase_item >= 100 ? 'Tercapai' : ($persentase_item >= 80 ? 'Hampir Tercapai' : 'Belum Tercapai');
                                $status_persentase = $persentase_item >= 100 ? 'success' : ($persentase_item >= 80 ? 'warning' : 'danger');
                                
                                ?>
                                <tr>
                                    <td width="30" class="text-center"><?php echo $no2++; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $deputy_class; ?>"><?php echo htmlspecialchars($data['nama_program']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($data['keterangan_program']); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($data['target_nominal'], 0, ',', '.'); ?></td>
                                    <td width="120" class="text-center">
                                        <span class="badge badge-warning"><?php echo htmlspecialchars($data['nama_status']); ?></span><br>
                                    </td>
                                    <td width="80" class="text-center">
                                        <!-- Button Edit Rencana -->
                                        <a href="#" class="btn btn-icon btn-round btn-success btn-sm" data-toggle="modal" data-target="#modalUbahRencana<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Ubah">
                                            <i class="fas fa-pencil-alt fa-sm"></i>
                                        </a>
                                        <!-- Form Modal Edit Rencana -->
                                        <div class="modal fade" id="modalUbahRencana<?php echo $data['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="modalRencanaLabel<?php echo $data['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <!-- Modal Header -->
                                                    <div class="modal-header btn-success text-white">
                                                        <h5 class="modal-title font-weight-bold" id="modalRencanaLabel<?php echo $data['id']; ?>">
                                                            <i class="fas fa-edit mr-2"></i>Edit Data Rencana
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>

                                                    <form action="modules/training_center/proses_ubah.php" method="POST" id="formUbahRencana<?php echo $data['id']; ?>">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                                            
                                                            <!-- Edit Nama Program Rencana-->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="nama_program<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-graduation-cap mr-1 text-primary"></i>Nama Program <span class="text-danger">*</span>
                                                                        </label>
                                                                        <input type="text" autocomplete="off"
                                                                            class="form-control" 
                                                                            id="nama_program<?php echo $data['id']; ?>" 
                                                                            name="nama_program" 
                                                                            value="<?php echo htmlspecialchars($data['nama_program']); ?>" 
                                                                            placeholder="Masukkan nama program"
                                                                            required>                       
                                                                    </div>
                                                                </div>
                                                                <!-- Edit Nominal Rencana -->
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="target_nominal<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-money-bill-wave mr-1 text-warning"></i>Nominal Rencana <span class="text-danger">*</span>
                                                                        </label>
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text bg-warning text-white">Rp</span>
                                                                            </div>
                                                                            <input type="text" autocomplete="off"
                                                                                class="form-control currency" 
                                                                                id="target_nominal<?php echo $data['id']; ?>" 
                                                                                name="target_nominal" 
                                                                                value="<?php echo number_format($data['target_nominal'], 0, ',', '.'); ?>" 
                                                                                placeholder="0"
                                                                                required>
                                                                        </div>
                                                                        <small class="form-text text-muted">
                                                                            <i class="fas fa-info-circle mr-1"></i>Ubah nominal rencana
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!--Edit Tanggal Rencana -->
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="tgl_surat<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-calendar-alt mr-1 text-info"></i>Tanggal Surat <span class="text-danger">*</span>
                                                                        </label>
                                                                        <input type="date" 
                                                                            class="form-control" 
                                                                            id="tgl_surat<?php echo $data['id']; ?>" 
                                                                            name="tgl_surat" 
                                                                            value="<?php echo date('Y-m-d', strtotime($data['tgl_surat'])); ?>" 
                                                                            required>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Edit Keterangan Rencana -->
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="keterangan_program<?php echo $data['id']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-sticky-note mr-1 text-secondary"></i>Keterangan
                                                                        </label>
                                                                        <textarea class="form-control" 
                                                                                id="keterangan_program<?php echo $data['id']; ?>" 
                                                                                name="keterangan_program" 
                                                                                rows="3" 
                                                                                placeholder="Masukkan keterangan tambahan (opsional)"><?php echo isset($data['keterangan_program']) ? htmlspecialchars($data['keterangan_program']) : ''; ?></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Data Sebelumnya -->
                                                            <div class="alert alert-light border mt-3">
                                                                <h6 class="mb-2">
                                                                    <i class="fas fa-history mr-2 text-info"></i>Data Sebelumnya
                                                                </h6>
                                                                <div class="row small">
                                                                    <div class="col-md-4">
                                                                        <strong>Program:</strong><br>
                                                                        <?php echo htmlspecialchars($data['nama_program']); ?><br><br>
                                                                        <strong>Status:</strong><br>
                                                                        <span class="badge badge-success"><?php echo htmlspecialchars($data['nama_status']); ?></span>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        
                                                                        <strong>Nominal:</strong><br>
                                                                        <span class="text-warning font-weight-bold">Rp <?php echo number_format($data['target_nominal'], 0, ',', '.'); ?></span>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <strong>Tanggal:</strong><br>
                                                                        <?php echo date('d/m/Y', strtotime($data['tgl_surat'])); ?><br><br>
                                                                        <strong>Periode:</strong><br>
                                                                        <span class="badge badge-info"><?php echo date('M-Y', strtotime($data['tgl_surat'])); ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Modal Footer button simpan edit rencana-->
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                <i class="fas fa-times mr-1"></i>Batal
                                                            </button>
                                                            <button type="submit" class="btn btn-success" name="ubahRencana">
                                                                <i class="fas fa-save mr-1"></i>Simpan Perubahan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Button hapus rencana -->
                                        <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusTarget<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Hapus">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </a>
                                        <!-- Modal Hapus -->
                                        <div class="modal fade" id="modalHapusTarget<?php echo $data['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="fas fa-trash mr-2"></i>Hapus Data Rencana Training Center</h5>
                                                    </div>
                                                    <div class="modal-body text-left">Anda yakin ingin menghapus data Rencana Training Center<strong><?php echo $data['nama_program']; ?></strong> Tanggal <strong><?php echo date('d/m/Y', strtotime($data['tgl_surat'])); ?></strong>?</div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default btn-round" data-dismiss="modal">Batal</button>
                                                        <a href="modules/training_center/proses_hapus.php?id=<?php echo $data['id']; ?>" class="btn btn-danger btn-round">Ya, Hapus</a>
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
    const monthlyAccumulativeData = <?php echo json_encode($monthly_accumulative); ?>;
    const programData = <?php echo json_encode($program_data); ?>;
    const kategoriData = <?php echo json_encode($kategori_data); ?>; // Data kategori baru

    // Variabel untuk menyimpan instance chart
    let lineChart, doughnutChart, barChart, kategoriChart;

    // Function untuk inisialisasi semua chart
    function initializeCharts() {
        console.log('Initializing all charts...');
        
        // Destroy existing charts jika ada
        if (lineChart) {
            lineChart.destroy();
            lineChart = null;
        }
        if (doughnutChart) {
            doughnutChart.destroy();
            doughnutChart = null;
        }
        if (barChart) {
            barChart.destroy();
            barChart = null;
        
        }
        if (kategoriChart) {
            kategoriChart.destroy();
            kategoriChart = null;
        }

        // Panggil semua fungsi inisialisasi chart
        try {
            initializeLineChart();
            initializeDoughnutChart();
            initializeBarChart();
            initializeKategoriChart();
            console.log('All charts initialized successfully');
        } catch (error) {
            console.error('Error initializing charts:', error);
        }
    }

    // Function untuk inisialisasi line chart
    function initializeLineChart() {
        console.log('Initializing line chart...');
        
        // Validasi data
        if (!monthlyAccumulativeData || Object.keys(monthlyAccumulativeData).length === 0) {
            console.error('Data monthlyAccumulativeData kosong atau tidak tersedia');
            return;
        }

        // Siapkan data untuk line chart dengan 4 garis
        const months = Object.keys(monthlyAccumulativeData).sort();
        const lineChartLabels = months.map(month => monthlyAccumulativeData[month].label);
        const targetData = months.map(month => monthlyAccumulativeData[month].target || 0);
        const realisasiData = months.map(month => monthlyAccumulativeData[month].realisasi || 0);
        const terkontrakData = months.map(month => monthlyAccumulativeData[month].terkontrak || 0);
        const ongoingData = months.map(month => monthlyAccumulativeData[month].ongoing || 0);

        // Cek apakah canvas element ada
        const lineCtx = document.getElementById('lineChart');
        if (!lineCtx) {
            console.error('Canvas element lineChart tidak ditemukan');
            return;
        }

        // Line Chart - 4 Garis: Target, Realisasi, Terkontrak, Ongoing
        lineChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: lineChartLabels,
                datasets: [
                    {
                        label: 'Target',
                        data: targetData,
                        borderColor: '#FF6384',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4,
                        fill: false,
                        borderWidth: 3,
                        pointBackgroundColor: '#FF6384',
                        pointBorderColor: '#FF6384',
                        pointRadius: 5
                    },
                    {
                        label: 'Realisasi',
                        data: realisasiData,
                        borderColor: '#36A2EB',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.4,
                        fill: false,
                        borderWidth: 3,
                        pointBackgroundColor: '#36A2EB',
                        pointBorderColor: '#36A2EB',
                        pointRadius: 5
                    },
                    {
                        label: 'Terkontrak',
                        data: terkontrakData,
                        borderColor: '#FFCE56',
                        backgroundColor: 'rgba(255, 206, 86, 0.1)',
                        tension: 0.4,
                        fill: false,
                        borderWidth: 3,
                        pointBackgroundColor: '#FFCE56',
                        pointBorderColor: '#FFCE56',
                        pointRadius: 5
                    },
                    {
                        label: 'Ongoing',
                        data: ongoingData,
                        borderColor: '#4BC0C0',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4,
                        fill: false,
                        borderWidth: 3,
                        pointBackgroundColor: '#4BC0C0',
                        pointBorderColor: '#4BC0C0',
                        pointRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Progress Kumulatif Bulanan'
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
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
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
        
        console.log('Line chart initialized successfully');
    }

    // Function untuk inisialisasi doughnut chart
    function initializeDoughnutChart() {
        console.log('Initializing doughnut chart...');
        
        const doughnutCtx = document.getElementById('doughnutChart');
        if (!doughnutCtx) {
            console.error('Canvas element doughnutChart tidak ditemukan');
            return;
        }

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
                    title: {
                        display: true,
                        text: 'Progress Target vs Realisasi'
                    },
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const percentage = totalTarget > 0 ? ((context.parsed / totalTarget) * 100).toFixed(1) : 0;
                                return context.label + ': Rp ' + context.parsed.toLocaleString('id-ID') + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        
        console.log('Doughnut chart initialized successfully');
    }

    // Function untuk inisialisasi bar chart
    function initializeBarChart() {
        console.log('Initializing bar chart...');
        
        // Validasi data
        if (!monthlyAccumulativeData || Object.keys(monthlyAccumulativeData).length === 0) {
            console.error('Data monthlyAccumulativeData kosong atau tidak tersedia');
            return;
        }
        
        const months = Object.keys(monthlyAccumulativeData).sort();
        const monthlyLabels = months.map(month => monthlyAccumulativeData[month].label);
        const monthlyTargetData = months.map(month => monthlyAccumulativeData[month].target || 0);
        const monthlyRealisasiData = months.map(month => monthlyAccumulativeData[month].realisasi || 0);
        
        const barCtx = document.getElementById('barChart');
        if (!barCtx) {
            console.error('Canvas element barChart tidak ditemukan');
            return;
        }
        
        barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Target Kumulatif',
                    data: monthlyTargetData,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: '#FF6384',
                    borderWidth: 1
                }, {
                    label: 'Realisasi Kumulatif',
                    data: monthlyRealisasiData,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: '#36A2EB',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Target vs Realisasi Kumulatif Bulanan'
                    },
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
                        title: {
                            display: true,
                            text: 'Nominal (Rupiah)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                }
            }
        });
        
        console.log('Bar chart initialized successfully');
    }

    

    // Function untuk initialize pie chart kategori
    function initializeKategoriChart() {
        console.log('Initializing kategori chart...');
        console.log('kategoriData:', kategoriData);
        
        // Validasi data
        if (!kategoriData || Object.keys(kategoriData).length === 0) {
            console.error('Data kategoriData kosong atau tidak tersedia');
            return;
        }
        
        const kategoriCtx = document.getElementById('kategoriChart');
        if (!kategoriCtx) {
            console.error('Canvas element kategoriChart tidak ditemukan');
            return;
        }
        
        // Pastikan kita mendapatkan context 2D
        const ctx = kategoriCtx.getContext('2d');
        if (!ctx) {
            console.error('Tidak dapat mendapatkan 2D context dari kategoriChart');
            return;
        }
        
        // Siapkan data untuk pie chart berdasarkan jumlah count
        const kategoriLabels = Object.keys(kategoriData);
        const kategoriCountData = kategoriLabels.map(kategori => {
            const value = kategoriData[kategori].count || 0;
            console.log(`Kategori: ${kategori}, Count: ${value}`);
            return value;
        });
        
        console.log('Kategori Labels:', kategoriLabels);
        console.log('Kategori Count Data:', kategoriCountData);
        
        // Generate warna untuk setiap kategori
        const colors = generateColors(kategoriLabels.length);
        
        // Buat chart dengan konfigurasi yang lebih sederhana
        kategoriChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: kategoriLabels,
                datasets: [{
                    label: 'Jumlah per Kategori',
                    data: kategoriCountData,
                    backgroundColor: colors.background,
                    borderColor: colors.border,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribusi Kategori Training Center'
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((sum, val) => sum + val, 0);
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.parsed + ' kegiatan (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        
        console.log('Kategori chart initialized successfully');
    }

    // Utility function untuk generate warna
    function generateColors(count) {
        const baseColors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4',
            '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE'
        ];
        
        const backgroundColors = [];
        const borderColors = [];
        
        for (let i = 0; i < count; i++) {
            const baseColor = baseColors[i % baseColors.length];
            backgroundColors.push(baseColor + '80'); // Tambahkan transparansi
            borderColors.push(baseColor);
        }
        
        return {
            background: backgroundColors,
            border: borderColors
        };
    }

    // Function untuk update semua chart
    function updateCharts() {
        console.log('Updating all charts...');
        initializeCharts();
    }

    // Function untuk refresh data
    function refreshData() {
        console.log('Refreshing page...');
        location.reload();
    }

    // Initialize semua saat DOM ready
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#RealisasiDataTable')) {
            $('#RealisasiDataTable').DataTable().destroy();
        }
        
        $('#RealisasiDataTable').DataTable({
            "pageLength": 25,
            "ordering": false
        });

        if ($.fn.DataTable.isDataTable('#kontrakDataTable')) {
            $('#kontrakDataTable').DataTable().destroy();
        }
        
        $('#kontrakDataTable').DataTable({
            "pageLength": 25,
            "ordering": false
        });

        if ($.fn.DataTable.isDataTable('#OngoingDataTable')) {
            $('#OngoingDataTable').DataTable().destroy();
        }
        
        $('#OngoingDataTable').DataTable({
            "pageLength": 25,
            "ordering": false
        });

        if ($.fn.DataTable.isDataTable('#targetDataTable')) {
            $('#targetDataTable').DataTable().destroy();
        }
        
        $('#targetDataTable').DataTable({
            "pageLength": 25,
            "ordering": false
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
                    window.history.replaceState({}, document.title, window.location.pathname + '?module=training_center');
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
                    window.history.replaceState({}, document.title, window.location.pathname + '?module=training_center');
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
                    window.history.replaceState({}, document.title, window.location.pathname + '?module=training_center');
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