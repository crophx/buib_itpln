<?php
// mencegah direct access file PHP agar file PHP tidak bisa diakses secara langsung dari browser dan hanya dapat dijalankan ketika di include oleh file lain
// jika file diakses secara langsung
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    // alihkan ke halaman error 404
    header('location: 404.html');
}
// jika file di include oleh file lain, tampilkan isi file
else {
    // pengecekan hak akses untuk menampilkan konten sesuai dengan hak akses
    // jika hak akses = Administrator atau hak akses = Bendahara, tampilkan konten
    if (in_array($_SESSION['hak_akses'], ['SuperAdmin', 'BUIB', 'Pimpinan', 'SekretarisPimpinan', 'PusatBisnis'])) {
        ?>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Segera Hadir!</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">

        <head>
            <style>
                /* Reset dasar */
                body,
                html {
                    margin: 0;
                    padding: 0;
                    font-family: 'Poppins', sans-serif;
                    height: 100%;
                }

                /* Latar belakang halaman disamakan dengan aplikasi utama Anda */
                .bg-container {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    text-align: center;
                    min-height: 100vh;
                    /* Warna disesuaikan menjadi putih/abu-abu muda seperti aplikasi Anda */
                    background-color: #f4f5f8;
                }

                /* Kontainer utama untuk konten */
                .content-box {
                    background: #ffffff;
                    /* Latar belakang kotak menjadi putih */
                    padding: 40px 50px;
                    border-radius: 10px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
                    max-width: 900px;
                    width: 100%;
                }

                /* Styling untuk judul utama */
                h1 {
                    font-size: 3.5rem;
                    margin-bottom: 10px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 2px;
                    color: #343a40;
                    /* Warna teks utama menjadi gelap */
                }

                /* Styling untuk sub-judul */
                p {
                    font-size: 1.1rem;
                    margin-bottom: 30px;
                    font-weight: 300;
                    color: #6c757d;
                    /* Warna teks sekunder */
                }

                .page-title {
                    color: #343a40 !important;
                    /* Pastikan judul fitur juga gelap */
                }

                /* Kontainer untuk countdown timer */
                #countdown {
                    display: flex;
                    justify-content: center;
                    gap: 20px;
                    margin-bottom: 30px;
                }

                /* Styling untuk setiap kotak waktu */
                .time-box {
                    background: #16283aff;
                    /* Warna biru primer dari tema Anda */
                    color: #ffffff;
                    padding: 15px;
                    border-radius: 10px;
                    min-width: 90px;
                }

                .time-box span {
                    display: block;
                    font-size: 2.5rem;
                    font-weight: 700;
                    color: #ffffff;
                }

                .time-box .label {
                    font-size: 0.8rem;
                    text-transform: uppercase;
                    font-weight: 300;
                    margin-top: 5px;
                    color: rgba(255, 255, 255, 0.8);
                }
            </style>
        </head>

        <body>

            <div class="bg-container">
                <div class="content-box">
                    <h1>COMING SOON</h1>
                    <p>Halaman Pusat Bisnis sedang dalam tahapan pengembangan.<br></p>
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="page-title mt-4 mb-2">Fitur Mendatang</h4>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats  card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center">
                                                <i class="fas fa-coffee"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ml-3 ml-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Edu-cafe</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats  card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center">
                                                <i class="fas fa-utensils"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ml-3 ml-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">e-Canteen</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats  card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center">
                                                <i class="fas fa-heartbeat"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ml-3 ml-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Edu-Health</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats  card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center">
                                                <i class="fas fa-parking"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ml-3 ml-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">e-Parking</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // ======================================================
                // UBAH TANGGAL TARGET PELUNCURAN ANDA DI SINI
                // Format: "Bulan Hari, Tahun Jam:Menit:Detik"
                // Contoh: "Jan 1, 2026 00:00:00"
                // ======================================================
                const countDownDate = new Date("Aug 13, 2025 00:00:00").getTime();

                // Memperbarui hitungan setiap 1 detik
                const x = setInterval(function () {
                    const now = new Date().getTime();
                    const distance = countDownDate - now;

                    // Perhitungan waktu untuk hari, jam, menit, dan detik
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Menampilkan hasilnya di dalam elemen yang sesuai
                    document.getElementById("days").innerText = String(days).padStart(2, '0');
                    document.getElementById("hours").innerText = String(hours).padStart(2, '0');
                    document.getElementById("minutes").innerText = String(minutes).padStart(2, '0');
                    document.getElementById("seconds").innerText = String(seconds).padStart(2, '0');

                    // Jika hitungan selesai, tulis teks
                    if (distance < 0) {
                        clearInterval(x);
                        document.getElementById("countdown").innerHTML = "<h2>Situs kami sudah diluncurkan!</h2>";
                    }
                }, 1000);
            </script>
        </body>

    <?php }
}
?>