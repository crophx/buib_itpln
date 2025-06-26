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
							<li class="nav-item"><a>Data Training Center</a></li>
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
                                <a href="?module=kategori_peserta" class="btn btn-primary">
                                    <span class="btn-label"></span>Kategori Peserta
                                </a>
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
                                    <h4 class="card-title mb-0" id="totalTarget">Rp <?php echo number_format($total_target_calc, 0, ',', '.'); ?></h4>
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
                                    <h4 class="card-title mb-0" id="totalRealisasi">Rp <?php echo number_format($total_realisasi_calc, 0, ',', '.'); ?></h4>
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
                                    <h4 class="card-title mb-0" id="persentaseCapaian"><?php echo $persentase_total; ?>%</h4>
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
                                    <h4 class="card-title mb-0" id="totalDokumen"><?php echo number_format($total_doc_calc, 0, ',', '.'); ?></h4>
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
                            <i class="fas fa-chart-bar mr-2"></i>Realisasi per Kategori Peserta
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
            <div class="card-header d-flex justify-content-between align-items-center">
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
                            ?>
                            <tr>
                                <td width="30" class="text-center"><?php echo $no1++; ?></td>
                                <td><?php echo htmlspecialchars($data['nama_program']); ?></td>
                                <td><?php echo htmlspecialchars($data['nama_kategori']); ?></td>
                                <td class="text-right">Rp <?php echo number_format($data['realisasi_nominal'], 0, ',', '.'); ?></td>
                                <td width="100" class="text-center"><?php echo date('M-Y', strtotime($data['tgl_surat'])); ?></td>
                                <td width="80" class="text-center">
                                    <a href="#" class="btn btn-icon btn-round btn-success btn-sm edit-btn" data-id="<?php echo $data['id']; ?>" data-type="realisasi" data-tooltip="tooltip" title="Ubah">
                                        <i class="fas fa-pencil-alt fa-sm"></i>
                                    </a>
                                    <a href="#" class="btn btn-icon btn-round btn-danger btn-sm delete-btn" data-id="<?php echo $data['id']; ?>" data-type="realisasi" data-tooltip="tooltip" title="Hapus">
                                        <i class="fas fa-trash fa-sm"></i>
                                    </a>
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
            <div class="card-header d-flex justify-content-between align-items-center">
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
                                        
                                        <!-- Modal Hapus Kontrak-->
                                        <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusKontrak<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Hapus">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </a>
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
            <div class="card-header d-flex justify-content-between align-items-center">
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
                                        
                                        <!-- Modal Hapus ONgoing -->
                                        <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusOngoing<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Hapus">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </a>
                                        
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
            <div class="card-header d-flex justify-content-between align-items-center">
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
                                ?>
                                <tr>
                                    <td width="30" class="text-center"><?php echo $no2++; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($data['nama_program']); ?>
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
                                        <!-- Button hapus rencana -->
                                        <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusTarget<?php echo $data['id']; ?>" data-tooltip="tooltip" title="Hapus">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </a>
                                        
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
    const kategoriData = <?php echo json_encode($kategori_data); ?>;
    const allTableData = <?php echo json_encode($table_data); ?>;

    // Variabel untuk menyimpan instance chart
    let lineChart, doughnutChart, barChart, kategoriChart;

    // Function untuk filter data berdasarkan tahun dan program
    function filterData(tahun, program) {
        console.log('Filtering data for Tahun:', tahun, 'Program:', program);
        
        // Filter data berdasarkan tahun dan program
        let filteredData = allTableData;
        
        if (tahun !== 'all') {
            filteredData = filteredData.filter(item => {
                const itemYear = new Date(item.tgl_surat).getFullYear().toString();
                return itemYear === tahun;
            });
        }
        
        if (program !== 'all') {
            filteredData = filteredData.filter(item => item.nama_program === program);
        }
        
        // Hitung ulang data untuk charts
        const filteredYearlyData = {};
        const filteredMonthlyData = {};
        const filteredProgramData = {};
        const filteredKategoriData = {};
        let totalTarget = 0, totalRealisasi = 0, totalKontrak = 0, totalOngoing = 0;
        
        filteredData.forEach(item => {
            const tahun = new Date(item.tgl_surat).getFullYear().toString();
            const bulan = new Date(item.tgl_surat).toISOString().slice(0, 7);
            const bulanNama = new Date(item.tgl_surat).toLocaleString('id-ID', { month: 'short', year: 'numeric' });
            
            // Yearly data
            if (!filteredYearlyData[tahun]) {
                filteredYearlyData[tahun] = { target: 0, realisasi: 0 };
            }
            filteredYearlyData[tahun].target += parseFloat(item.target_nominal || 0);
            filteredYearlyData[tahun].realisasi += parseFloat(item.realisasi_nominal || 0);
            
            // Monthly data
            if (!filteredMonthlyData[bulan]) {
                filteredMonthlyData[bulan] = {
                    label: bulanNama,
                    target: 0,
                    realisasi: 0,
                    kontrak: 0,
                    ongoing: 0
                };
            }
            filteredMonthlyData[bulan].target += parseFloat(item.target_nominal || 0);
            filteredMonthlyData[bulan].realisasi += parseFloat(item.realisasi_nominal || 0);
            filteredMonthlyData[bulan].kontrak += parseFloat(item.kontrak_nominal || 0);
            filteredMonthlyData[bulan].ongoing += parseFloat(item.ongoing_nominal || 0);
            
            // Program data
            if (!filteredProgramData[item.nama_program]) {
                filteredProgramData[item.nama_program] = { target: 0, realisasi: 0 };
            }
            filteredProgramData[item.nama_program].target += parseFloat(item.target_nominal || 0);
            filteredProgramData[item.nama_program].realisasi += parseFloat(item.realisasi_nominal || 0);
            
            // Kategori data
            if (!filteredKategoriData[item.nama_kategori]) {
                filteredKategoriData[item.nama_kategori] = { count: 0, realisasi: 0, target: 0 };
            }
            filteredKategoriData[item.nama_kategori].count++;
            filteredKategoriData[item.nama_kategori].realisasi += parseFloat(item.realisasi_nominal || 0);
            filteredKategoriData[item.nama_kategori].target += parseFloat(item.target_nominal || 0);
            
            // Total calculations
            totalTarget += parseFloat(item.target_nominal || 0);
            totalRealisasi += parseFloat(item.realisasi_nominal || 0);
            totalKontrak += parseFloat(item.kontrak_nominal || 0);
            totalOngoing += parseFloat(item.ongoing_nominal || 0);
        });
        
        // Hitung monthly accumulative
        const monthlyAccumulative = {};
        let cumulativeTarget = 0, cumulativeRealisasi = 0, cumulativeKontrak = 0, cumulativeOngoing = 0;
        const monthlyTarget = totalTarget / 12;
        
        Object.keys(filteredMonthlyData).sort().forEach(bulan => {
            cumulativeTarget += monthlyTarget;
            cumulativeRealisasi += filteredMonthlyData[bulan].realisasi;
            cumulativeKontrak += filteredMonthlyData[bulan].realisasi + filteredMonthlyData[bulan].kontrak;
            cumulativeOngoing += filteredMonthlyData[bulan].realisasi + filteredMonthlyData[bulan].kontrak + filteredMonthlyData[bulan].ongoing;
            
            monthlyAccumulative[bulan] = {
                label: filteredMonthlyData[bulan].label,
                target: cumulativeTarget,
                realisasi: cumulativeRealisasi,
                terkontrak: cumulativeKontrak,
                ongoing: cumulativeOngoing
            };
        });
        
        return {
            yearlyData: filteredYearlyData,
            monthlyAccumulative: monthlyAccumulative,
            programData: filteredProgramData,
            kategoriData: filteredKategoriData,
            totals: { target: totalTarget, realisasi: totalRealisasi, kontrak: totalKontrak, ongoing: totalOngoing },
            filteredTableData: filteredData
        };
    }

    // Function untuk update semua chart dan tabel
    // Function untuk update semua chart dan tabel
    function updateChartsAndTables() {
        const tahun = $('#filterTahun').val();
        const program = $('#filterProgram').val();
        const periode = $('#filterPeriode').val();
        
        console.log('Updating charts, tables, and summary cards with filters:', { tahun, program, periode });
        
        const filteredData = filterData(tahun, program);
        
        // Update summary cards
        const totalTarget = filteredData.totals.target;
        const totalRealisasi = filteredData.totals.realisasi;
        const persentaseCapaian = totalTarget > 0 ? ((totalRealisasi / totalTarget) * 100).toFixed(2) : 0;
        const totalDokumen = filteredData.filteredTableData.length;
        
        $('#totalTarget').text('Rp ' + totalTarget.toLocaleString('id-ID'));
        $('#totalRealisasi').text('Rp ' + totalRealisasi.toLocaleString('id-ID'));
        $('#persentaseCapaian').text(persentaseCapaian + '%');
        $('#totalDokumen').text(totalDokumen.toLocaleString('id-ID'));
        
        // Update charts
        updateLineChart(filteredData.monthlyAccumulative);
        updateDoughnutChart(filteredData.totals);
        updateBarChart(filteredData.monthlyAccumulative);
        updateKategoriChart(filteredData.kategoriData);
        
        // Update tables
        updateTables(filteredData.filteredTableData);
    }

    // Function untuk update line chart
    function updateLineChart(monthlyAccumulative) {
        if (lineChart) lineChart.destroy();
        
        const months = Object.keys(monthlyAccumulative).sort();
        const labels = months.map(month => monthlyAccumulative[month].label);
        const targetData = months.map(month => monthlyAccumulative[month].target || 0);
        const realisasiData = months.map(month => monthlyAccumulative[month].realisasi || 0);
        const terkontrakData = months.map(month => monthlyAccumulative[month].terkontrak || 0);
        const ongoingData = months.map(month => monthlyAccumulative[month].ongoing || 0);
        
        lineChart = new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Target', data: targetData, borderColor: '#FF6384', tension: 0.4, fill: false },
                    { label: 'Realisasi', data: realisasiData, borderColor: '#36A2EB', tension: 0.4, fill: false },
                    { label: 'Terkontrak', data: terkontrakData, borderColor: '#FFCE56', tension: 0.4, fill: false },
                    { label: 'Ongoing', data: ongoingData, borderColor: '#4BC0C0', tension: 0.4, fill: false }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Progress Kumulatif Bulanan' },
                    legend: { position: 'top' },
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
    }

    // Function untuk update doughnut chart
    function updateDoughnutChart(totals) {
        if (doughnutChart) doughnutChart.destroy();
        
        const totalProgress = totals.realisasi + totals.kontrak + totals.ongoing;
        const sisaTarget = totals.target - totalProgress > 0 ? totals.target - totalProgress : 0;
        
        doughnutChart = new Chart(document.getElementById('doughnutChart'), {
            type: 'doughnut',
            data: {
                labels: ['Realisasi', 'Kontrak', 'Ongoing', 'Sisa Target'],
                datasets: [{
                    data: [totals.realisasi, totals.kontrak, totals.ongoing, sisaTarget],
                    backgroundColor: ['#4BC0C0', '#36A2EB', '#FFCE56', '#FFE0E0'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Progress Target vs Realisasi, Kontrak, dan Ongoing' },
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = totals.target;
                                const value = context.parsed;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return context.label + ': Rp ' + value.toLocaleString('id-ID') + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Function untuk update bar chart
    function updateBarChart(monthlyAccumulative) {
        if (barChart) barChart.destroy();
        
        const months = Object.keys(monthlyAccumulative).sort();
        const labels = months.map(month => monthlyAccumulative[month].label);
        const targetData = months.map(month => monthlyAccumulative[month].target || 0);
        const realisasiData = months.map(month => monthlyAccumulative[month].realisasi || 0);
        
        barChart = new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Target Kumulatif', data: targetData, backgroundColor: 'rgba(255, 99, 132, 0.7)' },
                    { label: 'Realisasi Kumulatif', data: realisasiData, backgroundColor: 'rgba(54, 162, 235, 0.7)' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Target vs Realisasi Kumulatif Bulanan' },
                    legend: { position: 'top' }
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
    }

    // Function untuk update kategori chart
    function updateKategoriChart(kategoriData) {
        if (kategoriChart) kategoriChart.destroy();
        
        const labels = Object.keys(kategoriData);
        const countData = labels.map(kategori => kategoriData[kategori].count || 0);
        const colors = generateColors(labels.length);
        
        kategoriChart = new Chart(document.getElementById('kategoriChart'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah per Kategori',
                    data: countData,
                    backgroundColor: colors.background,
                    borderColor: colors.border,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Distribusi Kategori Training Center' },
                    legend: { position: 'bottom' },
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
    }

    //TAMPIL DATA TABEL REALISASI, TABEL KONTRAK, TABEL ONGOING, TABEL RENCANA
    // Function untuk update semua tabel
    function updateTables(filteredData) {
        // Update Realisasi table
        const realisasiTable = $('#RealisasiDataTable').DataTable();
        realisasiTable.clear();
        let totalRealisasi = 0;
        filteredData.filter(d => d.realisasi_nominal > 0).forEach((data, index) => {
            totalRealisasi += parseFloat(data.realisasi_nominal);
            realisasiTable.row.add([
                index + 1,
                data.nama_program,
                data.nama_kategori,
                'Rp ' + parseFloat(data.realisasi_nominal).toLocaleString('id-ID'),
                new Date(data.tgl_surat).toLocaleString('id-ID', { month: 'short', year: 'numeric' }),
                getAksiButtons(data.id, 'realisasi')
            ]);
        });
        realisasiTable.draw();
        $('#RealisasiDataTable tfoot').html(`
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td class="text-center" colspan="3"><strong>TOTAL REALISASI</strong></td>
                <td class="text-right" style="color: #28a745; font-size: 1.1em;">Rp ${totalRealisasi.toLocaleString('id-ID')}</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
            </tr>
        `);
        
        // Update Kontrak table
        const kontrakTable = $('#kontrakDataTable').DataTable();
        kontrakTable.clear();
        let totalKontrak = 0;
        filteredData.filter(d => d.kontrak_nominal > 0).forEach((data, index) => {
            totalKontrak += parseFloat(data.kontrak_nominal);
            kontrakTable.row.add([
                index + 1,
                data.nama_program,
                data.nama_kategori,
                new Date(data.tgl_surat).toLocaleString('id-ID', { month: 'short', year: 'numeric' }),
                data.keterangan_program,
                'Rp ' + parseFloat(data.kontrak_nominal).toLocaleString('id-ID'),
                `<span class="badge badge-primary">${data.nama_status}</span>`,
                getAksiButtons(data.id, 'kontrak')
            ]);
        });
        kontrakTable.draw();
        $('#kontrakDataTable tfoot').html(`
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td class="text-center" colspan="5"><strong>TOTAL KONTRAK</strong></td>
                <td class="text-right" style="color: #28a745; font-size: 1.1em;">Rp ${totalKontrak.toLocaleString('id-ID')}</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
            </tr>
        `);
        
        // Update Ongoing table
        const ongoingTable = $('#OngoingDataTable').DataTable();
        ongoingTable.clear();
        let totalOngoing = 0;
        filteredData.filter(d => d.ongoing_nominal > 0).forEach((data, index) => {
            totalOngoing += parseFloat(data.ongoing_nominal);
            ongoingTable.row.add([
                index + 1,
                data.nama_program,
                data.keterangan_program,
                new Date(data.tgl_surat).toLocaleString('id-ID', { month: 'short', year: 'numeric' }),
                'Rp ' + parseFloat(data.ongoing_nominal).toLocaleString('id-ID'),
                `<span class="badge badge-danger">${data.nama_status}</span>`,
                getAksiButtons(data.id, 'ongoing')
            ]);
        });
        ongoingTable.draw();
        $('#OngoingDataTable tfoot').html(`
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td class="text-center" colspan="4"><strong>TOTAL ONGOING</strong></td>
                <td class="text-right" style="color: #28a745; font-size: 1.1em;">Rp ${totalOngoing.toLocaleString('id-ID')}</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
            </tr>
        `);
        
        // Update Target table
        const targetTable = $('#targetDataTable').DataTable();
        targetTable.clear();
        let totalTarget = 0;
        filteredData.filter(d => d.target_nominal > 0).forEach((data, index) => {
            totalTarget += parseFloat(data.target_nominal);
            targetTable.row.add([
                index + 1,
                data.nama_program,
                data.keterangan_program,
                'Rp ' + parseFloat(data.target_nominal).toLocaleString('id-ID'),
                `<span class="badge badge-warning">${data.nama_status}</span>`,
                getAksiButtons(data.id, 'target')
            ]);
        });
        targetTable.draw();
        $('#targetDataTable tfoot').html(`
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td class="text-center" colspan="3"><strong>TOTAL RENCANA</strong></td>
                <td class="text-right" style="color: #28a745; font-size: 1.1em;">Rp ${totalTarget.toLocaleString('id-ID')}</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
            </tr>
        `);
    }

    // Function untuk generate aksi buttons
    function getAksiButtons(id, type) {
        return `
            <a href="#" class="btn btn-icon btn-round btn-success btn-sm edit-btn" data-id="${id}" data-type="${type}" title="Ubah">
                <i class="fas fa-pencil-alt fa-sm"></i>
            </a>
            <a href="#" class="btn btn-icon btn-round btn-danger btn-sm delete-btn" data-id="${id}" data-type="${type}" title="Hapus">
                <i class="fas fa-trash fa-sm"></i>
            </a>
        `;
    }

    // MODAL UBAH DAN MODAL HAPUS   
    // Function untuk generate modal content dynamically TABEL REALISASI, TABEL TERKONTRAK, TABEL ONGOING, TABEL RENCANA
    function generateModal(id, type, isDelete = false) {
        const capitalizedType = type.charAt(0).toUpperCase() + type.slice(1);
        const modalId = isDelete ? `modalHapus${capitalizedType}${id}` : `modal${capitalizedType}${id}`;
        
        // Find the data for this ID
        const item = allTableData.find(d => d.id == id);
        if (!item) {
            console.error(`Data with ID ${id} not found in allTableData`);
            return '';
        }

        if (isDelete) {
            // Delete modal
            return `
                <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="${modalId}Label" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-trash mr-2"></i>Hapus Data ${capitalizedType} Training Center</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body text-left">
                                Anda yakin ingin menghapus data ${capitalizedType} Training Center <strong>${item.nama_program}</strong> Tanggal <strong>${new Date(item.tgl_surat).toLocaleDateString('id-ID')}</strong>?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default btn-round" data-dismiss="modal">Batal</button>
                                <a href="modules/training_center/proses_hapus.php?id=${id}" class="btn btn-danger btn-round delete-confirm">Ya, Hapus</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Edit modal UBAH
        let nominalFields = '';
        switch (type) {
            //modal Ubah REALISASI
            case 'realisasi':
                nominalFields = `
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-semibold">
                                <i class="fas fa-money-bill-wave mr-1 text-warning"></i>Nominal Realisasi <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-warning text-white">Rp</span>
                                </div>
                                <input type="text" class="form-control currency" name="realisasi_nominal" value="${Number(item.realisasi_nominal || 0).toLocaleString('id-ID')}" required>
                            </div>
                        </div>
                    </div>
                `;
                break;
            //modal ubah KONTRAK
            case 'kontrak':
                nominalFields = `
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-semibold">
                                <i class="fas fa-money-bill-wave mr-1 text-warning"></i>Nominal Kontrak <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-warning text-white">Rp</span>
                                </div>
                                <input type="text" class="form-control currency" name="kontrak_nominal" value="${Number(item.kontrak_nominal || 0).toLocaleString('id-ID')}" required>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>Jika sudah terealisasi, isi dengan 0
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-semibold">
                                <i class="fas fa-bullseye mr-1 text-primary"></i>Realisasi Nominal
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-primary text-white">Rp</span>
                                </div>
                                <input type="text" class="form-control currency" name="realisasi_nominal" value="${Number(item.realisasi_nominal || 0).toLocaleString('id-ID')}">
                            </div>
                        </div>
                    </div>
                `;
                break;
            //Modal UBAH ONGOING
            case 'ongoing':
                nominalFields = `
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-semibold">
                                <i class="fas fa-money-bill-wave mr-1 text-warning"></i>Nominal Ongoing <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-warning text-white">Rp</span>
                                </div>
                                <input type="text" class="form-control currency" name="ongoing_nominal" value="${Number(item.ongoing_nominal || 0).toLocaleString('id-ID')}" required>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>Jika sudah terealisasi, isi dengan 0
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-semibold">
                                <i class="fas fa-bullseye mr-1 text-primary"></i>Kontrak Nominal
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-primary text-white">Rp</span>
                                </div>
                                <input type="text" class="form-control currency" name="kontrak_nominal" value="${Number(item.kontrak_nominal || 0).toLocaleString('id-ID')}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-semibold">
                                <i class="fas fa-bullseye mr-1 text-primary"></i>Realisasi Nominal
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-primary text-white">Rp</span>
                                </div>
                                <input type="text" class="form-control currency" name="realisasi_nominal" value="${Number(item.realisasi_nominal || 0).toLocaleString('id-ID')}">
                            </div>
                        </div>
                    </div>
                `;
                break;
            //Modal UBAH RENCANA
            case 'target':
                nominalFields = `
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-semibold">
                                <i class="fas fa-money-bill-wave mr-1 text-warning"></i>Nominal Rencana <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-warning text-white">Rp</span>
                                </div>
                                <input type="text" class="form-control currency" name="target_nominal" value="${Number(item.target_nominal || 0).toLocaleString('id-ID')}" required>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>Ubah nominal rencana
                            </small>
                        </div>
                    </div>
                `;
                break;
            default:
                console.error(`Invalid type: ${type}`);
                return '';
        }

        return `
            <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="${modalId}Label" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header btn-success text-white">
                            <h5 class="modal-title font-weight-bold" id="${modalId}Label">
                                <i class="fas fa-edit mr-2"></i>Edit Data ${capitalizedType}
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="modules/training_center/proses_ubah.php" method="POST" id="form${capitalizedType}${id}">
                            <div class="modal-body">
                                <input type="hidden" name="id" value="${id}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label font-weight-semibold">
                                                <i class="fas fa-graduation-cap mr-1 text-primary"></i>Nama Program <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" name="nama_program" value="${item.nama_program || ''}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label font-weight-semibold">
                                                <i class="fas fa-tags mr-1 text-success"></i>Kategori TC <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" name="kategori_tc" required>
                                                <option value="">-- Pilih Kategori --</option>
                                                <?php
                                                $kategori_query = mysqli_query($mysqli, "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");
                                                while ($kategori = mysqli_fetch_array($kategori_query)) {
                                                    echo "<option value='{$kategori['id_kategori']}' " . ($kategori['id_kategori'] == '${item.kategori_tc}' ? 'selected' : '') . ">{$kategori['nama_kategori']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    ${nominalFields}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label font-weight-semibold">
                                                <i class="fas fa-calendar-alt mr-1 text-info"></i>Tanggal Surat <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" name="tgl_surat" value="${item.tgl_surat ? new Date(item.tgl_surat).toISOString().split('T')[0] : ''}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label font-weight-semibold">
                                                <i class="fas fa-info-circle mr-1 text-success"></i>Status <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" name="status_tc" required>
                                                <option value="">-- Pilih Status --</option>
                                                <?php
                                                $status_query = mysqli_query($mysqli, "SELECT * FROM tbl_status ORDER BY nama_status ASC");
                                                while ($status = mysqli_fetch_array($status_query)) {
                                                    echo "<option value='{$status['id_status']}' " . ($status['id_status'] == '${item.status_tc}' ? 'selected' : '') . ">{$status['nama_status']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label font-weight-semibold">
                                                <i class="fas fa-sticky-note mr-1 text-secondary"></i>Keterangan
                                            </label>
                                            <textarea class="form-control" name="keterangan_program" rows="3">${item.keterangan_program || ''}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-light border mt-3">
                                    <h6 class="mb-2">
                                        <i class="fas fa-history mr-2 text-info"></i>Data Sebelumnya
                                    </h6>
                                    <div class="row small">
                                        <div class="col-md-4">
                                            <strong>Program:</strong><br>
                                            ${item.nama_program}<br><br>
                                            <strong>Kategori:</strong><br>
                                            <span class="badge badge-primary">${item.nama_kategori || ''}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Status:</strong><br>
                                            <span class="badge badge-success">${item.nama_status || ''}</span><br><br>
                                            <strong>Nominal:</strong><br>
                                            <span class="text-warning font-weight-bold">Rp ${Number(item[`${type}_nominal`] || 0).toLocaleString('id-ID')}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Tanggal:</strong><br>
                                            ${new Date(item.tgl_surat).toLocaleDateString('id-ID')}<br><br>
                                            <strong>Periode:</strong><br>
                                            <span class="badge badge-info">${new Date(item.tgl_surat).toLocaleString('id-ID', { month: 'short', year: 'numeric' })}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i>Batal
                                </button>
                                <button type="submit" class="btn btn-success" name="ubah${capitalizedType}">
                                    <i class="fas fa-save mr-1"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
    }

    // Utility function untuk generate warna
    function generateColors(count) {
        const baseColors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4'
        ];
        
        const backgroundColors = [];
        const borderColors = [];
        
        for (let i = 0; i < count; i++) {
            const baseColor = baseColors[i % baseColors.length];
            backgroundColors.push(baseColor + '80');
            borderColors.push(baseColor);
        }
        
        return {
            background: backgroundColors,
            border: borderColors
        };
    }

    // Function untuk toggle chart periode (yearly/monthly)
    function toggleChartPeriode() {
        updateChartsAndTables();
    }

    // Initialize saat DOM ready
    $(document).ready(function() {
        // Initialize DataTables
        ['RealisasiDataTable', 'kontrakDataTable', 'OngoingDataTable', 'targetDataTable'].forEach(tableId => {
            if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
                $(`#${tableId}`).DataTable().destroy();
            }
            $(`#${tableId}`).DataTable({
                pageLength: 25,
                ordering: false
            });
        });
        
        // Initialize charts and tables
        updateChartsAndTables();
        
        // Event listeners untuk filter
        $('#filterTahun, #filterProgram, #filterPeriode').on('change', updateChartsAndTables);
    });


    // Event delegation untuk handle button clicks
    // Event listener for edit buttons
    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const type = $(this).data('type');
        
        console.log(`Edit button clicked: ID=${id}, Type=${type}`); // Debugging
        
        // Remove existing modals to prevent duplicates
        $(`#modal${type.charAt(0).toUpperCase() + type.slice(1)}${id}`).remove();
        
        // Generate and append new modal
        const modalHtml = generateModal(id, type);
        if (!modalHtml) {
            console.error('Failed to generate edit modal');
            return;
        }
        $('body').append(modalHtml);
        
        // Initialize currency plugin for nominal inputs
        $('.currency').maskMoney({
            prefix: '',
            thousands: '.',
            decimal: ',',
            precision: 0
        });
        
        // Show modal
        $(`#modal${type.charAt(0).toUpperCase() + type.slice(1)}${id}`).modal('show');
    });

    // Event listener for delete buttons
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const type = $(this).data('type');
        
        console.log(`Delete button clicked: ID=${id}, Type=${type}`); // Debugging
        
        // Remove existing modals to prevent duplicates
        $(`#modalHapus${type.charAt(0).toUpperCase() + type.slice(1)}${id}`).remove();
        
        // Generate and append new modal
        const modalHtml = generateModal(id, type, true);
        if (!modalHtml) {
            console.error('Failed to generate delete modal');
            return;
        }
        $('body').append(modalHtml);
        
        // Show modal
        const modalSelector = `#modalHapus${type.charAt(0).toUpperCase() + type.slice(1)}${id}`;
        $(modalSelector).modal('show');
    });

    // Update the initialize function to include modal cleanup
    $(document).ready(function() {
        // Initialize DataTables
        ['RealisasiDataTable', 'kontrakDataTable', 'OngoingDataTable', 'targetDataTable'].forEach(tableId => {
            if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
                $(`#${tableId}`).DataTable().destroy();
            }
            $(`#${tableId}`).DataTable({
                pageLength: 25,
                ordering: false,
                drawCallback: function() {
                    // Reinitialize tooltips or other plugins if needed
                    $('[data-tooltip="tooltip"]').tooltip();
                }
            });
        });
        
        // Initialize charts and tables
        updateChartsAndTables();
        
        // Event listeners untuk filter
        $('#filterTahun, #filterProgram, #filterPeriode').on('change', updateChartsAndTables);
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap CSS and JS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <!-- jQuery MaskMoney -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>


<?php } 
}
?>