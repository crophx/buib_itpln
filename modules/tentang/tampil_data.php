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
                <h4 class="page-title"><i class="fas fa-info-circle mr-2"></i> Tentang Aplikasi</h4>
                <!-- breadcrumbs -->
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Aplikasi</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Tentang</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="card">
            <div class="card-body">
                <div class="py-3">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hashtag text-brand mr-2"></i>
                        </div>
                        <div>
                            <h4 class="lh-2 text-dark mb-3">Copyright</h4>
                            <p>&copy; 2023 - <a href="https://pustakakoding.com/" target="_blank" class="text-brand text-decoration-none">Pustaka Koding</a> - Indra Styawantoro. All rights reserved.</p>
                        </div>
                    </div>
                </div>
                <div class="py-3">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hashtag text-brand mr-2"></i>
                        </div>
                        <div>
                            <h4 class="lh-2 text-dark mb-3">Permissions</h4>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Private use</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Modification</p>
                        </div>
                    </div>
                </div>
                <div class="py-3">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hashtag text-brand mr-2"></i>
                        </div>
                        <div>
                            <h4 class="lh-2 text-dark mb-3">Limitations</h4>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Commercial use</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Distribution</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Liability</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Warranty</p>
                        </div>
                    </div>
                </div>
                <div class="py-3">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hashtag text-brand mr-2"></i>
                        </div>
                        <div>
                            <h4 class="lh-2 text-dark mb-3">Requirements</h4>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> PHP 8.2.<small>x</small></p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> MySQL 8.0.<small>x</small></p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> MySQLi Extension</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Bootstrap 4 (Atlantis Lite Bootstrap Dashboard)</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Web Font Loader v1.6.16</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> jQuery v3.2.1</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> jQuery Scrollbar v0.2.10</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> jQuery UI v1.12.1</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> jQuery UI Touch Punch v0.2.3</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> DataTables v1.10.<small>x</small></p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Datepicker v1.9.0</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Select2 4.0.13</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Bootstrap Notify v3.1.5</p>
                            <p><i class="far fa-circle fa-xs text-brand mr-2"></i> Dompdf v2.0.1</p>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
<?php } ?>