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
if (!isset($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan', 'ManajerBKS', 'ManajerBKI','ManajerLemtera','ManajerTC', 'ManajerBUIB'])) {
    echo "<script>alert('Akses ditolak! Hak akses: " . ($_SESSION['hak_akses'] ?? 'tidak ada') . "');</script>";
    header('location: ?module=login');
    exit;
}

// Check database connection
if (!isset($mysqli)) {
    die("Koneksi database tidak tersedia. Pastikan file koneksi sudah di-include.");
}

// Function untuk mendapatkan badge status
function getStatusBadge($status) {
    switch($status) {
        case 'Disetujui': return '<span class="badge badge-success">Disetujui</span>';
        case 'Ditolak': return '<span class="badge badge-danger">Ditolak</span>';
        default: return '<span class="badge badge-secondary">' . htmlspecialchars($status) . '</span>';
    }
}

// Ambil parameter untuk filter dan pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($mysqli, $_GET['search']) : '';
$jenis_filter = isset($_GET['jenis']) ? (int)$_GET['jenis'] : 0;
$bulan_filter = isset($_GET['bulan']) ? mysqli_real_escape_string($mysqli, $_GET['bulan']) : '';
$tahun_filter = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- KONDISI WHERE DASAR ---
// Mengambil status Disetujui dan Ditolak
$base_condition = "p.status_pengajuan IN ('Disetujui', 'Ditolak')";

// --- KONDISI HAK AKSES UNTUK MANAJER ---
$hak_akses_condition = "";
$current_hak_akses = $_SESSION['hak_akses'];
$current_nama_user = $_SESSION['nama_user'] ?? '';

if (strpos($current_hak_akses, 'Manajer') === 0) {
    $map_hak_akses = [
        'ManajerBKS' => 'BKS',
        'ManajerBKI' => 'BKI',
        'ManajerLemtera' => 'LEMTERA',
        'ManajerTC' => 'TrainingCenter',
        'ManajerBUIB' => 'BUIB'
    ];
    
    if (isset($map_hak_akses[$current_hak_akses])) {
        $bagian = $map_hak_akses[$current_hak_akses];
        // Manajer melihat pengajuan dari stafnya ATAU yang ditujukan kepadanya
        $hak_akses_condition = "AND (u.hak_akses = '$bagian' OR p.tujuan_surat = '" . mysqli_real_escape_string($mysqli, $current_nama_user) . "')";
    }
}

// --- KONDISI FILTER DARI FORM ---
$where_filters = [];
if (!empty($search)) {
    $where_filters[] = "(p.judul_surat LIKE '%$search%' OR p.perihal LIKE '%$search%' OR p.nomor_surat LIKE '%$search%' OR u.nama_user LIKE '%$search%' OR p.tujuan_surat LIKE '%$search%')";
}
if ($jenis_filter > 0) {
    $where_filters[] = "p.jenis_dokumen = $jenis_filter";
}
if (!empty($bulan_filter)) {
    $where_filters[] = "DATE_FORMAT(p.tanggal_ttd, '%Y-%m') = '$bulan_filter'";
}
if ($tahun_filter > 0) {
    $where_filters[] = "YEAR(p.tanggal_ttd) = $tahun_filter";
}

$filter_condition = "";
if (!empty($where_filters)) {
    $filter_condition = " AND " . implode(" AND ", $where_filters);
}

// Query untuk menghitung total data
$count_query = "SELECT COUNT(*) as total FROM tbl_pengajuan p 
                INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis 
                INNER JOIN tbl_user u ON p.id_pengaju = u.id_user 
                WHERE $base_condition $hak_akses_condition $filter_condition";

$count_result = mysqli_query($mysqli, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data riwayat
$query = "SELECT p.*, j.nama_jenis, u.nama_user as nama_pengaju
          FROM tbl_pengajuan p 
          INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis 
          INNER JOIN tbl_user u ON p.id_pengaju = u.id_user 
          WHERE $base_condition $hak_akses_condition $filter_condition
          ORDER BY p.tanggal_ttd DESC, p.tanggal_pengajuan DESC LIMIT $offset, $limit";
$result = mysqli_query($mysqli, $query);

// Ambil data untuk dropdown filter
$query_jenis = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis ASC");
$query_bulan = mysqli_query($mysqli, "SELECT DISTINCT DATE_FORMAT(tanggal_ttd, '%Y-%m') as bulan FROM tbl_pengajuan WHERE status_pengajuan = 'Disetujui' AND tanggal_ttd IS NOT NULL ORDER BY bulan DESC");
$query_tahun = mysqli_query($mysqli, "SELECT DISTINCT YEAR(tanggal_ttd) as tahun FROM tbl_pengajuan WHERE status_pengajuan = 'Disetujui' AND tanggal_ttd IS NOT NULL ORDER BY tahun DESC");

// Hitung statistik (disesuaikan dengan hak akses)
$stats_query = mysqli_query($mysqli, "SELECT 
    COUNT(CASE WHEN p.status_pengajuan IN ('Disetujui', 'Ditolak') THEN 1 END) as total_riwayat,
    COUNT(CASE WHEN p.status_pengajuan = 'Disetujui' AND DATE(p.tanggal_ttd) = CURDATE() THEN 1 END) as hari_ini,
    COUNT(CASE WHEN p.status_pengajuan = 'Disetujui' AND WEEK(p.tanggal_ttd) = WEEK(CURDATE()) AND YEAR(p.tanggal_ttd) = YEAR(CURDATE()) THEN 1 END) as minggu_ini
    FROM tbl_pengajuan p
    INNER JOIN tbl_user u ON p.id_pengaju = u.id_user
    WHERE 1=1 $hak_akses_condition
");
$stats = mysqli_fetch_assoc($stats_query);
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
                <a href="?module=antrian_surat" class="btn btn-secondary btn-round"><span class="btn-label"><i class="fa fa-arrow-left mr-2"></i></span> Kembali ke Antrian</a>
            </div>
        </div>
    </div>
</div>

<div class="page-inner mt--5">
    <div class="row">
        <div class="col-sm-6 col-md-4"><div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-check-circle"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Total Riwayat</p><h4 class="card-title"><?php echo number_format($stats['total_riwayat']); ?></h4></div></div></div></div></div></div>
        <div class="col-sm-6 col-md-4"><div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-check-circle"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Disetujui Hari Ini</p><h4 class="card-title"><?php echo number_format($stats['hari_ini']); ?></h4></div></div></div></div></div></div>
        <div class="col-sm-6 col-md-4"><div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-check-circle"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Disetujui Minggu Ini</p><h4 class="card-title"><?php echo number_format($stats['minggu_ini']); ?></h4></div></div></div></div></div></div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-filter mr-2"></i>Filter & Pencarian</div></div>
                <div class="card-body">
                    <form method="GET" action=""><input type="hidden" name="module" value="riwayat_pengajuan"><div class="row"><div class="col-md-3"><div class="form-group"><label>Pencarian</label><input type="text" class="form-control" name="search" placeholder="Cari judul, perihal, pengaju..." value="<?php echo htmlspecialchars($search); ?>"></div></div><div class="col-md-3"><div class="form-group"><label>Jenis Dokumen</label><select class="form-control" name="jenis"><option value="">Semua Jenis</option><?php mysqli_data_seek($query_jenis, 0); while ($jenis = mysqli_fetch_assoc($query_jenis)): ?><option value="<?php echo $jenis['id_jenis']; ?>" <?php echo $jenis_filter == $jenis['id_jenis'] ? 'selected' : ''; ?>><?php echo $jenis['nama_jenis']; ?></option><?php endwhile; ?></select></div></div><div class="col-md-2"><div class="form-group"><label>Bulan TTD</label><select class="form-control" name="bulan"><option value="">Semua Bulan</option><?php mysqli_data_seek($query_bulan, 0); while ($bulan = mysqli_fetch_assoc($query_bulan)): ?><option value="<?php echo $bulan['bulan']; ?>" <?php echo $bulan_filter == $bulan['bulan'] ? 'selected' : ''; ?>><?php echo date('F Y', strtotime($bulan['bulan'] . '-01')); ?></option><?php endwhile; ?></select></div></div><div class="col-md-2"><div class="form-group"><label>Tahun TTD</label><select class="form-control" name="tahun"><option value="">Semua Tahun</option><?php mysqli_data_seek($query_tahun, 0); while ($tahun = mysqli_fetch_assoc($query_tahun)): ?><option value="<?php echo $tahun['tahun']; ?>" <?php echo $tahun_filter == $tahun['tahun'] ? 'selected' : ''; ?>><?php echo $tahun['tahun']; ?></option><?php endwhile; ?></select></div></div><div class="col-md-2"><div class="form-group"><label>&nbsp;</label><div class="btn-group d-block"><button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Filter</button><a href="?module=riwayat_pengajuan" class="btn btn-secondary"><i class="fas fa-undo mr-1"></i> Reset</a></div></div></div></div></form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><div class="d-flex align-items-center"><h5 class="card-title mb-0"><i class="fas fa-list mr-2"></i>Daftar Riwayat Pengajuan (<?php echo number_format($total_data); ?>)</h5><div class="ml-auto"><small class="text-muted">Menampilkan <?php echo $total_data > 0 ? $offset + 1 : 0; ?> - <?php echo min($offset + $limit, $total_data); ?> dari <?php echo $total_data; ?> data</small></div></div></div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">Tanggal Proses</th>
                                        <th width="10%">Status</th>
                                        <th width="20%">Judul Surat</th>
                                        <th width="15%">Pengaju</th>
                                        <th width="15%">Tujuan Surat</th> 
                                        <th width="15%">Catatan</th>
                                        <th width="10%">File</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = $offset + 1;
                                    while ($row = mysqli_fetch_assoc($result)): 
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <div class="text-sm">
                                                <?php 
                                                $tanggal = !empty($row['tanggal_ttd']) ? $row['tanggal_ttd'] : $row['tanggal_pengajuan'];
                                                ?>
                                                <strong><?php echo date('d/m/Y', strtotime($tanggal)); ?></strong><br>
                                                <small class="text-muted"><?php echo date('H:i', strtotime($tanggal)); ?> WIB</small>
                                            </div>
                                        </td>
                                        <td><?php echo getStatusBadge($row['status_pengajuan']); ?></td>
                                        <td>
                                            <div class="text-sm">
                                                <strong><?php echo htmlspecialchars($row['judul_surat']); ?></strong>
                                                <?php if (!empty($row['nomor_surat'])): ?>
                                                    <br><small class="text-muted">No: <?php echo htmlspecialchars($row['nomor_surat']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-sm"><strong><?php echo htmlspecialchars($row['nama_pengaju']); ?></strong></div>
                                        </td>
                                        <td>
                                            <div class="text-sm"><?php echo htmlspecialchars($row['tujuan_surat'] ?: '-'); ?></div>
                                        </td>
                                        <td>
                                            <div class="text-sm"><?php echo htmlspecialchars($row['catatan_pimpinan'] ?: 'Tidak ada catatan'); ?></div>
                                        </td>
                                        <td>
                                            <?php 
                                            $file_path = !empty($row['file_dokumen_signed']) && file_exists($row['file_dokumen_signed']) 
                                                         ? $row['file_dokumen_signed'] 
                                                         : (!empty($row['file_dokumen']) && file_exists($row['file_dokumen']) ? $row['file_dokumen'] : '');
                                            ?>
                                            <?php if (!empty($file_path)): ?>
                                                <a href="<?php echo htmlspecialchars($file_path); ?>" class="btn btn-sm btn-outline-secondary" title="Lihat Dokumen" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div><small class="text-muted">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></small></div>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($page > 1): 
                                            $prev_params = array_merge($_GET, ['page' => $page - 1]);
                                        ?>
                                            <li class="page-item"><a class="page-link" href="?<?php echo http_build_query($prev_params); ?>"><i class="fas fa-chevron-left"></i></a></li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): 
                                            $page_params = array_merge($_GET, ['page' => $i]);
                                        ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?<?php echo http_build_query($page_params); ?>"><?php echo $i; ?></a></li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): 
                                            $next_params = array_merge($_GET, ['page' => $page + 1]);
                                        ?>
                                            <li class="page-item"><a class="page-link" href="?<?php echo http_build_query($next_params); ?>"><i class="fas fa-chevron-right"></i></a></li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada riwayat pengajuan</h5>
                            <p class="text-muted">Belum ada pengajuan yang cocok dengan filter yang Anda pilih.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>