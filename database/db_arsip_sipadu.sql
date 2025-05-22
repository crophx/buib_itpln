-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 25 Sep 2023 pada 16.35
-- Versi server: 8.0.30
-- Versi PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_arsip_sipadu`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_arsip`
--

CREATE TABLE `tbl_arsip` (
  `id_arsip` int NOT NULL,
  `jenis_dokumen` int NOT NULL,
  `bulan_tahun` varchar(15) NOT NULL,
  `tahun_anggaran` varchar(4) NOT NULL,
  `dipa` enum('01','03') NOT NULL,
  `dokumen_elektronik` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tbl_arsip`
--

INSERT INTO `tbl_arsip` (`id_arsip`, `jenis_dokumen`, `bulan_tahun`, `tahun_anggaran`, `dipa`, `dokumen_elektronik`) VALUES
(1, 1, 'September 2023', '2023', '03', '6e28ac9aac515ec0118a5824d367a59a4bf9078f.pdf'),
(2, 3, 'September 2023', '2023', '03', '3a8d749b903767566a8946e9cc922f17c3f18da3.pdf'),
(3, 4, 'September 2023', '2023', '03', '740ab834d7381f2921a6586ed1040ff058f64274.pdf'),
(4, 9, 'September 2023', '2023', '03', 'a47015ee573873f750b998213e2c6e55261a2207.pdf'),
(5, 7, 'September 2023', '2023', '01', '78268ef76f111471eb2b8e3738163546c538fd54.pdf'),
(6, 1, 'Oktober 2023', '2023', '03', '9ecfacfa94dcc89f01b094bb102300fc38248a45.pdf'),
(7, 9, 'Oktober 2023', '2023', '03', '6c6a88acbd2034a26422a192ebec37ab5b1cdfd2.pdf'),
(8, 4, 'Oktober 2023', '2023', '03', '39a14d5d26706912c966dd923cf88e331f311703.pdf'),
(9, 2, 'Oktober 2023', '2023', '03', '13258c776eb9116a0c88b364f709dca40a14eec1.pdf');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_jenis`
--

CREATE TABLE `tbl_jenis` (
  `id_jenis` int NOT NULL,
  `nama_jenis` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tbl_jenis`
--

INSERT INTO `tbl_jenis` (`id_jenis`, `nama_jenis`) VALUES
(1, 'Buku Kas Umum'),
(2, 'Buku Pembantu Bank'),
(3, 'Buku Pembantu Kas'),
(4, 'Buku Pembantu Kas Tunai'),
(5, 'Buku Pembantu BPP'),
(6, 'Buku Pembantu Uang Muka'),
(7, 'Buku Uang Persediaan'),
(8, 'Buku Pembantu LS Bendahara'),
(9, 'Buku Pembantu Pajak'),
(10, 'Buku Pembantu Lain-lain'),
(11, 'Buku Pengawasan Kartu Kredit Pemerintah');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_profil`
--

CREATE TABLE `tbl_profil` (
  `nama` varchar(100) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `telepon` varchar(13) NOT NULL,
  `email` varchar(50) NOT NULL,
  `website` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `logo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tbl_profil`
--

INSERT INTO `tbl_profil` (`nama`, `alamat`, `telepon`, `email`, `website`, `logo`) VALUES
('Pengadilan Negeri Nusantara', 'Kota Bandar Lampung, Lampung', '081377783334', 'pengadilan@gmail.com', 'www.pengadilan.go.id', 'b6242644bddb5e866684e80e83bd86a0c1a5a580.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id_user` int NOT NULL,
  `nama_user` varchar(50) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `hak_akses` enum('Administrator','Bendahara','Pengguna') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tbl_user`
--

INSERT INTO `tbl_user` (`id_user`, `nama_user`, `username`, `password`, `hak_akses`) VALUES
(1, 'Indra Styawantoro', 'administrator', '$2y$12$Nm5y4o8cI6.OQsXz7hrNlun3k.yYo29kT73VLyqnln80qy.EWtLBy', 'Administrator'),
(2, 'Arshaka Keenandra', 'bendahara', '$2y$12$TjIlTeU3qZM.5wrYV026tOFnMGuofvwKIx7qt0gDbcBSqpIPUsTbq', 'Bendahara'),
(3, 'Danang Kesuma', 'pengguna', '$2y$12$jFg2wNDYGR770gdKdvuw.O6alIqR2YKVMUA5vAn1OXo2.yyxPp.vq', 'Pengguna');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tbl_arsip`
--
ALTER TABLE `tbl_arsip`
  ADD PRIMARY KEY (`id_arsip`);

--
-- Indeks untuk tabel `tbl_jenis`
--
ALTER TABLE `tbl_jenis`
  ADD PRIMARY KEY (`id_jenis`);

--
-- Indeks untuk tabel `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbl_arsip`
--
ALTER TABLE `tbl_arsip`
  MODIFY `id_arsip` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `tbl_jenis`
--
ALTER TABLE `tbl_jenis`
  MODIFY `id_jenis` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
