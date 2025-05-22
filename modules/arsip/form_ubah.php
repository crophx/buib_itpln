<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    // alihkan ke halaman error 404
    header('location: 404.html');
}
// jika file di include oleh file lain, tampilkan isi file
else {
    // mengecek data GET "id_arsip"
    if (isset($_GET['id'])) {
        // ambil data GET dari button ubah
        $id_arsip = $_GET['id'];

        // sql statement untuk menampilkan data dari tabel "tbl_arsip" dan "tbl_jenis" berdasarkan "id_arsip"
        $query = mysqli_query($mysqli, "SELECT a.id_arsip, a.jenis_dokumen, a.bulan_tahun, a.tahun_anggaran, a.dipa, a.dokumen_elektronik, b.nama_jenis 
                                        FROM tbl_arsip as a INNER JOIN tbl_jenis as b ON a.jenis_dokumen=b.id_jenis 
                                        WHERE a.id_arsip='$id_arsip'")
                                        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
        // ambil data hasil query
        $data = mysqli_fetch_assoc($query);
        // pisahkan data bulan dan tahun sebelum ditampilkan ke form ubah
        $bulan_tahun = $data['bulan_tahun'];
        $dataArray   = explode(" ", $bulan_tahun);
        $bulan       = $dataArray[0];
        $tahun       = $dataArray[1];
    }
?>
    <div class="panel-header">
        <div class="page-inner py-4">
            <div class="page-header">
                <!-- judul halaman -->
                <h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Arsip Dokumen</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=arsip">Arsip</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Ubah</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <!-- judul form -->
                <div class="card-title">Ubah Data Arsip Dokumen</div>
            </div>
            <!-- form ubah data -->
            <form action="modules/arsip/proses_ubah.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="card-body">
                    <input type="hidden" name="id_arsip" value="<?php echo $data['id_arsip']; ?>">

                    <div class="form-group col-lg-4">
                        <label>Jenis Dokumen <span class="text-danger">*</span></label>
                        <select name="jenis_dokumen" class="form-control select2-single" autocomplete="off" required>
                            <option value="<?php echo $data['jenis_dokumen']; ?>"><?php echo $data['nama_jenis']; ?></option>
							<option disabled value="">-- Pilih --</option>
                            <?php
                            // sql statement untuk menampilkan data dari tabel "tbl_jenis"
                            $query_jenis = mysqli_query($mysqli, "SELECT * FROM tbl_jenis ORDER BY nama_jenis ASC")
                                                                  or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
                            // ambil data hasil query
                            while ($data_jenis = mysqli_fetch_assoc($query_jenis)) {
                                // tampilkan data
                                echo "<option value='$data_jenis[id_jenis]'>$data_jenis[nama_jenis]</option>";
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Jenis dokumen tidak boleh kosong.</div>
                    </div>

                    <div class="row">
                        <div class="col-lg-2 pr-lg-0">
                            <div class="form-group">
                                <label>Bulan <span class="text-danger">*</span></label>
                                <select name="bulan" class="form-control select2-single" autocomplete="off" required>
                                    <option value="<?php echo $bulan; ?>"><?php echo $bulan; ?></option>
                                    <option disabled value="">-- Pilih --</option>
                                    <option value="Januari">Januari</option>
                                    <option value="Februari">Februari</option>
                                    <option value="Maret">Maret</option>
                                    <option value="April">April</option>
                                    <option value="Mei">Mei</option>
                                    <option value="Juni">Juni</option>
                                    <option value="Juli">Juli</option>
                                    <option value="Agustus">Agustus</option>
                                    <option value="September">September</option>
                                    <option value="Oktober">Oktober</option>
                                    <option value="November">November</option>
                                    <option value="Desember">Desember</option>
                                </select>
                                <div class="invalid-feedback">Bulan tidak boleh kosong.</div>
                            </div>
                        </div>

                        <div class="col-lg-2 pr-lg-0">
                            <div class="form-group">
                                <label>Tahun <span class="text-danger">*</span></label>
                                <input type="text" name="tahun" class="form-control" maxlength="4" autocomplete="off" onKeyPress="return goodchars(event,'0123456789',this)" value="<?php echo $tahun; ?>" required>
                                <div class="invalid-feedback">Tahun tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-4">
                        <hr class="mt-2 mb-1">
                    </div>

                    <div class="row">
                        <div class="col-lg-2 pr-lg-0">
                            <div class="form-group">
                                <label>Tahun Anggaran <span class="text-danger">*</span></label>
                                <input type="text" name="tahun_anggaran" class="form-control" maxlength="4" autocomplete="off" onKeyPress="return goodchars(event,'0123456789',this)" value="<?php echo $data['tahun_anggaran']; ?>" required>
                                <div class="invalid-feedback">Tahun anggaran tidak boleh kosong.</div>
                            </div>
                        </div>

                        <div class="col-lg-2 pr-lg-0">
                            <div class="form-group">
                                <label>DIPA <span class="text-danger">*</span></label>
                                <select name="dipa" class="form-control select2-single" autocomplete="off" required>
                                    <option value="<?php echo $data['dipa']; ?>"><?php echo $data['dipa']; ?></option>
                                    <option disabled value="">-- Pilih --</option>
                                    <option value="01">01</option>
                                    <option value="03">03</option>
                                </select>
                                <div class="invalid-feedback">DIPA tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-4">
                        <hr class="mt-2 mb-1">
                    </div>

                    <div class="form-group col-lg-4">
                        <label>Dokumen Elektronik <span class="text-danger">*</span></label>
                        <input type="file" accept=".pdf" name="dokumen_elektronik" class="form-control" autocomplete="off">
                        <a href="?module=tampil_detail_arsip&id=<?php echo $data['id_arsip']; ?>"><i class="far fa-file-alt fa-8x text-warning mt-3 mb-2"></i></a>
                        <small class="form-text text-primary pt-1">
                            Keterangan : <br>
                            - Tipe file yang bisa diunggah adalah *.pdf. <br>
                            - Ukuran file yang bisa diunggah maksimal 10 Mb. <br>
                            - Kosongkan Dokumen Elektronik jika tidak diubah.
                        </small>
                    </div>
                </div>
                <div class="card-action">
                    <!-- button simpan data -->
                    <input type="submit" name="simpan" value="Simpan" class="btn btn-success btn-round pl-4 pr-4 mr-2">
                    <!-- button kembali ke halaman tampil data -->
                    <a href="?module=arsip" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php } ?>