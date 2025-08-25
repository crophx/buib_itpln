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
if (!isset($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan', 'LEMTERA', 'BUIB', 'BKS', 'BKI', 'TrainingCenter', 'User', 'ManajerBKS', 'ManajerBKI','ManajerLemtera','ManajerTC', 'ManajerBUIB'])) {
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

$user_id_column = 'id_pengaju';

// Ambil parameter untuk filter dan pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($mysqli, $_GET['search']) : '';
$jenis_filter = isset($_GET['jenis']) ? (int)$_GET['jenis'] : 0;
$bulan_filter = isset($_GET['bulan']) ? mysqli_real_escape_string($mysqli, $_GET['bulan']) : '';
$tahun_filter = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

// Pagination for each status
$limit = 10;
$page_menunggu = isset($_GET['page_menunggu']) ? (int)$_GET['page_menunggu'] : 1;
$page_ditolak = isset($_GET['page_ditolak']) ? (int)$_GET['page_ditolak'] : 1;
$page_disetujui = isset($_GET['page_disetujui']) ? (int)$_GET['page_disetujui'] : 1;

$offset_menunggu = ($page_menunggu - 1) * $limit;
$offset_ditolak = ($page_ditolak - 1) * $limit;
$offset_disetujui = ($page_disetujui - 1) * $limit;

// Determine user filter based on access level
$user_condition = "";
if (!in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan'])) {
    $user_id = $_SESSION['id_user'] ?? 0;
    $user_condition = "AND p.$user_id_column = $user_id";
}

// Function to build query conditions
function buildWhereConditions($search, $jenis_filter, $bulan_filter, $tahun_filter) {
    $conditions = [];
    if (!empty($search)) {
        $conditions[] = "(p.judul_surat LIKE '%$search%' OR p.perihal LIKE '%$search%' OR p.nomor_surat LIKE '%$search%' OR u.nama_user LIKE '%$search%')";
    }
    if ($jenis_filter > 0) {
        $conditions[] = "p.jenis_dokumen = $jenis_filter";
    }
    if (!empty($bulan_filter)) {
        $conditions[] = "DATE_FORMAT(p.tanggal_pengajuan, '%Y-%m') = '$bulan_filter'";
    }
    if ($tahun_filter > 0) {
        $conditions[] = "YEAR(p.tanggal_pengajuan) = $tahun_filter";
    }
    return $conditions;
}

$where_conditions = buildWhereConditions($search, $jenis_filter, $bulan_filter, $tahun_filter);
$where_clause = !empty($where_conditions) ? " AND " . implode(" AND ", $where_conditions) : "";

// --- Query for each status ---
function getQueryResults($mysqli, $status, $user_condition, $where_clause, $offset, $limit) {
    $user_id_col = 'id_pengaju';
    $query = "SELECT p.*, j.nama_jenis, u.nama_user as nama_pengaju 
              FROM tbl_pengajuan p 
              INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis 
              INNER JOIN tbl_user u ON p.$user_id_col = u.id_user 
              WHERE p.status_pengajuan = '$status' $user_condition $where_clause 
              ORDER BY p.tanggal_pengajuan DESC 
              LIMIT $offset, $limit";
    return mysqli_query($mysqli, $query);
}

function getTotalData($mysqli, $status, $user_condition, $where_clause) {
    $user_id_col = 'id_pengaju';
    $count_query = "SELECT COUNT(*) as total 
                    FROM tbl_pengajuan p 
                    INNER JOIN tbl_user u ON p.$user_id_col = u.id_user 
                    WHERE p.status_pengajuan = '$status' $user_condition $where_clause";
    $result = mysqli_query($mysqli, $count_query);
    return mysqli_fetch_assoc($result)['total'];
}

// Disetujui
$total_data_disetujui = getTotalData($mysqli, 'Disetujui', $user_condition, $where_clause);
$total_pages_disetujui = ceil($total_data_disetujui / $limit);
$result_disetujui = getQueryResults($mysqli, 'Disetujui', $user_condition, $where_clause, $offset_disetujui, $limit);

// Menunggu
$total_data_menunggu = getTotalData($mysqli, 'Menunggu', $user_condition, $where_clause);
$total_pages_menunggu = ceil($total_data_menunggu / $limit);
$result_menunggu = getQueryResults($mysqli, 'Menunggu', $user_condition, $where_clause, $offset_menunggu, $limit);

// Ditolak
$total_data_ditolak = getTotalData($mysqli, 'Ditolak', $user_condition, $where_clause);
$total_pages_ditolak = ceil($total_data_ditolak / $limit);
$result_ditolak = getQueryResults($mysqli, 'Ditolak', $user_condition, $where_clause, $offset_ditolak, $limit);

// Ambil data untuk filter
$query_jenis = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis ASC");
$query_bulan = mysqli_query($mysqli, "SELECT DISTINCT DATE_FORMAT(tanggal_pengajuan, '%Y-%m') as bulan FROM tbl_pengajuan ORDER BY bulan DESC");
$query_tahun = mysqli_query($mysqli, "SELECT DISTINCT YEAR(tanggal_pengajuan) as tahun FROM tbl_pengajuan ORDER BY tahun DESC");

$stats = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT 
    (SELECT COUNT(*) FROM tbl_pengajuan WHERE status_pengajuan = 'Disetujui' $user_condition) as total_disetujui,
    (SELECT COUNT(*) FROM tbl_pengajuan WHERE status_pengajuan = 'Menunggu' $user_condition) as total_menunggu,
    (SELECT COUNT(*) FROM tbl_pengajuan WHERE status_pengajuan = 'Ditolak' $user_condition) as total_ditolak
"));

// Helper functions
function buildUrl($params) {
    return '?' . http_build_query(array_merge($_GET, $params));
}
function getStatusBadge($status) { /* ... (as before) ... */ }
function getTujuanBadge($tujuan) {
    $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
    $hash = crc32($tujuan);
    $color_index = $hash % count($colors);
    return '<span class="badge badge-' . $colors[$color_index] . '">' . htmlspecialchars($tujuan) . '</span>';
}
?>

<div class="panel-header">
    <div class="page-inner py-4">
        <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
            <div class="page-header"><h4 class="page-title"><i class="fas fa-history mr-2"></i> Riwayat Pengajuan Anda</h4></div>
            <div class="ml-md-auto py-2 py-md-0"><a href="?module=form_entri_pengajuan" class="btn btn-secondary btn-round"><span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Buat Pengajuan Baru</a></div>
        </div>
    </div>
</div>

<div class="page-inner mt--5">
    
    <div class="row">
        <div class="col-sm-6 col-md-4"><div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-check-circle"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Disetujui</p><h4 class="card-title"><?php echo number_format($stats['total_disetujui']); ?></h4></div></div></div></div></div></div>
        <div class="col-sm-6 col-md-4"><div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-clock"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Menunggu</p><h4 class="card-title"><?php echo number_format($stats['total_menunggu']); ?></h4></div></div></div></div></div></div>
        <div class="col-sm-6 col-md-4"><div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-times-circle"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Ditolak</p><h4 class="card-title"><?php echo number_format($stats['total_ditolak']); ?></h4></div></div></div></div></div></div>
    </div>

    <ul class="nav nav-pills nav-secondary" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="pills-menunggu-tab" data-toggle="pill" href="#pills-menunggu" role="tab" aria-controls="pills-menunggu" aria-selected="true">
                Menunggu <span class="badge badge-warning"><?php echo $total_data_menunggu; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pills-disetujui-tab" data-toggle="pill" href="#pills-disetujui" role="tab" aria-controls="pills-disetujui" aria-selected="false">
                Disetujui <span class="badge badge-success"><?php echo $total_data_disetujui; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pills-ditolak-tab" data-toggle="pill" href="#pills-ditolak" role="tab" aria-controls="pills-ditolak" aria-selected="false">
                Ditolak <span class="badge badge-danger"><?php echo $total_data_ditolak; ?></span>
            </a>
        </li>
    </ul>

    <div class="tab-content mt-2 mb-3" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-menunggu" role="tabpanel" aria-labelledby="pills-menunggu-tab">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th><th>Tgl. Pengajuan</th><th>Judul Surat</th><th>Perihal</th><th>Tujuan</th><th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($total_data_menunggu > 0): $no = $offset_menunggu + 1; while ($row = mysqli_fetch_assoc($result_menunggu)): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['judul_surat']); ?></td>
                                        <td><?php echo htmlspecialchars($row['perihal']); ?></td>
                                        <td><?php echo getTujuanBadge($row['tujuan_surat']); ?></td>
                                        <td><a href="<?php echo $row['file_dokumen']; ?>" class="btn btn-xs btn-info" target="_blank"><i class="fas fa-eye"></i> Lihat</a></td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="6" class="text-center">Tidak ada pengajuan yang sedang menunggu.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pills-disetujui" role="tabpanel" aria-labelledby="pills-disetujui-tab">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th><th>Tgl. Disetujui</th><th>Judul Surat</th><th>Perihal</th><th>Tujuan</th><th>File TTD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($total_data_disetujui > 0): $no = $offset_disetujui + 1; while ($row = mysqli_fetch_assoc($result_disetujui)): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_ttd'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['judul_surat']); ?></td>
                                        <td><?php echo htmlspecialchars($row['perihal']); ?></td>
                                        <td><?php echo getTujuanBadge($row['tujuan_surat']); ?></td>
                                        <td><a href="<?php echo $row['file_dokumen_signed']; ?>" class="btn btn-xs btn-success" target="_blank"><i class="fas fa-download"></i> Unduh</a></td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="6" class="text-center">Belum ada pengajuan yang disetujui.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pills-ditolak" role="tabpanel" aria-labelledby="pills-ditolak-tab">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th><th>Tgl. Pengajuan</th><th>Judul Surat</th><th>Perihal</th><th>Tujuan</th><th>Catatan</th><th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($total_data_ditolak > 0): $no = $offset_ditolak + 1; while ($row = mysqli_fetch_assoc($result_ditolak)): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['judul_surat']); ?></td>
                                        <td><?php echo htmlspecialchars($row['perihal']); ?></td>
                                        <td><?php echo getTujuanBadge($row['tujuan_surat']); ?></td>
                                        <td><?php echo htmlspecialchars($row['catatan_pimpinan']); ?></td>
                                        <td>
                                            <?php if ($row['id_pengaju'] == $_SESSION['id_user']): ?>
                                                <button class="btn btn-xs btn-warning btn-revisi" 
                                                        data-id="<?php echo $row['id_pengajuan']; ?>" 
                                                        data-judul="<?php echo htmlspecialchars($row['judul_surat']); ?>"
                                                        data-toggle="modal" 
                                                        data-target="#revisiModal">
                                                    <i class="fas fa-upload"></i> Revisi Dokumen
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="7" class="text-center">Tidak ada pengajuan yang ditolak.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="revisiModal" tabindex="-1" role="dialog" aria-labelledby="revisiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="modules/pengajuan_surat/proses_revisi.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="revisiModalLabel">Unggah Dokumen Revisi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_pengajuan_revisi" id="id_pengajuan_revisi">
                    <p>Anda akan merevisi dokumen untuk pengajuan:</p>
                    <p><strong><span id="judul_surat_modal"></span></strong></p>
                    <hr>
                    <div class="form-group">
                        <label for="file_dokumen_revisi">Pilih Dokumen Baru <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" id="file_dokumen_revisi" name="file_dokumen_revisi" accept=".pdf,.doc,.docx" required>
                        <small class="form-text text-muted">Format yang diizinkan: PDF, DOC, DOCX. Maksimal 10MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Kirim Revisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script untuk mengisi data ke dalam modal revisi
$(document).ready(function() {
    $('.btn-revisi').on('click', function() {
        // Ambil data dari tombol yang di-klik
        const id = $(this).data('id');
        const judul = $(this).data('judul');

        // Set nilai-nilai tersebut ke dalam form di modal
        $('#id_pengajuan_revisi').val(id);
        $('#judul_surat_modal').text(judul);
    });

    // Handle tab state on page reload
    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('lastTab', $(this).attr('href'));
    });
    let lastTab = localStorage.getItem('lastTab');
    if (lastTab) {
        $('[href="' + lastTab + '"]').tab('show');
    }
});
</script>