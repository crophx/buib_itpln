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
                    <h4 class="page-title"><i class="fas fa-clone mr-2"></i> Jenis Dokumen</h4>
                    <!-- breadcrumbs -->
                    <ul class="breadcrumbs">
                        <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a href="?module=jenis">Jenis</a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a>Data</a></li>
                    </ul>
                </div>
                <div class="ml-md-auto py-2 py-md-0">
                    <!-- button entri data -->
                    <a href="?module=form_entri_jenis" class="btn btn-success btn-round">
                        <span class="btn-label"><i class="fa fa-plus mr-2"></i></span> Entri Data
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <!-- judul tabel -->
                <div class="card-title">Data Jenis Dokumen</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <!-- tabel untuk menampilkan data dari database -->
                    <table id="basic-datatables" class="display table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Jenis Dokumen</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // variabel untuk nomor urut tabel
                            $no = 1;
                            // sql statement untuk menampilkan data dari tabel "tbl_jenis"
                            $query = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis ASC")
                                                            or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
                            // ambil data hasil query
                            while ($data = mysqli_fetch_assoc($query)) { ?>
                                <!-- tampilkan data -->
                                <tr>
                                    <td width="30" class="text-center"><?php echo $no++; ?></td>
                                    <td width="250"><?php echo $data['nama_jenis']; ?></td>
                                    <td width="70" class="text-center">
                                        <div>
                                            <!-- button ubah data -->
                                            <a href="?module=form_ubah_jenis&id=<?php echo $data['id_jenis']; ?>" class="btn btn-icon btn-round btn-success btn-sm mr-md-1" data-tooltip="tooltip" data-placement="top" title="Ubah">
                                                <i class="fas fa-pencil-alt fa-sm"></i>
                                            </a>
                                            <!-- button hapus data -->
											<a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal" data-target="#modalHapus<?php echo $data['id_jenis']; ?>" data-tooltip="tooltip" data-placement="top" title="Hapus">
												<i class="fas fa-trash fa-sm"></i>
											</a>
											<!-- Modal Hapus -->
											<div class="modal fade" id="modalHapus<?php echo $data['id_jenis']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
												<div class="modal-dialog" role="document">
													<div class="modal-content">
														<div class="modal-header">
															<h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-trash mr-2"></i>Hapus Data Jenis Dokumen</h5>
														</div>
														<div class="modal-body text-left">Anda yakin ingin menghapus data jenis dokumen <strong><?php echo $data['nama_jenis']; ?></strong>?</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-default btn-round" data-dismiss="modal">Batal</button>
															<a href="modules/jenis/proses_hapus.php?id=<?php echo $data['id_jenis']; ?>" class="btn btn-danger btn-round">Ya, Hapus</a>
														</div>
													</div>
												</div>
											</div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
		$(document).ready(function() {
			// dapatkan parameter URL
			let queryString = window.location.search;
			let urlParams = new URLSearchParams(queryString);
			// ambil data dari URL
			let pesan = urlParams.get('pesan');
			let jenis = urlParams.get('jenis');

			// menampilkan pesan sesuai dengan proses yang dijalankan
			// jika pesan = 1
			if (pesan === '1') {
				// tampilkan pesan sukses simpan data
				$.notify({
					title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
					message: 'Data jenis dokumen berhasil disimpan.'
				}, {
					type: 'success'
				});
			}
			// jika pesan = 2
			else if (pesan === '2') {
				// tampilkan pesan sukses ubah data
				$.notify({
					title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
					message: 'Data jenis dokumen berhasil diubah.'
				}, {
					type: 'success'
				});
			}
			// jika pesan = 3
			else if (pesan === '3') {
				// tampilkan pesan sukses hapus data
				$.notify({
					title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
					message: 'Data jenis dokumen berhasil dihapus.'
				}, {
					type: 'success'
				});
			}
			// jika pesan = 4
			else if (pesan === '4') {
				// tampilkan pesan gagal simpan data
				$.notify({
					title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
					message: 'Jenis dokumen <strong>' + jenis + '</strong> sudah ada. Silahkan ganti jenis dokumen yang Anda masukan.'
				}, {
					type: 'danger'
				});
			}
			// jika pesan = 5
			else if (pesan === '5') {
				// tampilkan pesan gagal hapus data
				$.notify({
					title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
					message: 'Data jenis dokumen tidak bisa dihapus karena sudah digunakan pada data Arsip Dokumen.'
				}, {
					type: 'danger'
				});
			}
		});
	</script>
<?php } ?>