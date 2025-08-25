<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    // alihkan ke halaman error 404
    header('location: 404.html');
    // alihkan ke halaman error 404
    header('location: 404.html');
}


// jika file di include oleh file lain, tampilkan isi file
else {
    // pengecekan hak akses untuk menampilkan konten sesuai dengan hak akses
    // jika hak akses = SuperAdmin atau hak akses = Pimpinan, atau hak akses = SekretarisPimpinan, tampilkan konten
    if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'BKS'])) {

        // Fungsi helper untuk menghitung sisa waktu
        function hitung_masa_berlaku($tanggal_awal, $tanggal_akhir)
        {
            if (empty($tanggal_awal) || empty($tanggal_akhir) || $tanggal_awal == '0000-00-00' || $tanggal_akhir == '0000-00-00') {
                return '<span class="badge badge-secondary">Tidak Terdefinisi</span>';
            }

            $awal = new DateTime($tanggal_awal);
            $akhir = new DateTime($tanggal_akhir);

            // Jika akhir lebih kecil dari awal
            if ($akhir < $awal) {
                return '<span class="badge badge-danger">Tanggal tidak valid</span>';
            }

            $selisih = $awal->diff($akhir);

            if ($selisih->y > 0)
                return '<span class="badge badge-success">' . $selisih->y . ' Tahun ' . $selisih->m . ' Bulan ' . $selisih->d . ' Hari</span>';
            elseif ($selisih->m > 0)
                return '<span class="badge badge-warning">' . $selisih->m . ' Bulan ' . $selisih->d . ' Hari</span>';
            else
                return '<span class="badge badge-info">' . $selisih->d . ' Hari</span>';
        }

        ?>

        <?php

        // ==========================================================
        // KODE PHP UNTUK MENGAMBIL DATA CHART
        // ==========================================================

        // 1. Data untuk Chart Jumlah Dokumen (MoU, PKS, IA)
        $total_mou = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(id) as total FROM tbl_mou_bks"))['total'];
        $total_pks = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(id) as total FROM tbl_pks_bks"))['total'];
        $total_ia = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COUNT(id) as total FROM tbl_i_a"))['total'];

        // 2. Data untuk Chart Bentuk Kerjasama (Akademik vs Non-Akademik)
        $data_bentuk = [
            'Akademik' => 0,
            'Non-Akademik' => 0
        ];

        // a. Hitung dari tabel tbl_mou_bks
        $query_mou = mysqli_query($mysqli, "SELECT bentuk_kerjasama_bks, COUNT(id) as total FROM tbl_mou_bks GROUP BY bentuk_kerjasama_bks");
        while ($row = mysqli_fetch_assoc($query_mou)) {
            if (isset($data_bentuk[$row['bentuk_kerjasama_bks']])) {
                $data_bentuk[$row['bentuk_kerjasama_bks']] += $row['total'];
            }
        }

        // b. Hitung dari tabel tbl_pks_bks
        $query_pks = mysqli_query($mysqli, "SELECT bentuk_kerjasama_bks, COUNT(id) as total FROM tbl_pks_bks GROUP BY bentuk_kerjasama_bks");
        while ($row = mysqli_fetch_assoc($query_pks)) {
            if (isset($data_bentuk[$row['bentuk_kerjasama_bks']])) {
                $data_bentuk[$row['bentuk_kerjasama_bks']] += $row['total'];
            }
        }

        // c. Hitung dari tabel tbl_i_a
        $query_ia = mysqli_query($mysqli, "SELECT bentuk_kerjasama_bks, COUNT(id) as total FROM tbl_i_a GROUP BY bentuk_kerjasama_bks");
        while ($row = mysqli_fetch_assoc($query_ia)) {
            if (isset($data_bentuk[$row['bentuk_kerjasama_bks']])) {
                $data_bentuk[$row['bentuk_kerjasama_bks']] += $row['total'];
            }
        }
        // Sekarang variabel $data_bentuk berisi total gabungan dari ketiga tabel.

        // 3. Data untuk Chart Klasifikasi Mitra
        $query_klasifikasi = mysqli_query($mysqli, "SELECT klasifikasi_mitra, COUNT(id) as total FROM tbl_mou_bks GROUP BY klasifikasi_mitra");
        $data_klasifikasi = [];
        while ($row = mysqli_fetch_assoc($query_klasifikasi)) {
            $data_klasifikasi[$row['klasifikasi_mitra']] = $row['total'];
        }
        ?>
        <style>
            .truncate-text {
                max-width: 250px;
                /* Atur lebar maksimum kolom */
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                text-align: left !important;
            }

            .row-eq-height {
                display: flex;
                flex-wrap: wrap;
            }

            .row-eq-height .card {
                height: 90%;
            }
        </style>

        <div class="panel-header">
            <div class="page-inner py-45">
                <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
                    <div class="page-header">
                        <!-- judul halaman -->
                        <h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Bagian Kerjasama (BKS)</h4>
                        <!-- breadcrumbs -->
                        <ul class="breadcrumbs">
                            <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                            <li class="separator"><i class="flaticon-right-arrow"></i></li>
                            <li class="nav-item"><a href="?module=beranda">Beranda</a></li>
                            <li class="separator"><i class="flaticon-right-arrow"></i></li>
                            <li class="nav-item"><a>BKS</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menampilkan Chart -->
        <div class="page-inner mt--5">
            <div class="row row-eq-height">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Jumlah Dokumen per Jenis</div>
                        </div>
                        <div class="card-body"><canvas id="chartJenisDokumen"></canvas></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Kerjasama Berdasarkan Bentuk</div>
                        </div>
                        <div class="card-body"><canvas id="chartBentukKerjasama"></canvas></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Kerjasama per Klasifikasi Mitra</div>
                        </div>
                        <div class="card-body"><canvas id="chartKlasifikasiMitra"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel MoU -->
        <div class="page-inner mt--5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">Data Memorandum of Understanding (MoU)</div>

                    <div>
                        <!-- Button tambah mitra -->
                        <a href="?module=mitra_bki" class="btn btn-warning btn-round">
                            <span class="btn-label"><i class="fas fa-users mr-2"></i></span> Mitra
                        </a>
                        <!-- Button tambah jenis dokumen -->
                        <a href="?module=jenis_dokumen_bki" class="btn btn-primary btn-round ml-2">
                            <span class="btn-label"><i class="fas fa-sitemap mr-2"></i></span> Jenis Dokumen
                        </a>
                        <!-- Button tambah dokumen -->
                        <a href="?module=form_entri_MoU_bks" class="btn btn-success btn-round ml-2">
                            <span class="btn-label"><i class="fas fa-edit mr-2"></i></span> Input MoU
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <!-- tabel untuk menampilkan data dari database -->
                        <table id="mou-datatables" class="display table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th class="text-center">No. Dokumen</th>
                                    <th class="text-center">Bentuk Kerjasama</th>
                                    <th class="text-center">Jenis Dokumen</th>
                                    <th class="text-center">Mitra</th>
                                    <th class="text-center">Tentang</th>
                                    <th class="text-center">Siswa Waktu</th>
                                    <th class="text-center">Tanggal Penandatanganan</th>
                                    <th class="text-center">PIC</th>
                                    <th class="text-center">Link Dokumen</th>
                                    <th class="text-center">Klasifikasi Mitra</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // variabel untuk nomor urut tabel
                                $no = 1;
                                // sql statement untuk menampilkan data dari tabel "tbl_bki" dan "tbl_jenis_dokumen_bki" dan "tbl_mitra_bki"
                                $query = mysqli_query($mysqli, "SELECT
                                    bks.id,
                                    bks.mitra_id,
                                    bks.no_dokumen,
                                    bks.tentang,
                                    bks.tanggal_awal,
                                    bks.tanggal_akhir,
                                    bks.link_dokumen_bks,
                                    bks.bentuk_kerjasama_bks,
                                    bks.jenis_dokumen_bks,
                                    bks.klasifikasi_mitra,
                                    bks.pic_bagian_id,
                                    
                                    -- Mengambil nama_mitra dan kategori dari tabel tbl_mitra_bki
                                    mitra.nama_mitra,
                                    
                                    -- Mengambil nama_bagian dari tbl_pic_bagian
                                    pic.nama_bagian AS pic_nama
                                FROM
                                    tbl_mou_bks AS bks
                                LEFT JOIN
                                    tbl_mitra_bki AS mitra ON bks.mitra_id = mitra.id
                                LEFT JOIN
                                    tbl_pic_bagian AS pic ON bks.pic_bagian_id = pic.id
                                ORDER BY
                                    bks.id DESC;")
                                    or die('Ada kesalahan pada query tampil data BKS: ' . mysqli_error($mysqli));

                                // ambil data hasil query
                                while ($data_mou = mysqli_fetch_assoc($query)) {
                                    $masa_berlaku = date('d/m/Y', strtotime($data_mou['tanggal_awal'])) . ' - ' . date('d/m/Y', strtotime($data_mou['tanggal_akhir']));
                                    $sisa_waktu = hitung_masa_berlaku($data_mou['tanggal_awal'], $data_mou['tanggal_akhir']);
                                    ?>
                                    <!-- tampilkan data -->
                                    <tr>

                                        <td width="30" class="text-center"><?php echo $no++; ?>
                                        </td>
                                        <td width="80" class="text-center">
                                            <?php echo $data_mou['no_dokumen']; ?>
                                        </td>
                                        <td width="80" class="text-center">
                                            <?php echo $data_mou['bentuk_kerjasama_bks']; ?>
                                        </td>
                                        <td width="100" class="text-center"><?php echo $data_mou['jenis_dokumen_bks']; ?>
                                        </td>
                                        <td width="100" class="text-center"><?php echo $data_mou['nama_mitra']; ?>
                                        </td>
                                        <td width="80" class="text-center truncate-text"><?php echo $data_mou['tentang']; ?>
                                        </td>
                                        <td width="80" class="text-center"><?php echo $sisa_waktu; ?></td>
                                        <td width="80" class="text-center"><?php echo $data_mou['tanggal_awal']; ?></td>
                                        <td width="80" class="text-center truncate-text"><?php echo $data_mou['pic_nama']; ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($data_mou['link_dokumen_bks'])): ?>
                                                <a href="<?php echo htmlspecialchars($data_mou['link_dokumen_bks']); ?>" target="_blank"
                                                    class="btn btn-info btn-sm" title="Lihat Dokumen"><i class="fas fa-link"></i></a>
                                            <?php else: ?>-<?php endif; ?>
                                        </td>
                                        <td width="80" class="text-center"><?php echo $data_mou['klasifikasi_mitra']; ?></td>

                                        <td width="80" class="text-center">
                                            <div>
                                                <!-- Button Ubah -->
                                                <a href="#" class="btn btn-icon btn-round btn-success btn-sm mr-md-1"
                                                    data-toggle="modal" data-target="#modalUbahMoU<?php echo $data_mou['id']; ?>"
                                                    title="Ubah Data">
                                                    <i class="fas fa-pencil-alt fa-sm"></i>
                                                </a>

                                                <!-- modalUbah -->
                                                <div class="modal fade" id="modalUbahMoU<?php echo $data_mou['id']; ?>"
                                                    tabindex="-1" role="dialog" aria-labelledby="modalUbahLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <form action="modules/bks/mou/proses_ubah.php" method="post">
                                                                <div class="modal-header btn-success">
                                                                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Ubah
                                                                        Data Dokumen BKS</h5>
                                                                    <button type="button" class="close" data-dismiss="modal"
                                                                        aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="id"
                                                                        value="<?php echo $data_mou['id']; ?>">

                                                                    <div class=" form-group">
                                                                        <label>No Dokumen MoU</label>
                                                                        <input type="text" name="no_dokumen" class="form-control"
                                                                            placeholder=""
                                                                            value="<?php echo htmlspecialchars($data_mou['no_dokumen']); ?>"
                                                                            selected readonly>
                                                                    </div>

                                                                    <div class=" row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Klasifikasi Mitra <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="klasifikasi_mitra"
                                                                                    class="form-control select2-single" required>
                                                                                    <option value="" disabled>-- Pilih Klasifikasi
                                                                                        --</option>
                                                                                    <?php
                                                                                    $klasifikasi_options = ['Industri', 'PLN Group', 'Pemerintahan', 'Start-Up', 'Perusahaan Multinasional', 'Perguruan Tinggi', 'Institusi/Organisasi Multilateral'];
                                                                                    foreach ($klasifikasi_options as $option) {
                                                                                        $selected = ($data_mou['klasifikasi_mitra'] == $option) ? 'selected' : '';
                                                                                        echo "<option value='{$option}' {$selected}>{$option}</option>";
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group ">
                                                                                <label>Mitra<span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="mitra_id"
                                                                                    class="form-control select2-single" required>
                                                                                    <option
                                                                                        value="<?php echo $data_mou['mitra_id']; ?>"
                                                                                        selected>
                                                                                        <?php echo htmlspecialchars($data_mou['nama_mitra']); ?>
                                                                                    </option>
                                                                                    <?php
                                                                                    $query_mitra_modal = mysqli_query($mysqli, "SELECT id, nama_mitra FROM tbl_mitra_bki ORDER BY nama_mitra ASC");
                                                                                    while ($mitra_modal = mysqli_fetch_assoc($query_mitra_modal)) {
                                                                                        if ($mitra_modal['id'] != $data_mou['mitra_id']) {
                                                                                            echo "<option value='{$mitra_modal['id']}'>{$mitra_modal['nama_mitra']}</option>";
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Tentang (Judul Kerjasama) <span
                                                                                class="text-danger">*</span></label>
                                                                        <textarea name="tentang" class="form-control" rows="3"
                                                                            required><?php echo htmlspecialchars($data_mou['tentang']); ?></textarea>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Bentuk Kerjasama <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="bentuk_kerjasama_bks"
                                                                                    class="form-control" required>
                                                                                    <option value="Akademik" <?php echo ($data_mou['bentuk_kerjasama_bks'] == 'Akademik') ? 'selected' : ''; ?>>Akademik</option>
                                                                                    <option value="Non-Akademik" <?php echo ($data_mou['bentuk_kerjasama_bks'] == 'Non-Akademik') ? 'selected' : ''; ?>>Non-Akademik</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Jenis Dokumen <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="jenis_dokumen_bks"
                                                                                    class="form-control" required>
                                                                                    <option value="MoU" <?php echo ($data_mou['jenis_dokumen_bks'] == 'MoU') ? 'selected' : ''; ?>>MoU</option>

                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Tanggal Awal</label>
                                                                                <input type="date" name="tanggal_awal"
                                                                                    class="form-control"
                                                                                    value="<?php echo isset($data_mou['tanggal_awal']) ? date('Y-m-d', strtotime($data_mou['tanggal_awal'])) : ''; ?>"
                                                                                    required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Tanggal Akhir</label>
                                                                                <input type="date" name="tanggal_akhir"
                                                                                    class="form-control"
                                                                                    value="<?php echo isset($data_mou['tanggal_akhir']) ? date('Y-m-d', strtotime($data_mou['tanggal_akhir'])) : ''; ?>"
                                                                                    required>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>PIC (Bagian / Prodi) <span
                                                                                class="text-danger">*</span></label>
                                                                        <select name="pic_bagian_id"
                                                                            class="form-control select2-single" required>
                                                                            <option
                                                                                value="<?php echo $data_mou['pic_bagian_id']; ?>"
                                                                                selected>
                                                                                <?php echo htmlspecialchars($data_mou['pic_nama']); ?>
                                                                            </option>
                                                                            <?php
                                                                            $query_pic_modal = mysqli_query($mysqli, "SELECT id, nama_bagian FROM tbl_pic_bagian ORDER BY nama_bagian ASC");
                                                                            while ($pic_modal = mysqli_fetch_assoc($query_pic_modal)) {
                                                                                if ($pic_modal['id'] != $data_mou['pic_bagian_id']) {
                                                                                    echo "<option value='{$pic_modal['id']}'>{$pic_modal['nama_bagian']}</option>";
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Link Dokumen</label>
                                                                        <input type="url" name="link_dokumen_bks"
                                                                            class="form-control" placeholder="https://"
                                                                            value="<?php echo htmlspecialchars($data_mou['link_dokumen_bks']); ?>">
                                                                    </div>

                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-default btn-round"
                                                                        data-dismiss="modal">Batal</button>
                                                                    <input type="submit" name="simpan" value="Simpan Perubahan"
                                                                        class="btn btn-success btn-round">
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Button Hapus -->
                                                <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#modalHapus<?php echo $data_mou['id']; ?>" data-tooltip="tooltip"
                                                    data-placement="top" title="Hapus"> <i class="fas fa-trash fa-sm"></i>
                                                </a>
                                                <!-- modalHapus -->
                                                <div class="modal fade" id="modalHapus<?php echo $data_mou['id']; ?>" tabindex="-1"
                                                    role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-sm" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalHapusLabel"><i
                                                                        class="fas fa-trash mr-2"></i> Hapus Data</h5>
                                                            </div>
                                                            <div class="modal-body text-left">
                                                                Anda yakin ingin menghapus dokumen dengan nomor MoU
                                                                <strong>
                                                                    <?php echo htmlspecialchars($data_mou['no_dokumen']); ?>
                                                                </strong>? yang bermitra pada
                                                                <strong>
                                                                    <?php echo htmlspecialchars($data_mou['nama_mitra']); ?>
                                                                </strong>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default btn-round"
                                                                    data-dismiss="modal">Batal</button>
                                                                <a href="modules/bks/mou/proses_hapus.php?id=<?php echo $data_mou['id']; ?>"
                                                                    class="btn btn-danger btn-round">Ya, Hapus</a>
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

        <!-- Tabel PKS -->
        <div class="page-inner mt--5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">Daftar Dokumen PKS</div>
                    <div>
                        <!-- Button tambah mitra -->
                        <a href="?module=mitra_bki" class="btn btn-warning btn-round">
                            <span class="btn-label"><i class="fas fa-users mr-2"></i></span> Mitra
                        </a>
                        <!-- Button tambah jenis dokumen -->
                        <a href="?module=jenis_dokumen_bki" class="btn btn-primary btn-round ml-2">
                            <span class="btn-label"><i class="fas fa-sitemap mr-2"></i></span> Jenis Dokumen
                        </a>
                        <!-- Button tambah dokumen -->
                        <a href="?module=form_entri_pks_bks" class="btn btn-success btn-round ml-2">
                            <span class="btn-label"><i class="fas fa-edit mr-2"></i></span> Input PKS
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="pks-datatables" class="display table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th>No. Dokumen PKS</th>
                                    <th class="text-center">Bentuk Kerjasama</th>
                                    <th class="text-center">Tentang</th>
                                    <th class="text-center">Mitra</th>
                                    <th class="text-center">Tgl. TTD</th>
                                    <th class="text-center">Sisa Waktu</th>
                                    <th class="text-center">PIC</th>
                                    <th class="text-center">MoU Induk</th>
                                    <th class="text-center">Link Dokumen PKS</th>
                                    <th class="text-center">Klasifikasi Mitra</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($mysqli, "SELECT
                                    pks.id,
                                    pks.mitra_id,
                                    pks.no_dokumen,
                                    pks.bentuk_kerjasama_bks,
                                    pks.tentang,
                                    pks.tanggal_awal,
                                    pks.tanggal_akhir,
                                    pks.link_dokumen_pks,
                                    pks.klasifikasi_mitra,
                                    mitra.nama_mitra,
                                    pks.pic_bagian_id,
                                    pic.nama_bagian AS pic_nama,
                                    mou.no_dokumen AS mou_induk_nomor
                                FROM
                                    tbl_pks_bks AS pks
                                LEFT JOIN
                                    tbl_mitra_bki AS mitra ON pks.mitra_id = mitra.id
                                LEFT JOIN
                                    tbl_pic_bagian AS pic ON pks.pic_bagian_id = pic.id
                                LEFT JOIN
                                    tbl_mou_bks AS mou ON pks.mou_id     = mou.id
                                ORDER BY
                                    pks.id DESC;")
                                    or die('Ada kesalahan pada query tampil data PKS: ' . mysqli_error($mysqli));

                                while ($data_pks = mysqli_fetch_assoc($query)) {
                                    $masa_berlaku = date('d/m/Y', strtotime($data_pks['tanggal_awal'])) . ' - ' . date('d/m/Y', strtotime($data_pks['tanggal_akhir']));
                                    $sisa_waktu = hitung_masa_berlaku($data_pks['tanggal_awal'], $data_pks['tanggal_akhir']);
                                    ?>
                                    <tr>
                                        <td width="30" class="text-center"><?php echo $no++; ?></td>
                                        <td width="120"><?php echo htmlspecialchars($data_pks['no_dokumen']); ?></td>
                                        <td width="180"><?php echo htmlspecialchars($data_pks['bentuk_kerjasama_bks']); ?></td>
                                        <td class="truncate-text"><?php echo htmlspecialchars($data_pks['tentang']); ?></td>
                                        <td width="100" class="text-center"><?php echo htmlspecialchars($data_pks['nama_mitra']); ?>
                                        </td>
                                        <td width="200" class="text-center">
                                            <?php echo htmlspecialchars($data_pks['tanggal_awal']); ?>
                                        </td>
                                        <td width="100" class="text-center"><?php echo $sisa_waktu; ?></td>
                                        <td width="100" class="text-center"><?php echo htmlspecialchars($data_pks['pic_nama']); ?>
                                        </td>
                                        <td width="120" class="text-center">
                                            <?php echo !empty($data_pks['mou_induk_nomor']) ? htmlspecialchars($data_pks['mou_induk_nomor']) : '-'; ?>
                                        </td>
                                        <td width="120" class="text-center">
                                            <?php if (!empty($data_pks['link_dokumen_pks'])) { ?>
                                                <a href="<?php echo htmlspecialchars($data_pks['link_dokumen_pks']); ?>" target="_blank"
                                                    class="btn btn-icon btn-info btn-sm" data-toggle="tooltip" data-placement="top"
                                                    title="Lihat Dokumen">
                                                    <i class="fas fa-link"></i>
                                                </a>
                                            <?php } ?>
                                        </td>
                                        <td width="100" class="text-center">
                                            <?php echo htmlspecialchars($data_pks['klasifikasi_mitra']); ?>
                                        </td>
                                        <td width="80" class="text-center">
                                            <div>
                                                <!-- Button Ubah -->
                                                <a href="#" class="btn btn-icon btn-round btn-success btn-sm mr-md-1"
                                                    data-toggle="modal"
                                                    data-target="#modalUbahpks_bks<?php echo $data_pks['id']; ?>"
                                                    data-tooltip="tooltip" data-placement="top" title="Ubah"> <i
                                                        class="fas fa-pencil-alt fa-sm"></i>
                                                </a>
                                                <!-- modalUbah -->
                                                <div class="modal fade" id="modalUbahpks_bks<?php echo $data_pks['id']; ?>"
                                                    tabindex="-1" data-bs-toogle role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <form action="modules/bks/pks/proses_ubah.php" method="post">
                                                                <div class="modal-header btn-success">
                                                                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Ubah
                                                                        Data PKS</h5>
                                                                    <button type="button" class="close" data-dismiss="modal"
                                                                        aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="id"
                                                                        value="<?php echo $data_pks['id']; ?>">

                                                                    <div class="form-group">
                                                                        <label>Terhubung ke MoU Induk (Opsional)</label>
                                                                        <select name="mou_id" class="form-control select2-single">
                                                                            <option value="">-- Tidak terhubung / Mandiri --
                                                                            </option>
                                                                            <?php
                                                                            // Query untuk mengambil semua opsi MoU
                                                                            $query_mou_options = mysqli_query($mysqli, "SELECT 
                                                                                    mou.id, 
                                                                                    mou.no_dokumen, 
                                                                                    mitra.nama_mitra 
                                                                                FROM 
                                                                                    tbl_mou_bks AS mou
                                                                                LEFT JOIN 
                                                                                    tbl_mitra_bki AS mitra ON mou.mitra_id = mitra.id
                                                                                ORDER BY 
                                                                                    mitra.nama_mitra ASC");

                                                                            while ($mou_option = mysqli_fetch_assoc($query_mou_options)) {
                                                                                // Logika ini akan memilih MoU yang sudah terhubung
                                                                                $selected = (isset($data_ia['mou_id']) && $mou_option['id'] == $data_ia['mou_id']) ? 'selected' : '';

                                                                                echo "<option value='{$mou_option['id']}' {$selected}>
                                                                                            {$mou_option['no_dokumen']} - {$mou_option['nama_mitra']}
                                                                                        </option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                    <hr />

                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Mitra <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="mitra_id"
                                                                                    class="form-control select2-single" required>
                                                                                    <option
                                                                                        value="<?php echo $data_pks['mitra_id']; ?>"
                                                                                        selected>
                                                                                        <?php echo htmlspecialchars($data_pks['nama_mitra']); ?>
                                                                                    </option>
                                                                                    <?php
                                                                                    $query_mitra_modal = mysqli_query($mysqli, "SELECT id, nama_mitra FROM tbl_mitra_bki ORDER BY nama_mitra ASC");
                                                                                    while ($mitra_modal = mysqli_fetch_assoc($query_mitra_modal)) {
                                                                                        if ($mitra_modal['id'] != $data_pks['mitra_id']) {
                                                                                            echo "<option value='{$mitra_modal['id']}'>{$mitra_modal['nama_mitra']}</option>";
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>PIC (Bagian / Prodi) <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="pic_bagian_id"
                                                                                    class="form-control select2-single" required>
                                                                                    <option
                                                                                        value="<?php echo $data_pks['pic_bagian_id']; ?>"
                                                                                        selected>
                                                                                        <?php echo htmlspecialchars($data_pks['pic_nama']); ?>
                                                                                    </option>
                                                                                    <?php
                                                                                    $query_pic_modal = mysqli_query($mysqli, "SELECT id, nama_bagian FROM tbl_pic_bagian ORDER BY nama_bagian ASC");
                                                                                    while ($pic_modal = mysqli_fetch_assoc($query_pic_modal)) {
                                                                                        if ($pic_modal['id'] != $data_pks['pic_bagian_id']) {
                                                                                            echo "<option value='{$pic_modal['id']}'>{$pic_modal['nama_bagian']}</option>";
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Tanggal Awal</label>
                                                                                <input type="date" name="tanggal_awal"
                                                                                    class="form-control"
                                                                                    value="<?php echo isset($data_mou['tanggal_awal']) ? date('Y-m-d', strtotime($data_mou['tanggal_awal'])) : ''; ?>"
                                                                                    required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Tanggal Akhir</label>
                                                                                <input type="date" name="tanggal_akhir"
                                                                                    class="form-control"
                                                                                    value="<?php echo isset($data_mou['tanggal_akhir']) ? date('Y-m-d', strtotime($data_mou['tanggal_akhir'])) : ''; ?>"
                                                                                    required>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>PIC (Bagian / Prodi) <span
                                                                                class="text-danger">*</span></label>
                                                                        <select name="pic_bagian_id"
                                                                            class="form-control select2-single" required>
                                                                            <option
                                                                                value="<?php echo $data_mou['pic_bagian_id']; ?>"
                                                                                selected>
                                                                                <?php echo htmlspecialchars($data_mou['pic_nama']); ?>
                                                                            </option>
                                                                            <?php
                                                                            $query_pic_modal = mysqli_query($mysqli, "SELECT id, nama_bagian FROM tbl_pic_bagian ORDER BY nama_bagian ASC");
                                                                            while ($pic_modal = mysqli_fetch_assoc($query_pic_modal)) {
                                                                                if ($pic_modal['id'] != $data_mou['pic_bagian_id']) {
                                                                                    echo "<option value='{$pic_modal['id']}'>{$pic_modal['nama_bagian']}</option>";
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Link Dokumen</label>
                                                                        <input type="url" name="link_dokumen_bks"
                                                                            class="form-control" placeholder="https://"
                                                                            value="<?php echo htmlspecialchars($data_mou['link_dokumen_bks']); ?>">
                                                                    </div>

                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-default btn-round"
                                                                        data-dismiss="modal">Batal</button>
                                                                    <input type="submit" name="simpan" value="Simpan Perubahan"
                                                                        class="btn btn-success btn-round">
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Button Hapus -->
                                                <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#modalHapus<?php echo $data_mou['id']; ?>" data-tooltip="tooltip"
                                                    data-placement="top" title="Hapus"> <i class="fas fa-trash fa-sm"></i>
                                                </a>
                                                <!-- modalHapus -->
                                                <div class="modal fade" id="modalHapus<?php echo $data_mou['id']; ?>" tabindex="-1"
                                                    role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-sm" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalHapusLabel"><i
                                                                        class="fas fa-trash mr-2"></i> Hapus Data</h5>
                                                            </div>
                                                            <div class="modal-body text-left">
                                                                Anda yakin ingin menghapus dokumen dengan nomor MoU
                                                                <strong>
                                                                    <?php echo htmlspecialchars($data_mou['no_dokumen']); ?>
                                                                </strong>? yang bermitra pada
                                                                <strong>
                                                                    <?php echo htmlspecialchars($data_mou['nama_mitra']); ?>
                                                                </strong>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default btn-round"
                                                                    data-dismiss="modal">Batal</button>
                                                                <a href="modules/bks/mou/proses_hapus.php?id=<?php echo $data_mou['id']; ?>"
                                                                    class="btn btn-danger btn-round">Ya, Hapus</a>
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

        <!-- Tabel PKS -->
        <div class="page-inner mt--5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">Daftar Dokumen PKS</div>
                    <div>
                        <!-- Button tambah mitra -->
                        <a href="?module=mitra_bki" class="btn btn-warning btn-round">
                            <span class="btn-label"><i class="fas fa-users mr-2"></i></span> Mitra
                        </a>
                        <!-- Button tambah jenis dokumen -->
                        <a href="?module=jenis_dokumen_bki" class="btn btn-primary btn-round ml-2">
                            <span class="btn-label"><i class="fas fa-sitemap mr-2"></i></span> Jenis Dokumen
                        </a>
                        <!-- Button tambah dokumen -->
                        <a href="?module=form_entri_pks_bks" class="btn btn-success btn-round ml-2">
                            <span class="btn-label"><i class="fas fa-edit mr-2"></i></span> Input PKS
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="pks-datatables" class="display table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th>No. Dokumen PKS</th>
                                    <th class="text-center">Bentuk Kerjasama</th>
                                    <th class="text-center">Tentang</th>
                                    <th class="text-center">Mitra</th>
                                    <th class="text-center">Tgl. TTD</th>
                                    <th class="text-center">Sisa Waktu</th>
                                    <th class="text-center">PIC</th>
                                    <th class="text-center">MoU Induk</th>
                                    <th class="text-center">Link Dokumen PKS</th>
                                    <th class="text-center">Klasifikasi Mitra</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($mysqli, "SELECT
                                    pks.id,
                                    pks.mitra_id,
                                    pks.no_dokumen,
                                    pks.bentuk_kerjasama_bks,
                                    pks.tentang,
                                    pks.tanggal_awal,
                                    pks.tanggal_akhir,
                                    pks.link_dokumen_pks,
                                    pks.klasifikasi_mitra,
                                    mitra.nama_mitra,
                                    pks.pic_bagian_id,
                                    pic.nama_bagian AS pic_nama,
                                    mou.no_dokumen AS mou_induk_nomor
                                FROM
                                    tbl_pks_bks AS pks
                                LEFT JOIN
                                    tbl_mitra_bki AS mitra ON pks.mitra_id = mitra.id
                                LEFT JOIN
                                    tbl_pic_bagian AS pic ON pks.pic_bagian_id = pic.id
                                LEFT JOIN
                                    tbl_mou_bks AS mou ON pks.mou_id     = mou.id
                                ORDER BY
                                    pks.id DESC;")
                                    or die('Ada kesalahan pada query tampil data PKS: ' . mysqli_error($mysqli));

                                while ($data_pks = mysqli_fetch_assoc($query)) {
                                    $masa_berlaku = date('d/m/Y', strtotime($data_pks['tanggal_awal'])) . ' - ' . date('d/m/Y', strtotime($data_pks['tanggal_akhir']));
                                    $sisa_waktu = hitung_masa_berlaku($data_pks['tanggal_awal'], $data_pks['tanggal_akhir']);
                                    ?>
                                    <tr>
                                        <td width="30" class="text-center"><?php echo $no++; ?></td>
                                        <td width="120"><?php echo htmlspecialchars($data_pks['no_dokumen']); ?></td>
                                        <td width="180"><?php echo htmlspecialchars($data_pks['bentuk_kerjasama_bks']); ?></td>
                                        <td class="truncate-text"><?php echo htmlspecialchars($data_pks['tentang']); ?></td>
                                        <td width="100" class="text-center"><?php echo htmlspecialchars($data_pks['nama_mitra']); ?>
                                        </td>
                                        <td width="200" class="text-center">
                                            <?php echo htmlspecialchars($data_pks['tanggal_awal']); ?>
                                        </td>
                                        <td width="100" class="text-center"><?php echo $sisa_waktu; ?></td>
                                        <td width="100" class="text-center"><?php echo htmlspecialchars($data_pks['pic_nama']); ?>
                                        </td>
                                        <td width="120" class="text-center">
                                            <?php echo !empty($data_pks['mou_induk_nomor']) ? htmlspecialchars($data_pks['mou_induk_nomor']) : '-'; ?>
                                        </td>
                                        <td width="120" class="text-center">
                                            <?php if (!empty($data_pks['link_dokumen_pks'])) { ?>
                                                <a href="<?php echo htmlspecialchars($data_pks['link_dokumen_pks']); ?>" target="_blank"
                                                    class="btn btn-icon btn-info btn-sm" data-toggle="tooltip" data-placement="top"
                                                    title="Lihat Dokumen">
                                                    <i class="fas fa-link"></i>
                                                </a>
                                            <?php } ?>
                                        </td>
                                        <td width="100" class="text-center">
                                            <?php echo htmlspecialchars($data_pks['klasifikasi_mitra']); ?>
                                        </td>
                                        <td width="80" class="text-center">
                                            <div>
                                                <!-- Button Ubah -->
                                                <a href="#" class="btn btn-icon btn-round btn-success btn-sm mr-md-1"
                                                    data-toggle="modal"
                                                    data-target="#modalUbahpks_bks<?php echo $data_pks['id']; ?>"
                                                    data-tooltip="tooltip" data-placement="top" title="Ubah"> <i
                                                        class="fas fa-pencil-alt fa-sm"></i>
                                                </a>
                                                <!-- modalUbah -->
                                                <div class="modal fade" id="modalUbahpks_bks<?php echo $data_pks['id']; ?>"
                                                    tabindex="-1" data-bs-toogle role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <form action="modules/bks/pks/proses_ubah.php" method="post">
                                                                <div class="modal-header btn-success">
                                                                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Ubah
                                                                        Data PKS</h5>
                                                                    <button type="button" class="close" data-dismiss="modal"
                                                                        aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="id"
                                                                        value="<?php echo $data_pks['id']; ?>">

                                                                    <div class="form-group">
                                                                        <label>Terhubung ke MoU Induk (Opsional)</label>
                                                                        <select name="mou_id" class="form-control select2-single">
                                                                            <option value="">-- Tidak terhubung / Mandiri --
                                                                            </option>
                                                                            <?php
                                                                            // Query untuk mengambil semua opsi MoU
                                                                            $query_mou_options = mysqli_query($mysqli, "SELECT 
                                                                                    mou.id, 
                                                                                    mou.no_dokumen, 
                                                                                    mitra.nama_mitra 
                                                                                FROM 
                                                                                    tbl_mou_bks AS mou
                                                                                LEFT JOIN 
                                                                                    tbl_mitra_bki AS mitra ON mou.mitra_id = mitra.id
                                                                                ORDER BY 
                                                                                    mitra.nama_mitra ASC");

                                                                            while ($mou_option = mysqli_fetch_assoc($query_mou_options)) {
                                                                                // Logika ini akan memilih MoU yang sudah terhubung
                                                                                $selected = (isset($data_ia['mou_id']) && $mou_option['id'] == $data_ia['mou_id']) ? 'selected' : '';

                                                                                echo "<option value='{$mou_option['id']}' {$selected}>
                                                                                            {$mou_option['no_dokumen']} - {$mou_option['nama_mitra']}
                                                                                        </option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                    <hr />

                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Mitra <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="mitra_id"
                                                                                    class="form-control select2-single" required>
                                                                                    <option
                                                                                        value="<?php echo $data_pks['mitra_id']; ?>"
                                                                                        selected>
                                                                                        <?php echo htmlspecialchars($data_pks['nama_mitra']); ?>
                                                                                    </option>
                                                                                    <?php
                                                                                    $query_mitra_modal = mysqli_query($mysqli, "SELECT id, nama_mitra FROM tbl_mitra_bki ORDER BY nama_mitra ASC");
                                                                                    while ($mitra_modal = mysqli_fetch_assoc($query_mitra_modal)) {
                                                                                        if ($mitra_modal['id'] != $data_pks['mitra_id']) {
                                                                                            echo "<option value='{$mitra_modal['id']}'>{$mitra_modal['nama_mitra']}</option>";
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>PIC (Bagian / Prodi) <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="pic_bagian_id"
                                                                                    class="form-control select2-single" required>
                                                                                    <option
                                                                                        value="<?php echo $data_pks['pic_bagian_id']; ?>"
                                                                                        selected>
                                                                                        <?php echo htmlspecialchars($data_pks['pic_nama']); ?>
                                                                                    </option>
                                                                                    <?php
                                                                                    $query_pic_modal = mysqli_query($mysqli, "SELECT id, nama_bagian FROM tbl_pic_bagian ORDER BY nama_bagian ASC");
                                                                                    while ($pic_modal = mysqli_fetch_assoc($query_pic_modal)) {
                                                                                        if ($pic_modal['id'] != $data_pks['pic_bagian_id']) {
                                                                                            echo "<option value='{$pic_modal['id']}'>{$pic_modal['nama_bagian']}</option>";
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>



                                                                    <div class="form-group">
                                                                        <label>Bentuk Kerjasama <span
                                                                                class="text-danger">*</span></label>
                                                                        <select name="bentuk_kerjasama_bks" class="form-control"
                                                                            required>
                                                                            <option value="" disabled>-- Pilih Bentuk Kerjasama --
                                                                            </option>

                                                                            <option value="Akademik" <?php echo ($data_pks['bentuk_kerjasama_bks'] == 'Akademik') ? 'selected' : ''; ?>>
                                                                                Akademik
                                                                            </option>

                                                                            <option value="Non-Akademik" <?php echo ($data_pks['bentuk_kerjasama_bks'] == 'Non-Akademik') ? 'selected' : ''; ?>>
                                                                                Non-Akademik
                                                                            </option>
                                                                        </select>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>No. Dokumen PKS</label>
                                                                        <input type="text" name="no_dokumen" class="form-control"
                                                                            value="<?php echo htmlspecialchars($data_pks['no_dokumen']); ?>"
                                                                            required>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Tentang (Judul PKS)</label>
                                                                        <textarea name="tentang" class="form-control" rows="3"
                                                                            required><?php echo htmlspecialchars($data_pks['tentang']); ?></textarea>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Tanggal Penandatanganan (Awal)</label>
                                                                                <input type="date" name="tanggal_awal"
                                                                                    class="form-control"
                                                                                    value="<?php echo $data_pks['tanggal_awal']; ?>"
                                                                                    required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Tanggal Berakhir</label>
                                                                                <input type="date" name="tanggal_akhir"
                                                                                    class="form-control"
                                                                                    value="<?php echo $data_pks['tanggal_akhir']; ?>"
                                                                                    required>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <div class="form-group">
                                                                            <label>Klasifikasi Mitra <span
                                                                                    class="text-danger">*</span></label>
                                                                            <select name="klasifikasi_mitra"
                                                                                class="form-control select2-single" required>
                                                                                <option value="" disabled>-- Pilih Klasifikasi
                                                                                    --</option>
                                                                                <?php
                                                                                $klasifikasi_options = ['Industri', 'PLN Group', 'Pemerintahan', 'Start-Up', 'Perusahaan Multinasional', 'Perguruan Tinggi', 'Institusi/Organisasi Multilateral'];
                                                                                foreach ($klasifikasi_options as $option) {
                                                                                    $selected = ($data_mou['klasifikasi_mitra'] == $option) ? 'selected' : '';
                                                                                    echo "<option value='{$option}' {$selected}>{$option}</option>";
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Link Dokumen PKS</label>
                                                                        <input type="url" name="link_dokumen_pks"
                                                                            class="form-control" placeholder="https://"
                                                                            value="<?php echo htmlspecialchars($data_pks['link_dokumen_pks']); ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-default btn-round"
                                                                        data-dismiss="modal">Batal</button>
                                                                    <input type="submit" name="simpan" value="Simpan Perubahan"
                                                                        class="btn btn-success btn-round">
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Button Hapus -->
                                                <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#modalHapus<?php echo $data_pks['id']; ?>" data-tooltip="tooltip"
                                                    data-placement="top" title="Hapus"> <i class="fas fa-trash fa-sm"></i>
                                                </a>
                                                <!-- modalHapus -->
                                                <div class="modal fade" id="modalHapus<?php echo $data_pks['id']; ?>" tabindex="-1"
                                                    role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalHapusLabel"><i
                                                                        class="fas fa-exclamation-triangle mr-2"></i> Konfirmasi
                                                                    Hapus</h5>
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Anda yakin ingin menghapus data PKS dengan nomor
                                                                    <strong><?php echo htmlspecialchars($data_pks['no_dokumen']); ?></strong>?
                                                                </p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary btn-round"
                                                                    data-dismiss="modal">Batal</button>
                                                                <a href="modules/bks/pks/proses_hapus.php?id=<?php echo $data_pks['id']; ?>"
                                                                    class="btn btn-danger btn-round">Ya, Hapus</a>
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

        <!-- Tabel Implementation of Arrangement-->
        <div class="page-inner mt--5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">Daftar Dokumen Implementation of Arrangement</div>
                    <div>
                        <!-- Button tambah mitra -->
                        <a href="?module=mitra_bki" class="btn btn-warning btn-round">
                            <span class="btn-label"><i class="fas fa-users mr-2"></i></span> Mitra
                        </a>
                        <!-- Button tambah jenis dokumen -->
                        <a href="?module=jenis_dokumen_bki" class="btn btn-primary btn-round ml-2">
                            <span class="btn-label"><i class="fas fa-sitemap mr-2"></i></span> Jenis Dokumen
                        </a>
                        <!-- Button tambah dokumen -->
                        <a href="?module=form_entri_ia_bks" class="btn btn-success btn-round ml-2">
                            <span class="btn-label"><i class="fas fa-edit mr-2"></i></span> Input IA
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="ia-datatables" class="display table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th>No. Dokumen </th>
                                    <th class="text-center">Bentuk Kerjasama</th>
                                    <th class="text-center">Tentang</th>
                                    <th class="text-center">Mitra</th>
                                    <th class="text-center">Tgl. TTD</th>
                                    <th class="text-center">Sisa Waktu</th>
                                    <th class="text-center">PIC</th>
                                    <th class="text-center">MoU Induk</th>
                                    <th class="text-center">Link Dokumen IA</th>
                                    <th class="text-center">Klasifikasi Mitra</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                // Query yang sudah kita buat tadi
                                $query = mysqli_query($mysqli, "SELECT
                                    ia.id,
                                    ia.no_dokumen,
                                    ia.mitra_id,
                                    ia.pic_bagian_id,
                                    ia.bentuk_kerjasama_bks,
                                    ia.tentang,
                                    ia.tanggal_awal,
                                    ia.tanggal_akhir,
                                    ia.link_dokumen_ia,
                                    ia.klasifikasi_mitra,
                                    
                                    -- Mengambil data dari tabel yang di-JOIN
                                    mitra.nama_mitra,
                                    pic.nama_bagian AS pic_nama,
                                    mou.no_dokumen AS mou_induk_nomor
                                FROM
                                    tbl_i_a AS ia
                                LEFT JOIN
                                    tbl_mitra_bki AS mitra ON ia.mitra_id = mitra.id
                                LEFT JOIN
                                    tbl_pic_bagian AS pic ON ia.pic_bagian_id = pic.id
                                LEFT JOIN
                                    tbl_mou_bks AS mou ON ia.mou_id = mou.id
                                ORDER BY
                                    ia.id DESC;")
                                    or die('Ada kesalahan pada query tampil data PKS: ' . mysqli_error($mysqli));

                                while ($data_ia = mysqli_fetch_assoc($query)) {
                                    $masa_berlaku = date('d/m/Y', strtotime($data_ia['tanggal_awal'])) . ' - ' . date('d/m/Y', strtotime($data_ia['tanggal_akhir']));
                                    $sisa_waktu = hitung_masa_berlaku($data_ia['tanggal_awal'], $data_ia['tanggal_akhir']);
                                    ?>
                                    <tr>
                                        <td width="30" class="text-center"><?php echo $no++; ?></td>
                                        <td width="120"><?php echo htmlspecialchars($data_ia['no_dokumen']); ?></td>
                                        <td width="180"><?php echo htmlspecialchars($data_ia['bentuk_kerjasama_bks']); ?></td>
                                        <td class="truncate-text" title="<?php echo htmlspecialchars($data_ia['tentang']); ?>">
                                            <?php echo htmlspecialchars($data_ia['tentang']); ?>
                                        </td>
                                        <td width="100" class="truncate-text"
                                            title="<?php echo htmlspecialchars($data_ia['nama_mitra']); ?>">
                                            <?php echo htmlspecialchars($data_ia['nama_mitra']); ?>
                                        </td>
                                        <td width="200" class="text-center">
                                            <?php echo htmlspecialchars($data_ia['tanggal_awal']); ?>
                                        </td>
                                        <td width="100" class="text-center"><?php echo $sisa_waktu; ?></td>
                                        <td width="100" class="text-center truncate-text">
                                            <?php echo htmlspecialchars($data_ia['pic_nama']); ?>
                                        </td>
                                        <td width="120" class="text-center">
                                            <?php echo !empty($data_ia['mou_induk_nomor']) ? htmlspecialchars($data_ia['mou_induk_nomor']) : '-'; ?>
                                        </td>
                                        <td width="120" class="text-center">
                                            <?php if (!empty($data_ia['link_dokumen_ia'])) { ?>
                                                <a href="<?php echo htmlspecialchars($data_ia['link_dokumen_ia']); ?>" target="_blank"
                                                    class="btn btn-icon btn-info btn-sm" data-toggle="tooltip" data-placement="top"
                                                    title="Lihat Dokumen">
                                                    <i class="fas fa-link"></i>
                                                </a>
                                            <?php } ?>
                                        </td>
                                        <td width="100" class="text-center">
                                            <?php echo htmlspecialchars($data_ia['klasifikasi_mitra']); ?>
                                        </td>
                                        <td width="80" class="text-center">
                                            <div>
                                                <!-- Button Ubah -->
                                                <a href="#" class="btn btn-icon btn-round btn-success btn-sm mr-md-1"
                                                    data-toggle="modal" data-target="#modalUbahIA_bks<?php echo $data_ia['id']; ?>"
                                                    data-tooltip="tooltip" data-placement="top" title="Ubah"> <i
                                                        class="fas fa-pencil-alt fa-sm"></i>
                                                </a>
                                                <!-- modalUbah -->
                                                <div class="modal fade" id="modalUbahIA_bks<?php echo $data_ia['id']; ?>"
                                                    tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <form action="modules/bks/implementation_arrangement/proses_ubah.php"
                                                                method="post">
                                                                <div class="modal-header btn-success">
                                                                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Ubah
                                                                        Data Implementation of Arrangement (IA)</h5>
                                                                    <button type="button" class="close" data-dismiss="modal"
                                                                        aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="id"
                                                                        value="<?php echo $data_ia['id']; ?>">

                                                                    <div class="form-group">
                                                                        <label>Terhubung ke MoU Induk (Opsional)</label>
                                                                        <select name="mou_id" class="form-control select2-single">
                                                                            <option value="">-- Tidak terhubung / Mandiri --
                                                                            </option>
                                                                            <?php
                                                                            // Query untuk mengambil semua opsi MoU
                                                                            $query_mou_options = mysqli_query($mysqli, "SELECT 
                                                                                    mou.id, 
                                                                                    mou.no_dokumen, 
                                                                                    mitra.nama_mitra 
                                                                                FROM 
                                                                                    tbl_mou_bks AS mou
                                                                                LEFT JOIN 
                                                                                    tbl_mitra_bki AS mitra ON mou.mitra_id = mitra.id
                                                                                ORDER BY 
                                                                                    mitra.nama_mitra ASC");

                                                                            while ($mou_option = mysqli_fetch_assoc($query_mou_options)) {
                                                                                // Logika ini akan memilih MoU yang sudah terhubung
                                                                                $selected = (isset($data_ia['mou_id']) && $mou_option['id'] == $data_ia['mou_id']) ? 'selected' : '';

                                                                                echo "<option value='{$mou_option['id']}' {$selected}>
                                                                                            {$mou_option['no_dokumen']} - {$mou_option['nama_mitra']}
                                                                                        </option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <hr />

                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Mitra <span class="text-danger">*</span></label>
                                                                            <select name="mitra_id"
                                                                                class="form-control select2-single" required>
                                                                                <option value="<?php echo $data_ia['mitra_id']; ?>"
                                                                                    selected>
                                                                                    <?php echo htmlspecialchars($data_ia['nama_mitra']); ?>
                                                                                </option>
                                                                                <?php
                                                                                $query_mitra_modal = mysqli_query($mysqli, "SELECT id, nama_mitra FROM tbl_mitra_bki ORDER BY nama_mitra ASC");
                                                                                while ($mitra_modal = mysqli_fetch_assoc($query_mitra_modal)) {
                                                                                    if ($mitra_modal['id'] != $data_ia['mitra_id']) {
                                                                                        echo "<option value='{$mitra_modal['id']}'>{$mitra_modal['nama_mitra']}</option>";
                                                                                    }
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>PIC (Bagian / Prodi) <span
                                                                                    class="text-danger">*</span></label>
                                                                            <select name="pic_bagian_id"
                                                                                class="form-control select2-single" required>
                                                                                <option
                                                                                    value="<?php echo $data_ia['pic_bagian_id']; ?>"
                                                                                    selected>
                                                                                    <?php echo htmlspecialchars($data_ia['pic_nama']); ?>
                                                                                </option>
                                                                                <?php
                                                                                $query_pic_modal = mysqli_query($mysqli, "SELECT id, nama_bagian FROM tbl_pic_bagian ORDER BY nama_bagian ASC");
                                                                                while ($pic_modal = mysqli_fetch_assoc($query_pic_modal)) {
                                                                                    if ($pic_modal['id'] != $data_ia['pic_bagian_id']) {
                                                                                        echo "<option value='{$pic_modal['id']}'>{$pic_modal['nama_bagian']}</option>";
                                                                                    }
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label>Bentuk Kerjasama <span
                                                                            class="text-danger">*</span></label>
                                                                    <select name="bentuk_kerjasama_bks"
                                                                        class="form-control select2-single" required>
                                                                        <option value="" disabled>-- Pilih Bentuk Kerjasama --
                                                                        </option>
                                                                        <option value="Akademik" <?php echo ($data_ia['bentuk_kerjasama_bks'] == 'Akademik') ? 'selected' : ''; ?>>
                                                                            Akademik
                                                                        </option>
                                                                        <option value="Non-Akademik" <?php echo ($data_ia['bentuk_kerjasama_bks'] == 'Non-Akademik') ? 'selected' : ''; ?>>
                                                                            Non-Akademik
                                                                        </option>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label>No. Dokumen IA</label>
                                                                    <input type="text" name="no_dokumen" class="form-control"
                                                                        value="<?php echo htmlspecialchars($data_ia['no_dokumen']); ?>"
                                                                        required>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label>Tentang (Judul IA)</label>
                                                                    <textarea name="tentang" class="form-control" rows="3"
                                                                        required><?php echo htmlspecialchars($data_ia['tentang']); ?></textarea>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Tanggal Penandatanganan (Awal)</label>
                                                                            <input type="date" name="tanggal_awal"
                                                                                class="form-control"
                                                                                value="<?php echo $data_ia['tanggal_awal']; ?>"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Tanggal Berakhir</label>
                                                                            <input type="date" name="tanggal_akhir"
                                                                                class="form-control"
                                                                                value="<?php echo $data_ia['tanggal_akhir']; ?>"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                </div>


                                                                <div class="form-group">
                                                                    <div class="form-group">
                                                                        <label>Klasifikasi Mitra <span
                                                                                class="text-danger">*</span></label>
                                                                        <select name="klasifikasi_mitra"
                                                                            class="form-control select2-single" required>
                                                                            <option value="" disabled>-- Pilih Klasifikasi
                                                                                --</option>
                                                                            <?php
                                                                            $klasifikasi_options = ['Industri', 'PLN Group', 'Pemerintahan', 'Start-Up', 'Perusahaan Multinasional', 'Perguruan Tinggi', 'Institusi/Organisasi Multilateral'];
                                                                            foreach ($klasifikasi_options as $option) {
                                                                                $selected = ($data_ia['klasifikasi_mitra'] == $option) ? 'selected' : '';
                                                                                echo "<option value='{$option}' {$selected}>{$option}</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label>Link Dokumen PKS</label>
                                                                    <input type="url" name="link_dokumen_ia" class="form-control"
                                                                        placeholder="https://"
                                                                        value="<?php echo htmlspecialchars($data_ia['link_dokumen_ia']); ?>">
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-default btn-round"
                                                                        data-dismiss="modal">Batal</button>
                                                                    <input type="submit" name="simpan" value="Simpan Perubahan"
                                                                        class="btn btn-success btn-round">
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Button Hapus -->
                                            <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal"
                                                data-target="#modalHapus<?php echo $data_ia['id']; ?>" data-tooltip="tooltip"
                                                data-placement="top" title="Hapus"> <i class="fas fa-trash fa-sm"></i>
                                            </a>
                                            <!-- modalHapus -->
                                            <div class="modal fade" id="modalHapus<?php echo $data_ia['id']; ?>" tabindex="-1"
                                                role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalHapusLabel"><i
                                                                    class="fas fa-exclamation-triangle mr-2"></i> Konfirmasi
                                                                Hapus</h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Anda yakin ingin menghapus data IA dengan nomor
                                                                <strong><?php echo htmlspecialchars($data_ia['no_dokumen']); ?></strong>?
                                                            </p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-round"
                                                                data-dismiss="modal">Batal</button>
                                                            <a href="modules/bks/implementation_arrangement/proses_hapus.php?id=<?php echo $data_ia['id']; ?>"
                                                                class="btn btn-danger btn-round">Ya, Hapus</a>
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

        <!-- Table Rencana -->
        <div class="page-inner mt--5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">Data Rencana Kegiatan BKS</div>
                    <div>
                        <!-- Button tambah dokumen -->
                        <a href="?module=form_entri_rk_bks" data-target="" class="btn btn-success btn-round ml-2">
                            <span class="btn-label"><i class="fas fa-edit mr-2"></i></span> Input
                            Data Rencana</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <!-- tabel untuk menampilkan data dari database -->
                        <table id="rencana-datatables" class="display table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th class="text-center">Rencana Mitra</th>
                                    <th class="text-center">Klasifikasi Mitra</th>
                                    <th class="text-center">Bentuk Kerjasama</th>
                                    <th class="text-center">Perihal</th>
                                    <th class="text-center">Keterangan</th>
                                    <th class="text-center">Status Kerjasama</th>
                                    <th class="text-center">Bulan Target Realisasi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // variabel untuk nomor urut tabel
                                $no = 1;
                                // sql statement untuk menampilkan data dari tabel "tbl_bki" dan "tbl_jenis_dokumen_bki" dan "tbl_mitra_bki"
                                $query = mysqli_query($mysqli, "SELECT 
                                    rk.id,
                                    rk.mitra_id,
                                    rk.bentuk_kerjasama,
                                    rk.klasifikasi_mitra,
                                    rk.target_realisasi,
                                    rk.keterangan,
                                    rk.perihal,
                                    rk.status_kerjasama_id,
                                    m.nama_mitra AS nama_mitra,
                                    m.negara,
                                    DATE_FORMAT(rk.target_realisasi, '%M %Y') AS bulan_target_realisasi,
                                    -- Mengambil data status_kerjasama dari tabel yang baru di-JOIN --
                                    sk.status_kerjasama 
                                FROM 
                                    tbl_rk_bks AS rk
                                JOIN 
                                    tbl_mitra_bki AS m ON rk.mitra_id = m.id
                                -- Tambahkan JOIN ke tabel status kerjasama --
                                LEFT JOIN 
                                    tbl_status_kerjasama AS sk ON rk.status_kerjasama_id = sk.id
                                ORDER BY 
                                    rk.target_realisasi ASC;")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
                                // ambil data hasil query
                                while ($data = mysqli_fetch_assoc($query)) {
                                    ?>
                                    <!-- tampilkan data -->
                                    <tr>
                                        <td width="30" class="text-center"><?php echo $no++; ?>
                                        </td>
                                        <td width="80">
                                            <?php echo $data['nama_mitra']; ?>
                                        </td>
                                        <td width="100" class="text-center"><?php echo $data['klasifikasi_mitra']; ?></td>
                                        <td width="100" class="text-center"><?php echo $data['bentuk_kerjasama']; ?></td>
                                        <td width="100" class="text-center truncate-text" title="<?php echo $data['perihal']; ?>">
                                            <?php echo $data['perihal']; ?>
                                        </td>
                                        <td width="120" class="text-center truncate-text"
                                            title="<?php echo $data['keterangan']; ?>">
                                            <?php echo $data['keterangan']; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status = $data['status_kerjasama'];
                                            $badge_class = '';

                                            switch ($status) {
                                                case 'Sangat Penting':
                                                    $badge_class = 'badge-danger'; // Merah
                                                    break;
                                                case 'Penting':
                                                    $badge_class = 'badge-warning'; // Kuning
                                                    break;
                                                case 'Biasa':
                                                    $badge_class = 'badge-success'; // Hijau
                                                    break;
                                                default:
                                                    $badge_class = 'badge-secondary'; // Abu-abu jika status tidak dikenali
                                                    break;
                                            }

                                            // Tampilkan status dengan badge yang sesuai
                                            echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars($status) . '</span>';
                                            ?>
                                        </td>
                                        <td width="80" class="text-center">
                                            <?php echo $data['bulan_target_realisasi']; ?>
                                        </td>
                                        <td width="10" class="text-center">
                                            <div>
                                                <!-- Button Ubah -->
                                                <a href="#" class="btn btn-icon btn-round btn-success btn-sm mr-md-1"
                                                    data-toggle="modal"
                                                    data-target="#modalUbahRencana_bks<?php echo $data['id']; ?>"
                                                    data-tooltip="tooltip" data-placement="top" title="Ubah"> <i
                                                        class="fas fa-pencil-alt fa-sm"></i>
                                                </a>
                                                <!-- modalUbah -->
                                                <div class="modal fade" id="modalUbahRencana_bks<?php echo $data['id']; ?>"
                                                    tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <form action="modules/bks/proses_ubah.php" method="post">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Ubah
                                                                        Data Rencana</h5>
                                                                    <button type="button" class="close" data-dismiss="modal"
                                                                        aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="id"
                                                                        value="<?php echo $data['id']; ?>">

                                                                    <div class="form-group">
                                                                        <label>Rencana Mitra <span
                                                                                class="text-danger">*</span></label>
                                                                        <select name="mitra_id" class="form-control select2-single"
                                                                            required>
                                                                            <option value="<?php echo $data['mitra_id']; ?>"
                                                                                selected>
                                                                                <?php echo htmlspecialchars($data['nama_mitra']); ?>
                                                                            </option>
                                                                            <?php
                                                                            $query_mitra_modal = mysqli_query($mysqli, "SELECT id, nama_mitra FROM tbl_mitra_bki ORDER BY nama_mitra ASC");
                                                                            while ($mitra_modal = mysqli_fetch_assoc($query_mitra_modal)) {
                                                                                if ($mitra_modal['id'] != $data['mitra_id']) {
                                                                                    echo "<option value='{$mitra_modal['id']}'>{$mitra_modal['nama_mitra']}</option>";
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Klasifikasi Mitra <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="klasifikasi_mitra"
                                                                                    class="form-control" required>
                                                                                    <?php
                                                                                    $klasifikasi_options = ['Industri', 'PLN Group', 'Pemerintahan', 'Start-Up', 'Perusahaan Multinasional', 'Perguruan Tinggi', 'Institusi/Organisasi Multilateral'];
                                                                                    foreach ($klasifikasi_options as $option) {
                                                                                        $selected = ($data['klasifikasi_mitra'] == $option) ? 'selected' : '';
                                                                                        echo "<option value='{$option}' {$selected}>{$option}</option>";
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Bentuk Kerjasama <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="bentuk_kerjasama" class="form-control"
                                                                                    required>
                                                                                    <option value="Akademik" <?php echo ($data['bentuk_kerjasama'] == 'Akademik') ? 'selected' : ''; ?>>Akademik</option>
                                                                                    <option value="Non-Akademik" <?php echo ($data['bentuk_kerjasama'] == 'Non-Akademik') ? 'selected' : ''; ?>>Non-Akademik</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Perihal (Judul Rencana) <span
                                                                                class="text-danger">*</span></label>
                                                                        <textarea name="perihal" class="form-control" rows="2"
                                                                            required><?php echo htmlspecialchars($data['perihal']); ?></textarea>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Keterangan</label>
                                                                        <textarea name="keterangan" class="form-control"
                                                                            rows="3"><?php echo htmlspecialchars($data['keterangan']); ?></textarea>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Target Realisasi <span
                                                                                        class="text-danger">*</span></label>
                                                                                <input type="date" name="target_realisasi"
                                                                                    class="form-control"
                                                                                    value="<?php echo $data['target_realisasi']; ?>"
                                                                                    required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>Status Kerjasama <span
                                                                                        class="text-danger">*</span></label>
                                                                                <select name="status_kerjasama_id"
                                                                                    class="form-control" required>
                                                                                    <?php
                                                                                    $query_status = mysqli_query($mysqli, "SELECT id, status_kerjasama FROM tbl_status_kerjasama ORDER BY id ASC");
                                                                                    while ($status_option = mysqli_fetch_assoc($query_status)) {
                                                                                        $selected = ($data['status_kerjasama_id'] == $status_option['id']) ? 'selected' : '';
                                                                                        echo "<option value='{$status_option['id']}' {$selected}>{$status_option['status_kerjasama']}</option>";
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-default btn-round"
                                                                        data-dismiss="modal">Batal</button>
                                                                    <input type="submit" name="simpan" value="Simpan Perubahan"
                                                                        class="btn btn-success btn-round">
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Button Hapus -->
                                            <a href="#" class="btn btn-icon btn-round btn-danger btn-sm" data-toggle="modal"
                                                data-target="#modalHapus<?php echo $data['id']; ?>" data-tooltip="tooltip"
                                                data-placement="top" title="Hapus"> <i class="fas fa-trash fa-sm"></i>
                                            </a>
                                            <!-- modalHapus -->
                                            <div class="modal fade" id="modalHapus<?php echo $data['id']; ?>" tabindex="-1"
                                                role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-sm" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalHapusLabel"><i
                                                                    class="fas fa-trash mr-2"></i> Hapus Data
                                                            </h5>
                                                        </div>
                                                        <div class="modal-body text-left">
                                                            Anda yakin ingin menghapus rencana pada mitra
                                                            <strong>
                                                                <?php echo htmlspecialchars($data['nama_mitra']); ?>
                                                            </strong>
                                                            dengan bentuk kerjasama
                                                            <strong>
                                                                <?php echo htmlspecialchars($data['bentuk_kerjasama']); ?>
                                                            </strong>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default btn-round"
                                                                data-dismiss="modal">Batal</button>
                                                            <a href="modules/bks/proses_hapus.php?id=<?php echo $data['id']; ?>"
                                                                class="btn btn-danger btn-round">Ya, Hapus</a>
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

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            $(document).ready(function () {
                // ==========================================================
                // KODE JAVASCRIPT UNTUK MENAMPILKAN CHART
                // ==========================================================

                // Chart 1: Jumlah Dokumen per Jenis (Pie Chart)
                const ctxJenis = document.getElementById('chartJenisDokumen').getContext('2d');
                new Chart(ctxJenis, {
                    type: 'pie',
                    data: {
                        labels: ['MoU', 'PKS', 'IA'],
                        datasets: [{
                            label: 'Jumlah Dokumen',
                            data: [
                                <?php echo $total_mou; ?>,
                                <?php echo $total_pks; ?>,
                                <?php echo $total_ia; ?>
                            ],
                            backgroundColor: ['#36A2EB', '#FFCE56', '#4BC0C0']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                // Chart 2: Kerjasama Berdasarkan Bentuk (Doughnut Chart)
                const ctxBentuk = document.getElementById('chartBentukKerjasama').getContext('2d');
                new Chart(ctxBentuk, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_keys($data_bentuk)); ?>,
                        datasets: [{
                            label: 'Bentuk Kerjasama',
                            data: <?php echo json_encode(array_values($data_bentuk)); ?>,
                            backgroundColor: ['#FF6384', '#36A2EB']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                // Chart 3: Kerjasama per Klasifikasi Mitra (Bar Chart)
                const ctxKlasifikasi = document.getElementById('chartKlasifikasiMitra').getContext('2d');
                new Chart(ctxKlasifikasi, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_keys($data_klasifikasi)); ?>,
                        datasets: [{
                            label: 'Jumlah Kerjasama',
                            data: <?php echo json_encode(array_values($data_klasifikasi)); ?>,
                            backgroundColor: '#FF6384'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }
                        }
                    }
                });
            });
        </script>

        <script type="text/javascript">
            $(document).ready(function () {
                // Inisialisasi DataTables untuk setiap tabel
                $('#mou-datatables').DataTable({
                    "pageLength": 10,
                    "ordering": false
                });
                $('#pks-datatables').DataTable({
                    "pageLength": 10,
                    "ordering": false
                });
                $('#ia-datatables').DataTable({
                    "pageLength": 10,
                    "ordering": false
                });
                $('#rencana-datatables').DataTable({
                    "pageLength": 10,
                    "ordering": false
                });
                // dapatkan parameter URL
                let queryString = window.location.search;
                let urlParams = new URLSearchParams(queryString);
                // ambil data dari URL
                let pesan = urlParams.get('pesan');
                let nomor = urlParams.get('nomor');
                // menampilkan pesan sesuai dengan proses yang dijalankan
                // jika pesan = 1
                if (pesan === '1') {
                    // tampilkan pesan sukses simpan data
                    $.notify({
                        title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
                        message: 'Data pada BKS berhasil disimpan.'
                    }, {
                        type: 'success'
                    });
                }
                // jika pesan = 2
                else if (pesan === '2') {
                    // tampilkan pesan sukses ubah data
                    $.notify({
                        title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
                        message: 'Data dokumen BKS berhasil diubah.'
                    }, {
                        type: 'success'
                    });
                }
                // jika pesan = 3
                else if (pesan === '3') {
                    // tampilkan pesan sukses hapus data
                    $.notify({
                        title: '<h5 class="text-success font-weight-bold mb-1"><i class="fas fa-check-circle mr-2"></i>Sukses!</h5>',
                        message: 'Data dokumen BKS berhasil dihapus.'
                    }, {
                        type: 'success'
                    });
                }
                // jika pesan = 4
                else if (pesan === '4') {
                    // tampilkan pesan gagal unggah file
                    $.notify({
                        title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
                        message: 'Data MoU tidak dapat dihapus dikarenakan <strong>masih ada PKS yang terhubung</strong> pada MoU yang ingin.'
                    }, {
                        type: 'danger'
                    });
                }
                // jika pesan = 5
                else if (pesan === '5') {
                    // tampilkan pesan gagal unggah file
                    $.notify({
                        title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
                        message: 'Ukuran file dokumen lebih dari 10 Mb. Harap unggah file dokumen yang memiliki ukuran <strong>maksimal 10 Mb</strong>.'
                    }, {
                        type: 'danger'
                    });
                }
                // jika pesan = 6
                else if (pesan === '6') {
                    // tampilkan pesan gagal unggah file
                    $.notify({
                        title: '<h5 class="text-danger font-weight-bold mb-1"><i class="fas fa-times-circle mr-2"></i>Gagal!</h5>',
                        message: 'Anda tidak berhak untuk menghapus atau mengakses file ini.'
                    }, {
                        type: 'danger'
                    });
                }
            });
        </script>
        <?php
    }
}
?>