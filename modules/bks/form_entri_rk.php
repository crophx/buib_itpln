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
                <h4 class="page-title"><i class="fas fa-plus-circle mr-2"></i>Input Data Rencana kegiatan</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=bks">Data Rencana Kegiatan</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Entri</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <div class="header-content"
                    style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <!--Judul Form-->
                    <div class="card-title" style="margin: 0;">
                        <i class="fas fa-edit mr-2"></i>Entri Data Rencana Kegiatan
                    </div>
                    <!-- button kembali -->
                    <div class="button-container">
                        <a href="?module=bks" class="btn btn-secondary btn-round">
                            <span class="btn-label"><i class="fa fa-arrow-left mr-2"></i></span> Kembali
                        </a>
                    </div>
                </div>
            </div>
            <!--Form Entri Data-->
            <form action="modules/bks/proses_simpan.php" method="post" enctype="multipart/form-data"
                class="needs-validation" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Nama Mitra <span class="text-danger">*</span></label>

                                <select name="mitra_id" class="form-control select2-single" required>
                                    <option value="" disabled selected>-- Pilih Mitra --</option>
                                    <?php
                                    // Query untuk mengambil semua data dari tabel master mitra
                                    $query_mitra = mysqli_query($mysqli, "SELECT id, nama_mitra FROM tbl_mitra_bki ORDER BY nama_mitra ASC");

                                    // Looping untuk membuat setiap opsi dropdown
                                    while ($data_mitra = mysqli_fetch_assoc($query_mitra)) {
                                        // Atribut 'value' diisi dengan ID, teks yang tampil diisi dengan NAMA
                                        echo "<option value='{$data_mitra['id']}'>{$data_mitra['nama_mitra']}</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Mitra tidak boleh kosong.</div>
                                <small class="form-text text-primary pt-1">
                                    Jika pilihan mitra tidak ada, silakan <a href="?module=mitra_bki"><i>[klik
                                            disini]</i></a>
                                    untuk menambahkan.
                                </small>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Bentuk Kerjasama</label> <span class="text-danger">*</span></label>
                                <select name="bentuk_kerjasama" class="form-control select2-single" required>
                                    <option value="" disabled selected>-- Pilih Bentuk Kerjasama --</option>
                                    <option value="Akademik">Akademik</option>
                                    <option value="Non-Akademik">Non Akademik</option>
                                </select>
                                <div class="invalid-feedback">Klasifikasi Mitra tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Tanggal Input Rencana<span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="tgl_input" class="form-control datepicker"
                                        placeholder="dd/mm/yyyy" autocomplete="off" value="<?php echo date('d/m/Y'); ?>"
                                        required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fa fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="invalid-feedback">Tanggal input tidak boleh kosong.</div>
                                <small class="form-text text-muted">Pilih tanggal input dengan mengklik pada
                                    kalender</small>
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

                    <div class="form-group">
                        <label>Klasifikasi Mitra<span class="text-danger">*</span></label>
                        <select name="klasifikasi_mitra" class="form-control select2-single" required>
                            <option value="" disabled selected>-- Pilih Klasifikasi --</option>
                            <option value="Industri">Industri</option>
                            <option value="PLN Group">PLN Group</option>
                            <option value="Pemerintahan">Pemerintahan</option>
                            <option value="Start-Up">Start-Up</option>
                            <option value="Perusahaan Multinasional">Perusahaan Multinasional</option>
                            <option value="Perguruan Tinggi">Perguruan Tinggi</option>
                            <option value="Institusi/Organisasi Multilateral">Institusi/Organisasi Multilateral</option>
                        </select>
                        <div class="invalid-feedback">Klasifikasi Mitra tidak boleh kosong.</div>
                    </div>

                    <!-- Info Box untuk menampilkan persentase otomatis -->
                    <div class="form-group">
                        <label>Keterangan <span class="text-danger">*</span></label>
                        <input name="keterangan" class="form-control" autocomplete="off" required>
                        <div class="invalid-feedback">Kegiatan tidak boleh kosong.</div>
                    </div>
                </div>

                <div class="card-action">
                    <!-- button simpan data -->
                    <input type="submit" name="simpan_rencana" value="Simpan"
                        class="btn btn-success btn-round pl-4 pr-4 mr-2">
                    <!-- button reset form -->
                    <input type="reset" value="Reset" class="btn btn-warning btn-round pl-4 pr-4 mr-2">
                    <!-- button kembali ke halaman tampil data -->
                    <a href="?module=bks" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Script untuk menginisialisasi datepicker dan kalkulasi persentase -->
    <script>
        $(document).ready(function () {
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
            $('input[name="target_nominal"]').on('keyup', function () {
                hitungPersentase();
            });

            // Format angka dengan pemisah ribuan saat input
            $('input[name="target_nominal"]').on('blur', function () {
                var value = $(this).val().replace(/[^0-9]/g, '');
                if (value) {
                    var formatted = parseInt(value).toLocaleString('id-ID');
                    $(this).val(formatted);
                }
                hitungPersentase();
            });

            // Hapus format saat focus untuk memudahkan edit
            $('input[name="target_nominal"]').on('focus', function () {
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