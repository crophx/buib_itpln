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
        <div class="page-inner py-4">
            <div class="page-header">
                <!-- judul halaman -->
                <h4 class="page-title"><i class="fas fa-plus-circle mr-2"></i>Input Data Realisasi</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=buib">Data Realisasi</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Entri</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <div class="header-content" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <!--Judul Form-->
                    <div class="card-title" style="margin: 0;">
                        <i class="fas fa-edit mr-2"></i>Entri Data Realisasi
                    </div>
                    <!-- button kembali -->
                    <div class="button-container">
                        <a href="?module=buib" class="btn btn-secondary btn-round">
                            <span class="btn-label"><i class="fa fa-arrow-left mr-2"></i></span> Kembali
                        </a>
                    </div>
                </div>
            </div>
            <!--Form Entri Data-->
            <form action="modules/buib/proses_simpan.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Deputy BUIB <span class="text-danger">*</span></label>
                                <select name="deputy_buib" class="form-control select2-single" autocomplete="off" required>
                                    <option selected disabled value="">-- Pilih Deputy --</option>
                                    <?php
                                    // sql statement untuk menampilkan data deputy dari tabel program atau tabel terpisah
                                    $deputy_query = mysqli_query($mysqli, "SELECT DISTINCT nama_program FROM tbl_program_buib WHERE nama_program LIKE '%Deputy%' ORDER BY nama_program ASC")
                                                                or die('Error pada query deputy: ' . mysqli_error($mysqli));
                                    // ambil data hasil query
                                    while ($deputy_data = mysqli_fetch_assoc($deputy_query)) {
                                        // tampilkan data
                                        echo "<option value='".$deputy_data['nama_program']."'>".$deputy_data['nama_program']."</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Deputy BUIB tidak boleh kosong.</div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Kegiatan <span class="text-danger">*</span></label>
                                <!-- PERBAIKAN: Ubah name dari "text" menjadi "kegiatan" -->
                                <input name="kegiatan" class="form-control" autocomplete="off" required>
                                <div class="invalid-feedback">Kegiatan tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Realisasi Nominal <span class="text-danger">*</span></label>
                                <input type="text" name="realisasi_nominal" class="form-control" autocomplete="off" onKeyPress="return goodchars(event,'0123456789',this)" required>
                                <div class="invalid-feedback">Realisasi nominal tidak boleh kosong.</div>
                                <small class="form-text text-muted">Masukkan angka tanpa titik atau koma</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Detail Tanggal <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="tgl_input" class="form-control datepicker" placeholder="dd/mm/yyyy" autocomplete="off" value="<?php echo date('d/m/Y'); ?>" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fa fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="invalid-feedback">Tanggal input tidak boleh kosong.</div>
                                <small class="form-text text-muted">Pilih tanggal input dengan mengklik pada kalender</small>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Bulan <span class="text-danger">*</span></label>
                                <select name="bulan" class="form-control" autocomplete="off" required>
                                    <option selected disabled value="">-- Pilih Bulan --</option>
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
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Tahun <span class="text-danger">*</span></label>
                                <select name="tahun" class="form-control" autocomplete="off" required>
                                    <option selected disabled value="">-- Pilih Tahun --</option>
                                    <?php
                                    $tahun_sekarang = date('Y');
                                    for ($i = $tahun_sekarang - 2; $i <= $tahun_sekarang + 2; $i++) {
                                        $selected = ($i == $tahun_sekarang) ? 'selected' : '';
                                        echo "<option value='$i' $selected>$i</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Tahun tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        
                    </div>

                    <!-- Info Box untuk menampilkan persentase otomatis -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-info" id="persentase-info" style="display: none;">
                                <i class="fas fa-info-circle mr-2"></i>
                                <span id="persentase-text"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-action">
                    <!-- button simpan data -->
                    <input type="submit" name="simpan_realisasi" value="Simpan" class="btn btn-success btn-round pl-4 pr-4 mr-2">
                    <!-- button reset form -->
                    <input type="reset" value="Reset" class="btn btn-warning btn-round pl-4 pr-4 mr-2">
                    <!-- button kembali ke halaman tampil data -->
                    <a href="?module=buib" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Script untuk menginisialisasi datepicker dan kalkulasi persentase -->
    <script>
        $(document).ready(function() {
            // Inisialisasi datepicker
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                orientation: 'bottom auto',
                language: 'id'
            });

            // Inisialisasi Select2
            $('.select2-single').select2({
                theme: 'bootstrap',
                placeholder: '-- Pilih --',
                allowClear: true
            });

            // Event listener untuk input realisasi
            $('input[name="realisasi_nominal"]').on('keyup', function() {
                hitungPersentase();
            });

            // Format angka dengan pemisah ribuan saat input
            $('input[name="realisasi_nominal"]').on('blur', function() {
                var value = $(this).val().replace(/[^0-9]/g, '');
                if (value) {
                    var formatted = parseInt(value).toLocaleString('id-ID');
                    $(this).val(formatted);
                }
                hitungPersentase();
            });

            // Hapus format saat focus untuk memudahkan edit
            $('input[name="realisasi_nominal"]').on('focus', function() {
                var value = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(value);
            });
        });

        // Fungsi untuk membatasi input hanya angka
        function goodchars(event, goodchars, field) {
            var key, keychar;
            key = event.keyCode;
            if (key == null) return true;
            
            // untuk backspace dan delete
            if (key == 0 || key == 8 || key == 9 || key == 13 || key == 27) return true;
            
            keychar = String.fromCharCode(key);
            keychar = keychar.toLowerCase();
            goodchars = goodchars.toLowerCase();
            
            // cek apakah karakter yang diinput termasuk dalam daftar yang diizinkan
            if (goodchars.indexOf(keychar) != -1)
                return true;
            return false;
        }
    </script>
    
<?php }
?>