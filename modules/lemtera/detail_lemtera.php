<?php
// Mencegah akses langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('location: 404.html');
    exit();
}
// Tampilkan isi file jika di-include
else {
    // Pastikan user sudah login dan memiliki hak akses
    if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'LEMTERA', 'Pimpinan', 'SekretarisPimpinan'])) {
        // Ambil ID dari URL
        $id_rk_lemtera = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // Query untuk mengambil data utama program
        $query_main = mysqli_query($mysqli, "SELECT a.*, b.nama_entity_lemtera, c.nama_status FROM tbl_rk_lemtera as a LEFT JOIN tbl_entity_lemtera as b ON a.entity_lemtera = b.id_entity LEFT JOIN tbl_status as c ON a.status_lemtera = c.id_status WHERE a.id = '$id_rk_lemtera'") or die('Error: ' . mysqli_error($mysqli));
        $data_main = mysqli_fetch_assoc($query_main);

        // Query untuk mengambil data detail
        $query_detail = mysqli_query($mysqli, "SELECT * FROM tbl_detail_lemtera WHERE id_rk_lemtera = '$id_rk_lemtera'") or die('Error: ' . mysqli_error($mysqli));
        $data_detail = mysqli_fetch_assoc($query_detail);

        // **BARU**: Query dan kalkulasi total
        $total_termin = 0;
        $query_termins = mysqli_query($mysqli, "SELECT * FROM tbl_termin_pembayaran WHERE id_rk_lemtera = '$id_rk_lemtera' ORDER BY tanggal_termin ASC");
        while($row = mysqli_fetch_assoc($query_termins)) { $total_termin += $row['nominal_termin']; }
        mysqli_data_seek($query_termins, 0); // Reset pointer

        $total_operasional = 0;
        $query_operasional = mysqli_query($mysqli, "SELECT * FROM tbl_biaya_operasional WHERE id_rk_lemtera = '$id_rk_lemtera' ORDER BY tanggal_biaya ASC");
        while($row = mysqli_fetch_assoc($query_operasional)) { $total_operasional += $row['jumlah_biaya']; }
        mysqli_data_seek($query_operasional, 0); // Reset pointer
        
        $total_penalty = 0;
        $query_penalties = mysqli_query($mysqli, "SELECT * FROM tbl_penalty_program WHERE id_rk_lemtera = '$id_rk_lemtera' ORDER BY tanggal_penalty ASC");
        while($row = mysqli_fetch_assoc($query_penalties)) { $total_penalty += $row['jumlah_penalty']; }
        mysqli_data_seek($query_penalties, 0); // Reset pointer

        $profit = $total_termin - ($total_operasional + $total_penalty);

        if (!$data_main) { header('location: ?module=lemtera'); exit(); }
?>

    <div class="panel-header">
        <div class="page-inner py-45">
            <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
                <div class="page-header">
                    <h4 class="page-title"><i class="fas fa-tasks mr-2"></i>Detail Program Lemtera</h4>
                    <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=lemtera">Data Lemtera</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Detail Program Lemtera</a></li>
                </ul>
                </div>
                <div class="ml-md-auto py-2 py-md-0"><a href="?module=lemtera" class="btn btn-secondary btn-round"><span class="btn-label"><i class="fa fa-arrow-left mr-2"></i></span>Kembali</a></div>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="row">
            <div class="col-md-12"><div class="card"><div class="card-body p-3"><h4 class="mb-1"><strong><?php echo htmlspecialchars($data_main['nama_program']); ?></strong></h4><p class="mb-0 text-muted">Kategori: <span class="badge badge-info"><?php echo htmlspecialchars($data_main['nama_entity_lemtera']); ?></span> | Status: <span class="badge badge-primary"><?php echo htmlspecialchars($data_main['nama_status']); ?></span></p></div></div></div>
            <div class="col-sm-6 col-md-3"><div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-hand-holding-usd"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Total Uang Masuk</p><h4 class="card-title">Rp <?php echo number_format($total_termin, 0, ',', '.'); ?></h4></div></div></div></div></div></div>
            <div class="col-sm-6 col-md-3"><div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-money-bill-wave"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Total Operasional</p><h4 class="card-title">Rp <?php echo number_format($total_operasional, 0, ',', '.'); ?></h4></div></div></div></div></div></div>
            <div class="col-sm-6 col-md-3"><div class="card card-stats card-round"><div class="card-body <?php echo ($profit < 0) ? 'bg-danger-gradient text-white' : 'bg-primary-gradient text-white'; ?>"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center bubble-shadow-small"><i class="fas fa-chart-line"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category text-white">Profit</p><h4 class="card-title text-white">Rp <?php echo number_format($profit, 0, ',', '.'); ?></h4></div></div></div></div></div></div>
            <div class="col-sm-6 col-md-3"><div class="card card-stats card-round"><div class="card-body"><div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-exclamation-triangle"></i></div></div><div class="col col-stats ml-3 ml-sm-0"><div class="numbers"><p class="card-category">Total Penalty</p><h4 class="card-title">Rp <?php echo number_format($total_penalty, 0, ',', '.'); ?></h4></div></div></div></div></div></div>
        </div>

        <form action="modules/lemtera/proses_detail.php" method="POST">
            <input type="hidden" name="id_rk_lemtera" value="<?php echo $id_rk_lemtera; ?>">
            <input type="hidden" name="id_detail" value="<?php echo isset($data_detail['id_detail']) ? $data_detail['id_detail'] : ''; ?>">

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-folder-open mr-2"></i>Dokumen & Jadwal</div></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Link LOI</label>
                            <div class="input-group">
                                <input type="text" name="loi_url" class="form-control" value="<?php echo htmlspecialchars($data_detail['loi_url'] ?? ''); ?>">
                                <?php if (!empty($data_detail['loi_url'])) : ?>
                                <div class="input-group-append">
                                    <a href="<?php echo htmlspecialchars($data_detail['loi_url']); ?>" target="_blank" class="btn btn-info btn-sm" title="Buka Link di Tab Baru"><i class="fas fa-external-link-alt"></i></a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Link SPK/PKS</label>
                            <div class="input-group">
                                <input type="text" name="spk_pks_url" class="form-control" value="<?php echo htmlspecialchars($data_detail['spk_pks_url'] ?? ''); ?>">
                                <?php if (!empty($data_detail['spk_pks_url'])) : ?>
                                <div class="input-group-append">
                                    <a href="<?php echo htmlspecialchars($data_detail['spk_pks_url']); ?>" target="_blank" class="btn btn-info btn-sm" title="Buka Link di Tab Baru"><i class="fas fa-external-link-alt"></i></a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Tanggal Akhir (Deadline)</label>
                            <input type="date" name="tanggal_akhir" class="form-control" value="<?php echo $data_detail['tanggal_akhir'] ?? ''; ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="card-title"><i class="fas fa-arrow-down mr-2"></i>Termin Pembayaran (Uang Masuk)</div>
                        <button type="button" id="add-termin" class="btn btn-light btn-round btn-sm"><i class="fa fa-plus"></i> Tambah Termin</button>
                    </div>
                </div>
                <div class="card-body" id="termin-container">
                    <?php while ($termin = mysqli_fetch_assoc($query_termins)) : ?>
                    <div class="termin-item p-3 mb-3 border rounded">
                        <div class="row">
                            <div class="col-md-3 form-group"><label>Tanggal</label><input type="date" name="tanggal_termin[]" class="form-control" value="<?php echo htmlspecialchars($termin['tanggal_termin']); ?>"></div>
                            <div class="col-md-3 form-group"><label>Nominal</label><input type="text" name="nominal_termin[]" class="form-control currency" value="<?php echo number_format($termin['nominal_termin'], 0, ',', '.'); ?>"></div>
                            <div class="col-md-5 form-group"><label>Keterangan</label><input type="text" name="keterangan_termin[]" class="form-control" value="<?php echo htmlspecialchars($termin['keterangan_termin']); ?>"></div>
                            <div class="col-md-1 d-flex align-items-end form-group"><button type="button" class="btn btn-danger btn-sm remove-termin"><i class="fas fa-trash"></i></button></div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 form-group mb-0">
                                <label>Link Bukti Bayar</label>
                                <div class="input-group">
                                    <input type="text" name="bukti_termin[]" class="form-control" value="<?php echo htmlspecialchars($termin['bukti_pembayaran_termin']); ?>">
                                    <?php if (!empty($termin['bukti_pembayaran_termin'])) : ?>
                                    <div class="input-group-append">
                                        <a href="<?php echo htmlspecialchars($termin['bukti_pembayaran_termin']); ?>" target="_blank" class="btn btn-info btn-sm" title="Buka Link di Tab Baru"><i class="fas fa-external-link-alt"></i></a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="card-title font-weight-bold"><i class="fas fa-arrow-up mr-2"></i>Biaya Operasional (Uang Keluar)</div>
                        <button type="button" id="add-operasional" class="btn btn-dark btn-round btn-sm"><i class="fa fa-plus"></i> Tambah Biaya</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th width="20%">Tanggal</th><th width="45%">Deskripsi Biaya</th><th width="25%">Jumlah (Rp)</th><th width="10%">Aksi</th></tr></thead>
                            <tbody id="operasional-container">
                                <?php while ($biaya = mysqli_fetch_assoc($query_operasional)) : ?>
                                <tr>
                                    <td><input type="date" name="tanggal_biaya[]" class="form-control" value="<?php echo htmlspecialchars($biaya['tanggal_biaya']); ?>"></td>
                                    <td><input type="text" name="deskripsi_biaya[]" class="form-control" value="<?php echo htmlspecialchars($biaya['deskripsi_biaya']); ?>"></td>
                                    <td><input type="text" name="jumlah_biaya[]" class="form-control currency" value="<?php echo number_format($biaya['jumlah_biaya'], 0, ',', '.'); ?>"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-operasional"><i class="fas fa-trash"></i></button></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot><tr class="bg-light"><td colspan="2" class="text-right"><strong>Total Biaya Operasional</strong></td><td class="text-right"><strong id="total-operasional-display">Rp <?php echo number_format($total_operasional, 0, ',', '.'); ?></strong></td><td></td></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <div class="d-flex justify-content-between align-items-center">
                         <div class="card-title"><i class="fas fa-arrow-up mr-2"></i>Penalty / Denda</div>
                         <button type="button" id="add-penalty" class="btn btn-light btn-round btn-sm"><i class="fa fa-plus"></i> Tambah Penalty</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th width="20%">Tanggal</th><th width="45%">Deskripsi Penalty</th><th width="25%">Jumlah (Rp)</th><th width="10%">Aksi</th></tr></thead>
                            <tbody id="penalty-container">
                                <?php while ($penalty = mysqli_fetch_assoc($query_penalties)) : ?>
                                <tr>
                                    <td><input type="date" name="tanggal_penalty[]" class="form-control" value="<?php echo htmlspecialchars($penalty['tanggal_penalty']); ?>"></td>
                                    <td><input type="text" name="deskripsi_penalty[]" class="form-control" value="<?php echo htmlspecialchars($penalty['deskripsi_penalty']); ?>"></td>
                                    <td><input type="text" name="jumlah_penalty[]" class="form-control currency" value="<?php echo number_format($penalty['jumlah_penalty'], 0, ',', '.'); ?>"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-penalty"><i class="fas fa-trash"></i></button></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot><tr class="bg-light"><td colspan="2" class="text-right"><strong>Total Penalty</strong></td><td class="text-right"><strong id="total-penalty-display">Rp <?php echo number_format($total_penalty, 0, ',', '.'); ?></strong></td><td></td></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card"><div class="card-action"><button type="submit" name="simpan_detail" class="btn btn-success btn-round"><i class="fas fa-save mr-2"></i>Simpan Semua Perubahan</button><a href="?module=lemtera" class="btn btn-default btn-round">Batal</a></div></div>
        </form>
    </div>

    <div id="termin-template" style="display: none;"><div class="termin-item p-3 mb-3 border rounded"><div class="row"><div class="col-md-3 form-group"><label>Tanggal</label><input type="date" name="tanggal_termin[]" class="form-control"></div><div class="col-md-3 form-group"><label>Nominal</label><input type="text" name="nominal_termin[]" class="form-control currency"></div><div class="col-md-5 form-group"><label>Keterangan</label><input type="text" name="keterangan_termin[]" class="form-control"></div><div class="col-md-1 d-flex align-items-end form-group"><button type="button" class="btn btn-danger btn-sm remove-termin"><i class="fas fa-trash"></i></button></div></div><div class="row"><div class="col-md-12 form-group mb-0"><label>Link Bukti Bayar</label><input type="text" name="bukti_termin[]" class="form-control"></div></div></div></div>
    <table style="display: none;"><tbody id="operasional-template"><tr><td><input type="date" name="tanggal_biaya[]" class="form-control"></td><td><input type="text" name="deskripsi_biaya[]" class="form-control"></td><td><input type="text" name="jumlah_biaya[]" class="form-control currency"></td><td><button type="button" class="btn btn-danger btn-sm remove-operasional"><i class="fas fa-trash"></i></button></td></tr></tbody></table>
    <table style="display: none;"><tbody id="penalty-template"><tr><td><input type="date" name="tanggal_penalty[]" class="form-control"></td><td><input type="text" name="deskripsi_penalty[]" class="form-control"></td><td><input type="text" name="jumlah_penalty[]" class="form-control currency"></td><td><button type="button" class="btn btn-danger btn-sm remove-penalty"><i class="fas fa-trash"></i></button></td></tr></tbody></table>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
    <script>
        $(document).ready(function() {
            function initCurrency(selector) { $(selector).maskMoney({ prefix: 'Rp ', thousands: '.', decimal: ',', precision: 0, allowNegative: false }); }
            function calculateTotal(containerId, displayId) {
                let total = 0;
                $(`#${containerId} .currency`).each(function() { total += $(this).maskMoney('unmasked')[0] || 0; });
                $(`#${displayId}`).text('Rp ' + total.toLocaleString('id-ID'));
            }
            initCurrency('.currency');

            // Termin Logic
            $('#add-termin').on('click', function() { $('#termin-container').append($('#termin-template').html()); initCurrency('#termin-container .termin-item:last-child .currency'); });
            $('#termin-container').on('click', '.remove-termin', function() { $(this).closest('.termin-item').remove(); });

            // Operasional Logic
            $('#add-operasional').on('click', function() { $('#operasional-container').append($('#operasional-template').html()); initCurrency('#operasional-container tr:last-child .currency'); });
            $('#operasional-container').on('click', '.remove-operasional', function() { $(this).closest('tr').remove(); calculateTotal('operasional-container', 'total-operasional-display'); });
            $('#operasional-container').on('blur', '.currency', function() { calculateTotal('operasional-container', 'total-operasional-display'); });

            // Penalty Logic
            $('#add-penalty').on('click', function() { $('#penalty-container').append($('#penalty-template').html()); initCurrency('#penalty-container tr:last-child .currency'); });
            $('#penalty-container').on('click', '.remove-penalty', function() { $(this).closest('tr').remove(); calculateTotal('penalty-container', 'total-penalty-display'); });
            $('#penalty-container').on('blur', '.currency', function() { calculateTotal('penalty-container', 'total-penalty-display'); });
        });
    </script>

<?php
    } else { header('location: 404.html'); }
}
?>