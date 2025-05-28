-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 23, 2025 at 02:27 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

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
-- Table structure for table `tbl_arsip`
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
-- Dumping data for table `tbl_arsip`
--

INSERT INTO `tbl_arsip` (`id_arsip`, `jenis_dokumen`, `bulan_tahun`, `tahun_anggaran`, `dipa`, `dokumen_elektronik`) VALUES
(1, 12, 'September 2023', '2023', '03', '6e28ac9aac515ec0118a5824d367a59a4bf9078f.pdf'),
(2, 14, 'September 2023', '2023', '03', '3a8d749b903767566a8946e9cc922f17c3f18da3.pdf'),
(3, 13, 'September 2023', '2023', '03', '740ab834d7381f2921a6586ed1040ff058f64274.pdf'),
(4, 14, 'September 2023', '2023', '03', 'a47015ee573873f750b998213e2c6e55261a2207.pdf'),
(5, 13, 'September 2023', '2023', '01', '78268ef76f111471eb2b8e3738163546c538fd54.pdf'),
(6, 13, 'Oktober 2023', '2023', '03', '9ecfacfa94dcc89f01b094bb102300fc38248a45.pdf'),
(7, 14, 'Oktober 2023', '2023', '03', '6c6a88acbd2034a26422a192ebec37ab5b1cdfd2.pdf'),
(8, 12, 'Oktober 2023', '2023', '03', '39a14d5d26706912c966dd923cf88e331f311703.pdf'),
(9, 12, 'Oktober 2023', '2023', '03', '13258c776eb9116a0c88b364f709dca40a14eec1.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_jenis`
--

CREATE TABLE `tbl_jenis` (
  `id_jenis` int NOT NULL,
  `nama_jenis` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_jenis`
--

INSERT INTO `tbl_jenis` (`id_jenis`, `nama_jenis`) VALUES
(12, 'MoU'),
(13, 'Pakta Integritas'),
(14, 'Implementation Arrangement/IA');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_profil`
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
-- Dumping data for table `tbl_profil`
--

INSERT INTO `tbl_profil` (`nama`, `alamat`, `telepon`, `email`, `website`, `logo`) VALUES
('Dashboard Kerja', 'Cengkareng, Jakarta Barat', '090909090990', 'azmi@itpln.ac.id', 'itpln.ac.id', '7d727aaef6dd7a0d7da4d2475fa05232c388e140.png');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id_user` int NOT NULL,
  `nama_user` varchar(50) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `hak_akses` enum('Administrator','Bendahara','Pengguna') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id_user`, `nama_user`, `username`, `password`, `hak_akses`) VALUES
(1, 'Azmi Azis', 'azmiazis', '$2y$12$Nm5y4o8cI6.OQsXz7hrNlun3k.yYo29kT73VLyqnln80qy.EWtLBy', 'Administrator'),
(2, 'Nabila Putri Cahyani', 'nabilaputri', '$2y$12$TjIlTeU3qZM.5wrYV026tOFnMGuofvwKIx7qt0gDbcBSqpIPUsTbq', 'Administrator'),
(3, 'user', 'pengguna', '$2y$12$jFg2wNDYGR770gdKdvuw.O6alIqR2YKVMUA5vAn1OXo2.yyxPp.vq', 'Pengguna');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_arsip`
--
ALTER TABLE `tbl_arsip`
  ADD PRIMARY KEY (`id_arsip`);

--
-- Indexes for table `tbl_jenis`
--
ALTER TABLE `tbl_jenis`
  ADD PRIMARY KEY (`id_jenis`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_arsip`
--
ALTER TABLE `tbl_arsip`
  MODIFY `id_arsip` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_jenis`
--
ALTER TABLE `tbl_jenis`
  MODIFY `id_jenis` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
