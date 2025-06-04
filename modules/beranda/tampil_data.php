<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    // alihkan ke halaman error 404
    header('location: 404.html');
}
// jika file di include oleh file lain, tampilkan isi file
else { ?>
    <div class="panel-header">
        <div class="page-inner py-45">
            <div class="page-header">
                <!-- judul halaman -->
                <h4 class="page-title"><i class="fas fa-home mr-2"></i> Beranda</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=beranda">Beranda</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="row row-card-no-pd align-items-center p-2 p-sm-4">
            <div class="col-lg-3 mb-4 mb-lg-0">
                <img class="img-fluid" src="assets/img/bg-dashboard.jpg">
            </div>
            <div class="col-lg-9 heroes-text px-xl-5">
                <h2 class="text-success mb-2">Selamat datang kembali <span><?php echo $_SESSION['nama_user']; ?></span> di <span> Website <?php echo $data['nama']; ?></span></h2>
                <h4 class="text-muted mb-4">Dashsboard Kerja adalah Sistem Informasi Pengelolaan Arsip Digital Dokumen dan juga antarmuka atau alat digital yang dirancang untuk memberikan karyawan ikhtisar komprehensif tentang berbagai aspek pekerjaan, kinerja, dan informasi organisasi yang relevan</h4>
                <a href="?module=arsip" class="btn btn-success btn-round px-4 mr-2 mb-3 mb-lg-0">
                    <span class="btn-label"><i class="fas fa-folder-open mr-2"></i></span> Arsip Dokumen
                </a>
            </div>
        </div>

        <?php
        // pengecekan hak akses untuk menampilkan konten sesuai dengan hak akses
        // jika hak akses = Administrator, tampilkan konten
        if ($_SESSION['hak_akses'] == 'SuperAdmin') { ?>
            <!-- tampilkan informasi jumlah data arsip, jenis, dan pengguna -->
            <div class="row mt-5">
                <!-- menampilkan informasi jumlah data arsip dokumen -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-teal bubble-shadow-small">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Arsip Dokumen</p>
                                        <?php
                                        // sql statement untuk menampilkan jumlah data pada tabel "tbl_arsip"
                                        $query = mysqli_query($mysqli, "SELECT id_arsip FROM tbl_arsip")
                                                                        or die('Ada kesalahan pada query jumlah data arsip : ' . mysqli_error($mysqli));
                                        // ambil jumlah data dari hasil query
                                        $jumlah_arsip = mysqli_num_rows($query);
                                        ?>
                                        <!-- tampilkan data -->
                                        <h4 class="card-title"><?php echo number_format($jumlah_arsip, 0, '', '.'); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan informasi jumlah data jenis dokumen -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-warning bubble-shadow-small">
                                        <i class="fas fa-clone"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Jenis Dokumen</p>
                                        <?php
                                        // sql statement untuk menampilkan jumlah data pada tabel "tbl_jenis"
                                        $query = mysqli_query($mysqli, "SELECT id_jenis FROM tbl_jenis")
                                                                        or die('Ada kesalahan pada query jumlah data jenis : ' . mysqli_error($mysqli));
                                        // ambil jumlah data dari hasil query
                                        $jumlah_jenis = mysqli_num_rows($query);
                                        ?>
                                        <!-- tampilkan data -->
                                        <h4 class="card-title"><?php echo number_format($jumlah_jenis, 0, '', '.'); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan informasi jumlah data pengguna aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-success bubble-shadow-small">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Pengguna Aplikasi</p>
                                        <?php
                                        // sql statement untuk menampilkan jumlah data pada tabel "tbl_user"
                                        $query = mysqli_query($mysqli, "SELECT id_user FROM tbl_user")
                                                                        or die('Ada kesalahan pada query jumlah data user : ' . mysqli_error($mysqli));
                                        // ambil jumlah data dari hasil query
                                        $jumlah_user = mysqli_num_rows($query);
                                        ?>
                                        <!-- tampilkan data -->
                                        <h4 class="card-title"><?php echo number_format($jumlah_user, 0, '', '.'); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan button Pusat Bisnis aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center" style="color: steelblue;">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <h4 class="card-tittle">Pusat Bisnis</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan button Bagian Kerja Sama (BKS) aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <a href="?module=bks" class="text-decoration-none">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-icon">
                                        <div class="icon-big text-center bubble-shadow-small">
                                            <i class="fas fa-clone" style="color: antiquewhite;"></i>
                                        </div>
                                    </div>
                                    <div class="col col-stats ml-3 ml-sm-0">
                                        <div class="numbers">
                                            <h4 class="card-tittle">Bagian Kerja Sama (BKS)</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- menampilkan button Bagian Kerja Internasional (BKI) aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center bubble-shadow-small">
                                        <i class="fas fa-camera" style="color: violet;"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <h4 class="card-tittle">Bagian Kerja Internasional (BKI)</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan button BUIB aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <a href="?module=buib" class="text-decoration-none">
                        <div class="card card-stats card-round card-hover">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-icon">
                                        <div class="icon-big text-center bubble-shadow-small">
                                            <i class="fas fa-clone" style="color: antiquewhite;"></i>
                                        </div>
                                    </div>
                                    <div class="col col-stats ml-3 ml-sm-0">
                                        <div class="numbers">
                                            <h4 class="card-tittle text-dark">BUIB</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- menampilkan button LENTERA aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center bubble-shadow-small">
                                        <i class="fas fa-leaf" style="color: green;"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <h4 class="card-tittle">LEMTERA</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan button Training Center (TC) aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center bubble-shadow-small">
                                        <i class="fas fa-camera" style="color: violet;"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <h4 class="card-tittle">Training Center (TC)</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        // jika hak akses selain Administrator, tampilkan konten
        else { ?>
            <!-- tampilkan informasi jumlah data arsip, dan jenis -->
            <div class="row mt-5">
                <!-- menampilkan informasi jumlah data arsip dokumen -->
                <div class="col-sm-12 col-md-6">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-teal bubble-shadow-small">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Arsip Dokumen</p>
                                        <?php
                                        // sql statement untuk menampilkan jumlah data pada tabel "tbl_arsip"
                                        $query = mysqli_query($mysqli, "SELECT id_arsip FROM tbl_arsip")
                                                                        or die('Ada kesalahan pada query jumlah data arsip : ' . mysqli_error($mysqli));
                                        // ambil jumlah data dari hasil query
                                        $jumlah_arsip = mysqli_num_rows($query);
                                        ?>
                                        <!-- tampilkan data -->
                                        <h4 class="card-title"><?php echo number_format($jumlah_arsip, 0, '', '.'); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan informasi jumlah data jenis dokumen -->
                <div class="col-sm-12 col-md-6">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-warning bubble-shadow-small">
                                        <i class="fas fa-clone"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Jenis Dokumen</p>
                                        <?php
                                        // sql statement untuk menampilkan jumlah data pada tabel "tbl_jenis"
                                        $query = mysqli_query($mysqli, "SELECT id_jenis FROM tbl_jenis")
                                                                        or die('Ada kesalahan pada query jumlah data jenis : ' . mysqli_error($mysqli));
                                        // ambil jumlah data dari hasil query
                                        $jumlah_jenis = mysqli_num_rows($query);
                                        ?>
                                        <!-- tampilkan data -->
                                        <h4 class="card-title"><?php echo number_format($jumlah_jenis, 0, '', '.'); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                
            </div>
        <?php } ?>
        
        <!-- Chart Target vs Realisasi Per Divisi -->
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-chart-line mr-2"></i> Perbandingan Target vs Realisasi Per Divisi</div>
                    </div>
                    <div class="card-body">
                        <canvas id="targetRealisasiChart" style="width: 100%; height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart container untuk menampilkan data arsip per jenis dokumen -->
        <div class="row mt-2">
            <!-- Bar Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-chart-bar mr-2"></i> Jumlah Arsip Per Jenis Dokumen (Bar Chart)</div>
                    </div>
                    <div class="card-body">
                        <canvas id="barChart" style="width: 100%; height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Pie Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-chart-pie mr-2"></i> Distribusi Arsip Per Jenis Dokumen (Pie Chart)</div>
                    </div>
                    <div class="card-body">
                        <canvas id="pieChart" style="width: 100%; height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Detail Target vs Realisasi -->
        <div class="card mt-2">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-table mr-2"></i> Detail Target vs Realisasi Per Divisi</div>
                <div class="card-tools">
                    <button class="btn btn-sm btn-secondary" onclick="toggleTargetTable()">
                        <i class="fas fa-eye" id="toggleTargetIcon"></i> <span id="toggleTargetText">Sembunyikan Tabel</span>
                    </button>
                </div>
            </div>
            <div class="card-body" id="targetTableContainer">
                <div class="table-responsive">
                    <table id="target-datatables" class="display table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Divisi</th>
                                <th class="text-center">Target (Rp)</th>
                                <th class="text-center">Realisasi (Rp)</th>
                                <th class="text-center">Persentase Tercapai</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no_target = 1;
                            $divisi_data = [];
                            
                            // Query untuk BUIB
                            $query_buib = mysqli_query($mysqli, "SELECT SUM(target_nominal) as total_target, SUM(realisasi_nominal) as total_realisasi 
                                                                FROM tbl_buib as a INNER JOIN tbl_jenis as b ON a.jenis_dokumen=b.id_jenis")
                                                               or die('Ada kesalahan pada query BUIB : ' . mysqli_error($mysqli));
                            $data_buib = mysqli_fetch_assoc($query_buib);
                            $target_buib = $data_buib['total_target'] ? $data_buib['total_target'] : 0;
                            $realisasi_buib = $data_buib['total_realisasi'] ? $data_buib['total_realisasi'] : 0;
                            
                            // Query untuk BKS
                            $query_bks = mysqli_query($mysqli, "SELECT SUM(target_nominal) as total_target, SUM(realisasi_nominal) as total_realisasi 
                                                               FROM tbl_bks as a INNER JOIN tbl_jenis as b ON a.jenis_dokumen=b.id_jenis")
                                                              or die('Ada kesalahan pada query BKS : ' . mysqli_error($mysqli));
                            $data_bks = mysqli_fetch_assoc($query_bks);
                            $target_bks = $data_bks['total_target'] ? $data_bks['total_target'] : 0;
                            $realisasi_bks = $data_bks['total_realisasi'] ? $data_bks['total_realisasi'] : 0;
                            
                            // Query untuk BKI
                            $query_bki = mysqli_query($mysqli, "SELECT SUM(target_nominal) as total_target, SUM(realisasi_nominal) as total_realisasi 
                                                               FROM tbl_bki as a INNER JOIN tbl_jenis as b ON a.jenis_dokumen=b.id_jenis")
                                                              or die('Ada kesalahan pada query BKI : ' . mysqli_error($mysqli));
                            $data_bki = mysqli_fetch_assoc($query_bki);
                            $target_bki = $data_bki['total_target'] ? $data_bki['total_target'] : 0;
                            $realisasi_bki = $data_bki['total_realisasi'] ? $data_bki['total_realisasi'] : 0;
                            
                            // Simpan data untuk chart
                            $divisi_names = ['BUIB', 'BKS', 'BKI'];
                            $target_data = [$target_buib, $target_bks, $target_bki];
                            $realisasi_data = [$realisasi_buib, $realisasi_bks, $realisasi_bki];
                            
                            // Tampilkan data dalam tabel
                            $divisions = [
                                ['name' => 'BUIB', 'target' => $target_buib, 'realisasi' => $realisasi_buib],
                                ['name' => 'BKS', 'target' => $target_bks, 'realisasi' => $realisasi_bks],
                                ['name' => 'BKI', 'target' => $target_bki, 'realisasi' => $realisasi_bki]
                            ];
                            
                            foreach ($divisions as $division) {
                                $persentase = $division['target'] > 0 ? round(($division['realisasi'] / $division['target']) * 100, 1) : 0;
                                $status = '';
                                $status_class = '';
                                
                                if ($persentase >= 100) {
                                    $status = 'Target Tercapai';
                                    $status_class = 'badge-success';
                                } elseif ($persentase >= 75) {
                                    $status = 'Mendekati Target';
                                    $status_class = 'badge-warning';
                                } else {
                                    $status = 'Belum Tercapai';
                                    $status_class = 'badge-danger';
                                }
                                ?>
                                <tr>
                                    <td width="50" class="text-center"><?php echo $no_target++; ?></td>
                                    <td width="150" class="text-center"><strong><?php echo $division['name']; ?></strong></td>
                                    <td width="200" class="text-right"><?php echo 'Rp ' . number_format($division['target'], 0, ',', '.'); ?></td>
                                    <td width="200" class="text-right"><?php echo 'Rp ' . number_format($division['realisasi'], 0, ',', '.'); ?></td>
                                    <td width="150" class="text-center"><?php echo $persentase; ?>%</td>
                                    <td width="150" class="text-center">
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status; ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabel sebagai backup/detail view -->
        <div class="card mt-2">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-table mr-2"></i> Detail Jumlah Arsip Per Jenis Dokumen</div>
                <div class="card-tools">
                    <button class="btn btn-sm btn-secondary" onclick="toggleTable()">
                        <i class="fas fa-eye" id="toggleIcon"></i> <span id="toggleText">Sembunyikan Tabel</span>
                    </button>
                </div>
            </div>
            <div class="card-body" id="tableContainer">
                <div class="table-responsive">
                    <table id="basic-datatables" class="display table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Jenis Dokumen</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-center">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // variabel untuk nomor urut tabel
                            $no = 1;
                            $total_arsip = 0;
                            
                            // hitung total arsip terlebih dahulu
                            $total_query = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM tbl_arsip")
                                                               or die('Ada kesalahan pada query total arsip : ' . mysqli_error($mysqli));
                            $total_data = mysqli_fetch_assoc($total_query);
                            $total_arsip = $total_data['total'];
                            
                            // sql statement untuk menampilkan jumlah data dari tabel "tbl_arsip" dan tabel "tbl_jenis", dikelompokan berdasarkan "jenis_dokumen"
                            $query = mysqli_query($mysqli, "SELECT COUNT(*) as jumlah, b.nama_jenis 
                                                            FROM tbl_arsip as a INNER JOIN tbl_jenis as b ON a.jenis_dokumen=b.id_jenis 
                                                            GROUP BY a.jenis_dokumen ORDER BY jumlah DESC")
                                                            or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
                            
                            // simpan data untuk chart
                            $chart_labels = [];
                            $chart_data = [];
                            $chart_colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF', '#4BC0C0'];
                            
                            // ambil data hasil query
                            while ($data = mysqli_fetch_assoc($query)) {
                                $persentase = $total_arsip > 0 ? round(($data['jumlah'] / $total_arsip) * 100, 1) : 0;
                                
                                // simpan untuk chart
                                $chart_labels[] = $data['nama_jenis'];
                                $chart_data[] = $data['jumlah'];
                                ?>
                                <!-- tampilkan data -->
                                <tr>
                                    <td width="50" class="text-center"><?php echo $no++; ?></td>
                                    <td width="200"><?php echo $data['nama_jenis']; ?></td>
                                    <td width="80" class="text-center"><?php echo $data['jumlah']; ?></td>
                                    <td width="100" class="text-center"><?php echo $persentase; ?>%</td>
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
        // Data untuk chart Target vs Realisasi
        const divisiNames = <?php echo json_encode($divisi_names); ?>;
        const targetData = <?php echo json_encode($target_data); ?>;
        const realisasiData = <?php echo json_encode($realisasi_data); ?>;

        // Chart Target vs Realisasi
        const targetCtx = document.getElementById('targetRealisasiChart').getContext('2d');
        const targetRealisasiChart = new Chart(targetCtx, {
            type: 'bar',
            data: {
                labels: divisiNames,
                datasets: [{
                    label: 'Target',
                    data: targetData,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Realisasi',
                    data: realisasiData,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
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
                            callback: function(value, index, values) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Data untuk chart dari PHP
        const chartLabels = <?php echo json_encode($chart_labels); ?>;
        const chartData = <?php echo json_encode($chart_data); ?>;
        const chartColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF', '#4BC0C0'];

        // Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        const barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Jumlah Arsip',
                    data: chartData,
                    backgroundColor: chartColors.slice(0, chartLabels.length),
                    borderColor: chartColors.slice(0, chartLabels.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' dokumen';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
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

        // Pie Chart
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        const pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: chartColors.slice(0, chartLabels.length),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' dokumen (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Function untuk toggle table visibility
        function toggleTable() {
            const tableContainer = document.getElementById('tableContainer');
            const toggleIcon = document.getElementById('toggleIcon');
            const toggleText = document.getElementById('toggleText');
            
            if (tableContainer.style.display === 'none') {
                tableContainer.style.display = 'block';
                toggleIcon.className = 'fas fa-eye';
                toggleText.textContent = 'Sembunyikan Tabel';
            } else {
                tableContainer.style.display = 'none';
                toggleIcon.className = 'fas fa-eye-slash';
                toggleText.textContent = 'Tampilkan Tabel';
            }
        }

        // Function untuk toggle target table visibility
        function toggleTargetTable() {
            const targetTableContainer = document.getElementById('targetTableContainer');
            const toggleTargetIcon = document.getElementById('toggleTargetIcon');
            const toggleTargetText = document.getElementById('toggleTargetText');
            
            if (targetTableContainer.style.display === 'none') {
                targetTableContainer.style.display = 'block';
                toggleTargetIcon.className = 'fas fa-eye';
                toggleTargetText.textContent = 'Sembunyikan Tabel';
            } else {
                targetTableContainer.style.display = 'none';
                toggleTargetIcon.className = 'fas fa-eye-slash';
                toggleTargetText.textContent = 'Tampilkan Tabel';
            }
        }
    </script>

<?php } ?>