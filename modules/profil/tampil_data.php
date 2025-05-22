<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    // alihkan ke halaman error 404
    header('location: 404.html');
}
// jika file di include oleh file lain, tampilkan isi file
else {
    // sql statement untuk menampilkan data dari tabel "tbl_profil"
    $query = mysqli_query($mysqli, "SELECT * FROM tbl_profil")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    // ambil data hasil query
    $data = mysqli_fetch_assoc($query);
?>
    <div class="panel-header">
        <div class="page-inner py-45">
            <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
                <div class="page-header">
                    <!-- judul halaman -->
                    <h4 class="page-title"><i class="fas fa-cog mr-2"></i> Profil Instansi</h4>
                    <!-- breadcrumbs -->
                    <ul class="breadcrumbs">
                        <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a>Profil</a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a>Detail</a></li>
                    </ul>
                </div>
                <div class="ml-md-auto py-2 py-md-0">
                    <!-- button ubah data -->
                    <a href="?module=form_ubah_profil" class="btn btn-success btn-round">
                        <span class="btn-label"><i class="fas fa-pencil-alt mr-2"></i></span> Ubah Profil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <!-- judul form -->
                        <div class="card-title">Profil Instansi</div>
                    </div>
                    <!-- detail data -->
                    <div class="card-body">
                        <table class="table table-striped">
                            <tr>
                                <td width="120">Nama Instansi</td>
                                <td width="10">:</td>
                                <td><?php echo $data['nama']; ?></td>
                            </tr>
                            <tr>
                                <td>Alamat</td>
                                <td>:</td>
                                <td><?php echo $data['alamat']; ?></td>
                            </tr>
                            <tr>
                                <td>Telepon</td>
                                <td>:</td>
                                <td><?php echo $data['telepon']; ?></td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>:</td>
                                <td><?php echo $data['email']; ?></td>
                            </tr>
                            <tr>
                                <td>Website</td>
                                <td>:</td>
                                <td><?php echo $data['website']; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <?php
                        // mengecek data logo
                        // jika data "logo" tidak ada di database
                        if (is_null($data['logo'])) { ?>
                            <!-- tampilkan foto default -->
                            <img style="max-height:328px" src="assets/img/no_image.png" class="img-fluid" alt="Logo">
                        <?php
                        }
                        // jika data "logo" ada di database
                        else { ?>
                            <!-- tampilkan logo dari database -->
                            <img style="max-height:328px" src="assets/img/<?php echo $data['logo']; ?>" class="img-fluid" alt="Logo">
                        <?php } ?>
                    </div>
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

            // menampilkan pesan sesuai dengan proses yang dijalankan
            // jika pesan = 1
            if (pesan === '1') {
                // tampilkan pesan gagal ubah data
                $.notify({
                    title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
                    message: 'Profil instansi berhasil diubah.'
                }, {
                    type: 'success'
                });
            }
        });
    </script>
<?php } ?>