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
        // ambil data GET dari button detail
        $id_arsip = $_GET['id'];

        // sql statement untuk menampilkan data dari tabel "tbl_arsip" dan "tbl_jenis" berdasarkan "id_arsip"
        $query = mysqli_query($mysqli, "SELECT a.id_arsip, a.jenis_dokumen, a.bulan_tahun, a.tahun_anggaran, a.dipa, a.dokumen_elektronik, b.nama_jenis 
                                        FROM tbl_arsip as a INNER JOIN tbl_jenis as b ON a.jenis_dokumen=b.id_jenis 
                                        WHERE a.id_arsip='$id_arsip'")
                                        or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
        // ambil data hasil query
        $data = mysqli_fetch_assoc($query);
    }
?>
    <div class="panel-header">
        <div class="page-inner py-45">
            <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
                <div class="page-header">
                    <!-- judul halaman -->
                    <h4 class="page-title"><i class="fas fa-folder-open mr-2"></i> Arsip Dokumen</h4>
                    <!-- breadcrumbs -->
                    <ul class="breadcrumbs">
                        <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a href="?module=arsip">Arsip</a></li>
                        <li class="separator"><i class="flaticon-right-arrow"></i></li>
                        <li class="nav-item"><a>Detail</a></li>
                    </ul>
                </div>
                <div class="ml-md-auto py-2 py-md-0">
                    <!-- button kembali ke halaman tampil data -->
                    <a href="?module=arsip" class="btn btn-success btn-round">
                        <span class="btn-label"><i class="far fa-arrow-alt-circle-left mr-2"></i></span> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-header">
                <!-- judul form -->
                <div class="card-title">Detail Data Arsip Dokumen</div>
            </div>
            <!-- detail data -->
            <div class="card-body">
                <table class="table table-striped">
                    <tr>
                        <td width="200">Jenis Dokumen</td>
                        <td width="10">:</td>
                        <td><?php echo $data['nama_jenis']; ?></td>
                    </tr>
                    <tr>
                        <td>Bulan, Tahun</td>
                        <td>:</td>
                        <td><?php echo $data['bulan_tahun']; ?></td>
                    </tr>
                    <tr>
                        <td>Tahun Anggaran</td>
                        <td>:</td>
                        <td><?php echo $data['tahun_anggaran']; ?></td>
                    </tr>
                    <tr>
                        <td>DIPA</td>
                        <td>:</td>
                        <td><?php echo $data['dipa']; ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Dokumen Elektronik</div>
            </div>
            <div class="card-body">
                <embed src="dokumen/<?php echo $data['dokumen_elektronik']; ?>" type="application/pdf" width="100%" height="700px">
            </div>
        </div>
    </div>
<?php } ?>