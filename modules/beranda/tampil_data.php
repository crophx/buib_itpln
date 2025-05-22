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
            <div class="page-header">
                <!-- judul halaman -->
                <h4 class="page-title"><i class="fas fa-home mr-2"></i> Beranda</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=beranda">Beranda</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="row row-card-no-pd align-items-center p-2 p-sm-4">
            <div class="col-lg-3 mb-4 mb-lg-0">
                <img class="img-fluid" src="assets/img/bg-dashboard.jpg">
            </div>
            <div class="col-lg-9 heroes-text px-xl-5">
                <h2 class="text-success mb-2">Selamat datang kembali <span><?php echo $_SESSION['nama_user']; ?></span> di <span> Website <?php echo $data['nama']; ?></span></h2>
                <h4 class="text-muted mb-4">Dashsboard Kerja adalah Sistem Informasi Pengelolaan Arsip Digital Dokumen dan juga antarmuka atau alat digital yang dirancang untuk memberikan karyawan ikhtisar komprehensif tentang berbagai aspek pekerjaan, kinerja, dan informasi organisasi yang relevan</h4>
                <a href="?module=arsip" class="btn btn-success btn-round px-4 mr-2 mb-3 mb-lg-0">
                    <span class="btn-label"><i class="fas fa-folder-open mr-2"></i></span> Arsip Dokumen
                </a>
            </div>
        </div>

        <?php
        // pengecekan hak akses untuk menampilkan konten sesuai dengan hak akses
        // jika hak akses = Administrator, tampilkan konten
        if ($_SESSION['hak_akses'] == 'Administrator') { ?>
            <!-- tampilkan informasi jumlah data arsip, jenis, dan pengguna -->
            <div class="row mt-5">
                <!-- menampilkan informasi jumlah data arsip dokumen -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-teal bubble-shadow-small">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Arsip Dokumen</p>
                                        <?php
                                        // sql statement untuk menampilkan jumlah data pada tabel "tbl_arsip"
                                        $query = mysqli_query($mysqli, "SELECT id_arsip FROM tbl_arsip")
                                                                        or die('Ada kesalahan pada query jumlah data arsip : ' . mysqli_error($mysqli));
                                        // ambil jumlah data dari hasil query
                                        $jumlah_arsip = mysqli_num_rows($query);
                                        ?>
                                        <!-- tampilkan data -->
                                        <h4 class="card-title"><?php echo number_format($jumlah_arsip, 0, '', '.'); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan informasi jumlah data jenis dokumen -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-warning bubble-shadow-small">
                                        <i class="fas fa-clone"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Jenis Dokumen</p>
                                        <?php
                                        // sql statement untuk menampilkan jumlah data pada tabel "tbl_jenis"
                                        $query = mysqli_query($mysqli, "SELECT id_jenis FROM tbl_jenis")
                                                                        or die('Ada kesalahan pada query jumlah data jenis : ' . mysqli_error($mysqli));
                                        // ambil jumlah data dari hasil query
                                        $jumlah_jenis = mysqli_num_rows($query);
                                        ?>
                                        <!-- tampilkan data -->
                                        <h4 class="card-title"><?php echo number_format($jumlah_jenis, 0, '', '.'); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan informasi jumlah data pengguna aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-success bubble-shadow-small">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Pengguna Aplikasi</p>
                                        <?php
                                        // sql statement untuk menampilkan jumlah data pada tabel "tbl_user"
                                        $query = mysqli_query($mysqli, "SELECT id_user FROM tbl_user")
                                                                        or die('Ada kesalahan pada query jumlah data user : ' . mysqli_error($mysqli));
                                        // ambil jumlah data dari hasil query
                                        $jumlah_user = mysqli_num_rows($query);
                                        ?>
                                        <!-- tampilkan data -->
                                        <h4 class="card-title"><?php echo number_format($jumlah_user, 0, '', '.'); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan button Pusat Bisnis aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center" style="color: steelblue;">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <h4 class="card-tittle">Pusat Bisnis</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan button Bagian Kerja Sama (BKS) aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center bubble-shadow-small">
                                        <i class="fas fa-leaf" style="color: green;"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <h4 class="card-tittle">Bagian Kerja Sama (BKS)</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan button Bagian Kerja Internasional (BKI) aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center bubble-shadow-small">
                                        <i class="fas fa-camera" style="color: violet;"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <h4 class="card-tittle">Bagian Kerja Internasional (BKI)</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan button LEMTERA aplikasi -->
                <div class="col-sm-12 col-md-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center bubble-shadow-small">
                                        <i class="fas fa-clone" style="color: antiquewhite;"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <h4 class="card-tittle">LEMTERA</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        // jika hak akses selain Administrator, tampilkan konten
        else { ?>
            <!-- tampilkan informasi jumlah data arsip, dan jenis -->
            <div class="row mt-5">
                <!-- menampilkan informasi jumlah data arsip dokumen -->
                <div class="col-sm-12 col-md-6">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-teal bubble-shadow-small">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Arsip Dokumen</p>
                                        <?php
                                        // sql statement untuk menampilkan jumlah data pada tabel "tbl_arsip"
                                        $query = mysqli_query($mysqli, "SELECT id_arsip FROM tbl_arsip")
                                                                        or die('Ada kesalahan pada query jumlah data arsip : ' . mysqli_error($mysqli));
                                        // ambil jumlah data dari hasil query
                                        $jumlah_arsip = mysqli_num_rows($query);
                                        ?>
                                        <!-- tampilkan data -->
                                        <h4 class="card-title"><?php echo number_format($jumlah_arsip, 0, '', '.'); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- menampilkan informasi jumlah data jenis dokumen -->
                <div class="col-sm-12 col-md-6">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-warning bubble-shadow-small">
                                        <i class="fas fa-clone"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ml-3 ml-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Jenis Dokumen</p>
                                        <?php
                                        // sql statement untuk menampilkan jumlah data pada tabel "tbl_jenis"
                                        $query = mysqli_query($mysqli, "SELECT id_jenis FROM tbl_jenis")
                                                                        or die('Ada kesalahan pada query jumlah data jenis : ' . mysqli_error($mysqli));
                                        // ambil jumlah data dari hasil query
                                        $jumlah_jenis = mysqli_num_rows($query);
                                        ?>
                                        <!-- tampilkan data -->
                                        <h4 class="card-title"><?php echo number_format($jumlah_jenis, 0, '', '.'); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <!-- tampilkan informasi jumlah data arsip per jenis dokumen -->
        <div class="card mt-2">
            <div class="card-header">
                <!-- judul tabel -->
                <div class="card-title"><i class="fas fa-info-circle mr-2"></i> Jumlah Arsip Per Jenis Dokumen</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <!-- tabel untuk menampilkan data dari database -->
                    <table id="basic-datatables" class="display table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">Jenis Dokumen</th>
                                <th class="text-center">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // variabel untuk nomor urut tabel
                            $no = 1;
                            // sql statement untuk menampilkan jumlah data dari tabel "tbl_arsip" dan tabel "tbl_jenis", dikelompokan berdasarkan "jenis_dokumen"
                            $query = mysqli_query($mysqli, "SELECT COUNT(*) as jumlah, b.nama_jenis 
                                                            FROM tbl_arsip as a INNER JOIN tbl_jenis as b ON a.jenis_dokumen=b.id_jenis 
                                                            GROUP BY a.jenis_dokumen ORDER BY jumlah DESC")
                                                            or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
                            // ambil data hasil query
                            while ($data = mysqli_fetch_assoc($query)) { ?>
                                <!-- tampilkan data -->
                                <tr>
                                    <td width="50" class="text-center"><?php echo $no++; ?></td>
                                    <td width="200"><?php echo $data['nama_jenis']; ?></td>
                                    <td width="80" class="text-center"><?php echo $data['jumlah']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php } ?>