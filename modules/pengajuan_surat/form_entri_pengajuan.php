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

// Ambil ID untuk edit (jika ada)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$edit_mode = $id > 0;

// Data untuk edit
$data_pengajuan = [];
if ($edit_mode) {
    $query = mysqli_query($mysqli, "SELECT * FROM tbl_pengajuan WHERE id_pengajuan = $id AND id_pengaju = ".$_SESSION['id_user']);
    if (mysqli_num_rows($query) > 0) {
        $data_pengajuan = mysqli_fetch_assoc($query);
        // Hanya bisa edit jika status masih menunggu
        if ($data_pengajuan['status_pengajuan'] != 'Menunggu') {
            echo "<script>alert('Pengajuan sudah diproses, tidak dapat diubah!'); window.location.href='?module=antrian_surat';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Data tidak ditemukan!'); window.location.href='?module=antrian_surat';</script>";
        exit;
    }
}

// Ambil data jenis dokumen
$query_jenis = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis ASC");

// BARU: Ambil data pimpinan/manajer untuk dropdown tujuan
$hak_akses_tujuan = ['Pimpinan', 'ManajerBKS', 'ManajerBUIB', 'ManajerBKI', 'ManajerLemtera', 'ManajerTC'];
$query_tujuan = mysqli_query($mysqli, "SELECT nama_user FROM tbl_user WHERE hak_akses IN ('" . implode("','", $hak_akses_tujuan) . "') ORDER BY nama_user ASC");

?>

<div class="panel-header">
    <div class="page-inner py-45">
        <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
            <div class="page-header">
                <h4 class="page-title">
                    <i class="fas fa-file-signature mr-2"></i>
                    <?php echo $edit_mode ? 'Ubah' : 'Ajukan'; ?> Pengajuan Tanda Tangan
                </h4>
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=antrian_surat">Pengajuan</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a><?php echo $edit_mode ? 'Ubah' : 'Tambah'; ?></a></li>
                </ul>
            </div>
            <div class="ml-md-auto py-2 py-md-0">
                <a href="?module=antrian_surat" class="btn btn-secondary btn-round">
                    <span class="btn-label"><i class="fa fa-arrow-left mr-2"></i></span> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Form <?php echo $edit_mode ? 'Ubah' : 'Pengajuan'; ?> Tanda Tangan
                    </div>
                </div> 
                
                <form action="modules/pengajuan_surat/proses_simpan.php" method="POST" enctype="multipart/form-data" id="formPengajuan">
                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="id_pengajuan" value="<?php echo $data_pengajuan['id_pengajuan']; ?>">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jenis_dokumen">Jenis Dokumen <span class="text-danger">*</span></label>
                                    <select class="form-control" id="jenis_dokumen" name="jenis_dokumen" required>
                                        <option value="">-- Pilih Jenis Dokumen --</option>
                                        <?php 
                                        mysqli_data_seek($query_jenis, 0); // Reset pointer
                                        while ($jenis = mysqli_fetch_assoc($query_jenis)): 
                                        ?>
                                            <option value="<?php echo $jenis['id_jenis']; ?>" 
                                                <?php echo ($edit_mode && $data_pengajuan['jenis_dokumen'] == $jenis['id_jenis']) ? 'selected' : ''; ?>>
                                                <?php echo $jenis['nama_jenis']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="judul_surat">Judul Surat <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="judul_surat" name="judul_surat" autocomplete="off"
                                           placeholder="Masukkan judul surat" required
                                           value="<?php echo $edit_mode ? htmlspecialchars($data_pengajuan['judul_surat']) : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="tanggal_pengajuan">Tanggal Pengajuan <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal_pengajuan" name="tanggal_pengajuan" 
                                           value="<?php echo $edit_mode ? $data_pengajuan['tanggal_pengajuan'] : date('Y-m-d'); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nomor_surat">Nomor Surat</label>
                                    <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" autocomplete="off"
                                           placeholder="Kosongkan jika belum ada nomor"
                                           value="<?php echo $edit_mode ? htmlspecialchars($data_pengajuan['nomor_surat']) : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="tujuan_surat">Tujuan Tanda Tangan <span class="text-danger">*</span></label>
                                    <select class="form-control" id="tujuan_surat" name="tujuan_surat" required>
                                        <option value="">-- Pilih Tujuan --</option>
                                        <?php 
                                        while ($tujuan = mysqli_fetch_assoc($query_tujuan)): 
                                            $nama_user = htmlspecialchars($tujuan['nama_user']);
                                        ?>
                                            <option value="<?php echo $nama_user; ?>" 
                                                <?php 
                                                if ($edit_mode && isset($data_pengajuan['tujuan_surat']) && $data_pengajuan['tujuan_surat'] == $nama_user) {
                                                    echo 'selected';
                                                } 
                                                ?>>
                                                <?php echo $nama_user; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="file_dokumen">Upload Dokumen <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control-file" id="file_dokumen" name="file_dokumen" 
                                           accept=".pdf,.doc,.docx" <?php echo !$edit_mode ? 'required' : ''; ?>>
                                    <small class="text-muted">
                                        Format yang diizinkan: PDF, DOC, DOCX. Maksimal 10MB.
                                    </small>
                                    <?php if ($edit_mode && !empty($data_pengajuan['file_dokumen'])): ?>
                                        <div class="mt-2">
                                            <small class="text-info">
                                                <i class="fas fa-file mr-1"></i>File saat ini: 
                                                <strong><?php echo basename($data_pengajuan['file_dokumen']); ?></strong>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group" id="file-preview" style="display: none;">
                                    <label>Preview File:</label>
                                    <div class="border p-2 rounded">
                                        <i class="fas fa-file-alt fa-2x text-primary"></i>
                                        <span id="file-name" class="ml-2"></span>
                                        <button type="button" class="btn btn-sm btn-danger float-right" onclick="removeFile()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="perihal">Perihal <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="perihal" name="perihal" rows="4" 
                                              placeholder="Jelaskan perihal atau isi ringkas dari surat yang akan ditandatangani" required><?php echo $edit_mode ? htmlspecialchars($data_pengajuan['perihal']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle mr-2"></i>Informasi:</h6>
                                    <ul class="mb-0">
                                        <li>Pastikan dokumen sudah dalam format final sebelum diupload.</li>
                                        <li>Dokumen akan masuk ke antrian tanda tangan pimpinan.</li>
                                        <li>Status pengajuan dapat dilihat di halaman riwayat pengajuan.</li>
                                        <li>Dokumen yang sudah ditandatangani dapat didownload di halaman riwayat.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-secondary btn-round mr-2" onclick="window.location.href='?module=antrian_surat'">
                                    <i class="fas fa-times mr-2"></i>Batal
                                </button>
                                <button type="submit" class="btn btn-primary btn-round" id="btnSubmit" name="simpan_pengajuan" value="1">
                                    <i class="fas fa-save mr-2"></i><?php echo $edit_mode ? 'Simpan Perubahan' : 'Ajukan Tanda Tangan'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // File upload preview
    $('#file_dokumen').change(function() {
        let file = this.files[0];
        if (file) {
            // Validasi ukuran file (10MB)
            if (file.size > 10485760) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file maksimal 10MB!',
                });
                $(this).val('');
                return;
            }

            // Validasi tipe file
            let allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tipe File Tidak Didukung',
                    text: 'Hanya file PDF, DOC, dan DOCX yang diizinkan!',
                });
                $(this).val('');
                return;
            }

            // Show preview
            $('#file-name').text(file.name);
            $('#file-preview').show();
        } else {
            $('#file-preview').hide();
        }
    });

    // Form validation
    $('#formPengajuan').submit(function(e) {
        let isValid = true;
        let errorMessages = [];

        // Validasi semua field yang required
        $(this).find('[required]').each(function() {
            if ($(this).val().trim() === '') {
                isValid = false;
                let label = $(this).closest('.form-group').find('label').text().replace('*', '').trim();
                errorMessages.push(`- ${label} harus diisi`);
            }
        });

        // Validasi file dokumen (hanya untuk pengajuan baru)
        <?php if (!$edit_mode): ?>
        if ($('#file_dokumen')[0].files.length === 0) {
            isValid = false;
            errorMessages.push('- File dokumen harus diupload');
        }
        <?php endif; ?>

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Data Tidak Lengkap',
                html: '<div style="text-align: left; margin-left: 20px;">' + [...new Set(errorMessages)].join('<br>') + '</div>',
            });
        } else {
            // Show loading
            $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
        }
    });
});

function removeFile() {
    $('#file_dokumen').val('');
    $('#file-preview').hide();
}
</script>