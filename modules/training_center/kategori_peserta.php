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
            <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
                <div class="page-header">
                    <!-- judul halaman -->
                    <h4 class="page-title"><i class="fas fa-clone mr-2"></i> Kategori Peserta</h4>
                    <!-- breadcrumbs -->
                    <ul class="breadcrumbs">
                        <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a href="?module=training_center">Training Center</a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a>Data Kategori Peserta</a></li>
                    </ul>
                </div>
                <div class="ml-md-auto py-2 py-md-0">
                    <!-- button entri data -->
                    <a href="#" class="btn btn-success btn-round" data-toggle="modal" data-target="#modalEntri">
                        <span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Entri Data
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Entri Data Baru -->
    <div class="modal fade" id="modalEntri" tabindex="-1" role="dialog" aria-labelledby="modalEntriLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold" id="modalEntriLabel">
                        <i class="fas fa-plus mr-2"></i>Tambah Kategori Peserta
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="modules/training_center/proses_kategori.php" method="POST" id="formEntriKategori">
                    <div class="modal-body">
                        <!-- Form Input Nama Kategori -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="nama_kategori_entri" class="form-label font-weight-semibold">
                                        <i class="fas fa-graduation-cap mr-1 text-primary"></i>Nama Kategori <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" autocomplete="off"
                                        class="form-control" 
                                        id="nama_kategori_entri" 
                                        name="nama_kategori" 
                                        placeholder="Masukkan nama kategori peserta"
                                        required>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>Contoh: Karyawan, Mahasiswa, Umum, dll.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-round" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Batal
                        </button>
                        <button type="submit" name="simpan" class="btn btn-success btn-round">
                            <i class="fas fa-save mr-1"></i>Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <!-- judul tabel -->
                <div class="card-title">Data Kategori Peserta</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <!-- tabel untuk menampilkan data dari database -->
                    <table id="basic-datatables" class="display table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Kategori Peserta</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // variabel untuk nomor urut tabel
                            $no = 1;
                            // sql statement untuk menampilkan data dari tabel "tbl_kategori"
                            $query = mysqli_query($mysqli, "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC")
                                                            or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
                            // ambil data hasil query
                            while ($data = mysqli_fetch_assoc($query)) { ?>
                                <!-- tampilkan data -->
                                <tr>
                                    <td width="30" class="text-center"><?php echo $no++; ?></td>
                                    <td width="250"><?php echo $data['nama_kategori']; ?></td>
                                    <td width="70" class="text-center">
                                        
                                        <!-- Button Edit Kategori -->
                                        <a href="#" class="btn btn-icon btn-round btn-success btn-sm" data-toggle="modal" data-target="#modalUbah<?php echo $data['id_kategori']; ?>" data-tooltip="tooltip" title="Ubah">
                                            <i class="fas fa-pencil-alt fa-sm"></i>
                                        </a>

                                        <!-- Modal Edit Kategori -->
                                        <div class="modal fade" id="modalUbah<?php echo $data['id_kategori']; ?>" tabindex="-1" role="dialog" aria-labelledby="modalUbahLabel<?php echo $data['id_kategori']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <!-- Modal Header -->
                                                    <div class="modal-header bg-warning text-white">
                                                        <h5 class="modal-title font-weight-bold" id="modalUbahLabel<?php echo $data['id_kategori']; ?>">
                                                            <i class="fas fa-edit mr-2"></i>Edit Kategori Peserta
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>

                                                    <form action="modules/training_center/proses_kategori.php" method="POST" id="formKategori<?php echo $data['id_kategori']; ?>">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id_kategori" value="<?php echo $data['id_kategori']; ?>">

                                                            <!-- Form Edit Nama Kategori -->
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="nama_kategori<?php echo $data['id_kategori']; ?>" class="form-label font-weight-semibold">
                                                                            <i class="fas fa-graduation-cap mr-1 text-primary"></i>Nama Kategori <span class="text-danger">*</span>
                                                                        </label>
                                                                        <input type="text" autocomplete="off"
                                                                            class="form-control" 
                                                                            id="nama_kategori<?php echo $data['id_kategori']; ?>" 
                                                                            name="nama_kategori" 
                                                                            value="<?php echo htmlspecialchars($data['nama_kategori']); ?>" 
                                                                            placeholder="Masukkan nama kategori peserta"
                                                                            required>
                                                                        <small class="form-text text-muted">
                                                                            <i class="fas fa-info-circle mr-1"></i>Masukkan nama kategori peserta yang baru
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Tampil Data Sebelumnya -->
                                                            <div class="alert alert-light border mt-3">
                                                                <h6 class="mb-2 font-weight-semibold">
                                                                    <i class="fas fa-history mr-2 text-info"></i>Data Sebelumnya
                                                                </h6>
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="d-flex align-items-center">
                                                                            <strong class="mr-2">Kategori:</strong>
                                                                            <span class="badge badge-info badge-lg"><?php echo htmlspecialchars($data['nama_kategori']); ?></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Modal Footer -->
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-round" data-dismiss="modal">
                                                                <i class="fas fa-times mr-1"></i>Batal
                                                            </button>
                                                            <button type="submit" name="ubah" class="btn btn-warning btn-round">
                                                                <i class="fas fa-save mr-1"></i>Update Data
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- button hapus data -->
                                        <!-- <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapus<?php echo $data['id_kategori']; ?>" data-tooltip="tooltip" data-placement="top" title="Hapus">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </a> -->

                                        <!-- Modal Hapus -->
                                        <!-- <div class="modal fade" id="modalHapus<?php echo $data['id_kategori']; ?>" tabindex="-1" role="dialog" aria-labelledby="modalHapusLabel<?php echo $data['id_kategori']; ?>" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title font-weight-bold" id="modalHapusLabel<?php echo $data['id_kategori']; ?>">
                                                            <i class="fas fa-trash mr-2"></i>Hapus Kategori Peserta
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <div class="mb-3">
                                                            <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                                                        </div>
                                                        <h6 class="mb-3">Konfirmasi Penghapusan</h6>
                                                        <p>Anda yakin ingin menghapus kategori peserta:</p>
                                                        <div class="alert alert-light border">
                                                            <strong class="text-danger"><?php echo htmlspecialchars($data['nama_kategori']); ?></strong>
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-info-circle mr-1"></i>Data yang sudah dihapus tidak dapat dikembalikan
                                                        </small>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-round" data-dismiss="modal">
                                                            <i class="fas fa-times mr-1"></i>Batal
                                                        </button>
                                                        <form action="modules/training_center/proses_kategori.php" method="POST">
                                                        <input type="hidden" name="id_kategori" value="<?php echo $data['id_kategori']; ?>">
                                                        <button type="submit" name="hapus" class="btn btn-danger btn-round">
                                                            <i class="fas fa-trash mr-1"></i>Ya, Hapus
                                                        </button>
                                                    </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> -->
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    

    
<?php } ?>