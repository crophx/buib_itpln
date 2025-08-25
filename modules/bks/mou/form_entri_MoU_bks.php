<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('location: 404.html');
} else { ?>
    <div class="panel-header">
        <div class="page-inner py-4">
            <div class="page-header">
                <!--Judul Halaman-->
                <h4 class="page-title"><i class="fas fa-folder-open mr-2"></i>Input Dokumen BKS</h4>
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=bks">Data BKS</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item">Entri</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <!--Judul Form-->
                <div class="card-title">Entri MoU Bagian Kerjasama</div>
            </div>
            <!--Form Entri Data-->
            <form action="modules/bks/mou/proses_simpan.php" method="post" enctype="multipart/form-data"
                class="needs-validation" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Mitra <span class="text-danger">*</span></label>
                                <select name="mitra_id" class="form-control select2-single" required>
                                    <option selected disabled value="">-- Pilih Mitra --</option>
                                    <?php
                                    $query_mitra = mysqli_query($mysqli, "SELECT * FROM tbl_mitra_bki ORDER BY nama_mitra ASC");
                                    while ($data_mitra = mysqli_fetch_assoc($query_mitra)) {
                                        echo "<option value='{$data_mitra['id']}'>{$data_mitra['nama_mitra']}</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Mitra tidak boleh kosong.</div>
                                <small class="form-text text-primary pt-1">
                                    Jika pilihan mitra tidak ada, silakan <a href="?module=mitra_bki">klik disini</a>
                                </small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Bentuk Kerjasama <span class="text-danger">*</span></label>
                                <select name="bentuk_kerjasama_bks" class="form-control select2-single" required>
                                    <option value="" disabled selected>-- Pilih Bentuk Kerjasama --</option>
                                    <option value="Akademik">Akademik</option>
                                    <option value="Non-Akademik">Non-Akademik</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Jenis Dokumen <span class="text-danger">*</span></label>

                                <select name="jenis_dokumen_bks" id="jenis_dokumen_select"
                                    class="form-control select2-single" autocomplete="off" required>
                                    <option value="" disabled selected>-- Pilih Jenis Dokumen --</option>
                                    <option value="MoU">MoU (Memorandum of Understanding)</option>
                                    <option value="PKS">PKS (Perjanjian Kerja Sama)</option>
                                    <option value="IA">IA (Implementation Arrangement)</option>
                                </select>
                                <div class="invalid-feedback">Jenis dokumen tidak boleh kosong.</div>

                                <div id="peringatan_pks" class="alert alert-warning mt-2" role="alert"
                                    style="display: none;">
                                    <strong>Perhatian!</strong> Untuk jenis dokumen selain MoU, silakan input melalui
                                    <strong>
                                        form pada halamannya masing-masing
                                    </strong>.
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>PIC (Bagian / Prodi) <span class="text-danger">*</span></label>
                                <select name="pic_bagian_id" class="form-control select2-single" autocomplete="off"
                                    required>
                                    <option selected disabled value="">-- Pilih PIC --</option>
                                    <?php
                                    $query_pic = mysqli_query($mysqli, "SELECT * FROM tbl_pic_bagian ORDER BY nama_bagian ASC");
                                    while ($data_pic = mysqli_fetch_assoc($query_pic)) {
                                        echo "<option value='{$data_pic['id']}'>{$data_pic['nama_bagian']}</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">PIC tidak boleh kosong.</div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Klasifikasi Mitra <span class="text-danger">*</span></label>
                                <select name="klasifikasi_mitra" class="form-control select2-single" required>
                                    <option value="" disabled selected>-- Pilih Klasifikasi --</option>
                                    <option value="Industri">Industri</option>
                                    <option value="PLN Group">PLN Group</option>
                                    <option value="Pemerintahan">Pemerintahan</option>
                                    <option value="Start-Up">Start-Up</option>
                                    <option value="Perusahaan Multinasional">Perusahaan Multinasional</option>
                                    <option value="Perguruan Tinggi">Perguruan Tinggi</option>
                                    <option value="Institusi/Organisasi Multilateral">Institusi/Organisasi Multilateral
                                    </option>
                                </select>
                                <div class="invalid-feedback">Klasifikasi Mitra tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tentang (Judul MoU) <span class="text-danger">*</span></label>
                        <textarea name="tentang" class="form-control" rows="3" required></textarea>
                        <div class="invalid-feedback">Judul MoU tidak boleh kosong.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nomor Dokumen MoU <span class="text-danger">*</span></label>
                                <input type="text" name="no_dokumen" class="form-control" autocomplete="off" required>
                                <div class="invalid-feedback">Nomor Dokumen tidak boleh kosong.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Penandatanganan (tanggal awal)<span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_awal" class="form-control"
                                    value="<?php echo date('Y-m-d'); ?>" required>
                                <div class="invalid-feedback">Tanggal tidak boleh kosong.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Jangka Berakhir <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_akhir" class="form-control" placeholder="Contoh: 5"
                                    autocomplete="off" required>
                                <div class="invalid-feedback">Tanggal tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Link Dokumen MOU<span class="text-danger">*</span></label>
                        <input type="url" name="link_dokumen_bks" class="form-control" placeholder="https://..." required>
                        <!-- <div class="invalid-feedback">Link Dokumen tidak boleh kosong.</div> -->
                        <small class="form-text text-primary pt-1">
                            Keterangan : <br>
                            - Tipe file yang bisa diunggah adalah *.pdf yang sudah dimasukkan kedalam drive. <br>
                            - Tidak memasukkan link folder. <br>
                            - Hanya satu file yang di upload pada drive.
                        </small>
                    </div>
                    <div class="form-group col-md-4">
                        <hr class="mt-2 mb-1">
                    </div>
                </div>

                <div class="card-action">
                    <!-- button simpan data -->
                    <input type="submit" name="simpan" value="Simpan" class="btn btn-success btn-round pl-4 pr-4 mr-2">
                    <!-- button kembali ke halaman tampil data -->
                    <a href="?module=bks" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Script untuk menginisialisasi datepicker -->
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
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function () {
            // Dengarkan event 'change' pada dropdown jenis dokumen
            $('#jenis_dokumen_select').on('change', function () {
                // Ambil teks dari opsi yang sedang dipilih (misal: "Memorandum of Understanding (MoU)")
                var teksPilihan = $(this).find('option:selected').text();

                // Cek apakah teks tersebut mengandung kata "MoU". 
                // .toUpperCase() digunakan agar pencarian tidak case-sensitive (MoU, mou, Mou akan terdeteksi)
                if (teksPilihan.toUpperCase().includes('MOU')) {
                    // Jika YA, ini adalah MoU
                    // Sembunyikan pesan peringatan
                    $('#peringatan_pks').hide();
                    // Aktifkan kembali tombol simpan
                    $('#tombol_simpan').prop('disabled', false);
                } else {
                    // Jika TIDAK, ini dianggap PKS atau lainnya
                    // Tampilkan pesan peringatan
                    $('#peringatan_pks').show();
                    // Nonaktifkan tombol simpan agar tidak bisa diklik
                    $('#tombol_simpan').prop('disabled', true);
                }
            });
        });
    </script>


    <?php
}
?>