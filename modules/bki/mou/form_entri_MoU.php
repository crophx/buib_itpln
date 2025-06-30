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
                <h4 class="page-title"><i class="fas fa-folder-open mr-2"></i>Form Arsip Dokumen MoU</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=bki">Form MoU</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Input MoU</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <!-- judul form -->
                <div class="card-title">Entri MoU</div>
            </div>
            <!-- form entri data -->
            <form action="modules/bki/mou/proses_simpan_mou.php" method="post" enctype="multipart/form-data"
                class="needs-validation" novalidate>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 pr-lg-0">
                            <div class="col-md-10">
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
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Jenis Dokumen <span class="text-danger">*</span></label>

                            <select name="jenis_dokumen_id" id="jenis_dokumen_select" class="form-control select2-single"
                                autocomplete="off" required>
                                <option selected disabled value="">-- Pilih Jenis Dokumen --</option>
                                <?php
                                $query_jenis = mysqli_query($mysqli, "SELECT * FROM tbl_jenis_dokumen_bki ORDER BY nama_dokumen ASC");
                                while ($data_jenis = mysqli_fetch_assoc($query_jenis)) {
                                    echo "<option value='{$data_jenis['id']}'>{$data_jenis['nama_dokumen']} ({$data_jenis['kode_singkat']})</option>";
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">Jenis dokumen tidak boleh kosong.</div>

                            <div id="peringatan_pks" class="alert alert-warning mt-2" role="alert" style="display: none;">
                                <strong>Perhatian!</strong> Untuk jenis dokumen selain MoU, silakan input melalui <bold>form
                                    input PKS.</bold>.
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-lg-4">
                        <div class="form-group">
                            <label>PIC (Bagian / Prodi) <span class="text-danger">*</span></label>
                            <select name="pic_bagian_id" class="form-control select2-single" autocomplete="off" required>
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
                                <label>Tanggal Penandatanganan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_penandatanganan" class="form-control"
                                    value="<?php echo date('Y-m-d'); ?>" required>
                                <div class="invalid-feedback">Tanggal tidak boleh kosong.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Jangka Waktu (Tahun) <span class="text-danger">*</span></label>
                                <input type="number" name="jangka_waktu_tahun" class="form-control" placeholder="Contoh: 5"
                                    autocomplete="off" required>
                                <div class="invalid-feedback">Jangka waktu tidak boleh kosong.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Link Dokumen MOU<span class="text-danger">*</span></label>
                        <input type="url" name="link_dokumen_MoU" class="form-control" placeholder="https://..." required>
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
                    <input type="submit" name="simpan" id="tombol_simpan" value="Simpan"
                        class="btn btn-success btn-round pl-4 pr-4 mr-2">
                    <!-- button kembali ke halaman tampil data -->
                    <a href="?module=bki" class="btn btn-default btn-round pl-4 pr-4">Batal</a>
                </div>
            </form>
        </div>
    </div>

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
<?php } ?>