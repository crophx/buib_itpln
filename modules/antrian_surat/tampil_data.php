<?php
// mencegah direct access file PHP
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('location: ../../404.html');
    exit;
}

// Cek hak akses
if (!isset($_SESSION['hak_akses'])) {
    header('location: ?module=login');
    exit;
}
function getDynamicBadge($text) {
    if (empty($text)) return '';
    // Tentukan palet warna yang akan digunakan
    $colors = ['primary', 'info', 'success', 'dark', 'secondary'];
    // Buat hash dari teks untuk mendapatkan warna yang konsisten
    $hash = crc32($text);
    $color_index = $hash % count($colors);
    // Kembalikan HTML untuk badge
    return '<span class="badge badge-' . $colors[$color_index] . '">' . htmlspecialchars($text) . '</span>';
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

// --- KONDISI HAK AKSES UNTUK MANAJER ---
$hak_akses_condition = "";
// Pastikan session 'hak_akses' dan 'nama_user' tersedia
if (isset($_SESSION['hak_akses']) && isset($_SESSION['nama_user'])) {
    $current_hak_akses = $_SESSION['hak_akses'];
    $current_nama_user = $_SESSION['nama_user'];

    // Cek apakah user adalah seorang Manajer
    if (strpos($current_hak_akses, 'Manajer') === 0) {
        // Peta untuk mencocokkan hak akses Manajer dengan divisi stafnya
        $map_hak_akses = [
            'ManajerBKS' => 'BKS',
            'ManajerBKI' => 'BKI',
            'ManajerLemtera' => 'LEMTERA',
            'ManajerTC' => 'TrainingCenter'
            // Anda bisa tambahkan pemetaan lain jika ada
        ];
        
        // Jika hak akses Manajer ada dalam peta
        if (isset($map_hak_akses[$current_hak_akses])) {
            $bagian = $map_hak_akses[$current_hak_akses];
            // Buat kondisi SQL:
            // Tampilkan surat jika pengaju berasal dari divisinya (u.hak_akses = '$bagian')
            // ATAU jika surat tersebut ditujukan kepadanya (p.tujuan_surat = '$current_nama_user')
            $hak_akses_condition = "AND (u.hak_akses = '$bagian' OR p.tujuan_surat = '" . mysqli_real_escape_string($mysqli, $current_nama_user) . "')";
        }
    }
}

// Query untuk menghitung total data
$count_query = "SELECT COUNT(*) as total FROM tbl_pengajuan p 
                INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis 
                INNER JOIN tbl_user u ON p.id_pengaju = u.id_user 
                WHERE p.status_pengajuan = 'Menunggu' $hak_akses_condition";

$where_conditions = [];
if (!empty($search)) {
    $where_conditions[] = "(p.judul_surat LIKE '%$search%' OR p.perihal LIKE '%$search%' OR p.nomor_surat LIKE '%$search%' OR u.nama_lengkap LIKE '%$search%')";
}
if ($jenis_filter > 0) {
    $where_conditions[] = "p.jenis_dokumen = $jenis_filter";
}
if (!empty($bulan_filter)) {
    $where_conditions[] = "DATE_FORMAT(p.tanggal_pengajuan, '%Y-%m') = '$bulan_filter'";
}
if ($tahun_filter > 0) {
    $where_conditions[] = "YEAR(p.tanggal_pengajuan) = $tahun_filter";
}

if (!empty($where_conditions)) {
    $count_query .= " AND " . implode(" AND ", $where_conditions);
}

$count_result = mysqli_query($mysqli, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data antrian
$query = "SELECT p.*, j.nama_jenis, u.nama_user as nama_pengaju, u.hak_akses
          FROM tbl_pengajuan p 
          INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis 
          INNER JOIN tbl_user u ON p.id_pengaju = u.id_user 
          WHERE p.status_pengajuan = 'Menunggu' $hak_akses_condition";

if (!empty($where_conditions)) {
    $query .= " AND " . implode(" AND ", $where_conditions);
}

$query .= " ORDER BY p.tanggal_pengajuan ASC LIMIT $offset, $limit";
$result = mysqli_query($mysqli, $query);

// Ambil data untuk filter
$query_jenis = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis ASC");
$query_bulan = mysqli_query($mysqli, "SELECT DISTINCT DATE_FORMAT(tanggal_pengajuan, '%Y-%m') as bulan FROM tbl_pengajuan WHERE status_pengajuan = 'Menunggu' ORDER BY bulan DESC");
$query_tahun = mysqli_query($mysqli, "SELECT DISTINCT YEAR(tanggal_pengajuan) as tahun FROM tbl_pengajuan WHERE status_pengajuan = 'Menunggu' AND tanggal_pengajuan IS NOT NULL ORDER BY tahun DESC");

// Hitung statistik
// Hitung statistik
$stats_query_sql = "SELECT 
    COUNT(p.id_pengajuan) as total_antrian,
    COUNT(CASE WHEN DATE(p.tanggal_pengajuan) = CURDATE() THEN 1 END) as hari_ini,
    COUNT(CASE WHEN WEEK(p.tanggal_pengajuan) = WEEK(CURDATE()) AND YEAR(p.tanggal_pengajuan) = YEAR(CURDATE()) THEN 1 END) as minggu_ini,
    COUNT(CASE WHEN DATEDIFF(CURDATE(), p.tanggal_pengajuan) > 7 THEN 1 END) as lebih_seminggu
    FROM tbl_pengajuan p 
    INNER JOIN tbl_user u ON p.id_pengaju = u.id_user 
    WHERE p.status_pengajuan = 'Menunggu' $hak_akses_condition";

$stats_query = mysqli_query($mysqli, $stats_query_sql);
$stats = mysqli_fetch_assoc($stats_query);
?>

<div class="panel-header">
    <div class="page-inner py-45">
        <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
            <div class="page-header">
                <h4 class="page-title">
                    <i class="fas fa-clock mr-2"></i>
                    Antrian Pengajuan Tanda Tangan
                </h4>
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Antrian Pengajuan</a></li>
                </ul>
            </div>
            <div class="ml-md-auto py-2 py-md-0">
                <a href="?module=form_entri_pengajuan" class="btn btn-primary btn-round">
                    <span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Pengajuan Baru
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-inner mt--5">
    <!-- Statistik Antrian -->
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total Antrian</p>
                                <h4 class="card-title"><?php echo number_format($stats['total_antrian']); ?></h4>
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
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Hari Ini</p>
                                <h4 class="card-title"><?php echo number_format($stats['hari_ini']); ?></h4>
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
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Minggu Ini</p>
                                <h4 class="card-title"><?php echo number_format($stats['minggu_ini']); ?></h4>
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
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Lebih 1 Minggu</p>
                                <h4 class="card-title"><?php echo number_format($stats['lebih_seminggu']); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter dan Pencarian -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-filter mr-2"></i>Filter & Pencarian
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="module" value="antrian">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Pencarian</label>
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Cari judul, perihal, nomor, atau nama pengaju..."
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Jenis Dokumen</label>
                                    <select class="form-control" name="jenis">
                                        <option value="">Semua Jenis</option>
                                        <?php while ($jenis = mysqli_fetch_assoc($query_jenis)): ?>
                                            <option value="<?php echo $jenis['id_jenis']; ?>" 
                                                <?php echo $jenis_filter == $jenis['id_jenis'] ? 'selected' : ''; ?>>
                                                <?php echo $jenis['nama_jenis']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Bulan</label>
                                    <select class="form-control" name="bulan">
                                        <option value="">Semua Bulan</option>
                                        <?php while ($bulan = mysqli_fetch_assoc($query_bulan)): ?>
                                            <option value="<?php echo $bulan['bulan']; ?>" 
                                                <?php echo $bulan_filter == $bulan['bulan'] ? 'selected' : ''; ?>>
                                                <?php echo date('F Y', strtotime($bulan['bulan'] . '-01')); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Tahun Anggaran</label>
                                    <select class="form-control" name="tahun">
                                        <option value="">Semua Tahun</option>
                                        <?php while ($tahun = mysqli_fetch_assoc($query_tahun)): ?>
                                            <option value="<?php echo $tahun['tahun']; ?>" 
                                                <?php echo $tahun_filter == $tahun['tahun'] ? 'selected' : ''; ?>>
                                                <?php echo $tahun['tahun']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="btn-group d-block">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search mr-1"></i> Filter
                                        </button>
                                        <a href="?module=antrian_surat" class="btn btn-secondary">
                                            <i class="fas fa-undo mr-1"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Antrian -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list mr-2"></i>
                            Daftar Antrian (<?php echo number_format($total_data); ?> pengajuan)
                        </h5>
                        <div class="ml-auto">
                            <small class="text-muted">
                                Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $limit, $total_data); ?> dari <?php echo $total_data; ?> data
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">Tanggal Masuk</th>
                                        <th width="20%">Judul Surat</th>
                                        <th width="15%">Perihal</th>
                                        <th width="15%">Pengaju</th>
                                        <th width="10%">Tujuan Surat</th>
                                        <th width="10%">Status</th>
                                        <th width="5%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = $offset + 1;
                                    while ($row = mysqli_fetch_assoc($result)):
                                        $days_waiting = (strtotime(date('Y-m-d')) - strtotime($row['tanggal_pengajuan'])) / (60 * 60 * 24);
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <div class="text-sm">
                                                <strong><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></strong><br>
                                                <small class="text-muted"><?php echo ceil($days_waiting); ?> hari lalu</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-sm">
                                                <strong><?php echo htmlspecialchars($row['judul_surat']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['nama_jenis']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-sm"><?php echo htmlspecialchars($row['perihal']); ?></div>
                                        </td>
                                        <td>
                                            <?php echo getDynamicBadge($row['nama_pengaju']); ?>
                                        </td>
                                        <td>
                                            <?php echo getDynamicBadge($row['tujuan_surat']); ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">Menunggu</span>
                                        </td>
                                        <td>
                                            <a href="?module=detail_antrian&id=<?php echo $row['id_pengajuan']; ?>" class="btn btn-sm btn-primary" title="Lihat Detail & Proses">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <small class="text-muted">
                                        Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
                                    </small>
                                </div>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?php echo buildUrl(array_merge($_GET, ['page' => $page - 1])); ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="<?php echo buildUrl(array_merge($_GET, ['page' => $i])); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?php echo buildUrl(array_merge($_GET, ['page' => $page + 1])); ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada pengajuan dalam antrian</h5>
                            <p class="text-muted">Semua pengajuan telah diproses atau belum ada pengajuan baru.</p>
                            <a href="?module=form_entri_pengajuan" class="btn btn-primary btn-round">
                                <i class="fas fa-plus mr-2"></i>Buat Pengajuan Baru
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pengajuan -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle mr-2"></i>Detail Pengajuan
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <div class="text-center py-3">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2">Memuat detail pengajuan...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showDetail(id) {
    $('#modalDetail').modal('show');
    $('#modalDetailContent').html(`
        <div class="text-center py-3">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-2">Memuat detail pengajuan...</p>
        </div>
    `);
    
    $.ajax({
        url: 'modules/pengajuan/detail_ajax.php',
        type: 'POST',
        data: { id: id },
        success: function(response) {
            $('#modalDetailContent').html(response);
        },
        error: function() {
            $('#modalDetailContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Gagal memuat detail pengajuan. Silakan coba lagi.
                </div>
            `);
        }
    });
}

// Auto refresh halaman setiap 5 menit
setInterval(function() {
    location.reload();
}, 300000);
</script>

<?php
// Function untuk membuat URL dengan parameter
function buildUrl($params) {
    $url = '?';
    $query_params = [];
    foreach ($params as $key => $value) {
        if (!empty($value)) {
            $query_params[] = $key . '=' . urlencode($value);
        }
    }
    return $url . implode('&', $query_params);
}
?>