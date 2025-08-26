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

        // Query mengambil nama program dan juga nama entity
        $query_main = mysqli_query($mysqli, "SELECT a.nama_program, b.nama_entity_lemtera 
                                             FROM tbl_rk_lemtera as a 
                                             LEFT JOIN tbl_entity_lemtera as b ON a.entity_lemtera = b.id_entity 
                                             WHERE a.id = '$id_rk_lemtera'") or die('Error: ' . mysqli_error($mysqli));
        $data_main = mysqli_fetch_assoc($query_main);

        if (!$data_main) { header('location: ?module=lemtera'); exit(); }
        
        $total_pembayaran_pihak_ketiga = 0;

        // Query untuk mengambil data pihak ketiga dan terminnya
        $pihak_ketiga_data = [];
        $query_pihak_ketiga = mysqli_query($mysqli, "SELECT * FROM tbl_pihak_ketiga WHERE id_rk_lemtera = '$id_rk_lemtera' ORDER BY nama_pihak_ketiga ASC");
        while ($pk = mysqli_fetch_assoc($query_pihak_ketiga)) {
            $id_pk = $pk['id_pihak_ketiga'];
            $pk['termins'] = [];
            $query_termin_pk = mysqli_query($mysqli, "SELECT * FROM tbl_termin_pihak_ketiga WHERE id_pihak_ketiga = '$id_pk' ORDER BY tanggal_termin ASC");
            while ($termin_pk = mysqli_fetch_assoc($query_termin_pk)) {
                $pk['termins'][] = $termin_pk;
                $total_pembayaran_pihak_ketiga += $termin_pk['nominal_termin'];
            }
            $pihak_ketiga_data[] = $pk;
        }
?>

    <div class="panel-header">
        <div class="page-inner py-45">
            <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
                <div class="page-header">
                    <h4 class="page-title"><i class="fas fa-users mr-2"></i>Kelola Pihak Ketiga</h4>
                    <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=lemtera">Data Lemtera</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=detail_lemtera&id=<?php echo $id_rk_lemtera; ?>">Detail Program</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Kelola Pihak Ketiga</a></li>
                </ul>
                </div>
                <div class="ml-md-auto py-2 py-md-0"><a href="?module=detail_lemtera&id=<?php echo $id_rk_lemtera; ?>" class="btn btn-secondary btn-round"><span class="btn-label"><i class="fa fa-arrow-left mr-2"></i></span>Kembali ke Detail</a></div>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body p-3">
                        <h4 class="mb-1"><strong><?php echo htmlspecialchars($data_main['nama_program']); ?></strong></h4>
                        <p class="mb-0 text-muted">Kategori Entity: <span class="badge badge-info"><?php echo htmlspecialchars($data_main['nama_entity_lemtera']); ?></span></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-primary bubble-shadow-small">
                                    <i class="fas fa-receipt"></i>
                                </div>
                            </div>
                            <div class="col col-stats ml-3 ml-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Total Pembayaran</p>
                                    <h4 class="card-title">Rp <?php echo number_format($total_pembayaran_pihak_ketiga, 0, ',', '.'); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <form action="modules/lemtera/proses_pihak_ketiga.php" method="POST">
            <input type="hidden" name="id_rk_lemtera" value="<?php echo $id_rk_lemtera; ?>">
            
            <div id="pihak-ketiga-container">
                <?php 
                $pk_index = 0;
                foreach ($pihak_ketiga_data as $pk) : ?>
                <div class="card pihak-ketiga-item">
                    <div class="card-header" style="background-color: #36A2EB; color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0 font-weight-bold"><i class="fas fa-user-tie mr-2"></i>Data Pihak Ketiga</div>
                            <button type="button" class="btn btn-light btn-sm remove-pihak-ketiga"><i class="fas fa-trash-alt mr-1"></i> Hapus</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="id_pihak_ketiga[<?php echo $pk_index; ?>]" value="<?php echo $pk['id_pihak_ketiga']; ?>">
                        <div class="row">
                            <div class="col-md-4 form-group"><label>Nama</label><input type="text" name="nama_pihak_ketiga[<?php echo $pk_index; ?>]" class="form-control" value="<?php echo htmlspecialchars($pk['nama_pihak_ketiga']); ?>"></div>
                            <div class="col-md-4 form-group"><label>Role</label><input type="text" name="role_pihak_ketiga[<?php echo $pk_index; ?>]" class="form-control" value="<?php echo htmlspecialchars($pk['role_pihak_ketiga']); ?>"></div>
                            
                            <div class="col-md-4 form-group">
                                <label>Link Surat/Kontrak</label>
                                <div class="input-group">
                                    <input type="text" name="link_surat[<?php echo $pk_index; ?>]" class="form-control" value="<?php echo htmlspecialchars($pk['link_surat']); ?>">
                                    <?php if (!empty($pk['link_surat'])) : ?>
                                    <div class="input-group-append">
                                        <a href="<?php echo htmlspecialchars($pk['link_surat']); ?>" target="_blank" class="btn btn-info btn-sm" title="Buka Link di Tab Baru"><i class="fas fa-external-link-alt"></i></a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold">Termin Pembayaran (Uang Keluar)</h6>
                            <button type="button" class="btn btn-primary btn-round btn-sm add-termin-pk"><i class="fa fa-plus"></i> Tambah Termin</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light"><tr><th width="25%">Tanggal</th><th width="40%">Nominal (Rp)</th><th width="35%">Link Bukti Bayar</th><th width="5%">Aksi</th></tr></thead>
                                <tbody class="termin-pk-container">
                                    <?php foreach ($pk['termins'] as $termin_pk) : ?>
                                    <tr>
                                        <input type="hidden" name="id_termin_pk[<?php echo $pk_index; ?>][]" value="<?php echo $termin_pk['id_termin_pk']; ?>">
                                        <td><input type="date" name="tanggal_termin_pk[<?php echo $pk_index; ?>][]" class="form-control" value="<?php echo htmlspecialchars($termin_pk['tanggal_termin']); ?>"></td>
                                        <td><input type="text" name="nominal_termin_pk[<?php echo $pk_index; ?>][]" class="form-control currency" value="<?php echo number_format($termin_pk['nominal_termin'], 0, ',', '.'); ?>"></td>
                                        <td><input type="text" name="bukti_termin_pk[<?php echo $pk_index; ?>][]" class="form-control" value="<?php echo htmlspecialchars($termin_pk['link_bukti_bayar']); ?>"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-termin-pk"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php 
                $pk_index++;
                endforeach; ?>
            </div>

            <button type="button" id="add-pihak-ketiga" class="btn btn-info btn-round mb-3"><i class="fa fa-plus"></i> Tambah Pihak Ketiga Baru</button>
            <div class="card"><div class="card-action"><button type="submit" name="simpan" class="btn btn-success btn-round"><i class="fas fa-save mr-2"></i>Simpan Semua Perubahan</button></div></div>
        </form>
    </div>

    <div id="pihak-ketiga-template" style="display: none;">
        <div class="card pihak-ketiga-item">
            <div class="card-header" style="background-color: #36A2EB; color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0 font-weight-bold"><i class="fas fa-user-tie mr-2"></i>Data Pihak Ketiga (Baru)</div>
                    <button type="button" class="btn btn-light btn-sm remove-pihak-ketiga"><i class="fas fa-trash-alt mr-1"></i> Hapus</button>
                </div>
            </div>
            <div class="card-body">
                <input type="hidden" name="id_pihak_ketiga[PK_INDEX]" value="new">
                <div class="row">
                    <div class="col-md-4 form-group"><label>Nama</label><input type="text" name="nama_pihak_ketiga[PK_INDEX]" class="form-control"></div>
                    <div class="col-md-4 form-group"><label>Role</label><input type="text" name="role_pihak_ketiga[PK_INDEX]" class="form-control"></div>
                    
                    <div class="col-md-4 form-group">
                        <label>Link Surat/Kontrak</label>
                        <div class="input-group">
                            <input type="text" name="link_surat[PK_INDEX]" class="form-control">
                        </div>
                    </div>

                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="font-weight-bold">Termin Pembayaran (Uang Keluar)</h6>
                    <button type="button" class="btn btn-primary btn-round btn-sm add-termin-pk"><i class="fa fa-plus"></i> Tambah Termin</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light"><tr><th width="25%">Tanggal</th><th width="40%">Nominal (Rp)</th><th width="35%">Link Bukti Bayar</th><th width="5%">Aksi</th></tr></thead>
                        <tbody class="termin-pk-container"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <table style="display: none;"><tbody id="termin-pk-template"><tr>
        <input type="hidden" name="id_termin_pk[PK_INDEX][]" value="new">
        <td><input type="date" name="tanggal_termin_pk[PK_INDEX][]" class="form-control"></td>
        <td><input type="text" name="nominal_termin_pk[PK_INDEX][]" class="form-control currency"></td>
        <td><input type="text" name="bukti_termin_pk[PK_INDEX][]" class="form-control"></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-termin-pk"><i class="fas fa-trash"></i></button></td>
    </tr></tbody></table>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
    <script>
        $(document).ready(function() {
            let pk_index = <?php echo $pk_index; ?>;

            function initCurrency(selector) { $(selector).maskMoney({ prefix: 'Rp ', thousands: '.', decimal: ',', precision: 0, allowNegative: false }); }
            initCurrency('.currency');

            $('#add-pihak-ketiga').on('click', function() {
                let template = $('#pihak-ketiga-template').html().replace(/PK_INDEX/g, pk_index);
                $('#pihak-ketiga-container').append(template);
                pk_index++;
            });

            $('#pihak-ketiga-container').on('click', '.remove-pihak-ketiga', function() {
                $(this).closest('.pihak-ketiga-item').remove();
            });

            $('#pihak-ketiga-container').on('click', '.add-termin-pk', function() {
                let item = $(this).closest('.pihak-ketiga-item');
                let currentIndex = item.find('input[name^="id_pihak_ketiga"]').attr('name').match(/\d+/)[0];
                let template = $('#termin-pk-template').html().replace(/PK_INDEX/g, currentIndex);
                
                item.find('.termin-pk-container').append(template);
                initCurrency(item.find('.termin-pk-container tr:last-child .currency'));
            });

            $('#pihak-ketiga-container').on('click', '.remove-termin-pk', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
<?php
    } else { header('location: 404.html'); }
}
?>