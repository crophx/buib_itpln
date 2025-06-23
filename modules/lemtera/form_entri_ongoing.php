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
                <h4 class="page-title"><i class="fas fa-plus-circle mr-2"></i>Input Data Ongoing</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=lemtera">Data Ongoing</a></li>
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
                        <i class="fas fa-edit mr-2"></i>Entri Data Ongoing
                    </div>
                    <!-- button kembali -->
                    <div class="button-container">
                        <a href="?module=lemtera" class="btn btn-secondary btn-round">
                            <span class="btn-label"><i class="fa fa-arrow-left mr-2"></i></span> Kembali
                        </a>
                    </div>
                </div>
            </div>
            <!--Form Entri Data-->
            <form action="modules/lemtera/proses_simpan.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Nama Program <span class="text-danger">*</span></label>
                                <input name="nama_program" class="form-control" autocomplete="off" required>
                                <div class="invalid-feedback">Nama program tidak boleh kosong.</div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Entity Lemtera<span class="text-danger">*</span></label>
                                <select name="entity_lemtera" class="form-control select2-single" autocomplete="off" required>
                                    <option selected disabled value="">-- Pilih Kategori --</option>
                                    <?php
                                    // Query untuk mengambil data kategori dari tbl_kategori
                                    $query_kategori = mysqli_query($mysqli, "SELECT id_kategori, nama_entity_lemtera FROM tbl_kategori ORDER BY nama_entity_lemtera ASC") 
                                                    or die('Error pada query kategori: '. mysqli_error($mysqli));
                                    
                                    while ($data_kategori = mysqli_fetch_assoc($query_kategori)) {
                                        echo "<option value='".$data_kategori['id_kategori']."'>".$data_kategori['nama_entity_lemtera']."</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Kategori Lemtera tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Ongoing Nominal <span class="text-danger">*</span></label>
                                <input type="text" name="ongoing_nominal" class="form-control" autocomplete="off" onKeyPress="return goodchars(event,'0123456789',this)" required>
                                <div class="invalid-feedback">Ongoing nominal tidak boleh kosong.</div>
                                <small class="form-text text-muted">Masukkan angka tanpa titik atau koma</small>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Tanggal Surat <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="tgl_surat" class="form-control datepicker" placeholder="dd/mm/yyyy" autocomplete="off" value="<?php echo date('d/m/Y'); ?>" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fa fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="invalid-feedback">Tanggal surat tidak boleh kosong.</div>
                                <small class="form-text text-muted">Pilih tanggal surat dengan mengklik pada kalender</small>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status_lemtera" class="form-control select2-single" autocomplete="off" required>
                                    <option selected disabled value="">-- Pilih Status --</option>
                                    <?php
                                    // Query untuk mengambil data status dari tbl_status
                                    $query_status = mysqli_query($mysqli, "SELECT id_status, nama_status FROM tbl_status ORDER BY nama_status ASC") 
                                                  or die('Error pada query status: '. mysqli_error($mysqli));
                                    
                                    while ($data_status = mysqli_fetch_assoc($query_status)) {
                                        echo "<option value='".$data_status['id_status']."'>".$data_status['nama_status']."</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Status tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Keterangan Program</label>
                                <textarea name="keterangan_program" class="form-control" rows="4" placeholder="Masukkan keterangan program (opsional)"></textarea>
                                <small class="form-text text-muted">Keterangan tambahan mengenai program Lemtera yang sedang berjalan</small>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box untuk menampilkan informasi preview ongoing -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-warning" id="ongoing-info" style="display: none;">
                                <i class="fas fa-clock mr-2"></i>
                                <span id="ongoing-text"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Progress Box -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-info" id="progress-info" style="display: none;">
                                <i class="fas fa-tasks mr-2"></i>
                                <strong>Status Program:</strong> Program sedang dalam tahap pelaksanaan
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-action">
                    <!-- button simpan data -->
                    <input type="submit" name="simpan_ongoing" value="Simpan" class="btn btn-success btn-round pl-4 pr-4 mr-2">
                    <!-- button reset form -->
                    <input type="reset" value="Reset" class="btn btn-warning btn-round pl-4 pr-4 mr-2">
                    <!-- button kembali ke halaman tampil data -->
                    <a href="?module=lemtera" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Script untuk menginisialisasi datepicker dan format nominal -->
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

            // Format angka dengan pemisah ribuan saat input ongoing nominal
            $('input[name="ongoing_nominal"]').on('blur', function() {
                var value = $(this).val().replace(/[^0-9]/g, '');
                if (value) {
                    var formatted = parseInt(value).toLocaleString('id-ID');
                    $(this).val(formatted);
                    tampilkanInfoOngoing(value);
                }
            });

            // Hapus format saat focus untuk memudahkan edit
            $('input[name="ongoing_nominal"]').on('focus', function() {
                var value = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(value);
                $('#ongoing-info').hide();
            });

            // Event listener untuk menampilkan info ongoing
            $('input[name="ongoing_nominal"]').on('keyup', function() {
                var value = $(this).val().replace(/[^0-9]/g, '');
                if (value && value.length > 3) {
                    tampilkanInfoOngoing(value);
                } else {
                    $('#ongoing-info').hide();
                }
            });

            
        });

        // Fungsi untuk menampilkan informasi ongoing
        function tampilkanInfoOngoing(value) {
            if (value) {
                var formatted = parseInt(value).toLocaleString('id-ID');
                var ongoingText = 'Nilai program yang sedang berjalan: Rp ' + formatted;
                $('#ongoing-text').text(ongoingText);
                $('#ongoing-info').show();
            }
        }

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

        // Fungsi untuk reset form
        $('input[type="reset"]').on('click', function() {
            $('#ongoing-info').hide();
            $('#progress-info strong').next().text('Program sedang dalam tahap pelaksanaan');
        });
    </script>
    
<?php }
?>