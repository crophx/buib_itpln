<?php
// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('location: ../../404.html');
    exit;
}

// Start session if not already started
if (!isset($_SESSION)) {
    session_start();
}

// Debug: Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check access rights
if (!isset($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan', 'LEMTERA', 'BUIB', 'BKS', 'BKI', 'TrainingCenter', 'User'])) {
    echo "<script>alert('Akses ditolak! Hak akses: " . ($_SESSION['hak_akses'] ?? 'tidak ada') . "');</script>";
    header('location: ?module=login');
    exit;
}

// Check database connection
if (!isset($mysqli)) {
    die("Koneksi database tidak tersedia. Pastikan file koneksi sudah di-include.");
}

// First, let's check what columns exist in the table
$table_info = mysqli_query($mysqli, "DESCRIBE tbl_pengajuan");
$columns = [];
while ($row = mysqli_fetch_assoc($table_info)) {
    $columns[] = $row['Field'];
}

// Common possible column names for user ID
$possible_user_columns = ['id_pengaju', 'user_id', 'id_user', 'pengaju_id', 'created_by'];
$user_id_column = null;

foreach ($possible_user_columns as $col) {
    if (in_array($col, $columns)) {
        $user_id_column = $col;
        break;
    }
}

// If we can't find the user column, show error
if (!$user_id_column) {
    die("Error: Cannot find user ID column in tbl_pengajuan. Available columns: " . implode(', ', $columns));
}

// Ambil parameter untuk filter dan pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($mysqli, $_GET['search']) : '';
$jenis_filter = isset($_GET['jenis']) ? (int)$_GET['jenis'] : 0;
$bulan_filter = isset($_GET['bulan']) ? mysqli_real_escape_string($mysqli, $_GET['bulan']) : '';
$tahun_filter = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

// Pagination for each status
$limit = 10;
$page_menunggu = isset($_GET['page_menunggu']) ? (int)$_GET['page_menunggu'] : 1;
$page_ditolak = isset($_GET['page_ditolak']) ? (int)$_GET['page_ditolak'] : 1;
$page_disetujui = isset($_GET['page_disetujui']) ? (int)$_GET['page_disetujui'] : 1; // --- BARU ---

$offset_menunggu = ($page_menunggu - 1) * $limit;
$offset_ditolak = ($page_ditolak - 1) * $limit;
$offset_disetujui = ($page_disetujui - 1) * $limit; // --- BARU ---

// Determine user filter based on access level
$user_condition = "";
if (!in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan'])) {
    $user_id = $_SESSION['id_user'] ?? 0;
    // Logika ini sudah mencakup hak akses BUIB, BKS, BKI, dll.
    $user_condition = "AND p.$user_id_column = $user_id";
}

// Function to build query conditions
function buildWhereConditions($search, $jenis_filter, $bulan_filter, $tahun_filter, $status) {
    $conditions = [];
    if (!empty($search)) {
        $conditions[] = "(p.judul_surat LIKE '%$search%' OR p.perihal LIKE '%$search%' OR p.nomor_surat LIKE '%$search%' OR u.nama_user LIKE '%$search%')";
    }
    if ($jenis_filter > 0) {
        $conditions[] = "p.jenis_dokumen = $jenis_filter";
    }
    if (!empty($bulan_filter)) {
        // --- MODIFIKASI ---: Menggunakan tanggal_ttd untuk status 'Disetujui'
        if ($status == 'Disetujui') {
            $conditions[] = "DATE_FORMAT(p.tanggal_ttd, '%Y-%m') = '$bulan_filter'";
        } else {
            $conditions[] = "DATE_FORMAT(p.tanggal_pengajuan, '%Y-%m') = '$bulan_filter'";
        }
    }
    if ($tahun_filter > 0) {
        // --- MODIFIKASI ---: Menggunakan tanggal_ttd untuk status 'Disetujui'
        if ($status == 'Disetujui') {
            $conditions[] = "YEAR(p.tanggal_ttd) = $tahun_filter";
        } else {
            $conditions[] = "YEAR(p.tanggal_pengajuan) = $tahun_filter";
        }
    }
    return $conditions;
}

// --- Query for Menunggu status ---
$count_query_menunggu = "SELECT COUNT(*) as total FROM tbl_pengajuan p INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis INNER JOIN tbl_user u ON p.$user_id_column = u.id_user WHERE p.status_pengajuan = 'Menunggu' $user_condition";
$where_conditions_menunggu = buildWhereConditions($search, $jenis_filter, $bulan_filter, $tahun_filter, 'Menunggu');
if (!empty($where_conditions_menunggu)) {
    $count_query_menunggu .= " AND " . implode(" AND ", $where_conditions_menunggu);
}
$total_data_menunggu = mysqli_fetch_assoc(mysqli_query($mysqli, $count_query_menunggu))['total'];
$total_pages_menunggu = ceil($total_data_menunggu / $limit);

$query_menunggu = "SELECT p.*, j.nama_jenis, u.nama_user as nama_pengaju FROM tbl_pengajuan p INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis INNER JOIN tbl_user u ON p.$user_id_column = u.id_user WHERE p.status_pengajuan = 'Menunggu' $user_condition";
if (!empty($where_conditions_menunggu)) {
    $query_menunggu .= " AND " . implode(" AND ", $where_conditions_menunggu);
}
$query_menunggu .= " ORDER BY p.tanggal_pengajuan DESC LIMIT $offset_menunggu, $limit";
$result_menunggu = mysqli_query($mysqli, $query_menunggu);


// --- BARU: Query for Disetujui status ---
$count_query_disetujui = "SELECT COUNT(*) as total FROM tbl_pengajuan p INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis INNER JOIN tbl_user u ON p.$user_id_column = u.id_user WHERE p.status_pengajuan = 'Disetujui' $user_condition";
$where_conditions_disetujui = buildWhereConditions($search, $jenis_filter, $bulan_filter, $tahun_filter, 'Disetujui');
if (!empty($where_conditions_disetujui)) {
    $count_query_disetujui .= " AND " . implode(" AND ", $where_conditions_disetujui);
}
$total_data_disetujui = mysqli_fetch_assoc(mysqli_query($mysqli, $count_query_disetujui))['total'];
$total_pages_disetujui = ceil($total_data_disetujui / $limit);

$query_disetujui = "SELECT p.*, j.nama_jenis, u.nama_user as nama_pengaju FROM tbl_pengajuan p INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis INNER JOIN tbl_user u ON p.$user_id_column = u.id_user WHERE p.status_pengajuan = 'Disetujui' $user_condition";
if (!empty($where_conditions_disetujui)) {
    $query_disetujui .= " AND " . implode(" AND ", $where_conditions_disetujui);
}
$query_disetujui .= " ORDER BY p.tanggal_ttd DESC LIMIT $offset_disetujui, $limit";
$result_disetujui = mysqli_query($mysqli, $query_disetujui);


// --- Query for Ditolak status ---
$count_query_ditolak = "SELECT COUNT(*) as total FROM tbl_pengajuan p INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis INNER JOIN tbl_user u ON p.$user_id_column = u.id_user WHERE p.status_pengajuan = 'Ditolak' $user_condition";
$where_conditions_ditolak = buildWhereConditions($search, $jenis_filter, $bulan_filter, $tahun_filter, 'Ditolak');
if (!empty($where_conditions_ditolak)) {
    $count_query_ditolak .= " AND " . implode(" AND ", $where_conditions_ditolak);
}
$total_data_ditolak = mysqli_fetch_assoc(mysqli_query($mysqli, $count_query_ditolak))['total'];
$total_pages_ditolak = ceil($total_data_ditolak / $limit);

$query_ditolak = "SELECT p.*, j.nama_jenis, u.nama_user as nama_pengaju FROM tbl_pengajuan p INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis INNER JOIN tbl_user u ON p.$user_id_column = u.id_user WHERE p.status_pengajuan = 'Ditolak' $user_condition";
if (!empty($where_conditions_ditolak)) {
    $query_ditolak .= " AND " . implode(" AND ", $where_conditions_ditolak);
}
$query_ditolak .= " ORDER BY p.tanggal_pengajuan DESC LIMIT $offset_ditolak, $limit";
$result_ditolak = mysqli_query($mysqli, $query_ditolak);


// Ambil data untuk filter
$query_jenis = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis ASC");

$user_condition_filter = "";
if (!in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan'])) {
    $user_id = $_SESSION['id_user'] ?? 0;
    $user_condition_filter = "WHERE p.$user_id_column = $user_id";
}

// --- MODIFIKASI ---: Filter bulan dan tahun mengambil dari semua status
$query_bulan = mysqli_query($mysqli, "SELECT DISTINCT DATE_FORMAT(tanggal_pengajuan, '%Y-%m') as bulan FROM tbl_pengajuan p $user_condition_filter ORDER BY bulan DESC");
$query_tahun = mysqli_query($mysqli, "SELECT DISTINCT YEAR(tanggal_pengajuan) as tahun FROM tbl_pengajuan p $user_condition_filter ORDER BY tahun DESC");

// Hitung statistik
$stats_query_base = "FROM tbl_pengajuan ";
if (!in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan'])) {
    $user_id = $_SESSION['id_user'] ?? 0;
    $stats_query_base .= " WHERE $user_id_column = $user_id";
}

$stats_query = mysqli_query($mysqli, "SELECT 
    (SELECT COUNT(*) FROM tbl_pengajuan WHERE status_pengajuan = 'Disetujui' " . ($user_condition_filter ? str_replace('p.', '', $user_condition) : '') . ") as total_disetujui,
    (SELECT COUNT(*) FROM tbl_pengajuan WHERE status_pengajuan = 'Menunggu' " . ($user_condition_filter ? str_replace('p.', '', $user_condition) : '') . ") as total_menunggu,
    (SELECT COUNT(*) FROM tbl_pengajuan WHERE status_pengajuan = 'Ditolak' " . ($user_condition_filter ? str_replace('p.', '', $user_condition) : '') . ") as total_ditolak,
    (SELECT COUNT(*) FROM tbl_pengajuan WHERE status_pengajuan = 'Disetujui' AND WEEK(tanggal_ttd) = WEEK(CURDATE()) AND YEAR(tanggal_ttd) = YEAR(CURDATE()) " . ($user_condition_filter ? str_replace('p.', '', $user_condition) : '') . ") as minggu_ini
");
$stats = mysqli_fetch_assoc($stats_query);

// Function untuk membuat URL dengan parameter
function buildUrl($params) {
    $current_params = $_GET;
    // Hapus parameter halaman yang tidak relevan agar URL bersih
    unset($current_params['page_menunggu'], $current_params['page_disetujui'], $current_params['page_ditolak']);
    $merged_params = array_merge($current_params, $params);
    
    return '?' . http_build_query($merged_params);
}

// Function untuk mendapatkan badge status
function getStatusBadge($status) {
    switch($status) {
        case 'Disetujui': return '<span class="badge badge-success">Disetujui</span>';
        case 'Menunggu': return '<span class="badge badge-warning">Menunggu</span>';
        case 'Ditolak': return '<span class="badge badge-danger">Ditolak</span>';
        default: return '<span class="badge badge-secondary">' . $status . '</span>';
    }
}
?>

<div class="panel-header">
    <div class="page-inner py-4">
        <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
            <div class="page-header">
                <h4 class="page-title"><i class="fas fa-history mr-2"></i> Riwayat Pengajuan Tanda Tangan</h4>
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Riwayat Pengajuan</a></li>
                </ul>
            </div>
            <div class="ml-md-auto py-2 py-md-0">
                <a href="?module=form_entri_pengajuan" class="btn btn-secondary btn-round">
                    <span class="btn-label"><i class="fa fa-plus-left mr-2"></i></span> Entri Pengajuan
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-inner mt--5">
    
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-check-circle"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Disetujui</p><h4 class="card-title"><?php echo number_format($stats['total_disetujui']); ?></h4></div></div></div></div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-clock"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Menunggu</p><h4 class="card-title"><?php echo number_format($stats['total_menunggu']); ?></h4></div></div></div></div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-times-circle"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Ditolak</p><h4 class="card-title"><?php echo number_format($stats['total_ditolak']); ?></h4></div></div></div></div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-calendar"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Disetujui Minggu Ini</p><h4 class="card-title"><?php echo number_format($stats['minggu_ini']); ?></h4></div></div></div></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-filter mr-2"></i>Filter & Pencarian</div></div>
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="module" value="riwayat_pengajuan">
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>Pencarian</label><input type="text" class="form-control" name="search" placeholder="Cari judul, perihal, nomor..." value="<?php echo htmlspecialchars($search); ?>"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Jenis Dokumen</label><select class="form-control" name="jenis"><option value="">Semua Jenis</option><?php mysqli_data_seek($query_jenis, 0); while ($jenis = mysqli_fetch_assoc($query_jenis)): ?><option value="<?php echo $jenis['id_jenis']; ?>" <?php echo $jenis_filter == $jenis['id_jenis'] ? 'selected' : ''; ?>><?php echo $jenis['nama_jenis']; ?></option><?php endwhile; ?></select></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Bulan</label><select class="form-control" name="bulan"><option value="">Semua Bulan</option><?php mysqli_data_seek($query_bulan, 0); while ($bulan = mysqli_fetch_assoc($query_bulan)): ?><option value="<?php echo $bulan['bulan']; ?>" <?php echo $bulan_filter == $bulan['bulan'] ? 'selected' : ''; ?>><?php echo date('F Y', strtotime($bulan['bulan'] . '-01')); ?></option><?php endwhile; ?></select></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Tahun</label><select class="form-control" name="tahun"><option value="">Semua Tahun</option><?php mysqli_data_seek($query_tahun, 0); while ($tahun = mysqli_fetch_assoc($query_tahun)): ?><option value="<?php echo $tahun['tahun']; ?>" <?php echo $tahun_filter == $tahun['tahun'] ? 'selected' : ''; ?>><?php echo $tahun['tahun']; ?></option><?php endwhile; ?></select></div></div>
                            <div class="col-md-1"><div class="form-group"><label> </label><div class="btn-group-vertical d-block"><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button><a href="?module=riwayat_pengajuan" class="btn btn-secondary btn-sm mt-1"><i class="fas fa-undo"></i></a></div></div></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list mr-2"></i>Daftar Riwayat - <?php echo getStatusBadge('Disetujui'); ?> (<?php echo number_format($total_data_disetujui); ?> pengajuan)</h5>
                        <div class="ml-auto"><small class="text-muted">Menampilkan <?php echo $total_data_disetujui > 0 ? $offset_disetujui + 1 : 0; ?> - <?php echo min($offset_disetujui + $limit, $total_data_disetujui); ?> dari <?php echo $total_data_disetujui; ?> data</small></div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result_disetujui) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="12%">Tgl. Disetujui</th>
                                        <th width="10%">Status</th>
                                        <th width="12%">Jenis Dokumen</th>
                                        <th width="25%">Judul Surat</th>
                                        <?php if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan'])): ?><th width="12%">Pengaju</th><?php endif; ?>
                                        <th width="12%">Catatan</th>
                                        <th width="12%">File</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = $offset_disetujui + 1; while ($row = mysqli_fetch_assoc($result_disetujui)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><div class="text-sm"><?php if ($row['tanggal_ttd']): ?><strong><?php echo date('d/m/Y', strtotime($row['tanggal_ttd'])); ?></strong><br><small class="text-muted"><?php echo date('H:i', strtotime($row['tanggal_ttd'])); ?></small><?php else: ?><span class="text-muted">-</span><?php endif; ?></div></td>
                                            <td><?php echo getStatusBadge($row['status_pengajuan']); ?></td>
                                            <td><span class="badge badge-info"><?php echo $row['nama_jenis']; ?></span></td>
                                            <td><div class="text-sm"><strong><?php echo htmlspecialchars($row['judul_surat']); ?></strong><?php if (!empty($row['nomor_surat'])): ?><br><small class="text-muted">No: <?php echo htmlspecialchars($row['nomor_surat']); ?></small><?php endif; ?></div></td>
                                            <?php if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan'])): ?><td><div class="text-sm"><strong><?php echo htmlspecialchars($row['nama_pengaju']); ?></strong></div></td><?php endif; ?>
                                            <td><div class="text-sm"><?php echo htmlspecialchars($row['catatan_pimpinan'] ?: '-'); ?></div></td>
                                            <td><?php if (!empty($row['file_dokumen']) && file_exists($row['file_dokumen_signed'])): ?><a href="<?php echo $row['file_dokumen_signed']; ?>" class="btn btn-sm btn-outline-secondary" title="Lihat Dokumen Asli" target="_blank"><i class="fas fa-eye"></i></a><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages_disetujui > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div><small class="text-muted">Halaman <?php echo $page_disetujui; ?> dari <?php echo $total_pages_disetujui; ?></small></div>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($page_disetujui > 1): ?><li class="page-item"><a class="page-link" href="<?php echo buildUrl(['page_disetujui' => $page_disetujui - 1]); ?>"><i class="fas fa-chevron-left"></i></a></li><?php endif; ?>
                                        <?php for ($i = max(1, $page_disetujui - 2); $i <= min($total_pages_disetujui, $page_disetujui + 2); $i++): ?><li class="page-item <?php echo $i == $page_disetujui ? 'active' : ''; ?>"><a class="page-link" href="<?php echo buildUrl(['page_disetujui' => $i]); ?>"><?php echo $i; ?></a></li><?php endfor; ?>
                                        <?php if ($page_disetujui < $total_pages_disetujui): ?><li class="page-item"><a class="page-link" href="<?php echo buildUrl(['page_disetujui' => $page_disetujui + 1]); ?>"><i class="fas fa-chevron-right"></i></a></li><?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-3"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><h5 class="text-muted">Tidak ada riwayat pengajuan</h5><p class="text-muted">Belum ada pengajuan dengan status Disetujui.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list mr-2"></i>Daftar Riwayat - <?php echo getStatusBadge('Menunggu'); ?> (<?php echo number_format($total_data_menunggu); ?> pengajuan)</h5>
                        <div class="ml-auto"><small class="text-muted">Menampilkan <?php echo $total_data_menunggu > 0 ? $offset_menunggu + 1 : 0; ?> - <?php echo min($offset_menunggu + $limit, $total_data_menunggu); ?> dari <?php echo $total_data_menunggu; ?> data</small></div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result_menunggu) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="12%">Tgl. Pengajuan</th>
                                        <th width="10%">Status</th>
                                        <th width="12%">Jenis Dokumen</th>
                                        <th width="25%">Judul Surat</th>
                                        <?php if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan'])): ?><th width="12%">Pengaju</th><?php endif; ?>
                                        <th width="12%">Catatan</th>
                                        <th width="12%">File</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = $offset_menunggu + 1; while ($row = mysqli_fetch_assoc($result_menunggu)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><div class="text-sm"><?php if ($row['tanggal_pengajuan']): ?><strong><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></small><?php else: ?><span class="text-muted">-</span><?php endif; ?></div></td>
                                            <td><?php echo getStatusBadge($row['status_pengajuan']); ?></td>
                                            <td><span class="badge badge-info"><?php echo $row['nama_jenis']; ?></span></td>
                                            <td><div class="text-sm"><strong><?php echo htmlspecialchars($row['judul_surat']); ?></strong><?php if (!empty($row['nomor_surat'])): ?><br><small class="text-muted">No: <?php echo htmlspecialchars($row['nomor_surat']); ?></small><?php endif; ?></div></td>
                                            <?php if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan'])): ?><td><div class="text-sm"><strong><?php echo htmlspecialchars($row['nama_pengaju']); ?></strong></div></td><?php endif; ?>
                                            <td><div class="text-sm"><?php echo htmlspecialchars($row['catatan_pimpinan'] ?: '-'); ?></div></td>
                                            <td><?php if (!empty($row['file_dokumen']) && file_exists($row['file_dokumen'])): ?><a href="<?php echo $row['file_dokumen']; ?>" class="btn btn-sm btn-outline-secondary" title="Lihat Dokumen Asli" target="_blank"><i class="fas fa-eye"></i></a><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages_menunggu > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div><small class="text-muted">Halaman <?php echo $page_menunggu; ?> dari <?php echo $total_pages_menunggu; ?></small></div>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($page_menunggu > 1): ?><li class="page-item"><a class="page-link" href="<?php echo buildUrl(['page_menunggu' => $page_menunggu - 1]); ?>"><i class="fas fa-chevron-left"></i></a></li><?php endif; ?>
                                        <?php for ($i = max(1, $page_menunggu - 2); $i <= min($total_pages_menunggu, $page_menunggu + 2); $i++): ?><li class="page-item <?php echo $i == $page_menunggu ? 'active' : ''; ?>"><a class="page-link" href="<?php echo buildUrl(['page_menunggu' => $i]); ?>"><?php echo $i; ?></a></li><?php endfor; ?>
                                        <?php if ($page_menunggu < $total_pages_menunggu): ?><li class="page-item"><a class="page-link" href="<?php echo buildUrl(['page_menunggu' => $page_menunggu + 1]); ?>"><i class="fas fa-chevron-right"></i></a></li><?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-3"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><h5 class="text-muted">Tidak ada riwayat pengajuan</h5><p class="text-muted">Belum ada pengajuan dengan status Menunggu.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list mr-2"></i>Daftar Riwayat - <?php echo getStatusBadge('Ditolak'); ?> (<?php echo number_format($total_data_ditolak); ?> pengajuan)</h5>
                        <div class="ml-auto"><small class="text-muted">Menampilkan <?php echo $total_data_ditolak > 0 ? $offset_ditolak + 1 : 0; ?> - <?php echo min($offset_ditolak + $limit, $total_data_ditolak); ?> dari <?php echo $total_data_ditolak; ?> data</small></div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result_ditolak) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="12%">Tgl. Pengajuan</th>
                                        <th width="10%">Status</th>
                                        <th width="12%">Jenis Dokumen</th>
                                        <th width="25%">Judul Surat</th>
                                        <?php if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan'])): ?><th width="12%">Pengaju</th><?php endif; ?>
                                        <th width="12%">Catatan</th>
                                        <th width="12%">File</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = $offset_ditolak + 1; while ($row = mysqli_fetch_assoc($result_ditolak)): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><div class="text-sm"><?php if ($row['tanggal_pengajuan']): ?><strong><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></small><?php else: ?><span class="text-muted">-</span><?php endif; ?></div></td>
                                        <td><?php echo getStatusBadge($row['status_pengajuan']); ?></td>
                                        <td><span class="badge badge-info"><?php echo $row['nama_jenis']; ?></span></td>
                                        <td><div class="text-sm"><strong><?php echo htmlspecialchars($row['judul_surat']); ?></strong><?php if (!empty($row['nomor_surat'])): ?><br><small class="text-muted">No: <?php echo htmlspecialchars($row['nomor_surat']); ?></small><?php endif; ?></div></td>
                                        <?php if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan'])): ?><td><div class="text-sm"><strong><?php echo htmlspecialchars($row['nama_pengaju']); ?></strong></div></td><?php endif; ?>
                                        <td><div class="text-sm"><?php echo htmlspecialchars($row['catatan_pimpinan'] ?: '-'); ?></div></td>
                                        <td><?php if (!empty($row['file_dokumen']) && file_exists($row['file_dokumen'])): ?><a href="<?php echo $row['file_dokumen']; ?>" class="btn btn-sm btn-outline-secondary" title="Lihat Dokumen Asli" target="_blank"><i class="fas fa-eye"></i></a><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages_ditolak > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div><small class="text-muted">Halaman <?php echo $page_ditolak; ?> dari <?php echo $total_pages_ditolak; ?></small></div>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($page_ditolak > 1): ?><li class="page-item"><a class="page-link" href="<?php echo buildUrl(['page_ditolak' => $page_ditolak - 1]); ?>"><i class="fas fa-chevron-left"></i></a></li><?php endif; ?>
                                        <?php for ($i = max(1, $page_ditolak - 2); $i <= min($total_pages_ditolak, $page_ditolak + 2); $i++): ?><li class="page-item <?php echo $i == $page_ditolak ? 'active' : ''; ?>"><a class="page-link" href="<?php echo buildUrl(['page_ditolak' => $i]); ?>"><?php echo $i; ?></a></li><?php endfor; ?>
                                        <?php if ($page_ditolak < $total_pages_ditolak): ?><li class="page-item"><a class="page-link" href="<?php echo buildUrl(['page_ditolak' => $page_ditolak + 1]); ?>"><i class="fas fa-chevron-right"></i></a></li><?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-3"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><h5 class="text-muted">Tidak ada riwayat pengajuan</h5><p class="text-muted">Belum ada pengajuan dengan status Ditolak.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>