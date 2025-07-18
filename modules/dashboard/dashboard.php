<?php
// Cek status login pengguna
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('location: ../../login.php?pesan=2');
    exit();
} else {
    // Panggil file koneksi database
    require_once 'config/database.php';

    // Ambil tahun yang dipilih dari filter, default 'all'
    $selected_year = isset($_GET['tahun']) && is_numeric($_GET['tahun']) ? $_GET['tahun'] : 'all';

    // Fungsi untuk membuat klausa WHERE untuk filter tahun
    function getYearWhereClause($year, $date_column = 'tgl_surat')
    {
        if ($year !== 'all') {
            return "WHERE YEAR($date_column) = " . (int) $year;
        }
        return "";
    }

    // Fungsi untuk mengambil ringkasan data
    function getSummaryData($mysqli, $tableName, $year)
    {
        $where_clause = getYearWhereClause($year);
        $sql = "SELECT
                    SUM(target_nominal) as total_target,
                    SUM(realisasi_nominal) as total_realisasi,
                    SUM(kontrak_nominal) as total_kontrak,
                    SUM(ongoing_nominal) as total_ongoing,
                    CASE
                        WHEN SUM(target_nominal) > 0 THEN ROUND((SUM(realisasi_nominal) / SUM(target_nominal)) * 100, 2)
                        ELSE 0
                    END as overall_progress
                FROM $tableName
                $where_clause";
        $result = mysqli_query($mysqli, $sql);
        return mysqli_fetch_assoc($result);
    }

    // Fungsi untuk data aktual bulanan
    function getMonthlyActualData($mysqli, $tableName, $year)
    {
        $where_clause = getYearWhereClause($year);
        $query = mysqli_query($mysqli, "SELECT * FROM $tableName $where_clause ORDER BY tgl_surat ASC");
        
        $months_data = [];
        while ($data = mysqli_fetch_assoc($query)) {
            $bulan = date('Y-m', strtotime($data['tgl_surat']));
            $bulan_nama = date('M Y', strtotime($data['tgl_surat']));
            if (!isset($months_data[$bulan])) {
                $months_data[$bulan] = ['label' => $bulan_nama, 'realisasi' => 0, 'kontrak' => 0, 'ongoing' => 0];
            }
            $months_data[$bulan]['realisasi'] += $data['realisasi_nominal'];
            $months_data[$bulan]['kontrak'] += $data['kontrak_nominal'];
            $months_data[$bulan]['ongoing'] += $data['ongoing_nominal'];
        }
        ksort($months_data);
        return $months_data;
    }

    // Fungsi untuk data kumulatif bulanan
    function getMonthlyCumulativeData($mysqli, $tableName, $year)
    {
        $where_clause = getYearWhereClause($year);
        $query_all = mysqli_query($mysqli, "SELECT * FROM $tableName $where_clause ORDER BY tgl_surat ASC");

        // Dapatkan total target untuk tahun yang dipilih
        $yearly_target_query = mysqli_query($mysqli, "SELECT SUM(target_nominal) as total_target FROM $tableName $where_clause");
        $yearly_target_result = mysqli_fetch_assoc($yearly_target_query);
        $yearly_target = $yearly_target_result['total_target'] ?? 0;
        
        $monthly_target = $yearly_target > 0 ? $yearly_target / 12 : 0;
        
        $months_data = [];
        while ($data = mysqli_fetch_assoc($query_all)) {
            $bulan = date('Y-m', strtotime($data['tgl_surat']));
            $bulan_nama = date('M Y', strtotime($data['tgl_surat']));
            if (!isset($months_data[$bulan])) {
                $months_data[$bulan] = ['label' => $bulan_nama, 'realisasi' => 0, 'kontrak' => 0, 'ongoing' => 0];
            }
            $months_data[$bulan]['realisasi'] += $data['realisasi_nominal'];
            $months_data[$bulan]['kontrak'] += $data['kontrak_nominal'];
            $months_data[$bulan]['ongoing'] += $data['ongoing_nominal'];
        }

        ksort($months_data);
        $monthly_accumulative = [];
        $cumulative_target = 0;
        $cumulative_realisasi = 0;
        $cumulative_terkontrak = 0;
        $cumulative_ongoing = 0;
        foreach ($months_data as $month => $data) {
            $cumulative_target += $monthly_target;
            $cumulative_realisasi += $data['realisasi'];
            $cumulative_terkontrak += ($data['realisasi'] + $data['kontrak']);
            $cumulative_ongoing += ($data['realisasi'] + $data['kontrak'] + $data['ongoing']);
            $monthly_accumulative[$month] = ['label' => $data['label'], 'target' => $cumulative_target, 'realisasi' => $cumulative_realisasi, 'terkontrak' => $cumulative_terkontrak, 'ongoing' => $cumulative_ongoing];
        }
        return $monthly_accumulative;
    }
    
    // Fungsi khusus untuk BUIB dengan target rata-rata
    function getBuibMonthlyData($mysqli, $tableName, $year) {
        $where_clause = getYearWhereClause($year);
        $query = mysqli_query($mysqli, "SELECT * FROM $tableName $where_clause ORDER BY tgl_surat ASC");
        
        $yearly_target_query = mysqli_query($mysqli, "SELECT SUM(target_nominal) as total_target FROM $tableName $where_clause");
        $yearly_target_result = mysqli_fetch_assoc($yearly_target_query);
        $yearly_target = $yearly_target_result['total_target'] ?? 0;
        
        $monthly_target = $yearly_target > 0 ? $yearly_target / 12 : 0;

        $months_data = [];
        while ($data = mysqli_fetch_assoc($query)) {
            $bulan = date('Y-m', strtotime($data['tgl_surat']));
            $bulan_nama = date('M Y', strtotime($data['tgl_surat']));
            if (!isset($months_data[$bulan])) {
                $months_data[$bulan] = ['label' => $bulan_nama, 'realisasi' => 0, 'kontrak' => 0, 'ongoing' => 0, 'target' => $monthly_target];
            }
            $months_data[$bulan]['realisasi'] += $data['realisasi_nominal'];
            $months_data[$bulan]['kontrak'] += $data['kontrak_nominal'];
            $months_data[$bulan]['ongoing'] += $data['ongoing_nominal'];
        }
        ksort($months_data);
        return $months_data;
    }


    // Fungsi untuk mengambil data realisasi per kategori/entity/deputy
    function getRealisasiPerKategori($mysqli, $tableName, $kategoriColumn, $joinTable, $joinColumn, $namaKategoriColumn, $year)
    {
        $where_clause = ($year !== 'all') ? "WHERE YEAR(a.tgl_surat) = " . (int) $year : "";
        $sql = "SELECT
                    b.$namaKategoriColumn as kategori,
                    SUM(a.realisasi_nominal) as total_realisasi
                FROM $tableName as a
                INNER JOIN $joinTable as b ON a.$kategoriColumn = b.$joinColumn
                $where_clause
                GROUP BY b.$namaKategoriColumn
                ORDER BY total_realisasi DESC";
        $result = mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // Ambil data berdasarkan filter tahun
    $lemtera_summary = getSummaryData($mysqli, 'tbl_rk_lemtera', $selected_year);
    $training_center_summary = getSummaryData($mysqli, 'tbl_rk_training_center', $selected_year);
    $buib_summary = getSummaryData($mysqli, 'tbl_rk_buib', $selected_year);

    $lemtera_monthly_cumulative = getMonthlyCumulativeData($mysqli, 'tbl_rk_lemtera', $selected_year);
    $training_center_monthly_cumulative = getMonthlyCumulativeData($mysqli, 'tbl_rk_training_center', $selected_year);
    
    $lemtera_monthly_actual = getMonthlyActualData($mysqli, 'tbl_rk_lemtera', $selected_year);
    $training_center_monthly_actual = getMonthlyActualData($mysqli, 'tbl_rk_training_center', $selected_year);
    $buib_monthly_actual = getBuibMonthlyData($mysqli, 'tbl_rk_buib', $selected_year);

    $lemtera_kategori = getRealisasiPerKategori($mysqli, 'tbl_rk_lemtera', 'entity_lemtera', 'tbl_entity_lemtera', 'id_entity', 'nama_entity_lemtera', $selected_year);
    $training_center_kategori = getRealisasiPerKategori($mysqli, 'tbl_rk_training_center', 'kategori_tc', 'tbl_kategori', 'id_kategori', 'nama_kategori', $selected_year);
    $buib_kategori = getRealisasiPerKategori($mysqli, 'tbl_rk_buib', 'deputy_buib', 'tbl_deputy_buib', 'id_deputy', 'nama_deputy', $selected_year);

    function formatNumber($number)
    {
        if ($number > 0) {
            if ($number >= 1000000)
                return number_format($number / 1000000, 1) . 'M';
            return number_format($number, 0, ',', '.');
        }
        return 'Rp 0';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>

<body>

    <div class="panel-header">
        <div class="page-inner py-45">
            <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
                <div class="page-header">
                    <h4 class="page-title"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</h4>
                    <ul class="breadcrumbs">
                        <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a>Dashboard</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="">
                            <input type="hidden" name="module" value="dashboard">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label for="filterTahun">Filter Tahun:</label>
                                    <select name="tahun" id="filterTahun" class="form-control">
                                        <option value="all">Semua Tahun</option>
                                        <?php
                                        // Query untuk mendapatkan semua tahun unik dari semua tabel
                                        $query_tahun = mysqli_query($mysqli, "(SELECT DISTINCT YEAR(tgl_surat) as tahun FROM tbl_rk_lemtera) UNION (SELECT DISTINCT YEAR(tgl_surat) as tahun FROM tbl_rk_training_center) UNION (SELECT DISTINCT YEAR(tgl_surat) as tahun FROM tbl_rk_buib) ORDER BY tahun DESC");
                                        while ($data_tahun = mysqli_fetch_assoc($query_tahun)) {
                                            $tahun = $data_tahun['tahun'];
                                            if ($tahun) {
                                                $selected = ($selected_year == $tahun) ? 'selected' : '';
                                                echo "<option value='{$tahun}' {$selected}>{$tahun}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-leaf"></i></div>
                            </div>
                            <div class="col col-stats ml-3 ml-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Lembaga Terapan</p>
                                    <h4 class="card-title"><?php echo $lemtera_summary['overall_progress']; ?>%</h4>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-4 text-center border-right"><small class="text-muted">Realisasi</small>
                                <h6 class="font-weight-bold">
                                    <?php echo formatNumber($lemtera_summary['total_realisasi']); ?></h6>
                            </div>
                            <div class="col-4 text-center border-right"><small class="text-muted">Kontrak</small>
                                <h6 class="font-weight-bold">
                                    <?php echo formatNumber($lemtera_summary['total_kontrak']); ?></h6>
                            </div>
                            <div class="col-4 text-center"><small class="text-muted">Ongoing</small>
                                <h6 class="font-weight-bold">
                                    <?php echo formatNumber($lemtera_summary['total_ongoing']); ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-chalkboard-teacher"></i></div>
                            </div>
                            <div class="col col-stats ml-3 ml-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Training Center</p>
                                    <h4 class="card-title"><?php echo $training_center_summary['overall_progress']; ?>%
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-4 text-center border-right"><small class="text-muted">Realisasi</small>
                                <h6 class="font-weight-bold">
                                    <?php echo formatNumber($training_center_summary['total_realisasi']); ?></h6>
                            </div>
                            <div class="col-4 text-center border-right"><small class="text-muted">Kontrak</small>
                                <h6 class="font-weight-bold">
                                    <?php echo formatNumber($training_center_summary['total_kontrak']); ?></h6>
                            </div>
                            <div class="col-4 text-center"><small class="text-muted">Ongoing</small>
                                <h6 class="font-weight-bold">
                                    <?php echo formatNumber($training_center_summary['total_ongoing']); ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-university"></i></div>
                            </div>
                            <div class="col col-stats ml-3 ml-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Badan Usaha Inkubasi Bisnis</p>
                                    <h4 class="card-title"><?php echo $buib_summary['overall_progress']; ?>%</h4>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-4 text-center border-right"><small class="text-muted">Realisasi</small>
                                <h6 class="font-weight-bold">
                                    <?php echo formatNumber($buib_summary['total_realisasi']); ?></h6>
                            </div>
                            <div class="col-4 text-center border-right"><small class="text-muted">Kontrak</small>
                                <h6 class="font-weight-bold">
                                    <?php echo formatNumber($buib_summary['total_kontrak']); ?></h6>
                            </div>
                            <div class="col-4 text-center"><small class="text-muted">Ongoing</small>
                                <h6 class="font-weight-bold">
                                    <?php echo formatNumber($buib_summary['total_ongoing']); ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title text-center"><h3 class="card-title text-success"><i class="fas fa-leaf mr-2"></i>Lembaga Terapan (Lemtera)</h3></div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="lemteraLineChart" style="height: 300px;"></canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="lemteraDoughnutChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-8">
                                <canvas id="lemteraBarChart" style="height: 300px;"></canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="lemteraPieChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="text-center"><h3 class="card-title text-primary"><i class="fas fa-chalkboard-teacher mr-2"></i>Training Center</h3></div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="trainingCenterLineChart" style="height: 300px;"></canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="trainingCenterDoughnutChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-8">
                                <canvas id="trainingCenterBarChart" style="height: 300px;"></canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="trainingCenterPieChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="text-center"><h3 class="card-title text-warning"><i class="fas fa-university mr-2"></i>Badan Usaha & Inkubasi Bisnis (BUIB)</h3></div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="buibLineChart" style="height: 300px;"></canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="buibDoughnutChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-8">
                                <canvas id="buibBarChart" style="height: 300px;"></canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="buibPieChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="text-center"><h3 class="card-title text-danger"><i class="fas fa-handshake mr-2"></i>Bagian Kerja Sama</h3></div>
                    </div>
                    <div class="card-body">
                        </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="text-center"><h3 class="card-title text-info"><i class="fas fa-globe mr-2"></i>Bagian Kerja Sama Internasional</h3></div>
                    </div>
                    <div class="card-body">
                         </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Data from PHP
            const lemteraMonthlyCumulative = <?php echo json_encode($lemtera_monthly_cumulative); ?>;
            const trainingCenterMonthlyCumulative = <?php echo json_encode($training_center_monthly_cumulative); ?>;
            const buibMonthlyActual = <?php echo json_encode($buib_monthly_actual); ?>;
            
            const lemteraMonthlyActual = <?php echo json_encode($lemtera_monthly_actual); ?>;
            const trainingCenterMonthlyActual = <?php echo json_encode($training_center_monthly_actual); ?>;

            const lemteraSummary = <?php echo json_encode($lemtera_summary); ?>;
            const trainingCenterSummary = <?php echo json_encode($training_center_summary); ?>;
            const buibSummary = <?php echo json_encode($buib_summary); ?>;

            const lemteraKategori = <?php echo json_encode($lemtera_kategori); ?>;
            const trainingCenterKategori = <?php echo json_encode($training_center_kategori); ?>;
            const buibKategori = <?php echo json_encode($buib_kategori); ?>;

            // Render Line Chart (bisa kumulatif atau aktual)
            function renderLineChart(canvasId, chartData, chartTitle, isCumulative = true) {
                const ctx = document.getElementById(canvasId).getContext('2d');
                const labels = Object.values(chartData).map(item => item.label);
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Target',
                                data: Object.values(chartData).map(item => item.target || 0),
                                borderColor: '#FF6384',
                                tension: 0.3,
                                fill: false
                            },
                            {
                                label: 'Realisasi' + (isCumulative ? ' Kumulatif' : ''),
                                data: Object.values(chartData).map(item => item.realisasi || 0),
                                borderColor: '#36A2EB',
                                tension: 0.3,
                                fill: false
                            },
                            {
                                label: 'Kontrak' + (isCumulative ? ' Kumulatif' : ''),
                                data: isCumulative ? Object.values(chartData).map(item => item.terkontrak || 0) : Object.values(chartData).map(item => item.kontrak || 0),
                                borderColor: '#FFCE56',
                                tension: 0.3,
                                fill: false
                            },
                            {
                                label: 'Ongoing' + (isCumulative ? ' Kumulatif' : ''),
                                data: isCumulative ? Object.values(chartData).map(item => item.ongoing || 0) : Object.values(chartData).map(item => item.ongoing || 0),
                                borderColor: '#4BC0C0',
                                tension: 0.3,
                                fill: false
                            }
                        ]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            title: {
                                display: true,
                                text: chartTitle,
                                font: { size: 16 }
                            },
                            legend: { 
                                display: true, 
                                position: 'top' 
                            } 
                        } 
                    }
                });
            }

            // Render Doughnut Chart (selalu berdasarkan total summary)
            function renderDoughnutChart(canvasId, summaryData, chartTitle) {
                const ctx = document.getElementById(canvasId).getContext('2d');
                const sisaTarget = summaryData.total_target - (parseFloat(summaryData.total_realisasi) + parseFloat(summaryData.total_kontrak) + parseFloat(summaryData.total_ongoing));
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Realisasi', 'Kontrak', 'Ongoing', 'Sisa Target'],
                        datasets: [{
                            data: [summaryData.total_realisasi, summaryData.total_kontrak, summaryData.total_ongoing, sisaTarget > 0 ? sisaTarget : 0],
                            backgroundColor: ['#4BC0C0', '#36A2EB', '#FFCE56', '#FF6384'],
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            title: {
                                display: true,
                                text: chartTitle,
                                font: { size: 16 }
                            },
                            legend: { 
                                display: true, 
                                position: 'bottom' 
                            } 
                        } 
                    }
                });
            }

            // Render Bar Chart (selalu data aktual bulanan)
            function renderBarChart(canvasId, monthlyData, chartTitle) {
                const ctx = document.getElementById(canvasId).getContext('2d');
                const labels = Object.values(monthlyData).map(item => item.label);
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Realisasi per Bulan',
                            data: Object.values(monthlyData).map(item => item.realisasi || 0),
                            backgroundColor: '#36A2EB'
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            title: {
                                display: true,
                                text: chartTitle,
                                font: { size: 16 }
                            },
                            legend: { 
                                display: false 
                            } 
                        } 
                    }
                });
            }

            // Render Pie Chart (berdasarkan kategori)
            function renderPieChart(canvasId, kategoriData, chartTitle) {
                const ctx = document.getElementById(canvasId).getContext('2d');
                const labels = kategoriData.map(item => item.kategori);
                const data = kategoriData.map(item => item.total_realisasi);
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#E7E9ED', '#A6C561', '#D98880', '#F1C40F', '#7D3C98', '#16A085'],
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            title: {
                                display: true,
                                text: chartTitle,
                                font: { size: 16 }
                            },
                            legend: { 
                                display: true, 
                                position: 'bottom' 
                            } 
                        } 
                    }
                });
            }


            // Initialize all charts
            // Lemtera: Line (Kumulatif), lainnya (Aktual)
            renderLineChart('lemteraLineChart', lemteraMonthlyCumulative, 'Grafik Kumulatif Pendapatan', true);
            renderDoughnutChart('lemteraDoughnutChart', lemteraSummary, 'Komposisi Pendapatan');
            renderBarChart('lemteraBarChart', lemteraMonthlyActual, 'Realisasi Aktual per Bulan');
            renderPieChart('lemteraPieChart', lemteraKategori, 'Realisasi per Entitas');

            // Training Center: Line (Kumulatif), lainnya (Aktual)
            renderLineChart('trainingCenterLineChart', trainingCenterMonthlyCumulative, 'Grafik Kumulatif Pendapatan', true);
            renderDoughnutChart('trainingCenterDoughnutChart', trainingCenterSummary, 'Komposisi Pendapatan');
            renderBarChart('trainingCenterBarChart', trainingCenterMonthlyActual, 'Realisasi Aktual per Bulan');
            renderPieChart('trainingCenterPieChart', trainingCenterKategori, 'Realisasi per Kategori');

            // BUIB: Semua (Aktual dengan target rata-rata)
            renderLineChart('buibLineChart', buibMonthlyActual, 'Grafik Aktual Pendapatan', false);
            renderDoughnutChart('buibDoughnutChart', buibSummary, 'Komposisi Pendapatan');
            renderBarChart('buibBarChart', buibMonthlyActual, 'Realisasi Aktual per Bulan');
            renderPieChart('buibPieChart', buibKategori, 'Realisasi per Deputy');

        });
    </script>