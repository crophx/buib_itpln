<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    // alihkan ke halaman error 404
    header('location: 404.html');
}
// jika file di include oleh file lain, tampilkan isi file
else {

// Fungsi helper untuk merender card menu
function render_menu_card($title, $module_name, $icon_class, $icon_style_color, $current_user_role) {
    $can_access = false; // Default: tidak bisa akses, akan diubah jika kondisi terpenuhi
    $full_access_roles = ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan'];

    if (in_array($current_user_role, $full_access_roles)) {
        $can_access = true;
    } else {
        switch ($current_user_role) {
            case 'BKS':
                if ($module_name === 'bks') $can_access = true;
                break;
            case 'BKI':
                if ($module_name === 'bki') $can_access = true;
                break;
            case 'TrainingCenter':
                if ($module_name === 'training_center') $can_access = true;
                break;
            case 'PusatBisnis':
                if ($module_name === 'pusat_bisnis') $can_access = true;
                break;
            case 'LEMTERA':
                if ($module_name === 'lemtera') $can_access = true;
                break;
        }
    }

    $link_href = $can_access ? "?module=" . $module_name : "javascript:void(0);";
    $card_extra_style = !$can_access ? "opacity: 0.6; cursor: not-allowed;" : "";

    // Modifikasi di sini untuk $anchor_extra_attributes
    $base_anchor_style = "text-decoration: none !important;"; // Menghilangkan garis bawah secara paksa

    if (!$can_access) {
        $current_anchor_style = $base_anchor_style . " pointer-events: none; cursor: default;";
        $onclick_attr = 'onclick="event.preventDefault(); alert(\'Anda tidak memiliki hak akses ke modul ini.\'); return false;"';
        $anchor_extra_attributes = 'style="' . $current_anchor_style . '" ' . $onclick_attr;
    } else {
        $current_anchor_style = $base_anchor_style . " color: inherit;"; // Pertahankan pewarisan warna jika bisa diakses
        $anchor_extra_attributes = 'style="' . $current_anchor_style . '"';
    }

    $icon_html = '<i class="' . $icon_class . '"' . ($icon_style_color ? ' style="color: ' . $icon_style_color . ';"' : '') . '></i>';
    if ($module_name === 'bks') {
        $icon_html = '<i class="' . $icon_class . '" style="color: black;"></i>';
    } elseif ($module_name === 'buib' && (is_null($icon_style_color) || $icon_style_color === 'antiquewhite')) {
        $icon_html = '<i class="' . $icon_class . '" style="color: antiquewhite;"></i>';
    }

    echo '<div class="col-sm-12 col-md-4">';
    // Kelas "text-decoration-none" dari Bootstrap tetap ada, inline style akan memperkuatnya
    echo '  <a href="' . $link_href . '" class="text-decoration-none" ' . $anchor_extra_attributes . '>';
    echo '    <div class="card card-stats card-round" style="' . $card_extra_style . '">';
    echo '      <div class="card-body">';
    echo '        <div class="row align-items-center">';
    echo '          <div class="col-icon">';
    echo '            <div class="icon-big text-center bubble-shadow-small">';
    echo $icon_html;
    echo '            </div>';
    echo '          </div>';
    echo '          <div class="col col-stats ml-3 ml-sm-0">';
    echo '            <div class="numbers">';
    echo '              <h4 class="card-tittle">' . htmlspecialchars($title) . '</h4>';
    echo '            </div>';
    echo '          </div>';
    echo '        </div>';
    echo '      </div>';
    echo '    </div>';
    echo '  </a>';
    echo '</div>';
}
?>
    <div class="panel-header">
        <div class="page-inner py-45">
            <div class="page-header">
                <h4 class="page-title"><i class="fas fa-home mr-2"></i> Beranda</h4>
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=beranda">Beranda</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="row row-card-no-pd align-items-center p-2 p-sm-4">
            <div class="col-lg-3 mb-4 mb-lg-0">
                <img class="img-fluid" src="assets/img/bg-dashboard.jpg">
            </div>
            <div class="col-lg-9 heroes-text px-xl-5">
                <h2 class="text-success mb-2">Selamat datang kembali <span><?php echo $_SESSION['nama_user']; ?></span> di <span> Website <?php echo $data['nama']; ?></span></h2>
                <h4 class="text-muted mb-4">Dashsboard Kerja adalah Sistem Informasi Monitoring dan juga antarmuka atau alat digital yang dirancang untuk memberikan informasi komprehensif tentang berbagai aspek pekerjaan, kinerja, dan informasi dalam bentuk visualisasi.</h4>
            </div>
        </div>

        <?php
        // Pengecekan hak akses untuk menampilkan blok menu card ini
        if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'BKS', 'BKI', 'TrainingCenter', 'LEMTERA', 'PusatBisnis'])) { ?>
            <div class="row mt-5">
            <?php
            render_menu_card('Pusat Bisnis', 'pusat_bisnis', 'fas fa-truck', 'steelblue', $_SESSION['hak_akses']);
            render_menu_card('Bagian Kerja Sama (BKS)', 'bks', 'fas fa-clone', null, $_SESSION['hak_akses']);
            render_menu_card('Bagian Kerja Internasional (BKI)', 'bki', 'fas fa-camera', 'violet', $_SESSION['hak_akses']);
            render_menu_card('BUIB', 'buib', 'fas fa-university', 'antiquewhite', $_SESSION['hak_akses']);
            render_menu_card('LEMTERA', 'lemtera', 'fas fa-leaf', 'green', $_SESSION['hak_akses']);
            render_menu_card('Training Center (TC)', 'training_center', 'fas fa-chalkboard-teacher', 'tomato', $_SESSION['hak_akses']);
            ?>
            </div>
        <?php } ?>
        <div class="card mt-2">
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        const chartLabels = <?php echo json_encode($chart_labels); ?>;
        const chartData = <?php echo json_encode($chart_data); ?>;
        const chartColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF', '#4D5360'];

        if (document.getElementById('barChart') && chartData.length > 0) {
            const barCtx = document.getElementById('barChart').getContext('2d');
            const barChart = new Chart(barCtx, { /* ... Konfigurasi Bar Chart ... */ });
        }

        if (document.getElementById('pieChart') && chartData.length > 0) {
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            const pieChart = new Chart(pieCtx, { /* ... Konfigurasi Pie Chart ... */ });
        }

        function toggleTable() {
            const tableContainer = document.getElementById('tableContainer');
            const toggleIcon = document.getElementById('toggleIcon');
            const toggleText = document.getElementById('toggleText');

            if (tableContainer.style.display === 'none') {
                tableContainer.style.display = 'block';
                toggleIcon.className = 'fas fa-eye';
                toggleText.textContent = 'Sembunyikan Tabel';
            } else {
                tableContainer.style.display = 'none';
                toggleIcon.className = 'fas fa-eye-slash';
                toggleText.textContent = 'Tampilkan Tabel';
            }
        }

        // Function untuk toggle target table visibility
        function toggleTargetTable() {
            const targetTableContainer = document.getElementById('targetTableContainer');
            const toggleTargetIcon = document.getElementById('toggleTargetIcon');
            const toggleTargetText = document.getElementById('toggleTargetText');
            
            if (targetTableContainer.style.display === 'none') {
                targetTableContainer.style.display = 'block';
                toggleTargetIcon.className = 'fas fa-eye';
                toggleTargetText.textContent = 'Sembunyikan Tabel';
            } else {
                targetTableContainer.style.display = 'none';
                toggleTargetIcon.className = 'fas fa-eye-slash';
                toggleTargetText.textContent = 'Tampilkan Tabel';
            }
        }
    </script>
<?php } ?>