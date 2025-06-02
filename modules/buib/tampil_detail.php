<?php
if(basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('location: 404.html');
}
else {
    if (isset($_GET['id'])) {
        $id_buib = mysqli_real_escape_string($mysqli, $_GET['id']);

        $query = mysqli_query($mysqli, "SELECT a.id_buib, a.mitra, a.jenis_dokumen, a.no_dokumen, a.target_nominal, a.realisasi_nominal, a.tgl_upload, a.dokumen_buib, b.nama_jenis
                                FROM tbl_buib as a INNER JOIN tbl_jenis as b ON a.jenis_dokumen=b.id_jenis
                                WHERE a.id_buib='$id_buib'")
                                or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));

        // Ambil data hasil query
        $data = mysqli_fetch_assoc($query);
        
    }
?>
    <div class="panel-header">
        <div class="page-inner py-45">
            <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
                <!-- Judul Halaman -->
                <h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Detail Dokumen BUIB</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=buib">BUIB</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Detail</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <!-- Card Detail Informasi -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>
                            Detail Informasi Dokumen
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="200"><strong>ID BUIB</strong></td>
                                        <td width="20">:</td>
                                        <td><?php echo htmlspecialchars($data['id_buib']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nama Mitra</strong></td>
                                        <td>:</td>
                                        <td><?php echo htmlspecialchars($data['mitra']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jenis Dokumen</strong></td>
                                        <td>:</td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <?php echo htmlspecialchars($data['nama_jenis']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>No Dokumen</strong></td>
                                        <td>:</td>
                                        <td><?php echo htmlspecialchars($data['no_dokumen']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Target Nominal</strong></td>
                                        <td>:</td>
                                        <td>
                                            <span class="text-success font-weight-bold">
                                                Rp <?php echo number_format($data['target_nominal'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Realisasi Nominal</strong></td>
                                        <td>:</td>
                                        <td>
                                            <span class="text-info font-weight-bold">
                                                Rp <?php echo number_format($data['realisasi_nominal'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Persentase Realisasi</strong></td>
                                        <td>:</td>
                                        <td>
                                            <?php 
                                            $persentase = ($data['target_nominal'] > 0) ? 
                                                         ($data['realisasi_nominal'] / $data['target_nominal']) * 100 : 0;
                                            $color_class = '';
                                            if ($persentase >= 100) {
                                                $color_class = 'text-success';
                                            } elseif ($persentase >= 75) {
                                                $color_class = 'text-warning';
                                            } else {
                                                $color_class = 'text-danger';
                                            }
                                            ?>
                                            <span class="<?php echo $color_class; ?> font-weight-bold">
                                                <?php echo number_format($persentase, 2); ?>%
                                            </span>
                                            <div class="progress mt-2" style="height: 8px;">
                                                <div class="progress-bar <?php echo str_replace('text-', 'bg-', $color_class); ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo min($persentase, 100); ?>%"
                                                     aria-valuenow="<?php echo $persentase; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Upload</strong></td>
                                        <td>:</td>
                                        <td>
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            <?php echo date('d F Y', strtotime($data['tgl_upload'])); ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Dokumen -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Dokumen
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($data['dokumen_buib'])): ?>
                            <div class="mb-3">
                                <i class="fas fa-file-pdf fa-5x text-danger mb-3"></i>
                                <h6 class="text-muted">File PDF</h6>
                                <p class="small text-muted mb-3">
                                    <?php echo htmlspecialchars($data['dokumen_buib']); ?>
                                </p>
                            </div>
                            
                            <!-- Tombol Aksi Dokumen -->
                            <div class="btn-group-vertical w-100">
                                <a href="dokumen/buib/<?php echo htmlspecialchars($data['dokumen_buib']); ?>" 
                                   target="_blank" 
                                   class="btn btn-primary btn-sm mb-2">
                                    <i class="fas fa-eye mr-1"></i> Lihat Dokumen
                                </a>
                                <a href="dokumen/buib/<?php echo htmlspecialchars($data['dokumen_buib']); ?>" 
                                   download 
                                   class="btn btn-success btn-sm">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fas fa-file-slash fa-5x text-muted mb-3"></i>
                                <h6 class="text-muted">Tidak Ada Dokumen</h6>
                                <p class="small text-muted">
                                    Dokumen belum diunggah untuk data ini
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card Statistik Singkat -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Ringkasan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="border-right">
                                    <h4 class="text-success mb-1">
                                        <?php echo number_format($data['target_nominal'] / 1000000, 1); ?>M
                                    </h4>
                                    <small class="text-muted">Target</small>
                                </div>
                            </div>
                            <div class="col-6 text-center">
                                <h4 class="text-info mb-1">
                                    <?php echo number_format($data['realisasi_nominal'] / 1000000, 1); ?>M
                                </h4>
                                <small class="text-muted">Realisasi</small>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <small class="text-muted">
                                Status: 
                                <?php if ($persentase >= 100): ?>
                                    <span class="badge badge-success">Tercapai</span>
                                <?php elseif ($persentase >= 75): ?>
                                    <span class="badge badge-warning">Hampir Tercapai</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Belum Tercapai</span>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Aksi -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Aksi Dokumen</h6>
                                <small class="text-muted">Pilih aksi yang ingin dilakukan</small>
                            </div>
                            <div class="btn-group">
                                <a href="?module=buib&action=ubah&id=<?php echo $data['id_buib']; ?>" 
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <a href="?module=buib&action=hapus&id=<?php echo $data['id_buib']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                    <i class="fas fa-trash mr-1"></i> Hapus
                                </a>
                                <a href="?module=buib" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom CSS untuk styling tambahan -->
    <style>
        .progress {
            background-color: #f8f9fa;
        }
        .btn-group-vertical .btn {
            border-radius: 4px;
        }
        .btn-group-vertical .btn + .btn {
            margin-top: 5px;
        }
        .border-right {
            border-right: 1px solid #dee2e6 !important;
        }
        .table-borderless td {
            border: none;
            padding: 8px 0;
        }
        .card {
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
    </style>

<?php 
}
?>