<?php
// Cek status login pengguna
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('location: ../../login.php?pesan=2');
    exit();
} else {
    // Panggil file koneksi database
    require_once 'config/database.php';

    // Ambil tahun yang dipilih dari filter, default tahun sekarang
    $selected_year = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

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

    // Data untuk BKI
    $query_mou_negara = mysqli_query($mysqli, "SELECT b.negara, COUNT(a.id) as jumlah_mou FROM tbl_mou as a LEFT JOIN tbl_mitra_bki as b ON a.mitra_id=b.id GROUP BY b.negara ORDER BY jumlah_mou DESC");
    $data_mou_negara = [];
    while ($row = mysqli_fetch_assoc($query_mou_negara)) {
        $data_mou_negara[] = $row;
    }

    $year_filter_bki = ($selected_year == 'all') ? "" : "WHERE YEAR(tanggal) = " . (int)$selected_year;
    $query_dokumen_bulan = mysqli_query($mysqli, "
        SELECT DATE_FORMAT(tanggal, '%Y-%m') as bulan,
               SUM(CASE WHEN tipe = 'MoU' THEN 1 ELSE 0 END) as jumlah_mou,
               SUM(CASE WHEN tipe = 'PKS' THEN 1 ELSE 0 END) as jumlah_pks
        FROM (
            SELECT tanggal_penandatanganan as tanggal, 'MoU' as tipe FROM tbl_mou
            UNION ALL
            SELECT tanggal_penandatanganan as tanggal, 'PKS' as tipe FROM tbl_pks
        ) as combined
        $year_filter_bki
        GROUP BY bulan
        ORDER BY bulan ASC
    ");
    $data_dokumen_bulan = [];
    while($row = mysqli_fetch_assoc($query_dokumen_bulan)){
        $data_dokumen_bulan[] = $row;
    }

    $query_mitra_bki = mysqli_query($mysqli, "
        SELECT m.nama_mitra,
               (COUNT(DISTINCT mou.id) + COUNT(DISTINCT pks.id)) as jumlah_dokumen
        FROM tbl_mitra_bki m
        LEFT JOIN tbl_mou mou ON m.id = mou.mitra_id
        LEFT JOIN tbl_pks pks ON m.id = pks.mitra_id
        GROUP BY m.nama_mitra
        HAVING jumlah_dokumen > 0
        ORDER BY jumlah_dokumen DESC
    ");
    $data_mitra_bki = [];
    while($row = mysqli_fetch_assoc($query_mitra_bki)){
        $data_mitra_bki[] = $row;
    }
    
    // =================================================================
    // KODE UNTUK DATA BKS (BAGIAN KERJA SAMA)
    // =================================================================
    $year_filter_bks_main = ($selected_year == 'all') ? '' : 'WHERE YEAR(tanggal_awal) = ' . (int)$selected_year;

    // 1. Chart Klasifikasi Mitra
    $query_bks_klasifikasi = mysqli_query($mysqli, "
        SELECT klasifikasi_mitra, COUNT(*) as jumlah FROM (
            SELECT klasifikasi_mitra FROM tbl_mou_bks $year_filter_bks_main
            UNION ALL
            SELECT klasifikasi_mitra FROM tbl_pks_bks $year_filter_bks_main
            UNION ALL
            SELECT klasifikasi_mitra FROM tbl_i_a $year_filter_bks_main
        ) as combined_docs GROUP BY klasifikasi_mitra
    ");
    $data_bks_klasifikasi = [];
    while($row = mysqli_fetch_assoc($query_bks_klasifikasi)){ $data_bks_klasifikasi[] = $row; }

    // 2. Chart Jumlah Dokumen (MoU, PKS, IA)
    $query_bks_jml_dokumen = mysqli_query($mysqli, "
        SELECT 'MoU' as tipe, COUNT(*) as jumlah FROM tbl_mou_bks $year_filter_bks_main
        UNION ALL
        SELECT 'PKS' as tipe, COUNT(*) as jumlah FROM tbl_pks_bks $year_filter_bks_main
        UNION ALL
        SELECT 'IA' as tipe, COUNT(*) as jumlah FROM tbl_i_a $year_filter_bks_main
    ");
    $data_bks_jml_dokumen = [];
    while($row = mysqli_fetch_assoc($query_bks_jml_dokumen)){ $data_bks_jml_dokumen[] = $row; }

    // 3. Chart Bentuk Kerjasama (Berdasarkan MoU, PKS, IA)
    $query_bks_bentuk = mysqli_query($mysqli, "
        SELECT bentuk_kerjasama_bks, COUNT(*) as jumlah FROM (
            SELECT bentuk_kerjasama_bks FROM tbl_mou_bks $year_filter_bks_main
            UNION ALL
            SELECT bentuk_kerjasama_bks FROM tbl_pks_bks $year_filter_bks_main
            UNION ALL
            SELECT bentuk_kerjasama_bks FROM tbl_i_a $year_filter_bks_main
        ) as combined_docs GROUP BY bentuk_kerjasama_bks
    ");
    $data_bks_bentuk = [];
    while($row = mysqli_fetch_assoc($query_bks_bentuk)){ $data_bks_bentuk[] = $row; }

    // 4. Chart Jumlah Dokumen (MoU, PKS, IA) per Bulan
    $year_filter_bks_main = ($selected_year == 'all') ? '' : 'WHERE YEAR(tanggal_awal) = ' . (int)$selected_year;
    
    $query_bks_bulanan = mysqli_query($mysqli, "
        SELECT 
            DATE_FORMAT(tanggal_awal, '%Y-%m') as bulan,
            SUM(CASE WHEN tipe = 'MoU' THEN 1 ELSE 0 END) as jumlah_mou,
            SUM(CASE WHEN tipe = 'PKS' THEN 1 ELSE 0 END) as jumlah_pks,
            SUM(CASE WHEN tipe = 'IA' THEN 1 ELSE 0 END) as jumlah_ia
        FROM (
            SELECT tanggal_awal, 'MoU' as tipe FROM tbl_mou_bks
            UNION ALL
            SELECT tanggal_awal, 'PKS' as tipe FROM tbl_pks_bks
            UNION ALL
            SELECT tanggal_awal, 'IA' as tipe FROM tbl_i_a
        ) as combined_docs
        $year_filter_bks_main
        GROUP BY bulan
        ORDER BY bulan ASC
    ");
    
    $data_bks_bulanan = [];
    while($row = mysqli_fetch_assoc($query_bks_bulanan)){
        $data_bks_bulanan[] = $row;
    }
    
    // 5. Chart Kategori Jangka Waktu
    $query_bks_jangka_waktu = mysqli_query($mysqli, "
        SELECT CASE 
            WHEN DATEDIFF(tanggal_akhir, tanggal_awal) <= 365 THEN 'Jangka Pendek (≤1 tahun)'
            WHEN DATEDIFF(tanggal_akhir, tanggal_awal) <= 1095 THEN 'Jangka Menengah (1-3 tahun)'
            ELSE 'Jangka Panjang (>3 tahun)'
        END as kategori_waktu, COUNT(*) as jumlah FROM (
            SELECT tanggal_awal, tanggal_akhir FROM tbl_mou_bks $year_filter_bks_main
            UNION ALL
            SELECT tanggal_awal, tanggal_akhir FROM tbl_pks_bks $year_filter_bks_main
            UNION ALL
            SELECT tanggal_awal, tanggal_akhir FROM tbl_i_a $year_filter_bks_main
        ) as combined_docs WHERE tanggal_awal IS NOT NULL AND tanggal_akhir IS NOT NULL
        GROUP BY kategori_waktu
    ");
    $data_bks_jangka_waktu = [];
    while($row = mysqli_fetch_assoc($query_bks_jangka_waktu)){ $data_bks_jangka_waktu[] = $row; }
    
    // 6. Query untuk menghitung total mitra unik BKS
    $query_bks_total_mitra = mysqli_query($mysqli, "
        SELECT COUNT(DISTINCT mitra_id) as total FROM (
            SELECT mitra_id FROM tbl_mou_bks $year_filter_bks_main
            UNION
            SELECT mitra_id FROM tbl_pks_bks $year_filter_bks_main
            UNION
            SELECT mitra_id FROM tbl_i_a $year_filter_bks_main
        ) as combined_mitra
    ");
    $data_bks_total_mitra = mysqli_fetch_assoc($query_bks_total_mitra);

    // =================================================================
    // REVISI: KODE UNTUK DATA GABUNGAN (LEMTERA, TC, BUIB)
    // =================================================================

    // 1. Data untuk Pie Chart Komposisi
    $data_gabungan_komposisi = [
        ['kategori' => 'Lembaga Terapan', 'total_realisasi' => $lemtera_summary['total_realisasi'] ?? 0],
        ['kategori' => 'Training Center', 'total_realisasi' => $training_center_summary['total_realisasi'] ?? 0],
        ['kategori' => 'BUIB', 'total_realisasi' => $buib_summary['total_realisasi'] ?? 0],
    ];

    // 2. Data untuk Line Chart Perbandingan Aktual per Bagian
    $all_months = array_keys($lemtera_monthly_actual) + array_keys($training_center_monthly_actual) + array_keys($buib_monthly_actual);
    sort($all_months);
    $unique_months = array_unique($all_months);

    $labels_perbandingan = [];
    $data_lemtera = [];
    $data_tc = [];
    $data_buib = [];

    foreach ($unique_months as $month) {
        $labels_perbandingan[] = date('M Y', strtotime($month . '-01'));
        $data_lemtera[] = $lemtera_monthly_actual[$month]['realisasi'] ?? 0;
        $data_tc[] = $training_center_monthly_actual[$month]['realisasi'] ?? 0;
        $data_buib[] = $buib_monthly_actual[$month]['realisasi'] ?? 0;
    }

    $data_perbandingan_aktual = [
        'labels' => $labels_perbandingan,
        'datasets' => [
            ['label' => 'Realisasi Lemtera', 'data' => $data_lemtera, 'borderColor' => '#4BC0C0', 'tension' => 0.3, 'fill' => false],
            ['label' => 'Realisasi TC', 'data' => $data_tc, 'borderColor' => '#36A2EB', 'tension' => 0.3, 'fill' => false],
            ['label' => 'Realisasi BUIB', 'data' => $data_buib, 'borderColor' => '#FFCE56', 'tension' => 0.3, 'fill' => false],
        ]
    ];

    // 3. Data untuk Bar Chart Target vs Pencapaian per Bagian (Revisi Stacked)
    $data_target_vs_pencapaian = [
        'labels' => ['Lembaga Terapan', 'Training Center', 'BUIB'],
        'datasets' => [
            [
                'label' => 'Target',
                'data' => [
                    $lemtera_summary['total_target'] ?? 0,
                    $training_center_summary['total_target'] ?? 0,
                    $buib_summary['total_target'] ?? 0
                ],
                'backgroundColor' => '#FF6384', // Warna Merah untuk Target (konsisten)
                'stack' => 'Target' // Diberi stack terpisah agar tidak menumpuk dengan yang lain
            ],
            [
                'label' => 'Realisasi',
                'data' => [
                    $lemtera_summary['total_realisasi'] ?? 0,
                    $training_center_summary['total_realisasi'] ?? 0,
                    $buib_summary['total_realisasi'] ?? 0
                ],
                'backgroundColor' => '#4BC0C0', // Warna Hijau/Teal untuk Realisasi (konsisten)
                'stack' => 'Pencapaian' // Kunci agar menumpuk dengan Kontrak & Ongoing
            ],
            [
                'label' => 'Kontrak',
                'data' => [
                    $lemtera_summary['total_kontrak'] ?? 0,
                    $training_center_summary['total_kontrak'] ?? 0,
                    $buib_summary['total_kontrak'] ?? 0
                ],
                'backgroundColor' => '#36A2EB', // Warna Biru untuk Kontrak (konsisten)
                'stack' => 'Pencapaian' // Kunci agar menumpuk dengan Realisasi & Ongoing
            ],
            [
                'label' => 'Ongoing',
                'data' => [
                    $lemtera_summary['total_ongoing'] ?? 0,
                    $training_center_summary['total_ongoing'] ?? 0,
                    $buib_summary['total_ongoing'] ?? 0
                ],
                'backgroundColor' => '#FFCE56', // Warna Kuning untuk Ongoing (konsisten)
                'stack' => 'Pencapaian' // Kunci agar menumpuk dengan Realisasi & Kontrak
            ]
        ]
    ];

    // Menyiapkan data untuk Summary Card BKS dan BKI
    // Proses data BKS
    $bks_summary = ['mou' => 0, 'pks' => 0, 'ia' => 0, 'mitra' => 0, 'total' => 0];
    foreach ($data_bks_jml_dokumen as $doc) {
        if ($doc['tipe'] == 'MoU') $bks_summary['mou'] = $doc['jumlah'];
        if ($doc['tipe'] == 'PKS') $bks_summary['pks'] = $doc['jumlah'];
        if ($doc['tipe'] == 'IA') $bks_summary['ia'] = $doc['jumlah'];
    }
    $bks_summary['mitra'] = $data_bks_total_mitra['total'] ?? 0;
    $bks_summary['total'] = $bks_summary['mou'] + $bks_summary['pks'] + $bks_summary['ia'];

    // Proses data BKI
    $bki_summary = ['mou' => 0, 'pks' => 0, 'mitra' => 0, 'negara' => 0, 'total' => 0];
    foreach ($data_dokumen_bulan as $data) {
        $bki_summary['mou'] += $data['jumlah_mou'];
        $bki_summary['pks'] += $data['jumlah_pks'];
    }
    $bki_summary['mitra'] = count($data_mitra_bki);
    $bki_summary['negara'] = count($data_mou_negara); // Menghitung jumlah negara
    $bki_summary['total'] = $bki_summary['mou'] + $bki_summary['pks'];

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
                                        <option value="all" <?php echo ($selected_year == 'all') ? 'selected' : ''; ?>>Semua Tahun</option>
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
            <div class="col-md-6">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-handshake"></i></div>
                            </div>
                            <div class="col col-stats ml-3 ml-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Bagian Kerja Sama (BKS)</p>
                                    <h4 class="card-title"><?php echo $bks_summary['total']; ?> Dokumen</h4>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-3 text-center border-right"><small class="text-muted">Jumlah MoU</small>
                                <h6 class="font-weight-bold"><?php echo $bks_summary['mou']; ?></h6>
                            </div>
                            <div class="col-3 text-center border-right"><small class="text-muted">Jumlah PKS</small>
                                <h6 class="font-weight-bold"><?php echo $bks_summary['pks']; ?></h6>
                            </div>
                            <div class="col-3 text-center border-right"><small class="text-muted">Jumlah IA</small>
                                <h6 class="font-weight-bold"><?php echo $bks_summary['ia']; ?></h6>
                            </div>
                            <div class="col-3 text-center"><small class="text-muted">Total Mitra</small>
                                <h6 class="font-weight-bold"><?php echo $bks_summary['mitra']; ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-globe"></i></div>
                            </div>
                            <div class="col col-stats ml-3 ml-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Bagian Kerja Sama Internasional (BKI)</p>
                                    <h4 class="card-title"><?php echo $bki_summary['total']; ?> Dokumen</h4>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-3 text-center border-right"><small class="text-muted">Jumlah MoU</small>
                                <h6 class="font-weight-bold"><?php echo $bki_summary['mou']; ?></h6>
                            </div>
                            <div class="col-3 text-center border-right"><small class="text-muted">Jumlah PKS</small>
                                <h6 class="font-weight-bold"><?php echo $bki_summary['pks']; ?></h6>
                            </div>
                            <div class="col-3 text-center border-right"><small class="text-muted">Total Mitra</small>
                                <h6 class="font-weight-bold"><?php echo $bki_summary['mitra']; ?></h6>
                            </div>
                            <div class="col-3 text-center"><small class="text-muted">Total Negara</small>
                                <h6 class="font-weight-bold"><?php echo $bki_summary['negara']; ?></h6>
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
                        <div class="card-title text-center"><h3 class="card-title text-dark"><i class="fas fa-chart-pie mr-2"></i>Grafik Gabungan (Lemtera, TC & BUIB)</h3></div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="gabunganLineChartPerbandingan" style="height: 300px;"></canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="gabunganPieChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <canvas id="gabunganBarChartTarget" style="height: 300px;"></canvas>
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
                        <div class="text-center"><h3 class="card-title text-danger"><i class="fas fa-handshake mr-2"></i>Bagian Kerja Sama (BKS)</h3></div>
                    </div>
                    <div class="card-body">
                         <div class="row">
                            <div class="col-md-6">
                                <canvas id="chartBksKlasifikasi" height="200"></canvas>
                            </div>
                            <div class="col-md-6">
                                <canvas id="chartBksJmlDokumen" height="200"></canvas>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <canvas id="chartBksBentuk" height="200"></canvas>
                            </div>
                             <div class="col-md-6">
                                <canvas id="chartBksJangkaWaktu" height="200"></canvas>
                            </div>
                        </div>
                        <div class="row mt-4">
                             <div class="col-md-12">
                                <canvas id="chartBksBulanan" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="text-center"><h3 class="card-title text-info"><i class="fas fa-globe mr-2"></i>Bagian Kerja Sama Internasional</h3></div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="chartMoUDistribusi" height="200"></canvas>
                            </div>
                            <div class="col-md-6">
                                <canvas id="chartMoUvsPKS" height="200"></canvas>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <canvas id="chartMitra" height="200"></canvas>
                            </div>
                        </div>
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

            // Data Gabungan Revisi
            const dataGabunganKomposisi = <?php echo json_encode($data_gabungan_komposisi); ?>;
            const dataPerbandinganAktual = <?php echo json_encode($data_perbandingan_aktual); ?>;
            const dataTargetVsPencapaian = <?php echo json_encode($data_target_vs_pencapaian); ?>;
            
            const dataMouNegara = <?php echo json_encode($data_mou_negara); ?>;
            const dataDokumenBulan = <?php echo json_encode($data_dokumen_bulan); ?>;
            const dataMitraBki = <?php echo json_encode($data_mitra_bki); ?>;

            // Data BKS from PHP
            const dataBksKlasifikasi = <?php echo json_encode($data_bks_klasifikasi); ?>;
            const dataBksJmlDokumen = <?php echo json_encode($data_bks_jml_dokumen); ?>;
            const dataBksBentuk = <?php echo json_encode($data_bks_bentuk); ?>;
            const dataBksBulanan = <?php echo json_encode($data_bks_bulanan); ?>;
            const dataBksJangkaWaktu = <?php echo json_encode($data_bks_jangka_waktu); ?>;
            
            // Render Line Chart
            function renderLineChart(canvasId, chartData, chartTitle, isCumulative = true) {
                const ctx = document.getElementById(canvasId).getContext('2d');
                const labels = Object.values(chartData).map(item => item.label);
                new Chart(ctx, {
                    type: 'line',
                    data: { labels: labels, datasets: [ { label: 'Target', data: Object.values(chartData).map(item => item.target || 0), borderColor: '#FF6384', tension: 0.3, fill: false }, { label: 'Realisasi' + (isCumulative ? ' Kumulatif' : ''), data: Object.values(chartData).map(item => item.realisasi || 0), borderColor: '#36A2EB', tension: 0.3, fill: false }, { label: 'Kontrak' + (isCumulative ? ' Kumulatif' : ''), data: isCumulative ? Object.values(chartData).map(item => item.terkontrak || 0) : Object.values(chartData).map(item => item.kontrak || 0), borderColor: '#FFCE56', tension: 0.3, fill: false }, { label: 'Ongoing' + (isCumulative ? ' Kumulatif' : ''), data: isCumulative ? Object.values(chartData).map(item => item.ongoing || 0) : Object.values(chartData).map(item => item.ongoing || 0), borderColor: '#4BC0C0', tension: 0.3, fill: false } ] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: chartTitle, font: { size: 16 } }, legend: { display: true, position: 'top' } } }
                });
            }

            // Render Doughnut Chart
            function renderDoughnutChart(canvasId, summaryData, chartTitle) {
                const ctx = document.getElementById(canvasId).getContext('2d');
                const sisaTarget = summaryData.total_target - (parseFloat(summaryData.total_realisasi) + parseFloat(summaryData.total_kontrak) + parseFloat(summaryData.total_ongoing));
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Realisasi', 'Kontrak', 'Ongoing', 'Sisa Target'],
                        datasets: [{ data: [summaryData.total_realisasi, summaryData.total_kontrak, summaryData.total_ongoing, sisaTarget > 0 ? sisaTarget : 0], backgroundColor: ['#4BC0C0', '#36A2EB', '#FFCE56', '#FF6384'], }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: chartTitle, font: { size: 16 } }, legend: { display: true, position: 'bottom' } } }
                });
            }

            // Render Bar Chart
            function renderBarChart(canvasId, monthlyData, chartTitle) {
                const ctx = document.getElementById(canvasId).getContext('2d');
                const labels = Object.values(monthlyData).map(item => item.label);
                new Chart(ctx, {
                    type: 'bar',
                    data: { labels: labels, datasets: [{ label: 'Realisasi per Bulan', data: Object.values(monthlyData).map(item => item.realisasi || 0), backgroundColor: '#36A2EB' }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: chartTitle, font: { size: 16 } }, legend: { display: false } } }
                });
            }

            // Render Pie Chart
            function renderPieChart(canvasId, kategoriData, chartTitle, labelKey = 'kategori', valueKey = 'total_realisasi') {
                const ctx = document.getElementById(canvasId).getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: kategoriData.map(item => item[labelKey]),
                        datasets: [{ data: kategoriData.map(item => item[valueKey]), backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#E7E9ED', '#A6C561', '#D98880', '#F1C40F', '#7D3C98', '#16A085'], }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: chartTitle, font: { size: 16 } }, legend: { display: true, position: 'bottom' } } }
                });
            }

            // Inisialisasi Chart Gabungan (Versi Revisi)
            // 1. Pie Chart Komposisi
            renderPieChart('gabunganPieChart', dataGabunganKomposisi, 'Komposisi Realisasi Gabungan', 'kategori', 'total_realisasi');

            // 2. Line Chart Perbandingan Realisasi Aktual
            new Chart(document.getElementById('gabunganLineChartPerbandingan'), {
                type: 'line',
                data: dataPerbandinganAktual,
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { title: { display: true, text: 'Perbandingan Realisasi Aktual per Bagian' } }
                }
            });

            // 3. Bar Chart Perbandingan Target vs Pencapaian
            new Chart(document.getElementById('gabunganBarChartTarget'), {
                type: 'bar',
                data: dataTargetVsPencapaian,
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { title: { display: true, text: 'Perbandingan Target vs Pencapaian per Bagian' } },
                    scales: { 
                        x: { stacked: true },
                        y: { 
                            stacked: true,
                            beginAtZero: true 
                        } 
                    }
                }
            });

            // Initialize charts for Lemtera, TC, BUIB
            renderLineChart('lemteraLineChart', lemteraMonthlyCumulative, 'Grafik Kumulatif Pendapatan', true);
            renderDoughnutChart('lemteraDoughnutChart', lemteraSummary, 'Komposisi Pendapatan');
            renderBarChart('lemteraBarChart', lemteraMonthlyActual, 'Realisasi Aktual per Bulan');
            renderPieChart('lemteraPieChart', lemteraKategori, 'Realisasi per Entitas');

            renderLineChart('trainingCenterLineChart', trainingCenterMonthlyCumulative, 'Grafik Kumulatif Pendapatan', true);
            renderDoughnutChart('trainingCenterDoughnutChart', trainingCenterSummary, 'Komposisi Pendapatan');
            renderBarChart('trainingCenterBarChart', trainingCenterMonthlyActual, 'Realisasi Aktual per Bulan');
            renderPieChart('trainingCenterPieChart', trainingCenterKategori, 'Realisasi per Kategori');

            renderLineChart('buibLineChart', buibMonthlyActual, 'Grafik Aktual Pendapatan', false);
            renderDoughnutChart('buibDoughnutChart', buibSummary, 'Komposisi Pendapatan');
            renderBarChart('buibBarChart', buibMonthlyActual, 'Realisasi Aktual per Bulan');
            renderPieChart('buibPieChart', buibKategori, 'Realisasi per Deputy');

            // Initialize charts for BKS
            renderPieChart('chartBksKlasifikasi', dataBksKlasifikasi, 'Klasifikasi Mitra', 'klasifikasi_mitra', 'jumlah');
            new Chart(document.getElementById('chartBksJmlDokumen'), { type: 'doughnut', data: { labels: dataBksJmlDokumen.map(item => item.tipe), datasets: [{ data: dataBksJmlDokumen.map(item => item.jumlah), backgroundColor: ['#1f77b4', '#ff7f0e', '#2ca02c'], }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Jumlah Dokumen (MoU, PKS, IA)' } } } });
            renderPieChart('chartBksBentuk', dataBksBentuk, 'Bentuk Kerja Sama', 'bentuk_kerjasama_bks', 'jumlah');
            renderPieChart('chartBksJangkaWaktu', dataBksJangkaWaktu, 'Kategori Jangka Waktu Kerjasama', 'kategori_waktu', 'jumlah');
            new Chart(document.getElementById('chartBksBulanan'), {
                type: 'bar',
                data: {
                    labels: dataBksBulanan.map(item => new Date(item.bulan + '-01').toLocaleString('default', { month: 'short', year: 'numeric' })),
                    datasets: [
                        {
                            label: 'MoU',
                            data: dataBksBulanan.map(item => parseInt(item.jumlah_mou)),
                            backgroundColor: '#1f77b4', // Biru
                        },
                        {
                            label: 'PKS',
                            data: dataBksBulanan.map(item => parseInt(item.jumlah_pks)),
                            backgroundColor: '#ff7f0e', // Oranye
                        },
                        {
                            label: 'IA',
                            data: dataBksBulanan.map(item => parseInt(item.jumlah_ia)),
                            backgroundColor: '#2ca02c', // Hijau
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Jumlah Dokumen (MoU, PKS, IA) per Bulan'
                        }
                    },
                    scales: {
                        x: {
                            stacked: true // Menumpuk bar di sumbu X
                        },
                        y: {
                            stacked: true, // Menumpuk bar di sumbu Y
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1 // Memastikan sumbu Y hanya menampilkan bilangan bulat
                            }
                        }
                    }
                }
            });            
            // Initialize charts for BKI
            new Chart(document.getElementById('chartMoUDistribusi'), { type: 'doughnut', data: { labels: dataMouNegara.map(item => item.negara || 'Tidak Diketahui'), datasets: [{ data: dataMouNegara.map(item => parseInt(item.jumlah_mou)), backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'], }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Distribusi MoU per Negara' } } } });
            new Chart(document.getElementById('chartMoUvsPKS'), { type: 'bar', data: { labels: dataDokumenBulan.map(item => new Date(item.bulan + '-01').toLocaleString('default', { month: 'short', year: 'numeric' })), datasets: [ { label: 'MoU', data: dataDokumenBulan.map(item => parseInt(item.jumlah_mou)), backgroundColor: '#1f77b4', }, { label: 'PKS', data: dataDokumenBulan.map(item => parseInt(item.jumlah_pks)), backgroundColor: '#ff7f0e', } ] }, options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Perbandingan MoU vs PKS per Bulan' } }, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } } });
            new Chart(document.getElementById('chartMitra'), { type: 'pie', data: { labels: dataMitraBki.map(item => item.nama_mitra), datasets: [{ label: 'Jumlah Dokumen', data: dataMitraBki.map(item => parseInt(item.jumlah_dokumen)), backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#E7E9ED', '#A6C561', '#D98880', '#F1C40F', '#7D3C98', '#16A085'], }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Jumlah Dokumen per Mitra' } } } });

        });
    </script>
</body>
</html>